<?php

namespace App\Services;

use App\Repositories\StudentRepository;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class StudentService
{
    protected $studentRepository;

    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    public function list($filters = [])
    {
        return $this->studentRepository->list($filters);
    }

    public function create(array $data)
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

    public function find($id)
    {
        return $this->studentRepository->find($id);
    }

    public function update($id, array $data)
    {
        $student = $this->studentRepository->find($id);

        DB::beginTransaction();
        try {
            // Update Student
            $this->studentRepository->update($student, [
                'name' => $data['name'],
                'email' => $data['email'],
                'birth_date' => $data['birth_date'],
            ]);

            // Update User
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

    public function delete($id)
    {
        $student = $this->studentRepository->find($id);
        $userId = $student->user_id;

        DB::beginTransaction();
        try {
            $this->studentRepository->delete($student);
            User::where('id', $userId)->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
