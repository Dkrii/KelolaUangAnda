<?php

namespace App\Exports;

use App\Enums\TransactionType;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportExport implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    /**
     * @var array<string, int>
     */
    private array $layout = [];

    /**
     * @param  array<string, mixed>  $report
     */
    public function __construct(private readonly array $report) {}

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $period = $this->report['period'];
        $start = $this->report['start'];
        $end = $this->report['end'];

        $rows = [
            ['LAPORAN KEUANGAN', '', ''],
            [$period->label(), '', $start->format('d M Y').' s.d. '.$end->format('d M Y')],
            ['', '', ''],
            ['RINGKASAN', '', ''],
            ['Pemasukan', '', (float) $this->report['income']],
            ['Pengeluaran', '', (float) $this->report['expense']],
            ['Arus Kas Bersih', '', (float) $this->report['net']],
            ['Jumlah Transaksi', '', (int) $this->report['transactionCount']],
            ['', '', ''],
            ['PEMASUKAN PER KATEGORI', '', ''],
            ['Kategori', 'Porsi', 'Nominal'],
        ];

        $incomeStart = count($rows) + 1;
        $incomeCategories = $this->report['categoriesIncome'];

        if ($incomeCategories === []) {
            $rows[] = ['Tidak ada pemasukan pada periode ini', '', ''];
        } else {
            foreach ($incomeCategories as $category) {
                $rows[] = [
                    $category['name'],
                    round($category['percentage'] / 100, 4),
                    (float) $category['amount'],
                ];
            }
        }

        $incomeEnd = count($rows);
        $rows[] = ['', '', ''];

        $expenseSection = count($rows) + 1;
        $rows[] = ['PENGELUARAN PER KATEGORI', '', ''];
        $rows[] = ['Kategori', 'Porsi', 'Nominal'];

        $expenseStart = count($rows) + 1;
        $expenseCategories = $this->report['categoriesExpense'];

        if ($expenseCategories === []) {
            $rows[] = ['Tidak ada pengeluaran pada periode ini', '', ''];
        } else {
            foreach ($expenseCategories as $category) {
                $rows[] = [
                    $category['name'],
                    round($category['percentage'] / 100, 4),
                    (float) $category['amount'],
                ];
            }
        }

        $expenseEnd = count($rows);
        $rows[] = ['', '', ''];

        $transactionSection = count($rows) + 1;
        $rows[] = ['RINCIAN TRANSAKSI', '', ''];
        $rows[] = ['Tanggal', 'Keterangan', 'Nominal'];

        $transactionStart = count($rows) + 1;
        $transactions = $this->report['transactions'];

        if ($transactions->isEmpty()) {
            $rows[] = ['-', 'Tidak ada transaksi pada periode ini', ''];
        } else {
            foreach ($transactions as $transaction) {
                $description = $transaction->description ?: '-';

                if ($transaction->category) {
                    $description .= ' - '.$transaction->category->name;
                }

                $amount = (float) $transaction->amount;
                $rows[] = [
                    $transaction->transaction_date->format('d M Y'),
                    $description,
                    $transaction->type === TransactionType::Income ? $amount : -$amount,
                ];
            }
        }

        $transactionEnd = count($rows);

        $this->layout = [
            'lastRow' => $transactionEnd,
            'summaryStart' => 5,
            'summaryEnd' => 8,
            'incomeSection' => 10,
            'incomeHeader' => 11,
            'incomeStart' => $incomeStart,
            'incomeEnd' => $incomeEnd,
            'expenseSection' => $expenseSection,
            'expenseHeader' => $expenseSection + 1,
            'expenseStart' => $expenseStart,
            'expenseEnd' => $expenseEnd,
            'transactionSection' => $transactionSection,
            'transactionHeader' => $transactionSection + 1,
            'transactionStart' => $transactionStart,
            'transactionEnd' => $transactionEnd,
        ];

        return $rows;
    }

    /**
     * @return array<string, float|int>
     */
    public function columnWidths(): array
    {
        return [
            'A' => 26,
            'B' => 44,
            'C' => 20,
        ];
    }

    /**
     * @return array<string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $layout = $this->layout;

                $sheet->getDefaultRowDimension()->setRowHeight(18);
                $sheet->getStyle('A1:C'.$layout['lastRow'])
                    ->getFont()
                    ->setName('Calibri')
                    ->setSize(10);

                $sheet->mergeCells('A1:C1');
                $sheet->getStyle('A1:C1')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '047857'],
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

                $sheet->mergeCells('A2:C2');
                $sheet->getStyle('A2:C2')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'ECFDF5'],
                    ],
                    'font' => [
                        'color' => ['rgb' => '065F46'],
                        'size' => 10,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->mergeCells('A4:C4');
                $sectionStyle = static function (int $row, string $background, string $color) use ($sheet): void {
                    $sheet->mergeCells('A'.$row.':C'.$row);
                    $sheet->getStyle('A'.$row.':C'.$row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $background],
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => $color],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(22);
                };

                $sheet->getStyle('A4:C4')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3F4F6'],
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '374151'],
                    ],
                ]);

                $headerStyle = static function (int $row) use ($sheet): void {
                    $sheet->getStyle('A'.$row.':C'.$row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F9FAFB'],
                        ],
                        'font' => ['bold' => true],
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'D1D5DB'],
                            ],
                        ],
                    ]);
                };

                $bodyStyle = static function (int $start, int $end) use ($sheet): void {
                    $sheet->getStyle('A'.$start.':C'.$end)->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'F3F4F6'],
                            ],
                        ],
                    ]);
                };

                $sheet->getStyle('A'.$layout['summaryStart'].':C'.$layout['summaryEnd'])->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);
                $sheet->getStyle('C'.$layout['summaryStart'].':C'.($layout['summaryEnd'] - 1))
                    ->getNumberFormat()
                    ->setFormatCode('"Rp" #,##0.00');
                $sheet->getStyle('C'.$layout['summaryStart'].':C'.($layout['summaryEnd'] - 1))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('A'.($layout['summaryEnd'] - 1).':C'.($layout['summaryEnd'] - 1))->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'ECFDF5'],
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '065F46'],
                    ],
                ]);
                $sheet->getStyle('C'.$layout['summaryEnd'])
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sectionStyle($layout['incomeSection'], 'D1FAE5', '065F46');
                $sectionStyle($layout['expenseSection'], 'FFE4E6', '9F1239');
                $sectionStyle($layout['transactionSection'], 'F3F4F6', '374151');

                $headerStyle($layout['incomeHeader']);
                $headerStyle($layout['expenseHeader']);
                $headerStyle($layout['transactionHeader']);

                $bodyStyle($layout['incomeStart'], $layout['incomeEnd']);
                $bodyStyle($layout['expenseStart'], $layout['expenseEnd']);
                $bodyStyle($layout['transactionStart'], $layout['transactionEnd']);

                foreach (['incomeStart' => 'incomeEnd', 'expenseStart' => 'expenseEnd'] as $startKey => $endKey) {
                    $sheet->getStyle('B'.$layout[$startKey].':C'.$layout[$endKey])
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('B'.$layout[$startKey].':B'.$layout[$endKey])
                        ->getNumberFormat()
                        ->setFormatCode('0.0%');
                    $sheet->getStyle('C'.$layout[$startKey].':C'.$layout[$endKey])
                        ->getNumberFormat()
                        ->setFormatCode('"Rp" #,##0.00');
                }

                $sheet->getStyle('C'.$layout['transactionStart'].':C'.$layout['transactionEnd'])
                    ->getNumberFormat()
                    ->setFormatCode('"Rp" #,##0.00');
                $sheet->getStyle('C'.$layout['transactionStart'].':C'.$layout['transactionEnd'])
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                for ($row = $layout['transactionStart']; $row <= $layout['transactionEnd']; $row++) {
                    $value = $sheet->getCell('C'.$row)->getValue();

                    if (! is_numeric($value) || (float) $value === 0.0) {
                        continue;
                    }

                    $sheet->getStyle('C'.$row)->getFont()->setColor(
                        new Color(
                            (float) $value > 0 ? '047857' : 'E11D48',
                        ),
                    );
                }

                $sheet->freezePane('A4');
                $sheet->setSelectedCell('A1');
            },
        ];
    }

    public function title(): string
    {
        return 'Laporan Keuangan';
    }
}
