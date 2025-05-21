<?php

namespace App\Livewire\Pages;

use App\Models\Category;
use App\Models\Foods;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PromoPage extends Component
{
    public $categories;
    public $items;
    public $selectedCategory = null;

    public function mount(Foods $foods)
    {
        $this->categories = Category::all();
        $this->items = $foods->getPromo(); // hanya data promo
    }

    public function selectCategory($categoryId = null)
    {
        $this->selectedCategory = $categoryId;
    }

    #[Layout('components.layouts.page')]
    public function render()
    {
        $filteredProducts = $this->getFilteredItems();

        return view('product.promo', [
            'filteredProducts' => $filteredProducts,
            'categories' => $this->categories,
            'selectedCategory' => $this->selectedCategory,
        ]);
    }

    public function getFilteredItems()
    {
        if ($this->selectedCategory) {
            return $this->items->filter(function ($item) {
                return $item->categories_id == $this->selectedCategory;
            });
        }

        return $this->items;
    }
}
