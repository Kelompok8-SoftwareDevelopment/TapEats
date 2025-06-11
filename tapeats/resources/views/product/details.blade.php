<div x-data="{ open: false }" class="bg-white min-h-screen font-poppins">
    <livewire:components.page-title-nav
        :title="'Food Detail'"
        wire:key="{{ str()->random(50) }}"
        :hasBack="true"
        :hasFilter="false"
    ></livewire:components.page-title-nav>

    <div>
        <img src="{{ Storage::url($food->image) }}" alt="{{ $food->name }}" />
    </div>
    <div
        class="relative z-20 -mt-12 flex-grow overflow-hidden rounded-t-3xl p-4 font-poppins"
    >
        <div class="flex items-center justify-between">
            <!-- <div
                class="flex w-fit items-center gap-1.5 rounded-full bg-[#b7e4c7] px-6 py-2 text-black-100"
            >
                <img
                    src="{{ asset('assets/icons/spoon-icon.svg') }}"
                    alt="Sold"
                />
                <span class="text-sm font-semibold">{{ $food->total_sold ?? 0 }} Terjual</span>
            </div> -->
            <div
                class="{{ $food->percent ? 'block' : 'invisible' }} relative flex items-center justify-center gap-1.5 rounded-full bg-cover px-2 py-1.5"
            >
                <img
                    src="{{ asset('assets/icons/discount-icon.svg') }}"
                    alt="Discount container"
                    class="h-16 w-16"
                />
                <span class="absolute z-10 text-xl font-semibold text-white">
                    {{ $food->percent }}%
                </span>
            </div>
        </div>
        <div class="space-y-2">
            <h3 class="text-xl font-semibold">{{ $food->name }}</h3>
            <div class="flex w-full items-start justify-between">
                <div
                    class="flex items-start gap-1 text-lg font-semibold text-black-100"
                >
                    <span class="mt-1">
                        <img
                            src="{{ asset('assets/icons/price-icon.svg') }}"
                        />
                    </span>
                    <div>
                        <span class="block">
                            RP
                            {{ $food->price_afterdiscount ? number_format($food->price_afterdiscount, 0, ',', '.') : number_format($food->price, 0, ',', '.') }}
                        </span>
                        @if ($food->is_promo)
                            <span
                                class="-mt-1 block text-sm text-black-40 line-through"
                            >
                                RP
                                {{ number_format($food->price, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div
                    class="mt-1 flex items-center gap-1 font-medium text-[#2D5900]"
                >
                    <span>
                        <img
                            src="{{ asset('assets/icons/category-icon.svg') }}"
                            class="scale-[1.1]"
                        />
                    </span>
                    <span>
                        {{ $matchedCategory ? $matchedCategory->name : 'Unknown' }}
                    </span>
                </div>
            </div>
            <div class="break-words text-sm font-medium text-black-50">
                {!! $food->description !!}
            </div>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <button
                wire:click="addToCart"
                class="flex items-center gap-2 rounded-full bg-[#b7e4c7] px-6 py-2 font-semibold text-[#2D5900]"
            >
                <img
                    src="{{ asset('assets/icons/cart-active-icon.svg') }}"
                    alt="Cart"
                />
                <span>Cart</span>
            </button>
            <button
                wire:click="orderNow"
                class="flex items-center gap-2 rounded-full bg-[#2D5900] px-6 py-2 font-semibold text-black-10"
            >
                <span>Checkout</span>
                <img
                    src="{{ asset('assets/icons/arrow-right-white-icon.svg') }}"
                    alt="Cart"
                />
            </button>
        </div>
    </div>

    <livewire:components.toast />
</div>