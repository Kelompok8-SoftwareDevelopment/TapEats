<div
    @if ($data->status !== 'out_of_stock')
        wire:click="showDetails"
    @endif
    @class([
        // mode GRID
        'w-full h-[320px]'                             => $isGrid,
        // mode SCROLL
        'min-w-[165px] max-w-[180px]'                  => ! $isGrid,
        // shared
        'flex flex-col justify-between rounded-2xl bg-white p-2 font-poppins shadow-lg transition-all',
        'cursor-not-allowed opacity-60'                => $data->status === 'out_of_stock',
        'hover:ring-2 hover:ring-inset hover:ring-[#2D5900]' => $data->status !== 'out_of_stock',
    ])>

    {{-- Gambar dan badge --}}
    <div class="relative w-full aspect-square rounded-xl overflow-hidden">
        <!-- <div class="absolute left-1.5 top-1.5 z-10 flex items-center gap-1.5 rounded-full bg-white px-2 py-1.5">
            <img src="{{ asset('assets/icons/spoon-icon.svg') }}" alt="Sold" />
            <span class="text-xs font-semibold text-black-100">
                {{ $data->total_sold ?? 0 }} Sold
            </span>
        </div> -->

        @if ($data->is_promo)
        <div
            style="background-image: url('{{ asset('assets/icons/discount-icon.svg') }}');"
            class="absolute bottom-1.5 right-1.5 z-10 flex h-10 items-center gap-1.5 rounded-full bg-cover px-2 py-1.5">
            <span class="text-xs font-semibold text-white">
                {{ $data->percent }}%
            </span>
        </div>
        @endif

        <img
            src="{{ Storage::url($data->image) }}"
            alt="{{ $data->name }}"
            class="w-full h-full object-cover {{ $data->status === 'out_of_stock' ? 'opacity-50' : '' }}"
            loading="lazy" />
    </div>

    {{-- Konten bawah --}}
    <div class="flex flex-col justify-between flex-grow mt-2">
        <p class="font-semibold text-black-100 leading-tight line-clamp-2 h-[3.5rem]">
            {{ $data->name }}
        </p>

        <div class="mt-1 flex items-start gap-1 font-semibold text-black-100">
            <img src="{{ asset('assets/icons/price-icon.svg') }}" />
            <div>
                <span>
                    {{ number_format($data->price_afterdiscount ?? $data->price, 0, ',', '.') }}
                </span>
                @if ($data->is_promo)
                <span class="-mt-1 block text-xs text-black-40 line-through">
                    {{ number_format($data->price, 0, ',', '.') }}
                </span>
                @endif
            </div>
        </div>

        <div class="mt-auto flex items-center justify-between">
            <div class="flex items-center gap-1 text-xs font-medium text-black-100">
                <img src="{{ asset('assets/icons/category-icon.svg') }}" alt="Category" />
                <span>{{ $matchedCategory ? $matchedCategory->name : 'Unknown' }}</span>
            </div>

            @if ($data->status === 'out_of_stock')
                <div class="rounded-full bg-gray-300 px-3 py-1 text-xs font-semibold text-white">
                    Sold
                </div>
            @else
                <button
                    wire:click.stop="addToCart"
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-[#2D5900] hover:bg-[#3e7a00] transition-all shadow-md">
                    <img src="{{ asset('assets/icons/plus3.svg') }}" alt="Add to cart" class="h-6 w-6" />
                </button>
            @endif
        </div>
    </div>

    <livewire:components.toast />
</div>