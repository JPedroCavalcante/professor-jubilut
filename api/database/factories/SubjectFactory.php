<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Subject;
use App\Course;
use App\Professor;
use Faker\Generator as Faker;

$factory->define(Subject::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence(3),
        'description' => $faker->paragraph,
        'course_id' => function () {
            return factory(Course::class)->create()->id;
        },
        'professor_id' => function () {
            return factory(Professor::class)->create()->id;
        },
    ];
});
