<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClassroomData;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use CreatesClassroomData, RefreshDatabase;

    public function test_admin_role_helper_returns_correct_value(): void
    {
        $this->assertTrue($this->createAdmin()->isAdmin());
        $this->assertFalse($this->createUser()->isAdmin());
    }

    public function test_room_relationships_return_schedules_and_requests(): void
    {
        $room = $this->createRoom();
        $this->createSchedule(room: $room);
        $this->createBookingRequest(room: $room);

        $this->assertCount(1, $room->schedules);
        $this->assertCount(1, $room->bookingRequests);
    }

    public function test_course_belongs_to_instructor_and_has_requests(): void
    {
        $instructor = $this->createUser();
        $course = $this->createCourse($instructor);
        $this->createBookingRequest(course: $course);

        $this->assertTrue($course->instructor->is($instructor));
        $this->assertCount(1, $course->bookingRequests);
    }

    public function test_schedule_belongs_to_user_and_room(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom();
        $schedule = $this->createSchedule($user, $room);

        $this->assertTrue($schedule->user->is($user));
        $this->assertTrue($schedule->room->is($room));
    }

    public function test_booking_request_belongs_to_all_related_models(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom();
        $course = $this->createCourse($user);
        $request = $this->createBookingRequest($user, $room, $course);

        $this->assertTrue($request->user->is($user));
        $this->assertTrue($request->room->is($room));
        $this->assertTrue($request->course->is($course));
    }
}
