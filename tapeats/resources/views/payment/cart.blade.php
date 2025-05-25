<div
    x-data="{ open: false }"
    class="flex min-h-screen flex-col bg-white font-poppins"
>
    <livewire:components.page-title-nav
        :title="'Cart'"
        wire:key="{{ str()->random(50) }}"
        :hasBack="true"
        :hasFilter="false"
    />

    <div class="container">
        <h2 class="mb-4 text-lg font-medium text-black-100">
            Newly Added
        </h2>

        @if (isset($cartItems) && count($cartItems) > 0)
            <livewire:components.menu-item-list
                :items="$cartItems"
                wire:key="{{ str()->random(50) }}"
            />

            <div class="mt-6 flex items-center justify-between">
                <button
                    x-on:click="open = true"
                    class="flex items-center gap-2 rounded-full bg-[#b7e4c7] px-6 py-2 font-semibold text-[#2D5900]"
                >
                    Delete ({{ count($selectedItems) }})
                </button>
                <button
                    x-bind:disabled="! {{ count($selectedItems) }} > 0"
                    wire:click="checkout"
                    class="flex items-center gap-2 rounded-full bg-[#2D5900] px-6 py-2 font-semibold text-black-10"
                >
                    <span>Order Now</span>
                    <img
                        src="{{ asset("assets/icons/arrow-right-white-icon.svg") }}"
                        alt="Cart"
                    />
                </button>
            </div>
        @else
            <div>
                <img
                    src="{{ asset("assets/images/bg-cart-empty.png") }}"
                    alt="No data"
                    class="w-full overflow-hidden rounded-3xl"
                />
                <div class="mt-4 w-full text-center">
                    <p class="text-lg font-semibold text-black-80">
                        No Data
                    </p>
                    <p class="text-sm font-medium text-black-30">
                        Please Add Your Order Here
                    </p>
                </div>
            </div>
        @endif
    </div>

    <div x-show="open">
        <livewire:components.delete-confirm-modal />
    </div>
</div>
