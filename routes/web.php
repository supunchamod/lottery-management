<?php

use App\Http\Controllers\DailyRecordController;
use App\Http\Controllers\DailySummaryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\WinningController;
use Illuminate\Support\Facades\Route;

// ── Public landing ────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('dashboard'));

// ═════════════════════════════════════════════════════════════════════════════
// Blade UI routes  (no auth guard in dev — add ['auth'] middleware in production)
// ═════════════════════════════════════════════════════════════════════════════
Route::group([], function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Daily Sales (Blade UI + form handler)
    Route::get ('/daily-sales',  [PageController::class, 'dailySalesIndex'])->name('daily-sales.index');
    Route::post('/daily-sales',  [PageController::class, 'dailySalesStore'])->name('daily-records.store');

    // Winnings (Blade UI)
    Route::get ('/winnings',         [PageController::class, 'winningsIndex'])->name('winnings.index');
    Route::get ('/winnings/create',  [PageController::class, 'winningsCreate'])->name('winnings.create');
    Route::post('/winnings',         [PageController::class, 'winningsStore'])->name('winnings.store');

    // Expenses
    Route::get ('/expenses',  [PageController::class, 'expensesIndex'])->name('expenses.index');
    Route::post('/expenses',  [PageController::class, 'expensesStore'])->name('expenses.store');

    // Cheques
    Route::get   ('/cheques',          [PageController::class, 'chequesIndex'])->name('cheques.index');
    Route::get   ('/cheques/create',   [PageController::class, 'chequesCreate'])->name('cheques.create');
    Route::patch ('/cheques/{cheque}/clear', [PageController::class, 'chequesClear'])->name('cheques.clear');

    // Sales Assistants
    Route::get ('/assistants',                     [PageController::class, 'assistantsIndex'])->name('assistants.index');
    Route::get ('/assistants/create',              [PageController::class, 'assistantsCreate'])->name('assistants.create');
    Route::post('/assistants',                     [PageController::class, 'assistantsStore'])->name('assistants.store');
    Route::get ('/assistants/{assistant}/edit',    [PageController::class, 'assistantsEdit'])->name('assistants.edit');
    Route::get ('/assistants/{assistant}/ledger',  [PageController::class, 'assistantsLedger'])->name('assistants.ledger');

    // Stock
    Route::get('/stock',        [PageController::class, 'stockIndex'])->name('stock.index');
    Route::get('/stock/create', [PageController::class, 'stockCreate'])->name('stock.create');

    // Reports
    Route::get('/reports', [PageController::class, 'reportsIndex'])->name('reports.index');
});

// ═════════════════════════════════════════════════════════════════════════════
// JSON API routes  (for AJAX / future mobile client)
// ═════════════════════════════════════════════════════════════════════════════
Route::prefix('api')->name('api.')->group(function () {

    // Daily P&L summary
    Route::get('/daily-summary',       [DailySummaryController::class, 'show'])->name('daily-summary');
    Route::get('/daily-summary/range', [DailySummaryController::class, 'range'])->name('daily-summary.range');

    // Daily records CRUD
    Route::get ('/daily-records',              [DailyRecordController::class, 'index'])->name('daily-records.index');
    Route::post('/daily-records',              [DailyRecordController::class, 'store'])->name('daily-records.store');
    Route::get ('/daily-records/{dailyRecord}',[DailyRecordController::class, 'show'])->name('daily-records.show');
    Route::put ('/daily-records/{dailyRecord}',[DailyRecordController::class, 'update'])->name('daily-records.update');

    // Winning calculator
    Route::post('/winnings/calculate', [WinningController::class, 'calculate'])->name('winnings.calculate');
    Route::post('/winnings',           [WinningController::class, 'store'])->name('winnings.store');
    Route::get ('/winnings/{date}',    [WinningController::class, 'showByDate'])->name('winnings.show');
});
