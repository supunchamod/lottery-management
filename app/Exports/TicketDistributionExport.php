<?php

namespace App\Exports;

use App\Models\DailyTicketNote;
use App\Models\DailyTicketStock;
use App\Models\Lottery;
use App\Models\SalesAssistant;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TicketDistributionExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle,
    WithEvents
{
    private Collection $lotteries;
    private array      $grid       = [];
    private Collection $notes;
    private int        $rowNum     = 0;
    private array      $colTotals  = [];
    private int        $grandTotal = 0;

    public function __construct(private readonly string $date)
    {
        // Same lottery sort order as the screen
        $this->lotteries = Lottery::orderByRaw('sort_order IS NULL, sort_order ASC, created_at ASC')->get();

        // Pre-load ticket stock keyed as [assistant_id][lottery_id] => quantity
        foreach (DailyTicketStock::where('date', $date)->get() as $r) {
            $this->grid[$r->assistant_id][$r->lottery_id] = (int) $r->quantity;
        }

        // Pre-load notes keyed by assistant_id
        $this->notes = DailyTicketNote::where('date', $date)->get()->keyBy('assistant_id');

        // Initialise per-lottery column totals
        foreach ($this->lotteries as $l) {
            $this->colTotals[$l->id] = 0;
        }
    }

    // ── Sheet tab title ───────────────────────────────────────────────────────

    public function title(): string
    {
        return 'Distribution ' . Carbon::parse($this->date)->format('d-M-Y');
    }

    // ── Data source: assistants in the exact same order as the screen ─────────
    //  Route ASC (NULLs last), then sales_assistants.id ASC

    public function collection(): Collection
    {
        return SalesAssistant::with('route')
            ->withoutGlobalScope('ordered')
            ->leftJoin('assistant_routes', 'sales_assistants.route_id', '=', 'assistant_routes.id')
            ->select('sales_assistants.*')
            ->orderByRaw('assistant_routes.name IS NULL, assistant_routes.name ASC')
            ->orderBy('sales_assistants.id', 'asc')
            ->get();
    }

    // ── Column headings ───────────────────────────────────────────────────────

    public function headings(): array
    {
        $headers = ['#', 'Route', 'Assistant Name'];

        foreach ($this->lotteries as $lottery) {
            $headers[] = $lottery->name;
        }

        $headers[] = 'Total';
        $headers[] = 'Remarks';
        $headers[] = 'Status';

        return $headers;
    }

    // ── Row mapping ───────────────────────────────────────────────────────────

    public function map($assistant): array
    {
        $this->rowNum++;

        $assistantGrid = $this->grid[$assistant->id] ?? [];
        $note          = $this->notes[$assistant->id] ?? null;
        $isNoSales     = (bool) ($note?->is_no_sales ?? false);

        $row = [
            $this->rowNum,
            $assistant->route?->name ?? '—',
            $assistant->name,
        ];

        $rowTotal = 0;
        foreach ($this->lotteries as $lottery) {
            $qty = $isNoSales ? 0 : ($assistantGrid[$lottery->id] ?? 0);

            $row[] = $qty > 0 ? $qty : '';

            if ($qty > 0) {
                $rowTotal                       += $qty;
                $this->colTotals[$lottery->id]  += $qty;
            }
        }

        $this->grandTotal += $rowTotal;

        $row[] = $rowTotal > 0 ? $rowTotal : '';
        $row[] = $note?->remarks ?? '';
        $row[] = $isNoSales ? 'No Sales' : '';

        return $row;
    }

    // ── Cell styles (headings row — row 4 after the 3-row header block) ───────

    public function styles(Worksheet $sheet): array
    {
        return [
            4 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    // ── AfterSheet: branded header, zebra rows, totals footer, freeze ─────────

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $lotteryCount  = $this->lotteries->count();
                $totalCols     = 3 + $lotteryCount + 3;          // #, Route, Name, lotteries…, Total, Remarks, Status
                $lastColLetter = Coordinate::stringFromColumnIndex($totalCols);
                $lastDataRow   = $sheet->getHighestRow();         // heading row + data rows (before insert)

                // ── Insert 3 rows at the top for the branded header ───────────
                $sheet->insertNewRowBefore(1, 3);

                // Row 1 — Agency name + report title
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $sheet->setCellValue('A1', 'W.R Soysa Lottery Agency — Ticket Distribution');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
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

                // Row 3 — Date + generated timestamp (split across two halves)
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

                // ── Zebra-stripe data rows (rows 5+, shifted by 3 after insert) ─
                $dataStart = 5;
                $dataEnd   = $lastDataRow + 3;

                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $bgColor = ($r % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getRowDimension($r)->setRowHeight(14);
                }

                // ── Totals footer row ─────────────────────────────────────────
                $actualTotalsRow = $dataEnd + 2;

                $sheet->setCellValue("A{$actualTotalsRow}", 'TOTALS');

                foreach ($this->lotteries as $idx => $lottery) {
                    $colLetter = Coordinate::stringFromColumnIndex(4 + $idx); // D = col 4
                    $total     = $this->colTotals[$lottery->id] ?? 0;
                    if ($total > 0) {
                        $sheet->setCellValue("{$colLetter}{$actualTotalsRow}", $total);
                    }
                }

                $totalColLetter = Coordinate::stringFromColumnIndex(4 + $lotteryCount);
                if ($this->grandTotal > 0) {
                    $sheet->setCellValue("{$totalColLetter}{$actualTotalsRow}", $this->grandTotal);
                }

                $sheet->getStyle("A{$actualTotalsRow}:{$lastColLetter}{$actualTotalsRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'borders'   => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '93C5FD']]],
                ]);
                $sheet->getStyle("A{$actualTotalsRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getRowDimension($actualTotalsRow)->setRowHeight(18);

                // ── Outer border around the whole block ───────────────────────
                $sheet->getStyle("A1:{$lastColLetter}{$actualTotalsRow}")->applyFromArray([
                    'borders' => [
                        'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E3A8A']],
                    ],
                ]);

                // ── Freeze pane below header rows so columns + names stay fixed ─
                $sheet->freezePane('D5');
            },
        ];
    }
}
