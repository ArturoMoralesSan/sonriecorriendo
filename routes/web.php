<?php

use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RaceChecklistController;
use App\Http\Controllers\Admin\RaceController;
use App\Http\Controllers\Admin\RaceExpenseController;
use App\Http\Controllers\Admin\RaceGalleryController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaleController as AdminSaleController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\SponsorPaymentController;
use App\Http\Controllers\Admin\SponsorRaceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\RouteController;
use App\Http\Controllers\Customer\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Público
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

/*
|--------------------------------------------------------------------------
| Carreras
|--------------------------------------------------------------------------
*/

Route::get('/carreras', [HomeController::class, 'races'])
    ->name('races.index');

Route::get('/carreras/{slug}', [HomeController::class, 'race'])
    ->name('races.show');

/*
|--------------------------------------------------------------------------
| Galería
|--------------------------------------------------------------------------
*/

Route::get('/galeria', [HomeController::class, 'galleries'])
    ->name('gallery.index');

Route::get('/galeria/{slug}', [HomeController::class, 'gallery'])
    ->name('gallery.show');

/*
|--------------------------------------------------------------------------
| Tienda
|--------------------------------------------------------------------------
*/

Route::get('/tienda', [HomeController::class, 'shop'])
    ->name('shop.index');

Route::get('/productos/{slug}', [HomeController::class, 'product'])
    ->name('products.show');

/*
|--------------------------------------------------------------------------
| Clubes
|--------------------------------------------------------------------------
*/

Route::get('/clubes', [HomeController::class, 'clubs'])
    ->name('clubs.index');

Route::get('/clubes/{slug}', [HomeController::class, 'club'])
    ->name('clubs.show');




Route::get('/rutas', [HomeController::class, 'routes'])
    ->name('routes.index');

Route::get('/rutas/{route}', [HomeController::class, 'route'])
    ->name('routes.show');
/*
|--------------------------------------------------------------------------
| Carrito
|--------------------------------------------------------------------------
*/

Route::get('/carrito', [CartController::class, 'index'])
    ->name('cart.index');

Route::post('/carrito/agregar', [CartController::class, 'add'])
    ->name('cart.add');

Route::patch('/carrito/actualizar', [CartController::class, 'update'])
    ->name('cart.update');

Route::delete('/carrito/eliminar', [CartController::class, 'remove'])
    ->name('cart.remove');

Route::delete('/carrito/vaciar', [CartController::class, 'clear'])
    ->name('cart.clear');

/*
|--------------------------------------------------------------------------
| Pedidos / Ventas de tienda
|--------------------------------------------------------------------------
*/

Route::post('/carrito/finalizar', [SaleController::class, 'checkout'])
    ->name('sales.checkout');

Route::get('/ventas/{sale}', [SaleController::class, 'show'])
    ->name('sales.show');



Route::post(
    '/mercadopago/webhook',
    [MercadoPagoController::class, 'webhook']
)->name('mercadopago.webhook');


Route::get('/pedidos', [OrderController::class, 'index'])
    ->middleware([
        'auth',
        'permission:orders.view',
    ])
    ->name('customer.orders.index');
    
Route::get('/pedidos/{sale}', [OrderController::class, 'show'])
    ->middleware([
        'auth',
        'permission:orders.view',
    ])
    ->name('customer.orders.show');
/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('permission:dashboard.view');

        /*
        |--------------------------------------------------------------------------
        | Usuarios
        |--------------------------------------------------------------------------
        */

        Route::resource('users', UserController::class)
            ->middleware([
                'index' => 'permission:users.view',
                'create' => 'permission:users.create',
                'store' => 'permission:users.create',
                'edit' => 'permission:users.edit',
                'update' => 'permission:users.edit',
                'destroy' => 'permission:users.delete',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        Route::resource('roles', RoleController::class)
            ->middleware([
                'index' => 'permission:roles.view',
                'create' => 'permission:roles.create',
                'store' => 'permission:roles.create',
                'edit' => 'permission:roles.edit',
                'update' => 'permission:roles.edit',
                'destroy' => 'permission:roles.delete',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Permisos
        |--------------------------------------------------------------------------
        */

        Route::resource('permissions', PermissionController::class)
            ->middleware([
                'index' => 'permission:permissions.view',
                'create' => 'permission:permissions.create',
                'store' => 'permission:permissions.create',
                'edit' => 'permission:permissions.edit',
                'update' => 'permission:permissions.edit',
                'destroy' => 'permission:permissions.delete',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Métodos de pago
        |--------------------------------------------------------------------------
        */

        Route::resource('payment-methods', PaymentMethodController::class)
            ->except(['show'])
            ->middleware([
                'index' => 'permission:payment-methods.view',
                'create' => 'permission:payment-methods.create',
                'store' => 'permission:payment-methods.create',
                'edit' => 'permission:payment-methods.edit',
                'update' => 'permission:payment-methods.edit',
                'destroy' => 'permission:payment-methods.delete',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Carreras
        |--------------------------------------------------------------------------
        */

        Route::resource('races', RaceController::class)
            ->middleware([
                'index' => 'permission:races.view',
                'create' => 'permission:races.create',
                'store' => 'permission:races.create',
                'show' => 'permission:races.view',
                'edit' => 'permission:races.edit',
                'update' => 'permission:races.edit',
                'destroy' => 'permission:races.delete',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Sponsors
        |--------------------------------------------------------------------------
        */

        Route::resource('sponsors', SponsorController::class)
            ->middleware([
                'index' => 'permission:sponsors.view',
                'create' => 'permission:sponsors.create',
                'store' => 'permission:sponsors.create',
                'show' => 'permission:sponsors.view',
                'edit' => 'permission:sponsors.edit',
                'update' => 'permission:sponsors.edit',
                'destroy' => 'permission:sponsors.delete',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Sponsor - Carreras
        |--------------------------------------------------------------------------
        */

        Route::get(
            'sponsors/{sponsor}/races/create',
            [SponsorRaceController::class, 'create']
        )
            ->name('sponsors.races.create')
            ->middleware('permission:sponsors.edit');

        Route::post(
            'sponsors/{sponsor}/races',
            [SponsorRaceController::class, 'store']
        )
            ->name('sponsors.races.store')
            ->middleware('permission:sponsors.edit');

        Route::get(
            'sponsors/{sponsor}/races/{raceSponsor}',
            [SponsorRaceController::class, 'show']
        )
            ->name('sponsors.races.show')
            ->middleware('permission:sponsors.view');

        Route::delete(
            'sponsors/{sponsor}/races/{raceSponsor}',
            [SponsorRaceController::class, 'destroy']
        )
            ->name('sponsors.races.destroy')
            ->middleware('permission:sponsors.edit');

        /*
        |--------------------------------------------------------------------------
        | Sponsor - Pagos
        |--------------------------------------------------------------------------
        */

        Route::post(
            'race-sponsors/{raceSponsor}/payments',
            [SponsorPaymentController::class, 'store']
        )
            ->name('race-sponsors.payments.store')
            ->middleware('permission:sponsors.edit');

        Route::delete(
            'race-sponsors/{raceSponsor}/payments/{payment}',
            [SponsorPaymentController::class, 'destroy']
        )
            ->name('race-sponsors.payments.destroy')
            ->middleware('permission:sponsors.edit');

        /*
        |--------------------------------------------------------------------------
        | Checklist de carreras
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'races.checklist',
            RaceChecklistController::class
        )
            ->only([
                'index',
                'store',
                'update',
                'destroy',
            ])
            ->parameters([
                'checklist' => 'checklistItem',
            ])
            ->middleware([
                'index' => 'permission:races.view',
                'store' => 'permission:races.edit',
                'update' => 'permission:races.edit',
                'destroy' => 'permission:races.edit',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Checklist - Cambiar estado
        |--------------------------------------------------------------------------
        */

        Route::patch(
            'races/{race}/checklist/{checklistItem}/status',
            [RaceChecklistController::class, 'updateStatus']
        )
            ->name('races.checklist.status')
            ->middleware('permission:races.edit');

        /*
        |--------------------------------------------------------------------------
        | Checklist - Aplicar plantilla
        |--------------------------------------------------------------------------
        */

        Route::post(
            'races/{race}/checklist/apply-template',
            [RaceChecklistController::class, 'applyTemplate']
        )
            ->name('races.checklist.apply-template')
            ->middleware('permission:races.edit');

        /*
        |--------------------------------------------------------------------------
        | Egresos de carreras
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'races.expenses',
            RaceExpenseController::class
        )
            ->only([
                'index',
                'store',
                'update',
                'destroy',
            ])
            ->parameters([
                'expenses' => 'expense',
            ])
            ->middleware([
                'index' => 'permission:races.view',
                'store' => 'permission:races.edit',
                'update' => 'permission:races.edit',
                'destroy' => 'permission:races.edit',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Galería de carreras
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'races.gallery',
            RaceGalleryController::class
        )
            ->only([
                'index',
                'store',
                'update',
                'destroy',
            ])
            ->parameters([
                'gallery' => 'gallery',
            ])
            ->middleware([
                'index' => 'permission:races.view',
                'store' => 'permission:races.edit',
                'update' => 'permission:races.edit',
                'destroy' => 'permission:races.edit',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Clubes
        |--------------------------------------------------------------------------
        */

        Route::resource('clubs', ClubController::class)
            ->middleware([
                'index' => 'permission:clubs.view',
                'create' => 'permission:clubs.create',
                'store' => 'permission:clubs.create',
                'edit' => 'permission:clubs.edit',
                'update' => 'permission:clubs.edit',
                'destroy' => 'permission:clubs.delete',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Productos
        |--------------------------------------------------------------------------
        */

        Route::resource('products', ProductController::class)
            ->middleware([
                'index' => 'permission:products.view',
                'create' => 'permission:products.create',
                'store' => 'permission:products.create',
                'show' => 'permission:products.view',
                'edit' => 'permission:products.edit',
                'update' => 'permission:products.edit',
                'destroy' => 'permission:products.delete',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Ventas - Cliente por QR
        |--------------------------------------------------------------------------
        */

        Route::post(
            'sales/customer-by-qr',
            [AdminSaleController::class, 'customerByQr']
        )
            ->name('sales.customer-by-qr');

        /*
        |--------------------------------------------------------------------------
        | Ventas
        |--------------------------------------------------------------------------
        */

        Route::resource('sales', AdminSaleController::class)
            ->middleware([
                'index' => 'permission:sales.view',
                'create' => 'permission:sales.create',
                'store' => 'permission:sales.create',
                'show' => 'permission:sales.view',
                'edit' => 'permission:sales.edit',
                'update' => 'permission:sales.edit',
                'destroy' => 'permission:sales.delete',
            ]);

        Route::resource('banners', BannerController::class)
            ->middleware([
                'index' => 'permission:banners.view',
                'create' => 'permission:banners.create',
                'store' => 'permission:banners.create',
                'show' => 'permission:banners.view',
                'edit' => 'permission:banners.edit',
                'update' => 'permission:banners.edit',
                'destroy' => 'permission:banners.delete',
            ]);
        
       
        Route::resource('routes', RouteController::class)
            ->middleware([
                'index' => 'permission:routes.view',
                'create' => 'permission:routes.create',
                'store' => 'permission:routes.create',
                'show' => 'permission:routes.view',
                'edit' => 'permission:routes.edit',
                'update' => 'permission:routes.edit',
                'destroy' => 'permission:routes.delete',
            ]);
    });

require __DIR__.'/settings.php';
