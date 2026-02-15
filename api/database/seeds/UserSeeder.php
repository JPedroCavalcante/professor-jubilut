<?php

use Illuminate\Database\Seeder;
use App\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@jubilut.com.br',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Aluno Placeholder',
            'email'    => 'student@jubilut.com.br',
            'password' => bcrypt('password'),
            'role'     => 'student',
        ]);
    }
}
