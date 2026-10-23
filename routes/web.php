<?php

use App\Http\Controllers\BankController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DdPaymentController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShoppingGroupController;
use App\Http\Controllers\ShoppingTypeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('receipts', ReceiptController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('shops', ShopController::class)
        ->only(['index', 'store']);

    Route::resource('shopping_types', ShoppingTypeController::class)
        ->only(['index', 'store']);

    Route::resource('shopping_groups', ShoppingGroupController::class)
        ->only(['index', 'store']);

    Route::resource('payment_methods', PaymentMethodController::class)
        ->only(['index', 'store']);

    Route::resource('dd_payments', DdPaymentController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('/plan', [PlanController::class, 'index'])->name('plan.index');
    Route::post('/plan/budgets', [PlanController::class, 'storeBudget'])->name('plan.budgets.store');
    Route::delete('/plan/budgets/{budget}', [PlanController::class, 'destroyBudget'])->name('plan.budgets.destroy');
    Route::post('/plan/incomes', [PlanController::class, 'storeIncome'])->name('plan.incomes.store');
    Route::put('/plan/incomes/{income}', [PlanController::class, 'updateIncome'])->name('plan.incomes.update');
    Route::delete('/plan/incomes/{income}', [PlanController::class, 'destroyIncome'])->name('plan.incomes.destroy');

    Route::get('/imports', [ImportController::class, 'index'])->name('imports.index');
    Route::post('/imports/preview', [ImportController::class, 'preview'])->name('imports.preview');
    Route::post('/imports', [ImportController::class, 'store'])->name('imports.store');

    Route::get('/bank', [BankController::class, 'index'])->name('bank.index');
    Route::post('/bank/connect', [BankController::class, 'connect'])->name('bank.connect');
    Route::get('/bank/callback', [BankController::class, 'callback'])->name('bank.callback');
    Route::post('/bank/sync', [BankController::class, 'sync'])->name('bank.sync');
    Route::delete('/bank', [BankController::class, 'disconnect'])->name('bank.disconnect');
});

require __DIR__.'/auth.php';
