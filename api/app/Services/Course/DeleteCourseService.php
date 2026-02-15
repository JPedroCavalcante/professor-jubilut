<?php

namespace App\Services\Course;

use App\Repositories\CourseRepository;

class DeleteCourseService
{
    protected $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    public function execute($id)
    {
        return $this->courseRepository->delete($id);
    }
}
