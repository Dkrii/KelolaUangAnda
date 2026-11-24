@props(['padding' => 'p-6'])

<div {{ $attributes->merge(['class' => "bg-white border border-neutral-200/80 rounded-xl shadow-xs $padding"]) }}>
    {{ $slot }}
</div>
