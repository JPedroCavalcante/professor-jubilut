<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class StudentRegistrationTest extends TestCase
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

    public function test_admin_can_create_a_student()
    {
        $token = $this->getAdminToken();

        $response = $this->postJson('/api/admin/students', [
            'name' => 'Novo Aluno',
            'email' => 'novo@aluno.com',
            'birth_date' => '2000-01-15',
            'password' => 'secret123',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('students', ['email' => 'novo@aluno.com']);
        $this->assertDatabaseHas('users', ['email' => 'novo@aluno.com', 'role' => 'student']);
    }

    public function test_student_email_must_be_unique()
    {
        $token = $this->getAdminToken();

        $this->postJson('/api/admin/students', [
            'name' => 'Aluno 1',
            'email' => 'duplicado@aluno.com',
            'birth_date' => '2000-01-15',
            'password' => 'secret123',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response = $this->postJson('/api/admin/students', [
            'name' => 'Aluno 2',
            'email' => 'duplicado@aluno.com',
            'birth_date' => '2001-02-20',
            'password' => 'secret456',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_student_requires_mandatory_fields()
    {
        $token = $this->getAdminToken();

        $response = $this->postJson('/api/admin/students', [
            'email' => 'test@aluno.com',
            'password' => 'secret123',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }
}
