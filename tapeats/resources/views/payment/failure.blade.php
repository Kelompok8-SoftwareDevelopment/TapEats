<x-layouts.app>
    <div class="grid h-screen place-content-center bg-white font-poppins">
        <img
            src="{{ asset("assets/images/bg-cart-empty.png") }}"
            alt="Tidak ada data"
            class="w-full overflow-hidden rounded-3xl"
        />
        <div class="mt-4 w-full text-center">
            <p class="text-lg font-semibold text-black-80">Transaction Failed</p>
            <p class="mt-2 text-sm font-medium text-black-50">
                Please try again
            </p>

            <a
                href="/cart"
                wire:navigate
                class="mt-4 flex w-full items-center justify-center gap-2 rounded-full bg-[#2D5900] px-6 py-3 font-semibold text-white"
            >
                Return to Cart
            </a>
        </div>
    </div>

    {{-- SweetAlert for error feedback --}}
    @if (session('debug'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Transaksi Gagal',
                    text: "{{ session('debug') }}",
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#2D5900'
                });
            });
        </script>
    @endif
</x-layouts.app>