<?php

use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Hotspot\AuthController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public Routes
Route::get('tariffs', [\App\Http\Controllers\PublicController::class, 'tariffs'])->name('public.tariffs');

// Admin Routes (Protected)
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Route::resource('tariffs', TariffController::class);
    Route::get('vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::post('vouchers/generate', [VoucherController::class, 'generate'])->name('vouchers.generate');
    Route::get('vouchers/{voucher}', [VoucherController::class, 'show'])->name('vouchers.show');

    // Reports
    Route::get('reports/payments', [\App\Http\Controllers\Admin\ReportController::class, 'payments'])->name('reports.payments');
    Route::get('reports/sessions', [\App\Http\Controllers\Admin\ReportController::class, 'sessions'])->name('reports.sessions');
});

// Public Payment Routes
Route::get('payment/{tariff}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('payment/mpesa/initiate', [PaymentController::class, 'initiate'])->name('payment.initiate');
Route::get('payment/mpesa/status/{checkoutId}', [PaymentController::class, 'status'])->name('payment.status');

// M-Pesa Callback (No CSRF protection needed)
Route::post('mpesa/callback', [PaymentController::class, 'callback'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Hotspot Routes
Route::get('hotspot/login', [AuthController::class, 'showLogin'])->name('hotspot.login');
Route::post('hotspot/login', [AuthController::class, 'login'])->name('hotspot.login.submit');
Route::get('hotspot/success', [AuthController::class, 'success'])->name('hotspot.success');

require __DIR__ . '/settings.php';
