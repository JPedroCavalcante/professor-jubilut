<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Student;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function getTokenFor(User $user)
    {
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        return $response->json('access_token');
    }

    public function test_admin_can_login_with_valid_credentials()
    {
        $admin = factory(User::class)->state('admin')->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'user' => ['id', 'name', 'email', 'role'],
        ]);
        $this->assertEquals('bearer', $response->json('token_type'));
        $this->assertEquals('admin', $response->json('user.role'));
    }

    public function test_student_can_login_with_valid_credentials()
    {
        $user = factory(User::class)->create([
            'role' => 'student',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'token_type', 'user']);
        $this->assertEquals('student', $response->json('user.role'));
    }

    public function test_login_fails_with_wrong_password()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
        $response->assertJsonFragment(['message' => 'Credenciais inválidas.']);
    }

    public function test_login_fails_with_nonexistent_email()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_requires_email_field()
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_login_requires_password_field()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    public function test_login_requires_valid_email_format()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_authenticated_user_can_logout()
    {
        $user = factory(User::class)->state('admin')->create([
            'password' => bcrypt('password'),
        ]);
        $token = $this->getTokenFor($user);

        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Logged out successfully.']);
    }

    public function test_logout_requires_authentication()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    public function test_me_returns_authenticated_user_data()
    {
        $user = factory(User::class)->state('admin')->create([
            'password' => bcrypt('password'),
        ]);
        $token = $this->getTokenFor($user);

        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['id', 'name', 'email', 'role']]);
        $this->assertEquals($user->email, $response->json('data.email'));
        $this->assertEquals('admin', $response->json('data.role'));
    }

    public function test_me_returns_student_id_for_student_users()
    {
        $user = factory(User::class)->create([
            'role' => 'student',
            'password' => bcrypt('password'),
        ]);
        $student = factory(Student::class)->create(['user_id' => $user->id]);
        $token = $this->getTokenFor($user);

        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($student->id, $response->json('data.student_id'));
    }

    public function test_me_requires_authentication()
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    public function test_me_does_not_expose_password()
    {
        $user = factory(User::class)->state('admin')->create([
            'password' => bcrypt('password'),
        ]);
        $token = $this->getTokenFor($user);

        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $this->assertArrayNotHasKey('password', $response->json('data'));
    }
}
