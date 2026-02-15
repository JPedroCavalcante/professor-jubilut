<?php

namespace App\Services\Enrollment;

use App\Repositories\EnrollmentRepository;

class ListStudentEnrollmentsService
{
    protected $enrollmentRepository;

    public function __construct(EnrollmentRepository $enrollmentRepository)
    {
        $this->enrollmentRepository = $enrollmentRepository;
    }

    public function execute($studentId)
    {
        return $this->enrollmentRepository->getStudentCourses($studentId);
    }
}
