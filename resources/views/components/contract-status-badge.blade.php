@props(['status', 'label'])

@php
    $classes = [
        'active' => 'text-bg-success',
        'expired' => 'text-bg-warning',
        'terminated' => 'text-bg-secondary',
    ];
@endphp

<span {{ $attributes->class(['badge', $classes[$status] ?? 'text-bg-secondary']) }}>{{ $label }}</span>
