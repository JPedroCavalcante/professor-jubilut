<?php

namespace App\Services\Enrollment;

use App\Repositories\EnrollmentRepository;

class UnenrollStudentService
{
    protected $enrollmentRepository;

    public function __construct(EnrollmentRepository $enrollmentRepository)
    {
        $this->enrollmentRepository = $enrollmentRepository;
    }

    public function execute($studentId, $courseId)
    {
        return $this->enrollmentRepository->unenroll($studentId, $courseId);
    }
}
