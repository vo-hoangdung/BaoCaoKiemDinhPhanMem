<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClassroomData;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use CreatesClassroomData, RefreshDatabase;

    public function test_admin_can_create_course(): void
    {
        $admin = $this->createAdmin();
        $instructor = $this->createUser();

        $this->actingAs($admin)->post('/courses', [
            'name' => 'Kiểm thử phần mềm',
            'code' => 'KTPM001',
            'instructorId' => $instructor->id,
            'credits' => 3,
            'description' => 'Môn kiểm thử',
        ])->assertRedirect('/dashboard')->assertSessionHas('success');

        $this->assertDatabaseHas('courses', ['code' => 'KTPM001']);
    }

    public function test_course_code_must_be_unique(): void
    {
        $admin = $this->createAdmin();
        $instructor = $this->createUser();
        $this->createCourse($instructor, ['code' => 'DUP001']);

        $this->actingAs($admin)->post('/courses', [
            'name' => 'Môn trùng',
            'code' => 'DUP001',
            'instructorId' => $instructor->id,
            'credits' => 3,
        ])->assertSessionHasErrors('code');
    }

    public function test_credits_must_be_between_one_and_ten(): void
    {
        $admin = $this->createAdmin();
        $instructor = $this->createUser();

        $this->actingAs($admin)->post('/courses', [
            'name' => 'Môn sai tín chỉ',
            'code' => 'BAD001',
            'instructorId' => $instructor->id,
            'credits' => 11,
        ])->assertSessionHasErrors('credits');
    }

    public function test_admin_can_update_course(): void
    {
        $admin = $this->createAdmin();
        $instructor = $this->createUser();
        $course = $this->createCourse($instructor);

        $this->actingAs($admin)->put("/courses/{$course->id}", [
            'name' => 'Môn học cập nhật',
            'code' => $course->code,
            'instructorId' => $instructor->id,
            'credits' => 4,
            'description' => 'Đã cập nhật',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('courses', ['id' => $course->id, 'credits' => 4]);
    }

    public function test_course_used_by_booking_request_cannot_be_deleted(): void
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $this->createBookingRequest(course: $course);

        $this->actingAs($admin)->delete("/courses/{$course->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }

    public function test_regular_user_cannot_create_course(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->post('/courses', [])->assertForbidden();
    }
}
