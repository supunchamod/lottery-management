<?php

use App\Http\Controllers\DailyRecordController;
use App\Http\Controllers\DailySummaryController;
use App\Http\Controllers\WinningController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

// ─── Authenticated routes ─────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // ── Admin-only routes ────────────────────────────────────────────────────
    Route::middleware(['role:admin'])->group(function () {
        // Daily P&L summary
        Route::get('/daily-summary',        [DailySummaryController::class, 'show']);
        Route::get('/daily-summary/range',  [DailySummaryController::class, 'range']);
    });

    // ── Admin + Sub-Admin routes ─────────────────────────────────────────────
    Route::middleware(['role:admin,sub-admin'])->group(function () {

        // Daily sales records
        Route::get('/daily-records',             [DailyRecordController::class, 'index']);
        Route::post('/daily-records',            [DailyRecordController::class, 'store']);
        Route::get('/daily-records/{dailyRecord}', [DailyRecordController::class, 'show']);
        Route::put('/daily-records/{dailyRecord}', [DailyRecordController::class, 'update']);

        // Winning calculator
        Route::post('/winnings/calculate',   [WinningController::class, 'calculate']);  // live preview
        Route::post('/winnings',             [WinningController::class, 'store']);
        Route::get('/winnings/{date}',       [WinningController::class, 'showByDate']);
    });
});
