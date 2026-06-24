<?php

namespace Tests\Support;

use App\Models\BookingRequest;
use App\Models\Course;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

trait CreatesClassroomData
{
    protected function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'username' => 'user_'.uniqid(),
            'fullName' => 'Người dùng kiểm thử',
            'email' => uniqid().'@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ], $overrides));
    }

    protected function createAdmin(array $overrides = []): User
    {
        return $this->createUser(array_merge(['role' => 'admin'], $overrides));
    }

    protected function createRoom(array $overrides = []): Room
    {
        return Room::create(array_merge([
            'name' => 'Phòng '.uniqid(),
            'capacity' => 40,
            'location' => 'Tầng 2',
            'equipment' => 'Máy chiếu',
        ], $overrides));
    }

    protected function createCourse(?User $instructor = null, array $overrides = []): Course
    {
        $instructor ??= $this->createUser();

        return Course::create(array_merge([
            'name' => 'Môn học kiểm thử',
            'code' => 'MH'.strtoupper(substr(uniqid(), -6)),
            'description' => 'Dữ liệu phục vụ kiểm thử',
            'credits' => 3,
            'instructorId' => $instructor->id,
        ], $overrides));
    }

    protected function createSchedule(?User $user = null, ?Room $room = null, array $overrides = []): Schedule
    {
        $user ??= $this->createUser();
        $room ??= $this->createRoom();

        return Schedule::create(array_merge([
            'roomId' => $room->id,
            'userId' => $user->id,
            'startTime' => Carbon::tomorrow()->setTime(8, 0),
            'endTime' => Carbon::tomorrow()->setTime(10, 0),
            'purpose' => 'Giảng dạy',
        ], $overrides));
    }

    protected function createBookingRequest(
        ?User $user = null,
        ?Room $room = null,
        ?Course $course = null,
        array $overrides = []
    ): BookingRequest {
        $user ??= $this->createUser();
        $room ??= $this->createRoom();
        $course ??= $this->createCourse($user);

        return BookingRequest::create(array_merge([
            'userId' => $user->id,
            'roomId' => $room->id,
            'courseId' => $course->id,
            'requestDate' => Carbon::tomorrow()->toDateString(),
            'startTime' => '13:00',
            'endTime' => '15:00',
            'purpose' => 'Yêu cầu kiểm thử',
            'status' => 'pending',
        ], $overrides));
    }
}
