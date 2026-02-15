<?php

namespace App\Services\Student;

use App\Repositories\StudentRepository;
use App\User;
use Illuminate\Support\Facades\DB;
use Exception;

class DeleteStudentService
{
    protected $studentRepository;

    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    public function execute($id)
    {
        $student = $this->studentRepository->find($id);
        $userId = $student->user_id;

        DB::beginTransaction();
        try {
            $this->studentRepository->delete($id);
            User::where('id', $userId)->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
