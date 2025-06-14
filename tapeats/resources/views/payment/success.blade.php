<x-layouts.app>
    <div class="min-h-screen flex flex-col justify-between">
        <div class="max-w-md mx-auto mt-10 p-6 bg-white shadow rounded font-mono text-sm text-black-80">
            <div class="text-center">
                <h1 class="text-xl font-bold mt-2">OLLIES</h1>
                <p class="text-xs">Jl. Dummy Address No.123, Kota Contoh</p>
                <p class="text-xs">Telp: 081234567890</p>
                <p class="text-xs mt-1">Invoice: {{ $transaction->external_id }}</p>
            </div>

            <hr class="my-3 border-dashed">

            <div class="text-xs">
                <p>Date: {{ $transaction->created_at->format('Y-m-d') }}</p>
                <p>Time: {{ $transaction->created_at->format('H:i:s') }}</p>
                <p>Transaction Code: {{ $transaction->code }}</p>
                <p>Table Number: {{ $transaction->barcodes_id }}</p>
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
            <p>Payment (Cash): Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
            <p>Back: Rp 0</p>

            <div class="text-center mt-4">
                <p>Thank You for Your Order</p>
                <p class="text-xs mt-1">Feedback: https://ollies.test/feedback</p>
            </div>
        </div>

        <div class="max-w-md mx-auto mt-6 px-6 space-y-3 mb-10">
            <a
                href="/"
                wire:navigate
                class="flex w-full items-center justify-center gap-2 rounded-full bg-[#b7e4c7] px-6 py-3 font-semibold text-[#2D5900]"
            >
                Back to Menu
            </a>

            <form action="{{ route('transactions.download', $transaction->id) }}" method="GET">
                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-full bg-[#2D5900] px-6 py-3 font-semibold text-white hover:opacity-90 transition"
                >
                    Download Receipt
                </button>
            </form>
        </div>

    </div>
</x-layouts.app>
