<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreStudentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $studentId = $this->route('student');
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,' . $studentId,
            'birth_date' => 'nullable|date',
        ];

        if ($isUpdate) {
            $rules['password'] = 'nullable|string|min:6';
        } else {
            $rules['password'] = 'required|string|min:6';
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
