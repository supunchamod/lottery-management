<?php

namespace App\Http\Controllers;

use App\Models\DailyTicketNote;
use App\Models\DailyTicketStock;
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
        $assistants = SalesAssistant::with('route')
            ->withoutGlobalScope('ordered')
            ->leftJoin('assistant_routes', 'sales_assistants.route_id', '=', 'assistant_routes.id')
            ->select('sales_assistants.*')
            ->orderByRaw('assistant_routes.name IS NULL, assistant_routes.name ASC')
            ->orderBy('sales_assistants.created_at', 'asc')
            ->get();
        $lotteries  = Lottery::orderByRaw('sort_order IS NULL, sort_order ASC, created_at ASC')->get();

        // Load all records for this date, keyed as [assistant_id][lottery_id] => quantity
        $records = DailyTicketStock::where('date', $date)->get();
        $grid    = [];
        foreach ($records as $r) {
            $grid[$r->assistant_id][$r->lottery_id] = $r->quantity;
        }

        // Load per-assistant notes (no_sales flag + remarks)
        $notesCollection = DailyTicketNote::where('date', $date)->get()->keyBy('assistant_id');
        $alpineNoSales   = [];
        $alpineRemarks   = [];
        foreach ($assistants as $a) {
            $note                   = $notesCollection[$a->id] ?? null;
            $alpineNoSales[$a->id] = (bool) ($note->is_no_sales ?? false);
            $alpineRemarks[$a->id] = $note->remarks ?? '';
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
            'date', 'assistants', 'lotteries', 'grid', 'colTotals', 'rowTotals', 'grandTotal',
            'notesCollection', 'alpineNoSales', 'alpineRemarks'
        ));
    }

    /**
     * Upsert the full grid for a given date, including no-sales flags and remarks.
     * Rows with qty = 0 (and not no-sales) are deleted to keep the table clean.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date'       => 'required|date',
            'qty'        => 'nullable|array',
            'qty.*.*'    => 'nullable|integer|min:0',
            'no_sales'   => 'nullable|array',
            'no_sales.*' => 'nullable|in:0,1',
            'remarks'    => 'nullable|array',
            'remarks.*'  => 'nullable|string|max:255',
        ]);

        $date        = $request->input('date');
        $grid        = $request->input('qty', []);
        $noSalesMap  = $request->input('no_sales', []);
        $remarksMap  = $request->input('remarks', []);

        // Collect every assistant ID submitted across all inputs
        $allIds = collect(array_keys($grid + $noSalesMap + $remarksMap))->map('intval')->unique()->all();

        DB::transaction(function () use ($date, $grid, $noSalesMap, $remarksMap, $allIds) {
            foreach ($allIds as $assistantId) {
                $isNoSales = ($noSalesMap[$assistantId] ?? '0') === '1';
                $remarks   = trim($remarksMap[$assistantId] ?? '');

                // Persist or clear the note record
                if ($isNoSales || $remarks !== '') {
                    DailyTicketNote::updateOrCreate(
                        ['date' => $date, 'assistant_id' => $assistantId],
                        ['is_no_sales' => $isNoSales, 'remarks' => $remarks ?: null]
                    );
                } else {
                    DailyTicketNote::where(['date' => $date, 'assistant_id' => $assistantId])->delete();
                }

                // No-sales: wipe all lottery rows for this assistant and skip qty processing
                if ($isNoSales) {
                    DailyTicketStock::where(['date' => $date, 'assistant_id' => $assistantId])->delete();
                    continue;
                }

                // Normal qty upsert / delete
                foreach ($grid[$assistantId] ?? [] as $lotteryId => $qty) {
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

    // ── Load From Date / Copy To Date ─────────────────────────────────────────

    /**
     * AJAX: Return the ticket distribution grid saved on a given date.
     * Used by the "Load Data From…" modal so the user can pre-fill today's
     * grid from any historical date without a full page reload.
     *
     * POST /ticket-distribution/load-from-date
     * Body: { date }
     * Response: { date, grid: { [aId]: { [lId]: qty } }, noSales: { [aId]: bool }, remarks: { [aId]: string } }
     */
    public function loadFromDate(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $date = $request->input('date');

        $records = DailyTicketStock::where('date', $date)->get();
        $grid    = [];
        foreach ($records as $r) {
            $grid[$r->assistant_id][$r->lottery_id] = (int) $r->quantity;
        }

        $notes   = DailyTicketNote::where('date', $date)->get()->keyBy('assistant_id');
        $noSales = [];
        $remarks = [];
        foreach ($notes as $aId => $note) {
            $noSales[$aId] = (bool) $note->is_no_sales;
            $remarks[$aId] = $note->remarks ?? '';
        }

        return response()->json([
            'date'    => $date,
            'grid'    => $grid,
            'noSales' => $noSales,
            'remarks' => $remarks,
        ]);
    }

    /**
     * AJAX: Write the current grid to a target date.
     * Used by the "Copy to Future Date" modal; mirrors the store() logic
     * exactly but accepts the data as JSON and returns a JSON response.
     *
     * POST /ticket-distribution/copy-to-date
     * Body: { target_date, grid: { [aId]: { [lId]: qty } }, no_sales: { [aId]: '0'|'1' }, remarks: { [aId]: string } }
     */
    public function copyToDate(Request $request)
    {
        $request->validate([
            'target_date'  => 'required|date',
            'grid'         => 'nullable|array',
            'grid.*.*'     => 'nullable|integer|min:0',
            'no_sales'     => 'nullable|array',
            'no_sales.*'   => 'nullable|in:0,1',
            'remarks'      => 'nullable|array',
            'remarks.*'    => 'nullable|string|max:255',
        ]);

        $date       = $request->input('target_date');
        $grid       = $request->input('grid', []);
        $noSalesMap = $request->input('no_sales', []);
        $remarksMap = $request->input('remarks', []);

        $allIds = collect(array_keys($grid + $noSalesMap + $remarksMap))->map('intval')->unique()->all();

        DB::transaction(function () use ($date, $grid, $noSalesMap, $remarksMap, $allIds) {
            foreach ($allIds as $assistantId) {
                $isNoSales = ($noSalesMap[$assistantId] ?? '0') === '1';
                $remarks   = trim($remarksMap[$assistantId] ?? '');

                if ($isNoSales || $remarks !== '') {
                    DailyTicketNote::updateOrCreate(
                        ['date' => $date, 'assistant_id' => $assistantId],
                        ['is_no_sales' => $isNoSales, 'remarks' => $remarks ?: null]
                    );
                } else {
                    DailyTicketNote::where(['date' => $date, 'assistant_id' => $assistantId])->delete();
                }

                if ($isNoSales) {
                    DailyTicketStock::where(['date' => $date, 'assistant_id' => $assistantId])->delete();
                    continue;
                }

                foreach ($grid[$assistantId] ?? [] as $lotteryId => $qty) {
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

        return response()->json([
            'success' => true,
            'message' => 'Data copied to ' . Carbon::parse($date)->format('d M Y') . '.',
        ]);
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

        $assistants = SalesAssistant::orderBy('created_at', 'asc')->get();
        $lotteries  = Lottery::orderByRaw('sort_order IS NULL, sort_order ASC, created_at ASC')->get();

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
        $assistants = SalesAssistant::orderBy('created_at', 'asc')->get();
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
