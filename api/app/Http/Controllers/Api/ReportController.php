<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Course;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function intelligence()
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
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'age' => $age,
                ];
            });

            $avgAge = round($studentsWithAge->avg('age'), 1);
            $youngest = $studentsWithAge->sortBy('age')->first();
            $oldest = $studentsWithAge->sortByDesc('age')->first();

            $report[] = [
                'course_id' => $course->id,
                'course_title' => $course->title,
                'avg_age' => $avgAge,
                'youngest' => $youngest,
                'oldest' => $oldest,
                'total_students' => $studentsWithAge->count(),
            ];
        }

        return response()->json($report);
    }
}
