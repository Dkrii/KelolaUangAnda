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
            <tr><th>Pemasukan</th><td class="right">Rp {{ number_format((float) $report['income'], 0, ',', '.') }}</td></tr>
            <tr><th>Pengeluaran</th><td class="right">Rp {{ number_format((float) $report['expense'], 0, ',', '.') }}</td></tr>
            <tr><th>Arus Kas Bersih</th><td class="right">Rp {{ number_format((float) $report['net'], 0, ',', '.') }}</td></tr>
            <tr><th>Jumlah Transaksi</th><td class="right">{{ $report['transactionCount'] }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Pemasukan per Kategori</h2>
        @if (count($report['categoriesIncome']) === 0)
            <p class="muted">Belum ada pemasukan pada periode ini.</p>
        @else
            <table>
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
        <h2>Pengeluaran per Kategori</h2>
        @if (count($report['categoriesExpense']) === 0)
            <p class="muted">Belum ada pengeluaran pada periode ini.</p>
        @else
            <table>
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
                        <tr>
                            <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
                            <td>{{ $transaction->description ?: '-' }}</td>
                            <td>{{ $transaction->category?->name ?? '-' }}</td>
                            <td>{{ $transaction->type->label() }}</td>
                            <td class="right">Rp {{ number_format((float) $transaction->amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>
