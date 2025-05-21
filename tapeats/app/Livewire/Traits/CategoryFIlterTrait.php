<?php

namespace App\Livewire\Traits;

trait CategoryFilterTrait
{
    public function getFilteredItems()
    {
        if (count($this->selectedCategories) > 0) {
            return $this->items->filter(function ($item) {
                return in_array($item->categories_id, $this->selectedCategories);
            });
        }

        return $this->items;
    }
}
