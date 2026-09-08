<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReportController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Transaksi
Route::prefix('transactions')->name('transactions.')->group(function () {
    Route::get('/in', [TransactionController::class, 'stockIn'])->name('in');
    Route::post('/in', [TransactionController::class, 'storeStockIn'])->name('in.store');
    
    Route::get('/out', [TransactionController::class, 'stockOut'])->name('out');
    Route::post('/out', [TransactionController::class, 'storeStockOut'])->name('out.store');
});

// Stok & Laporan
Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
Route::post('/stock/{product}/toggle-active', [StockController::class, 'toggleActive'])->name('stock.toggle-active');
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

