<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; background: #fff; }

    /* ── Header ─────────────────────────────────────────────────── */
    .header { background: #1e3a8a; color: #fff; padding: 14px 20px; margin-bottom: 14px; }
    .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
    .brand h1  { font-size: 16px; font-weight: 700; letter-spacing: 0.3px; }
    .brand p   { font-size: 8px; color: #bfdbfe; margin-top: 2px; }
    .header-meta { text-align: right; }
    .header-meta .date-label { font-size: 11px; font-weight: 700; }
    .header-meta p { font-size: 8px; color: #bfdbfe; }

    /* ── Section title ──────────────────────────────────────────── */
    .section-title {
        font-size: 9px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: #64748b;
        border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin: 10px 20px 6px;
    }

    /* ── KPI boxes ──────────────────────────────────────────────── */
    .kpi-row { display: flex; gap: 6px; margin: 0 20px 10px; }
    .kpi-box {
        flex: 1; border: 1px solid #e2e8f0; border-radius: 5px;
        padding: 6px 8px; background: #f8fafc;
    }
    .kpi-box .kpi-label { font-size: 7px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.3px; }
    .kpi-box .kpi-val   { font-size: 12px; font-weight: 700; margin-top: 2px; }

    /* ── Tables ─────────────────────────────────────────────────── */
    table { width: calc(100% - 40px); margin: 0 20px 10px; border-collapse: collapse; }
    thead tr { background: #1e40af; color: #fff; }
    thead th { padding: 5px 6px; text-align: right; font-size: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
    thead th:first-child { text-align: left; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody td { padding: 4px 6px; font-size: 8.5px; text-align: right; border-bottom: 1px solid #f1f5f9; }
    tbody td:first-child { text-align: left; }
    tfoot tr { background: #1e3a8a; color: #fff; }
    tfoot td { padding: 5px 6px; font-size: 9px; font-weight: 700; text-align: right; }
    tfoot td:first-child { text-align: left; }

    /* ── NLB/DLB winning side-by-side ───────────────────────────── */
    .winning-grid { display: flex; gap: 10px; margin: 0 20px 10px; }
    .winning-board { flex: 1; }
    .board-header {
        padding: 5px 8px; color: #fff; font-size: 9px; font-weight: 700;
        border-radius: 4px 4px 0 0;
    }
    .nlb-header { background: #1d4ed8; }
    .dlb-header { background: #ea580c; }
    .winning-row { display: flex; justify-content: space-between; padding: 3px 8px; border-bottom: 1px solid #f1f5f9; font-size: 8px; }
    .winning-row:nth-child(even) { background: #f8fafc; }
    .board-total { display: flex; justify-content: space-between; padding: 4px 8px; font-weight: 700; font-size: 9px; }
    .nlb-total { background: #dbeafe; color: #1d4ed8; }
    .dlb-total { background: #ffedd5; color: #c2410c; }

    /* ── Footer ─────────────────────────────────────────────────── */
    .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 6px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 7px; color: #94a3b8; }
    .profit-box { margin: 0 20px 10px; padding: 10px 14px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; }
    .profit-positive { background: #dcfce7; border-left: 4px solid #16a34a; }
    .profit-negative { background: #fee2e2; border-left: 4px solid #dc2626; }
    .profit-box .profit-label { font-size: 10px; font-weight: 600; }
    .profit-box .profit-val   { font-size: 16px; font-weight: 800; }
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div class="header-top">
        <div class="brand">
            <h1>W.R Soysa — Lottery Agency</h1>
            <p>NLB · DLB Agent | Girandurukotte &amp; Mahiyanganya | 072-0673295 / 078-4766684</p>
        </div>
        <div class="header-meta">
            <div class="date-label">Daily Sales Summary</div>
            <p>{{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</p>
            <p>Generated: {{ now()->format('d M Y H:i') }}</p>
        </div>
    </div>
</div>

{{-- KPI Row --}}
<div class="kpi-row">
    <div class="kpi-box">
        <div class="kpi-label">Total Issued</div>
        <div class="kpi-val" style="color:#1e40af">Rs.{{ number_format($totals['issued'], 2) }}</div>
    </div>
    <div class="kpi-box">
        <div class="kpi-label">Cash Collected</div>
        <div class="kpi-val" style="color:#0f766e">Rs.{{ number_format($totals['cash'], 2) }}</div>
    </div>
    <div class="kpi-box">
        <div class="kpi-label">Total Winnings</div>
        <div class="kpi-val" style="color:#7c3aed">Rs.{{ number_format($totals['grand_winning'], 2) }}</div>
    </div>
    <div class="kpi-box">
        <div class="kpi-label">Total Expenses</div>
        <div class="kpi-val" style="color:#be123c">Rs.{{ number_format($totals['expenses'], 2) }}</div>
    </div>
    <div class="kpi-box">
        <div class="kpi-label">Outstanding</div>
        <div class="kpi-val" style="color:#c2410c">Rs.{{ number_format($totals['balance'], 2) }}</div>
    </div>
</div>

{{-- Sales Table --}}
<div class="section-title">Assistant Daily Sales</div>
<table>
    <thead>
        <tr>
            <th style="text-align:left">Assistant</th>
            <th>Issued Val</th>
            <th>Returns</th>
            <th>NLB Win</th>
            <th>DLB Win</th>
            <th>Cash</th>
            <th>C+W</th>
            <th>Balance</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $s)
        <tr>
            <td>{{ $s->assistant->name }}</td>
            <td>{{ number_format($s->tickets_issued_val, 0) }}</td>
            <td>{{ number_format($s->returns_val, 0) }}</td>
            <td>—</td>
            <td>—</td>
            <td>{{ number_format($s->cash_collected, 0) }}</td>
            <td>{{ number_format($s->cash_collected + $s->winning_val, 0) }}</td>
            <td style="{{ $s->balance > 0 ? 'color:#dc2626' : ($s->balance < 0 ? 'color:#16a34a' : '') }}">
                {{ number_format($s->balance, 0) }}
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>TOTALS ({{ $sales->count() }} assistants)</td>
            <td>{{ number_format($totals['issued'], 0) }}</td>
            <td>{{ number_format($totals['returns'], 0) }}</td>
            <td>{{ number_format($totals['nlb_winning'], 0) }}</td>
            <td>{{ number_format($totals['dlb_winning'], 0) }}</td>
            <td>{{ number_format($totals['cash'], 0) }}</td>
            <td>{{ number_format($totals['cash'] + $totals['grand_winning'], 0) }}</td>
            <td>{{ number_format($totals['balance'], 0) }}</td>
        </tr>
    </tfoot>
</table>

{{-- NLB / DLB Winning --}}
@if($winning)
<div class="section-title">Winning Breakdown — NLB &amp; DLB</div>
<div class="winning-grid">
    {{-- NLB --}}
    <div class="winning-board">
        <div class="board-header nlb-header">NLB – National Lottery Board</div>
        @foreach(\App\Models\Winning::NLB_TIERS as $col => $denom)
            @php $qty = $winning->{$col} ?? 0; @endphp
            @if($qty > 0)
            <div class="winning-row">
                <span>Rs.{{ number_format($denom) }}</span>
                <span>{{ $qty }} × {{ number_format($denom) }}</span>
                <span><b>Rs.{{ number_format($qty * $denom) }}</b></span>
            </div>
            @endif
        @endforeach
        <div class="board-total nlb-total">
            <span>NLB TOTAL</span>
            <span>Rs.{{ number_format($winning->nlb_total, 2) }}</span>
        </div>
    </div>
    {{-- DLB --}}
    <div class="winning-board">
        <div class="board-header dlb-header">DLB – Development Lottery Board</div>
        @foreach(\App\Models\Winning::DLB_TIERS as $col => $denom)
            @php $qty = $winning->{$col} ?? 0; @endphp
            @if($qty > 0)
            <div class="winning-row">
                <span>Rs.{{ number_format($denom) }}</span>
                <span>{{ $qty }} × {{ number_format($denom) }}</span>
                <span><b>Rs.{{ number_format($qty * $denom) }}</b></span>
            </div>
            @endif
        @endforeach
        <div class="board-total dlb-total">
            <span>DLB TOTAL</span>
            <span>Rs.{{ number_format($winning->dlb_total, 2) }}</span>
        </div>
    </div>
</div>
@endif

{{-- Commission --}}
@if($commission->count())
<div class="section-title">Commission Breakdown</div>
<table>
    <thead>
        <tr>
            <th style="text-align:left">Lottery</th>
            <th>Board</th>
            <th>Commission (Rs.)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($commission as $c)
        <tr>
            <td>{{ $c->name }}</td>
            <td style="text-align:center">{{ $c->board }}</td>
            <td>{{ number_format($c->commission, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">GROSS COMMISSION</td>
            <td>Rs.{{ number_format($totals['commission'], 2) }}</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- Expenses --}}
@if($expenses->count())
<div class="section-title">Expenses</div>
<table>
    <thead>
        <tr>
            <th style="text-align:left">Title</th>
            <th style="text-align:left">Description</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($expenses as $e)
        <tr>
            <td>{{ $e->title }}</td>
            <td>{{ $e->description }}</td>
            <td>{{ number_format($e->amount, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">TOTAL EXPENSES</td>
            <td>Rs.{{ number_format($totals['expenses'], 2) }}</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- Net Profit Box --}}
<div class="profit-box {{ $totals['net_profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
    <div>
        <div class="profit-label">{{ $totals['net_profit'] >= 0 ? 'Net Profit' : 'Net Loss' }} for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</div>
        <div style="font-size:8px;color:#64748b;margin-top:2px">
            Gross Commission Rs.{{ number_format($totals['commission'], 2) }}
            − Expenses Rs.{{ number_format($totals['expenses'], 2) }}
        </div>
    </div>
    <div class="profit-val" style="color:{{ $totals['net_profit'] >= 0 ? '#16a34a' : '#dc2626' }}">
        Rs.{{ number_format(abs($totals['net_profit']), 2) }}
    </div>
</div>

<div class="footer">
    <span>W.R Soysa Lottery Agency — Confidential</span>
    <span>Printed: {{ now()->format('d M Y H:i:s') }}</span>
</div>

</body>
</html>
