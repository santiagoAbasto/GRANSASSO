<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@gransasso.com'],
            [
                'name'     => 'Admin',
                'password' => bcrypt('password'),
            ]
        );

        $this->call([
            SiteSettingSeeder::class,
            HomeSeeder::class,
            BrandSeeder::class,
            GearboxSeeder::class,
        ]);
    }
}
