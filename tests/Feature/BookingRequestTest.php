<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\Course;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BookingRequestTest extends TestCase
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

    private function makeBookingRequest(User $user, Room $room, Course $course, array $overrides = []): BookingRequest
    {
        return BookingRequest::create(array_merge([
            'userId' => $user->id,
            'roomId' => $room->id,
            'courseId' => $course->id,
            'requestDate' => Carbon::tomorrow()->toDateString(),
            'startTime' => '08:00',
            'endTime' => '10:00',
            'purpose' => 'Đặt phòng học bù',
            'status' => 'pending',
        ], $overrides));
    }

    public function test_user_can_create_booking_request(): void
    {
        $user = $this->makeUser();
        $teacher = $this->makeUser([
            'username' => 'teacher_01',
            'email' => 'teacher_01@example.com',
        ]);
        $room = $this->makeRoom();
        $course = $this->makeCourse($teacher);

        $response = $this->actingAs($user)->post(route('requests.store'), [
            'roomId' => $room->id,
            'courseId' => $course->id,
            'requestDate' => Carbon::tomorrow()->toDateString(),
            'startTime' => '08:00',
            'endTime' => '10:00',
            'purpose' => 'Đặt phòng để học bù môn kiểm thử',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', [
            'userId' => $user->id,
            'roomId' => $room->id,
            'courseId' => $course->id,
            'purpose' => 'Đặt phòng để học bù môn kiểm thử',
            'status' => 'pending',
        ]);
    }

    public function test_booking_request_date_cannot_be_in_the_past(): void
    {
        $user = $this->makeUser();
        $teacher = $this->makeUser([
            'username' => 'teacher_02',
            'email' => 'teacher_02@example.com',
        ]);
        $room = $this->makeRoom();
        $course = $this->makeCourse($teacher);

        $response = $this->actingAs($user)->from('/dashboard')->post(route('requests.store'), [
            'roomId' => $room->id,
            'courseId' => $course->id,
            'requestDate' => Carbon::yesterday()->toDateString(),
            'startTime' => '08:00',
            'endTime' => '10:00',
            'purpose' => 'Ngày quá khứ',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors('requestDate');
        $this->assertDatabaseMissing('booking_requests', ['purpose' => 'Ngày quá khứ']);
    }

    public function test_booking_request_end_time_must_be_after_start_time(): void
    {
        $user = $this->makeUser();
        $teacher = $this->makeUser([
            'username' => 'teacher_03',
            'email' => 'teacher_03@example.com',
        ]);
        $room = $this->makeRoom();
        $course = $this->makeCourse($teacher);

        $response = $this->actingAs($user)->from('/dashboard')->post(route('requests.store'), [
            'roomId' => $room->id,
            'courseId' => $course->id,
            'requestDate' => Carbon::tomorrow()->toDateString(),
            'startTime' => '10:00',
            'endTime' => '08:00',
            'purpose' => 'Giờ kết thúc sai',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors('endTime');
        $this->assertDatabaseMissing('booking_requests', ['purpose' => 'Giờ kết thúc sai']);
    }

    public function test_booking_request_fails_when_room_has_conflicting_schedule(): void
    {
        $user = $this->makeUser();
        $teacher = $this->makeUser([
            'username' => 'teacher_04',
            'email' => 'teacher_04@example.com',
        ]);
        $room = $this->makeRoom();
        $course = $this->makeCourse($teacher);
        $date = Carbon::tomorrow();

        Schedule::create([
            'roomId' => $room->id,
            'userId' => $user->id,
            'startTime' => $date->copy()->setTime(8, 0),
            'endTime' => $date->copy()->setTime(10, 0),
            'purpose' => 'Lịch đã tồn tại',
        ]);

        $response = $this->actingAs($user)->from('/dashboard')->post(route('requests.store'), [
            'roomId' => $room->id,
            'courseId' => $course->id,
            'requestDate' => $date->toDateString(),
            'startTime' => '09:00',
            'endTime' => '11:00',
            'purpose' => 'Yêu cầu bị trùng lịch',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors('conflict');
        $this->assertDatabaseMissing('booking_requests', ['purpose' => 'Yêu cầu bị trùng lịch']);
    }

    public function test_admin_can_approve_booking_request_and_schedule_is_created(): void
    {
        $admin = $this->makeAdmin();
        $user = $this->makeUser([
            'username' => 'request_user',
            'email' => 'request_user@example.com',
        ]);
        $teacher = $this->makeUser([
            'username' => 'teacher_05',
            'email' => 'teacher_05@example.com',
        ]);
        $room = $this->makeRoom();
        $course = $this->makeCourse($teacher);
        $bookingRequest = $this->makeBookingRequest($user, $room, $course);

        $response = $this->actingAs($admin)->post(route('requests.approve', $bookingRequest));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', [
            'id' => $bookingRequest->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('schedules', [
            'roomId' => $room->id,
            'userId' => $user->id,
        ]);
    }

    public function test_admin_can_reject_booking_request(): void
    {
        $admin = $this->makeAdmin();
        $user = $this->makeUser([
            'username' => 'reject_user',
            'email' => 'reject_user@example.com',
        ]);
        $teacher = $this->makeUser([
            'username' => 'teacher_06',
            'email' => 'teacher_06@example.com',
        ]);
        $room = $this->makeRoom();
        $course = $this->makeCourse($teacher);
        $bookingRequest = $this->makeBookingRequest($user, $room, $course);

        $response = $this->actingAs($admin)->post(route('requests.reject', $bookingRequest));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('booking_requests', [
            'id' => $bookingRequest->id,
            'status' => 'rejected',
        ]);
    }

    public function test_normal_user_cannot_approve_booking_request(): void
    {
        $user = $this->makeUser();
        $teacher = $this->makeUser([
            'username' => 'teacher_07',
            'email' => 'teacher_07@example.com',
        ]);
        $room = $this->makeRoom();
        $course = $this->makeCourse($teacher);
        $bookingRequest = $this->makeBookingRequest($user, $room, $course);

        $response = $this->actingAs($user)->post(route('requests.approve', $bookingRequest));

        $response->assertForbidden();
        $this->assertDatabaseHas('booking_requests', [
            'id' => $bookingRequest->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_update_other_users_booking_request(): void
    {
        $owner = $this->makeUser([
            'username' => 'owner_user',
            'email' => 'owner_user@example.com',
        ]);
        $otherUser = $this->makeUser([
            'username' => 'other_user',
            'email' => 'other_user@example.com',
        ]);
        $teacher = $this->makeUser([
            'username' => 'teacher_08',
            'email' => 'teacher_08@example.com',
        ]);
        $room = $this->makeRoom();
        $course = $this->makeCourse($teacher);
        $bookingRequest = $this->makeBookingRequest($owner, $room, $course, [
            'purpose' => 'Yêu cầu của người khác',
        ]);

        $response = $this->actingAs($otherUser)->put(route('requests.update', $bookingRequest), [
            'roomId' => $room->id,
            'courseId' => $course->id,
            'requestDate' => Carbon::tomorrow()->toDateString(),
            'startTime' => '13:00',
            'endTime' => '15:00',
            'purpose' => 'Sửa trái phép',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('booking_requests', [
            'id' => $bookingRequest->id,
            'purpose' => 'Yêu cầu của người khác',
        ]);
    }
}
