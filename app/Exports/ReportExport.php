<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  array<string, mixed>  $report
     */
    public function __construct(private readonly array $report) {}

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        $rows = [
            ['Ringkasan', 'Pemasukan', $this->report['income']],
            ['Ringkasan', 'Pengeluaran', $this->report['expense']],
            ['Ringkasan', 'Arus Kas Bersih', $this->report['net']],
            ['Ringkasan', 'Jumlah Transaksi', (string) $this->report['transactionCount']],
        ];

        foreach ($this->report['categoriesIncome'] as $category) {
            $rows[] = ['Kategori Pemasukan', $category['name'], $category['amount']];
        }

        foreach ($this->report['categoriesExpense'] as $category) {
            $rows[] = ['Kategori Pengeluaran', $category['name'], $category['amount']];
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Jenis', 'Keterangan', 'Nominal'];
    }

    public function title(): string
    {
        return 'Laporan Keuangan';
    }
}
