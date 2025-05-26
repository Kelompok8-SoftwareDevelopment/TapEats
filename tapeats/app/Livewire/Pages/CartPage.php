<?php

namespace App\Livewire\Pages;

use App\Livewire\Traits\CartManagement;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Session;

class CartPage extends Component
{
    use CartManagement;

    public $foods;
    public $title = 'All Foods';

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
        $this->selectedItems = collect($this->cartItems)
            ->filter(fn($item) => $item['selected'] ?? false)
            ->values()
            ->toArray();

        $this->selectAll = count($this->selectedItems) === count($this->cartItems);

        session(['has_unpaid_transaction' => false]);
    }

    public function deleteSelected()
    {
        $selectedIds = collect($this->selectedItems)
            ->pluck('id')
            ->toArray();

        $this->cartItems = collect($this->cartItems)
            ->reject(fn($item) => in_array($item['id'], $selectedIds))
            ->values()
            ->toArray();

        session([
            'cart_items' => $this->cartItems
        ]);

        $this->selectedItems = [];
    }

    public function checkout()
    {
        if (empty($this->selectedItems)) {
            $this->addError('selectedItems', 'Please select at least one item to proceed.');
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
}
