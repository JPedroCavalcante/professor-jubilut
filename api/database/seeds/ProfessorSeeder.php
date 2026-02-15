<?php

use Illuminate\Database\Seeder;
use App\Professor;

class ProfessorSeeder extends Seeder
{
    public function run()
    {
        Professor::create(['name' => 'Paulo Jubilut', 'email' => 'jubilut@prof.com']);
        Professor::create(['name' => 'Maria Silva', 'email' => 'maria.silva@prof.com']);
        Professor::create(['name' => 'Carlos Santos', 'email' => 'carlos.santos@prof.com']);
        Professor::create(['name' => 'Ana Oliveira', 'email' => 'ana.oliveira@prof.com']);
        Professor::create(['name' => 'Roberto Lima', 'email' => 'roberto.lima@prof.com']);
    }
}
