<?php

namespace App\Services\Course;

use App\Repositories\CourseRepository;

class UpdateCourseService
{
    protected $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    public function execute($id, array $data)
    {
        return $this->courseRepository->update($id, $data);
    }
}
