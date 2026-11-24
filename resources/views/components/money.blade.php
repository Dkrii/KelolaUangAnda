@props(['amount', 'currency' => 'IDR'])

@php
$formatted = 'Rp 0';
try {
    $num = is_numeric($amount) ? (float) $amount : 0;
    $formatter = new \NumberFormatter('id_ID', \NumberFormatter::CURRENCY);
    $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
    $formatted = $formatter->formatCurrency($num, $currency);
} catch (\Throwable $e) {
    $formatted = 'Rp ' . number_format((float) ($amount ?? 0), 0, ',', '.');
}
@endphp

<span {{ $attributes }}>{{ $formatted }}</span>
