<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Student;
use App\Course;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function getAdminToken()
    {
        $admin = factory(User::class)->state('admin')->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        return $response->json('access_token');
    }

    public function test_students_endpoint_returns_paginated_response()
    {
        $token = $this->getAdminToken();
        factory(Student::class, 20)->create();

        $response = $this->getJson('/api/admin/students', ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total'],
        ]);

        // Default per page is 15
        $this->assertCount(15, $response->json('data'));
    }

    public function test_courses_endpoint_returns_paginated_response()
    {
        $token = $this->getAdminToken();
        factory(Course::class, 20)->create();

        $response = $this->getJson('/api/admin/courses', ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total'],
        ]);

        // Default per page is 15
        $this->assertCount(15, $response->json('data'));
    }
}
