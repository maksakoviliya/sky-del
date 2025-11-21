<?php

use App\Http\Controllers\ActController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/sanctum/token', TokenController::class);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users/auth', AuthController::class);

    // Companies
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');

    // Recipients
    Route::get('/recipients', [RecipientController::class, 'index'])->name('recipients.index');
    Route::get('/recipientsForUser/{user}', [RecipientController::class, 'recipientsForUser'])->name('recipients.forUser');

    // Orders
	Route::post('/orders/export-selected-orders', [OrderController::class, 'export'])
		->middleware(['admin'])
		->name('orders.export');
	
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.create');
    Route::post('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::get('/ordersAnalytics', [OrderController::class, 'fetchOrdersAnalytics'])->name('orders.fetchOrdersAnalytics');

    // Acts
    Route::get('/acts', [ActController::class, 'index'])->name('acts.index');
    Route::post('/acts', [ActController::class, 'store'])->name('acts.store');
    Route::delete('/acts/{act}', [ActController::class, 'destroy'])->name('acts.destroy');
    Route::get('/acts/download/{act}', [ActController::class, 'download'])->name('acts.download');

    Route::middleware(['admin'])->group(function() {
        // Clients
        Route::get('/clients', [UserController::class, 'index'])->name('clients.index');
        Route::get('/allClients', [UserController::class, 'allClients'])->name('clients.allClients');
        Route::post('/clients/export-orders', [UserController::class, 'exportOrders'])->name('clients.exportOrders');
        Route::get('/clients/{user}', [UserController::class, 'show'])->name('clients.show');
        Route::post('/clients', [UserController::class, 'store'])->name('clients.store');
        Route::post('/clients/{user}', [UserController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{user}', [UserController::class, 'destroy'])->name('clients.destroy');

        // Orders
        Route::post('/setOrderStatus/{order}', [OrderController::class, 'setOrderStatus'])->name('orders.setOrderStatus');
        Route::post('/setOrderCourier/{order}', [OrderController::class, 'setOrderCourier'])->name('orders.setOrderCourier');
        Route::post('/setOrderPayment/{order}', [OrderController::class, 'setOrderPayment'])->name('orders.setOrderPayment');
        Route::post('/ordersPay', [OrderController::class, 'ordersPay'])->name('orders.ordersPay');

        // Couriers
        Route::get('/couriers', [CourierController::class, 'index'])->name('couriers.index');
        Route::get('/couriers/{courier}', [CourierController::class, 'show'])->name('couriers.show');
        Route::post('/couriers', [CourierController::class, 'store'])->name('couriers.store');
        Route::post('/couriers/{courier}', [CourierController::class, 'update'])->name('couriers.update');
        Route::delete('/couriers/{courier}', [CourierController::class, 'destroy'])->name('couriers.destroy');
    });
});
