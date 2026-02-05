@props([
    'variant' => 'secondary',
    'type' => 'button',
])

@php
    $base = 'btn';
    $variantClass = 'btn-outline-' . $variant;
    $additional = $attributes->get('class') ?? '';
    $classes = trim($base . ' ' . $variantClass . ' ' . $additional);
    $hasHref = $attributes->has('href');
    // Remove href from attributes when rendering button element
    $buttonAttributes = $attributes->except(['href']);
@endphp

@if ($hasHref)
    <a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $buttonAttributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
