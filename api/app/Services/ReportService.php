<?php

namespace App\Services;

use App\Student;
use App\Course;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    public function getIntelligenceReport()
    {
        $courses = Course::with([
            'students' => function ($query) {
                $query->whereNotNull('birth_date');
            }
        ])->get();

        $report = [];

        foreach ($courses as $course) {
            $students = $course->students;

            if ($students->isEmpty()) {
                $report[] = [
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'avg_age' => 0,
                    'youngest' => null,
                    'oldest' => null,
                    'total_students' => 0,
                ];
                continue;
            }

            $now = Carbon::now();

            $studentsWithAge = $students->map(function ($student) use ($now) {
                $age = Carbon::parse($student->birth_date)->diffInYears($now);
                // We return object structure to match existing logic if needed,
                // or just arrays. The controller used object access for sort.
                $student->age = $age;
                return $student;
            });

            $avgAge = round($studentsWithAge->avg('age'), 1);
            $youngest = $studentsWithAge->sortBy('age')->first();
            $oldest = $studentsWithAge->sortByDesc('age')->first();

            $report[] = [
                'course_id' => $course->id,
                'course_title' => $course->title,
                'avg_age' => $avgAge,
                'youngest' => [
                    'name' => $youngest->name,
                    'age' => $youngest->age,
                ],
                'oldest' => [
                    'name' => $oldest->name,
                    'age' => $oldest->age,
                ],
                'total_students' => $studentsWithAge->count(),
            ];
        }

        return $report;
    }
}
