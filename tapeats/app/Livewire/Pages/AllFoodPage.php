<?php

namespace App\Livewire\Pages;

use App\Livewire\Traits\CategoryFilterTrait;
use App\Models\Category;
use App\Models\Foods;
use Livewire\Component;
use Livewire\Attributes\Layout;

class AllFoodPage extends Component
{
    public $categories;
    public $items;
    public $selectedCategory = null;

    public function mount(Foods $foods)
    {
        $this->categories = Category::all();
        $this->items = $foods->getAllFoods(); 
    }

    public function selectCategory($categoryId = null)
    {
        $this->selectedCategory = $categoryId;
    }

    #[Layout('components.layouts.page')]
    public function render()
    {
        $filteredProducts = $this->getFilteredItems();

        return view('product.all-food', [
            'filteredProducts' => $filteredProducts,
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
