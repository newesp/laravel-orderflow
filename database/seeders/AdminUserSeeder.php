<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminUser::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo Administrator',
                'password' => Hash::make('demo1234'),
                'is_demo' => true,
            ]
        );
    }
}
