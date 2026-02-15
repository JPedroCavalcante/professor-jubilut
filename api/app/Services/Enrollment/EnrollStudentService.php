<?php

namespace App\Services\Enrollment;

use App\Repositories\EnrollmentRepository;
use Exception;

class EnrollStudentService
{
    protected $enrollmentRepository;

    public function __construct(EnrollmentRepository $enrollmentRepository)
    {
        $this->enrollmentRepository = $enrollmentRepository;
    }

    public function execute($studentId, $courseId)
    {
        if ($this->enrollmentRepository->isEnrolled($studentId, $courseId)) {
            throw new Exception('O aluno já está matriculado neste curso.');
        }

        return $this->enrollmentRepository->enroll($studentId, $courseId);
    }
}
