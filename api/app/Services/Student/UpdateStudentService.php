<?php

namespace App\Services\Student;

use App\Repositories\StudentRepository;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class UpdateStudentService
{
    protected $studentRepository;

    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    public function execute($id, array $data)
    {
        $student = $this->studentRepository->find($id);

        DB::beginTransaction();
        try {
            $this->studentRepository->update($id, [
                'name' => $data['name'],
                'email' => $data['email'],
                'birth_date' => $data['birth_date'],
            ]);

            $user = User::find($student->user_id);
            if ($user) {
                $userUpdate = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                ];
                if (!empty($data['password'])) {
                    $userUpdate['password'] = Hash::make($data['password']);
                }
                $user->update($userUpdate);
            }

            DB::commit();
            return $student;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
