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
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.manage',
            'roles.manage',
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.cancel',
            'payments.create',
            'master-data.manage',
            'stock.adjust',
            'reports.view',
            'audit-logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $admin = Role::findOrCreate('admin', 'web');
        $admin->syncPermissions($permissions);

        $employee = Role::findOrCreate('employee', 'web');
        $employee->syncPermissions([
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.cancel',
            'payments.create',
        ]);

        $adminUser = User::query()->first();

        if (! $adminUser && env('ADMIN_EMAIL') && env('ADMIN_PASSWORD')) {
            $adminUser = User::query()->create([
                'name' => env('ADMIN_NAME', 'Administrator'),
                'email' => env('ADMIN_EMAIL'),
                'email_verified_at' => now(),
                'password' => Hash::make(env('ADMIN_PASSWORD')),
            ]);
        }

        $adminUser?->syncRoles($admin);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
