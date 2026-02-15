<?php

use Illuminate\Database\Seeder;
use App\Course;

class CourseSeeder extends Seeder
{
    public function run()
    {
        Course::firstOrCreate(
            ['title' => 'Biologia Geral'],
            [
                'description' => 'Fundamentos de biologia celular e molecular.',
                'start_date' => '2024-02-01',
                'end_date' => '2024-06-30',
            ]
        );

        Course::firstOrCreate(
            ['title' => 'Ecologia e Meio Ambiente'],
            [
                'description' => 'Estudo dos ecossistemas e sustentabilidade.',
                'start_date' => '2024-03-01',
                'end_date' => '2024-07-31',
            ]
        );

        Course::firstOrCreate(
            ['title' => 'Genetica Aplicada'],
            [
                'description' => 'Principios de genetica e engenharia genetica.',
                'start_date' => '2024-04-01',
                'end_date' => '2024-08-31',
            ]
        );

        Course::firstOrCreate(
            ['title' => 'Anatomia Humana'],
            [
                'description' => 'Estudo detalhado da anatomia do corpo humano.',
                'start_date' => '2024-05-01',
                'end_date' => '2024-09-30',
            ]
        );

        Course::firstOrCreate(
            ['title' => 'Zoologia'],
            [
                'description' => 'Classificacao e estudo dos animais.',
                'start_date' => '2024-06-01',
                'end_date' => '2024-10-31',
            ]
        );
    }
}
