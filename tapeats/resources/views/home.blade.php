<div class="bg-white min-h-screen">
    <header
        style="
            background-image: url('{{ asset("assets/images/ollies1.jpg") }}');
        "
        class="rounded-b-3xl bg-cover font-poppins">
        <div class="p-6">
            <div class="rounded-3xl bg-white px-5 py-2">
                <div class="flex items-center gap-1.5">
                    <img
                        src="{{ asset("assets/images/logo_ollies2.webp") }}"
                        class="w-12 h-12 object-contain"
                        alt="TapEats logo" />
                    <span class="text-lg font-semibold text-black">
                        OLLIE'S
                    </span>
                </div>
                <div
                    style="
                        background-image: url('{{ asset("assets/images/ollies3.jpg") }}');
                    "
                    class="rounded-2xl bg-primary-50 px-5 py-3.5 text-center text-white">
                    <span class="tex-sm block font-semibold">Table Number</span>
                    <a
                        href="/"
                        class="mt-1.5 block cursor-pointer text-3xl font-bold">
                        {{ $tableNumber ?? "" }}
                    </a>
                </div>
            </div>
            <div class="mt-5">
                <h2
                    class="mb-3 text-center text-xl font-semibold text-white">
                    What do You Want to Eat?
                </h2>
                <!-- <form class="flex overflow-hidden rounded-full" method="GET"> -->
                <form wire:submit.prevent class="flex overflow-hidden rounded-full">
                    <input
                        type="search"
                        class="h-12 w-full appearance-none rounded-full px-4 placeholder:font-semibold placeholder:text-black-30"
                        placeholder="Search"
                        wire:model.live.debounce.300ms="term" />
                    @if (! $term)
                    <img
                        src="{{ asset("assets/icons/search-icon.svg") }}"
                        alt="Search Icon"
                        class="-ml-10" />
                    @endif
                </form>
            </div>
        </div>
    </header>

    <main
        class="mb-24 mt-8 space-y-6 font-poppins"
        x-data="{ open: @entangle(" isCustomerDataComplete") }">
        <!-- <div wire:loading class="w-full">
            <div class="my-2 w-full">
                <p class="text-center text-black-70">
                    Hang tight, we’re finding your food...
                </p>
            </div>
        </div> -->
        <div wire:loading.remove>
            @if ($term == "")
            <div>
                <div class="container flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-black-100">
                        Today's promo
                    </h3>
                    <a
                        href="/food/promo"
                        wire:navigate
                        class="block font-semibold text-[#2D5900]">
                        See More
                    </a>
                </div>
                <div
                    class="hide-scrollbar ml-4 flex items-stretch gap-4 overflow-x-auto py-4">
                    @if (isset($promos) && count($promos) > 0)
                    @foreach ($promos as $promo)
                    <livewire:components.food-card
                        wire:key="promo-{{ $promo->id }}"
                        :data="$promo"
                        :categories="$categories"
                        :isGrid="false" />
                    @endforeach
                    @else
                    <div class="my-2 w-full">
                        <p class="text-center text-black-70">
                            No promo available
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="container">
                <div
                    style="
                        background-image: url('{{ asset("assets/images/ollies2.jpg") }}');
                    "
                    class="space-y-6 rounded-2xl bg-cover p-6 text-center text-white">
                    <h3 class="text-left text-2xl font-semibold text-white">
                        Find Comfort
                        <br />
                        in Our Flavours!
                    </h3>
                    <a
                        href="/food"
                        wire:navigate
                        class="block w-fit rounded-full bg-[#2D5900] px-6 py-2 font-semibold text-white">
                        All Menu
                    </a>
                </div>
            </div>

            <div class="mt-4">
                <div class="container flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-black-100">
                        Everyone's Favorite
                    </h3>
                    <a
                        href="/food/favorite"
                        wire:navigate
                        class="block font-semibold text-[#2D5900]">
                        See More
                    </a>
                </div>
                <div
                    class="hide-scrollbar ml-4 flex items-stretch space-x-4 overflow-x-auto py-4">
                    @if (isset($favorites) && count($favorites) > 0)
                    @foreach ($favorites as $favorite)
                    <livewire:components.food-card
                        wire:key="favorite-{{ $favorite->id }}"
                        :data="$favorite"
                        :categories="$categories"
                        :isGrid="false" />
                    @endforeach
                    @else
                    <div class="my-2 w-full">
                        <p class="text-center text-black-70">
                            No favorite available
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @else
            @if ($searchResult->isEmpty())
            <div class="my-2 w-full">
                <p class="text-center text-black-70">
                    No matching food items found
                </p>
            </div>
            @else
            <div
                class="container mb-24 grid grid-cols-2 items-center gap-4">
                @foreach ($searchResult as $result)
                <livewire:components.food-card
                    wire:key="{{ str()->random(50) }}"
                    :data="$result"
                    :categories="$categories" />
                @endforeach
            </div>
            @endif
            @endif
    </main>
</div>