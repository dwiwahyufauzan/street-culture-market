<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            'view products',
            'create products',
            'edit products',
            'delete products',
            'manage upsells',
            'manage crosssells',
            'view orders',
            'manage orders',
            'view reports',
            'manage users',
            'manage settings',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions([
            'view products',
            'create products',
            'edit products',
            'delete products',
            'manage upsells',
            'manage crosssells',
            'view orders',
            'manage orders',
        ]);

        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        $ownerRole->syncPermissions(Permission::all());

        Role::firstOrCreate(['name' => 'customer']);

        // Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@streetculturemarket.com'],
            [
                'name' => 'Admin SCM',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081234567890',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
            ]
        );
        $admin->syncRoles(['admin']);

        // Default Owner User
        $owner = User::firstOrCreate(
            ['email' => 'owner@streetculturemarket.com'],
            [
                'name' => 'Owner SCM',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'phone' => '081298765432',
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
            ]
        );
        $owner->syncRoles(['owner']);

        // Default Customer User
        $customer = User::firstOrCreate(
            ['email' => 'customer@streetculturemarket.com'],
            [
                'name' => 'John Customer',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '085712345678',
                'address' => 'Jl. Sudirman No. 45',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12190',
            ]
        );
        $customer->syncRoles(['customer']);
    }
}
