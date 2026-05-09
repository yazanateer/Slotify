<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@slotify.com'],
            [
                'name' => 'Slotify Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'business_id' => null
            ]
        );
    }
}
