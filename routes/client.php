<?php

use App\Http\Controllers\Client\AuthenticatedSessionController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\MessageController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Client\PasswordController;
use App\Http\Controllers\Client\ReturnController;
use Illuminate\Support\Facades\Route;

Route::prefix('client')->name('client.')->group(function () {
    Route::middleware('guest:client')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);
    });

    Route::middleware('auth:client')->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('password/change', [PasswordController::class, 'edit'])->name('password.edit');
        Route::put('password/change', [PasswordController::class, 'update'])->name('password.update');

        Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
        Route::post('messages', [MessageController::class, 'store'])->name('messages.store');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('orders/{order}/feedback', [OrderController::class, 'feedback'])->name('orders.feedback');

        Route::get('returns', [ReturnController::class, 'index'])->name('returns.index');
        Route::get('returns/create/{order}', [ReturnController::class, 'create'])->name('returns.create');
        Route::post('returns/{order}', [ReturnController::class, 'store'])->name('returns.store');
    });
});
