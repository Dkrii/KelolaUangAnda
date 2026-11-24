@props([
    'title',
    'value',
    'icon' => null,
    'trend' => null,
    'trendPositive' => true,
    'subtext' => null,
    'variant' => 'default', // default | emerald | rose | indigo
])

@php
$accentClass = match($variant) {
    'emerald' => 'text-emerald-700 bg-emerald-50 border-emerald-100',
    'rose' => 'text-rose-700 bg-rose-50 border-rose-100',
    'indigo' => 'text-indigo-700 bg-indigo-50 border-indigo-100',
    default => 'text-neutral-700 bg-neutral-50 border-neutral-100',
};
@endphp

<div {{ $attributes->merge(['class' => 'bg-white border border-neutral-200/80 rounded-xl p-5 shadow-xs transition hover:shadow-sm']) }}>
    <div class="flex items-center justify-between">
        <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ $title }}</p>
        @if ($icon)
            <div class="flex h-9 w-9 items-center justify-center rounded-lg border {{ $accentClass }}">
                {{ $icon }}
            </div>
        @endif
    </div>

    <div class="mt-3 flex items-baseline gap-2">
        <p class="text-2xl font-semibold tracking-tight text-neutral-900">{{ $value }}</p>
    </div>

    @if ($subtext || $trend)
        <div class="mt-2 flex items-center gap-1.5 text-xs text-neutral-500">
            @if ($trend)
                <span class="inline-flex items-center font-medium {{ $trendPositive ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $trend }}
                </span>
            @endif
            @if ($subtext)
                <span>{{ $subtext }}</span>
            @endif
        </div>
    @endif
</div>
