<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BoardSettlementController;
use App\Http\Controllers\BoardTransactionController;
use App\Http\Controllers\BulkDepositController;
use App\Http\Controllers\DailyRecordController;
use App\Http\Controllers\DailySalesController;
use App\Http\Controllers\DailySummaryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketDistributionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WinningController;
use Illuminate\Support\Facades\Route;

// ── Public landing ────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('dashboard'));

// ═════════════════════════════════════════════════════════════════════════════
// Guest-only authentication routes
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.attempt');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

// ═════════════════════════════════════════════════════════════════════════════
// Protected Blade UI routes — require authentication
// ═════════════════════════════════════════════════════════════════════════════
Route::middleware('auth')->group(function () {

    Route::get('/run-migration', function () {
        try {
            Artisan::call('migrate', ['--force' => true]);
            return "Migration Successful: <br><pre>" . Artisan::output() . "</pre>";
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    });

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Daily Sales
    Route::get ('/daily-sales',          [DailySalesController::class, 'index'])->name('daily-sales.index');
    Route::post('/daily-sales',          [DailySalesController::class, 'store'])->name('daily-sales.store');
    Route::post('/daily-sales/entry',    [DailySalesController::class, 'storeSingle'])->name('daily-sales.entry');
    Route::get ('/daily-sales/analysis', [DailySalesController::class, 'analysis'])->name('daily-sales.analysis');

    // Bulk Deposits
    Route::get   ('/bulk-deposits',                           [BulkDepositController::class, 'index'])->name('bulk-deposits.index');
    Route::get   ('/bulk-deposits/create',                    [BulkDepositController::class, 'create'])->name('bulk-deposits.create');
    Route::post  ('/bulk-deposits',                           [BulkDepositController::class, 'store'])->name('bulk-deposits.store');
    Route::get   ('/bulk-deposits/{bulkDeposit}/edit',        [BulkDepositController::class, 'edit'])->name('bulk-deposits.edit');
    Route::put   ('/bulk-deposits/{bulkDeposit}',             [BulkDepositController::class, 'update'])->name('bulk-deposits.update');
    Route::delete('/bulk-deposits/{bulkDeposit}',             [BulkDepositController::class, 'destroy'])->name('bulk-deposits.destroy');
    Route::get   ('/bulk-deposits/{bulkDeposit}/distribute',  [BulkDepositController::class, 'distribute'])->name('bulk-deposits.distribute');
    Route::post  ('/bulk-deposits/{bulkDeposit}/distribute',  [BulkDepositController::class, 'saveDistribution'])->name('bulk-deposits.save-distribution');

    // Winnings
    Route::get ('/winnings',        [PageController::class, 'winningsIndex'])->name('winnings.index');
    Route::get ('/winnings/create', [PageController::class, 'winningsCreate'])->name('winnings.create');
    Route::post('/winnings',        [PageController::class, 'winningsStore'])->name('winnings.store');

    // Expenses
    Route::get ('/expenses', [PageController::class, 'expensesIndex'])->name('expenses.index');
    Route::post('/expenses', [PageController::class, 'expensesStore'])->name('expenses.store');

    // Cheques
    Route::get   ('/cheques',                [PageController::class, 'chequesIndex'])->name('cheques.index');
    Route::get   ('/cheques/create',         [PageController::class, 'chequesCreate'])->name('cheques.create');
    Route::post  ('/cheques',                [PageController::class, 'chequesStore'])->name('cheques.store');
    Route::patch ('/cheques/{cheque}/clear', [PageController::class, 'chequesClear'])->name('cheques.clear');

    // Sales Assistants
    Route::get ('/assistants',                    [PageController::class, 'assistantsIndex'])->name('assistants.index');
    Route::get ('/assistants/create',             [PageController::class, 'assistantsCreate'])->name('assistants.create');
    Route::post('/assistants',                    [PageController::class, 'assistantsStore'])->name('assistants.store');
    Route::get ('/assistants/{assistant}/edit',   [PageController::class, 'assistantsEdit'])->name('assistants.edit');
    Route::put ('/assistants/{assistant}',        [PageController::class, 'assistantsUpdate'])->name('assistants.update');
    Route::get ('/assistants/{assistant}/ledger', [PageController::class, 'assistantsLedger'])->name('assistants.ledger');
    Route::delete('/assistants/{assistant}', [PageController::class, 'assistantsDestroy'])->name('assistants.destroy');
    // Lotteries
    Route::get ('/lotteries',                [PageController::class, 'lotteriesIndex'])->name('lotteries.index');
    Route::get ('/lotteries/create',         [PageController::class, 'lotteriesCreate'])->name('lotteries.create');
    Route::post('/lotteries',                [PageController::class, 'lotteriesStore'])->name('lotteries.store');
Route::get ('/lotteries/{lottery}/edit', [PageController::class, 'lotteriesEdit'])->name('lotteries.edit');
    Route::put   ('/lotteries/{lottery}',    [PageController::class, 'lotteriesUpdate'])->name('lotteries.update');
    Route::delete('/lotteries/{lottery}',    [PageController::class, 'lotteriesDestroy'])->name('lotteries.destroy');

    // Stock
    Route::get ('/stock',        [PageController::class, 'stockIndex'])->name('stock.index');
    Route::get ('/stock/create', [PageController::class, 'stockCreate'])->name('stock.create');
    Route::post('/stock',        [PageController::class, 'stockStore'])->name('stock.store');

    // Bundle Counter
    Route::get ('/bundle-counter',      [PageController::class, 'bundleCounterIndex'])->name('bundle-counter.index');
    Route::post('/bundle-counter/save', [PageController::class, 'bundleCounterStore'])->name('bundle-counter.store');

    // Ticket Distribution
    Route::get ('/ticket-distribution',                 [TicketDistributionController::class, 'index'])->name('ticket-distribution.index');
    Route::post('/ticket-distribution',                 [TicketDistributionController::class, 'store'])->name('ticket-distribution.store');
    Route::get ('/ticket-distribution/summary',         [TicketDistributionController::class, 'summary'])->name('ticket-distribution.summary');
    Route::post('/ticket-distribution/load-from-date',  [TicketDistributionController::class, 'loadFromDate'])->name('ticket-distribution.load-from-date');
    Route::post('/ticket-distribution/copy-to-date',    [TicketDistributionController::class, 'copyToDate'])->name('ticket-distribution.copy-to-date');

    // Sub-sellers (nested under an assistant)
    Route::get   ('/ticket-distribution/{assistant}/sub-sellers',         [TicketDistributionController::class, 'subSellersIndex'])->name('ticket-distribution.sub-sellers.index');
    Route::post  ('/ticket-distribution/sub-sellers',                     [TicketDistributionController::class, 'subSellersStore'])->name('ticket-distribution.sub-sellers.store');
    Route::put   ('/ticket-distribution/sub-sellers/{subSeller}',         [TicketDistributionController::class, 'subSellersUpdate'])->name('ticket-distribution.sub-sellers.update');
    Route::delete('/ticket-distribution/sub-sellers/{subSeller}/destroy', [TicketDistributionController::class, 'subSellersDestroy'])->name('ticket-distribution.sub-sellers.destroy');

    // Board Settlement
    Route::get ('/board-settlement', [BoardSettlementController::class, 'index'])->name('board-settlement.index');
    Route::post('/board-settlement', [BoardSettlementController::class, 'store'])->name('board-settlement.store');

    // Board Transaction Ledger
    Route::get   ('/board-transactions',                    [BoardTransactionController::class, 'index'])->name('board-transactions.index');
    Route::get   ('/board-transactions/create',             [BoardTransactionController::class, 'create'])->name('board-transactions.create');
    Route::post  ('/board-transactions',                    [BoardTransactionController::class, 'store'])->name('board-transactions.store');
    Route::delete('/board-transactions/{boardTransaction}', [BoardTransactionController::class, 'destroy'])->name('board-transactions.destroy');

    // ── Admin-only routes ─────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {

        // Reports
        Route::get('/reports',                        [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/assistants',             [ReportController::class, 'assistantPerformance'])->name('reports.assistants');
        Route::get('/reports/pdf/daily-sales',        [ReportController::class, 'pdfDailySales'])->name('reports.pdf.daily-sales');
        Route::get('/reports/pdf/ledger/{assistant}', [ReportController::class, 'pdfLedger'])->name('reports.pdf.ledger');
        Route::get('/reports/pdf/range',              [ReportController::class, 'pdfRange'])->name('reports.pdf.range');
        Route::get('/reports/excel',                  [ReportController::class, 'excelExport'])->name('reports.excel');

        // Activity Log
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        // User Management
        Route::get   ('/users',             [UserController::class, 'index'])->name('users.index');
        Route::get   ('/users/create',      [UserController::class, 'create'])->name('users.create');
        Route::post  ('/users',             [UserController::class, 'store'])->name('users.store');
        Route::get   ('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put   ('/users/{user}',      [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}',      [UserController::class, 'destroy'])->name('users.destroy');
    });
});

// ═════════════════════════════════════════════════════════════════════════════
// JSON API routes  (for AJAX / future mobile client)
// ═════════════════════════════════════════════════════════════════════════════
Route::prefix('api')->name('api.')->middleware('auth')->group(function () {

    // Daily P&L summary
    Route::get('/daily-summary',       [DailySummaryController::class, 'show'])->name('daily-summary');
    Route::get('/daily-summary/range', [DailySummaryController::class, 'range'])->name('daily-summary.range');

    // Daily records CRUD
    Route::get ('/daily-records',               [DailyRecordController::class, 'index'])->name('daily-records.index');
    Route::post('/daily-records',               [DailyRecordController::class, 'store'])->name('daily-records.store');
    Route::get ('/daily-records/{dailyRecord}', [DailyRecordController::class, 'show'])->name('daily-records.show');
    Route::put ('/daily-records/{dailyRecord}', [DailyRecordController::class, 'update'])->name('daily-records.update');

    // Winning calculator
    Route::post('/winnings/calculate', [WinningController::class, 'calculate'])->name('winnings.calculate');
    Route::post('/winnings',           [WinningController::class, 'store'])->name('winnings.store');
    Route::get ('/winnings/{date}',    [WinningController::class, 'showByDate'])->name('winnings.show');
});
