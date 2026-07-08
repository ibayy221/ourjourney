<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // TODO: ganti email & password sebelum production!
        User::create([
            'name'     => 'ubay',
            'email'    => 'ubay',
            'password' => Hash::make('dini321'),
        ]);

        User::create([
            'name'     => 'dini',
            'email'    => 'dini',
            'password' => Hash::make('ubay123'),
        ]);
    }
}
