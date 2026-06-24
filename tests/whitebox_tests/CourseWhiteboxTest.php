<?php

namespace Tests\WhiteboxTests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClassroomData;
use Tests\TestCase;

/**
 * White-box tests cho quản lý môn học.
 */
class CourseWhiteboxTest extends TestCase
{
    use CreatesClassroomData, RefreshDatabase;

    public function test_01_course_model_links_instructor_and_booking_requests(): void
    {
        $instructor = $this->createUser(['fullName' => 'Giảng viên WB']);
        $course = $this->createCourse($instructor, ['code' => 'WB101']);
        $this->createBookingRequest(course: $course);

        $this->assertSame('WB101', $course->code);
        $this->assertTrue($course->instructor->is($instructor));
        $this->assertCount(1, $course->bookingRequests);
    }

    public function test_02_admin_can_create_course_and_validation_rejects_duplicate_or_bad_credits(): void
    {
        $admin = $this->createAdmin();
        $instructor = $this->createUser();

        $this->actingAs($admin)->post('/courses', [
            'name' => 'Whitebox Testing',
            'code' => 'WBTEST',
            'instructorId' => $instructor->id,
            'credits' => 3,
            'description' => 'Môn học kiểm thử hộp trắng',
        ])->assertRedirect('/dashboard')->assertSessionHas('success');

        $this->assertDatabaseHas('courses', ['code' => 'WBTEST']);

        $this->actingAs($admin)->post('/courses', [
            'name' => 'Môn trùng mã',
            'code' => 'WBTEST',
            'instructorId' => $instructor->id,
            'credits' => 3,
        ])->assertSessionHasErrors('code');

        $this->actingAs($admin)->post('/courses', [
            'name' => 'Môn sai tín chỉ',
            'code' => 'WBBAD',
            'instructorId' => $instructor->id,
            'credits' => 11,
        ])->assertSessionHasErrors('credits');
    }

    public function test_03_admin_can_update_course_but_cannot_delete_course_in_use(): void
    {
        $admin = $this->createAdmin();
        $instructor = $this->createUser();
        $course = $this->createCourse($instructor);

        $this->actingAs($admin)->put("/courses/{$course->id}", [
            'name' => 'Môn đã cập nhật',
            'code' => $course->code,
            'instructorId' => $instructor->id,
            'credits' => 4,
            'description' => 'Đã sửa',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('courses', ['id' => $course->id, 'credits' => 4]);

        $this->createBookingRequest(course: $course);

        $this->actingAs($admin)->delete("/courses/{$course->id}")
            ->assertSessionHas('error');
        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }

    public function test_04_regular_user_cannot_create_course(): void
    {
        $this->actingAs($this->createUser())->post('/courses', [])
            ->assertForbidden();
    }
}
