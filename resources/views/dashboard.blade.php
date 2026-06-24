@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles.css') }}">
<div class="container">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error">
        @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
        @endforeach
    </div>
    @endif

    <div class="header">
        <h1>🏫 Hệ thống Quản lý Lớp học</h1>
        <div class="user-info">
            <span id="currentUser">{{ auth()->user()->fullName }}</span>
            <span id="userRole" class="user-role">{{ auth()->user()->isAdmin() ? 'Quản trị viên' : 'Người dùng' }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn">Đăng xuất</button>
            </form>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>{{ $totalRooms }}</h3>
            <p>📚 Tổng số phòng</p>
        </div>
        <div class="stat-card">
            <h3>{{ $totalBookings }}</h3>
            <p>📅 Lịch đặt phòng</p>
        </div>
        <div class="stat-card">
            <h3>{{ $totalCourses }}</h3>
            <p>🎓 Môn học</p>
        </div>
        <div class="stat-card">
            <h3>{{ $pendingRequests }}</h3>
            <p>⏳ Yêu cầu chờ duyệt</p>
        </div>
    </div>

    <div class="nav-tabs">
        <button class="nav-tab active" onclick="showTab('rooms')">🏠 Phòng học</button>
        <button class="nav-tab" onclick="showTab('schedules')">📅 Lịch đặt phòng</button>
        <button class="nav-tab" onclick="showTab('courses')">📚 Môn học</button>
        @if(auth()->user()->isAdmin())
        <button class="nav-tab" onclick="showTab('users')">👥 Người dùng</button>
        @endif
        <button class="nav-tab" onclick="showTab('requests')">📝 Yêu cầu đặt phòng</button>
    </div>

    <div class="tab-content">
        <!-- Rooms Tab -->
        <div id="rooms" class="tab-pane active">
            @if(auth()->user()->isAdmin())
            <div class="action-buttons">
                <button class="btn" onclick="showModal('addRoomModal')">➕ Thêm phòng học</button>
            </div>
            @endif
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Tên phòng</th>
                            <th>Sức chứa</th>
                            <th>Vị trí</th>
                            <th>Thiết bị</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rooms as $room)
                        <tr>
                            <td>{{ $room->name }}</td>
                            <td>{{ $room->capacity }}</td>
                            <td>{{ $room->location }}</td>
                            <td>{{ $room->equipment }}</td>
                            <td>
                                @if(auth()->user()->isAdmin())
                                <button class="btn"
                                    onclick="editRoom({{ $room->id }})"
                                    data-room-id="{{ $room->id }}"
                                    data-room-name="{{ $room->name }}"
                                    data-room-capacity="{{ $room->capacity }}"
                                    data-room-location="{{ $room->location }}"
                                    data-room-equipment="{{ $room->equipment }}">Sửa</button>
                                <form method="POST" action="{{ route('rooms.destroy', $room->id) }}" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa phòng này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Xóa</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Schedules Tab -->
        <div id="schedules" class="tab-pane">
            <div class="action-buttons">
                <button class="btn" onclick="showModal('addScheduleModal')">➕ Đặt phòng</button>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Phòng</th>
                            <th>Người đặt</th>
                            <th>Thời gian bắt đầu</th>
                            <th>Thời gian kết thúc</th>
                            <th>Mục đích</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->room->name ?? 'N/A' }}</td>
                            <td>{{ $schedule->user->fullName ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($schedule->startTime)->format('d/m/Y H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($schedule->endTime)->format('d/m/Y H:i') }}</td>
                            <td>{{ $schedule->purpose }}</td>
                            <td>
                                @if(auth()->user()->isAdmin() || auth()->user()->id === $schedule->userId)
                                <button class="btn"
                                    onclick="editSchedule({{ $schedule->id }})"
                                    data-schedule-id="{{ $schedule->id }}"
                                    data-schedule-room-id="{{ $schedule->roomId }}"
                                    data-schedule-start-time="{{ $schedule->startTime }}"
                                    data-schedule-end-time="{{ $schedule->endTime }}"
                                    data-schedule-purpose="{{ $schedule->purpose }}">Sửa</button>
                                <form method="POST" action="{{ route('schedules.destroy', $schedule->id) }}" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa lịch này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Xóa</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Courses Tab -->
        <div id="courses" class="tab-pane">
            @if(auth()->user()->isAdmin())
            <div class="action-buttons">
                <button class="btn" onclick="showModal('addCourseModal')">➕ Thêm môn học</button>
            </div>
            @endif
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Tên môn học</th>
                            <th>Mã môn</th>
                            <th>Số tín chỉ</th>
                            <th>Giảng viên</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courses as $course)
                        <tr>
                            <td>{{ $course->name }}</td>
                            <td>{{ $course->code }}</td>
                            <td>{{ $course->credits }}</td>
                            <td>{{ $course->instructor->fullName ?? 'N/A' }}</td>
                            <td>
                                @if(auth()->user()->isAdmin())
                                <button class="btn" onclick="editCourse({{ $course->id }})">Sửa</button>
                                <form method="POST" action="{{ route('courses.destroy', $course->id) }}" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa môn học này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Xóa</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Users Tab -->
        @if(auth()->user()->isAdmin())
        <div id="users" class="tab-pane">
            <div class="action-buttons">
                <button class="btn" onclick="showModal('addUserModal')">➕ Thêm người dùng</button>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Tên đăng nhập</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->fullName }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->isAdmin() ? 'Quản trị viên' : 'Người dùng' }}</td>
                            <td>
                                @if($user->id !== auth()->user()->id)
                                <button class="btn" onclick="editUser({{ $user->id }})">Sửa</button>
                                <form method="POST" action="{{ route('users.destroy', $user->id) }}" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Xóa</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Booking Requests Tab -->
        <div id="requests" class="tab-pane">
            <div class="action-buttons">
                <button class="btn" onclick="showModal('addRequestModal')">➕ Tạo yêu cầu</button>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Người yêu cầu</th>
                            <th>Phòng</th>
                            <th>Môn học</th>
                            <th>Thời gian yêu cầu</th>
                            <th>Thời gian kết thúc</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookingRequests as $request)
                        <tr>
                            <td>{{ $request->user->fullName ?? 'N/A' }}</td>
                            <td>{{ $request->room->name ?? 'N/A' }}</td>
                            <td>{{ $request->course->name ?? 'N/A' }}</td>
                            <td>{{ $request->requestDate->format('d/m/Y') }} {{ substr($request->startTime, 0, 5) }}</td>
                            <td>{{ $request->requestDate->format('d/m/Y') }} {{ substr($request->endTime, 0, 5) }}</td>
                            <td>
                                <span class="status-badge {{ $request->status === 'pending' ? 'status-pending' : ($request->status === 'approved' ? 'status-approved' : 'status-rejected') }}">
                                    {{ $request->status === 'pending' ? 'Chờ duyệt' : ($request->status === 'approved' ? 'Đã duyệt' : 'Từ chối') }}
                                </span>
                            </td>
                            <td>
                                @if(auth()->user()->isAdmin() && $request->status === 'pending')
                                <form method="POST" action="{{ route('requests.approve', $request->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success">Duyệt</button>
                                </form>
                                <form method="POST" action="{{ route('requests.reject', $request->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Từ chối</button>
                                </form>
                                @endif
                                @if(auth()->user()->id === $request->userId && $request->status === 'pending')
                                <button class="btn" onclick="editBookingRequest({{ $request->id }})">Sửa</button>
                                <form method="POST" action="{{ route('requests.destroy', $request->id) }}" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa yêu cầu này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Xóa</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Include -->
@include('modal.add-room')
@include('modal.add-schedule')
@include('modal.add-course')
@if(auth()->user()->isAdmin())
@include('modal.add-user')
@endif
@include('modal.add-booking-request')

<!-- Edit Modals -->
@include('modal.edit-room')
@include('modal.edit-schedule')
@include('modal.edit-course')
@if(auth()->user()->isAdmin())
@include('modal.edit-user')
@endif
@include('modal.edit-booking-request')

<script>
    function showTab(tabName) {
        // Hide all tab panes
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('active');
        });

        // Remove active class from all tabs
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Show selected tab
        document.getElementById(tabName).classList.add('active');
        event.target.classList.add('active');
    }

    function showModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Edit functions
    function editRoom(roomId) {
        // Get data from the button that was clicked
        const button = event.target;
        const roomName = button.getAttribute('data-room-name');
        const roomCapacity = button.getAttribute('data-room-capacity');
        const roomLocation = button.getAttribute('data-room-location');
        const roomEquipment = button.getAttribute('data-room-equipment');

        console.log('Edit room data:', {
            roomId,
            roomName,
            roomCapacity,
            roomLocation,
            roomEquipment
        });

        // Fill form fields
        const nameField = document.getElementById('edit_room_name');
        const capacityField = document.getElementById('edit_room_capacity');
        const locationField = document.getElementById('edit_room_location');
        const equipmentField = document.getElementById('edit_room_equipment');

        if (nameField) nameField.value = roomName || '';
        if (capacityField) capacityField.value = roomCapacity || '';
        if (locationField) locationField.value = roomLocation || '';
        if (equipmentField) equipmentField.value = roomEquipment || '';

        // Set form action
        const form = document.getElementById('editRoomForm');
        if (form) {
            form.action = `/rooms/${roomId}`;
        }

        showModal('editRoomModal');
    }

    function editSchedule(scheduleId) {
        // Get data from the button that was clicked
        const button = event.target;
        const roomId = button.getAttribute('data-schedule-room-id');
        const startTime = button.getAttribute('data-schedule-start-time');
        const endTime = button.getAttribute('data-schedule-end-time');
        const purpose = button.getAttribute('data-schedule-purpose');

        // Fill form fields
        const roomField = document.getElementById('edit_schedule_roomId');
        const startField = document.getElementById('edit_schedule_startTime');
        const endField = document.getElementById('edit_schedule_endTime');
        const purposeField = document.getElementById('edit_schedule_purpose');

        if (roomField) roomField.value = roomId || '';
        if (startField) startField.value = startTime ? startTime.slice(0, 16) : '';
        if (endField) endField.value = endTime ? endTime.slice(0, 16) : '';
        if (purposeField) purposeField.value = purpose || '';

        // Set form action
        const form = document.getElementById('editScheduleForm');
        if (form) {
            form.action = `/schedules/${scheduleId}`;
        }

        showModal('editScheduleModal');
    }

    function editCourse(courseId) {
        const courseData = @json($courses->keyBy('id'));
        const course = courseData[courseId];

        if (course) {
            document.getElementById('edit_course_name').value = course.name;
            document.getElementById('edit_course_code').value = course.code;
            document.getElementById('edit_course_description').value = course.description || '';
            document.getElementById('edit_course_credits').value = course.credits || '';
            document.getElementById('edit_course_instructorId').value = course.instructorId;

            document.getElementById('editCourseForm').action = `/courses/${courseId}`;
            showModal('editCourseModal');
        }
    }

    function editUser(userId) {
        const userData = @json($users->keyBy('id'));
        const user = userData[userId];

        if (user) {
            document.getElementById('edit_user_username').value = user.username;
            document.getElementById('edit_user_fullName').value = user.fullName;
            document.getElementById('edit_user_email').value = user.email;
            document.getElementById('edit_user_role').value = user.role;
            document.getElementById('edit_user_password').value = ''; // Clear password field

            document.getElementById('editUserForm').action = `/users/${userId}`;
            showModal('editUserModal');
        }
    }

    function editBookingRequest(requestId) {
        const requestData = @json($bookingRequests->keyBy('id'));
        const request = requestData[requestId];

        if (request) {
            document.getElementById('edit_request_roomId').value = request.roomId || request.room_id;
            document.getElementById('edit_request_courseId').value = request.courseId || request.course_id;

            // Format date correctly for input type="date" (Y-m-d format)
            let requestDate = request.requestDate || request.request_date;
            if (typeof requestDate === 'object' && requestDate.date) {
                requestDate = requestDate.date.substring(0, 10); // Extract YYYY-MM-DD from datetime
            } else if (typeof requestDate === 'string') {
                requestDate = requestDate.substring(0, 10); // Extract YYYY-MM-DD
            }
            document.getElementById('edit_request_requestDate').value = requestDate;

            // Format time correctly (H:i format, remove seconds if present)
            let startTime = request.startTime || request.start_time;
            let endTime = request.endTime || request.end_time;
            if (startTime && startTime.length > 5) startTime = startTime.substring(0, 5);
            if (endTime && endTime.length > 5) endTime = endTime.substring(0, 5);

            document.getElementById('edit_request_startTime').value = startTime;
            document.getElementById('edit_request_endTime').value = endTime;
            document.getElementById('edit_request_purpose').value = request.purpose;

            document.getElementById('editBookingRequestForm').action = `/booking-requests/${requestId}`;
            showModal('editBookingRequestModal');
        }
    }
</script>

<!-- Auto show modal on validation errors -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->any())
        // Check the current URL to determine which modal to show
        const currentUrl = window.location.href;
        const referrer = document.referrer;

        // Check if we came from a form submission based on old() values
        @if(old('username') || old('fullName'))
        showModal('addUserModal');
        @elseif(old('roomId') || old('courseId'))
        showModal('addRequestModal');
        @elseif(old('name') && old('code'))
        showModal('addCourseModal');
        @elseif(old('name') && old('capacity'))
        showModal('addRoomModal');
        @elseif(old('roomId') && old('purpose'))
        showModal('addScheduleModal');
        @endif
        @endif
    });
</script>
@endsection
