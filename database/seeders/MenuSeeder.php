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
            ['name' => 'Panel de administración'],
            [
                'icon' => 'LayoutDashboard',
                'order' => 1,
                'route' => 'admin.dashboard',
                'is_submenu' => false,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Carreras
        |--------------------------------------------------------------------------
        */

        $races = Menu::updateOrCreate(
            ['name' => 'Carreras'],
            [
                'icon' => 'Trophy',
                'order' => 2,
                'route' => null,
                'is_submenu' => true,
            ]
        );

        $this->link(
            $races,
            'Listado de carreras',
            'CalendarDays',
            1,
            'admin.races.index',
            'races.view'
        );

        /*
        |--------------------------------------------------------------------------
        | Rutas
        |--------------------------------------------------------------------------
        */

        $routes = Menu::updateOrCreate(
            ['name' => 'Rutas'],
            [
                'icon' => 'Route',
                'order' => 3,
                'route' => null,
                'is_submenu' => true,
            ]
        );

        $this->link(
            $routes,
            'Listado de rutas',
            'Map',
            1,
            'admin.routes.index',
            'routes.view'
        );

        /*
        |--------------------------------------------------------------------------
        | Patrocinadores
        |--------------------------------------------------------------------------
        */

        $sponsors = Menu::updateOrCreate(
            ['name' => 'Patrocinadores'],
            [
                'icon' => 'Handshake',
                'order' => 4,
                'route' => null,
                'is_submenu' => true,
            ]
        );

        $this->link(
            $sponsors,
            'Listado de patrocinadores',
            'Building2',
            1,
            'admin.sponsors.index',
            'sponsors.view'
        );

        /*
        |--------------------------------------------------------------------------
        | Clubes
        |--------------------------------------------------------------------------
        */

        $clubs = Menu::updateOrCreate(
            ['name' => 'Clubes'],
            [
                'icon' => 'UsersRound',
                'order' => 5,
                'route' => null,
                'is_submenu' => true,
            ]
        );

        $this->link(
            $clubs,
            'Listado de clubes',
            'UsersRound',
            1,
            'admin.clubs.index',
            'clubs.view'
        );

        /*
        |--------------------------------------------------------------------------
        | Sucursales
        |--------------------------------------------------------------------------
        */

        $branches = Menu::updateOrCreate(
            ['name' => 'Sucursales'],
            [
                'icon' => 'Store',
                'order' => 6,
                'route' => null,
                'is_submenu' => true,
            ]
        );

        $this->link(
            $branches,
            'Listado de sucursales',
            'MapPin',
            1,
            'admin.branches.index',
            'branches.view'
        );

        /*
        |--------------------------------------------------------------------------
        | Venta
        |--------------------------------------------------------------------------
        */

        $sales = Menu::updateOrCreate(
            ['name' => 'Venta'],
            [
                'icon' => 'ShoppingCart',
                'order' => 7,
                'route' => null,
                'is_submenu' => true,
            ]
        );

        $this->link(
            $sales,
            'Ventas',
            'Receipt',
            1,
            'admin.sales.index',
            'sales.view'
        );

        $this->link(
            $sales,
            'Productos',
            'Package',
            2,
            'admin.products.index',
            'products.view'
        );

        $this->link(
            $sales,
            'Métodos de pago',
            'CreditCard',
            3,
            'admin.payment-methods.index',
            'payment-methods.view'
        );

        /*
        |--------------------------------------------------------------------------
        | Banners
        |--------------------------------------------------------------------------
        */

        $banners = Menu::updateOrCreate(
            ['name' => 'Banners'],
            [
                'icon' => 'Images',
                'order' => 8,
                'route' => null,
                'is_submenu' => true,
            ]
        );

        $this->link(
            $banners,
            'Listado de banners',
            'Image',
            1,
            'admin.banners.index',
            'banners.view'
        );

        /*
        |--------------------------------------------------------------------------
        | Mi cuenta
        |--------------------------------------------------------------------------
        |
        | Se muestra para usuarios que tengan orders.view.
        |
        */

        $account = Menu::updateOrCreate(
            ['name' => 'Mi cuenta'],
            [
                'icon' => 'CircleUserRound',
                'order' => 9,
                'route' => null,
                'is_submenu' => true,
            ]
        );

        $this->link(
            $account,
            'Mis pedidos',
            'ShoppingBag',
            1,
            'customer.orders.index',
            'orders.view'
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
                'order' => 10,
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
            ->where('guard_name', 'web')
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