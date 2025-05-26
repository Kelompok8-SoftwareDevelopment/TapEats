<?php

namespace App\Livewire\Pages;

use App\Livewire\Traits\CartManagement;
use App\Models\Foods;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Session;
use Livewire\Component;

class CartPage extends Component
{
    use CartManagement;

    public $foods;
    public $title = "All Foods";

    public bool $selectAll = true;

    public $selectedItems = [];

    #[Session(key: 'cart_items')]
    public $cartItems = [];
    #[Session(key: 'has_unpaid_transaction')]
    public $hasUnpaidTransaction;

    public function mount()
    {
        $this->updateSelectedItems();
    }

    public function updatedSelectAll()
    {
        foreach ($this->cartItems as &$item) {
            $item['selected'] = $this->selectAll;
        }

        $this->updateSelectedItems();
    }

    public function updateSelectedItems()
    {
        $this->selectedItems = collect($this->cartItems)->filter(fn($item) => $item['selected'])->toArray();

        $this->selectAll = count($this->selectedItems) === count($this->cartItems);

        session(['has_unpaid_transaction' => false]);
    }

    public function deleteSelected()
    {
        $this->cartItems = collect($this->cartItems)->filter(fn($item) => !$item['selected'])->toArray();

        $selectedIds = collect($this->selectedItems)->map(fn($item) => $item['id'])->toArray();

        $cartItemIds = collect(session('cart_items', []))
            ->map(fn($item) => $item['id'])
            ->toArray();

        $cartItemIds = array_diff($cartItemIds, $selectedIds);

        session(['cart_items' => $cartItemIds]);

        $this->selectedItems = [];
    }

    public function checkout()
    {
        if (empty($this->cartItems)) {
        $this->addError('cartItems', 'Your cart is empty.');
        return;
    }
        session(['cart_items' => $this->cartItems]);
        return $this->redirect('/checkout', navigate: true);
    }

    #[Layout('components.layouts.page')]
    public function render()
    {
        return view('payment.cart');
    }

    public function deleteSingleItem($index)
    {
        $this->cartItems = collect($this->cartItems)
            ->filter(fn($item, $i) => $i !== $index)
            ->values()
            ->toArray();
        $cartItemIds = collect($this->cartItems)->map(fn($item) => $item['id'])->toArray();
        session(['cart_items' => $cartItemIds]);
        $this->updateSelectedItems();
        $this->updateTotals();
    }
}
