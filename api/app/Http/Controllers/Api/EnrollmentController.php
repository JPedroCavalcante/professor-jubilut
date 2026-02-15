<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enrollment\StoreEnrollmentRequest;
use App\Services\Enrollment\ListStudentEnrollmentsService;
use App\Services\Enrollment\EnrollStudentService;
use App\Services\Enrollment\UnenrollStudentService;
use App\Http\Resources\CourseResource;
use App\Student;
use Exception;

class EnrollmentController extends Controller
{
    public function getMyCourses(ListStudentEnrollmentsService $service)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        $courses = $service->execute($student->id);
        return CourseResource::collection($courses);
    }

    public function getStudentCourses($studentId, ListStudentEnrollmentsService $service)
    {
        $courses = $service->execute($studentId);
        return CourseResource::collection($courses);
    }

    public function store(StoreEnrollmentRequest $request, $studentId, EnrollStudentService $service)
    {
        try {
            $service->execute($studentId, $request->course_id);
            return response()->json(null, 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function delete($studentId, $courseId, UnenrollStudentService $service)
    {
        $service->execute($studentId, $courseId);
        return response()->json(null, 204);
    }
}
