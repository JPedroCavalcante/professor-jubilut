<?php

namespace App\Services\Course;

use App\Repositories\CourseRepository;

class CreateCourseService
{
    protected $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    public function execute(array $data)
    {
        return $this->courseRepository->create($data);
    }
}
