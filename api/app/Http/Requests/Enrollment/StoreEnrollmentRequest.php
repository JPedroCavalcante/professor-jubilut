<?php

namespace App\Http\Requests\Enrollment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'course_id' => 'required|exists:courses,id',
        ];
    }

    public function messages()
    {
        return [
            'course_id.required' => 'O campo curso é obrigatório.',
            'course_id.exists' => 'O curso selecionado é inválido.',
        ];
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
