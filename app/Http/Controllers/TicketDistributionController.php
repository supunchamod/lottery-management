<?php

namespace App\Http\Controllers;

use App\Models\DailyTicketStock;
use App\Models\DefaultDistribution;
use App\Models\Lottery;
use App\Models\SalesAssistant;
use App\Models\SubSeller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketDistributionController extends Controller
{
    // ── Main Grid ─────────────────────────────────────────────────────────────

    /**
     * Show the data-entry grid (all assistants × all lotteries) for a given date.
     */
    public function index(Request $request)
    {
        $date       = $request->input('date', today()->toDateString());
        $assistants = SalesAssistant::orderBy('name')->get();
        $lotteries  = Lottery::orderBy('board')->orderBy('name')->get();

        // Load all records for this date, keyed as [assistant_id][lottery_id] => quantity
        $records = DailyTicketStock::where('date', $date)->get();
        $grid    = [];
        foreach ($records as $r) {
            $grid[$r->assistant_id][$r->lottery_id] = $r->quantity;
        }

        // PHP-side totals (also used to seed Alpine state)
        $colTotals = [];
        foreach ($lotteries as $l) {
            $colTotals[$l->id] = collect($grid)->sum(fn ($row) => $row[$l->id] ?? 0);
        }
        $rowTotals = [];
        foreach ($assistants as $a) {
            $rowTotals[$a->id] = collect($grid[$a->id] ?? [])->sum();
        }
        $grandTotal = array_sum($colTotals);

        return view('ticket-distribution.index', compact(
            'date', 'assistants', 'lotteries', 'grid', 'colTotals', 'rowTotals', 'grandTotal'
        ));
    }

    /**
     * Upsert the full grid for a given date.
     * Rows with qty = 0 or blank are deleted (keep table clean).
     */
    public function store(Request $request)
    {
        $request->validate([
            'date'    => 'required|date',
            'qty'     => 'nullable|array',
            'qty.*.*' => 'nullable|integer|min:0',
        ]);

        $date = $request->input('date');
        $grid = $request->input('qty', []);   // [assistant_id][lottery_id] => qty

        DB::transaction(function () use ($date, $grid) {
            foreach ($grid as $assistantId => $lotteryQtys) {
                foreach ($lotteryQtys as $lotteryId => $qty) {
                    $qty = (int) ($qty ?? 0);

                    if ($qty > 0) {
                        DailyTicketStock::updateOrCreate(
                            ['date' => $date, 'assistant_id' => $assistantId, 'lottery_id' => $lotteryId],
                            ['quantity' => $qty]
                        );
                    } else {
                        DailyTicketStock::where([
                            'date'         => $date,
                            'assistant_id' => $assistantId,
                            'lottery_id'   => $lotteryId,
                        ])->delete();
                    }
                }
            }
        });

        return redirect()
            ->route('ticket-distribution.index', ['date' => $date])
            ->with('success', 'Ticket distribution saved for ' . Carbon::parse($date)->format('d M Y') . '.');
    }

    // ── Smart Default Quantity ─────────────────────────────────────────────────

    /**
     * AJAX: Return the default grid for the weekday of the given date.
     *
     * GET /api/ticket-distribution/defaults?date=YYYY-MM-DD
     *
     * Response: { day_of_week: 1, day_name: "Monday", defaults: { assistantId: { lotteryId: qty } } }
     */
    public function getDefaults(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $carbon     = Carbon::parse($request->input('date'));
        $dayOfWeek  = $carbon->dayOfWeek;           // 0=Sun … 6=Sat

        return response()->json([
            'day_of_week' => $dayOfWeek,
            'day_name'    => $carbon->format('l'),  // e.g. "Monday"
            'defaults'    => DefaultDistribution::gridForDay($dayOfWeek),
        ]);
    }

    /**
     * AJAX: Upsert default quantities for the weekday derived from the given date.
     *
     * POST /api/ticket-distribution/defaults
     * Body: { date: "YYYY-MM-DD", qty: { assistantId: { lotteryId: qty } } }
     */
    public function saveDefaults(Request $request)
    {
        $request->validate([
            'date'    => 'required|date',
            'qty'     => 'required|array',
            'qty.*.*' => 'nullable|integer|min:0',
        ]);

        $dayOfWeek = Carbon::parse($request->input('date'))->dayOfWeek;
        $grid      = $request->input('qty', []);

        DB::transaction(function () use ($dayOfWeek, $grid) {
            foreach ($grid as $assistantId => $lotteryQtys) {
                foreach ($lotteryQtys as $lotteryId => $qty) {
                    $qty = (int) ($qty ?? 0);

                    if ($qty > 0) {
                        DefaultDistribution::updateOrCreate(
                            [
                                'assistant_id' => $assistantId,
                                'lottery_id'   => $lotteryId,
                                'day_of_week'  => $dayOfWeek,
                            ],
                            ['default_qty' => $qty]
                        );
                    } else {
                        DefaultDistribution::where([
                            'assistant_id' => $assistantId,
                            'lottery_id'   => $lotteryId,
                            'day_of_week'  => $dayOfWeek,
                        ])->delete();
                    }
                }
            }
        });

        return response()->json(['message' => 'Defaults saved.', 'day_of_week' => $dayOfWeek]);
    }

    // ── Summary ───────────────────────────────────────────────────────────────

    /**
     * Weekly / Monthly aggregation view.
     */
    public function summary(Request $request)
    {
        $mode      = $request->input('mode', 'weekly');
        $reference = $request->input('ref', today()->toDateString());

        $refDate = Carbon::parse($reference);

        if ($mode === 'weekly') {
            $from  = $refDate->copy()->startOfWeek(Carbon::MONDAY);
            $to    = $refDate->copy()->endOfWeek(Carbon::SUNDAY);
            $label = 'Week of ' . $from->format('d M') . ' – ' . $to->format('d M Y');
        } else {
            $from  = $refDate->copy()->startOfMonth();
            $to    = $refDate->copy()->endOfMonth();
            $label = $refDate->format('F Y');
        }

        $assistants = SalesAssistant::orderBy('name')->get();
        $lotteries  = Lottery::orderBy('board')->orderBy('name')->get();

        $rows = DailyTicketStock::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->select('assistant_id', 'lottery_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('assistant_id', 'lottery_id')
            ->get();

        $summary = [];
        foreach ($rows as $r) {
            $summary[$r->assistant_id][$r->lottery_id] = (int) $r->total_qty;
        }

        $colTotals = [];
        foreach ($lotteries as $l) {
            $colTotals[$l->id] = collect($summary)->sum(fn ($row) => $row[$l->id] ?? 0);
        }
        $rowTotals = [];
        foreach ($assistants as $a) {
            $rowTotals[$a->id] = collect($summary[$a->id] ?? [])->sum();
        }
        $grandTotal = array_sum($colTotals);

        return view('ticket-distribution.summary', compact(
            'mode', 'reference', 'label', 'from', 'to',
            'assistants', 'lotteries', 'summary', 'colTotals', 'rowTotals', 'grandTotal'
        ));
    }

    // ── Sub-seller CRUD (kept intact) ─────────────────────────────────────────

    public function subSellersIndex(SalesAssistant $assistant)
    {
        $assistants = SalesAssistant::orderBy('name')->get();
        $subSellers = SubSeller::where('assistant_id', $assistant->id)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('ticket-distribution.sub-sellers', compact('assistant', 'assistants', 'subSellers'));
    }

    public function subSellersStore(Request $request)
    {
        $data = $request->validate([
            'assistant_id' => 'required|exists:sales_assistants,id',
            'name'         => 'required|string|max:100',
            'phone'        => 'nullable|string|max:20',
        ]);

        SubSeller::create($data + ['is_active' => true]);

        return back()->with('success', 'Sub-seller added.');
    }

    public function subSellersUpdate(Request $request, SubSeller $subSeller)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'phone'     => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $subSeller->update($data);

        return back()->with('success', 'Sub-seller updated.');
    }

    public function subSellersDestroy(SubSeller $subSeller)
    {
        $subSeller->update(['is_active' => false]);

        return back()->with('success', 'Sub-seller deactivated.');
    }
}
