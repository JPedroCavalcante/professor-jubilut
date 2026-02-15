<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Services\Student\ListStudentService;
use App\Services\Student\CreateStudentService;
use App\Services\Student\FindStudentService;
use App\Services\Student\UpdateStudentService;
use App\Services\Student\DeleteStudentService;
use App\Http\Resources\StudentResource;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request, ListStudentService $service)
    {
        $students = $service->execute($request->only('name', 'email'));
        return StudentResource::collection($students);
    }

    public function store(StoreStudentRequest $request, CreateStudentService $service)
    {
        $service->execute($request->validated());
        return response()->json(null, 201);
    }

    public function show($id, FindStudentService $service)
    {
        $student = $service->execute($id);
        return new StudentResource($student);
    }

    public function update(StoreStudentRequest $request, $id, UpdateStudentService $service)
    {
        $service->execute($id, $request->validated());
        return response()->json(null, 204);
    }

    public function delete($id, DeleteStudentService $service)
    {
        $service->execute($id);
        return response()->json(null, 204);
    }
}
