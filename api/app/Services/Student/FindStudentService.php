<?php

namespace App\Services\Student;

use App\Repositories\StudentRepository;

class FindStudentService
{
    protected $studentRepository;

    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    public function execute($id)
    {
        return $this->studentRepository->find($id);
    }
}
