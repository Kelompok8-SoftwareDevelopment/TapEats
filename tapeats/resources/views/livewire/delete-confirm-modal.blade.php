<x-modal :title="''" :showClose="false">
    @section("content")
        <div class="mb-4 flex flex-col items-center">
            <img
                src="{{ asset("assets/icons/warning-icon.svg") }}"
                alt="Warning"
            />
            <p class="my-4 text-center text-2xl font-semibold text-black-80">
                "Are you sure you want to remove this item?"            </p>
        </div>
        <div class="flex items-center justify-between gap-4">
            <button
                x-on:click="open = false"
                type="button"
                class="w-1/2 cursor-pointer rounded-full bg-[#2D5900] px-5 py-2 font-semibold text-white outline-none hover:bg-[#3b7a01]"
            >
                No
            </button>
            <button
                type="submit"
                x-on:click="
                    $wire.$parent.deleteSelected()
                    open = false
                "
                class="w-1/2 cursor-pointer rounded-full bg-[#2D5900] px-5 py-2 font-semibold text-white hover:bg-[#3b7a01]"
            >
                Yes
            </button>
        </div>
    @endsection
</x-modal>
