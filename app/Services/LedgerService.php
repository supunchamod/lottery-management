<?php

namespace App\Services;

use App\Models\DailySale;
use App\Models\DailySaleRecord;
use App\Models\Ledger;
use App\Models\SalesAssistant;
use Illuminate\Support\Facades\DB;

/**
 * LedgerService
 *
 * Automatically posts double-entry ledger entries whenever a sales or
 * payment record is created/updated.
 *
 * Ledger convention (per the Excel screenshots):
 *  - DEBIT  : value flows TO the assistant  (tickets issued, credit given)
 *  - CREDIT : value flows FROM the assistant (cash collected, returns)
 *
 * running_balance is maintained per-assistant:
 *   new_balance = last_balance + (debit amount) − (credit amount)
 * A positive running_balance means the assistant owes the agency money.
 */
class LedgerService
{
    /**
     * Post a DEBIT entry for tickets issued to an assistant.
     * Called when a new DailySale record is stored.
     */
    public function postIssuance(SalesAssistant $assistant, DailySale $sale): Ledger
    {
        return DB::transaction(function () use ($assistant, $sale) {
            $entry = $this->createEntry(
                assistant : $assistant,
                date      : $sale->date->toDateString(),
                type      : 'debit',
                amount    : $sale->tickets_issued_val,
                description: "Tickets issued – daily sale #{$sale->id}",
            );

            $assistant->increment('current_balance', $sale->tickets_issued_val);

            return $entry;
        });
    }

    /**
     * Post a CREDIT entry when cash is collected from an assistant.
     * Called when a payment record (cash_collected) is saved.
     *
     * @param  float|string  $amount
     */
    public function postPayment(SalesAssistant $assistant, string $date, float $amount, string $description = 'Cash collected'): Ledger
    {
        return DB::transaction(function () use ($assistant, $date, $amount, $description) {
            $entry = $this->createEntry(
                assistant  : $assistant,
                date       : $date,
                type       : 'credit',
                amount     : $amount,
                description: $description,
            );

            $assistant->decrement('current_balance', $amount);

            return $entry;
        });
    }

    /**
     * Post a CREDIT entry for returns (unsold tickets returned by assistant).
     */
    public function postReturn(SalesAssistant $assistant, DailySale $sale): Ledger
    {
        return DB::transaction(function () use ($assistant, $sale) {
            $entry = $this->createEntry(
                assistant  : $assistant,
                date       : $sale->date->toDateString(),
                type       : 'credit',
                amount     : $sale->returns_val,
                description: "Returns – {$sale->returns_qty} ticket(s) returned, sale #{$sale->id}",
            );

            $assistant->decrement('current_balance', $sale->returns_val);

            return $entry;
        });
    }

    /**
     * Post a CREDIT entry for winnings paid out by the assistant on behalf of the agency.
     */
    public function postWinningPayout(SalesAssistant $assistant, DailySale $sale): Ledger
    {
        return DB::transaction(function () use ($assistant, $sale) {
            $entry = $this->createEntry(
                assistant  : $assistant,
                date       : $sale->date->toDateString(),
                type       : 'credit',
                amount     : $sale->winning_val,
                description: "Winnings paid out by assistant – sale #{$sale->id}",
            );

            $assistant->decrement('current_balance', $sale->winning_val);

            return $entry;
        });
    }

    /**
     * Convenience method: post ALL ledger entries for a completed DailySale
     * in a single transaction (issuance + returns + winnings + cash).
     *
     * Use this instead of the individual methods when saving a full day record.
     */
    public function postDailySale(SalesAssistant $assistant, DailySale $sale): void
    {
        DB::transaction(function () use ($assistant, $sale) {
            // 1. Debit: tickets issued
            $this->createEntry(
                assistant  : $assistant,
                date       : $sale->date->toDateString(),
                type       : 'debit',
                amount     : $sale->tickets_issued_val,
                description: "Tickets issued – sale #{$sale->id}",
            );
            $assistant->increment('current_balance', $sale->tickets_issued_val);

            // 2. Credit: returns
            if ($sale->returns_val > 0) {
                $this->createEntry(
                    assistant  : $assistant,
                    date       : $sale->date->toDateString(),
                    type       : 'credit',
                    amount     : $sale->returns_val,
                    description: "Unsold returns ({$sale->returns_qty} tickets) – sale #{$sale->id}",
                );
                $assistant->decrement('current_balance', $sale->returns_val);
            }

            // 3. Credit: winnings paid out
            if ($sale->winning_val > 0) {
                $this->createEntry(
                    assistant  : $assistant,
                    date       : $sale->date->toDateString(),
                    type       : 'credit',
                    amount     : $sale->winning_val,
                    description: "Winnings paid – sale #{$sale->id}",
                );
                $assistant->decrement('current_balance', $sale->winning_val);
            }

            // 4. Credit: cash collected
            if ($sale->cash_collected > 0) {
                $this->createEntry(
                    assistant  : $assistant,
                    date       : $sale->date->toDateString(),
                    type       : 'credit',
                    amount     : $sale->cash_collected,
                    description: "Cash collected – sale #{$sale->id}",
                );
                $assistant->decrement('current_balance', $sale->cash_collected);
            }
        });
    }

    /**
     * Rebuild the complete running_balance for one assistant from scratch.
     * Useful after bulk imports or manual corrections.
     */
    public function recalculateRunningBalance(SalesAssistant $assistant): void
    {
        DB::transaction(function () use ($assistant) {
            $running = 0.00;

            $assistant->ledgers()
                ->orderBy('date')
                ->orderBy('id')
                ->each(function (Ledger $entry) use (&$running) {
                    $running = $entry->type === 'debit'
                        ? $running + $entry->amount
                        : $running - $entry->amount;

                    $entry->running_balance = $running;
                    $entry->saveQuietly();
                });

            $assistant->update(['current_balance' => $running]);
        });
    }

    /**
     * Post or re-post ledger entries for a DailySaleRecord.
     * On update (isNew=false) the previous entries for that date are wiped first,
     * then fresh ones are written, and the running balance is rebuilt.
     *
     * Ledger logic for the new model:
     *   DEBIT  = value (tickets issued)
     *   CREDIT = cash + total_winning (C+W collected/offset)
     */
    public function postOrUpdateDailySaleRecord(
        SalesAssistant $assistant,
        DailySaleRecord $record,
        bool $isNew = true,
    ): void {
        DB::transaction(function () use ($assistant, $record, $isNew) {
            $dateStr = $record->date->toDateString();

            if (! $isNew) {
                // Remove old entries for this specific date to avoid double-posting.
                // We rebuild running_balance at the end.
                $assistant->ledgers()->where('date', $dateStr)->delete();
            }

            // 1. Debit: tickets issued (value)
            if ($record->value > 0) {
                $this->createEntry(
                    assistant   : $assistant,
                    date        : $dateStr,
                    type        : 'debit',
                    amount      : (float) $record->value,
                    description : "Tickets issued – sale rec #{$record->id}",
                );
            }

            // 2. Credit: cash collected
            if ($record->cash > 0) {
                $this->createEntry(
                    assistant   : $assistant,
                    date        : $dateStr,
                    type        : 'credit',
                    amount      : (float) $record->cash,
                    description : "Cash collected – sale rec #{$record->id}",
                );
            }

            // 3. Credit: winnings paid out
            if ($record->total_winning > 0) {
                $this->createEntry(
                    assistant   : $assistant,
                    date        : $dateStr,
                    type        : 'credit',
                    amount      : (float) $record->total_winning,
                    description : "Winnings paid (NLB {$record->nlb_winning} + DLB {$record->dlb_winning}) – rec #{$record->id}",
                );
            }

            // Rebuild running balance from scratch so the order is consistent
            $this->recalculateRunningBalance($assistant);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function createEntry(
        SalesAssistant $assistant,
        string         $date,
        string         $type,        // 'debit' | 'credit'
        float          $amount,
        string         $description = '',
    ): Ledger {
        // Fetch the latest running_balance for this assistant
        $last = $assistant->ledgers()
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->value('running_balance') ?? 0.00;

        $running = $type === 'debit'
            ? $last + $amount
            : $last - $amount;

        return Ledger::create([
            'assistant_id'    => $assistant->id,
            'date'            => $date,
            'type'            => $type,
            'amount'          => $amount,
            'running_balance' => $running,
        ]);
    }
}
