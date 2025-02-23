<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'id' => Str::uuid(),
            'fullname' => 'Candra',
            'no_handphone' => '081234567890',
            'password' => bcrypt('root'),
            'role' => 'admin',
        ]);
    }
}
