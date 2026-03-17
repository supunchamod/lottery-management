<?php

namespace App\Http\Controllers;

use App\Models\CreditLog;
use App\Models\DailySaleRecord;
use App\Models\SalesAssistant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditLogController extends Controller
{
    // ── List all credit logs ──────────────────────────────────────────────────
    public function index(Request $request)
    {
        $assistants = SalesAssistant::orderBy('name')->get();

        $query = CreditLog::with('assistant')
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($request->filled('assistant_id')) {
            $query->where('assistant_id', $request->input('assistant_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->input('to'));
        }

        $logs = $query->paginate(50)->withQueryString();

        $totals = [
            'total_amount'  => CreditLog::sum('amount'),
            'total_paid'    => CreditLog::sum('paid_amount'),
            'total_pending' => CreditLog::where('status', 'pending')->sum(DB::raw('amount - paid_amount')),
        ];

        return view('credit-logs.index', compact('logs', 'assistants', 'totals'));
    }

    // ── Mark a credit log as paid (partial or full) ───────────────────────────
    public function markPaid(Request $request, CreditLog $creditLog)
    {
        $request->validate([
            'paid_amount' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request, $creditLog) {
            $newPaid = (float) $creditLog->paid_amount + (float) $request->input('paid_amount');
            $newPaid = min($newPaid, (float) $creditLog->amount); // cap at total

            $creditLog->paid_amount = $newPaid;

            if ($newPaid >= (float) $creditLog->amount) {
                $creditLog->status  = 'paid';
                $creditLog->paid_at = today();

                // ── Financial Sync: zero out the linked daily record balance ──
                if ($creditLog->daily_sale_record_id) {
                    $rec = DailySaleRecord::find($creditLog->daily_sale_record_id);
                    if ($rec && $rec->balance > 0) {
                        // Reduce balance by the credited amount
                        $rec->balance = max(0, (float) $rec->balance - (float) $creditLog->amount);
                        $rec->saveQuietly();
                    }
                }
            }

            $creditLog->save();
        });

        return redirect()
            ->route('credit-logs.index')
            ->with('success', 'Credit log updated successfully.');
    }

    // ── Delete a credit log ───────────────────────────────────────────────────
    public function destroy(CreditLog $creditLog)
    {
        $creditLog->delete();
        return redirect()->route('credit-logs.index')->with('success', 'Credit log deleted.');
    }
}
