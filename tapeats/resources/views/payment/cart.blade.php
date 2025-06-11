<div x-data="{ open: false }" class="flex min-h-screen flex-col bg-white font-poppins">
    <livewire:components.page-title-nav
        :title="'Cart'"
        wire:key="'cart-title-nav'"
        :hasBack="true"
        :hasFilter="false" />

    <div class="container">
        <h2 class="mb-4 text-lg font-medium text-black-100">Newly Added</h2>

        @php
            $validCartItems = array_filter($cartItems, fn($item) => isset($item['id']));
        @endphp

        @if (!empty($validCartItems) && count($validCartItems) > 0)
            <livewire:components.menu-item-list
                :items="$validCartItems"
                wire:key="{{ str()->random(50) }}" />

            <div class="mt-6 flex items-center justify-between">
                <button
                    x-on:click="open = true"
                    :disabled="{{ count($selectedItems) < 1 ? 'true' : 'false' }}"
                    class="flex items-center gap-2 rounded-full px-6 py-3 font-semibold {{ count($selectedItems) < 1 
                        ? 'bg-primary-10 text-primary-50 opacity-50 cursor-not-allowed' 
                        : 'bg-primary-10 text-primary-50' }}">
                    Delete ({{ count($selectedItems) }})
                </button>

                <button
                    wire:click="checkout"
                    class="flex items-center gap-2 rounded-full bg-primary-50 px-6 py-3 font-semibold text-black-10">
                    <span>Order Now</span>
                    <img
                        src="{{ asset('assets/icons/arrow-right-white-icon.svg') }}"
                        alt="Cart" />
                </button>
            </div>
        @else
            <div>
                <img
                    src="{{ asset('assets/images/bg-cart-empty.png') }}"
                    alt="No data"
                    class="w-full overflow-hidden rounded-3xl" />
                <div class="mt-4 w-full text-center">
                    <p class="text-lg font-semibold text-black-80">No Data</p>
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