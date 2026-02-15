<?php

use Illuminate\Database\Seeder;
use App\Student;
use App\User;

class StudentSeeder extends Seeder
{
    public function run()
    {
        // Link the existing student user
        $studentUser = User::where('email', 'student@jubilut.com.br')->first();
        if ($studentUser) {
            Student::create([
                'name' => 'Aluno Placeholder',
                'email' => 'student@jubilut.com.br',
                'birth_date' => '2000-05-15',
                'user_id' => $studentUser->id,
            ]);
        }

        // Create additional student users and student records
        $students = [
            ['name' => 'Joao Pedro', 'email' => 'joao.pedro@aluno.com', 'birth_date' => '1998-03-20'],
            ['name' => 'Maria Clara', 'email' => 'maria.clara@aluno.com', 'birth_date' => '2001-07-10'],
            ['name' => 'Lucas Mendes', 'email' => 'lucas.mendes@aluno.com', 'birth_date' => '1999-11-25'],
            ['name' => 'Ana Beatriz', 'email' => 'ana.beatriz@aluno.com', 'birth_date' => '2002-01-08'],
        ];

        foreach ($students as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt('password'),
                'role' => 'student',
            ]);

            Student::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'birth_date' => $data['birth_date'],
                'user_id' => $user->id,
            ]);
        }
    }
}
