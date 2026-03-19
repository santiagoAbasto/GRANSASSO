<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PageSection;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {

        /*
        |--------------------------------------------------------------------------
        | EMPRESA PAGE
        |--------------------------------------------------------------------------
        */

        $empresa = Page::firstOrCreate(
            ['slug' => 'empresa'],
            [
                'title' => [
                    'es' => 'Empresa',
                    'en' => 'Company',
                    'pt' => 'Empresa'
                ],
                'meta_title' => [
                    'es' => 'Empresa – Gran Sasso',
                    'en' => 'Company – Gran Sasso',
                    'pt' => 'Empresa – Gran Sasso'
                ],
                'meta_description' => [
                    'es' => 'Conocé la historia de Gran Sasso, nuestros valores y compromiso con la calidad.',
                    'en' => 'Learn about Gran Sasso\'s history, our values and commitment to quality.',
                    'pt' => 'Conheça a história da Gran Sasso, nossos valores e compromisso com a qualidade.'
                ]
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | BANNER (imágenes gestionadas desde el admin via page_section_images)
        |--------------------------------------------------------------------------
        */

        PageSection::firstOrCreate(
            ['page_id' => $empresa->id, 'type' => 'banner'],
            [
                'order'     => 1,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | QUIÉNES SOMOS
        |--------------------------------------------------------------------------
        */

        PageSection::firstOrCreate(
            ['page_id' => $empresa->id, 'type' => 'about_main'],
            [
                'title' => [
                    'es' => 'Quiénes somos',
                    'en' => 'Who we are',
                    'pt' => 'Quem somos'
                ],
                'content' => [
                    'es' => "GRAN SASSO cuenta con más de 60 años de trayectoria en la fabricación de engranajes para cajas de velocidades y partes de transmisión, consolidándose como un referente de calidad y confiabilidad en el mercado.\n\nLa empresa inició sus actividades en 1957, brindando servicios de mecanizado para un reducido grupo de clientes. Con el paso del tiempo, y gracias a una política constante de inversión en tecnología, procesos y capacitación de su equipo de trabajo, logró un crecimiento sostenido que le permitió posicionarse entre los proveedores más reconocidos del sector.\n\nHoy, GRAN SASSO desarrolla y comercializa productos de marca propia, diseñados para ser totalmente intercambiables y compatibles con piezas originales, garantizando un alto nivel de rendimiento, durabilidad y precisión. Cada componente es el resultado de procesos controlados y de un fuerte compromiso con los estándares de calidad.\n\nLa mejora continua forma parte del ADN de la empresa. A través del desarrollo constante de nuevos productos, la incorporación de tecnologías y la apertura de nuevos mercados, GRAN SASSO sigue evolucionando para responder a las necesidades de la industria actual, manteniendo siempre el foco en la excelencia, la confianza y el servicio a sus clientes.",
                    'en' => 'GRAN SASSO has more than 60 years of experience in manufacturing gears for gearboxes and transmission parts, establishing itself as a benchmark for quality and reliability in the market.',
                    'pt' => 'A GRAN SASSO tem mais de 60 anos de trajetória na fabricação de engrenagens para caixas de velocidades e peças de transmissão, consolidando-se como referência de qualidade e confiabilidade no mercado.',
                ],
                'image'     => null,
                'order'     => 2,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | MISIÓN
        |--------------------------------------------------------------------------
        */

        PageSection::firstOrCreate(
            ['page_id' => $empresa->id, 'type' => 'about_mision'],
            [
                'title' => [
                    'es' => 'Misión',
                    'en' => 'Mission',
                    'pt' => 'Missão'
                ],
                'content' => [
                    'es' => 'Satisfacer las necesidades del mercado con piezas fabricadas según estándares de calidad similares al original, generando valor para nuestros clientes, accionistas, colaboradores.',
                    'en' => 'Satisfy market needs with parts manufactured to quality standards similar to the original, generating value for our customers, shareholders, and collaborators.',
                    'pt' => 'Satisfazer as necessidades do mercado com peças fabricadas segundo padrões de qualidade similares ao original, gerando valor para nossos clientes, acionistas e colaboradores.',
                ],
                'order'     => 3,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | VISIÓN
        |--------------------------------------------------------------------------
        */

        PageSection::firstOrCreate(
            ['page_id' => $empresa->id, 'type' => 'about_vision'],
            [
                'title' => [
                    'es' => 'Visión',
                    'en' => 'Vision',
                    'pt' => 'Visão'
                ],
                'content' => [
                    'es' => 'Ser referencia en calidad de productos, prestación de servicios y suministro de componentes para transmisión y cajas de velocidades. Superando expectativas de clientes y mercados, desarrollando personas y negocios.',
                    'en' => 'Be a reference in product quality, service delivery and supply of components for transmissions and gearboxes. Exceeding customer and market expectations, developing people and business.',
                    'pt' => 'Ser referência em qualidade de produtos, prestação de serviços e fornecimento de componentes para transmissão e caixas de velocidades. Superando as expectativas de clientes e mercados, desenvolvendo pessoas e negócios.',
                ],
                'order'     => 4,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | VALORES
        |--------------------------------------------------------------------------
        */

        PageSection::firstOrCreate(
            ['page_id' => $empresa->id, 'type' => 'about_calidad'],
            [
                'title' => [
                    'es' => 'Valores',
                    'en' => 'Values',
                    'pt' => 'Valores'
                ],
                'content' => [
                    'es' => 'Respeto, apreciación de nuestros clientes, proveedores y colaboradores, Conducta Ética, pasión por lo que hacemos, preservación del medio ambiente, investigación e innovación, responsabilidad social.',
                    'en' => 'Respect, appreciation of our customers, suppliers and collaborators, Ethical Conduct, passion for what we do, environmental preservation, research and innovation, social responsibility.',
                    'pt' => 'Respeito, valorização dos nossos clientes, fornecedores e colaboradores, Conduta Ética, paixão pelo que fazemos, preservação do meio ambiente, pesquisa e inovação, responsabilidade social.',
                ],
                'order'     => 5,
                'is_active' => true,
            ]
        );

    }
}
