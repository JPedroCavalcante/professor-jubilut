<?php

namespace App\Repositories;

use App\Course;

class CourseRepository extends AbstractRepository
{
    public function __construct(Course $model)
    {
        parent::__construct($model);
    }
}
