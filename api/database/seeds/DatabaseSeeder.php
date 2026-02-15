<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(CourseSeeder::class);
        $this->call(ProfessorSeeder::class);
        $this->call(SubjectSeeder::class);
        $this->call(StudentSeeder::class);
    }
}
