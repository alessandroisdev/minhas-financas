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

    // Módulo Arquivológico
    Route::resource('documents', \App\Http\Controllers\DocumentController::class)->only(['index', 'store', 'destroy']);
    Route::post('documents/{document}/download', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
    Route::resource('document_folders', \App\Http\Controllers\DocumentFolderController::class)->only(['store', 'destroy']);

    // Transacoes e Conciliação
    Route::resource('transactions', TransactionController::class)->except(['create', 'show', 'edit', 'update']);
    Route::post('transactions/{transaction}/pay', [TransactionController::class, 'markAsPaid'])->name('transactions.pay');
    
    Route::post('bank-import', [\App\Http\Controllers\BankImportController::class, 'store'])->name('bank-import.store');

    // Arena Kanban de Conciliação OFX
    Route::get('reconciliation', [\App\Http\Controllers\BankReconciliationController::class, 'index'])->name('reconciliation.index');
    Route::post('reconciliation/fast-create', [\App\Http\Controllers\BankReconciliationController::class, 'fastCreate'])->name('reconciliation.fastCreate');
    Route::post('reconciliation/match', [\App\Http\Controllers\BankReconciliationController::class, 'match'])->name('reconciliation.match');

    // Fechamento Contabil (ZIP Export)
    Route::post('export-accounting', [\App\Http\Controllers\AccountingExportController::class, 'exportZip'])->name('accounting.export');

    // Cartões de Crédito e Faturas (Upgrade 4.0)
    Route::resource('credit-cards', \App\Http\Controllers\CreditCardController::class)->except(['create', 'edit', 'update', 'destroy']);
    Route::post('credit-cards/{id}/transactions', [\App\Http\Controllers\CreditCardController::class, 'storeTransaction'])->name('credit-cards.storeTransaction');
    Route::post('credit-cards/{id}/pay', [\App\Http\Controllers\CreditCardController::class, 'payInvoice'])->name('credit-cards.payInvoice');
});

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request')->middleware('guest');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('guest');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update')->middleware('guest');

    // Fim das rotas protegidas
