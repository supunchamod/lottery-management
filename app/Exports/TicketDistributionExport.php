<?php

namespace App\Exports;

use App\Models\DailyTicketNote;
use App\Models\DailyTicketStock;
use App\Models\Lottery;
use App\Models\SalesAssistant;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TicketDistributionExport implements FromArray, ShouldAutoSize, WithTitle, WithEvents
{
    // ── Pre-loaded data ───────────────────────────────────────────────────────

    private Collection $lotteries;
    private array      $grid      = [];   // [assistant_id][lottery_id] => qty
    private Collection $notes;            // keyed by assistant_id

    // ── Aggregation (populated while building rows) ───────────────────────────

    private array $colTotals  = [];       // [lottery_id => total]
    private int   $grandTotal = 0;
    private int   $seqNum     = 0;        // sequential # printed on each assistant row

    // ── Row-type tracking (1-based, pre-insert) used by AfterSheet ───────────

    private array $routeHeaderRows = [];  // rows that carry a route heading
    private array $assistantRows   = [];  // rows that carry an assistant's data

    // ── Lazy-build guard ──────────────────────────────────────────────────────

    private array $builtRows = [];
    private bool  $built     = false;

    // ─────────────────────────────────────────────────────────────────────────

    public function __construct(private readonly string $date)
    {
        // Lotteries in the same sort order as the screen
        $this->lotteries = Lottery::orderByRaw('sort_order IS NULL, sort_order ASC, created_at ASC')->get();

        // Ticket quantities keyed by [assistant_id][lottery_id]
        foreach (DailyTicketStock::where('date', $date)->get() as $r) {
            $this->grid[$r->assistant_id][$r->lottery_id] = (int) $r->quantity;
        }

        // No-sales flags and remarks keyed by assistant_id
        $this->notes = DailyTicketNote::where('date', $date)->get()->keyBy('assistant_id');

        // Initialise column totals for every lottery
        foreach ($this->lotteries as $l) {
            $this->colTotals[$l->id] = 0;
        }
    }

    // ── Sheet tab title ───────────────────────────────────────────────────────

    public function title(): string
    {
        return 'Distribution ' . Carbon::parse($this->date)->format('d-M-Y');
    }

    // ── Build and return the full flat array of rows ──────────────────────────
    //
    //  Row layout (no separate "Route" column):
    //    A          B               C … C+n-1      C+n       C+n+1    C+n+2
    //    #    Assistant Name   [Lottery 1…n]       Total     Remarks  Status
    //
    //  Between each route group we inject:
    //    1. A merged route-header row (e.g. "ROUTE A")
    //    2. Assistant rows for that route
    //    3. An empty separator row (except after the last group)
    //
    //  Total columns = 2 (# + Name) + n (lotteries) + 3 (Total, Remarks, Status)

    public function array(): array
    {
        if ($this->built) {
            return $this->builtRows;
        }

        $totalCols = 2 + $this->lotteries->count() + 3;

        // ── Row 1: column headings ────────────────────────────────────────────
        $heading = ['#', 'Assistant Name'];
        foreach ($this->lotteries as $l) {
            $heading[] = $l->name;
        }
        $heading[]       = 'Total';
        $heading[]       = 'Remarks';
        $heading[]       = 'Status';
        $this->builtRows[] = $heading;

        // ── Route groups in the same order as the screen ──────────────────────
        //    Route ASC (NULLs last), then sales_assistants.id ASC
        $assistants = SalesAssistant::with('route')
            ->withoutGlobalScope('ordered')
            ->leftJoin('assistant_routes', 'sales_assistants.route_id', '=', 'assistant_routes.id')
            ->select('sales_assistants.*')
            ->orderByRaw('assistant_routes.name IS NULL, assistant_routes.name ASC')
            ->orderBy('sales_assistants.id', 'asc')
            ->get();

        $routeGroups = $assistants
            ->groupBy(fn ($a) => $a->route_id ?? 'unassigned')
            ->map(fn ($grp) => ['route' => $grp->first()->route, 'assistants' => $grp])
            ->sortBy(fn ($g) => $g['route']?->name ?? 'ZZZZZ')
            ->values();

        $groupCount = $routeGroups->count();
        $rowIndex   = 2; // next available 1-based row number in the output array

        foreach ($routeGroups as $gi => $group) {
            // ── Route header row ──────────────────────────────────────────────
            $routeLabel = $group['route']?->name
                ? strtoupper($group['route']->name)
                : 'UNASSIGNED';

            $routeRow    = array_fill(0, $totalCols, '');
            $routeRow[0] = $routeLabel;

            $this->builtRows[]        = $routeRow;
            $this->routeHeaderRows[]  = $rowIndex++;

            // ── Assistant rows ────────────────────────────────────────────────
            foreach ($group['assistants'] as $assistant) {
                $this->builtRows[]   = $this->buildAssistantRow($assistant, $totalCols);
                $this->assistantRows[] = $rowIndex++;
            }

            // ── Empty separator (skip after the last group) ───────────────────
            if ($gi < $groupCount - 1) {
                $this->builtRows[] = array_fill(0, $totalCols, '');
                $rowIndex++;
            }
        }

        $this->built = true;

        return $this->builtRows;
    }

    // ── Build one assistant data row ──────────────────────────────────────────

    private function buildAssistantRow(SalesAssistant $assistant, int $totalCols): array
    {
        $this->seqNum++;

        $aGrid     = $this->grid[$assistant->id] ?? [];
        $note      = $this->notes[$assistant->id] ?? null;
        $isNoSales = (bool) ($note?->is_no_sales ?? false);

        $row      = [$this->seqNum, $assistant->name];
        $rowTotal = 0;

        foreach ($this->lotteries as $lottery) {
            $qty = $isNoSales ? 0 : ($aGrid[$lottery->id] ?? 0);
            $row[] = $qty > 0 ? $qty : '';
            if ($qty > 0) {
                $rowTotal                      += $qty;
                $this->colTotals[$lottery->id] += $qty;
            }
        }

        $this->grandTotal += $rowTotal;

        $row[] = $rowTotal > 0 ? $rowTotal : '';
        $row[] = $note?->remarks ?? '';
        $row[] = $isNoSales ? 'No Sales' : '';

        return $row;
    }

    // ── AfterSheet: all formatting ────────────────────────────────────────────
    //
    //  Call order inside maatwebsite/excel:
    //    1. array()  → writes data to sheet (rows are 1-based, headings at row 1)
    //    2. AfterSheet fires  → we insert 3 branded header rows before row 1,
    //       which shifts everything down by 3.  All tracked row numbers get +3.

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Ensure rows have been built (safety guard)
                $this->array();

                $sheet = $event->sheet->getDelegate();

                $lotteryCount  = $this->lotteries->count();
                $totalCols     = 2 + $lotteryCount + 3;
                $lastColLetter = Coordinate::stringFromColumnIndex($totalCols);
                $lastDataRow   = $sheet->getHighestRow(); // before insert

                // ── Insert 3 branded header rows at the top ───────────────────
                $sheet->insertNewRowBefore(1, 3);

                // After the insert every tracked row shifts down by 3
                $headingRow      = 1 + 3;   // = 4
                $routeHeaderRows = array_map(fn ($r) => $r + 3, $this->routeHeaderRows);
                $assistantRows   = array_map(fn ($r) => $r + 3, $this->assistantRows);
                $lastShiftedRow  = $lastDataRow + 3;

                // ─────────────────────────────────────────────────────────────
                // Rows 1-3: Branded agency header
                // ─────────────────────────────────────────────────────────────

                // Row 1 — Agency name
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $sheet->setCellValue('A1', 'W.R Soysa Lottery Agency — Ticket Distribution');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                                    'vertical'   => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Row 2 — Sub-heading
                $sheet->mergeCells("A2:{$lastColLetter}2");
                $sheet->setCellValue('A2',
                    'NLB · DLB Agent | Girandurukotte & Mahiyanganya | 072-0673295 / 078-4766684'
                );
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 9, 'color' => ['rgb' => 'BFDBFE']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(16);

                // Row 3 — Date (left) + generated timestamp (right)
                $half    = (int) ceil($totalCols / 2);
                $halfCol = Coordinate::stringFromColumnIndex($half);
                $nextCol = Coordinate::stringFromColumnIndex($half + 1);

                $sheet->mergeCells("A3:{$halfCol}3");
                $sheet->setCellValue('A3', 'Date: ' . Carbon::parse($this->date)->format('d M Y (l)'));

                $sheet->mergeCells("{$nextCol}3:{$lastColLetter}3");
                $sheet->setCellValue("{$nextCol}3", 'Generated: ' . now()->format('d M Y H:i'));

                $sheet->getStyle("A3:{$lastColLetter}3")->applyFromArray([
                    'font'      => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '475569']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                ]);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("{$nextCol}3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getRowDimension(3)->setRowHeight(14);

                // ─────────────────────────────────────────────────────────────
                // Row 4: Column headings
                // ─────────────────────────────────────────────────────────────
                $sheet->getStyle("A{$headingRow}:{$lastColLetter}{$headingRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                                    'vertical'   => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension($headingRow)->setRowHeight(18);

                // ─────────────────────────────────────────────────────────────
                // Route header rows: merged, light-blue background, bold
                // ─────────────────────────────────────────────────────────────
                foreach ($routeHeaderRows as $r) {
                    $sheet->mergeCells("A{$r}:{$lastColLetter}{$r}");
                    $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '1E3A8A']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT,
                                        'vertical'   => Alignment::VERTICAL_CENTER,
                                        'indent'     => 1],
                    ]);
                    $sheet->getRowDimension($r)->setRowHeight(18);
                }

                // ─────────────────────────────────────────────────────────────
                // Assistant rows: zebra-striping (white / very-light grey)
                // ─────────────────────────────────────────────────────────────
                foreach ($assistantRows as $idx => $r) {
                    $bgColor = ($idx % 2 === 0) ? 'FFFFFF' : 'F8FAFC';
                    $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getRowDimension($r)->setRowHeight(14);
                }

                // ─────────────────────────────────────────────────────────────
                // Borders: thin on every cell in the data block
                //   • headings row + route-header rows + assistant rows
                //   • empty separator rows are intentionally left border-free
                // ─────────────────────────────────────────────────────────────
                $borderedRows = array_merge([$headingRow], $routeHeaderRows, $assistantRows);
                foreach ($borderedRows as $r) {
                    $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['rgb' => 'CBD5E1'],
                            ],
                        ],
                    ]);
                }

                // ─────────────────────────────────────────────────────────────
                // Totals footer row (2 rows below the last data row)
                // ─────────────────────────────────────────────────────────────
                $totalsRow = $lastShiftedRow + 2;

                $sheet->setCellValue("A{$totalsRow}", 'TOTALS');

                // Per-lottery column totals  (lotteries start at column C = index 3)
                foreach ($this->lotteries as $idx => $lottery) {
                    $colLetter = Coordinate::stringFromColumnIndex(3 + $idx);
                    $total     = $this->colTotals[$lottery->id] ?? 0;
                    if ($total > 0) {
                        $sheet->setCellValue("{$colLetter}{$totalsRow}", $total);
                    }
                }

                // Grand total (Total column = lotteries start + count)
                $totalColLetter = Coordinate::stringFromColumnIndex(3 + $lotteryCount);
                if ($this->grandTotal > 0) {
                    $sheet->setCellValue("{$totalColLetter}{$totalsRow}", $this->grandTotal);
                }

                $sheet->getStyle("A{$totalsRow}:{$lastColLetter}{$totalsRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'borders'   => [
                        'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '93C5FD']],
                    ],
                ]);
                $sheet->getStyle("A{$totalsRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getRowDimension($totalsRow)->setRowHeight(18);

                // ─────────────────────────────────────────────────────────────
                // Outer border around the whole document
                // ─────────────────────────────────────────────────────────────
                $sheet->getStyle("A1:{$lastColLetter}{$totalsRow}")->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color'       => ['rgb' => '1E3A8A'],
                        ],
                    ],
                ]);

                // ─────────────────────────────────────────────────────────────
                // Freeze pane: keep the 4 header rows and the # + Name columns
                // visible while scrolling through lotteries / assistants
                // ─────────────────────────────────────────────────────────────
                $sheet->freezePane('C5');
            },
        ];
    }
}
