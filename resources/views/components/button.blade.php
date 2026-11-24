@props(['variant' => 'primary', 'type' => 'button'])

@php
$classes = match($variant) {
    'primary' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm focus-visible:outline-emerald-600',
    'secondary' => 'bg-white hover:bg-neutral-50 text-neutral-800 border border-neutral-300 shadow-sm focus-visible:outline-neutral-500',
    'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm focus-visible:outline-rose-600',
    'ghost' => 'hover:bg-neutral-100 text-neutral-700 focus-visible:outline-neutral-500',
    default => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm focus-visible:outline-emerald-600',
};
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50 disabled:cursor-not-allowed $classes"]) }}
>
    {{ $slot }}
</button>
