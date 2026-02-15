<?php

namespace App\Services\Course;

use App\Repositories\CourseRepository;

class ListCourseService
{
    protected $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    public function execute($perPage = 15)
    {
        return $this->courseRepository->paginate($perPage);
    }
}
