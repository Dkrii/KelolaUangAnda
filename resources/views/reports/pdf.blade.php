<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #171717; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        p { margin: 0; }
        .muted { color: #737373; }
        .section { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d4d4d4; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
        .right { text-align: right; }
        .summary td { width: 50%; }
        .summary th.income-label { background-color: #ecfdf5; color: #047857; }
        .summary th.expense-label { background-color: #fff1f2; color: #be123c; }
        .summary td.income-value { color: #047857; font-weight: bold; }
        .summary td.expense-value { color: #be123c; font-weight: bold; }
        h2.income-title { color: #047857; border-bottom: 1px solid #a7f3d0; padding-bottom: 4px; }
        h2.expense-title { color: #be123c; border-bottom: 1px solid #fecdd3; padding-bottom: 4px; }
        table.income-table tbody td { background-color: #f0fdf4; }
        table.expense-table tbody td { background-color: #fff1f2; }
        .income-row td { background-color: #f0fdf4; }
        .expense-row td { background-color: #fff1f2; }
        .transfer-row td { background-color: #eef2ff; }
        .income-row .type, .income-row .amount { color: #047857; font-weight: bold; }
        .expense-row .type, .expense-row .amount { color: #be123c; font-weight: bold; }
        .transfer-row .type, .transfer-row .amount { color: #4338ca; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Laporan Keuangan</h1>
    <p class="muted">
        Periode {{ $report['period']->label() }}:
        {{ $report['start']->format('d M Y') }} &ndash; {{ $report['end']->format('d M Y') }}
    </p>

    <div class="section">
        <table class="summary">
            <tr><th class="income-label">Pemasukan</th><td class="right income-value">Rp {{ number_format((float) $report['income'], 0, ',', '.') }}</td></tr>
            <tr><th class="expense-label">Pengeluaran</th><td class="right expense-value">Rp {{ number_format((float) $report['expense'], 0, ',', '.') }}</td></tr>
            <tr><th>Arus Kas Bersih</th><td class="right">Rp {{ number_format((float) $report['net'], 0, ',', '.') }}</td></tr>
            <tr><th>Jumlah Transaksi</th><td class="right">{{ $report['transactionCount'] }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2 class="income-title">Pemasukan per Kategori</h2>
        @if (count($report['categoriesIncome']) === 0)
            <p class="muted">Belum ada pemasukan pada periode ini.</p>
        @else
            <table class="income-table">
                <thead><tr><th>Kategori</th><th class="right">Nominal</th><th class="right">Porsi</th></tr></thead>
                <tbody>
                    @foreach ($report['categoriesIncome'] as $category)
                        <tr>
                            <td>{{ $category['name'] }}</td>
                            <td class="right">Rp {{ number_format((float) $category['amount'], 0, ',', '.') }}</td>
                            <td class="right">{{ number_format($category['percentage'], 1, ',', '.') }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="section">
        <h2 class="expense-title">Pengeluaran per Kategori</h2>
        @if (count($report['categoriesExpense']) === 0)
            <p class="muted">Belum ada pengeluaran pada periode ini.</p>
        @else
            <table class="expense-table">
                <thead><tr><th>Kategori</th><th class="right">Nominal</th><th class="right">Porsi</th></tr></thead>
                <tbody>
                    @foreach ($report['categoriesExpense'] as $category)
                        <tr>
                            <td>{{ $category['name'] }}</td>
                            <td class="right">Rp {{ number_format((float) $category['amount'], 0, ',', '.') }}</td>
                            <td class="right">{{ number_format($category['percentage'], 1, ',', '.') }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="section">
        <h2>Rincian Transaksi</h2>
        @if ($report['transactions']->isEmpty())
            <p class="muted">Tidak ada transaksi pada periode ini.</p>
        @else
            <table>
                <thead>
                    <tr><th>Tanggal</th><th>Keterangan</th><th>Kategori</th><th>Jenis</th><th class="right">Nominal</th></tr>
                </thead>
                <tbody>
                    @foreach ($report['transactions'] as $transaction)
                        @php
                            $tone = match ($transaction->type) {
                                \App\Enums\TransactionType::Income => 'income',
                                \App\Enums\TransactionType::Expense => 'expense',
                                \App\Enums\TransactionType::Transfer => 'transfer',
                            };
                        @endphp
                        <tr class="{{ $tone }}-row">
                            <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
                            <td>{{ $transaction->description ?: '-' }}</td>
                            <td>{{ $transaction->category?->name ?? '-' }}</td>
                            <td class="type">{{ $transaction->type->label() }}</td>
                            <td class="right amount">Rp {{ number_format((float) $transaction->amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>
