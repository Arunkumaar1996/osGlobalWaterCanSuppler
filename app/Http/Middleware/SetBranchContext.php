<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetBranchContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            if (! session()->has('active_branch_id')) {
                // If user has assigned branches, pick primary or first
                $branch = $user->branches()->wherePivot('is_primary', true)->first()
                    ?? $user->branches()->first()
                    ?? ($user->customerProfile ? $user->customerProfile->branch : null)
                    ?? ($user->staffProfile ? $user->staffProfile->branch : null)
                    ?? Branch::where('is_primary', true)->first();

                if ($branch) {
                    session(['active_branch_id' => $branch->id, 'active_branch_name' => $branch->name]);
                }
            }
        } else {
            if (! session()->has('active_branch_id')) {
                $primary = Branch::where('is_primary', true)->first() ?? Branch::first();
                if ($primary) {
                    session(['active_branch_id' => $primary->id, 'active_branch_name' => $primary->name]);
                }
            }
        }

        return $next($request);
    }
}
