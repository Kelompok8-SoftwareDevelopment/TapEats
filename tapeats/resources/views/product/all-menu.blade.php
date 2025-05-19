<div class="mx-auto max-w-[420px] w-full p-4 font-poppins">

    {{-- Title Navigation --}}
    <livewire:components.page-title-nav
        :title="'All Menus'"
        wire:key="{{ str()->random(50) }}"
    ></livewire:components.page-title-nav>

    {{-- Filter Categories --}}
    <div class="my-4 flex flex-wrap gap-2">
        @foreach ($categories as $category)
            <button
                wire:click="$set('selectedCategories', [{{ $category->id }}])"
                class="rounded-full border border-primary-50 bg-white px-4 py-1.5 text-sm font-medium text-primary-60 transition hover:bg-primary-50 hover:text-white"
            >
                {{ $category->name }}
            </button>
        @endforeach

        {{-- Reset Filter --}}
        <button
            wire:click="$set('selectedCategories', [])"
            class="rounded-full border border-red-300 bg-white px-4 py-1.5 text-sm font-medium text-red-500 transition hover:bg-red-500 hover:text-white"
        >
            Reset
        </button>
    </div>

    {{-- Food Cards Grid --}}
    <div class="mb-24 grid grid-cols-2 gap-4">
    @if (isset($filteredProducts) && count($filteredProducts) > 0)
        @foreach ($filteredProducts as $food)
            <livewire:components.food-card
                wire:key="food-card-{{ $food->id }}"
                :data="$food"
                :categories="$categories"
            />
        @endforeach
    @else
        <div class="my-2 w-full col-span-2">
            <p class="text-center text-black-70">No food available</p>
        </div>
    @endif
</div>


</div>
