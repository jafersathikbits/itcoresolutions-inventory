<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Create the 3 roles:
     * - Operator
     * - Admin
     * - Super Admin
     *
     * We also create permissions so later we can scale cleanly (optional but best practice).
     */
    public function run(): void
    {
        // Clear cached roles/permissions so changes apply immediately
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // --- Permissions (future-proof, used later) ---
        $permissions = [
            // Units
            'units.view', 'units.create', 'units.update', 'units.delete',

            // Materials + composites
            'materials.view', 'materials.create', 'materials.update', 'materials.delete',
            'materials.stock.add', 'materials.composite.manage',

            // Products + BOM
            'products.view', 'products.create', 'products.update', 'products.delete',
            'products.bom.manage',

            // Orders
            'orders.view', 'orders.create', 'orders.update_status',

            // Dashboard
            'dashboard.view',

            // Reports
            'reports.view', 'reports.generate', 'reports.download',

            // User management (Super Admin only)
            'users.view', 'users.create', 'users.update', 'users.delete', 'users.transfer_ownership',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // --- Roles ---
        $operator = Role::firstOrCreate(['name' => 'Operator', 'guard_name' => 'web']);
        $admin    = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $super    = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        // --- Assign permissions per role (as per your spec) ---

        // Operator: view materials/products, place orders, update order status
        $operator->syncPermissions([
            'dashboard.view',

            'materials.view',
            'products.view',

            'orders.view',
            'orders.create',
            'orders.update_status',

            'reports.view',
        ]);

        // Admin: all features EXCEPT user management
        // (Includes material CRUD and stock add, products CRUD + BOM, reports generate/download)
        $admin->syncPermissions([
            'dashboard.view',

            'units.view', 'units.create', 'units.update', 'units.delete',

            'materials.view', 'materials.create', 'materials.update', 'materials.delete',
            'materials.stock.add', 'materials.composite.manage',

            'products.view', 'products.create', 'products.update', 'products.delete',
            'products.bom.manage',

            'orders.view', 'orders.create', 'orders.update_status',

            'reports.view', 'reports.generate', 'reports.download',
        ]);

        // Super Admin: full access including user management
        $super->syncPermissions(Permission::all());
    }
}
