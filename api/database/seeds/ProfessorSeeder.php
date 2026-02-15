<?php

use Illuminate\Database\Seeder;
use App\Professor;

class ProfessorSeeder extends Seeder
{
    public function run()
    {
        Professor::firstOrCreate(['email' => 'jubilut@prof.com'], ['name' => 'Paulo Jubilut']);
        Professor::firstOrCreate(['email' => 'maria.silva@prof.com'], ['name' => 'Maria Silva']);
        Professor::firstOrCreate(['email' => 'carlos.santos@prof.com'], ['name' => 'Carlos Santos']);
        Professor::firstOrCreate(['email' => 'ana.oliveira@prof.com'], ['name' => 'Ana Oliveira']);
        Professor::firstOrCreate(['email' => 'roberto.lima@prof.com'], ['name' => 'Roberto Lima']);
    }
}
