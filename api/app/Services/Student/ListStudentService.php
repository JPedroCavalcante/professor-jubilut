<?php

namespace App\Services\Student;

use App\Repositories\StudentRepository;

class ListStudentService
{
    protected $studentRepository;

    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    public function execute(array $filters = [])
    {
        return $this->studentRepository->list($filters);
    }
}
