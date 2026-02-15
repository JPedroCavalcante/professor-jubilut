<?php

use Illuminate\Database\Seeder;
use App\Subject;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        Subject::create([
            'title' => 'Citologia',
            'description' => 'Estudo das celulas.',
            'course_id' => 1,
            'professor_id' => 1,
        ]);

        Subject::create([
            'title' => 'Biomas Brasileiros',
            'description' => 'Estudo dos biomas do Brasil.',
            'course_id' => 2,
            'professor_id' => 2,
        ]);

        Subject::create([
            'title' => 'Hereditariedade',
            'description' => 'Leis de Mendel e heranca genetica.',
            'course_id' => 3,
            'professor_id' => 3,
        ]);

        Subject::create([
            'title' => 'Sistema Nervoso',
            'description' => 'Anatomia do sistema nervoso central e periferico.',
            'course_id' => 4,
            'professor_id' => 4,
        ]);

        Subject::create([
            'title' => 'Vertebrados',
            'description' => 'Classificacao e estudo dos vertebrados.',
            'course_id' => 5,
            'professor_id' => 5,
        ]);
    }
}
