<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => '123456',
            ],
        );
        
        $profile = Profile::where('name', 'Administrador')->firstOrFail();

        $admin->profiles()->syncWithoutDetaching([$profile->id]);
    }
}
