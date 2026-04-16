<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Alessandro Souza',
            'username' => 'apsouza',
            'email' => 'alessandro.souza@norte.dev.br',
            'password' => bcrypt('MUpw@*56'),
        ]);
    }
}
