<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClassroomData;
use Tests\TestCase;

class BookingRequestManagementTest extends TestCase
{
    use CreatesClassroomData, RefreshDatabase;

    public function test_user_can_create_booking_request(): void
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
            'purpose' => 'Dạy bù',
        ])->assertRedirect('/dashboard')->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', [
            'userId' => $user->id,
            'status' => 'pending',
            'purpose' => 'Dạy bù',
        ]);
    }

    public function test_booking_date_cannot_be_in_the_past(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom();
        $course = $this->createCourse($user);

        $this->actingAs($user)->post('/booking-requests', [
            'roomId' => $room->id,
            'courseId' => $course->id,
            'requestDate' => Carbon::yesterday()->toDateString(),
            'startTime' => '09:00',
            'endTime' => '11:00',
            'purpose' => 'Ngày sai',
        ])->assertSessionHasErrors('requestDate');
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom();
        $course = $this->createCourse($user);

        $this->actingAs($user)->post('/booking-requests', [
            'roomId' => $room->id,
            'courseId' => $course->id,
            'requestDate' => Carbon::tomorrow()->toDateString(),
            'startTime' => '11:00',
            'endTime' => '09:00',
            'purpose' => 'Giờ sai',
        ])->assertSessionHasErrors('endTime');
    }

    public function test_request_conflicting_with_schedule_is_rejected(): void
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
    }

    public function test_owner_can_update_pending_request(): void
    {
        $user = $this->createUser();
        $request = $this->createBookingRequest($user);

        $this->actingAs($user)->put("/booking-requests/{$request->id}", [
            'roomId' => $request->roomId,
            'courseId' => $request->courseId,
            'requestDate' => Carbon::tomorrow()->addDay()->toDateString(),
            'startTime' => '14:00',
            'endTime' => '16:00',
            'purpose' => 'Nội dung đã sửa',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', ['id' => $request->id, 'purpose' => 'Nội dung đã sửa']);
    }

    public function test_processed_request_cannot_be_updated(): void
    {
        $user = $this->createUser();
        $request = $this->createBookingRequest($user, overrides: ['status' => 'approved']);

        $this->actingAs($user)->put("/booking-requests/{$request->id}", [])
            ->assertSessionHas('error');
    }

    public function test_admin_can_approve_request_and_create_schedule(): void
    {
        $admin = $this->createAdmin();
        $request = $this->createBookingRequest();

        $this->actingAs($admin)->post("/booking-requests/{$request->id}/approve")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', ['id' => $request->id, 'status' => 'approved']);
        $this->assertDatabaseHas('schedules', ['roomId' => $request->roomId, 'userId' => $request->userId]);
    }

    public function test_admin_can_reject_pending_request(): void
    {
        $admin = $this->createAdmin();
        $request = $this->createBookingRequest();

        $this->actingAs($admin)->post("/booking-requests/{$request->id}/reject")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', ['id' => $request->id, 'status' => 'rejected']);
    }

    public function test_regular_user_cannot_approve_request(): void
    {
        $user = $this->createUser();
        $request = $this->createBookingRequest();

        $this->actingAs($user)->post("/booking-requests/{$request->id}/approve")
            ->assertForbidden();
    }

    public function test_other_user_cannot_delete_request(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $request = $this->createBookingRequest($owner);

        $this->actingAs($other)->delete("/booking-requests/{$request->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('booking_requests', ['id' => $request->id]);
    }
}
