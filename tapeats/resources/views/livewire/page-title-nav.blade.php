<div class="relative flex items-center justify-center p-4 font-poppins">
    {{-- Back Button --}}
    <div
        class="{{ $hasBack ? 'block' : 'invisible' }} absolute left-4 grid aspect-square cursor-pointer place-content-center rounded-full p-3 transition-all"
        style="background-color: #b7e4c7;"
        x-data
        x-on:click="window.history.back()"
        onmouseover="this.style.backgroundColor='#a4d6b1'"
        onmouseout="this.style.backgroundColor='#b7e4c7'"
    >
        <img src="{{ asset('assets/icons/arrow-left-icon.svg') }}" alt="Back" />
    </div>

    {{-- Title --}}
    <h2 class="text-xl font-semibold text-black-100 text-center w-full">{{ $title }}</h2>
</div>
