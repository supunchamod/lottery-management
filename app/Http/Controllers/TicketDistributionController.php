<?php

namespace App\Http\Controllers;

use App\Models\Lottery;
use App\Models\SalesAssistant;
use App\Models\SubSeller;
use App\Models\TicketDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketDistributionController extends Controller
{
    /**
     * Show the distribution grid for a given assistant + date.
     */
    public function index(Request $request)
    {
        $assistants = SalesAssistant::orderBy('name')->get();
        $lotteries  = Lottery::orderBy('board')->orderBy('name')->get();

        $assistantId = $request->input('assistant_id', $assistants->first()?->id);
        $date        = $request->input('date', today()->toDateString());

        $assistant = $assistants->firstWhere('id', $assistantId);

        // Sub-sellers belonging to this assistant
        $subSellers = SubSeller::where('assistant_id', $assistantId)
            ->active()
            ->orderBy('name')
            ->get();

        // All distributions for this assistant + date
        // Keyed as [sub_seller_id][lottery_id] => qty
        $distributions = TicketDistribution::where('assistant_id', $assistantId)
            ->where('date', $date)
            ->get()
            ->groupBy('sub_seller_id')
            ->map(fn ($rows) => $rows->keyBy('lottery_id')->map(fn ($r) => $r->qty));

        // Column totals  [lottery_id => total_qty]
        $colTotals = [];
        foreach ($lotteries as $lottery) {
            $colTotals[$lottery->id] = $distributions->sum(fn ($row) => $row->get($lottery->id, 0));
        }

        // Row totals  [sub_seller_id => total_qty]
        $rowTotals = [];
        foreach ($subSellers as $seller) {
            $rowTotals[$seller->id] = collect($lotteries)->sum(
                fn ($l) => $distributions->get($seller->id, collect())->get($l->id, 0)
            );
        }

        $grandTotal = array_sum($colTotals);

        return view('ticket-distribution.index', compact(
            'assistants', 'lotteries', 'assistant', 'assistantId',
            'date', 'subSellers', 'distributions', 'colTotals', 'rowTotals', 'grandTotal'
        ));
    }

    /**
     * Show the blank form to enter a new distribution day.
     */
    public function create(Request $request)
    {
        $assistants = SalesAssistant::orderBy('name')->get();
        $lotteries  = Lottery::orderBy('board')->orderBy('name')->get();

        $assistantId = $request->input('assistant_id', $assistants->first()?->id);
        $date        = $request->input('date', today()->toDateString());

        $subSellers = SubSeller::where('assistant_id', $assistantId)
            ->active()
            ->orderBy('name')
            ->get();

        // Pre-fill with any existing values (for edit-in-place)
        $existing = TicketDistribution::where('assistant_id', $assistantId)
            ->where('date', $date)
            ->get()
            ->groupBy('sub_seller_id')
            ->map(fn ($rows) => $rows->keyBy('lottery_id')->map(fn ($r) => $r->qty));

        return view('ticket-distribution.create', compact(
            'assistants', 'lotteries', 'assistantId', 'date', 'subSellers', 'existing'
        ));
    }

    /**
     * Save (upsert) the bulk distribution grid.
     */
    public function store(Request $request)
    {
        $request->validate([
            'assistant_id'    => 'required|exists:sales_assistants,id',
            'date'            => 'required|date',
            'qty'             => 'nullable|array',
            'qty.*.*'         => 'nullable|integer|min:0',
        ]);

        $assistantId = $request->input('assistant_id');
        $date        = $request->input('date');
        $grid        = $request->input('qty', []);  // [sub_seller_id][lottery_id] => qty

        DB::transaction(function () use ($assistantId, $date, $grid) {
            foreach ($grid as $subSellerId => $lotteryQtys) {
                foreach ($lotteryQtys as $lotteryId => $qty) {
                    $qty = (int) ($qty ?? 0);

                    if ($qty > 0) {
                        TicketDistribution::updateOrCreate(
                            [
                                'date'          => $date,
                                'sub_seller_id' => $subSellerId,
                                'lottery_id'    => $lotteryId,
                            ],
                            [
                                'assistant_id' => $assistantId,
                                'qty'          => $qty,
                            ]
                        );
                    } else {
                        // Remove zero-qty rows to keep the table clean
                        TicketDistribution::where([
                            'date'          => $date,
                            'sub_seller_id' => $subSellerId,
                            'lottery_id'    => $lotteryId,
                        ])->delete();
                    }
                }
            }
        });

        return redirect()
            ->route('ticket-distribution.index', [
                'assistant_id' => $assistantId,
                'date'         => $date,
            ])
            ->with('success', 'Ticket distribution saved successfully.');
    }

    // ── Sub-seller CRUD ────────────────────────────────────────────────────────

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
