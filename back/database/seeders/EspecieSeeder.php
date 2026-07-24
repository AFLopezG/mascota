<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EspecieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('especies')->insert([
            ['id'=>1,'codigo'=>'C','nombre'=>'CANINO'],
            ['id'=>2,'codigo'=>'F','nombre'=>'FELINO'],
            ['id'=>3,'codigo'=>'O','nombre'=>'OTRO'],

        ]);

        DB::table('razas')->insert([
            ['nombre'=>'Labrador Retriever','descrpcion'=>'Extremadamente popular, amigable, ideal para familias','especie_id'=>1],
            ['nombre'=>'Golden Retriever','descrpcion'=>'Muy cariñoso, paciente con niños yácil de entrenar','especie_id'=>1],
            ['nombre'=>'Pastor Alemán','descrpcion'=>'Leal, protector e inteligente','especie_id'=>1],
            ['nombre'=>'Bulldog Francés','descrpcion'=>'El perro de compañía urbano/departamento más popular','especie_id'=>1],
            ['nombre'=>'Caniche / Poodle','descrpcion'=>'Muy inteligente y popular por ser hipoalergénico','especie_id'=>1],
            ['nombre'=>'Beagle','descrpcion'=>'Compacto, alegre y de gran olfato','especie_id'=>1],
            ['nombre'=>'Chihuahua','descrpcion'=>'Muy común por su tamaño pequeño y adaptabilidad','especie_id'=>1],
            ['nombre'=>'Pug','descrpcion'=>'Carlino) (Sociable,どcil y de temperamento tranquilo','especie_id'=>1],
            ['nombre'=>'Yorkshire Terrier','descrpcion'=>'Pequeño, activo y muy afectuoso','especie_id'=>1],
            ['nombre'=>'Boxer','descrpcion'=>'Enérgico, juguetón y paciente con los niños','especie_id'=>1],
            ['nombre'=>'Siberian Husky','descrpcion'=>'Muy popular por su apariencia, activo y sociable','especie_id'=>1],
            ['nombre'=>'Cocker Spaniel','descrpcion'=>'Alegre, mediano y muy cariñoso','especie_id'=>1],
            ['nombre'=>'Shih Tzu','descrpcion'=>'Tranquilo, de compañía y excelente para espacios pequeños','especie_id'=>1],
            ['nombre'=>'Dachshund','descrpcion'=>'Curioso, compacto y de personalidad marcada','especie_id'=>1],
            ['nombre'=>'Border Collie','descrpcion'=>'El más destacado por inteligencia y agilidad','especie_id'=>1],
            ['nombre'=>'Bulldog Inglés','descrpcion'=>'Calmo, paciente y de ritmo pausado','especie_id'=>1],
            ['nombre'=>'Dóberman','descrpcion'=>'Elegante, protector y muy leal','especie_id'=>1],
            ['nombre'=>'Rottweiler','descrpcion'=>'Poderoso, seguro de sí mismo y buen guardián','especie_id'=>1],
            ['nombre'=>'Gran Danés','descrpcion'=>'El gigante noble más conocido','especie_id'=>1],
            ['nombre'=>'San Bernardo','descrpcion'=>'Famoso por su tamaño y carácter apacible','especie_id'=>1],

            ['nombre'=>' Gato Común Europeo / Mestizo','descrpcion'=>'El más presente en los hogares de todo el mundo, muy resistente, adaptable y variado en temperamento.','especie_id'=>2],
            ['nombre'=>'Siamés','descrpcion'=>'Extremadamente conocido, muy comunicativo (maullador), cariñoso y de ojos azules intensos.','especie_id'=>2],
            ['nombre'=>'Persa','descrpcion'=>'Famoso por su rostro chato, abundante pelaje y temperamento sumamente tranquilo y hogareño.','especie_id'=>2],
            ['nombre'=>'Maine Coon','descrpcion'=>'Reconocido como el "gigante afable"; muy grande, sociable, juguetón y excelente con niños.','especie_id'=>2],
            ['nombre'=>'Ragdoll','descrpcion'=>'Su nombre significa "muñeca de trapo" porque se relaja por completo al cargarlo; es extremadamenteどcil y pacífico.','especie_id'=>2],
            ['nombre'=>'British Shorthair (Británico de Pelo Corto)','descrpcion'=>'De aspecto de "oso de peluche", es calmo, independiente y muy paciente.','especie_id'=>2],
            ['nombre'=>'Sphynx (Esfinge)','descrpcion'=>'Famoso por no tener pelo; es muy cariñoso, busca siempre el calor humano y es de gran energía.','especie_id'=>2],
            ['nombre'=>'Bengalí (Gato Bengala)','descrpcion'=>'De aspecto salvaje (similar a un leopardo), muy activo, inteligente y curioso (suele gustarle el agua).','especie_id'=>2],
            ['nombre'=>'Bosque de Noruega','descrpcion'=>'De gran tamaño y pelaje denso apto para el frío; es tranquilo, robusto y gran trepador.','especie_id'=>2],
            ['nombre'=>'Scottish Fold','descrpcion'=>'Reconocible por sus orejas plegadas hacia adelante; de carácter dulce, silencioso y amigable.','especie_id'=>2],
            ['nombre'=>'Azul Ruso','descrpcion'=>'De pelaje gris/plateado y ojos verdes; es reservado con extraños pero increíblemente leal a su familia.','especie_id'=>2],
            ['nombre'=>'Angora Turco','descrpcion'=>'Elegante, de pelo largo y seda, muy ágil, curioso y afectuoso.','especie_id'=>2],
            ['nombre'=>'Abisinio','descrpcion'=>'Una de las razas más antiguas; muy atlético, activo, inteligente y siempre en movimiento.','especie_id'=>2],
            ['nombre'=>'American Shorthair','descrpcion'=>'Muy popular por su equilibrio entre serenidad, buena salud y habilidades de caza.','especie_id'=>2],
            ['nombre'=>'Siberiano','descrpcion'=>'Gato grande y de pelaje frondoso; famoso por producir menos proteín','especie_id'=>2],
            ['nombre'=>'Exótico de Pelo Corto','descrpcion'=>'Es prácticamente un Persa pero con pelo corto;ácil de cuidar y de carácter sumamente dulce.','especie_id'=>2],
            ['nombre'=>'Burmés','descrpcion'=>'Muy enfocado en las personas, juguetón, inteligente y leal.','especie_id'=>2],
            ['nombre'=>'Devon Rex','descrpcion'=>'De pelo corto y rizado, orejas grandes y personalidad juguetona y traviesa.','especie_id'=>2],
            ['nombre'=>'Munchkin','descrpcion'=>'Conocido por sus patas muy cortas; es juguetón, rápido y bastante sociable.','especie_id'=>2],
            ['nombre'=>'Birmano (Sagrado de Birmania)','descrpcion'=>'Ojos azules y "guantes" blancos en sus patas; es muy cariñoso, apacible y equilibrado.','especie_id'=>2],
        ]);
    }
}
