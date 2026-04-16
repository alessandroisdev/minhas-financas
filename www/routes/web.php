<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/stream', [StreamController::class, 'subscribe'])->name('stream')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [FinanceController::class, 'index'])->name('dashboard');
    Route::post('/bank/import', [FinanceController::class, 'importStore'])->name('bank.import');
    Route::get('/bank/reconciliation/{import}', [FinanceController::class, 'showReconciliation'])->name('bank.reconciliation');
    Route::post('/bank/reconciliation/{bankTransaction}/manual', [FinanceController::class, 'manualMatch'])->name('bank.reconciliation.manual');

    // Categorias
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

    // Transacoes
    Route::resource('transactions', TransactionController::class)->except(['create', 'show', 'edit', 'update']);
    Route::post('transactions/{transaction}/pay', [TransactionController::class, 'markAsPaid'])->name('transactions.pay');
});

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request')->middleware('guest');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('guest');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update')->middleware('guest');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard')->middleware('auth');
