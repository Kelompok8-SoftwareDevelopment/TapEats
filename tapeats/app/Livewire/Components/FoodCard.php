<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\TransactionItems;

class FoodCard extends Component
{
    public $categories;
    public $matchedCategory;
    public $data;
    public bool $isGrid = true;

    public function mount()
    {
        $this->matchedCategory = collect($this->categories)->firstWhere('id', $this->data->categories_id);

        // Hitung total sold dari transaksi yang sudah PAID
        $this->data->total_sold = $this->calculateTotalSold();
    }

    /**
     * Menghitung total item yang terjual dari transaksi yang sudah PAID
     */
    private function calculateTotalSold()
    {
        return TransactionItems::where('foods_id', $this->data->id)
            ->whereHas('transaction', function ($query) {
                $query->where('payment_status', 'PAID');
            })
            ->sum('quantity') ?? 0;
    }

    public function showDetails()
    {
        return $this->redirect('/food/' . $this->data->id, navigate: true);
    }

    public function render()
    {
        return view('livewire.food-card');
    }

    public function addToCart()
    {
        $cartItems = session('cart_items', []);

        $existingItemIndex = collect($cartItems)->search(fn($item) => $item['id'] === $this->data->id);

        if ($existingItemIndex !== false) {
            $cartItems[$existingItemIndex]['quantity'] += 1;
        } else {
            $cartItems[] = [
                'id' => $this->data->id,
                'name' => $this->data->name,
                'price' => $this->data->price,
                'price_afterdiscount' => $this->data->price_afterdiscount,
                'image' => $this->data->image,
                'is_promo' => $this->data->is_promo,
                'quantity' => 1,
                'selected' => true,
            ];
        }

        session(['cart_items' => $cartItems]);
        session(['has_unpaid_transaction' => false]);

        $this->dispatch(
            'toast',
            data: [
                'message1' => 'Item added to cart',
                'message2' => $this->data->name,
                'type' => 'success',
            ]
        );
    }
}
