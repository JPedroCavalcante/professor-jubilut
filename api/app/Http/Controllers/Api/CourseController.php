<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Services\Course\ListCourseService;
use App\Services\Course\CreateCourseService;
use App\Services\Course\FindCourseService;
use App\Services\Course\UpdateCourseService;
use App\Services\Course\DeleteCourseService;
use App\Http\Resources\CourseResource;

class CourseController extends Controller
{
    public function index(ListCourseService $service)
    {
        $courses = $service->execute();
        return CourseResource::collection($courses);
    }

    public function store(StoreCourseRequest $request, CreateCourseService $service)
    {
        $service->execute($request->validated());
        return response()->json(null, 201);
    }

    public function show($id, FindCourseService $service)
    {
        $course = $service->execute($id);
        return new CourseResource($course);
    }

    public function update(StoreCourseRequest $request, $id, UpdateCourseService $service)
    {
        $service->execute($id, $request->validated());
        return response()->json(null, 204);
    }

    public function delete($id, DeleteCourseService $service)
    {
        $service->execute($id);
        return response()->json(null, 204);
    }
}
