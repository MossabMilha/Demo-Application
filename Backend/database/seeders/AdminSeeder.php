<?php

namespace Database\Seeders;

use App\Models\admin;
use App\Models\user;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'first_name' => 'Mossab',
            'last_name' => 'Milha',
            'email' => 'mossabmilha.m@gmail.com',
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);

        Admin::create([
            'user_id' => $user->id,
            'phone' => '+212771729927',
        ]);
    }
}
