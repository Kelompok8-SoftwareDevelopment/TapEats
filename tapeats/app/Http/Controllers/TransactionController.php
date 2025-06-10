<?php

namespace App\Http\Controllers;

use App\Models\Barcodes;
use App\Models\Category;
use App\Models\Foods;
use App\Models\Transaction;
use App\Models\TransactionItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;

class TransactionController extends Controller
{
    var $apiInstance = null;

    public function __construct()
    {
        $xenditKey = config('xendit.secret_key');
        Log::info('Xendit API key loaded: ' . (empty($xenditKey) ? 'null' : 'set (length: ' . strlen($xenditKey) . ')'));
        if (empty($xenditKey)) {
            Log::error('Xendit API key is empty or not set in config');
        }
        Configuration::setXenditKey($xenditKey);
        $this->apiInstance = new InvoiceApi();
    }

    public function handlePayment(Request $request)
    {
        Log::info('Session sebelum payment:', session()->all());

        $action = $request->input('action');
        Log::info('handlePayment called with action: ' . $action . ', request: ' . json_encode($request->all()));

        if ($action === 'pay') {
            // Jika ada transaksi unpaid di session
            if (session()->has('has_unpaid_transaction')) {
                $externalId = session('external_id');
                $tx = Transaction::where('external_id', $externalId)->first();

                if ($tx && $tx->payment_status === 'PENDING') {
                    Log::info("Masih PENDING, redirect ke checkout_link lama: {$tx->checkout_link}");
                    return redirect($tx->checkout_link);
                }

                // Kalau sudah PAID atau EXPIRED, clear session agar bisa buat invoice baru
                session()->forget([
                    'has_unpaid_transaction',
                    'external_id',
                    'checkout_link',
                ]);
                Log::info('Session setelah clear (expired/paid):', session()->all());
            }
            // Tidak ada pending, lanjut bikin invoice baru
            return $this->processPayment($request);
        }

        if ($action === 'continue') {
            $externalId = session('external_id');
            Log::info('Continue action - external_id from session: ' . $externalId);

            if (empty($externalId)) {
                Log::warning('No external_id in session for continue action');
                return view('payment.failure');
            }

            $transaction = Transaction::where('external_id', $externalId)->first();
            if (! $transaction) {
                Log::warning("Transaction dengan external_id {$externalId} tidak ditemukan");
                return view('payment.failure');
            }

            return redirect($transaction->checkout_link);
        }

        Log::warning('Invalid action: ' . $action);
        abort(400, 'Invalid action.');
    }

    public function processPayment(Request $request)
    {
        Log::info('processPayment started', ['request' => $request->all()]);
        $uuid = (string) Str::uuid();
        Log::info('Generated UUID: ' . $uuid);

        $sessionToken = session('payment_token');
        $requestToken = $request->input('token');
        Log::info('Token check - session: ' . $sessionToken . ', request: ' . $requestToken);
        if ($sessionToken !== $requestToken) {
            Log::warning('Token mismatch - session: ' . $sessionToken . ', request: ' . $requestToken);
            return redirect()->route('payment.failure')->with('debug', 'Token mismatch: session_token=' . $sessionToken . ', request_token=' . $requestToken);
        }

        $cartItems = session('cart_items');
        $name = session('name');
        $phone = session('phone');
        $tableNumber = session('table_number');
        if (!$tableNumber) {
            return redirect()->route('product.scan')->with('message', 'Nomor meja tidak ditemukan.');
        }

        Log::info('Session data - cart_items: ' . json_encode($cartItems) . ', name: ' . $name . ', phone: ' . $phone . ', table_number: ' . $tableNumber);
        if (empty($cartItems) || empty($name) || empty($phone) || empty($tableNumber)) {
            Log::warning('Missing session data - cart_items: ' . json_encode($cartItems) . ', name: ' . $name . ', phone: ' . $phone . ', table_number: ' . $tableNumber);
            return response()->json(['success' => false, 'message' => 'Data is empty'], 400);
        }

        $barcode = Barcodes::where('table_number', $tableNumber)->first();

        if (!$barcode) {
            Log::warning("Barcode dengan nomor meja {$tableNumber} tidak ditemukan.");
            return redirect()->route('payment.failure')->with('debug', "Nomor meja {$tableNumber} tidak terdaftar.");
        }

        $barcodesId = $barcode->id;

        $transactionCode = 'TRX_' . mt_rand(100000, 999999);
        Log::info('Generated transaction code: ' . $transactionCode);

        try {
            $subTotal = 0;
            $items = collect($cartItems)->map(function ($item) use (&$subTotal) {
                $price = isset($item['price_afterdiscount']) ? $item['price_afterdiscount'] : $item['price'];
                $foodSubtotal = $price * $item['quantity'];
                $subTotal += $foodSubtotal;
                $category = Category::find($item['categories_id'])->name;
                $url = route('product.detail', ['id' => $item['id']]);
                return ['name' => $item['name'], 'quantity' => $item['quantity'], 'price' => (int) $price, 'category' => $category, 'url' => $url];
            })->values()->toArray();
            Log::info('Calculated items and subtotal: ' . json_encode($items) . ', subtotal: ' . $subTotal);

            $ppn = 0.11 * $subTotal;
            Log::info('Calculated PPN: ' . $ppn);

            $description = <<<END
            Pembayaran makanan<br>
            Nomor Meja: {$tableNumber}<br>
            Nama: {$name}<br>
            Nomor Telepon: {$phone}<br>
            Kode Transaksi: {$transactionCode}<br>
            END;
            Log::info('Generated description: ' . $description);

            $createInvoiceRequest = new CreateInvoiceRequest([
                'external_id' => $uuid,
                'amount' => $subTotal + $ppn,
                'description' => $description,
                'invoice_duration' => 60, // Sementara 5 Menit
                'currency' => 'IDR',
                'customer' => ['given_names' => $name, 'mobile_number' => $phone],
                'success_redirect_url' => route('payment.success'),
                'failure_redirect_url' => route('payment.failure'),
                'locale' => 'id',
                'items' => $items,
                'fees' => [['type' => 'PPN 11%', 'value' => $ppn]],
                'customer_notification_preference' => ['invoice_paid' => ['whatsapp']],
            ]);
            Log::info('Created Invoice Request: ' . json_encode($createInvoiceRequest));

            $invoice = $this->apiInstance->createInvoice($createInvoiceRequest);
            Log::info('Invoice created, response: ' . json_encode($invoice));

            $checkoutLink = $invoice->getInvoiceUrl();
            if (empty($checkoutLink)) {
                Log::warning('No checkout URL in invoice response: ' . json_encode($invoice));
                return redirect()->route('payment.failure')->with('debug', 'No checkout URL in invoice response');
            }

            foreach ($cartItems as $item) {
                $food = Foods::find($item['id']);

                if (!$food) {
                    return redirect()->route('payment.failure')->with('debug', "Produk ID {$item['id']} tidak ditemukan.");
                }

                if ($food->stock < $item['quantity']) {
                    return redirect()->route('payment.failure')->with('debug', "Stok untuk {$food->name} tidak cukup. Stock available: {$food->stock}");
                }

                $food->stock -= $item['quantity'];
                $food->save();
            }


            $transaction = new Transaction();
            $transaction->checkout_link = $checkoutLink;
            $transaction->payment_method = "PENDING";
            $transaction->phone = $phone;
            $transaction->name = $name;
            $transaction->subtotal = $subTotal;
            $transaction->ppn = $ppn;
            $transaction->barcodes_id = $barcodesId;
            $transaction->total = $subTotal + $ppn;
            $transaction->external_id = $uuid;
            $transaction->code = $transactionCode;
            $transaction->payment_status = $invoice->getStatus() ?? 'PENDING';
            $transaction->save();
            Log::info('Transaction saved, id: ' . $transaction->id . ', status: ' . $transaction->payment_status);

            foreach ($cartItems as $cartItem) {
                $price = isset($cartItem['price_afterdiscount']) ? $cartItem['price_afterdiscount'] : $cartItem['price'];
                TransactionItems::create([
                    'transaction_id' => $transaction->id,
                    'foods_id' => $cartItem['id'],
                    'quantity' => $cartItem['quantity'],
                    'price' => $price,
                    'subtotal' => $price * $cartItem['quantity'],
                ]);
            }
            Log::info('Transaction items created for transaction: ' . $transaction->id);

            session(['external_id' => $uuid]);
            session(['has_unpaid_transaction' => true]);
            Log::info('Session updated with external_id: ' . $uuid);

            return redirect($checkoutLink);
        } catch (\Exception $e) {
            Log::error('Failed to create invoice', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('payment.failure')->with('debug', 'Exception: ' . $e->getMessage());
        }
    }

    public function paymentStatus($id)
    {
        Log::info('paymentStatus called with id: ' . $id);
        try {
            $result = $this->apiInstance->getInvoices(null, $id);
            Log::info('Invoice lookup result: ' . json_encode($result));

            $transaction = Transaction::where('external_id', $id)->firstOrFail();
            Log::info('Transaction found, id: ' . $transaction->id);

            if ($transaction->payment_status === 'SETTLED') {
                $this->clearSession();
                Log::info('Payment settled, clearing session');
                return response()->json(['success' => true, 'message' => 'Pembayaran anda telah berhasil diproses']);
            }

            $transaction->payment_status = $result[0]['status'];
            $transaction->payment_method = $result[0]['payment_method'];
            $transaction->save();
            Log::info('Transaction updated, new status: ' . $transaction->payment_status);

            $this->clearSession();
            Log::info('Session cleared after update');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to get invoice', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return view('payment.failure');
        }
    }

    public function handleWebhook(Request $request)
    {
        Log::info('handleWebhook called, payload: ' . json_encode($request->all()));
        $webhookToken = $request->header('x-callback-token');
        $expectedToken = config('xendit.webhook_token');
        Log::info('Webhook token check - received: ' . $webhookToken . ', expected: ' . $expectedToken);

        if ($webhookToken !== $expectedToken) {
            Log::warning('Unauthorized webhook request');
            return response()->json(['message' => 'Unauthorized webhook request.'], 401);
        }

        try {
            $data = $request->all();
            $external_id = $data['external_id'];
            $status = $data['status'];
            $payment_method = isset($data['payment_method']) ? $data['payment_method'] : null;
            Log::info('Webhook data extracted - external_id: ' . $external_id . ', status: ' . $status . ', payment_method: ' . $payment_method);

            if (! $external_id || ! $status) {
                Log::warning('Invalid webhook payload: missing external_id or status');
                return response()->json(['message' => 'Invalid paylod'], 400);
            }

            $transaction = Transaction::where('external_id', $external_id)->first();
            if (!$transaction) {
                Log::warning('Transaction not found for external_id: ' . $external_id);
                return response()->json(['message' => 'Transaction not found.'], 404);
            }

            $transaction->payment_status = $status;
            if ($payment_method) {
                $transaction->payment_method = $payment_method;
            }
            $transaction->save();
            Log::info('Transaction updated, id: ' . $transaction->id . ', new status: ' . $status);

            if ($status === 'EXPIRED') {
                $items = TransactionItems::where('transaction_id', $transaction->id)->get();

                foreach ($items as $item) {
                    $food = Foods::find($item->foods_id);
                    if ($food) {
                        $food->stock += $item->quantity;
                        $food->save();
                        Log::info("Stok dikembalikan untuk {$food->name}, jumlah: {$item->quantity}");
                    }
                }
            }

            if (in_array($status, ['PAID', 'EXPIRED'], true)) {
                session()->forget([
                    'has_unpaid_transaction',
                    'external_id',
                    'checkout_link',
                ]);
                Log::info("Session cleared after webhook for status: {$status}");
            }

            // $this->clearSession();
            // Log::info('Session cleared after webhook');

            return response()->json([
                'code' => 200,
                'message' => 'Webhook received',
                'status' => $status,
                'payment_method' => $payment_method,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to handle webhook', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to handle webhook.'], 500);
        }
    }

    public function clearSession()
    {
        $keys = ['name', 'external_id', 'has_unpaid_transaction', 'cart_items', 'payment_token'];
        Session::forget($keys);
        Session::save();
        Log::info('Session cleared, removed keys: ' . json_encode($keys));
    }
}
