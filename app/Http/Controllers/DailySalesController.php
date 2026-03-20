<?php

namespace App\Http\Controllers;

use App\Models\DailySaleRecord;
use App\Models\SalesAssistant;
use App\Services\LedgerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailySalesController extends Controller
{
    public function __construct(private readonly LedgerService $ledger) {}

    // ── Grid index ────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $date       = $request->input('date', today()->toDateString());
        $assistants = SalesAssistant::with('route')->orderBy('sales_assistants.created_at', 'asc')->get();

        // Load existing records for this date, keyed by assistant_id
        $records = DailySaleRecord::where('date', $date)
            ->get()
            ->keyBy('assistant_id');

        // Build Alpine seed data for every assistant
        $alpineRows = [];
        foreach ($assistants as $a) {
            $r = $records->get($a->id);
            $alpineRows[$a->id] = [
                'qty'        => $r->tickets_issued_qty ?? 0,
                'unitPrice'  => $r ? (float) $r->unit_price  : 40,
                'd5'         => $r->denom_5    ?? 0,
                'd10'        => $r->denom_10   ?? 0,
                'd20'        => $r->denom_20   ?? 0,
                'd50'        => $r->denom_50   ?? 0,
                'd100'       => $r->denom_100  ?? 0,
                'd500'       => $r->denom_500  ?? 0,
                'd1000'      => $r->denom_1000 ?? 0,
                'd5000'      => $r->denom_5000 ?? 0,
                'nlbWinning' => $r ? (float) $r->nlb_winning : 0,
                'dlbWinning' => $r ? (float) $r->dlb_winning : 0,
                'remarks'    => $r->remarks ?? '',
            ];
        }

        // Day summary from stored records
        $summary = [
            'totalValue'   => $records->sum('value'),
            'totalCash'    => $records->sum('cash'),
            'totalWinning' => $records->sum('total_winning'),
            'totalCW'      => $records->sum('cw'),
            'totalBalance' => $records->sum('balance'),
        ];

        // Date navigation — last 8 days
        $navDates = collect(range(0, 7))->map(
            fn ($i) => Carbon::today()->subDays($i)->toDateString()
        );

        return view('daily-sales.index', compact(
            'date', 'assistants', 'records', 'alpineRows', 'summary', 'navDates'
        ));
    }

    // ── Bulk upsert ───────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'date'          => 'required|date',
            'rows'          => 'nullable|array',
            'rows.*.qty'    => 'nullable|integer|min:0',
            'rows.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        $date = $request->input('date');
        $rows = $request->input('rows', []);

        DB::transaction(function () use ($date, $rows) {
            foreach ($rows as $assistantId => $data) {
                $qty  = (int)   ($data['qty']        ?? 0);
                $up   = (float) ($data['unit_price']  ?? 0);
                $nlb  = (float) ($data['nlb_winning'] ?? 0);
                $dlb  = (float) ($data['dlb_winning'] ?? 0);
                $d5   = (int)   ($data['d5']   ?? 0);
                $d10  = (int)   ($data['d10']  ?? 0);
                $d20  = (int)   ($data['d20']  ?? 0);
                $d50  = (int)   ($data['d50']  ?? 0);
                $d100 = (int)   ($data['d100'] ?? 0);
                $d500 = (int)   ($data['d500'] ?? 0);
                $d1k  = (int)   ($data['d1000'] ?? 0);
                $d5k  = (int)   ($data['d5000'] ?? 0);

                $isEmpty = ($qty === 0 && $nlb === 0 && $dlb === 0
                    && $d5 === 0 && $d10 === 0 && $d20 === 0
                    && $d50 === 0 && $d100 === 0 && $d500 === 0
                    && $d1k === 0 && $d5k === 0);

                if ($isEmpty) {
                    DailySaleRecord::where(['date' => $date, 'assistant_id' => $assistantId])->delete();
                    continue;
                }

                $rec = DailySaleRecord::firstOrNew([
                    'date'         => $date,
                    'assistant_id' => $assistantId,
                ]);

                $rec->fill([
                    'tickets_issued_qty' => $qty,
                    'unit_price'         => $up,
                    'denom_5'    => $d5,   'denom_10'   => $d10,
                    'denom_20'   => $d20,  'denom_50'   => $d50,
                    'denom_100'  => $d100, 'denom_500'  => $d500,
                    'denom_1000' => $d1k,  'denom_5000' => $d5k,
                    'nlb_winning' => $nlb,
                    'dlb_winning' => $dlb,
                    'remarks'     => $data['remarks'] ?? null,
                ]);
                $rec->compute();

                $isNew = ! $rec->exists;
                $rec->save();

                // Post / re-post ledger entry
                $assistant = SalesAssistant::find($assistantId);
                if ($assistant) {
                    $this->ledger->postOrUpdateDailySaleRecord($assistant, $rec, $isNew);
                }
            }
        });

        return redirect()
            ->route('daily-sales.index', ['date' => $date])
            ->with('success', 'Daily records saved for ' . Carbon::parse($date)->format('d M Y') . '.');
    }

    // ── Single-entry AJAX save (modal "Add to Table" button) ─────────────────
    // Saves one assistant's record for any date and returns JSON.
    // The bulk store() endpoint is unchanged and handles the full grid save.

    public function storeSingle(Request $request)
    {
        $request->validate([
            'date'         => 'required|date',
            'assistant_id' => 'required|exists:sales_assistants,id',
            'qty'          => 'nullable|integer|min:0',
            'unit_price'   => 'nullable|numeric|min:0',
            'nlb_winning'  => 'nullable|numeric|min:0',
            'dlb_winning'  => 'nullable|numeric|min:0',
            'd5'           => 'nullable|integer|min:0',
            'd10'          => 'nullable|integer|min:0',
            'd20'          => 'nullable|integer|min:0',
            'd50'          => 'nullable|integer|min:0',
            'd100'         => 'nullable|integer|min:0',
            'd500'         => 'nullable|integer|min:0',
            'd1000'        => 'nullable|integer|min:0',
            'd5000'        => 'nullable|integer|min:0',
            'remarks'      => 'nullable|string|max:500',
        ]);

        $date        = $request->input('date');
        $assistantId = $request->input('assistant_id');

        $qty  = (int)   $request->input('qty',          0);
        $up   = (float) $request->input('unit_price',   0);
        $nlb  = (float) $request->input('nlb_winning',  0);
        $dlb  = (float) $request->input('dlb_winning',  0);
        $d5   = (int)   $request->input('d5',   0);
        $d10  = (int)   $request->input('d10',  0);
        $d20  = (int)   $request->input('d20',  0);
        $d50  = (int)   $request->input('d50',  0);
        $d100 = (int)   $request->input('d100', 0);
        $d500 = (int)   $request->input('d500', 0);
        $d1k  = (int)   $request->input('d1000', 0);
        $d5k  = (int)   $request->input('d5000', 0);

        $isEmpty = ($qty === 0 && $nlb === 0 && $dlb === 0
            && $d5 === 0 && $d10 === 0 && $d20 === 0
            && $d50 === 0 && $d100 === 0 && $d500 === 0
            && $d1k === 0 && $d5k === 0);

        if ($isEmpty) {
            DailySaleRecord::where(['date' => $date, 'assistant_id' => $assistantId])->delete();
            return response()->json(['success' => true, 'deleted' => true, 'date' => $date, 'assistant_id' => (int) $assistantId]);
        }

        $rec = null;

        DB::transaction(function () use (
            $date, $assistantId, $qty, $up, $nlb, $dlb,
            $d5, $d10, $d20, $d50, $d100, $d500, $d1k, $d5k, $request, &$rec
        ) {
            $rec   = DailySaleRecord::firstOrNew(['date' => $date, 'assistant_id' => $assistantId]);
            $isNew = ! $rec->exists;

            $rec->fill([
                'tickets_issued_qty' => $qty,
                'unit_price'         => $up,
                'denom_5'    => $d5,   'denom_10'   => $d10,
                'denom_20'   => $d20,  'denom_50'   => $d50,
                'denom_100'  => $d100, 'denom_500'  => $d500,
                'denom_1000' => $d1k,  'denom_5000' => $d5k,
                'nlb_winning' => $nlb,
                'dlb_winning' => $dlb,
                'remarks'     => $request->input('remarks'),
            ]);
            $rec->compute();
            $rec->save();

            $assistant = SalesAssistant::find($assistantId);
            if ($assistant) {
                $this->ledger->postOrUpdateDailySaleRecord($assistant, $rec, $isNew);
            }
        });

        // Return the saved row in the same shape the Alpine grid uses
        return response()->json([
            'success'      => true,
            'date'         => $date,
            'assistant_id' => (int) $assistantId,
            'row'          => [
                'qty'        => (int)   $rec->tickets_issued_qty,
                'unitPrice'  => (float) $rec->unit_price,
                'd5'         => (int)   $rec->denom_5,
                'd10'        => (int)   $rec->denom_10,
                'd20'        => (int)   $rec->denom_20,
                'd50'        => (int)   $rec->denom_50,
                'd100'       => (int)   $rec->denom_100,
                'd500'       => (int)   $rec->denom_500,
                'd1000'      => (int)   $rec->denom_1000,
                'd5000'      => (int)   $rec->denom_5000,
                'nlbWinning' => (float) $rec->nlb_winning,
                'dlbWinning' => (float) $rec->dlb_winning,
                'remarks'    => $rec->remarks ?? '',
            ],
        ]);
    }

    // ── Analysis ──────────────────────────────────────────────────────────────

    public function analysis(Request $request)
    {
        $assistants = SalesAssistant::orderBy('created_at', 'asc')->get();
        $allIds     = $assistants->pluck('id')->map(fn ($id) => (int) $id)->all();

        // Multi-assistant selector — defaults to all assistants
        $inputIds     = $request->input('assistant_ids', $allIds);
        if (! is_array($inputIds)) {
            $inputIds = [$inputIds];
        }
        $assistantIds = array_values(array_intersect(array_map('intval', $inputIds), $allIds));
        if (empty($assistantIds)) {
            $assistantIds = $allIds;
        }

        $period    = $request->input('period', 'today'); // today|weekly|monthly|overall|custom
        $today     = Carbon::today();
        $startDate = $request->input('start_date', $today->copy()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date',   $today->toDateString());

        $query = DailySaleRecord::whereIn('assistant_id', $assistantIds);

        match ($period) {
            'today'   => $query->whereDate('date', $today),
            'weekly'  => $query->whereBetween('date', [
                            $today->copy()->startOfWeek()->toDateString(),
                            $today->copy()->endOfWeek()->toDateString(),
                        ]),
            'monthly' => $query->whereBetween('date', [
                            $today->copy()->startOfMonth()->toDateString(),
                            $today->copy()->endOfMonth()->toDateString(),
                        ]),
            'custom'  => $query->whereBetween('date', [$startDate, $endDate]),
            default   => null, // overall — no date filter
        };

        $records = $query->orderBy('date')->get();

        $selectedAssistants = $assistants->whereIn('id', $assistantIds)->values();

        // Per-assistant breakdown for comparison table
        $byAssistant = $records->groupBy('assistant_id')->map(fn ($recs) => [
            'totalValue'   => $recs->sum('value'),
            'totalCash'    => $recs->sum('cash'),
            'totalWinning' => $recs->sum('total_winning'),
            'totalCW'      => $recs->sum('cw'),
            'totalBalance' => $recs->sum('balance'),
            'recordCount'  => $recs->count(),
        ]);

        $stats = [
            'totalValue'       => $records->sum('value'),
            'totalCash'        => $records->sum('cash'),
            'totalWinning'     => $records->sum('total_winning'),
            'totalCW'          => $records->sum('cw'),
            'totalOutstanding' => $records->where('balance', '>', 0)->sum('balance'),
            'totalOverpaid'    => $records->where('balance', '<', 0)->sum(fn ($r) => abs($r->balance)),
            'recordCount'      => $records->count(),
        ];

        // Chart — per-assistant comparison bar chart
        $chartLabels  = $selectedAssistants->map(fn ($a) => $a->name)->toArray();
        $chartValue   = $selectedAssistants->map(fn ($a) => (float) ($byAssistant->get($a->id)['totalValue']   ?? 0))->toArray();
        $chartCash    = $selectedAssistants->map(fn ($a) => (float) ($byAssistant->get($a->id)['totalCash']    ?? 0))->toArray();
        $chartWinning = $selectedAssistants->map(fn ($a) => (float) ($byAssistant->get($a->id)['totalWinning'] ?? 0))->toArray();

        return view('daily-sales.analysis', compact(
            'assistants', 'selectedAssistants', 'assistantIds', 'allIds',
            'period', 'startDate', 'endDate',
            'records', 'byAssistant', 'stats',
            'chartLabels', 'chartValue', 'chartCash', 'chartWinning'
        ));
    }
}
