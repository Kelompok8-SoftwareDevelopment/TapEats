<?php

namespace App\Livewire\Pages;

use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PaymentSuccessPage extends Component
{
    public $transaction;

    public function mount()
{
    $this->transaction = Transaction::with('items.food')
        ->where('external_id', session('external_id'))
        ->first();

    session()->forget(['external_id', 'has_unpaid_transaction', 'cart_items', 'payment_token']);
    session()->save();
}

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('payment.success', [
            'transaction' => $this->transaction
        ]);
    }
}
