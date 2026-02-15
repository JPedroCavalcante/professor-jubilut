<?php

Route::post('/login', 'Api\AuthController@login');

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', 'Api\AuthController@logout');
    Route::get('/me', 'Api\AuthController@me');

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::resource('courses', 'Api\CourseController')->except(['destroy']);
        Route::delete('courses/{course}', 'Api\CourseController@delete');

        Route::resource('professors', 'Api\ProfessorController');
        Route::resource('subjects', 'Api\SubjectController');

        Route::resource('students', 'Api\StudentController')->except(['destroy']);
        Route::delete('students/{student}', 'Api\StudentController@delete');

        Route::get('students/{student}/courses', 'Api\EnrollmentController@getStudentCourses');
        Route::post('students/{student}/courses', 'Api\EnrollmentController@store');
        Route::delete('students/{student}/courses/{course}', 'Api\EnrollmentController@delete');

        Route::get('reports/intelligence', 'Api\ReportController@intelligence');
    });

    Route::prefix('student')->middleware('role:student')->group(function () {
        Route::get('/profile', 'Api\ProfileController@show');
        Route::put('/profile', 'Api\ProfileController@update');
        Route::get('/courses', 'Api\EnrollmentController@getMyCourses');
    });
});
