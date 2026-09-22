<?php

namespace Database\Seeders;

use App\Models\Link;
use App\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Menu::updateOrCreate(
            ['name' => 'Dashboard'],
            [
                'icon' => 'LayoutDashboard',
                'order' => 1,
                'route' => 'admin.dashboard',
                'is_submenu' => false,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Zoológico
        |--------------------------------------------------------------------------
        */

       

        /*
        |--------------------------------------------------------------------------
        | Venta
        |--------------------------------------------------------------------------
        */

        $ticketOffice = Menu::updateOrCreate(
            ['name' => 'Venta'],
            [
                'icon' => 'Ticket',
                'order' => 3,
                'route' => null,
                'is_submenu' => true,
            ]
        );


        $this->link(
            $ticketOffice,
            'Métodos de pago',
            'CreditCard',
            2,
            'admin.payment-methods.index',
            'payment-methods.view'
        );

        
        

        /*
        |--------------------------------------------------------------------------
        | Administración
        |--------------------------------------------------------------------------
        */

        $administration = Menu::updateOrCreate(
            ['name' => 'Administración'],
            [
                'icon' => 'Settings',
                'order' => 6,
                'route' => null,
                'is_submenu' => true,
            ]
        );

        $this->link(
            $administration,
            'Usuarios',
            'Users',
            1,
            'admin.users.index',
            'users.view'
        );

        $this->link(
            $administration,
            'Roles',
            'Shield',
            2,
            'admin.roles.index',
            'roles.view'
        );

        $this->link(
            $administration,
            'Permisos',
            'KeyRound',
            3,
            'admin.permissions.index',
            'permissions.view'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Crear o actualizar un enlace de menú
    |--------------------------------------------------------------------------
    */

    private function link(
        Menu $menu,
        string $name,
        string $icon,
        int $order,
        ?string $route,
        string $permission
    ): void {
        $permissionModel = Permission::where(
            'name',
            $permission
        )
            ->where(
                'guard_name',
                'web'
            )
            ->first();

        Link::updateOrCreate(
            [
                'menu_id' => $menu->id,
                'name' => $name,
            ],
            [
                'icon' => $icon,
                'order' => $order,
                'route' => $route,
                'permission_id' => $permissionModel?->id,
            ]
        );
    }
}
