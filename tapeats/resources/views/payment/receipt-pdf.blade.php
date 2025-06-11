<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Order Receipt</title>
    <style>
        body {
            font-family: monospace;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #000;
        }
        .container {
            max-width: 380px;
            margin: auto;
            padding: 16px;
            background-color: #fff;
        }
        .text-center {
            text-align: center;
        }
        .text-xs { font-size: 10px; }
        .text-sm { font-size: 12px; }
        .text-xl { font-size: 18px; }
        .font-bold { font-weight: bold; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .mt-4 { margin-top: 16px; }
        .mb-2 { margin-bottom: 8px; }
        .ml-2 { margin-left: 8px; }
        .hr-dashed {
            border-top: 1px dashed #000;
            margin: 12px 0;
        }
        .flex {
            display: flex;
            justify-content: space-between;
        }
        .logo {
            width: 64px;
            height: 64px;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center">
            <h1 class="text-xl font-bold mt-2">OLLIES</h1>
            <p class="text-xs">Jl. Dummy Address No.123, Kota Contoh</p>
            <p class="text-xs">Telp: 081234567890</p>
            <p class="text-xs mt-1">Invoice: {{ $transaction->external_id }}</p>
        </div>

        <div class="hr-dashed"></div>

        <div class="text-xs">
            <p>Date: {{ $transaction->created_at->format('Y-m-d') }}</p>
            <p>Time: {{ $transaction->created_at->format('H:i:s') }}</p>
            <p>Transaction Code: {{ $transaction->code }}</p>
            <p>Table Number: {{ $transaction->barcodes_id }}</p>
            <p>Name: {{ $transaction->name }}</p>
            <p>Phone Number: {{ $transaction->phone }}</p>
        </div>

        <div class="hr-dashed"></div>

        <div class="mb-2">
            @php $totalQty = 0; @endphp
            @foreach ($transaction->items as $index => $item)
                @php
                    $totalQty += $item->quantity;
                    $subtotal = $item->quantity * $item->price;
                @endphp
                <div class="mb-2">
                    <p><strong>{{ $index + 1 }}. {{ $item->food->name }}</strong></p>
                    <div class="ml-2 flex text-xs">
                        <span>{{ $item->quantity }} {{ $item->unit ?? '' }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                        <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="hr-dashed"></div>

        <p>Total QTY: {{ $totalQty }}</p>
        <p>Sub Total: Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
        <p class="font-bold">Total: Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
        <p>Payment Method: {{ $transaction->payment_method }}</p>

        <div class="text-center mt-4">
            <p>Enjoy Your Food</p>
            <p class="text-xs mt-1">Feedback: https://ollies.test/feedback</p>
        </div>
    </div>
</body>
</html>
