<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoomManagementTest extends TestCase
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
            'equipment' => 'Máy chiếu, bảng',
        ], $overrides));
    }

    public function test_admin_can_create_room(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('rooms.store'), [
            'name' => 'Phòng B202',
            'capacity' => 60,
            'location' => 'Tầng 2',
            'equipment' => 'Máy chiếu',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'name' => 'Phòng B202',
            'capacity' => 60,
            'location' => 'Tầng 2',
        ]);
    }

    public function test_normal_user_cannot_create_room(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post(route('rooms.store'), [
            'name' => 'Phòng C303',
            'capacity' => 40,
            'location' => 'Tầng 3',
            'equipment' => 'Bảng trắng',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('rooms', ['name' => 'Phòng C303']);
    }

    public function test_room_name_must_be_unique(): void
    {
        $admin = $this->makeAdmin();
        $this->makeRoom(['name' => 'Phòng Trùng']);

        $response = $this->actingAs($admin)->from('/dashboard')->post(route('rooms.store'), [
            'name' => 'Phòng Trùng',
            'capacity' => 35,
            'location' => 'Tầng 4',
            'equipment' => 'TV',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors('name');
    }

    public function test_room_capacity_must_be_greater_than_zero(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->from('/dashboard')->post(route('rooms.store'), [
            'name' => 'Phòng Sức Chứa Sai',
            'capacity' => 0,
            'location' => 'Tầng 1',
            'equipment' => null,
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors('capacity');
        $this->assertDatabaseMissing('rooms', ['name' => 'Phòng Sức Chứa Sai']);
    }

    public function test_admin_can_update_room(): void
    {
        $admin = $this->makeAdmin();
        $room = $this->makeRoom();

        $response = $this->actingAs($admin)->put(route('rooms.update', $room), [
            'name' => 'Phòng A101 Updated',
            'capacity' => 70,
            'location' => 'Tầng 5',
            'equipment' => 'Máy chiếu, loa',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Phòng A101 Updated',
            'capacity' => 70,
        ]);
    }

    public function test_admin_can_delete_unused_room(): void
    {
        $admin = $this->makeAdmin();
        $room = $this->makeRoom(['name' => 'Phòng Có Thể Xóa']);

        $response = $this->actingAs($admin)->delete(route('rooms.destroy', $room));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    public function test_admin_cannot_delete_room_that_has_schedule(): void
    {
        $admin = $this->makeAdmin();
        $user = $this->makeUser([
            'username' => 'schedule_user',
            'email' => 'schedule_user@example.com',
        ]);
        $room = $this->makeRoom(['name' => 'Phòng Đang Có Lịch']);

        Schedule::create([
            'roomId' => $room->id,
            'userId' => $user->id,
            'startTime' => Carbon::tomorrow()->setTime(8, 0),
            'endTime' => Carbon::tomorrow()->setTime(10, 0),
            'purpose' => 'Lịch học kiểm thử',
        ]);

        $response = $this->actingAs($admin)->delete(route('rooms.destroy', $room));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }
}
