<?php

namespace App\Livewire\Components;

use Livewire\Component;

class CheckoutItemList extends Component
{
    public $items;
    public bool $withCheckbox = true;

    public function mount($items)
    {
        $this->items = $items;
    }

    public function render()
    {
        return view('livewire.checkout-item-list');
    }
}
