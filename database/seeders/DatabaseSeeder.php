<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Hash;
use Illuminate\Database\Seeder;
 
class DatabaseSeeder extends Seeder
{


    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'phone' => '123456789',
            'address' => 'address',
            'city' => 'city',
            'password' => Hash::make('123123123'),
            'is_admin' => true
        ]);
    }
}
