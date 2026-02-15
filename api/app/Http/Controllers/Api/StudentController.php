<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Student;
use App\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->has('name') && $request->name) {
            $query->where('name', 'ilike', '%' . $request->name . '%');
        }

        if ($request->has('email') && $request->email) {
            $query->where('email', 'ilike', '%' . $request->email . '%');
        }

        $students = $query->orderBy('id', 'desc')->get();

        return response()->json($students);
    }

    public function store(StoreStudentRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['nome'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'student',
        ]);

        $student = Student::create([
            'nome' => $validated['nome'],
            'email' => $validated['email'],
            'data_nascimento' => $validated['data_nascimento'],
            'user_id' => $user->id,
        ]);

        return response()->json($student, 201);
    }

    public function show($id)
    {
        $student = Student::findOrFail($id);

        return response()->json($student);
    }

    public function update(StoreStudentRequest $request, $id)
    {
        $student = Student::findOrFail($id);
        $validated = $request->validated();

        $student->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'birth_date' => $validated['birth_date'],
        ]);

        // Sync user record
        $user = User::find($student->user_id);
        if ($user) {
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];
            if (!empty($validated['password'])) {
                $updateData['password'] = bcrypt($validated['password']);
            }
            $user->update($updateData);
        }

        return response()->json($student);
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $userId = $student->user_id;
        $student->delete();

        User::where('id', $userId)->delete();

        return response()->json(['message' => 'Student deleted successfully.']);
    }
}
