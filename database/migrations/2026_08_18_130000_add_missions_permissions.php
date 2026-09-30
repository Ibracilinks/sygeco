<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'view_missions',
            'create_missions',
            'edit_missions',
            'delete_missions',
            'generate_missions_pdf',
        ] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        $superadmin = Role::where('name', 'superadmin')->where('guard_name', $guard)->first();
        $dbcgoq = Role::where('name', 'dbcgoq')->where('guard_name', $guard)->first();
        $chef = Role::where('name', 'chef')->where('guard_name', $guard)->first();

        $all = Permission::whereIn('name', [
            'view_missions',
            'create_missions',
            'edit_missions',
            'delete_missions',
            'generate_missions_pdf',
        ])->where('guard_name', $guard)->get();

        $superadmin?->givePermissionTo($all);
        $dbcgoq?->givePermissionTo($all);
        $chef?->givePermissionTo(Permission::whereIn('name', [
            'view_missions',
            'create_missions',
            'edit_missions',
            'generate_missions_pdf',
        ])->where('guard_name', $guard)->get());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['superadmin', 'dbcgoq', 'chef'] as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
            if ($role) {
                $role->revokePermissionTo(Permission::whereIn('name', [
                    'view_missions',
                    'create_missions',
                    'edit_missions',
                    'delete_missions',
                    'generate_missions_pdf',
                ])->where('guard_name', $guard)->get());
            }
        }

        Permission::whereIn('name', [
            'view_missions',
            'create_missions',
            'edit_missions',
            'delete_missions',
            'generate_missions_pdf',
        ])->where('guard_name', $guard)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
