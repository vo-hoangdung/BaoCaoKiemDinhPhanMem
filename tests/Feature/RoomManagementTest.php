<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClassroomData;
use Tests\TestCase;

class RoomManagementTest extends TestCase
{
    use CreatesClassroomData, RefreshDatabase;

    public function test_admin_can_create_room(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post('/rooms', [
            'name' => 'Phòng A101',
            'capacity' => 45,
            'location' => 'Tầng 1',
            'equipment' => 'Máy chiếu',
        ])->assertRedirect('/dashboard')->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', ['name' => 'Phòng A101', 'capacity' => 45]);
    }

    public function test_room_name_must_be_unique(): void
    {
        $admin = $this->createAdmin();
        $this->createRoom(['name' => 'Phòng trùng']);

        $this->actingAs($admin)->post('/rooms', [
            'name' => 'Phòng trùng',
            'capacity' => 30,
            'location' => 'Tầng 1',
        ])->assertSessionHasErrors('name');
    }

    public function test_room_capacity_must_be_at_least_one(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post('/rooms', [
            'name' => 'Phòng sai',
            'capacity' => 0,
            'location' => 'Tầng 1',
        ])->assertSessionHasErrors('capacity');
    }

    public function test_admin_can_update_room(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createRoom();

        $this->actingAs($admin)->put("/rooms/{$room->id}", [
            'name' => 'Phòng đã cập nhật',
            'capacity' => 60,
            'location' => 'Tầng 3',
            'equipment' => 'Bảng thông minh',
        ])->assertRedirect('/dashboard')->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'capacity' => 60]);
    }

    public function test_admin_can_delete_unused_room(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createRoom();

        $this->actingAs($admin)->delete("/rooms/{$room->id}")
            ->assertRedirect('/dashboard')->assertSessionHas('success');

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    public function test_room_used_by_schedule_cannot_be_deleted(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createRoom();
        $this->createSchedule($admin, $room);

        $this->actingAs($admin)->delete("/rooms/{$room->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    public function test_regular_user_cannot_mutate_rooms(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->post('/rooms', [
            'name' => 'Phòng không được phép',
            'capacity' => 20,
            'location' => 'Tầng 1',
        ])->assertForbidden();
    }
}
