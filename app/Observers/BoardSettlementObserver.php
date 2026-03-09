<?php

namespace App\Observers;

use App\Models\BoardSettlement;
use App\Services\BoardLedgerService;

/**
 * BoardSettlementObserver
 *
 * Listens to Eloquent events on BoardSettlement and delegates ledger
 * synchronisation to BoardLedgerService.
 *
 * Events handled:
 *  saved   → create or refresh the linked "get_tickets" / "paid_bill" rows
 *  deleted → retract all ledger rows that belong to this settlement
 */
class BoardSettlementObserver
{
    public function __construct(private readonly BoardLedgerService $ledger) {}

    /**
     * Fires after every create OR update.
     * The service uses updateOrCreate, so re-saving is always safe.
     */
    public function saved(BoardSettlement $settlement): void
    {
        $this->ledger->postSettlement($settlement);
    }

    /**
     * Fires after the settlement row is hard-deleted.
     * Retracts both ledger rows and rebuilds running balances.
     */
    public function deleted(BoardSettlement $settlement): void
    {
        $this->ledger->retractSettlement($settlement);
    }
}
