<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Owner Lambo',
            'email' => 'lambo15@owner.com',
            'password' => Hash::make('lambo1234'),
            'role' => 'owner',
        ]);
    }
}
