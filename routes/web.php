<?php

use App\Http\Controllers\OrderChannelController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaperTypeController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PrintModeController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServicePriceController;
use App\Http\Controllers\ServiceTypeController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function (): RedirectResponse {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
})->name('home');

Route::get('/up', fn () => response()->json(['status' => 'ok']))->name('health');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('permission:users.manage')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
    });

    Route::middleware('permission:roles.manage')->group(function () {
        Route::resource('roles', RoleController::class)->except('show');
    });

    Route::middleware('permission:master-data.manage')->prefix('master-data')->group(function () {
        Route::resource('categories', ProductCategoryController::class)->except('show');
        Route::resource('units', UnitController::class)->except('show');
        Route::resource('products', ProductController::class)->except('show');
        Route::resource('paper-types', PaperTypeController::class)->except('show');
        Route::resource('service-types', ServiceTypeController::class)->except('show');
        Route::resource('print-modes', PrintModeController::class)->except('show');
        Route::resource('service-prices', ServicePriceController::class)->except('show');
        Route::resource('order-channels', OrderChannelController::class)->except('show');
        Route::resource('payment-methods', PaymentMethodController::class)->except('show');
    });

    Route::get('orders', [OrderController::class, 'index'])->middleware('permission:orders.view')->name('orders.index');
    Route::get('orders/create', [OrderController::class, 'create'])->middleware('permission:orders.create')->name('orders.create');
    Route::post('orders', [OrderController::class, 'store'])->middleware('permission:orders.create')->name('orders.store');
    Route::get('orders/{order}', [OrderController::class, 'show'])->middleware('permission:orders.view')->name('orders.show');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->middleware('permission:orders.update|orders.cancel')->name('orders.status.update');
});

require __DIR__.'/auth.php';
