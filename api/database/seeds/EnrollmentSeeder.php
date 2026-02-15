<?php

use Illuminate\Database\Seeder;
use App\Student;
use App\Course;

class EnrollmentSeeder extends Seeder
{
    public function run()
    {
        $students = Student::all();
        $courses = Course::all();

        if ($courses->isEmpty())
            return;

        foreach ($students as $student) {
            $coursesToEnroll = $courses->random(rand(1, 3));
            $student->courses()->syncWithoutDetaching($coursesToEnroll->pluck('id')->toArray());
        }
    }
}
