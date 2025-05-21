<div class="flex items-center justify-between px-4 py-4 font-poppins">
    {{-- Back Button --}}
    @if($hasBack)
    <div
        class="flex h-10 w-10 items-center justify-center rounded-full cursor-pointer transition-all"
        style="background-color: #b7e4c7;"
        x-data
        x-on:click="window.history.back()"
        onmouseover="this.style.backgroundColor='#a4d6b1'"
        onmouseout="this.style.backgroundColor='#b7e4c7'"
    >
        <img src="{{ asset('assets/icons/arrow-left-icon.svg') }}" alt="Back" class="w-4 h-4" />
    </div>
    @else
    <div class="h-10 w-10"></div>
    @endif

    {{-- Title --}}
    <h2 class="text-xl font-semibold text-black-100 text-center flex-1">
        {{ $title }}
    </h2>

    {{-- Spacer buat balance --}}
    <div class="h-10 w-10"></div>
</div>
