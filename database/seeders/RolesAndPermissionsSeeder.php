<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionsByModule = [
            'orders' => [
                ['name' => 'View Orders', 'slug' => 'orders.view'],
                ['name' => 'Create Orders', 'slug' => 'orders.create'],
                ['name' => 'Edit Orders', 'slug' => 'orders.edit'],
                ['name' => 'Cancel Orders', 'slug' => 'orders.cancel'],
                ['name' => 'Assign Deliveries', 'slug' => 'orders.assign'],
            ],
            'inventory' => [
                ['name' => 'View Inventory', 'slug' => 'inventory.view'],
                ['name' => 'Adjust Stock', 'slug' => 'inventory.adjust'],
                ['name' => 'Transfer Stock', 'slug' => 'inventory.transfer'],
            ],
            'can_exchange' => [
                ['name' => 'View Can Balances', 'slug' => 'can_exchange.view'],
                ['name' => 'Reconcile Cans', 'slug' => 'can_exchange.reconcile'],
                ['name' => 'Penalize Missing Cans', 'slug' => 'can_exchange.penalize'],
            ],
            'payments' => [
                ['name' => 'View Payments', 'slug' => 'payments.view'],
                ['name' => 'Record Payments', 'slug' => 'payments.record'],
                ['name' => 'Reconcile Cash', 'slug' => 'payments.reconcile_cash'],
                ['name' => 'Approve Credit Limits', 'slug' => 'payments.credit_approval'],
            ],
            'suppliers' => [
                ['name' => 'View Suppliers', 'slug' => 'suppliers.view'],
                ['name' => 'Manage Purchase Orders', 'slug' => 'suppliers.po_manage'],
                ['name' => 'Record Goods Receipt', 'slug' => 'suppliers.grn_manage'],
            ],
            'customers' => [
                ['name' => 'View Customers', 'slug' => 'customers.view'],
                ['name' => 'Edit Customer Details', 'slug' => 'customers.edit'],
            ],
            'staff' => [
                ['name' => 'View Staff', 'slug' => 'staff.view'],
                ['name' => 'Manage Staff Profiles', 'slug' => 'staff.manage'],
            ],
            'reports' => [
                ['name' => 'View Reports & Analytics', 'slug' => 'reports.view'],
                ['name' => 'Export Financial Reports', 'slug' => 'reports.export'],
            ],
            'settings' => [
                ['name' => 'Manage System Settings', 'slug' => 'settings.manage'],
                ['name' => 'Manage Branches & Zones', 'slug' => 'branches.manage'],
            ],
        ];

        $createdPermissions = [];
        foreach ($permissionsByModule as $module => $permissions) {
            foreach ($permissions as $perm) {
                $permissionModel = Permission::firstOrCreate(
                    ['slug' => $perm['slug']],
                    ['module' => $module, 'name' => $perm['name']]
                );
                $createdPermissions[$perm['slug']] = $permissionModel->id;
            }
        }

        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full unrestricted access to all branches and system modules',
                'is_system' => true,
                'permissions' => array_keys($createdPermissions),
            ],
            [
                'name' => 'Business Admin',
                'slug' => 'business-admin',
                'description' => 'Business operations and financial control across branches',
                'is_system' => true,
                'permissions' => array_keys($createdPermissions),
            ],
            [
                'name' => 'Branch Manager',
                'slug' => 'branch-manager',
                'description' => 'Day-to-day operations of an assigned branch',
                'is_system' => true,
                'permissions' => [
                    'orders.view', 'orders.create', 'orders.edit', 'orders.cancel', 'orders.assign',
                    'inventory.view', 'inventory.adjust', 'inventory.transfer',
                    'can_exchange.view', 'can_exchange.reconcile',
                    'payments.view', 'payments.record', 'payments.reconcile_cash',
                    'customers.view', 'customers.edit',
                    'staff.view', 'reports.view',
                ],
            ],
            [
                'name' => 'Order Manager',
                'slug' => 'order-manager',
                'description' => 'Processing incoming customer and subscription orders',
                'is_system' => true,
                'permissions' => [
                    'orders.view', 'orders.create', 'orders.edit', 'orders.cancel', 'orders.assign',
                    'customers.view', 'can_exchange.view',
                ],
            ],
            [
                'name' => 'Inventory Manager',
                'slug' => 'inventory-manager',
                'description' => 'Warehouse stock, container sanitization, and supplier procurement',
                'is_system' => true,
                'permissions' => [
                    'inventory.view', 'inventory.adjust', 'inventory.transfer',
                    'suppliers.view', 'suppliers.po_manage', 'suppliers.grn_manage',
                    'can_exchange.view', 'can_exchange.reconcile',
                ],
            ],
            [
                'name' => 'Accounts Manager',
                'slug' => 'accounts-manager',
                'description' => 'Financial ledger, payment gateways, credit approvals, and expenses',
                'is_system' => true,
                'permissions' => [
                    'payments.view', 'payments.record', 'payments.reconcile_cash', 'payments.credit_approval',
                    'reports.view', 'reports.export', 'customers.view',
                ],
            ],
            [
                'name' => 'Delivery Supervisor',
                'slug' => 'delivery-supervisor',
                'description' => 'Slot capacity, delivery routing, and staff dispatching',
                'is_system' => true,
                'permissions' => [
                    'orders.view', 'orders.assign', 'staff.view', 'payments.reconcile_cash',
                ],
            ],
            [
                'name' => 'Delivery Staff',
                'slug' => 'delivery-staff',
                'description' => 'Doorstep delivery execution, OTP verification, can and cash collection',
                'is_system' => true,
                'permissions' => [
                    'orders.view',
                ],
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'End customer ordering drinking water cans',
                'is_system' => true,
                'permissions' => [],
            ],
        ];

        foreach ($roles as $roleData) {
            $perms = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::firstOrCreate(['slug' => $roleData['slug']], $roleData);

            if (!empty($perms)) {
                $permIds = Permission::whereIn('slug', $perms)->pluck('id');
                $role->permissions()->sync($permIds);
            }
        }
    }
}
