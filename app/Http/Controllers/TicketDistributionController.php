<?php

namespace App\Http\Controllers;

use App\Exports\TicketDistributionExport;
use App\Models\DailySaleRecord;
use App\Models\DailyTicketNote;
use App\Models\DailyTicketStock;
use App\Models\Lottery;
use App\Models\SalesAssistant;
use App\Models\SubSeller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

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
            ->orderBy('sales_assistants.id', 'asc')
            ->get();
        $lotteries  = Lottery::orderByRaw('sort_order IS NULL, sort_order ASC, created_at ASC')->get();

        // Load all records for this date, keyed as [assistant_id][lottery_id] => quantity
        $records = DailyTicketStock::where('date', $date)->get();
        $grid    = [];
        foreach ($records as $r) {
            $grid[$r->assistant_id][$r->lottery_id] = $r->quantity;
        }

        // Load per-assistant notes (no_sales flag, handed_over flag, remarks)
        $notesCollection   = DailyTicketNote::where('date', $date)->get()->keyBy('assistant_id');
        $alpineNoSales     = [];
        $alpineRemarks     = [];
        $alpineHandedOver  = [];
        foreach ($assistants as $a) {
            $note                      = $notesCollection[$a->id] ?? null;
            $alpineNoSales[$a->id]    = (bool) ($note->is_no_sales ?? false);
            $alpineRemarks[$a->id]    = $note->remarks ?? '';
            $alpineHandedOver[$a->id] = (bool) ($note->is_handed_over ?? false);
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
            'notesCollection', 'alpineNoSales', 'alpineRemarks', 'alpineHandedOver'
        ));
    }

    /**
     * Upsert the full grid for a given date, including no-sales flags and remarks.
     * Rows with qty = 0 (and not no-sales) are deleted to keep the table clean.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date'          => 'required|date',
            'qty'           => 'nullable|array',
            'qty.*.*'       => 'nullable|integer|min:0',
            'no_sales'      => 'nullable|array',
            'no_sales.*'    => 'nullable|in:0,1',
            'handed_over'   => 'nullable|array',
            'handed_over.*' => 'nullable|in:0,1',
            'remarks'       => 'nullable|array',
            'remarks.*'     => 'nullable|string|max:255',
        ]);

        $date           = $request->input('date');
        $grid           = $request->input('qty', []);
        $noSalesMap     = $request->input('no_sales', []);
        $handedOverMap  = $request->input('handed_over', []);
        $remarksMap     = $request->input('remarks', []);

        // Build a price map [lottery_id => unit_price] for ticket value calculation
        $lotteryPrices = Lottery::pluck('unit_price', 'id');

        // Collect every assistant ID submitted across all inputs
        $allIds = collect(array_keys($grid + $noSalesMap + $handedOverMap + $remarksMap))->map('intval')->unique()->all();

        DB::transaction(function () use ($date, $grid, $noSalesMap, $handedOverMap, $remarksMap, $allIds, $lotteryPrices) {
            foreach ($allIds as $assistantId) {
                $isNoSales    = ($noSalesMap[$assistantId] ?? '0') === '1';
                $isHandedOver = ($handedOverMap[$assistantId] ?? '0') === '1';
                $remarks      = trim($remarksMap[$assistantId] ?? '');

                // ── 1. Persist or clear the note record ───────────────────────
                if ($isNoSales || $isHandedOver || $remarks !== '') {
                    DailyTicketNote::updateOrCreate(
                        ['date' => $date, 'assistant_id' => $assistantId],
                        ['is_no_sales' => $isNoSales, 'is_handed_over' => $isHandedOver, 'remarks' => $remarks ?: null]
                    );
                } else {
                    DailyTicketNote::where(['date' => $date, 'assistant_id' => $assistantId])->delete();
                }

                // ── 2. Ticket stock rows ───────────────────────────────────────
                if ($isNoSales) {
                    // No-sales: wipe all lottery rows for this assistant
                    DailyTicketStock::where(['date' => $date, 'assistant_id' => $assistantId])->delete();
                } else {
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

                // ── 3. DailySaleRecord (daily_sales_records) — always evaluated ──
                //
                // NOTE: The Daily Sales Entry UI reads from `daily_sales_records`
                // via DailySaleRecord, NOT from `daily_sales` via DailySale.
                // The key columns are:
                //   tickets_issued_qty  — total ticket count
                //   value               — total monetary value (sum of qty × price)
                //   balance             — value minus cash+winnings already entered
                //
                Log::info('[TicketDist] Processing assistant', [
                    'date'          => $date,
                    'assistant_id'  => $assistantId,
                    'is_handed_over'=> $isHandedOver,
                    'is_no_sales'   => $isNoSales,
                    'grid_keys'     => array_keys($grid[$assistantId] ?? []),
                ]);

                if ($isHandedOver && !$isNoSales) {
                    // Tally the total ticket count and total monetary value
                    // across all lotteries distributed to this assistant today.
                    $totalQty   = 0;
                    $totalValue = 0.0;
                    foreach ($grid[$assistantId] ?? [] as $lotteryId => $qty) {
                        $qty         = (int) ($qty ?? 0);
                        $totalQty   += $qty;
                        $totalValue += $qty * (float) ($lotteryPrices[$lotteryId] ?? 0);
                    }

                    Log::info('[TicketDist] Handed over — upserting DailySaleRecord', [
                        'date'         => $date,
                        'assistant_id' => $assistantId,
                        'total_qty'    => $totalQty,
                        'total_value'  => $totalValue,
                    ]);

                    // Fetch (or build) the DailySaleRecord, preserving cash/winning
                    // data the operator may have already entered in Daily Sales Entry.
                    $rec = DailySaleRecord::firstOrNew([
                        'date'         => $date,
                        'assistant_id' => $assistantId,
                    ]);

                    // cw = cash + total_winning (already entered in Daily Sales Entry)
                    $existingCw = (float) ($rec->cw ?? 0);

                    $rec->tickets_issued_qty = $totalQty;
                    $rec->value              = $totalValue;
                    $rec->balance            = $totalValue - $existingCw;

                    // Leave unit_price at 0 — multiple lotteries with different prices
                    // cannot be collapsed to a single price. The operator can set it
                    // manually in the Daily Sales Entry screen if needed.
                    if (! $rec->exists) {
                        $rec->unit_price = 0;
                    }

                    $rec->save();

                    Log::info('[TicketDist] DailySaleRecord saved', [
                        'record_id' => $rec->id,
                        'qty'       => $rec->tickets_issued_qty,
                        'value'     => $rec->value,
                        'balance'   => $rec->balance,
                    ]);
                } else {
                    // Checkbox unchecked (or no-sales): zero out qty/value on any
                    // existing DailySaleRecord — do NOT create one if absent.
                    $rec = DailySaleRecord::where([
                        'date'         => $date,
                        'assistant_id' => $assistantId,
                    ])->first();

                    if ($rec) {
                        Log::info('[TicketDist] Not handed over — zeroing DailySaleRecord', [
                            'date'         => $date,
                            'assistant_id' => $assistantId,
                        ]);
                        $rec->tickets_issued_qty = 0;
                        $rec->value              = 0;
                        $rec->balance            = 0 - (float) ($rec->cw ?? 0);
                        $rec->save();
                    }
                }
            }
        });

        return redirect()
            ->route('ticket-distribution.index', ['date' => $date])
            ->with('success', 'Ticket distribution saved for ' . Carbon::parse($date)->format('d M Y') . '.');
    }

    // ── Excel Export ──────────────────────────────────────────────────────────

    /**
     * Stream an Excel download of the ticket distribution for a given date.
     * The row order matches the screen exactly: Route ASC (NULLs last), then ID ASC.
     */
    public function exportExcel(Request $request)
    {
        $date     = $request->input('date', today()->toDateString());
        $filename = 'ticket-distribution-' . $date . '.xlsx';

        return Excel::download(new TicketDistributionExport($date), $filename);
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
