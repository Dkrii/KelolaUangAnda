@props(['variant' => 'neutral'])

@php
$classes = match ($variant) {
    'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
    'rose' => 'bg-rose-50 text-rose-700 border-rose-100',
    'indigo' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
    'amber' => 'bg-amber-50 text-amber-700 border-amber-100',
    default => 'bg-neutral-100 text-neutral-600 border-neutral-200',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-medium $classes"]) }}>
    {{ $slot }}
</span>
