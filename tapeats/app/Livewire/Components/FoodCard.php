<?php

namespace App\Livewire\Components;

use Livewire\Component;

class FoodCard extends Component
{
    public $categories;
    public $matchedCategory;
    public $data;
    public bool $isGrid = true;

   public function mount($data, $categories)
{
    $this->data = $data;
    $this->categories = $categories;

    $this->matchedCategory = collect($this->categories)
        ->firstWhere('id', $this->data->categories_id); // akses pakai ->
}

    


    public function showDetails()
    {
        return $this->redirect('/food/' . $this->data->id, navigate: true);
    }

    public function render()
    {
        return view('livewire.food-card');
    }
}
