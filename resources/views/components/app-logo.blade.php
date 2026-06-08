@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="{{ config('app.name', 'CANAM') }}" {{ $attributes }}>
        <x-slot name="logo" class="app-brand-icon-wrap flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-white/90 p-1 dark:bg-zinc-800">
            <img src="{{ asset('logo_canam.png') }}" alt="{{ config('app.name', 'CANAM') }}" class="app-brand-logo size-full object-contain" onerror="this.onerror=null;this.src='{{ asset('logo_canam.jpg') }}';">
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="{{ config('app.name', 'CANAM') }}" {{ $attributes }}>
        <x-slot name="logo" class="app-brand-icon-wrap flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-white/90 p-1 dark:bg-zinc-800">
            <img src="{{ asset('logo_canam.png') }}" alt="{{ config('app.name', 'CANAM') }}" class="app-brand-logo size-full object-contain" onerror="this.onerror=null;this.src='{{ asset('logo_canam.jpg') }}';">
        </x-slot>
    </flux:brand>
@endif
