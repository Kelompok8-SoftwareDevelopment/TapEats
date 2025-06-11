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
use Barryvdh\DomPDF\Facade\Pdf;

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

                if ($tx) {
                    // Cek status real-time dari Xendit
                    $realStatus = $this->checkInvoiceStatus($externalId);

                    if ($realStatus) {
                        // Update status di database
                        $tx->payment_status = $realStatus;
                        $tx->save();

                        // Jika masih PENDING, redirect ke checkout link
                        if ($realStatus === 'PENDING') {
                            Log::info("Masih PENDING, redirect ke checkout_link: {$tx->checkout_link}");
                            return redirect($tx->checkout_link);
                        }

                        // Jika EXPIRED atau PAID, clear session dan handle accordingly
                        if (in_array($realStatus, ['EXPIRED', 'PAID'])) {
                            $this->clearSession();
                            Log::info("Status {$realStatus}, session cleared");

                            if ($realStatus === 'EXPIRED') {
                                // Simpan ulang data ke session retry (untuk konfirmasi ulang)
                                session([
                                    'retry_cart' => session('cart_items'),
                                    'retry_name' => session('name'),
                                    'retry_phone' => session('phone'),
                                    'retry_table' => session('table_number'),
                                ]);

                                $this->clearSession(); // Bersihkan sesi transaksi saja, bukan form

                                Log::info("Transaction expired. Stock restored. Redirect to retry confirmation page.");
                                return redirect()->route('payment.retry'); // Buat route & halaman ini nanti
                            }

                            if ($realStatus === 'PAID') {
                                $this->clearSession(true); // Clear semua termasuk payment_token
                                return redirect()->route('payment.success');
                            }
                        }
                    } else {
                        // Jika tidak bisa cek status, clear session untuk safety
                        $this->clearSession();
                        Log::warning("Cannot check invoice status, clearing session for safety");
                    }
                } else {
                    // Transaksi tidak ditemukan, clear session
                    $this->clearSession();
                    Log::warning("Transaction not found for external_id: {$externalId}, clearing session");
                }
            }

            // Lanjut bikin invoice baru
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
            if (!$transaction) {
                Log::warning("Transaction dengan external_id {$externalId} tidak ditemukan");
                $this->clearSession();
                return view('payment.failure');
            }

            // Cek status real-time sebelum redirect
            $realStatus = $this->checkInvoiceStatus($externalId);
            if ($realStatus && $realStatus !== 'PENDING') {
                $transaction->payment_status = $realStatus;
                $transaction->save();

                if ($realStatus === 'EXPIRED') {
                    $this->clearSession();
                    return view('payment.failure')->with('message', 'Transaksi sudah expired');
                }

                if ($realStatus === 'PAID') {
                    $this->clearSession(true); // Clear semua termasuk payment_token
                    return redirect()->route('payment.success');
                }
            }

            return redirect($transaction->checkout_link);
        }

        Log::warning('Invalid action: ' . $action);
        abort(400, 'Invalid action.');
    }

    private function checkInvoiceStatus($externalId)
    {
        try {
            $result = $this->apiInstance->getInvoices(null, $externalId);
            if (!empty($result) && isset($result[0]['status'])) {
                return $result[0]['status'];
            }
        } catch (\Exception $e) {
            Log::error('Failed to check invoice status: ' . $e->getMessage());
        }
        return null;
    }

    private function restoreStock($transactionId)
    {
        try {
            $items = TransactionItems::where('transaction_id', $transactionId)->get();

            foreach ($items as $item) {
                $food = Foods::find($item->foods_id);
                if ($food) {
                    $food->stock += $item->quantity;
                    $food->save();
                    Log::info("Stock restored for {$food->name}, quantity: {$item->quantity}");
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to restore stock: ' . $e->getMessage());
        }
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
                $category = Category::find($item['categories_id'] ?? null)?->name ?? 'Uncategorized';
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
                'failure_redirect_url' => route('payment.status.redirect', ['id' => $uuid]),
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

            // Set session untuk tracking
            session([
                'external_id' => $uuid,
                'has_unpaid_transaction' => true,
                'checkout_link' => $checkoutLink
            ]);
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
                $this->clearSession(true); // Clear semua termasuk payment_token
                Log::info('Payment settled, clearing session');
                return response()->json(['success' => true, 'message' => 'Pembayaran anda telah berhasil diproses']);
            }

            $newStatus = $result[0]['status'];
            $transaction->payment_status = $newStatus;
            $transaction->payment_method = $result[0]['payment_method'];
            $transaction->save();
            Log::info('Transaction updated, new status: ' . $transaction->payment_status);

            // Clear session jika status final
            if (in_array($newStatus, ['PAID', 'SETTLED', 'EXPIRED'])) {

                if (in_array($newStatus, ['PAID', 'SETTLED'])) {
                    $this->clearSession(true); // Clear semua untuk payment success
                } else {
                    $this->clearSession(); // Hanya clear transaction data untuk expired
                }

                Log::info('Session cleared after status update to: ' . $newStatus);
            }

            return response()->json(['success' => true, 'status' => $newStatus]);
        } catch (\Exception $e) {
            Log::error('Failed to get invoice', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to get payment status'], 500);
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

            if (!$external_id || !$status) {
                Log::warning('Invalid webhook payload: missing external_id or status');
                return response()->json(['message' => 'Invalid payload'], 400);
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

            // Jika status expired balikin stock ke database
            if ($status === 'EXPIRED') {
                $this->restoreStock($transaction->id);
                Log::info("Stock restored for expired transaction: {$transaction->id}");
            }

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

    public function clearSession($clearAll = false)
    {
        if ($clearAll) {
            // Clear semua termasuk payment_token (ketika user benar-benar selesai atau keluar)
            $keys = ['name', 'phone', 'external_id', 'has_unpaid_transaction', 'cart_items', 'payment_token', 'checkout_link'];
        } else {
            // Clear hanya transaction-related session, keep payment_token untuk transaksi selanjutnya
            $keys = ['external_id', 'has_unpaid_transaction', 'checkout_link'];
        }

        Session::forget($keys);
        Session::save();
        Log::info('Session cleared, removed keys: ' . json_encode($keys));
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function food()
    {
        return $this->belongsTo(Food::class, 'foods_id');
    }

    public function download($id)
    {
        $transaction = Transaction::with('items.food')->findOrFail($id);

        $pdf = Pdf::loadView('payment.receipt-pdf', compact('transaction'));
        return $pdf->download('receipt-' . $transaction->code . '.pdf');
    }

    // Method baru untuk handle payment failure
    public function paymentFailure()
    {
        Log::info('Payment failure page accessed');

        // Clear session saat user sampai di failure page
        if (session()->has('has_unpaid_transaction')) {
            $externalId = session('external_id');
            if ($externalId) {
                // Cek apakah transaksi masih ada dan expired
                $transaction = Transaction::where('external_id', $externalId)->first();
                if ($transaction && $transaction->payment_status === 'PENDING') {
                    // Cek status real-time
                    $realStatus = $this->checkInvoiceStatus($externalId);
                    if ($realStatus === 'EXPIRED') {
                        $transaction->payment_status = 'EXPIRED';
                        $transaction->save();
                        Log::info("Transaction marked as expired and stock restored: {$transaction->id}");
                    }
                }
            }

            $this->clearSession(); // Hanya clear transaction data, keep payment_token
            Log::info('Session cleared on payment failure page');
        }

        return view('payment.failure');
    }

    public function retryPayment(Request $request)
    {
        // Isi ulang session seperti proses order awal
        $retryCart = session('retry_cart');
        $retryName = session('retry_name');
        $retryPhone = session('retry_phone');
        $retryTable = session('retry_table');

        // Set ulang data ke session agar processPayment bisa berjalan normal
        session([
            'cart_items' => $retryCart,
            'name' => $retryName,
            'phone' => $retryPhone,
            'table_number' => $retryTable,
        ]);

        // Tambahkan token untuk keamanan
        $token = Str::random(32);
        session(['payment_token' => $token]);

        // Redirect ke handlePayment (action: pay)
        return redirect()->route('payment.handle', [
            'action' => 'pay',
            'token' => $token
        ]);
    }

    public function showRetry()
    {
        if (!session('retry_cart')) {
            return redirect()->route('home')->with('message', 'Tidak ada transaksi untuk diulang.');
        }

        return view('payment.retry'); // Buat Blade view-nya
    }

    public function handleStatusRedirect($id)
    {
        Log::info("Redirected from Xendit after failure, ID: {$id}");

        $transaction = Transaction::where('external_id', $id)->first();

        if (!$transaction) {
            Log::warning("Transaction not found after redirect.");
            return redirect()->route('payment.failure')->with('debug', 'Transaksi tidak ditemukan.');
        }

        $status = $this->checkInvoiceStatus($id);

        if ($status === 'EXPIRED') {
            // Ambil ulang retry data dari transaksi
            session([
                'retry_cart' => $transaction->items->map(function ($item) {
                    return [
                        'id' => $item->foods_id,
                        'name' => $item->food->name ?? 'N/A',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ];
                })->toArray(),
                'retry_name' => $transaction->name,
                'retry_phone' => $transaction->phone,
                'retry_table' => $transaction->barcodes->table_number ?? null,
            ]);

            $this->clearSession();

            return redirect()->route('payment.retry');
        }

        if ($status === 'PAID' || $status === 'SETTLED') {
            $this->clearSession(true);
            return redirect()->route('payment.success');
        }

        // Jika status masih PENDING atau gagal lainnya
        return redirect()->route('payment.failure')->with('debug', "Status saat ini: {$status}");
    }
}
