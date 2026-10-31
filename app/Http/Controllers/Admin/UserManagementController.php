<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CustomerProfile;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display users list with filters.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $userType = $request->query('user_type');
        $roleId = $request->query('role_id');
        $branchId = $request->query('branch_id');
        $status = $request->query('status');

        $query = User::with(['roles', 'branches', 'customerProfile', 'staffProfile'])
            ->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('mobile', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($userType && in_array($userType, ['customer', 'staff', 'admin'])) {
            $query->where('user_type', $userType);
        }

        if ($roleId) {
            $query->whereHas('roles', fn($q) => $q->where('roles.id', $roleId));
        }

        if ($branchId) {
            $query->whereHas('branches', fn($q) => $q->where('branches.id', $branchId));
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        $users = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => User::count(),
            'customers' => User::where('user_type', 'customer')->count(),
            'staff' => User::where('user_type', 'staff')->count(),
            'admins' => User::where('user_type', 'admin')->count(),
        ];

        $roles = Role::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.users.index', compact(
            'users',
            'stats',
            'roles',
            'branches',
            'search',
            'userType',
            'roleId',
            'branchId',
            'status'
        ));
    }

    /**
     * Show form to create a new staff, administrator, or customer account.
     */
    public function create(): View
    {
        $roles = Role::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.users.create', compact('roles', 'branches'));
    }

    /**
     * Store new user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'mobile' => ['required', 'string', 'digits:10', 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:6'],
            'user_type' => ['required', 'string', 'in:admin,staff,customer'],
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['exists:roles,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'designation' => ['nullable', 'string', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'driving_license_no' => ['nullable', 'string', 'max:50'],
            'emergency_contact' => ['nullable', 'string', 'max:50'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'mobile' => $validated['mobile'],
            'password' => Hash::make($validated['password']),
            'user_type' => $validated['user_type'],
            'is_active' => true,
        ]);

        // Attach Roles
        $user->roles()->sync($validated['role_ids']);

        // Attach Primary Branch Access
        $user->branches()->sync([
            $validated['branch_id'] => ['is_primary' => true],
        ]);

        // Attach Profile according to user type
        if ($validated['user_type'] === 'staff') {
            StaffProfile::create([
                'user_id' => $user->id,
                'branch_id' => $validated['branch_id'],
                'designation' => $validated['designation'] ?? 'Delivery Executive',
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'driving_license_no' => $validated['driving_license_no'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'is_available_for_delivery' => true,
            ]);
        } elseif ($validated['user_type'] === 'customer') {
            CustomerProfile::create([
                'user_id' => $user->id,
                'branch_id' => $validated['branch_id'],
                'customer_type' => 'residential',
                'credit_limit' => 500.00,
                'outstanding_financial_balance' => 0.00,
                'allow_pay_later' => false,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User account for {$user->name} created successfully!");
    }

    /**
     * Show detailed user profile.
     */
    public function show(User $user): View
    {
        $user->load(['roles', 'branches', 'customerProfile.addresses', 'staffProfile']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show form to edit user.
     */
    public function edit(User $user): View
    {
        $user->load(['roles', 'branches', 'staffProfile', 'customerProfile']);
        $roles = Role::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.users.edit', compact('user', 'roles', 'branches'));
    }

    /**
     * Update user details.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'mobile' => ['required', 'string', 'digits:10', Rule::unique('users', 'mobile')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'user_type' => ['required', 'string', 'in:admin,staff,customer'],
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['exists:roles,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'is_active' => ['required', 'boolean'],
            'designation' => ['nullable', 'string', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'allow_pay_later' => ['nullable', 'boolean'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'mobile' => $validated['mobile'],
            'user_type' => $validated['user_type'],
            'is_active' => (bool) $validated['is_active'],
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        // Sync Roles & Branches
        $user->roles()->sync($validated['role_ids']);
        $user->branches()->sync([
            $validated['branch_id'] => ['is_primary' => true],
        ]);

        // Update Staff or Customer profile
        if ($user->user_type === 'staff' && $user->staffProfile) {
            $user->staffProfile->update([
                'branch_id' => $validated['branch_id'],
                'designation' => $validated['designation'] ?? $user->staffProfile->designation,
                'vehicle_number' => $validated['vehicle_number'] ?? $user->staffProfile->vehicle_number,
            ]);
        } elseif ($user->user_type === 'customer' && $user->customerProfile) {
            $user->customerProfile->update([
                'branch_id' => $validated['branch_id'],
                'credit_limit' => (float) ($validated['credit_limit'] ?? 500.00),
                'allow_pay_later' => (bool) ($validated['allow_pay_later'] ?? false),
            ]);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', "User account for {$user->name} updated successfully.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $statusLabel = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Account for {$user->name} has been {$statusLabel}.");
    }

    /**
     * Display Roles & Permissions Matrix.
     */
    public function permissions(): View
    {
        $roles = Role::with('permissions')->orderBy('name')->get();

        return view('admin.users.permissions', compact('roles'));
    }
}
