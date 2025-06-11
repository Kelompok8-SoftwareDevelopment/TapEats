<x-layouts.app>
    {{-- Static UI (tidak boleh berubah saat wire:navigate) --}}
    <livewire:components.main-menu wire:key="main-menu" />

    {{-- Dynamic content yang diganti tiap navigasi --}}
    <div class="relative">
        {{ $slot }}
    </div>
</x-layouts.app>
