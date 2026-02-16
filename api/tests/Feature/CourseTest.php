<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Course;

class CourseTest extends TestCase
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

    protected function getStudentToken()
    {
        $user = factory(User::class)->create([
            'role' => 'student',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        return $response->json('access_token');
    }

    public function test_admin_can_list_courses()
    {
        $token = $this->getAdminToken();
        factory(Course::class, 3)->create();

        $response = $this->getJson('/api/admin/courses', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'description', 'start_date', 'end_date'],
            ],
        ]);
    }

    public function test_unauthenticated_user_cannot_list_courses()
    {
        $response = $this->getJson('/api/admin/courses');

        $response->assertStatus(401);
    }

    public function test_student_cannot_access_admin_courses_list()
    {
        $token = $this->getStudentToken();

        $response = $this->getJson('/api/admin/courses', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_create_a_course()
    {
        $token = $this->getAdminToken();

        $response = $this->postJson('/api/admin/courses', [
            'title' => 'Introduction to Laravel',
            'description' => 'A comprehensive course on Laravel.',
            'start_date' => '2025-01-01',
            'end_date' => '2025-06-30',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('courses', ['title' => 'Introduction to Laravel']);
    }

    public function test_course_creation_requires_title()
    {
        $token = $this->getAdminToken();

        $response = $this->postJson('/api/admin/courses', [
            'description' => 'No title given.',
            'start_date' => '2025-01-01',
            'end_date' => '2025-06-30',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    public function test_course_creation_requires_start_date()
    {
        $token = $this->getAdminToken();

        $response = $this->postJson('/api/admin/courses', [
            'title' => 'No Start Date Course',
            'end_date' => '2025-06-30',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('start_date');
    }

    public function test_course_creation_requires_end_date()
    {
        $token = $this->getAdminToken();

        $response = $this->postJson('/api/admin/courses', [
            'title' => 'No End Date Course',
            'start_date' => '2025-01-01',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('end_date');
    }

    public function test_course_end_date_must_be_after_or_equal_to_start_date()
    {
        $token = $this->getAdminToken();

        $response = $this->postJson('/api/admin/courses', [
            'title' => 'Bad Date Range Course',
            'start_date' => '2025-06-01',
            'end_date' => '2025-01-01',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('end_date');
    }

    public function test_course_description_is_optional()
    {
        $token = $this->getAdminToken();

        $response = $this->postJson('/api/admin/courses', [
            'title' => 'Course Without Description',
            'start_date' => '2025-01-01',
            'end_date' => '2025-06-30',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('courses', ['title' => 'Course Without Description']);
    }

    public function test_student_cannot_create_a_course()
    {
        $token = $this->getStudentToken();

        $response = $this->postJson('/api/admin/courses', [
            'title' => 'Unauthorized Course',
            'start_date' => '2025-01-01',
            'end_date' => '2025-06-30',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_admin_can_view_a_single_course()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create(['title' => 'Specific Course']);

        $response = $this->getJson("/api/admin/courses/{$course->id}", [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Specific Course']);
    }

    public function test_show_returns_404_for_nonexistent_course()
    {
        $token = $this->getAdminToken();

        $response = $this->getJson('/api/admin/courses/99999', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(404);
    }

    public function test_admin_can_update_a_course()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();

        $response = $this->putJson("/api/admin/courses/{$course->id}", [
            'title' => 'Updated Title',
            'start_date' => '2025-03-01',
            'end_date' => '2025-09-30',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(204);
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'title' => 'Updated Title']);
    }

    public function test_course_update_validates_required_fields()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();

        $response = $this->putJson("/api/admin/courses/{$course->id}", [
            'description' => 'Missing required fields',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'start_date', 'end_date']);
    }

    public function test_student_cannot_update_a_course()
    {
        $token = $this->getStudentToken();
        $course = factory(Course::class)->create();

        $response = $this->putJson("/api/admin/courses/{$course->id}", [
            'title' => 'Should Not Update',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_a_course()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();

        $response = $this->deleteJson("/api/admin/courses/{$course->id}", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_delete_returns_404_for_nonexistent_course()
    {
        $token = $this->getAdminToken();

        $response = $this->deleteJson('/api/admin/courses/99999', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(404);
    }

    public function test_student_cannot_delete_a_course()
    {
        $token = $this->getStudentToken();
        $course = factory(Course::class)->create();

        $response = $this->deleteJson("/api/admin/courses/{$course->id}", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }
}
