<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Student;
use App\User;
use Faker\Generator as Faker;

$factory->define(Student::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'birth_date' => $faker->date('Y-m-d', '-18 years'),
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
    ];
});
