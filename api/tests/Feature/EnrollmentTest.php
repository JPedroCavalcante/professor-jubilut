<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Student;
use App\Course;

class EnrollmentTest extends TestCase
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

    public function test_admin_can_enroll_student_in_course()
    {
        $token = $this->getAdminToken();
        $student = factory(Student::class)->create();
        $course = factory(Course::class)->create();

        $response = $this->postJson("/api/admin/students/{$student->id}/courses", [
            'course_id' => $course->id,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_duplicate_enrollment_is_prevented()
    {
        $token = $this->getAdminToken();
        $student = factory(Student::class)->create();
        $course = factory(Course::class)->create();

        $this->postJson("/api/admin/students/{$student->id}/courses", [
            'course_id' => $course->id,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response = $this->postJson("/api/admin/students/{$student->id}/courses", [
            'course_id' => $course->id,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
    }

    public function test_admin_can_remove_enrollment()
    {
        $token = $this->getAdminToken();
        $student = factory(Student::class)->create();
        $course = factory(Course::class)->create();
        $student->courses()->attach($course->id);

        $response = $this->deleteJson("/api/admin/students/{$student->id}/courses/{$course->id}", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }
}
