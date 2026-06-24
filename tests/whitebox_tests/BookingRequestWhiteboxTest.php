<?php

namespace Tests\WhiteboxTests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClassroomData;
use Tests\TestCase;

/**
 * White-box tests cho yêu cầu đặt phòng.
 */
class BookingRequestWhiteboxTest extends TestCase
{
    use CreatesClassroomData, RefreshDatabase;

    public function test_01_booking_request_model_belongs_to_user_room_course(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom();
        $course = $this->createCourse($user);
        $request = $this->createBookingRequest($user, $room, $course);

        $this->assertTrue($request->user->is($user));
        $this->assertTrue($request->room->is($room));
        $this->assertTrue($request->course->is($course));
        $this->assertSame('pending', $request->status);
    }

    public function test_02_user_can_create_request_and_validation_rejects_bad_inputs(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom();
        $course = $this->createCourse($user);

        $this->actingAs($user)->post('/booking-requests', [
            'roomId' => $room->id,
            'courseId' => $course->id,
            'requestDate' => Carbon::tomorrow()->toDateString(),
            'startTime' => '09:00',
            'endTime' => '11:00',
            'purpose' => 'Yêu cầu white-box',
        ])->assertRedirect('/dashboard')->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', [
            'userId' => $user->id,
            'status' => 'pending',
            'purpose' => 'Yêu cầu white-box',
        ]);

        $this->actingAs($user)->post('/booking-requests', [
            'roomId' => $room->id,
            'courseId' => $course->id,
            'requestDate' => Carbon::yesterday()->toDateString(),
            'startTime' => '09:00',
            'endTime' => '11:00',
            'purpose' => 'Ngày quá khứ',
        ])->assertSessionHasErrors('requestDate');

        $this->actingAs($user)->post('/booking-requests', [
            'roomId' => $room->id,
            'courseId' => $course->id,
            'requestDate' => Carbon::tomorrow()->toDateString(),
            'startTime' => '11:00',
            'endTime' => '09:00',
            'purpose' => 'Giờ sai',
        ])->assertSessionHasErrors('endTime');
    }

    public function test_03_request_conflict_and_processed_update_branches(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom();
        $course = $this->createCourse($user);
        $date = Carbon::tomorrow();

        $this->createSchedule($user, $room, [
            'startTime' => $date->copy()->setTime(8, 0),
            'endTime' => $date->copy()->setTime(10, 0),
        ]);

        $this->actingAs($user)->post('/booking-requests', [
            'roomId' => $room->id,
            'courseId' => $course->id,
            'requestDate' => $date->toDateString(),
            'startTime' => '09:00',
            'endTime' => '11:00',
            'purpose' => 'Bị trùng lịch',
        ])->assertSessionHasErrors('conflict');

        $processed = $this->createBookingRequest($user, $room, $course, ['status' => 'approved']);
        $this->actingAs($user)->put("/booking-requests/{$processed->id}", [])
            ->assertSessionHas('error');
    }

    public function test_04_admin_can_approve_or_reject_and_regular_user_cannot(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $request = $this->createBookingRequest($user);

        $this->actingAs($user)->post("/booking-requests/{$request->id}/approve")
            ->assertForbidden();

        $this->actingAs($admin)->post("/booking-requests/{$request->id}/approve")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', ['id' => $request->id, 'status' => 'approved']);
        $this->assertDatabaseHas('schedules', ['roomId' => $request->roomId, 'userId' => $request->userId]);

        $secondRequest = $this->createBookingRequest();
        $this->actingAs($admin)->post("/booking-requests/{$secondRequest->id}/reject")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', ['id' => $secondRequest->id, 'status' => 'rejected']);
    }
}
