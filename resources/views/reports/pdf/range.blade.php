<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; background: #fff; }

    /* ── Header ─────────────────────────────────────────────────── */
    .header { background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); color: #fff; padding: 16px 20px 14px; margin-bottom: 14px; }
    .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
    .brand h1   { font-size: 17px; font-weight: 700; letter-spacing: 0.3px; }
    .brand p    { font-size: 7.5px; color: #bfdbfe; margin-top: 2px; }
    .header-meta { text-align: right; }
    .header-meta .report-title { font-size: 12px; font-weight: 700; }
    .header-meta .date-range   { font-size: 10px; color: #93c5fd; margin-top: 3px; }
    .header-meta .generated    { font-size: 7px; color: #bfdbfe; margin-top: 2px; }
    .header-divider { border-top: 1px solid rgba(255,255,255,0.15); margin-top: 10px; padding-top: 8px; display: flex; gap: 20px; }
    .header-stat { }
    .header-stat .hs-label { font-size: 7px; color: #bfdbfe; text-transform: uppercase; letter-spacing: 0.4px; }
    .header-stat .hs-val   { font-size: 11px; font-weight: 700; margin-top: 1px; }

    /* ── Section title ──────────────────────────────────────────── */
    .section-title {
        font-size: 8.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: #64748b;
        border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin: 12px 20px 7px;
    }

    /* ── KPI boxes ──────────────────────────────────────────────── */
    .kpi-row   { display: flex; gap: 6px; margin: 0 20px 12px; }
    .kpi-box   { flex: 1; border: 1px solid #e2e8f0; border-radius: 5px; padding: 7px 9px; background: #f8fafc; }
    .kpi-top   { display: flex; justify-content: space-between; align-items: center; }
    .kpi-label { font-size: 7px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.3px; }
    .kpi-val   { font-size: 13px; font-weight: 700; margin-top: 3px; }
    .kpi-sub   { font-size: 7px; color: #94a3b8; margin-top: 2px; }

    /* ── Tables ─────────────────────────────────────────────────── */
    table { width: calc(100% - 40px); margin: 0 20px 12px; border-collapse: collapse; }
    thead tr { background: #1e40af; color: #fff; }
    thead th { padding: 5px 6px; text-align: right; font-size: 7.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
    thead th:first-child { text-align: left; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody td { padding: 4px 6px; font-size: 8.5px; text-align: right; border-bottom: 1px solid #f1f5f9; }
    tbody td:first-child { text-align: left; }
    tfoot tr { background: #1e3a8a; color: #fff; }
    tfoot td { padding: 5px 6px; font-size: 9px; font-weight: 700; text-align: right; }
    tfoot td:first-child { text-align: left; }

    /* ── Profit / Loss highlight ─────────────────────────────────── */
    .profit-positive { color: #16a34a; }
    .profit-negative { color: #dc2626; }

    /* ── Net profit summary box ──────────────────────────────────── */
    .summary-box { margin: 0 20px 12px; padding: 10px 14px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; }
    .summary-positive { background: #dcfce7; border-left: 4px solid #16a34a; }
    .summary-negative { background: #fee2e2; border-left: 4px solid #dc2626; }
    .summary-box .sb-label { font-size: 10px; font-weight: 600; }
    .summary-box .sb-sub   { font-size: 7.5px; color: #64748b; margin-top: 2px; }
    .summary-box .sb-val   { font-size: 18px; font-weight: 800; }

    /* ── Assistant grid ──────────────────────────────────────────── */
    .assist-grid { display: flex; flex-wrap: wrap; gap: 6px; margin: 0 20px 12px; }
    .assist-card { width: 120px; border: 1px solid #e2e8f0; border-radius: 5px; padding: 6px 8px; background: #f8fafc; }
    .assist-name { font-size: 8px; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .assist-cash { font-size: 10px; font-weight: 700; color: #0f766e; margin-top: 2px; }
    .assist-rate { font-size: 7px; color: #94a3b8; margin-top: 1px; }

    /* ── Footer ─────────────────────────────────────────────────── */
    .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 5px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 7px; color: #94a3b8; }

    /* Page break utility */
    .page-break { page-break-after: always; }
</style>
</head>
<body>

{{-- ══ HEADER ══════════════════════════════════════════════════════════════ --}}
<div class="header">
    <div class="header-top">
        <div class="brand">
            <h1>W.R Soysa — Lottery Agency</h1>
            <p>NLB · DLB Agent &nbsp;|&nbsp; Girandurukotte &amp; Mahiyanganya &nbsp;|&nbsp; 072-0673295 / 078-4766684</p>
        </div>
        <div class="header-meta">
            <div class="report-title">Profit &amp; Loss Report</div>
            <div class="date-range">
                {{ \Carbon\Carbon::parse($from)->format('d M Y') }} &nbsp;→&nbsp; {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            </div>
            <div class="generated">Generated: {{ now()->format('d M Y H:i') }}</div>
        </div>
    </div>
    <div class="header-divider">
        <div class="header-stat">
            <div class="hs-label">Gross Commission</div>
            <div class="hs-val">Rs.{{ number_format($totals['gross_commission'], 2) }}</div>
        </div>
        <div class="header-stat">
            <div class="hs-label">Total Expenses</div>
            <div class="hs-val">Rs.{{ number_format($totals['total_expenses'], 2) }}</div>
        </div>
        <div class="header-stat">
            <div class="hs-label">Net Profit</div>
            <div class="hs-val" style="{{ $totals['net_profit'] >= 0 ? 'color:#4ade80' : 'color:#f87171' }}">
                Rs.{{ number_format(abs($totals['net_profit']), 2) }}
                {{ $totals['net_profit'] >= 0 ? '' : '(Loss)' }}
            </div>
        </div>
        <div class="header-stat">
            <div class="hs-label">Cash Collected</div>
            <div class="hs-val">Rs.{{ number_format($totals['cash_collected'], 2) }}</div>
        </div>
        <div class="header-stat">
            <div class="hs-label">Outstanding</div>
            <div class="hs-val" style="color:#fbbf24">Rs.{{ number_format($totals['outstanding'], 2) }}</div>
        </div>
        <div class="header-stat">
            <div class="hs-label">Active Days</div>
            <div class="hs-val">{{ $activeRows->count() }}</div>
        </div>
    </div>
</div>

{{-- ══ KPI SUMMARY ══════════════════════════════════════════════════════════ --}}
<div class="kpi-row">
    <div class="kpi-box">
        <div class="kpi-label">Total Issued</div>
        <div class="kpi-val" style="color:#1e40af">Rs.{{ number_format($totals['issued_val'], 2) }}</div>
        <div class="kpi-sub">Gross ticket value</div>
    </div>
    <div class="kpi-box">
        <div class="kpi-label">Returns</div>
        <div class="kpi-val" style="color:#c2410c">Rs.{{ number_format($totals['returns_val'], 2) }}</div>
        <div class="kpi-sub">Unsold returned</div>
    </div>
    <div class="kpi-box">
        <div class="kpi-label">Winnings Paid</div>
        <div class="kpi-val" style="color:#7c3aed">Rs.{{ number_format($totals['total_winning'], 2) }}</div>
        <div class="kpi-sub">NLB + DLB combined</div>
    </div>
    <div class="kpi-box">
        <div class="kpi-label">Gross Value</div>
        <div class="kpi-val" style="color:#0e7490">Rs.{{ number_format($totals['gross_value'], 2) }}</div>
        <div class="kpi-sub">Issued @ face value</div>
    </div>
    <div class="kpi-box">
        <div class="kpi-label">Commission Rate</div>
        @php
            $rate = $totals['gross_value'] > 0
                ? round(($totals['gross_commission'] / $totals['gross_value']) * 100, 2)
                : 0;
        @endphp
        <div class="kpi-val" style="color:#0f766e">{{ $rate }}%</div>
        <div class="kpi-sub">Avg across lotteries</div>
    </div>
</div>

{{-- ══ DAILY BREAKDOWN TABLE ═══════════════════════════════════════════════ --}}
<div class="section-title">Daily Breakdown — {{ $activeRows->count() }} active days</div>
<table>
    <thead>
        <tr>
            <th style="text-align:left">Date</th>
            <th>Issued (Rs.)</th>
            <th>Returns</th>
            <th>Winnings</th>
            <th>Cash</th>
            <th>Commission</th>
            <th>Expenses</th>
            <th>Net Profit</th>
            <th>Outstanding</th>
        </tr>
    </thead>
    <tbody>
        @foreach($activeRows as $r)
        <tr>
            <td style="font-weight:600">
                {{ \Carbon\Carbon::parse($r['date'])->format('d M Y') }}
                <span style="color:#94a3b8;font-size:7.5px">({{ $r['records'] }})</span>
            </td>
            <td>{{ number_format($r['issued_val'], 0) }}</td>
            <td style="color:#c2410c">{{ number_format($r['returns_val'], 0) }}</td>
            <td style="color:#7c3aed">{{ number_format($r['total_winning'], 0) }}</td>
            <td style="color:#0f766e">{{ number_format($r['cash_collected'], 0) }}</td>
            <td style="color:#1d4ed8;font-weight:600">{{ number_format($r['gross_commission'], 2) }}</td>
            <td style="color:#be123c">{{ number_format($r['total_expenses'], 2) }}</td>
            <td style="{{ $r['net_profit'] >= 0 ? 'color:#16a34a' : 'color:#dc2626' }};font-weight:700">
                {{ number_format($r['net_profit'], 2) }}
            </td>
            <td style="{{ $r['outstanding'] > 0 ? 'color:#c2410c' : 'color:#94a3b8' }}">
                {{ number_format($r['outstanding'], 0) }}
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>TOTALS ({{ $activeRows->count() }} days)</td>
            <td>{{ number_format($totals['issued_val'], 0) }}</td>
            <td>{{ number_format($totals['returns_val'], 0) }}</td>
            <td>{{ number_format($totals['total_winning'], 0) }}</td>
            <td>{{ number_format($totals['cash_collected'], 0) }}</td>
            <td>{{ number_format($totals['gross_commission'], 2) }}</td>
            <td>{{ number_format($totals['total_expenses'], 2) }}</td>
            <td>Rs.{{ number_format($totals['net_profit'], 2) }}</td>
            <td>{{ number_format($totals['outstanding'], 0) }}</td>
        </tr>
    </tfoot>
</table>

{{-- ══ NET PROFIT SUMMARY BOX ══════════════════════════════════════════════ --}}
<div class="summary-box {{ $totals['net_profit'] >= 0 ? 'summary-positive' : 'summary-negative' }}">
    <div>
        <div class="sb-label">{{ $totals['net_profit'] >= 0 ? 'Net Profit' : 'Net Loss' }}
            — {{ \Carbon\Carbon::parse($from)->format('d M Y') }} to {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
        </div>
        <div class="sb-sub">
            Gross Commission Rs.{{ number_format($totals['gross_commission'], 2) }}
            &minus; Total Expenses Rs.{{ number_format($totals['total_expenses'], 2) }}
        </div>
    </div>
    <div class="sb-val" style="color:{{ $totals['net_profit'] >= 0 ? '#16a34a' : '#dc2626' }}">
        Rs.{{ number_format(abs($totals['net_profit']), 2) }}
    </div>
</div>

{{-- ══ COMMISSION BY LOTTERY ════════════════════════════════════════════════ --}}
@if($commissionByLottery->count())
<div class="section-title">Commission Breakdown by Lottery</div>
<table>
    <thead>
        <tr>
            <th style="text-align:left">Lottery Name</th>
            <th style="text-align:center">Board</th>
            <th>Qty Issued</th>
            <th>Gross Value (Rs.)</th>
            <th>Commission (Rs.)</th>
            <th>Rate</th>
        </tr>
    </thead>
    <tbody>
        @foreach($commissionByLottery as $c)
        <tr>
            <td style="font-weight:600">{{ $c->name }}</td>
            <td style="text-align:center;color:{{ $c->board === 'NLB' ? '#1d4ed8' : '#ea580c' }};font-weight:700">
                {{ $c->board }}
            </td>
            <td>{{ number_format($c->total_qty) }}</td>
            <td>{{ number_format($c->gross_value, 2) }}</td>
            <td style="color:#1d4ed8;font-weight:600">{{ number_format($c->commission, 2) }}</td>
            <td style="color:#0f766e">
                {{ $c->gross_value > 0 ? round(($c->commission / $c->gross_value) * 100, 2) : 0 }}%
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">TOTAL COMMISSION</td>
            <td>Rs.{{ number_format($commissionByLottery->sum('gross_value'), 2) }}</td>
            <td>Rs.{{ number_format($commissionByLottery->sum('commission'), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
@endif

{{-- ══ TOP ASSISTANTS ═══════════════════════════════════════════════════════ --}}
@if($topAssistants->count())
<div class="section-title">Top Assistants — by Cash Collected</div>
<div class="assist-grid">
    @foreach($topAssistants as $a)
    @php
        $issued = (float)($a->total_issued ?? 0);
        $cash   = (float)($a->total_cash   ?? 0);
        $rate   = $issued > 0 ? round(($cash / $issued) * 100, 1) : 0;
    @endphp
    <div class="assist-card">
        <div class="assist-name">{{ $a->name }}</div>
        <div class="assist-cash">Rs.{{ number_format($cash, 0) }}</div>
        <div class="assist-rate">Collection rate: {{ $rate }}%</div>
    </div>
    @endforeach
</div>
@endif

{{-- ══ FOOTER ══════════════════════════════════════════════════════════════ --}}
<div class="footer">
    <span>W.R Soysa Lottery Agency — Confidential &nbsp;|&nbsp; NLB · DLB Licensed Agent</span>
    <span>Generated: {{ now()->format('d M Y H:i:s') }}</span>
</div>

</body>
</html>
