<div class="bg-white min-h-screen font-poppins">
    {{-- Page Title --}}
    <livewire:components.page-title-nav
        :title="'All Foods'"
        wire:key="{{ str()->random(50) }}"
    />

    {{-- Filter Tabs --}}
    <div class="container px-4 pt-2">
        <div class="flex gap-2 overflow-x-auto pb-2">
            {{-- "All" Button --}}
            <button
                wire:click="selectCategory"
                class="rounded-full px-4 py-2 text-sm font-medium border transition shrink-0"
                style="
                    background-color: {{ is_null($selectedCategory) ? '#b7e4c7' : '#ffffff' }};
                    color: {{ is_null($selectedCategory) ? '#2D5900' : '#000000' }};
                    border-color: {{ is_null($selectedCategory) ? '#b7e4c7' : '#d1d5db' }};
                "
            >
                All
            </button>

            {{-- Other Categories --}}
            @foreach ($categories as $category)
                <button
                    wire:click="selectCategory({{ $category->id }})"
                    class="rounded-full px-4 py-2 text-sm font-medium border transition shrink-0"
                    style="
                        background-color: {{ $selectedCategory === $category->id ? '#b7e4c7' : '#ffffff' }};
                        color: {{ $selectedCategory === $category->id ? '#2D5900' : '#000000' }};
                        border-color: {{ $selectedCategory === $category->id ? '#b7e4c7' : '#d1d5db' }};
                    "
                >
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Food Card List --}}
    <div class="container mt-4 mb-24 grid grid-cols-2 gap-4">
        @if (isset($filteredProducts) && count($filteredProducts) > 0)
            @foreach ($filteredProducts as $food)
                <livewire:components.food-card
                    wire:key="{{ str()->random(50) }}"
                    :data="$food"
                    :categories="$categories"
                />
            @endforeach
        @else
            <div class="col-span-2 my-2 w-full">
                <p class="text-center text-black-70">No food available</p>
            </div>
        @endif
    </div>
</div>
