<nav
    class="fixed bottom-0 z-20 w-full max-w-md rounded-t-3xl bg-white p-4 font-poppins"
    style="box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.1);"
>
    <div class="flex items-center justify-around">
        <!-- Home -->
        <a
            href="/"
            wire:navigate
            class="group flex flex-col items-center transition-transform hover:scale-110"
        >
            <img
                src="{{ request()->routeIs('home') ? asset('assets/icons/home-active-icon.svg') : asset('assets/icons/home-icon.svg') }}"
                alt="Home"
                class="h-6 w-6 transition-transform"
            />
            <span
                class="mt-1 text-sm transition-colors 
                {{ request()->routeIs('home') ? 'text-[#2A4F00]' : 'text-black-100 group-hover:text-[#2D5900]' }}"
            >
                Home
            </span>
        </a>

        <!-- Cart -->
        <a
            href="/cart"
            wire:navigate
            class="group flex flex-col items-center transition-transform hover:scale-110"
        >
            <img
                src="{{ request()->routeIs('payment.cart') ? asset('assets/icons/cart-active-icon.svg') : asset('assets/icons/cart-icon.svg') }}"
                alt="Cart"
                class="h-6 w-6 transition-transform"
            />
            <span
                class="mt-1 text-sm transition-colors 
                {{ request()->routeIs('payment.cart') ? 'text-[#2A4F00]' : 'text-black-100 group-hover:text-[#2D5900]' }}"
            >
                Cart
            </span>
        </a>

        <!-- Promo -->
        <a
            href="/food/promo"
            wire:navigate
            class="group flex flex-col items-center transition-transform hover:scale-110"
        >
            <img
                src="{{ request()->routeIs('product.promo') ? asset('assets/icons/promo-active-icon.svg') : asset('assets/icons/promo-icon.svg') }}"
                alt="Promo"
                class="h-6 w-6 transition-transform"
            />
            <span
                class="mt-1 text-sm transition-colors 
                {{ request()->routeIs('product.promo') ? 'text-[#2A4F00]' : 'text-black-100 group-hover:text-[#2D5900]' }}"
            >
                Promo
            </span>
        </a>

        <!-- All Food -->
        <a
            href="/food"
            wire:navigate
            class="group flex flex-col items-center transition-transform hover:scale-110"
        >
            <img
                src="{{ request()->routeIs('product.index') ? asset('assets/icons/food-active-icon.svg') : asset('assets/icons/food-icon.svg') }}"
                alt="All Food"
                class="h-6 w-6 transition-transform"
            />
            <span
                class="mt-1 text-sm transition-colors 
                {{ request()->routeIs('product.index') ? 'text-[#2A4F00]' : 'text-black-100 group-hover:text-[#2D5900]' }}"
            >
                All Food
            </span>
        </a>
    </div>
</nav>
