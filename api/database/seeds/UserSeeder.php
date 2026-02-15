<?php

use Illuminate\Database\Seeder;
use App\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::firstOrCreate(
            ['email' => 'admin@jubilut.com.br'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'student@jubilut.com.br'],
            [
                'name' => 'Aluno Placeholder',
                'password' => bcrypt('password'),
                'role' => 'student',
            ]
        );
    }
}
