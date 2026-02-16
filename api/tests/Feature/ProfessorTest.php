<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Professor;

class ProfessorTest extends TestCase
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

    public function test_admin_can_list_professors()
    {
        $token = $this->getAdminToken();
        factory(Professor::class, 3)->create();

        $response = $this->getJson('/api/admin/professors', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $this->assertCount(3, $response->json());
    }

    public function test_unauthenticated_user_cannot_list_professors()
    {
        $response = $this->getJson('/api/admin/professors');

        $response->assertStatus(401);
    }

    public function test_student_cannot_list_professors()
    {
        $token = $this->getStudentToken();

        $response = $this->getJson('/api/admin/professors', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_create_a_professor()
    {
        $token = $this->getAdminToken();

        $response = $this->postJson('/api/admin/professors', [
            'name' => 'Dr. Jane Smith',
            'email' => 'jane.smith@university.com',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('professors', ['email' => 'jane.smith@university.com']);
        $response->assertJsonFragment(['name' => 'Dr. Jane Smith']);
    }

    public function test_professor_creation_requires_name()
    {
        $token = $this->getAdminToken();

        $response = $this->postJson('/api/admin/professors', [
            'email' => 'noname@university.com',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_professor_creation_requires_email()
    {
        $token = $this->getAdminToken();

        $response = $this->postJson('/api/admin/professors', [
            'name' => 'No Email Professor',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_professor_creation_requires_valid_email_format()
    {
        $token = $this->getAdminToken();

        $response = $this->postJson('/api/admin/professors', [
            'name' => 'Bad Email Professor',
            'email' => 'not-an-email',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_professor_email_must_be_unique_on_create()
    {
        $token = $this->getAdminToken();
        factory(Professor::class)->create(['email' => 'existing@university.com']);

        $response = $this->postJson('/api/admin/professors', [
            'name' => 'Duplicate Email Professor',
            'email' => 'existing@university.com',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_student_cannot_create_a_professor()
    {
        $token = $this->getStudentToken();

        $response = $this->postJson('/api/admin/professors', [
            'name' => 'Unauthorized Professor',
            'email' => 'unauthorized@university.com',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_admin_can_view_a_single_professor()
    {
        $token = $this->getAdminToken();
        $professor = factory(Professor::class)->create(['name' => 'Prof. Viewable']);

        $response = $this->getJson("/api/admin/professors/{$professor->id}", [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Prof. Viewable']);
    }

    public function test_show_returns_404_for_nonexistent_professor()
    {
        $token = $this->getAdminToken();

        $response = $this->getJson('/api/admin/professors/99999', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(404);
    }

    public function test_admin_can_update_a_professor()
    {
        $token = $this->getAdminToken();
        $professor = factory(Professor::class)->create();

        $response = $this->putJson("/api/admin/professors/{$professor->id}", [
            'name' => 'Updated Professor Name',
            'email' => 'updated@university.com',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('professors', [
            'id' => $professor->id,
            'name' => 'Updated Professor Name',
            'email' => 'updated@university.com',
        ]);
    }

    public function test_professor_can_be_updated_with_own_email()
    {
        $token = $this->getAdminToken();
        $professor = factory(Professor::class)->create(['email' => 'own@university.com']);

        $response = $this->putJson("/api/admin/professors/{$professor->id}", [
            'name' => 'Same Email Professor',
            'email' => 'own@university.com',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
    }

    public function test_professor_update_email_must_be_unique_across_other_professors()
    {
        $token = $this->getAdminToken();
        factory(Professor::class)->create(['email' => 'taken@university.com']);
        $professor = factory(Professor::class)->create();

        $response = $this->putJson("/api/admin/professors/{$professor->id}", [
            'name' => 'Updated Name',
            'email' => 'taken@university.com',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_student_cannot_update_a_professor()
    {
        $token = $this->getStudentToken();
        $professor = factory(Professor::class)->create();

        $response = $this->putJson("/api/admin/professors/{$professor->id}", [
            'name' => 'Should Not Update',
            'email' => 'nope@university.com',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_a_professor()
    {
        $token = $this->getAdminToken();
        $professor = factory(Professor::class)->create();

        $response = $this->deleteJson("/api/admin/professors/{$professor->id}", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Professor deleted successfully.']);
        $this->assertDatabaseMissing('professors', ['id' => $professor->id]);
    }

    public function test_delete_returns_404_for_nonexistent_professor()
    {
        $token = $this->getAdminToken();

        $response = $this->deleteJson('/api/admin/professors/99999', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(404);
    }

    public function test_student_cannot_delete_a_professor()
    {
        $token = $this->getStudentToken();
        $professor = factory(Professor::class)->create();

        $response = $this->deleteJson("/api/admin/professors/{$professor->id}", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }
}
