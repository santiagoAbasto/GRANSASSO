<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Slider;

class HomeSeeder extends Seeder
{
    public function run(): void
    {

        /*
        |--------------------------------------------------------------------------
        | HOME PAGE
        |--------------------------------------------------------------------------
        */

        $home = Page::firstOrCreate([
            'slug' => 'home'
        ],[
            'title' => [
                'es' => 'Inicio',
                'en' => 'Home',
                'pt' => 'Início'
            ],
            'meta_title' => [
                'es' => 'Gran Sasso',
                'en' => 'Gran Sasso',
                'pt' => 'Gran Sasso'
            ],
            'meta_description' => [
                'es' => 'Gran Sasso engranajes industriales',
                'en' => 'Gran Sasso industrial gears',
                'pt' => 'Gran Sasso engrenagens industriais'
            ]
        ]);

        /*
        |--------------------------------------------------------------------------
        | SLIDER HERO
        |--------------------------------------------------------------------------
        */

        Slider::create([

            'title' => [
                'es' => 'Potencia y precisión en cada engranaje',
                'en' => 'Power and precision in every gear',
                'pt' => 'Potência e precisão em cada engrenagem'
            ],

            'subtitle' => [
                'es' => 'Soluciones confiables para la transmisión industrial.',
                'en' => 'Reliable solutions for industrial transmission.',
                'pt' => 'Soluções confiáveis para transmissão industrial.'
            ],

            'image' => 'images/sliders/hero.jpg',

            'button_text' => [
                'es' => 'Ver productos',
                'en' => 'View products',
                'pt' => 'Ver produtos'
            ],

            'button_link' => '/productos',

            'order' => 1
        ]);

        /*
        |--------------------------------------------------------------------------
        | NOSOTROS
        |--------------------------------------------------------------------------
        */

        PageSection::create([

            'page_id' => $home->id,

            'type' => 'about',

            'title' => [
                'es' => 'Quienes somos',
                'en' => 'Who we are',
                'pt' => 'Quem somos'
            ],

            'content' => [

                'es' => 'GRAN SASSO cuenta con 60 años de trayectoria fabricando engranajes para cajas de velocidades y partes de transmisión.

Inicio sus actividades en 1957, prestando servicio de mecanizado para pocos clientes. Con el tiempo y la constante inversión y capacitación de sus colaboradores, ha logrado ser reconocido por los principales mercados.

Con marca y productos propios totalmente intercambiables y compatibles con piezas originales. Hacemos de la mejora continua nuestra filosofía, desarrollamos nuevos productos, abrimos nuevos mercados y seguimos.',

                'en' => 'GRAN SASSO has 60 years of experience manufacturing gears for gearboxes and transmission parts.

It began its activities in 1957, providing machining services for a few clients. Over time, with constant investment and training of its collaborators, it has become recognized by the main markets.

With its own brand and products fully interchangeable and compatible with original parts. Continuous improvement is our philosophy, we develop new products, open new markets and keep moving forward.',

                'pt' => 'A GRAN SASSO possui 60 anos de trajetória fabricando engrenagens para caixas de velocidades e partes de transmissão.

Iniciou suas atividades em 1957 prestando serviços de usinagem para poucos clientes. Com o tempo e o investimento constante e capacitação de seus colaboradores, conseguiu reconhecimento nos principais mercados.

Com marca e produtos próprios totalmente intercambiáveis e compatíveis com peças originais. Fazemos da melhoria contínua nossa filosofia, desenvolvemos novos produtos, abrimos novos mercados e seguimos avançando.'
            ],

            'image' => 'images/about/about.jpg',

            'button_text' => [
                'es' => 'Mas información',
                'en' => 'More information',
                'pt' => 'Mais informações'
            ],

            'button_link' => '/empresa',

            'order' => 1
        ]);

        /*
        |--------------------------------------------------------------------------
        | CALIDAD
        |--------------------------------------------------------------------------
        */

        PageSection::create([

            'page_id' => $home->id,

            'type' => 'quality',

            'title' => [
                'es' => 'Calidad',
                'en' => 'Quality',
                'pt' => 'Qualidade'
            ],

            'content' => [

                'es' => 'Los productos son tratados térmicamente y sometidos a tratamientos físicos y químicos con el fin de obtener los más altos estándares de calidad. Todos los productos Gran Sasso son intercambiables con partes originales, dado que son fabricados con estrictas normas de seguridad.',

                'en' => 'Products are heat treated and subjected to physical and chemical treatments in order to obtain the highest quality standards. All Gran Sasso products are interchangeable with original parts, as they are manufactured under strict safety standards.',

                'pt' => 'Os produtos são tratados termicamente e submetidos a tratamentos físicos e químicos para obter os mais altos padrões de qualidade. Todos os produtos Gran Sasso são intercambiáveis com peças originais, pois são fabricados sob rigorosos padrões de segurança.'
            ],

            'image' => 'images/quality/quality.jpg',

            'button_text' => [
                'es' => 'Mas información',
                'en' => 'More information',
                'pt' => 'Mais informações'
            ],

            'button_link' => '/calidad',

            'order' => 2
        ]);

    }
}