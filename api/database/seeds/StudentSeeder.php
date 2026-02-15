<?php

use Illuminate\Database\Seeder;
use App\Student;
use App\User;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $studentUser = User::where('email', 'student@jubilut.com.br')->first();
        if (!$studentUser) {
            $studentUser = User::create([
                'name' => 'Aluno Placeholder',
                'email' => 'student@jubilut.com.br',
                'password' => bcrypt('password'),
                'role' => 'student'
            ]);
        }

        if (!Student::where('email', 'student@jubilut.com.br')->exists()) {
            Student::create([
                'name' => 'Aluno Placeholder',
                'email' => 'student@jubilut.com.br',
                'birth_date' => '2000-05-15',
                'user_id' => $studentUser->id,
            ]);
        }

        factory(Student::class, 50)->create();
    }
}
