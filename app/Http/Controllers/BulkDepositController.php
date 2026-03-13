<?php

namespace App\Http\Controllers;

use App\Models\BulkDeposit;
use App\Models\DailySaleRecord;
use App\Models\SalesAssistant;
use App\Services\LedgerService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkDepositController extends Controller
{
    public function __construct(private readonly LedgerService $ledger) {}

    // ── Buffer list ───────────────────────────────────────────────────────────

    public function index()
    {
        $bulkDeposits = BulkDeposit::with('assistant', 'createdBy')
            ->latest()
            ->paginate(20);

        return view('bulk-deposits.index', compact('bulkDeposits'));
    }

    // ── Create form ───────────────────────────────────────────────────────────

    public function create()
    {
        $assistants = SalesAssistant::orderBy('name')->get();

        return view('bulk-deposits.create', compact('assistants'));
    }

    // ── Store new bulk deposit ────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $this->validateForm($request);

        // ── Pending-duplicate guard ───────────────────────────────────────────
        $hasPending = BulkDeposit::where('assistant_id', $data['assistant_id'])
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return back()->withInput()->withErrors([
                'assistant_id' => 'This assistant already has a pending bulk record. Please process it before adding a new one.',
            ]);
        }

        $deposit = new BulkDeposit($this->fillableFields($data));
        $deposit->status     = 'pending';
        $deposit->created_by = auth()->id();
        $deposit->compute();
        $deposit->save();

        return redirect()
            ->route('bulk-deposits.index')
            ->with('success', 'Bulk deposit record created successfully.');
    }

    // ── Edit form ─────────────────────────────────────────────────────────────

    public function edit(BulkDeposit $bulkDeposit)
    {
        abort_if($bulkDeposit->isCompleted(), 403, 'Completed bulk deposits cannot be edited.');

        $assistants = SalesAssistant::orderBy('name')->get();

        return view('bulk-deposits.edit', compact('bulkDeposit', 'assistants'));
    }

    // ── Update bulk deposit ───────────────────────────────────────────────────

    public function update(Request $request, BulkDeposit $bulkDeposit)
    {
        abort_if($bulkDeposit->isCompleted(), 403, 'Completed bulk deposits cannot be edited.');

        $data = $this->validateForm($request, $bulkDeposit->id);

        // If assistant changed, re-run the pending-duplicate guard
        if ((int) $data['assistant_id'] !== (int) $bulkDeposit->assistant_id) {
            $hasPending = BulkDeposit::where('assistant_id', $data['assistant_id'])
                ->where('status', 'pending')
                ->exists();

            if ($hasPending) {
                return back()->withInput()->withErrors([
                    'assistant_id' => 'This assistant already has a pending bulk record.',
                ]);
            }
        }

        $bulkDeposit->fill($this->fillableFields($data));
        $bulkDeposit->compute();
        $bulkDeposit->save();

        return redirect()
            ->route('bulk-deposits.index')
            ->with('success', 'Bulk deposit updated successfully.');
    }

    // ── Delete bulk deposit ───────────────────────────────────────────────────

    public function destroy(BulkDeposit $bulkDeposit)
    {
        $bulkDeposit->load('assistant');
        $assistantName = $bulkDeposit->assistant->name ?? 'Unknown';

        DB::transaction(function () use ($bulkDeposit) {
            // If already distributed: remove the daily records that were created
            // from this bulk deposit's date range so no orphan data remains.
            if ($bulkDeposit->isCompleted()) {
                $assistant = $bulkDeposit->assistant;
                $dateRange = [
                    $bulkDeposit->date_from->toDateString(),
                    $bulkDeposit->date_to->toDateString(),
                ];

                DailySaleRecord::where('assistant_id', $bulkDeposit->assistant_id)
                    ->whereBetween('date', $dateRange)
                    ->delete();

                // Rebuild the ledger running balance so it stays consistent
                if ($assistant) {
                    $this->ledger->recalculateRunningBalance($assistant);
                }
            }

            $bulkDeposit->delete();
        });

        return redirect()
            ->route('bulk-deposits.index')
            ->with('success', "Bulk deposit for {$assistantName} deleted successfully.");
    }

    // ── Distribution form ─────────────────────────────────────────────────────

    public function distribute(BulkDeposit $bulkDeposit)
    {
        abort_if($bulkDeposit->isCompleted(), 403, 'This bulk deposit has already been distributed.');

        $bulkDeposit->load('assistant');

        $dates = collect(
            CarbonPeriod::create($bulkDeposit->date_from, $bulkDeposit->date_to)
        )->map(fn (Carbon $d) => $d->toDateString())->values();

        $existing = DailySaleRecord::where('assistant_id', $bulkDeposit->assistant_id)
            ->whereBetween('date', [
                $bulkDeposit->date_from->toDateString(),
                $bulkDeposit->date_to->toDateString(),
            ])
            ->get()
            ->keyBy(fn ($r) => $r->date->toDateString());

        $alpineRows = [];
        foreach ($dates as $date) {
            $r = $existing->get($date);
            $alpineRows[$date] = [
                'qty'        => $r->tickets_issued_qty ?? 0,
                'unitPrice'  => $r ? (float) $r->unit_price : 40,
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

        return view('bulk-deposits.distribute', compact(
            'bulkDeposit', 'dates', 'alpineRows'
        ));
    }

    // ── Save distribution ─────────────────────────────────────────────────────

    public function saveDistribution(Request $request, BulkDeposit $bulkDeposit)
    {
        abort_if($bulkDeposit->isCompleted(), 403, 'This bulk deposit has already been distributed.');

        $request->validate([
            'rows'              => 'nullable|array',
            'rows.*.qty'        => 'nullable|integer|min:0',
            'rows.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        $rows        = $request->input('rows', []);
        $assistantId = $bulkDeposit->assistant_id;
        $assistant   = SalesAssistant::findOrFail($assistantId);
        $from        = $bulkDeposit->date_from->toDateString();
        $to          = $bulkDeposit->date_to->toDateString();

        DB::transaction(function () use ($rows, $assistantId, $assistant, $from, $to, $bulkDeposit) {
            foreach ($rows as $date => $data) {
                if ($date < $from || $date > $to) {
                    continue;
                }

                $qty  = (int)   ($data['qty']         ?? 0);
                $up   = (float) ($data['unit_price']   ?? 0);
                $nlb  = (float) ($data['nlb_winning']  ?? 0);
                $dlb  = (float) ($data['dlb_winning']  ?? 0);
                $d20  = (int)   ($data['d20']   ?? 0);
                $d50  = (int)   ($data['d50']   ?? 0);
                $d100 = (int)   ($data['d100']  ?? 0);
                $d500 = (int)   ($data['d500']  ?? 0);
                $d1k  = (int)   ($data['d1000'] ?? 0);
                $d5k  = (int)   ($data['d5000'] ?? 0);

                $isEmpty = $qty === 0 && $nlb === 0 && $dlb === 0
                    && $d20 === 0 && $d50 === 0 && $d100 === 0
                    && $d500 === 0 && $d1k === 0 && $d5k === 0;

                if ($isEmpty) {
                    DailySaleRecord::where([
                        'date'         => $date,
                        'assistant_id' => $assistantId,
                    ])->delete();
                    continue;
                }

                $rec   = DailySaleRecord::firstOrNew(['date' => $date, 'assistant_id' => $assistantId]);
                $isNew = ! $rec->exists;

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
                $rec->save();

                $this->ledger->postOrUpdateDailySaleRecord($assistant, $rec, $isNew);
            }

            $bulkDeposit->update(['status' => 'completed']);
        });

        return redirect()
            ->route('bulk-deposits.index')
            ->with('success', "Bulk deposit for {$bulkDeposit->assistant->name} distributed successfully.");
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function validateForm(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'assistant_id' => 'required|exists:sales_assistants,id',
            'date_from'    => 'required|date',
            'date_to'      => 'required|date|after_or_equal:date_from',
            'total_qty'    => 'nullable|integer|min:0',
            'unit_price'   => 'nullable|numeric|min:0',
            'denom_5'      => 'nullable|integer|min:0',
            'denom_10'     => 'nullable|integer|min:0',
            'denom_20'     => 'nullable|integer|min:0',
            'denom_50'     => 'nullable|integer|min:0',
            'denom_100'    => 'nullable|integer|min:0',
            'denom_500'    => 'nullable|integer|min:0',
            'denom_1000'   => 'nullable|integer|min:0',
            'denom_5000'   => 'nullable|integer|min:0',
            'nlb_winning'  => 'nullable|numeric|min:0',
            'dlb_winning'  => 'nullable|numeric|min:0',
            'tw_winning'   => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string|max:500',
        ]);
    }

    private function fillableFields(array $data): array
    {
        return [
            'assistant_id' => $data['assistant_id'],
            'date_from'    => $data['date_from'],
            'date_to'      => $data['date_to'],
            'total_qty'    => (int)   ($data['total_qty']   ?? 0),
            'unit_price'   => (float) ($data['unit_price']  ?? 0),
            'denom_5'      => (int)   ($data['denom_5']     ?? 0),
            'denom_10'     => (int)   ($data['denom_10']    ?? 0),
            'denom_20'     => (int)   ($data['denom_20']    ?? 0),
            'denom_50'     => (int)   ($data['denom_50']    ?? 0),
            'denom_100'    => (int)   ($data['denom_100']   ?? 0),
            'denom_500'    => (int)   ($data['denom_500']   ?? 0),
            'denom_1000'   => (int)   ($data['denom_1000']  ?? 0),
            'denom_5000'   => (int)   ($data['denom_5000']  ?? 0),
            'nlb_winning'  => (float) ($data['nlb_winning'] ?? 0),
            'dlb_winning'  => (float) ($data['dlb_winning'] ?? 0),
            'tw_winning'   => (float) ($data['tw_winning']  ?? 0),
            'notes'        => $data['notes'] ?? null,
        ];
    }
}
