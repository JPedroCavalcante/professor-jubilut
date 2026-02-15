<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Student;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function getMyCourses()
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        return response()->json($student->courses);
    }

    public function getStudentCourses($studentId)
    {
        $student = Student::with('courses')->findOrFail($studentId);

        return response()->json($student->courses);
    }

    public function store(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);

        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($student->courses()->where('course_id', $request->course_id)->exists()) {
            return response()->json(['message' => 'Student is already enrolled in this course.'], 422);
        }

        $student->courses()->attach($request->course_id);

        return response()->json(['message' => 'Enrolled successfully.'], 201);
    }

    public function destroy($studentId, $courseId)
    {
        $student = Student::findOrFail($studentId);
        $student->courses()->detach($courseId);

        return response()->json(['message' => 'Unenrolled successfully.']);
    }
}
