<?php

namespace App\Livewire\Components;

use Livewire\Component;

class FoodCard extends Component
{
    public $categories;
    public $matchedCategory;
    public $data;
    public bool $isGrid = true;

    public function mount()
    {
        $this->matchedCategory = collect($this->categories)->firstWhere('id', $this->data->categories_id);
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
            $cartItems[] = array_merge(
                (array)$this->data,
                [
                    'quantity' => 1,
                    'selected' => true,
                ]
            );
        }

        session(['cart_items' => $cartItems]);
        session(['has_unpaid_transaction' => false]);

        $this->dispatch('toast',
            data: [
                'message1' => 'Item added to cart',
                'message2' => $this->data->name,
                'type' => 'success',
            ]
        );
    }
}
