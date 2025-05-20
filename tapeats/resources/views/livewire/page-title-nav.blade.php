<div class="container flex items-center justify-between p-4 font-poppins">
    <div
        class="{{ $hasBack ? 'block' : 'invisible' }} grid aspect-square cursor-pointer place-content-center rounded-full p-3 transition-all"
        style="background-color: #b7e4c7;"
        x-data
        x-on:click="window.history.back()"
        onmouseover="this.style.backgroundColor='#a4d6b1'"
        onmouseout="this.style.backgroundColor='#b7e4c7'"
    >
        <img src="{{ asset('assets/icons/arrow-left-icon.svg') }}" alt="Back" />
    </div>

    <h2 class="text-xl font-semibold text-black-100">{{ $title }}</h2>

    <div
        class="{{ $hasFilter ? 'block' : 'invisible' }} grid aspect-square cursor-pointer place-content-center rounded-full p-3 transition-all"
        style="background-color: #b7e4c7;"
        x-on:click="open = !open"
        onmouseover="this.style.backgroundColor='#a4d6b1'"
        onmouseout="this.style.backgroundColor='#b7e4c7'"
    >
        <img src="{{ asset('assets/icons/filter-icon.svg') }}" alt="Search" />
    </div>
</div>
