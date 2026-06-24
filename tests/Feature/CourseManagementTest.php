<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\Course;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'username' => 'user_test',
            'fullName' => 'User Test',
            'email' => 'user_test@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ], $overrides));
    }

    private function makeAdmin(array $overrides = []): User
    {
        return $this->makeUser(array_merge([
            'username' => 'admin_test',
            'fullName' => 'Admin Test',
            'email' => 'admin_test@example.com',
            'role' => 'admin',
        ], $overrides));
    }

    private function makeRoom(array $overrides = []): Room
    {
        return Room::create(array_merge([
            'name' => 'Phòng A101',
            'capacity' => 50,
            'location' => 'Tầng 1',
            'equipment' => 'Máy chiếu',
        ], $overrides));
    }

    private function makeCourse(User $instructor, array $overrides = []): Course
    {
        return Course::create(array_merge([
            'name' => 'Kiểm thử phần mềm',
            'code' => 'KTPM001',
            'description' => 'Môn học kiểm thử phần mềm',
            'credits' => 3,
            'instructorId' => $instructor->id,
        ], $overrides));
    }

    public function test_admin_can_create_course(): void
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeUser([
            'username' => 'teacher_01',
            'email' => 'teacher_01@example.com',
            'fullName' => 'Giảng viên 01',
        ]);

        $response = $this->actingAs($admin)->post(route('courses.store'), [
            'name' => 'Lập trình Web nâng cao',
            'code' => 'WEBNC001',
            'description' => 'Môn học về Laravel',
            'credits' => 3,
            'instructorId' => $teacher->id,
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('courses', [
            'name' => 'Lập trình Web nâng cao',
            'code' => 'WEBNC001',
            'credits' => 3,
            'instructorId' => $teacher->id,
        ]);
    }

    public function test_normal_user_cannot_create_course(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post(route('courses.store'), [
            'name' => 'Môn học không hợp lệ',
            'code' => 'NOAUTH001',
            'description' => 'User thường không được thêm',
            'credits' => 3,
            'instructorId' => $user->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('courses', ['code' => 'NOAUTH001']);
    }

    public function test_course_code_must_be_unique(): void
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeUser([
            'username' => 'teacher_02',
            'email' => 'teacher_02@example.com',
        ]);
        $this->makeCourse($teacher, ['code' => 'DUP001']);

        $response = $this->actingAs($admin)->from('/dashboard')->post(route('courses.store'), [
            'name' => 'Môn học bị trùng mã',
            'code' => 'DUP001',
            'description' => 'Kiểm tra trùng mã môn',
            'credits' => 3,
            'instructorId' => $teacher->id,
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors('code');
    }

    public function test_course_credits_must_be_between_1_and_10(): void
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeUser([
            'username' => 'teacher_03',
            'email' => 'teacher_03@example.com',
        ]);

        $response = $this->actingAs($admin)->from('/dashboard')->post(route('courses.store'), [
            'name' => 'Môn học sai tín chỉ',
            'code' => 'CREDIT999',
            'description' => 'Tín chỉ vượt quá giới hạn',
            'credits' => 11,
            'instructorId' => $teacher->id,
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors('credits');
        $this->assertDatabaseMissing('courses', ['code' => 'CREDIT999']);
    }

    public function test_admin_can_update_course(): void
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeUser([
            'username' => 'teacher_04',
            'email' => 'teacher_04@example.com',
        ]);
        $course = $this->makeCourse($teacher);

        $response = $this->actingAs($admin)->put(route('courses.update', $course), [
            'name' => 'Kiểm thử phần mềm nâng cao',
            'code' => 'KTPM002',
            'description' => 'Đã cập nhật môn học',
            'credits' => 4,
            'instructorId' => $teacher->id,
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Kiểm thử phần mềm nâng cao',
            'code' => 'KTPM002',
            'credits' => 4,
        ]);
    }

    public function test_admin_can_delete_unused_course(): void
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeUser([
            'username' => 'teacher_05',
            'email' => 'teacher_05@example.com',
        ]);
        $course = $this->makeCourse($teacher, ['code' => 'DELETE001']);

        $response = $this->actingAs($admin)->delete(route('courses.destroy', $course));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_admin_cannot_delete_course_that_is_used_in_booking_request(): void
    {
        $admin = $this->makeAdmin();
        $user = $this->makeUser([
            'username' => 'booking_user',
            'email' => 'booking_user@example.com',
        ]);
        $teacher = $this->makeUser([
            'username' => 'teacher_06',
            'email' => 'teacher_06@example.com',
        ]);
        $room = $this->makeRoom();
        $course = $this->makeCourse($teacher, ['code' => 'USED001']);

        BookingRequest::create([
            'userId' => $user->id,
            'roomId' => $room->id,
            'courseId' => $course->id,
            'requestDate' => Carbon::tomorrow()->toDateString(),
            'startTime' => '08:00',
            'endTime' => '10:00',
            'purpose' => 'Yêu cầu dùng môn học này',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->delete(route('courses.destroy', $course));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }
}
