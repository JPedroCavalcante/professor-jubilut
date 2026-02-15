<?php

namespace App\Services;

use App\Repositories\CourseRepository;

class CourseService
{
    protected $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    public function list()
    {
        return $this->courseRepository->list();
    }

    public function create(array $data)
    {
        return $this->courseRepository->create($data);
    }

    public function find($id)
    {
        return $this->courseRepository->find($id);
    }

    public function update($id, array $data)
    {
        $course = $this->courseRepository->find($id);
        return $this->courseRepository->update($course, $data);
    }

    public function delete($id)
    {
        $course = $this->courseRepository->find($id);
        return $this->courseRepository->delete($course);
    }
}
