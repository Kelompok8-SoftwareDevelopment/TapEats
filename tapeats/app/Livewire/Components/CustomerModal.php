<?php

namespace App\Livewire\Components;

use Livewire\Attributes\Session;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CustomerModal extends Component
{
    #[Validate('required', message: 'Name cannot be empty')]
    #[Session('name')]
    public $name = '';

    #[Validate('required|min:10', message: 'Phone number cannot be empty')]
    #[Session('phone')]
    public $phone = '';

    public function mount()
    {
        $this->name = session('name', '');
        $this->phone = session('phone', '');
    }

    public function saveUserInfo()
    {
        if (str_starts_with($this->phone, '08')) {
            $this->phone = '62' . substr($this->phone, 1);
        } elseif (str_starts_with($this->phone, '8')) {
            $this->phone = '62' . $this->phone;
        }

        $this->validate();
        session(['name' => $this->name, 'phone' => $this->phone]);

        $this->dispatch("saved-user-info");
    }

    public function render()
    {
        return view('livewire.customer-modal');
    }
}
