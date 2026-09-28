<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $superadmin = Role::firstOrCreate([
            'name' => 'SuperAdmin',
            'guard_name' => 'web',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $staff = Role::firstOrCreate([
            'name' => 'staff',
            'guard_name' => 'web',
        ]);

        $visitor = Role::firstOrCreate([
            'name' => 'customer',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Permisos
        |--------------------------------------------------------------------------
        */

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Usuarios
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Roles
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Permisos
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',

            // Métodos de pago
            'payment-methods.view',
            'payment-methods.create',
            'payment-methods.edit',
            'payment-methods.delete',

            // Carreras
            'races.view',
            'races.create',
            'races.edit',
            'races.delete',

            // Patrocinadores
            'sponsors.view',
            'sponsors.create',
            'sponsors.edit',
            'sponsors.delete',
        ];

        /*
        |--------------------------------------------------------------------------
        | Crear permisos
        |--------------------------------------------------------------------------
        */

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SuperAdmin
        |--------------------------------------------------------------------------
        |
        | SuperAdmin obtiene acceso total mediante Gate::before().
        |
        */

        $superadmin->syncPermissions([]);

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        |
        | Acceso completo a todos los módulos administrativos.
        |
        */

        $admin->syncPermissions($permissions);

        /*
        |--------------------------------------------------------------------------
        | Staff
        |--------------------------------------------------------------------------
        |
        | Puede consultar y operar carreras, checklist, egresos
        | y patrocinadores.
        |
        | Checklist y egresos utilizan races.view y races.edit.
        | Las operaciones de patrocinadores utilizan sponsors.view
        | y sponsors.edit.
        |
        */

        $staffPermissions = [
            // Consultar dashboard
            'dashboard.view',

            // Carreras y operaciones relacionadas
            'races.view',
            'races.edit',

            // Patrocinadores y operaciones relacionadas
            'sponsors.view',
            'sponsors.edit',
        ];

        $staff->syncPermissions($staffPermissions);

        /*
        |--------------------------------------------------------------------------
        | Customer
        |--------------------------------------------------------------------------
        |
        | No tiene permisos administrativos.
        |
        */

        $visitor->syncPermissions([]);

        // Limpiar caché nuevamente
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}