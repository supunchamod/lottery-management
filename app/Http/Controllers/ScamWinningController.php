<?php

namespace App\Http\Controllers;

use App\Models\DailySaleRecord;
use App\Models\SalesAssistant;
use App\Models\ScamWinning;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScamWinningController extends Controller
{
    // ── List all scam winning records ─────────────────────────────────────────
    public function index(Request $request)
    {
        $assistants = SalesAssistant::orderBy('name')->get();

        $query = ScamWinning::with('assistant')
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($request->filled('assistant_id')) {
            $query->where('assistant_id', $request->input('assistant_id'));
        }
        if ($request->filled('paid')) {
            $query->where('is_paid_back', $request->input('paid') === '1');
        }
        if ($request->filled('barcode')) {
            $query->where('ticket_barcode', 'like', '%' . $request->input('barcode') . '%');
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->input('to'));
        }

        $scams = $query->paginate(50)->withQueryString();

        $totals = [
            'total_reported' => ScamWinning::sum('reported_winning_value'),
            'total_actual'   => ScamWinning::sum('actual_winning_value'),
            'total_diff'     => ScamWinning::sum('difference'),
            'total_paid'     => ScamWinning::sum('paid_back_amount'),
            'total_owed'     => ScamWinning::where('is_paid_back', false)
                                    ->selectRaw('SUM(difference - paid_back_amount) as owed')
                                    ->value('owed') ?? 0,
        ];

        return view('scam-winnings.index', compact('scams', 'assistants', 'totals'));
    }

    // ── Show the create form ──────────────────────────────────────────────────
    public function create()
    {
        $assistants = SalesAssistant::orderBy('name')->get();
        return view('scam-winnings.create', compact('assistants'));
    }

    // ── Store a new scam winning record ───────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'assistant_id'           => 'required|exists:sales_assistants,id',
            'date'                   => 'required|date',
            'ticket_barcode'         => 'required|string|max:120',
            'reported_winning_value' => 'required|numeric|min:0',
            'actual_winning_value'   => 'required|numeric|min:0',
            'notes'                  => 'nullable|string|max:500',
        ]);

        $data['difference'] = max(0, (float) $data['reported_winning_value'] - (float) $data['actual_winning_value']);

        // Link to the daily sale record for this assistant on the given date
        $record = DailySaleRecord::where([
            'assistant_id' => $data['assistant_id'],
            'date'         => $data['date'],
        ])->first();

        if ($record) {
            $data['daily_sale_record_id'] = $record->id;
        }

        ScamWinning::create($data);

        return redirect()
            ->route('scam-winnings.index')
            ->with('success', 'Scam ticket recorded successfully.');
    }

    // ── API: check a barcode against scam log & return alert if history found ─
    public function checkBarcode(Request $request)
    {
        $request->validate([
            'barcode'      => 'required|string|max:120',
            'assistant_id' => 'nullable|exists:sales_assistants,id',
        ]);

        $barcode = $request->input('barcode');
        $assistantId = $request->input('assistant_id');

        // Look up this exact barcode in scam log
        $barcodeScams = ScamWinning::with('assistant')
            ->where('ticket_barcode', $barcode)
            ->get();

        // If assistant_id given, check their overall scam history
        $assistantHistory = [];
        if ($assistantId) {
            $assistantHistory = ScamWinning::with('assistant')
                ->where('assistant_id', $assistantId)
                ->orderByDesc('date')
                ->limit(10)
                ->get()
                ->toArray();
        }

        return response()->json([
            'barcode_scams'     => $barcodeScams->toArray(),
            'assistant_history' => $assistantHistory,
            'has_alert'         => $barcodeScams->isNotEmpty() || count($assistantHistory) > 0,
        ]);
    }

    // ── Mark a scam as paid back (partial or full) ────────────────────────────
    public function markPaidBack(Request $request, ScamWinning $scamWinning)
    {
        $request->validate([
            'paid_back_amount' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request, $scamWinning) {
            $newPaid = (float) $scamWinning->paid_back_amount + (float) $request->input('paid_back_amount');
            $newPaid = min($newPaid, (float) $scamWinning->difference);

            $scamWinning->paid_back_amount = $newPaid;

            if ($newPaid >= (float) $scamWinning->difference) {
                $scamWinning->is_paid_back    = true;
                $scamWinning->paid_back_date  = today();

                // ── Financial Sync: reduce balance in linked daily record ──────
                if ($scamWinning->daily_sale_record_id) {
                    $rec = DailySaleRecord::find($scamWinning->daily_sale_record_id);
                    if ($rec && $rec->balance > 0) {
                        $rec->balance = max(0, (float) $rec->balance - (float) $scamWinning->difference);
                        $rec->saveQuietly();
                    }
                }
            }

            $scamWinning->save();
        });

        return redirect()
            ->route('scam-winnings.index')
            ->with('success', 'Scam payback recorded successfully.');
    }

    // ── Delete a scam record ──────────────────────────────────────────────────
    public function destroy(ScamWinning $scamWinning)
    {
        $scamWinning->delete();
        return redirect()->route('scam-winnings.index')->with('success', 'Scam record deleted.');
    }
}
