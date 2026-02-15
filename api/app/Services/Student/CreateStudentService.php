<?php

namespace App\Services\Student;

use App\Repositories\StudentRepository;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class CreateStudentService
{
    protected $studentRepository;

    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    public function execute(array $data)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'student',
            ]);

            $studentData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'birth_date' => $data['birth_date'] ?? null,
                'user_id' => $user->id,
            ];

            $student = $this->studentRepository->create($studentData);

            DB::commit();
            return $student;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
