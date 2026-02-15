<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Professor\StoreProfessorRequest;
use App\Professor;

class ProfessorController extends Controller
{
    public function index()
    {
        $professors = Professor::orderBy('id', 'desc')->get();
        return response()->json($professors);
    }

    public function store(StoreProfessorRequest $request)
    {
        $professor = Professor::create($request->validated());
        return response()->json($professor, 201);
    }

    public function show($id)
    {
        $professor = Professor::findOrFail($id);
        return response()->json($professor);
    }

    public function update(StoreProfessorRequest $request, $id)
    {
        $professor = Professor::findOrFail($id);
        $professor->update($request->validated());
        return response()->json($professor);
    }

    public function destroy($id)
    {
        $professor = Professor::findOrFail($id);
        $professor->delete();
        return response()->json(['message' => 'Professor deleted successfully.']);
    }
}
