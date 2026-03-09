<?php

namespace App\Http\Controllers;

use App\Models\DailySale;
use App\Models\LotteryStock;
use App\Models\SalesAssistant;
use App\Services\LedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DailyRecordController extends Controller
{
    public function __construct(private readonly LedgerService $ledger) {}

    // ─────────────────────────────────────────────────────────────────────────
    // LIST
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /daily-records?date=2026-01-01&assistant_id=3
     * List records, optionally filtered.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DailySale::with('assistant')
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('assistant_id')) {
            $query->where('assistant_id', $request->assistant_id);
        }

        return response()->json($query->paginate(25));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /daily-records
     *
     * Required body:
     *   assistant_id, date, tickets_issued_val
     *   returns_qty, returns_val, winning_val, cash_collected
     *
     * Balance is calculated server-side:
     *   balance = tickets_issued_val − (returns_val + winning_val + cash_collected)
     *
     * Ledger entries are automatically posted via LedgerService.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'assistant_id'       => ['required', 'exists:sales_assistants,id'],
            'date'               => ['required', 'date'],
            'tickets_issued_val' => ['required', 'numeric', 'min:0'],
            'returns_qty'        => ['required', 'integer', 'min:0'],
            'returns_val'        => ['required', 'numeric', 'min:0'],
            'winning_val'        => ['required', 'numeric', 'min:0'],
            'cash_collected'     => ['required', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($data) {
            $assistant = SalesAssistant::findOrFail($data['assistant_id']);

            $sale = new DailySale($data);
            $sale->computeBalance();   // ← core formula applied here
            $sale->save();

            // Auto-post all ledger entries for this record
            $this->ledger->postDailySale($assistant, $sale);

            return response()->json([
                'message' => 'Daily record saved.',
                'sale'    => $sale->fresh(['assistant']),
                'balance_breakdown' => $this->balanceBreakdown($sale),
            ], 201);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * PUT /daily-records/{id}
     * Recalculates balance and rebuilds the ledger running balance.
     */
    public function update(Request $request, DailySale $dailyRecord): JsonResponse
    {
        $data = $request->validate([
            'tickets_issued_val' => ['sometimes', 'numeric', 'min:0'],
            'returns_qty'        => ['sometimes', 'integer', 'min:0'],
            'returns_val'        => ['sometimes', 'numeric', 'min:0'],
            'winning_val'        => ['sometimes', 'numeric', 'min:0'],
            'cash_collected'     => ['sometimes', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($data, $dailyRecord) {
            $dailyRecord->fill($data);
            $dailyRecord->computeBalance();
            $dailyRecord->save();

            // Rebuild the full running balance for this assistant from scratch
            $this->ledger->recalculateRunningBalance($dailyRecord->assistant);

            return response()->json([
                'message' => 'Daily record updated.',
                'sale'    => $dailyRecord->fresh(['assistant']),
                'balance_breakdown' => $this->balanceBreakdown($dailyRecord),
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────────────────

    public function show(DailySale $dailyRecord): JsonResponse
    {
        return response()->json([
            'sale'              => $dailyRecord->load('assistant'),
            'balance_breakdown' => $this->balanceBreakdown($dailyRecord),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Returns a human-readable breakdown of the balance formula for
     * display in the front-end / API response.
     *
     * balance = tickets_issued_val − (returns_val + winning_val + cash_collected)
     */
    private function balanceBreakdown(DailySale $sale): array
    {
        $deductions = $sale->returns_val + $sale->winning_val + $sale->cash_collected;

        return [
            'tickets_issued_val' => (float) $sale->tickets_issued_val,
            'deductions'         => [
                'returns_val'    => (float) $sale->returns_val,
                'winning_val'    => (float) $sale->winning_val,
                'cash_collected' => (float) $sale->cash_collected,
                'total'          => (float) $deductions,
            ],
            'balance'   => (float) $sale->balance,
            'status'    => $sale->balance > 0
                ? 'assistant_owes'      // positive → assistant owes agency
                : ($sale->balance < 0
                    ? 'agency_owes'     // negative → agency owes assistant
                    : 'settled'),
        ];
    }
}
