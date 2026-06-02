<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();

        if (!$adminRole) {
            throw new RuntimeException('Admin role not found. Run RoleSeeder before AdminSeeder.');
        }

        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);
    }
}
