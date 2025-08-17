<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    
        // Create admin account
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'), // change as needed
            'role' => 'admin',
        ]);

        // Create a sample customer account
        User::factory()->create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('customer123'), // change as needed
            'role' => 'customer',
        ]);
    }
}
