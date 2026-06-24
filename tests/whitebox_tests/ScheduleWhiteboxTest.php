<?php

namespace Tests\WhiteboxTests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClassroomData;
use Tests\TestCase;

/**
 * White-box tests cho quản lý lịch đặt phòng.
 */
class ScheduleWhiteboxTest extends TestCase
{
    use CreatesClassroomData, RefreshDatabase;

    public function test_01_schedule_model_belongs_to_user_and_room(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom();
        $schedule = $this->createSchedule($user, $room);

        $this->assertTrue($schedule->user->is($user));
        $this->assertTrue($schedule->room->is($room));
    }

    public function test_02_authenticated_user_can_create_schedule_with_valid_time(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom();
        $start = Carbon::tomorrow()->setTime(8, 0);

        $this->actingAs($user)->post('/schedules', [
            'roomId' => $room->id,
            'startTime' => $start->format('Y-m-d H:i:s'),
            'endTime' => $start->copy()->addHours(2)->format('Y-m-d H:i:s'),
            'purpose' => 'Lịch white-box',
        ])->assertRedirect('/dashboard')->assertSessionHas('success');

        $this->assertDatabaseHas('schedules', [
            'roomId' => $room->id,
            'userId' => $user->id,
            'purpose' => 'Lịch white-box',
        ]);
    }

    public function test_03_invalid_time_and_conflicting_schedule_are_rejected(): void
    {
        $user = $this->createUser();
        $room = $this->createRoom();
        $start = Carbon::tomorrow()->setTime(10, 0);

        $this->actingAs($user)->post('/schedules', [
            'roomId' => $room->id,
            'startTime' => $start->format('Y-m-d H:i:s'),
            'endTime' => $start->copy()->subHour()->format('Y-m-d H:i:s'),
            'purpose' => 'Giờ kết thúc sai',
        ])->assertSessionHasErrors('endTime');

        $this->createSchedule($user, $room, [
            'startTime' => $start,
            'endTime' => $start->copy()->addHours(2),
        ]);

        $this->actingAs($user)->post('/schedules', [
            'roomId' => $room->id,
            'startTime' => $start->copy()->addHour()->format('Y-m-d H:i:s'),
            'endTime' => $start->copy()->addHours(3)->format('Y-m-d H:i:s'),
            'purpose' => 'Bị trùng lịch',
        ])->assertSessionHasErrors('conflict');
    }

    public function test_04_owner_can_update_delete_but_other_user_cannot(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $schedule = $this->createSchedule($owner);
        $start = Carbon::tomorrow()->setTime(13, 0);

        $this->actingAs($owner)->put("/schedules/{$schedule->id}", [
            'roomId' => $schedule->roomId,
            'startTime' => $start->format('Y-m-d H:i:s'),
            'endTime' => $start->copy()->addHours(2)->format('Y-m-d H:i:s'),
            'purpose' => 'Lịch đã sửa',
        ])->assertSessionHas('success');

        $this->actingAs($other)->put("/schedules/{$schedule->id}", [])
            ->assertSessionHas('error');

        $this->actingAs($other)->delete("/schedules/{$schedule->id}")
            ->assertSessionHas('error');
        $this->assertDatabaseHas('schedules', ['id' => $schedule->id]);

        $this->actingAs($owner)->delete("/schedules/{$schedule->id}")
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }
}
