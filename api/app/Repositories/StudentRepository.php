<?php

namespace App\Repositories;

use App\Student;

class StudentRepository extends AbstractRepository
{
    public function __construct(Student $model)
    {
        parent::__construct($model);
    }

    public function list($filters = [], $perPage = 15)
    {
        $query = $this->model->query();

        if (!empty($filters['name'])) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($filters['name']) . '%']);
        }

        if (!empty($filters['email'])) {
            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($filters['email']) . '%']);
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }
}
