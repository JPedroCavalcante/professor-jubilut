<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Student;
use App\Course;

class EnrollmentEdgeCasesTest extends TestCase
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

    protected function getStudentToken(User $user)
    {
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        return $response->json('access_token');
    }

    public function test_admin_can_list_enrollments_for_a_student()
    {
        $token = $this->getAdminToken();
        $student = factory(Student::class)->create();
        $course1 = factory(Course::class)->create(['title' => 'First Enrolled Course']);
        $course2 = factory(Course::class)->create(['title' => 'Second Enrolled Course']);
        $student->courses()->attach([$course1->id, $course2->id]);

        $response = $this->getJson("/api/admin/students/{$student->id}/courses", [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_enrollments_list_is_empty_for_unenrolled_student()
    {
        $token = $this->getAdminToken();
        $student = factory(Student::class)->create();

        $response = $this->getJson("/api/admin/students/{$student->id}/courses", [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_enrollment_list_returns_course_details()
    {
        $token = $this->getAdminToken();
        $student = factory(Student::class)->create();
        $course = factory(Course::class)->create(['title' => 'Detail Check Course']);
        $student->courses()->attach($course->id);

        $response = $this->getJson("/api/admin/students/{$student->id}/courses", [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Detail Check Course']);
    }

    public function test_enrollment_requires_course_id_field()
    {
        $token = $this->getAdminToken();
        $student = factory(Student::class)->create();

        $response = $this->postJson("/api/admin/students/{$student->id}/courses", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422);
    }

    public function test_enrollment_rejects_nonexistent_course_id()
    {
        $token = $this->getAdminToken();
        $student = factory(Student::class)->create();

        $response = $this->postJson("/api/admin/students/{$student->id}/courses", [
            'course_id' => 99999,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
    }

    public function test_enrolling_student_in_multiple_courses_is_allowed()
    {
        $token = $this->getAdminToken();
        $student = factory(Student::class)->create();
        $course1 = factory(Course::class)->create();
        $course2 = factory(Course::class)->create();

        $this->postJson("/api/admin/students/{$student->id}/courses", [
            'course_id' => $course1->id,
        ], ['Authorization' => 'Bearer ' . $token])->assertStatus(201);

        $this->postJson("/api/admin/students/{$student->id}/courses", [
            'course_id' => $course2->id,
        ], ['Authorization' => 'Bearer ' . $token])->assertStatus(201);

        $this->assertCount(2, $student->courses()->get());
    }

    public function test_multiple_students_can_enroll_in_same_course()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();
        $student1 = factory(Student::class)->create();
        $student2 = factory(Student::class)->create();

        $this->postJson("/api/admin/students/{$student1->id}/courses", [
            'course_id' => $course->id,
        ], ['Authorization' => 'Bearer ' . $token])->assertStatus(201);

        $this->postJson("/api/admin/students/{$student2->id}/courses", [
            'course_id' => $course->id,
        ], ['Authorization' => 'Bearer ' . $token])->assertStatus(201);

        $this->assertDatabaseHas('enrollments', ['student_id' => $student1->id, 'course_id' => $course->id]);
        $this->assertDatabaseHas('enrollments', ['student_id' => $student2->id, 'course_id' => $course->id]);
    }

    public function test_unenrolling_a_student_not_enrolled_still_returns_204()
    {
        $token = $this->getAdminToken();
        $student = factory(Student::class)->create();
        $course = factory(Course::class)->create();

        $response = $this->deleteJson("/api/admin/students/{$student->id}/courses/{$course->id}", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(204);
    }

    public function test_admin_can_unenroll_and_reenroll_a_student()
    {
        $token = $this->getAdminToken();
        $student = factory(Student::class)->create();
        $course = factory(Course::class)->create();

        $this->postJson("/api/admin/students/{$student->id}/courses", [
            'course_id' => $course->id,
        ], ['Authorization' => 'Bearer ' . $token])->assertStatus(201);

        $this->deleteJson("/api/admin/students/{$student->id}/courses/{$course->id}", [], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(204);

        $this->assertDatabaseMissing('enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $this->postJson("/api/admin/students/{$student->id}/courses", [
            'course_id' => $course->id,
        ], ['Authorization' => 'Bearer ' . $token])->assertStatus(201);

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_student_can_view_their_own_enrolled_courses()
    {
        $user = factory(User::class)->create([
            'role' => 'student',
            'password' => bcrypt('password'),
        ]);
        $student = factory(Student::class)->create(['user_id' => $user->id]);
        $course = factory(Course::class)->create(['title' => 'My Enrolled Course']);
        $student->courses()->attach($course->id);

        $token = $this->getStudentToken($user);

        $response = $this->getJson('/api/student/courses', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'My Enrolled Course']);
    }

    public function test_student_courses_returns_empty_when_not_enrolled()
    {
        $user = factory(User::class)->create([
            'role' => 'student',
            'password' => bcrypt('password'),
        ]);
        factory(Student::class)->create(['user_id' => $user->id]);
        $token = $this->getStudentToken($user);

        $response = $this->getJson('/api/student/courses', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_student_only_sees_their_own_courses_not_others()
    {
        $user = factory(User::class)->create([
            'role' => 'student',
            'password' => bcrypt('password'),
        ]);
        $myStudent = factory(Student::class)->create(['user_id' => $user->id]);
        $otherStudent = factory(Student::class)->create();

        $myCourse = factory(Course::class)->create(['title' => 'My Course']);
        $otherCourse = factory(Course::class)->create(['title' => 'Other Course']);

        $myStudent->courses()->attach($myCourse->id);
        $otherStudent->courses()->attach($otherCourse->id);

        $token = $this->getStudentToken($user);

        $response = $this->getJson('/api/student/courses', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $data = collect($response->json('data'));
        $this->assertNotNull($data->firstWhere('title', 'My Course'));
        $this->assertNull($data->firstWhere('title', 'Other Course'));
    }

    public function test_unauthenticated_user_cannot_access_student_courses()
    {
        $response = $this->getJson('/api/student/courses');

        $response->assertStatus(401);
    }

    public function test_admin_cannot_access_student_courses_endpoint()
    {
        $admin = factory(User::class)->state('admin')->create([
            'password' => bcrypt('password'),
        ]);
        $response = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);
        $token = $response->json('access_token');

        $response = $this->getJson('/api/student/courses', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }
}
