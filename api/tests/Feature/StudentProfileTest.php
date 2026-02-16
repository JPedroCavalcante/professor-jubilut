<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Student;

class StudentProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function createStudentWithToken($userOverrides = [], $studentOverrides = [])
    {
        $user = factory(User::class)->create(array_merge([
            'role' => 'student',
            'password' => bcrypt('password'),
        ], $userOverrides));

        $student = factory(Student::class)->create(array_merge([
            'user_id' => $user->id,
        ], $studentOverrides));

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        return [
            'user' => $user,
            'student' => $student,
            'token' => $response->json('access_token'),
        ];
    }

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

    public function test_student_can_view_own_profile()
    {
        $data = $this->createStudentWithToken(
            ['name' => 'Profile Student'],
            ['name' => 'Profile Student', 'email' => 'profile@student.com']
        );

        $response = $this->getJson('/api/student/profile', [
            'Authorization' => 'Bearer ' . $data['token'],
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => 'profile@student.com']);
    }

    public function test_profile_response_contains_expected_fields()
    {
        $data = $this->createStudentWithToken();

        $response = $this->getJson('/api/student/profile', [
            'Authorization' => 'Bearer ' . $data['token'],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'name', 'email', 'birth_date', 'user_id']);
    }

    public function test_unauthenticated_user_cannot_access_profile()
    {
        $response = $this->getJson('/api/student/profile');

        $response->assertStatus(401);
    }

    public function test_admin_cannot_access_student_profile_endpoint()
    {
        $token = $this->getAdminToken();

        $response = $this->getJson('/api/student/profile', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }

    public function test_profile_returns_404_when_student_record_is_missing()
    {
        $user = factory(User::class)->create([
            'role' => 'student',
            'password' => bcrypt('password'),
        ]);
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $response->json('access_token');

        $response = $this->getJson('/api/student/profile', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(404);
        $response->assertJsonFragment(['message' => 'Student profile not found.']);
    }

    public function test_student_can_update_own_name()
    {
        $data = $this->createStudentWithToken(
            [],
            ['name' => 'Original Name', 'email' => 'original@student.com']
        );

        $response = $this->putJson('/api/student/profile', [
            'name' => 'Updated Name',
            'email' => 'original@student.com',
        ], ['Authorization' => 'Bearer ' . $data['token']]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('students', [
            'id' => $data['student']->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_student_can_update_own_email()
    {
        $data = $this->createStudentWithToken(
            [],
            ['name' => 'Email Updater', 'email' => 'before@student.com']
        );

        $response = $this->putJson('/api/student/profile', [
            'name' => 'Email Updater',
            'email' => 'after@student.com',
        ], ['Authorization' => 'Bearer ' . $data['token']]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('students', [
            'id' => $data['student']->id,
            'email' => 'after@student.com',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $data['user']->id,
            'email' => 'after@student.com',
        ]);
    }

    public function test_student_can_update_own_birth_date()
    {
        $data = $this->createStudentWithToken(
            [],
            ['birth_date' => '1995-05-15']
        );

        $response = $this->putJson('/api/student/profile', [
            'name' => $data['student']->name,
            'email' => $data['student']->email,
            'birth_date' => '1990-01-01',
        ], ['Authorization' => 'Bearer ' . $data['token']]);

        $response->assertStatus(200);
        $data['student']->refresh();
        $this->assertEquals('1990-01-01', $data['student']->birth_date->format('Y-m-d'));
    }

    public function test_student_can_update_profile_keeping_same_email()
    {
        $data = $this->createStudentWithToken(
            [],
            ['name' => 'Same Email', 'email' => 'keep@student.com']
        );

        $response = $this->putJson('/api/student/profile', [
            'name' => 'Updated With Same Email',
            'email' => 'keep@student.com',
        ], ['Authorization' => 'Bearer ' . $data['token']]);

        $response->assertStatus(200);
    }

    public function test_profile_update_requires_name()
    {
        $data = $this->createStudentWithToken();

        $response = $this->putJson('/api/student/profile', [
            'email' => $data['student']->email,
        ], ['Authorization' => 'Bearer ' . $data['token']]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_profile_update_requires_email()
    {
        $data = $this->createStudentWithToken();

        $response = $this->putJson('/api/student/profile', [
            'name' => $data['student']->name,
        ], ['Authorization' => 'Bearer ' . $data['token']]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_profile_update_requires_valid_email_format()
    {
        $data = $this->createStudentWithToken();

        $response = $this->putJson('/api/student/profile', [
            'name' => $data['student']->name,
            'email' => 'not-an-email',
        ], ['Authorization' => 'Bearer ' . $data['token']]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_profile_update_email_must_be_unique_across_other_students()
    {
        $this->createStudentWithToken(
            [],
            ['email' => 'taken@student.com']
        );

        $data = $this->createStudentWithToken(
            [],
            ['email' => 'mine@student.com']
        );

        $response = $this->putJson('/api/student/profile', [
            'name' => $data['student']->name,
            'email' => 'taken@student.com',
        ], ['Authorization' => 'Bearer ' . $data['token']]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_profile_update_birth_date_is_optional()
    {
        $data = $this->createStudentWithToken();

        $response = $this->putJson('/api/student/profile', [
            'name' => $data['student']->name,
            'email' => $data['student']->email,
        ], ['Authorization' => 'Bearer ' . $data['token']]);

        $response->assertStatus(200);
    }

    public function test_profile_update_also_syncs_user_name()
    {
        $data = $this->createStudentWithToken(
            ['name' => 'Old User Name'],
            ['name' => 'Old Student Name']
        );

        $response = $this->putJson('/api/student/profile', [
            'name' => 'New Synced Name',
            'email' => $data['student']->email,
        ], ['Authorization' => 'Bearer ' . $data['token']]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $data['user']->id,
            'name' => 'New Synced Name',
        ]);
    }

    public function test_unauthenticated_user_cannot_update_profile()
    {
        $response = $this->putJson('/api/student/profile', [
            'name' => 'Hacker',
            'email' => 'hacker@evil.com',
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_cannot_update_student_profile_endpoint()
    {
        $token = $this->getAdminToken();

        $response = $this->putJson('/api/student/profile', [
            'name' => 'Admin Pretending',
            'email' => 'admin@pretend.com',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }
}
