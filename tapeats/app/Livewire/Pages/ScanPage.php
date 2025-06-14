<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Component;

class ScanPage extends Component
{
    public function mount()
    {
        session()->forget('table_number');
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('product.scan');
    }
}


