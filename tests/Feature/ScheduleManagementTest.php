<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClassroomData;
use Tests\TestCase;

class ScheduleManagementTest extends TestCase
{
    use CreatesClassroomData, RefreshDatabase;

    public function test_authenticated_user_can_create_schedule(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom();
        $start = Carbon::tomorrow()->setTime(8, 0);

        $this->actingAs($user)->post('/schedules', [
            'roomId' => $room->id,
            'startTime' => $start->format('Y-m-d H:i:s'),
            'endTime' => $start->copy()->addHours(2)->format('Y-m-d H:i:s'),
            'purpose' => 'Dạy môn kiểm thử',
        ])->assertRedirect('/dashboard')->assertSessionHas('success');

        $this->assertDatabaseHas('schedules', [
            'roomId' => $room->id,
            'userId' => $user->id,
            'purpose' => 'Dạy môn kiểm thử',
        ]);
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom();
        $start = Carbon::tomorrow()->setTime(10, 0);

        $this->actingAs($user)->post('/schedules', [
            'roomId' => $room->id,
            'startTime' => $start->format('Y-m-d H:i:s'),
            'endTime' => $start->copy()->subHour()->format('Y-m-d H:i:s'),
            'purpose' => 'Lịch sai',
        ])->assertSessionHasErrors('endTime');
    }

    public function test_conflicting_schedule_is_rejected(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom();
        $start = Carbon::tomorrow()->setTime(8, 0);
        $this->createSchedule($user, $room, [
            'startTime' => $start,
            'endTime' => $start->copy()->addHours(2),
        ]);

        $this->actingAs($user)->post('/schedules', [
            'roomId' => $room->id,
            'startTime' => $start->copy()->addHour()->format('Y-m-d H:i:s'),
            'endTime' => $start->copy()->addHours(3)->format('Y-m-d H:i:s'),
            'purpose' => 'Lịch bị trùng',
        ])->assertSessionHasErrors('conflict');

        $this->assertDatabaseCount('schedules', 1);
    }

    public function test_owner_can_update_schedule(): void
    {
        $user = $this->createUser();
        $schedule = $this->createSchedule($user);
        $start = Carbon::tomorrow()->setTime(13, 0);

        $this->actingAs($user)->put("/schedules/{$schedule->id}", [
            'roomId' => $schedule->roomId,
            'startTime' => $start->format('Y-m-d H:i:s'),
            'endTime' => $start->copy()->addHours(2)->format('Y-m-d H:i:s'),
            'purpose' => 'Lịch đã cập nhật',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('schedules', ['id' => $schedule->id, 'purpose' => 'Lịch đã cập nhật']);
    }

    public function test_other_user_cannot_update_schedule(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $schedule = $this->createSchedule($owner);

        $this->actingAs($other)->put("/schedules/{$schedule->id}", [])
            ->assertSessionHas('error');
    }

    public function test_owner_can_delete_schedule(): void
    {
        $user = $this->createUser();
        $schedule = $this->createSchedule($user);

        $this->actingAs($user)->delete("/schedules/{$schedule->id}")
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_other_user_cannot_delete_schedule(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $schedule = $this->createSchedule($owner);

        $this->actingAs($other)->delete("/schedules/{$schedule->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('schedules', ['id' => $schedule->id]);
    }
}
