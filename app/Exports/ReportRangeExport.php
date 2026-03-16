<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportRangeExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithColumnFormatting,
    WithTitle,
    WithEvents
{
    public function __construct(
        private readonly Collection $rows,
        private readonly array      $totals,
        private readonly string     $from,
        private readonly string     $to,
    ) {}

    // ── Sheet title ────────────────────────────────────────────────────────────
    public function title(): string
    {
        return 'P&L ' . $this->from . ' to ' . $this->to;
    }

    // ── Data source: only rows that have any data ──────────────────────────────
    public function collection(): Collection
    {
        return collect($this->rows)
            ->filter(fn ($r) => $r['records'] > 0 || $r['total_expenses'] > 0);
    }

    // ── Column headings (row 4, after the 3-row header block) ─────────────────
    public function headings(): array
    {
        return [
            'Date',
            'Tickets Issued (Rs.)',
            'Returns (Rs.)',
            'Winnings (Rs.)',
            'Cash Collected (Rs.)',
            'Total Expenses (Rs.)',
            'Net Profit (Rs.)',
            'Outstanding (Rs.)',
            'Record Count',
        ];
    }

    // ── Map each row to the heading order ──────────────────────────────────────
    public function map($row): array
    {
        return [
            Carbon::parse($row['date'])->format('d M Y'),
            $row['issued_val'],
            $row['returns_val'],
            $row['total_winning'],
            $row['cash_collected'],
            $row['total_expenses'],
            $row['net_profit'],
            $row['outstanding'],
            $row['records'],
        ];
    }

    // ── Number formats ────────────────────────────────────────────────────────
    public function columnFormats(): array
    {
        return [
            'B' => '#,##0.00',
            'C' => '#,##0.00',
            'D' => '#,##0.00',
            'E' => '#,##0.00',
            'F' => '#,##0.00',
            'G' => '#,##0.00',
            'H' => '#,##0.00',
        ];
    }

    // ── Cell styles ────────────────────────────────────────────────────────────
    public function styles(Worksheet $sheet): array
    {
        // Heading row (row 4 — after 3 inserted header rows)
        return [
            4 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    // ── AfterSheet event: insert branded header + totals footer ───────────────
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $lastDataRow = $sheet->getHighestRow();   // includes heading row 4 + data rows
                $totalsRow = $lastDataRow + 2;

                // ── Insert 3 rows at the top for the branded header ────────────
                $sheet->insertNewRowBefore(1, 3);

                // Row 1 — Agency name + report period
                $sheet->mergeCells("A1:I1");
                $sheet->setCellValue('A1', 'W.R Soysa Lottery Agency — Profit & Loss Report');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Row 2 — Sub-heading
                $sheet->mergeCells("A2:I2");
                $sheet->setCellValue('A2',
                    'NLB · DLB Agent | Girandurukotte & Mahiyanganya | 072-0673295 / 078-4766684'
                );
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 9, 'color' => ['rgb' => 'BFDBFE']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(16);

                // Row 3 — Date range + generated timestamp
                $sheet->mergeCells("A3:E3");
                $sheet->setCellValue('A3',
                    'Period: ' . Carbon::parse($this->from)->format('d M Y')
                    . ' → ' . Carbon::parse($this->to)->format('d M Y')
                );
                $sheet->mergeCells("F3:I3");
                $sheet->setCellValue('F3', 'Generated: ' . now()->format('d M Y H:i'));
                $sheet->getStyle('A3:I3')->applyFromArray([
                    'font'      => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '475569']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->getStyle('F3')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(14);

                // ── Style each data row: zebra-stripe + profit colouring ───────
                $dataStart = 5;  // row 5 = first data row (after 3 header + 1 heading)
                $dataEnd   = $lastDataRow + 3;

                for ($r = $dataStart; $r <= $dataEnd; $r++) {
                    $bgColor = ($r % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:I{$r}")->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getRowDimension($r)->setRowHeight(14);

                    // Colour the Net Profit cell (column G)
                    $profitCell = "G{$r}";
                    $val        = $sheet->getCell($profitCell)->getCalculatedValue();
                    if (is_numeric($val)) {
                        $sheet->getStyle($profitCell)->getFont()
                            ->getColor()->setRGB($val >= 0 ? '15803D' : 'B91C1C');
                        $sheet->getStyle($profitCell)->getFont()->setBold(true);
                    }
                }

                // ── Totals footer row ──────────────────────────────────────────
                $actualTotalsRow = $dataEnd + 2;

                $sheet->setCellValue("A{$actualTotalsRow}", 'TOTALS');
                $sheet->setCellValue("B{$actualTotalsRow}", $this->totals['issued_val']);
                $sheet->setCellValue("C{$actualTotalsRow}", $this->totals['returns_val']);
                $sheet->setCellValue("D{$actualTotalsRow}", $this->totals['total_winning']);
                $sheet->setCellValue("E{$actualTotalsRow}", $this->totals['cash_collected']);
                $sheet->setCellValue("F{$actualTotalsRow}", $this->totals['total_expenses']);
                $sheet->setCellValue("G{$actualTotalsRow}", $this->totals['net_profit']);
                $sheet->setCellValue("H{$actualTotalsRow}", $this->totals['outstanding']);
                $sheet->setCellValue("I{$actualTotalsRow}", $this->totals['records']);

                $sheet->getStyle("A{$actualTotalsRow}:I{$actualTotalsRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'borders'   => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '93C5FD']]],
                ]);
                $sheet->getStyle("A{$actualTotalsRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getRowDimension($actualTotalsRow)->setRowHeight(18);

                // Apply number format to totals row numeric cells
                foreach (['B','C','D','E','F','G','H'] as $col) {
                    $sheet->getStyle("{$col}{$actualTotalsRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }

                // ── Outer border around the whole data block ───────────────────
                $sheet->getStyle("A1:I{$actualTotalsRow}")->applyFromArray([
                    'borders' => [
                        'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E3A8A']],
                    ],
                ]);

                // ── Freeze header rows ─────────────────────────────────────────
                $sheet->freezePane('A5');
            },
        ];
    }
}
