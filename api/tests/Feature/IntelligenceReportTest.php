<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Student;
use App\Course;
use Carbon\Carbon;

class IntelligenceReportTest extends TestCase
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

    public function test_report_returns_correct_structure()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();
        $student = factory(Student::class)->create(['birth_date' => '2000-01-01']);
        $student->courses()->attach($course->id);

        $response = $this->getJson('/api/admin/reports/intelligence', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['course_id', 'course_title', 'avg_age', 'youngest', 'oldest', 'total_students'],
        ]);
    }

    public function test_average_age_is_calculated_correctly()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();

        $now = Carbon::now();

        $student1 = factory(Student::class)->create([
            'birth_date' => $now->copy()->subYears(20)->subDays(1)->format('Y-m-d'),
        ]);

        $student2 = factory(Student::class)->create([
            'birth_date' => $now->copy()->subYears(30)->subDays(1)->format('Y-m-d'),
        ]);

        $course->students()->attach([$student1->id, $student2->id]);

        $response = $this->getJson('/api/admin/reports/intelligence', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $data = collect($response->json())->firstWhere('course_id', $course->id);
        $this->assertEquals(25.0, $data['avg_age']);
    }

    public function test_youngest_and_oldest_student_are_correct()
    {
        $token = $this->getAdminToken();
        $course = factory(Course::class)->create();
        $now = Carbon::now();

        $young = factory(Student::class)->create([
            'name' => 'Young Student',
            'birth_date' => $now->copy()->subYears(18)->subDays(1)->format('Y-m-d'),
        ]);
        $mid = factory(Student::class)->create([
            'name' => 'Mid Student',
            'birth_date' => $now->copy()->subYears(25)->subDays(1)->format('Y-m-d'),
        ]);
        $old = factory(Student::class)->create([
            'name' => 'Old Student',
            'birth_date' => $now->copy()->subYears(40)->subDays(1)->format('Y-m-d'),
        ]);

        $course->students()->attach([$young->id, $mid->id, $old->id]);

        $response = $this->getJson('/api/admin/reports/intelligence', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $data = collect($response->json())->firstWhere('course_id', $course->id);

        $this->assertEquals('Young Student', $data['youngest']['name']);
        $this->assertEquals(18, $data['youngest']['age']);
        $this->assertEquals('Old Student', $data['oldest']['name']);
        $this->assertEquals(40, $data['oldest']['age']);
    }
}
