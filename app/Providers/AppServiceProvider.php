<?php

namespace App\Providers;

use App\Models\BoardSettlement;
use App\Observers\BoardSettlementObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Automatically mirror every BoardSettlement save/delete
        // into the board_transactions ledger via the observer.
        BoardSettlement::observe(BoardSettlementObserver::class);
    }
}
