<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CanExchangeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CanBalanceController extends Controller
{
    public function __construct(
        protected CanExchangeService $exchangeService
    ) {}

    /**
     * Customer view of container holding balances and exchange movement history.
     */
    public function index(Request $request): View
    {
        $profile = $request->user()->customerProfile;

        if (! $profile) {
            abort(403, 'Customer profile not found.');
        }

        $totalHeld = $this->exchangeService->getCustomerTotalHeldCans($profile);
        $balances = $this->exchangeService->getCustomerCanBalances($profile);

        $movements = $profile->canMovements()
            ->with(['canType', 'reference'])
            ->latest('id')
            ->paginate(15);

        return view('customer.cans.index', compact('profile', 'totalHeld', 'balances', 'movements'));
    }
}
