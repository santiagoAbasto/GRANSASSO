<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {

        SiteSetting::create([

            'company_name' => 'Gran Sasso',

            'address' => [
                'es' => 'Calle 33 Nro. 2235 (1650) Villa Maipú, San Martín. Buenos Aires, Argentina',
                'en' => '33 Street No. 2235 (1650) Villa Maipú, San Martín. Buenos Aires, Argentina',
                'pt' => 'Rua 33 Nº 2235 (1650) Villa Maipú, San Martín. Buenos Aires, Argentina'
            ],

            'phone_1' => '+54 11 4755-9078',

            'phone_2' => '4752-1450',

            'phone_3' => '4753-1297',

            'email_sales' => 'ventas@gransasso.com.ar',

            'email_general' => 'comercial@gransasso.com.ar',

            'facebook' => 'https://facebook.com/gransasso'

        ]);
    }
}