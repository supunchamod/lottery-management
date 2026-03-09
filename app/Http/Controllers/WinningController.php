<?php

namespace App\Http\Controllers;

use App\Models\Winning;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * WinningController
 *
 * Manages the daily winning-ticket entry form (mirrors the NLB/DLB
 * winning calculator from the Access/Excel screenshots).
 *
 * Each row in `winnings` stores counts for every prize denomination for
 * both boards on a given date. Totals are computed server-side so the
 * front-end can display a live preview before save.
 */
class WinningController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // PREVIEW  (live calculator – no DB write)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /winnings/calculate
     *
     * Accepts any mix of NLB/DLB tier quantities and returns calculated
     * subtotals + grand total without persisting anything.
     * Designed to power the real-time "calculate" button on the entry form.
     */
    public function calculate(Request $request): JsonResponse
    {
        $data = $this->validateTiers($request);

        $winning = new Winning($data);
        $winning->computeTotals();

        return response()->json([
            'nlb_breakdown' => $this->buildBreakdown($winning, 'nlb', Winning::NLB_TIERS),
            'dlb_breakdown' => $this->buildBreakdown($winning, 'dlb', Winning::DLB_TIERS),
            'nlb_total'     => (float) $winning->nlb_total,
            'dlb_total'     => (float) $winning->dlb_total,
            'total_val'     => (float) $winning->total_val,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CRUD
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /winnings
     * Store or update the daily winning record.
     * One record per date — uses updateOrCreate so re-submissions are safe.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validateTiers($request, requireDate: true);

        $winning = Winning::firstOrNew(['date' => $data['date']]);
        $winning->fill($data);
        $winning->computeTotals();
        $winning->save();

        return response()->json([
            'message'       => 'Winning record saved.',
            'winning'       => $winning,
            'nlb_breakdown' => $this->buildBreakdown($winning, 'nlb', Winning::NLB_TIERS),
            'dlb_breakdown' => $this->buildBreakdown($winning, 'dlb', Winning::DLB_TIERS),
        ], 201);
    }

    /**
     * GET /winnings/{date}   e.g. /winnings/2026-01-01
     */
    public function showByDate(string $date): JsonResponse
    {
        $winning = Winning::where('date', $date)->firstOrFail();

        return response()->json([
            'winning'       => $winning,
            'nlb_breakdown' => $this->buildBreakdown($winning, 'nlb', Winning::NLB_TIERS),
            'dlb_breakdown' => $this->buildBreakdown($winning, 'dlb', Winning::DLB_TIERS),
            'nlb_total'     => (float) $winning->nlb_total,
            'dlb_total'     => (float) $winning->dlb_total,
            'total_val'     => (float) $winning->total_val,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build a line-by-line breakdown for one board (NLB or DLB).
     *
     * Returns an array like:
     *  [['denomination' => 40, 'qty' => 811, 'subtotal' => 32440], ...]
     */
    private function buildBreakdown(Winning $w, string $prefix, array $tiers): array
    {
        $rows = [];
        foreach ($tiers as $col => $denom) {
            $qty = (int) ($w->{$col} ?? 0);
            $rows[] = [
                'denomination' => $denom,
                'qty'          => $qty,
                'subtotal'     => $qty * $denom,
            ];
        }
        return $rows;
    }

    /**
     * Validate and return all NLB + DLB tier inputs.
     * $requireDate makes the `date` field mandatory (for store/update).
     */
    private function validateTiers(Request $request, bool $requireDate = false): array
    {
        $tierRules = [];

        foreach (array_keys(Winning::NLB_TIERS) as $col) {
            $tierRules[$col] = ['nullable', 'integer', 'min:0'];
        }

        foreach (array_keys(Winning::DLB_TIERS) as $col) {
            $tierRules[$col] = ['nullable', 'integer', 'min:0'];
        }

        return $request->validate([
            'date' => $requireDate
                ? ['required', 'date']
                : ['nullable', 'date'],
            ...$tierRules,
        ]);
    }
}
