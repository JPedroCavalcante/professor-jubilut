<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Course;
use App\Professor;
use App\Subject;

class SubjectTest extends TestCase
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

    public function test_admin_can_list_subjects()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();
        $professor = factory(Professor::class)->create();
        factory(Subject::class, 3)->create([
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ]);

        $response = $this->getJson('/api/admin/subjects', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $this->assertCount(3, $response->json());
    }

    public function test_subjects_list_includes_course_and_professor_data()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create(['title' => 'Parent Course']);
        $professor = factory(Professor::class)->create(['name' => 'Prof. Linked']);
        factory(Subject::class)->create([
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ]);

        $response = $this->getJson('/api/admin/subjects', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $subjects = $response->json();
        $this->assertEquals('Parent Course', $subjects[0]['course']['title']);
        $this->assertEquals('Prof. Linked', $subjects[0]['professor']['name']);
    }

    public function test_unauthenticated_user_cannot_list_subjects()
    {
        $response = $this->getJson('/api/admin/subjects');

        $response->assertStatus(401);
    }

    public function test_student_cannot_list_subjects()
    {
        $token = $this->getStudentToken();

        $response = $this->getJson('/api/admin/subjects', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_create_a_subject()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();
        $professor = factory(Professor::class)->create();

        $response = $this->postJson('/api/admin/subjects', [
            'title' => 'Advanced PHP',
            'description' => 'Deep dive into PHP internals.',
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('subjects', [
            'title' => 'Advanced PHP',
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ]);
    }

    public function test_created_subject_response_includes_relationships()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create(['title' => 'Course With Subject']);
        $professor = factory(Professor::class)->create(['name' => 'Prof. Creator']);

        $response = $this->postJson('/api/admin/subjects', [
            'title' => 'New Subject',
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
        $this->assertEquals('Course With Subject', $response->json('course.title'));
        $this->assertEquals('Prof. Creator', $response->json('professor.name'));
    }

    public function test_subject_creation_requires_title()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();
        $professor = factory(Professor::class)->create();

        $response = $this->postJson('/api/admin/subjects', [
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    public function test_subject_creation_requires_course_id()
    {
        $token = $this->getAdminToken();
        $professor = factory(Professor::class)->create();

        $response = $this->postJson('/api/admin/subjects', [
            'title' => 'Orphan Subject',
            'professor_id' => $professor->id,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('course_id');
    }

    public function test_subject_creation_requires_professor_id()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();

        $response = $this->postJson('/api/admin/subjects', [
            'title' => 'Subject Without Professor',
            'course_id' => $course->id,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('professor_id');
    }

    public function test_subject_creation_rejects_nonexistent_course_id()
    {
        $token = $this->getAdminToken();
        $professor = factory(Professor::class)->create();

        $response = $this->postJson('/api/admin/subjects', [
            'title' => 'Bad Course Subject',
            'course_id' => 99999,
            'professor_id' => $professor->id,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('course_id');
    }

    public function test_subject_creation_rejects_nonexistent_professor_id()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();

        $response = $this->postJson('/api/admin/subjects', [
            'title' => 'Bad Professor Subject',
            'course_id' => $course->id,
            'professor_id' => 99999,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('professor_id');
    }

    public function test_subject_description_is_optional()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();
        $professor = factory(Professor::class)->create();

        $response = $this->postJson('/api/admin/subjects', [
            'title' => 'No Description Subject',
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
    }

    public function test_student_cannot_create_a_subject()
    {
        $token = $this->getStudentToken();
        $course = factory(Course::class)->create();
        $professor = factory(Professor::class)->create();

        $response = $this->postJson('/api/admin/subjects', [
            'title' => 'Unauthorized Subject',
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_admin_can_view_a_single_subject()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();
        $professor = factory(Professor::class)->create();
        $subject = factory(Subject::class)->create([
            'title' => 'Viewable Subject',
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ]);

        $response = $this->getJson("/api/admin/subjects/{$subject->id}", [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Viewable Subject']);
        $this->assertArrayHasKey('course', $response->json());
        $this->assertArrayHasKey('professor', $response->json());
    }

    public function test_show_returns_404_for_nonexistent_subject()
    {
        $token = $this->getAdminToken();

        $response = $this->getJson('/api/admin/subjects/99999', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(404);
    }

    public function test_admin_can_update_a_subject()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();
        $professor = factory(Professor::class)->create();
        $subject = factory(Subject::class)->create([
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ]);

        $response = $this->putJson("/api/admin/subjects/{$subject->id}", [
            'title' => 'Updated Subject Title',
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'title' => 'Updated Subject Title',
        ]);
    }

    public function test_admin_can_reassign_subject_to_different_course()
    {
        $token = $this->getAdminToken();
        $originalCourse = factory(Course::class)->create();
        $newCourse = factory(Course::class)->create();
        $professor = factory(Professor::class)->create();
        $subject = factory(Subject::class)->create([
            'course_id' => $originalCourse->id,
            'professor_id' => $professor->id,
        ]);

        $response = $this->putJson("/api/admin/subjects/{$subject->id}", [
            'title' => $subject->title,
            'course_id' => $newCourse->id,
            'professor_id' => $professor->id,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'course_id' => $newCourse->id,
        ]);
    }

    public function test_student_cannot_update_a_subject()
    {
        $token = $this->getStudentToken();
        $course = factory(Course::class)->create();
        $professor = factory(Professor::class)->create();
        $subject = factory(Subject::class)->create([
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ]);

        $response = $this->putJson("/api/admin/subjects/{$subject->id}", [
            'title' => 'Should Not Update',
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_a_subject()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();
        $professor = factory(Professor::class)->create();
        $subject = factory(Subject::class)->create([
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ]);

        $response = $this->deleteJson("/api/admin/subjects/{$subject->id}", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Subject deleted successfully.']);
        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    public function test_delete_returns_404_for_nonexistent_subject()
    {
        $token = $this->getAdminToken();

        $response = $this->deleteJson('/api/admin/subjects/99999', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(404);
    }

    public function test_student_cannot_delete_a_subject()
    {
        $token = $this->getStudentToken();
        $course = factory(Course::class)->create();
        $professor = factory(Professor::class)->create();
        $subject = factory(Subject::class)->create([
            'course_id' => $course->id,
            'professor_id' => $professor->id,
        ]);

        $response = $this->deleteJson("/api/admin/subjects/{$subject->id}", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }
}
