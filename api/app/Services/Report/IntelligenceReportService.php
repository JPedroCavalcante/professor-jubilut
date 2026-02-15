<?php

namespace App\Services\Report;

use App\Course;
use Carbon\Carbon;

class IntelligenceReportService
{
    public function execute()
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
                $student->age = Carbon::parse($student->birth_date)->diffInYears($now);
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
