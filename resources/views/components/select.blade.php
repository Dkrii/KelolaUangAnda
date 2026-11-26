@props(['disabled' => false, 'error' => false])

@php
$borderClass = $error
    ? 'border-rose-300 text-rose-900 focus:border-rose-500 focus:ring-rose-500'
    : 'border-neutral-300 text-neutral-900 focus:border-emerald-500 focus:ring-emerald-500';
@endphp

<select
    @disabled($disabled)
    {{ $attributes->merge(['class' => "block w-full rounded-lg border bg-white px-3 py-2 text-sm shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:bg-neutral-50 disabled:text-neutral-500 $borderClass"]) }}
>
    {{ $slot }}
</select>
