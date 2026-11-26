<?php

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavingGoalController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('wallets', WalletController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('budgets', BudgetController::class)->except('show');
    Route::resource('transactions', TransactionController::class);

    Route::get('/transfers/create', [TransferController::class, 'create'])
        ->name('transfers.create');
    Route::post('/transfers', [TransferController::class, 'store'])
        ->name('transfers.store');

    Route::get('/receipts', [ReceiptController::class, 'index'])
        ->name('receipts.index');
    Route::get('/receipts/scan', [ReceiptController::class, 'create'])
        ->name('receipts.create');
    Route::post('/receipts/scan', [ReceiptController::class, 'store'])
        ->name('receipts.store');
    Route::get('/receipts/{receipt}/image', [ReceiptController::class, 'image'])
        ->name('receipts.image');
    Route::post('/receipts/{receipt}/retry', [ReceiptController::class, 'retry'])
        ->name('receipts.retry');
    Route::post('/receipts/{receipt}/confirm', [ReceiptController::class, 'confirm'])
        ->name('receipts.confirm');
    Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])
        ->name('receipts.show');
    Route::delete('/receipts/{receipt}', [ReceiptController::class, 'destroy'])
        ->name('receipts.destroy');

    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');
    Route::get('/reports/export/pdf', [ReportController::class, 'pdf'])
        ->name('reports.pdf');
    Route::get('/reports/export/excel', [ReportController::class, 'excel'])
        ->name('reports.excel');

    Route::resource('saving-goals', SavingGoalController::class)
        ->parameters(['saving-goals' => 'savingGoal']);
    Route::post('/saving-goals/{savingGoal}/contributions', [SavingGoalController::class, 'storeContribution'])
        ->name('saving-goals.contributions.store');
});

require __DIR__.'/auth.php';
