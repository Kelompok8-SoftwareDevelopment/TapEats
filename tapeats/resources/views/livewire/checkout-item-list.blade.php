<div>
    @if ($withCheckbox)
        <div class="mb-2">
            <label
                class="relative flex cursor-pointer select-none items-center pl-6"
            >
                <span class="-mt-0.5 text-sm font-medium text-black-50">
                    Select All
                </span>
                <input
                    type="checkbox"
                    wire:model.live="$parent.selectAll"
                    class="peer absolute h-0 w-0 cursor-pointer opacity-0"
                />
                <span
                    class="absolute left-0 top-0 h-4 w-4 rotate-180 rounded ring-2 ring-black-50 transition-colors duration-200 ease-in-out after:absolute after:left-1.5 after:top-1 after:h-3 after:w-1.5 after:rotate-45 after:border-2 after:border-b-0 after:border-r-0 after:border-black-50 peer-checked:ring-primary-50 peer-checked:after:block peer-checked:after:border-primary-50"
                ></span>
            </label>
        </div>
    @endif

    <div class="space-y-4">
    @foreach ($items as $index => $item)
        <div class="flex items-start gap-4 border-b pb-4" wire:key="item-{{ $index }}">
            {{-- Checkbox --}}
            @if ($withCheckbox)
                <label class="relative mt-2 cursor-pointer select-none pl-6">
                    <input
                        type="checkbox"
                        wire:model.live="$parent.cartItems.{{ $index }}.selected"
                        wire:change="$parent.updateSelectedItems()"
                        value="{{ $item["id"] }}"
                        class="peer absolute h-0 w-0 cursor-pointer opacity-0"
                    />
                    <span
                        class="absolute left-0 top-0 h-4 w-4 rotate-180 rounded ring-2 ring-black-50 transition-colors duration-200 ease-in-out after:absolute after:left-1.5 after:top-1 after:h-3 after:w-1.5 after:rotate-45 after:border-2 after:border-b-0 after:border-r-0 after:border-black-50 peer-checked:ring-primary-50 peer-checked:after:block peer-checked:after:border-primary-50"
                    ></span>
                </label>
            @endif

            <img
                src="{{ Storage::url($item["image"]) }}"
                alt="{{ $item["name"] }}"
                class="h-16 w-16 rounded-lg object-cover"
            />

            <div class="flex-1">
                <p class="text-base font-semibold text-black-100">
                    {{ $item["name"] }}
                </p>
                <div class="flex items-center justify-between mt-1">
                    <div>
                        <span class="text-sm font-semibold text-black-50">
                            RP {{ $item["price_afterdiscount"] ? number_format($item["price_afterdiscount"], 0, ",", ".") : number_format($item["price"], 0, ",", ".") }}
                        </span>
                        @if ($item["is_promo"])
                            <span class="ml-1 text-xs text-black-30 line-through">
                                RP {{ number_format($item["price"], 0, ",", ".") }}
                            </span>
                        @endif
                    </div>
                    <span class="text-sm text-black-70">x{{ $item["quantity"] }}</span>
                </div>
            </div>
        </div>
    @endforeach
</div>

</div>
