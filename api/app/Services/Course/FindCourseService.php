<?php

namespace App\Services\Course;

use App\Repositories\CourseRepository;

class FindCourseService
{
    protected $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    public function execute($id)
    {
        return $this->courseRepository->find($id);
    }
}
