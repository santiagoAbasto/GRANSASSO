<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Gearbox;
use App\Models\Application;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GearboxSeeder extends Seeder
{
    public function run(): void
    {
        $mercedes = Brand::where('slug', 'mercedes-benz')->first();
        if (!$mercedes) return;

        $gearboxes = [
            [
                'name'                => 'G-85/60-6',
                'compatible_vehicles' => 'L1218/1518/1215C/1315 L1620ELEC./1622/O500 ATEGO1718 OF1721/OF1417/OH1418',
                'applications'        => ['L1218', 'L1518', 'L1215C', 'L1315', 'L1620ELEC', 'L1622', 'O500', 'ATEGO1718', 'OF1721', 'OF1417', 'OH1418'],
            ],
            [
                'name'                => 'G-3/32',
                'compatible_vehicles' => 'L1114-L1113-L1214-L1516-L1316',
                'applications'        => ['L1114', 'L1113', 'L1214', 'L1516', 'L1316'],
            ],
            [
                'name'                => 'G-3/50/60',
                'compatible_vehicles' => 'L1620/L2213/O400/LS1632',
                'applications'        => ['L1620', 'L2213', 'O400', 'LS1632'],
            ],
            [
                'name'                => 'G-2 24/20',
                'compatible_vehicles' => 'L608/L608D/L608E',
                'applications'        => ['L608', 'L608D', 'L608E'],
            ],
            [
                'name'                => 'G3-55',
                'compatible_vehicles' => 'LK1418/LK1618/LK1620/LK2318',
                'applications'        => ['LK1418', 'LK1618', 'LK1620', 'LK2318'],
            ],
            [
                'name'                => 'G-3/60 Ant.',
                'compatible_vehicles' => 'LP1313/LP1513/LP1519',
                'applications'        => ['LP1313', 'LP1513', 'LP1519'],
            ],
        ];

        foreach ($gearboxes as $data) {
            $gearbox = Gearbox::firstOrCreate(
                ['slug' => $mercedes->id . '-' . Str::slug($data['name'])],
                [
                    'brand_id'            => $mercedes->id,
                    'name'                => $data['name'],
                    'slug'                => Str::slug($data['name']),
                    'compatible_vehicles' => $data['compatible_vehicles'],
                    'is_active'           => true,
                ]
            );

            // Seed applications for this gearbox
            foreach ($data['applications'] as $appName) {
                Application::firstOrCreate([
                    'gearbox_id' => $gearbox->id,
                    'name'       => $appName,
                ]);
            }
        }
    }
}
