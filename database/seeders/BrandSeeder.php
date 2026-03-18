<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Mercedes-Benz', 'logo' => 'brands/mercedes-benz.png'],
            ['name' => 'Eaton',         'logo' => 'brands/eaton.png'],
            ['name' => 'ZF',            'logo' => 'brands/zf.png'],
            ['name' => 'Fuller',        'logo' => 'brands/fuller.png'],
            ['name' => 'Volkswagen',    'logo' => 'brands/volkswagen.png'],
            ['name' => 'Scania',        'logo' => 'brands/scania.png'],
            ['name' => 'Ford',          'logo' => 'brands/ford.png'],
            ['name' => 'Iveco',         'logo' => 'brands/iveco.png'],
        ];

        foreach ($brands as $data) {
            Brand::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name'      => ['es' => $data['name'], 'en' => $data['name'], 'pt' => $data['name']],
                    'logo'      => $data['logo'],
                    'is_active' => true,
                ]
            );
        }
    }
}
