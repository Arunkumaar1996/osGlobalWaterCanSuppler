<?php

namespace App\Http\Controllers;

use App\Models\ContactInquiry;
use App\Models\DeliveryPincode;
use App\Models\DeliveryZone;
use App\Models\Faq;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    /**
     * Display the dynamic public landing page.
     */
    public function index(): View
    {
        $products = Product::with(['canType', 'category'])
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('capacity_value', 'desc')
            ->get();

        $deliveryZones = DeliveryZone::with(['pincodes' => function ($q) {
            $q->where('is_serviceable', true);
        }])->where('is_active', true)->get();

        $faqs = Faq::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $testimonials = Testimonial::where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take(6)
            ->get();

        return view('welcome', compact('products', 'deliveryZones', 'faqs', 'testimonials'));
    }

    /**
     * Interactive AJAX endpoint to check delivery pincode serviceability.
     */
    public function checkPincode(Request $request): JsonResponse
    {
        $request->validate([
            'pincode' => ['required', 'string', 'digits:6'],
        ]);

        $pin = trim($request->input('pincode'));

        $record = DeliveryPincode::with('zone.branch')
            ->where('pincode', $pin)
            ->where('is_serviceable', true)
            ->first();

        if ($record) {
            return response()->json([
                'success' => true,
                'serviceable' => true,
                'pincode' => $record->pincode,
                'area_name' => $record->area_name,
                'delivery_charge' => (float) $record->delivery_charge,
                'min_order_amount' => (float) $record->min_order_amount,
                'zone_name' => $record->zone?->name ?? 'Standard Hub Sector',
                'branch_name' => $record->zone?->branch?->name ?? 'Central Distribution Hub',
                'message' => "Great news! We deliver to {$record->area_name} ({$record->pincode}).",
            ]);
        }

        return response()->json([
            'success' => true,
            'serviceable' => false,
            'pincode' => $pin,
            'message' => "We do not deliver to {$pin} yet. We are expanding to new Chennai sectors soon!",
        ]);
    }

    /**
     * Handle public contact form inquiry.
     */
    public function submitContactInquiry(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['nullable', 'email', 'max:191'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:1000'],
        ], [
            'phone.regex' => 'Please enter a valid 10-digit Indian mobile number.',
        ]);

        ContactInquiry::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for reaching out! Our distribution team will contact you shortly.',
            ]);
        }

        return back()->with('success', 'Thank you for reaching out! Our distribution team will contact you shortly.');
    }
}
