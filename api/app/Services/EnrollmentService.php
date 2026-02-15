<?php

namespace App\Services;

use App\Repositories\EnrollmentRepository;
use App\Repositories\StudentRepository;
use Exception;

class EnrollmentService
{
    protected $enrollmentRepository;
    protected $studentRepository;

    public function __construct(EnrollmentRepository $enrollmentRepository, StudentRepository $studentRepository)
    {
        $this->enrollmentRepository = $enrollmentRepository;
        $this->studentRepository = $studentRepository;
    }

    public function getStudentCourses($studentId)
    {
        return $this->enrollmentRepository->getStudentCourses($studentId);
    }

    public function getMyCourses($userId)
    {
        $student = \App\Student::where('user_id', $userId)->firstOrFail();
        return $this->enrollmentRepository->getStudentCourses($student->id);
    }

    public function enroll($studentId, $courseId)
    {
        if ($this->enrollmentRepository->isEnrolled($studentId, $courseId)) {
            throw new Exception('Student is already enrolled in this course.');
        }

        $this->enrollmentRepository->enroll($studentId, $courseId);
    }

    public function unenroll($studentId, $courseId)
    {
        $this->enrollmentRepository->unenroll($studentId, $courseId);
    }
}
