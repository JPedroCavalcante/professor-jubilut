<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Subject;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with([
            'course:id,title',
            'professor:id,name',
        ])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($subjects);
    }

    public function store(StoreSubjectRequest $request)
    {
        $subject = Subject::create($request->validated());
        $subject->load(['course:id,title', 'professor:id,name']);

        return response()->json($subject, 201);
    }

    public function show($id)
    {
        $subject = Subject::with(['course:id,title', 'professor:id,name'])->findOrFail($id);

        return response()->json($subject);
    }

    public function update(StoreSubjectRequest $request, $id)
    {
        $subject = Subject::findOrFail($id);
        $subject->update($request->validated());
        $subject->load(['course:id,title', 'professor:id,name']);

        return response()->json($subject);
    }

    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return response()->json(['message' => 'Subject deleted successfully.']);
    }
}
