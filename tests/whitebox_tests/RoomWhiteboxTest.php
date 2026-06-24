<?php

namespace Tests\WhiteboxTests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClassroomData;
use Tests\TestCase;

/**
 * White-box tests cho quản lý phòng học.
 *
 * Bao phủ model Room, validation trong RoomController, nhánh phân quyền admin/user
 * và điều kiện không cho xóa phòng đã được sử dụng.
 */
class RoomWhiteboxTest extends TestCase
{
    use CreatesClassroomData, RefreshDatabase;

    public function test_01_room_model_creation_and_relationships(): void
    {
        $room = $this->createRoom([
            'name' => 'Phòng WB-101',
            'capacity' => 50,
            'location' => 'Tầng 1',
        ]);

        $this->assertSame('Phòng WB-101', $room->name);
        $this->assertSame(50, $room->capacity);

        $this->createSchedule(room: $room);
        $this->createBookingRequest(room: $room);

        $this->assertCount(1, $room->schedules);
        $this->assertCount(1, $room->bookingRequests);
    }

    public function test_02_admin_can_create_room_and_validation_blocks_bad_data(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post('/rooms', [
            'name' => 'Phòng WB-Create',
            'capacity' => 40,
            'location' => 'Tầng 2',
            'equipment' => 'Máy chiếu',
        ])->assertRedirect('/dashboard')->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', ['name' => 'Phòng WB-Create']);

        $this->actingAs($admin)->post('/rooms', [
            'name' => 'Phòng WB-Create',
            'capacity' => 20,
            'location' => 'Tầng 3',
        ])->assertSessionHasErrors('name');

        $this->actingAs($admin)->post('/rooms', [
            'name' => 'Phòng WB-Bad',
            'capacity' => 0,
            'location' => 'Tầng 3',
        ])->assertSessionHasErrors('capacity');
    }

    public function test_03_admin_can_update_and_delete_unused_room(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createRoom();

        $this->actingAs($admin)->put("/rooms/{$room->id}", [
            'name' => 'Phòng WB-Updated',
            'capacity' => 70,
            'location' => 'Tầng 4',
            'equipment' => 'Bảng thông minh',
        ])->assertRedirect('/dashboard')->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'capacity' => 70]);

        $this->actingAs($admin)->delete("/rooms/{$room->id}")
            ->assertRedirect('/dashboard')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    public function test_04_room_in_use_cannot_be_deleted_and_user_cannot_mutate(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $room = $this->createRoom();
        $this->createSchedule($admin, $room);

        $this->actingAs($admin)->delete("/rooms/{$room->id}")
            ->assertSessionHas('error');
        $this->assertDatabaseHas('rooms', ['id' => $room->id]);

        $this->actingAs($user)->post('/rooms', [
            'name' => 'Phòng trái quyền',
            'capacity' => 20,
            'location' => 'Tầng 1',
        ])->assertForbidden();
    }
}
