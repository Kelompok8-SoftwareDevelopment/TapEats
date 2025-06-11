<x-layouts.app>
    <div class="grid min-h-screen place-content-center bg-white font-poppins px-6 py-12">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-4 text-center">Previous Order</h2>

            <div class="mb-4 text-sm text-black-70">
                <p><strong>Name:</strong> {{ session('retry_name') }}</p>
                <p><strong>Phone Number:</strong> {{ session('retry_phone') }}</p>
                <p><strong>Table Number:</strong> {{ session('retry_table') }}</p>
            </div>

            <h3 class="text-base font-semibold mb-2">Pesanan:</h3>
            <ul class="list-disc list-inside text-sm text-black-70 mb-4">
                @foreach(session('retry_cart') as $item)
                    <li>
                        {{ $item['name'] ?? 'Produk tidak dikenal' }} × {{ $item['quantity'] }}
                        @ Rp{{ number_format($item['price'] ?? 0, 0, ',', '.') }}
                    </li>
                @endforeach
            </ul>

            <form method="POST" action="{{ route('payment.confirm') }}">
                @csrf
                <button type="submit"
                    class="w-full bg-[#2D5900] hover:opacity-90 text-white font-semibold py-3 px-6 rounded-full">
                    Retry Payment
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>