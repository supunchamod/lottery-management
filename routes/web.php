<?php

use App\Http\Controllers\DailyRecordController;
use App\Http\Controllers\DailySalesController;
use App\Http\Controllers\DailySummaryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketDistributionController;
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

    // Daily Sales — new express-edition grid
    Route::get ('/daily-sales',          [DailySalesController::class, 'index'])->name('daily-sales.index');
    Route::post('/daily-sales',          [DailySalesController::class, 'store'])->name('daily-sales.store');
    Route::get ('/daily-sales/analysis', [DailySalesController::class, 'analysis'])->name('daily-sales.analysis');

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
    Route::get  ('/assistants/{assistant}/edit',    [PageController::class, 'assistantsEdit'])->name('assistants.edit');
    Route::put  ('/assistants/{assistant}',         [PageController::class, 'assistantsUpdate'])->name('assistants.update');
    Route::get  ('/assistants/{assistant}/ledger',  [PageController::class, 'assistantsLedger'])->name('assistants.ledger');

    // Lotteries
    Route::get ('/lotteries',              [PageController::class, 'lotteriesIndex'])->name('lotteries.index');
    Route::get ('/lotteries/create',       [PageController::class, 'lotteriesCreate'])->name('lotteries.create');
    Route::post('/lotteries',              [PageController::class, 'lotteriesStore'])->name('lotteries.store');
    Route::get ('/lotteries/{lottery}/edit', [PageController::class, 'lotteriesEdit'])->name('lotteries.edit');
    Route::put ('/lotteries/{lottery}',    [PageController::class, 'lotteriesUpdate'])->name('lotteries.update');

    // Stock
    Route::get ('/stock',        [PageController::class, 'stockIndex'])->name('stock.index');
    Route::get ('/stock/create', [PageController::class, 'stockCreate'])->name('stock.create');
    Route::post('/stock',        [PageController::class, 'stockStore'])->name('stock.store');

    // ── Ticket Distribution ────────────────────────────────────────────────────
    Route::get ('/ticket-distribution',          [TicketDistributionController::class, 'index'])->name('ticket-distribution.index');
    Route::post('/ticket-distribution',          [TicketDistributionController::class, 'store'])->name('ticket-distribution.store');
    Route::get ('/ticket-distribution/summary',  [TicketDistributionController::class, 'summary'])->name('ticket-distribution.summary');

    // Sub-sellers CRUD (nested under an assistant)
    Route::get   ('/ticket-distribution/{assistant}/sub-sellers',         [TicketDistributionController::class, 'subSellersIndex'])->name('ticket-distribution.sub-sellers.index');
    Route::post  ('/ticket-distribution/sub-sellers',                     [TicketDistributionController::class, 'subSellersStore'])->name('ticket-distribution.sub-sellers.store');
    Route::put   ('/ticket-distribution/sub-sellers/{subSeller}',         [TicketDistributionController::class, 'subSellersUpdate'])->name('ticket-distribution.sub-sellers.update');
    Route::delete('/ticket-distribution/sub-sellers/{subSeller}/destroy', [TicketDistributionController::class, 'subSellersDestroy'])->name('ticket-distribution.sub-sellers.destroy');

    // ── Reports (advanced filter + assistant performance + PDF exports) ─────────
    Route::get('/reports',             [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/assistants',  [ReportController::class, 'assistantPerformance'])->name('reports.assistants');
    Route::get('/reports/pdf/daily-sales', [ReportController::class, 'pdfDailySales'])->name('reports.pdf.daily-sales');
    Route::get('/reports/pdf/ledger/{assistant}', [ReportController::class, 'pdfLedger'])->name('reports.pdf.ledger');
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
