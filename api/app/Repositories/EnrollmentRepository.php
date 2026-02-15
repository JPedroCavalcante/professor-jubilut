<?php

namespace App\Repositories;

use App\Repositories\AbstractRepository;
use App\Student;

class EnrollmentRepository extends AbstractRepository
{
    public function __construct(Student $model)
    {
        parent::__construct($model);
    }

    public function getStudentCourses($studentId)
    {
        return $this->find($studentId)->courses;
    }

    public function isEnrolled($studentId, $courseId)
    {
        return $this->find($studentId)
            ->courses()
            ->where('course_id', $courseId)
            ->exists();
    }

    public function enroll($studentId, $courseId)
    {
        return $this->find($studentId)->courses()->attach($courseId);
    }

    public function unenroll($studentId, $courseId)
    {
        return $this->find($studentId)->courses()->detach($courseId);
    }
}
