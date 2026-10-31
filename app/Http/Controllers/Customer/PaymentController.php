<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use App\Models\Order;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Customer financial passbook: chronological statement of debits, credits, and running balance.
     */
    public function ledger(Request $request): View
    {
        $profile = $request->user()->customerProfile;

        if (! $profile) {
            abort(403, 'Customer profile not found.');
        }

        $entries = $profile->ledger()
            ->latest('id')
            ->paginate(20);

        return view('customer.ledger.index', compact('profile', 'entries'));
    }

    /**
     * Customer outstanding dues portal: view balance and past unpaid orders.
     */
    public function dues(Request $request): View
    {
        $profile = $request->user()->customerProfile;

        if (! $profile) {
            abort(403, 'Customer profile not found.');
        }

        $unpaidOrders = Order::where('customer_profile_id', $profile->id)
            ->whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->latest('id')
            ->get();

        return view('customer.payments.pay-dues', compact('profile', 'unpaidOrders'));
    }

    /**
     * Initialize Razorpay checkout for a specific order.
     */
    public function createOrderCheckout(Request $request, Order $order): JsonResponse
    {
        $profile = $request->user()->customerProfile;

        if ($order->customer_profile_id !== $profile->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized order access.'], 403);
        }

        $remainingDue = max(0, (float) $order->total_amount - (float) $order->paid_amount);

        if ($remainingDue <= 0) {
            return response()->json(['success' => false, 'message' => 'Order is already fully paid.'], 422);
        }

        try {
            $razorpayOrder = $this->paymentService->createRazorpayOrder(
                $remainingDue,
                $order->order_number,
                [
                    'order_id' => $order->id,
                    'customer_id' => $profile->id,
                    'type' => 'order_payment',
                ]
            );

            return response()->json([
                'success' => true,
                'key' => config('services.razorpay.key'),
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'], // in paise
                'currency' => 'INR',
                'order_number' => $order->order_number,
                'customer_name' => $request->user()->name,
                'customer_email' => $request->user()->email,
                'customer_mobile' => $request->user()->mobile,
                'remaining_due' => $remainingDue,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Verify Razorpay payment signature and credit order payment.
     */
    public function verifyOrderPayment(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $profile = $request->user()->customerProfile;

        if ($order->customer_profile_id !== $profile->id) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
            abort(403);
        }

        $validated = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $isValid = $this->paymentService->verifyRazorpaySignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature']
        );

        if (! $isValid) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid payment signature.'], 400);
            }
            return back()->withErrors(['payment' => 'Payment signature verification failed.']);
        }

        $amountToPay = isset($validated['amount']) 
            ? (float) $validated['amount']
            : max(0, (float) $order->total_amount - (float) $order->paid_amount);

        $payment = $this->paymentService->recordPayment(
            customer: $profile,
            amount: $amountToPay,
            paymentMethod: 'razorpay',
            order: $order,
            transactionRef: $validated['razorpay_payment_id'],
            gatewayOrderId: $validated['razorpay_order_id'],
            gatewaySignature: $validated['razorpay_signature'],
            notes: "Online Razorpay payment for Order #{$order->order_number}",
            idempotencyKey: $validated['razorpay_payment_id']
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Payment of ₹" . number_format($amountToPay, 2) . " verified and credited successfully!",
                'payment_number' => $payment->payment_number,
                'order_status' => $order->fresh()->order_status,
                'payment_status' => $order->fresh()->payment_status,
            ]);
        }

        return redirect()->route('customer.orders.show', $order->id)
            ->with('success', "Payment of ₹" . number_format($amountToPay, 2) . " received successfully!");
    }

    /**
     * Initialize Razorpay checkout for settling customer dues.
     */
    public function createSettlementCheckout(Request $request): JsonResponse
    {
        $profile = $request->user()->customerProfile;

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $amount = (float) $validated['amount'];

        try {
            $receipt = "SETTLE-{$profile->id}-" . time();
            $razorpayOrder = $this->paymentService->createRazorpayOrder(
                $amount,
                $receipt,
                [
                    'customer_id' => $profile->id,
                    'type' => 'due_settlement',
                ]
            );

            return response()->json([
                'success' => true,
                'key' => config('services.razorpay.key'),
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'currency' => 'INR',
                'customer_name' => $request->user()->name,
                'customer_email' => $request->user()->email,
                'customer_mobile' => $request->user()->mobile,
                'amount_rupees' => $amount,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Verify Razorpay signature and credit customer's account balance.
     */
    public function verifySettlementPayment(Request $request): JsonResponse|RedirectResponse
    {
        $profile = $request->user()->customerProfile;

        $validated = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $isValid = $this->paymentService->verifyRazorpaySignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature']
        );

        if (! $isValid) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid payment signature.'], 400);
            }
            return back()->withErrors(['payment' => 'Payment verification failed.']);
        }

        $payment = $this->paymentService->recordPayment(
            customer: $profile,
            amount: (float) $validated['amount'],
            paymentMethod: 'razorpay',
            order: null,
            transactionRef: $validated['razorpay_payment_id'],
            gatewayOrderId: $validated['razorpay_order_id'],
            gatewaySignature: $validated['razorpay_signature'],
            notes: "Online account dues settlement",
            idempotencyKey: $validated['razorpay_payment_id']
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Payment of ₹" . number_format($validated['amount'], 2) . " received. Your dues have been updated!",
                'payment_number' => $payment->payment_number,
                'outstanding_balance' => $profile->fresh()->outstanding_financial_balance,
            ]);
        }

        return redirect()->route('customer.dues.index')
            ->with('success', "Payment of ₹" . number_format($validated['amount'], 2) . " received successfully!");
    }

    /**
     * Razorpay Server-to-Server Webhook handler.
     */
    public function webhook(Request $request): JsonResponse
    {
        $signature = $request->header('X-Razorpay-Signature');
        $webhookSecret = config('services.razorpay.webhook_secret');
        $payload = $request->getContent();

        if ($webhookSecret && ! str_starts_with(config('services.razorpay.key'), 'rzp_test_mock')) {
            $expected = hash_hmac('sha256', $payload, $webhookSecret);
            if (! hash_equals($expected, (string) $signature)) {
                Log::warning('Razorpay webhook signature mismatch.');
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        $event = $request->input('event');
        $paymentData = $request->input('payload.payment.entity');

        if (($event === 'payment.captured' || $event === 'order.paid') && $paymentData) {
            $paymentId = $paymentData['id'];
            $amount = ((float) $paymentData['amount']) / 100;
            $notes = $paymentData['notes'] ?? [];

            $orderId = $notes['order_id'] ?? null;
            $customerId = $notes['customer_id'] ?? null;

            if ($customerId) {
                $customer = CustomerProfile::find($customerId);
                $order = $orderId ? Order::find($orderId) : null;

                if ($customer) {
                    $this->paymentService->recordPayment(
                        customer: $customer,
                        amount: $amount,
                        paymentMethod: 'razorpay',
                        order: $order,
                        transactionRef: $paymentId,
                        gatewayOrderId: $paymentData['order_id'] ?? null,
                        gatewaySignature: $signature,
                        notes: "Payment captured via Razorpay Webhook [{$event}]",
                        idempotencyKey: $paymentId
                    );
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
