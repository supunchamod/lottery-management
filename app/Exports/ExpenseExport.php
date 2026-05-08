<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExpenseExport implements FromArray, ShouldAutoSize, WithTitle, WithEvents
{
    // Row-index tracking (1-based, before any insertions)
    private int $detailHeadingRow  = 0;
    private int $summaryHeadingRow = 0;
    private int $summaryTotalRow   = 0;
    private array $detailDataRows  = [];
    private array $summaryDataRows = [];

    public function __construct(
        private readonly Collection $expenses,
        private readonly Collection $summary,
        private readonly string     $periodLabel,
    ) {}

    public function title(): string
    {
        return 'Expenses';
    }

    public function array(): array
    {
        $rows = [];
        $r    = 1;   // 1-based row counter (will shift +3 after header insert)

        // ── Section 1: Expense Detail ─────────────────────────────────────────

        $rows[] = ['Date', 'Category', 'Amount (Rs.)', 'Description'];
        $this->detailHeadingRow = $r++;

        foreach ($this->expenses as $expense) {
            $rows[] = [
                $expense->date->format('d M Y'),
                $expense->category?->name ?? '—',
                (float) $expense->amount,
                $expense->description ?? '',
            ];
            $this->detailDataRows[] = $r++;
        }

        // ── Spacer ────────────────────────────────────────────────────────────
        $rows[] = ['', '', '', ''];
        $r++;
        $rows[] = ['', '', '', ''];
        $r++;

        // ── Section 2: Category Summary ───────────────────────────────────────
        $rows[] = ['CATEGORY SUMMARY', '', '', ''];
        $r++;

        $rows[] = ['Category', 'Entries', 'Total Amount (Rs.)', 'Share (%)'];
        $this->summaryHeadingRow = $r++;

        $grandTotal = (float) $this->summary->sum('total_amount');

        foreach ($this->summary as $item) {
            $share = $grandTotal > 0
                ? round(($item->total_amount / $grandTotal) * 100, 1)
                : 0;
            $rows[] = [
                $item->category?->name ?? 'Unknown',
                (int) $item->entry_count,
                (float) $item->total_amount,
                $share,
            ];
            $this->summaryDataRows[] = $r++;
        }

        // ── Grand total footer ────────────────────────────────────────────────
        $rows[] = [
            'TOTAL',
            (int) $this->summary->sum('entry_count'),
            $grandTotal,
            100.0,
        ];
        $this->summaryTotalRow = $r;

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->array();   // ensure tracking vars are populated

                $sheet = $event->sheet->getDelegate();

                // ── Insert 3 branded header rows at the top ───────────────────
                $sheet->insertNewRowBefore(1, 3);
                $shift = 3;

                $dHeading  = $this->detailHeadingRow  + $shift;
                $sHeading  = $this->summaryHeadingRow + $shift;
                $sTotalRow = $this->summaryTotalRow   + $shift;
                $dDataRows = array_map(fn ($r) => $r + $shift, $this->detailDataRows);
                $sDataRows = array_map(fn ($r) => $r + $shift, $this->summaryDataRows);
                $lastRow   = $sTotalRow;

                // ── Rows 1-3: Branded header ──────────────────────────────────
                $sheet->mergeCells('A1:D1');
                $sheet->setCellValue('A1', 'W.R Soysa Lottery Agency — Expense Report');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                                    'vertical'   => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->mergeCells('A2:D2');
                $sheet->setCellValue('A2',
                    'NLB · DLB Agent | Girandurukotte & Mahiyanganya | 072-0673295 / 078-4766684'
                );
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 9, 'color' => ['rgb' => 'BFDBFE']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(16);

                $sheet->mergeCells('A3:C3');
                $sheet->setCellValue('A3', 'Period: ' . $this->periodLabel);
                $sheet->setCellValue('D3', 'Generated: ' . now()->format('d M Y H:i'));
                $sheet->getStyle('A3:D3')->applyFromArray([
                    'font'      => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '475569']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                ]);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('D3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getRowDimension(3)->setRowHeight(14);

                // ── Detail heading row ────────────────────────────────────────
                $this->applyHeadingStyle($sheet, "A{$dHeading}:D{$dHeading}");
                $sheet->getRowDimension($dHeading)->setRowHeight(18);

                // ── Detail data rows: zebra-stripe + right-align amount ────────
                foreach ($dDataRows as $idx => $r) {
                    $bg = ($idx % 2 === 0) ? 'FFFFFF' : 'F8FAFC';
                    $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle("C{$r}")->getAlignment()
                          ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("C{$r}")->getNumberFormat()
                          ->setFormatCode('#,##0.00');
                    $sheet->getRowDimension($r)->setRowHeight(14);
                }

                // ── Thin borders for detail block ─────────────────────────────
                foreach (array_merge([$dHeading], $dDataRows) as $r) {
                    $this->applyThinBorder($sheet, "A{$r}:D{$r}");
                }

                // ── Summary section header label ──────────────────────────────
                $sLabelRow = $sHeading - 1;
                $sheet->mergeCells("A{$sLabelRow}:D{$sLabelRow}");
                $sheet->getStyle("A{$sLabelRow}:D{$sLabelRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '1E3A8A']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT,
                                    'vertical'   => Alignment::VERTICAL_CENTER,
                                    'indent'     => 1],
                ]);
                $sheet->getRowDimension($sLabelRow)->setRowHeight(18);

                // ── Summary heading row ───────────────────────────────────────
                $this->applyHeadingStyle($sheet, "A{$sHeading}:D{$sHeading}");
                $sheet->getRowDimension($sHeading)->setRowHeight(18);

                // ── Summary data rows ─────────────────────────────────────────
                foreach ($sDataRows as $idx => $r) {
                    $bg = ($idx % 2 === 0) ? 'FFFFFF' : 'F8FAFC';
                    $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("C{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$r}")->getNumberFormat()->setFormatCode('0.0"%"');
                    $sheet->getRowDimension($r)->setRowHeight(14);
                }

                // ── Summary total row ─────────────────────────────────────────
                $sheet->getStyle("A{$sTotalRow}:D{$sTotalRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'borders'   => ['top' => ['borderStyle' => Border::BORDER_MEDIUM,
                                              'color'       => ['rgb' => '93C5FD']]],
                ]);
                $sheet->getStyle("A{$sTotalRow}")->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("C{$sTotalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getRowDimension($sTotalRow)->setRowHeight(18);

                // ── Thin borders for summary block ────────────────────────────
                foreach (array_merge([$sHeading], $sDataRows, [$sTotalRow]) as $r) {
                    $this->applyThinBorder($sheet, "A{$r}:D{$r}");
                }

                // ── Outer border around whole document ────────────────────────
                $sheet->getStyle("A1:D{$lastRow}")->applyFromArray([
                    'borders' => [
                        'outline' => ['borderStyle' => Border::BORDER_MEDIUM,
                                      'color'       => ['rgb' => '1E3A8A']],
                    ],
                ]);

                $sheet->freezePane('A5');
            },
        ];
    }

    private function applyHeadingStyle($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER],
        ]);
    }

    private function applyThinBorder($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                 'color'       => ['rgb' => 'CBD5E1']],
            ],
        ]);
    }
}
