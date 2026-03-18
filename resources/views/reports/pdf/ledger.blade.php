<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }

    /* ── Header ─────────────────────────────────────────────────── */
    .header { background: #0f172a; color: #fff; padding: 14px 20px; margin-bottom: 12px; }
    .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
    .brand h1 { font-size: 15px; font-weight: 700; }
    .brand p  { font-size: 8px; color: #94a3b8; margin-top: 2px; }
    .header-meta { text-align: right; }
    .header-meta .title { font-size: 12px; font-weight: 700; color: #93c5fd; }
    .header-meta p { font-size: 8px; color: #64748b; margin-top: 2px; }

    /* ── Assistant card ─────────────────────────────────────────── */
    .assistant-card {
        display: flex; gap: 16px; margin: 0 20px 12px;
        border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px;
        background: #f8fafc;
    }
    .assistant-card .field { flex: 1; }
    .assistant-card .field label { font-size: 7px; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.4px; }
    .assistant-card .field p { font-size: 10px; font-weight: 600; color: #0f172a; margin-top: 2px; }

    /* ── Opening balance ────────────────────────────────────────── */
    .ob-row {
        display: flex; justify-content: space-between;
        margin: 0 20px 8px; padding: 6px 12px;
        background: #eff6ff; border-left: 3px solid #3b82f6; border-radius: 0 4px 4px 0;
        font-size: 9px;
    }

    /* ── Ledger table ───────────────────────────────────────────── */
    table { width: calc(100% - 40px); margin: 0 20px 12px; border-collapse: collapse; }
    thead tr { background: #1e3a8a; color: #fff; }
    thead th {
        padding: 5px 7px; font-size: 8px; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.3px; text-align: right;
    }
    thead th:first-child, thead th:nth-child(2) { text-align: left; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody td { padding: 4px 7px; font-size: 8.5px; text-align: right; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    tbody td:first-child, tbody td:nth-child(2) { text-align: left; }
    tfoot tr { background: #0f172a; color: #fff; }
    tfoot td { padding: 5px 7px; font-size: 9px; font-weight: 700; text-align: right; }
    tfoot td:first-child { text-align: left; }

    /* ── Type badges ────────────────────────────────────────────── */
    .badge-debit  { background: #fee2e2; color: #b91c1c; border-radius: 10px; padding: 1px 7px; font-size: 7.5px; font-weight: 700; }
    .badge-credit { background: #dcfce7; color: #15803d; border-radius: 10px; padding: 1px 7px; font-size: 7.5px; font-weight: 700; }

    /* ── Summary boxes ──────────────────────────────────────────── */
    .summary-row { display: flex; gap: 8px; margin: 0 20px 8px; }
    .summary-box { flex: 1; border-radius: 5px; padding: 8px 10px; }
    .summary-box .s-label { font-size: 7px; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.3px; }
    .summary-box .s-val   { font-size: 13px; font-weight: 700; margin-top: 3px; }
    .box-blue   { background: #eff6ff; }
    .box-green  { background: #f0fdf4; }
    .box-balance-pos { background: #fef2f2; }
    .box-balance-neg { background: #f0fdf4; }

    /* ── Footer ─────────────────────────────────────────────────── */
    .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 5px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 7px; color: #94a3b8; }
    .page-break { page-break-after: always; }
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div class="header-top">
        <div class="brand">
            <h1>W.R Soysa — Lottery Agency</h1>
            <p>NLB · DLB Agent | Girandurukotte &amp; Mahiyanganya | 072-0673295</p>
        </div>
        <div class="header-meta">
            <div class="title">Ledger Statement</div>
            <p>{{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
            <p>Generated: {{ now()->format('d M Y H:i') }}</p>
        </div>
    </div>
</div>

{{-- Assistant Card --}}
<div class="assistant-card">
    <div class="field">
        <label>Assistant Name</label>
        <p>{{ $assistant->name }}</p>
    </div>
    <div class="field">
        <label>Phone</label>
        <p>{{ $assistant->phone }}</p>
    </div>
    <div class="field">
        <label>Address</label>
        <p>{{ $assistant->address }}</p>
    </div>
    <div class="field">
        <label>Current Overall Balance</label>
        <p style="color:{{ $assistant->current_balance > 0 ? '#dc2626' : '#16a34a' }}">
            Rs.{{ number_format($assistant->current_balance, 2) }}
        </p>
    </div>
</div>

{{-- Opening Balance --}}
<div class="ob-row">
    <span>Opening Balance (before {{ \Carbon\Carbon::parse($from)->format('d M Y') }})</span>
    <strong>Rs.{{ number_format($openingBalance, 2) }}</strong>
</div>

{{-- Summary boxes --}}
@php
    $totalDebits  = $entries->where('type', 'debit')->sum('amount');
    $totalCredits = $entries->where('type', 'credit')->sum('amount');
    $closingBal   = $openingBalance + $totalDebits - $totalCredits;
@endphp
<div class="summary-row">
    <div class="summary-box box-blue">
        <div class="s-label">Total Debits</div>
        <div class="s-val" style="color:#1d4ed8">Rs.{{ number_format($totalDebits, 2) }}</div>
    </div>
    <div class="summary-box box-green">
        <div class="s-label">Total Credits</div>
        <div class="s-val" style="color:#15803d">Rs.{{ number_format($totalCredits, 2) }}</div>
    </div>
    <div class="summary-box {{ $closingBal > 0 ? 'box-balance-pos' : 'box-balance-neg' }}">
        <div class="s-label">Closing Balance</div>
        <div class="s-val" style="color:{{ $closingBal > 0 ? '#dc2626' : '#15803d' }}">
            Rs.{{ number_format(abs($closingBal), 2) }}
            {{ $closingBal > 0 ? '(Owes)' : ($closingBal < 0 ? '(Excess)' : '') }}
        </div>
    </div>
    <div class="summary-box" style="background:#f8fafc">
        <div class="s-label">Entries in Period</div>
        <div class="s-val" style="color:#334155">{{ $entries->count() }}</div>
    </div>
</div>

{{-- Ledger Entries --}}
<table>
    <thead>
        <tr>
            <th style="text-align:left">#</th>
            <th style="text-align:left">Date</th>
            <th style="text-align:left">Type</th>
            <th>Debit (Rs.)</th>
            <th>Credit (Rs.)</th>
            <th>Running Balance</th>
        </tr>
    </thead>
    <tbody>
        {{-- Opening row --}}
        <tr style="background:#f0f9ff">
            <td></td>
            <td>B/F</td>
            <td></td>
            <td>—</td>
            <td>—</td>
            <td><b>{{ number_format($openingBalance, 2) }}</b></td>
        </tr>
        @foreach($entries as $i => $e)
        <tr>
            <td style="color:#94a3b8">{{ $i + 1 }}</td>
            <td>{{ $e->date->format('d M Y') }}</td>
            <td>
                <span class="{{ $e->type === 'debit' ? 'badge-debit' : 'badge-credit' }}">
                    {{ strtoupper($e->type) }}
                </span>
            </td>
            <td style="color:{{ $e->type==='debit'?'#dc2626':'#94a3b8' }}">
                {{ $e->type === 'debit' ? number_format($e->amount, 2) : '—' }}
            </td>
            <td style="color:{{ $e->type==='credit'?'#16a34a':'#94a3b8' }}">
                {{ $e->type === 'credit' ? number_format($e->amount, 2) : '—' }}
            </td>
            <td style="font-weight:600;color:{{ $e->running_balance>0?'#dc2626':($e->running_balance<0?'#16a34a':'#64748b') }}">
                {{ number_format($e->running_balance, 2) }}
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">CLOSING BALANCE</td>
            <td>{{ number_format($totalDebits, 2) }}</td>
            <td>{{ number_format($totalCredits, 2) }}</td>
            <td>Rs.{{ number_format($closingBal, 2) }}</td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    <span>{{ $assistant->name }} — Ledger Statement | W.R Soysa Lottery Agency | Confidential</span>
    <span>Printed: {{ now()->format('d M Y H:i:s') }}</span>
</div>

</body>
</html>
