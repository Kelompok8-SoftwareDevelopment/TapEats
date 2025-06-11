<x-layouts.app>
    <div class="min-h-screen flex flex-col justify-between">
        <div class="max-w-md mx-auto mt-10 p-6 bg-white shadow rounded font-mono text-sm text-black-80">
            <div class="text-center">
                <img src="{{ asset('assets/icons/store-icon.svg') }}" alt="Logo Toko" class="mx-auto w-16 h-16">
                <h1 class="text-xl font-bold mt-2">OLLIES</h1>
                <p class="text-xs">Jl. Dummy Address No.123, Kota Contoh</p>
                <p class="text-xs">Telp: 081234567890</p>
                <p class="text-xs mt-1">Invoice: {{ $transaction->external_id }}</p>
            </div>

            <hr class="my-3 border-dashed">

            <div class="text-xs">
                <p>Tanggal: {{ $transaction->created_at->format('Y-m-d') }}</p>
                <p>Waktu: {{ $transaction->created_at->format('H:i:s') }}</p>
                <p>Kode Transaksi: {{ $transaction->code }}</p>
                <p>No. Meja: {{ $transaction->barcodes_id }}</p>
            </div>

            <hr class="my-3 border-dashed">

            <div class="mb-2">
                @php $totalQty = 0; @endphp
                @foreach ($transaction->items as $index => $item)
                    @php
                        $totalQty += $item->quantity;
                        $subtotal = $item->quantity * $item->price;
                    @endphp
                    <div class="mb-2">
                        <p><strong>{{ $index + 1 }}. {{ $item->food->name }}</strong></p>
                        <p class="text-xs ml-2">
                            {{ $item->quantity }} {{ $item->unit ?? '' }} x {{ number_format($item->price, 0, ',', '.') }}
                            <span class="float-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </p>
                    </div>
                @endforeach
            </div>

            <hr class="my-3 border-dashed">

            <p>Total QTY: {{ $totalQty }}</p>
            <p>Sub Total: Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
            <p class="font-bold">Total: Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
            <p>Bayar (Cash): Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
            <p>Kembali: Rp 0</p>

            <div class="text-center mt-4">
                <p>Terima kasih telah berbelanja</p>
                <p class="text-xs mt-1">Kritik & Saran: https://ollies.test/feedback</p>
            </div>
        </div>

        <div class="max-w-md mx-auto mt-6 px-6 space-y-3 mb-10">
            <a
                href="/"
                class="flex w-full items-center justify-center gap-2 rounded-full bg-primary-50 px-6 py-3 font-semibold text-black-10"
            >
                Kembali ke Menu
            </a>

            <form action="{{ route('transactions.download', $transaction->id) }}" method="GET">
                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-full bg-blue-500 px-6 py-3 font-semibold text-white hover:bg-blue-600 transition"
                >
                    Download Receipt
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
