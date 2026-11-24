@props([
    'type' => 'info', // info | success | warning | danger
    'title' => null,
])

@php
$classes = match($type) {
    'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
    'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
    'danger' => 'bg-rose-50 border-rose-200 text-rose-800',
    default => 'bg-blue-50 border-blue-200 text-blue-800',
};
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border p-4 text-sm $classes"]) }}>
    @if ($title)
        <h4 class="font-semibold">{{ $title }}</h4>
        <div class="mt-1 text-xs opacity-90">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif
</div>
