<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Student;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        $studentId = null;
        if ($this->role === 'student') {
            $student = Student::where('user_id', $this->id)->first();
            $studentId = $student ? $student->id : null;
        }

        return [
            'id' => $this->id,
            'student_id' => $studentId,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];
    }
}
