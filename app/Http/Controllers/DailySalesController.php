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
        $assistants = SalesAssistant::orderBy('name')->get();

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
                $d20  = (int)   ($data['d20']  ?? 0);
                $d50  = (int)   ($data['d50']  ?? 0);
                $d100 = (int)   ($data['d100'] ?? 0);
                $d500 = (int)   ($data['d500'] ?? 0);
                $d1k  = (int)   ($data['d1000'] ?? 0);
                $d5k  = (int)   ($data['d5000'] ?? 0);

                $isEmpty = ($qty === 0 && $nlb === 0 && $dlb === 0
                    && $d20 === 0 && $d50 === 0 && $d100 === 0
                    && $d500 === 0 && $d1k === 0 && $d5k === 0);

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

    // ── Analysis ──────────────────────────────────────────────────────────────

    public function analysis(Request $request)
    {
        $assistants  = SalesAssistant::orderBy('name')->get();
        $assistantId = $request->input('assistant_id', $assistants->first()?->id);
        $period      = $request->input('period', 'weekly'); // today|weekly|monthly|overall

        $assistant = $assistants->firstWhere('id', $assistantId);

        $query = DailySaleRecord::where('assistant_id', $assistantId);

        $today = Carbon::today();
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
            default   => null, // overall — no date filter
        };

        $records = $query->orderBy('date')->get();

        $stats = [
            'totalValue'      => $records->sum('value'),
            'totalCash'       => $records->sum('cash'),
            'totalWinning'    => $records->sum('total_winning'),
            'totalCW'         => $records->sum('cw'),
            'totalOutstanding' => $records->where('balance', '>', 0)->sum('balance'),
            'totalOverpaid'   => $records->where('balance', '<', 0)->sum(fn ($r) => abs($r->balance)),
            'recordCount'     => $records->count(),
        ];

        // Chart data — daily balance trend
        $chartLabels = $records->pluck('date')->map(fn ($d) => $d->format('d M'))->toArray();
        $chartBalance = $records->map(fn ($r) => (float) $r->balance)->toArray();
        $chartCash    = $records->map(fn ($r) => (float) $r->cash)->toArray();
        $chartValue   = $records->map(fn ($r) => (float) $r->value)->toArray();

        return view('daily-sales.analysis', compact(
            'assistants', 'assistant', 'assistantId', 'period',
            'records', 'stats', 'chartLabels', 'chartBalance', 'chartCash', 'chartValue'
        ));
    }
}
