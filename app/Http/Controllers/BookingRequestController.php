<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\Room;
use App\Models\User;
use App\Models\Course;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookingRequestController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin')->only(['approve', 'reject']);
    }

    /**
     * Display a listing of the booking requests and other data for the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $bookingRequests = Auth::user()->isAdmin()
            ? BookingRequest::with(['user', 'room', 'course'])->get()
            : BookingRequest::with(['user', 'room', 'course'])->where('userId', Auth::id())->get();

        $rooms = Room::all();
        $schedules = Auth::user()->isAdmin()
            ? Schedule::with(['room', 'user'])->get()
            : Schedule::with(['room', 'user'])->where('userId', Auth::id())->get();
        $courses = Course::with('instructor')->get();
        $users = Auth::user()->isAdmin() ? User::all() : collect([Auth::user()]);

        // Calculate stats
        $totalRooms = $rooms->count();
        $totalBookings = $schedules->count();
        $totalCourses = $courses->count();
        $pendingRequests = $bookingRequests->where('status', 'pending')->count();

        return view('dashboard', compact(
            'bookingRequests',
            'rooms',
            'schedules',
            'courses',
            'users',
            'totalRooms',
            'totalBookings',
            'totalCourses',
            'pendingRequests'
        ));
    }

    /**
     * Store a newly created booking request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Debug: Log request data
        Log::info('BookingRequest store called', [
            'all_data' => $request->all(),
            'only_expected' => $request->only(['roomId', 'courseId', 'requestDate', 'startTime', 'endTime', 'purpose'])
        ]);

        $request->validate([
            'roomId' => ['required', 'exists:rooms,id'],
            'courseId' => ['required', 'exists:courses,id'],
            'requestDate' => ['required', 'date', 'after_or_equal:today'],
            'startTime' => ['required', 'date_format:H:i'],
            'endTime' => ['required', 'date_format:H:i'],
            'purpose' => ['required', 'string', 'max:1000'],
        ], [
            'requestDate.after_or_equal' => 'Ngày yêu cầu phải từ hôm nay trở đi.',
            'startTime.date_format' => 'Thời gian bắt đầu không đúng định dạng.',
            'endTime.date_format' => 'Thời gian kết thúc không đúng định dạng.',
        ]);

        // Custom validation for time comparison
        if ($request->startTime >= $request->endTime) {
            return redirect()->back()
                ->withErrors(['endTime' => 'Thời gian kết thúc phải sau thời gian bắt đầu.'])
                ->withInput();
        }

        // Combine date and time for datetime comparison using Carbon
        try {
            $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->requestDate . ' ' . $request->startTime);
            $endDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->requestDate . ' ' . $request->endTime);
        } catch (\Exception $e) {
            Log::error('Error parsing datetime', [
                'requestDate' => $request->requestDate,
                'startTime' => $request->startTime,
                'endTime' => $request->endTime,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->withErrors(['time' => 'Lỗi xử lý thời gian. Vui lòng thử lại.'])->withInput();
        }

        // Check for scheduling conflicts
        $conflict = Schedule::where('roomId', $request->roomId)
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('startTime', [$startDateTime, $endDateTime])
                    ->orWhereBetween('endTime', [$startDateTime, $endDateTime])
                    ->orWhere(function ($query) use ($startDateTime, $endDateTime) {
                        $query->where('startTime', '<=', $startDateTime)
                            ->where('endTime', '>=', $endDateTime);
                    });
            })
            ->exists();

        if ($conflict) {
            return redirect()->back()->withErrors(['conflict' => 'Phòng đã được đặt trong khoảng thời gian này!'])->withInput();
        }

        $bookingData = [
            'userId' => Auth::id(),
            'roomId' => $request->roomId,
            'courseId' => $request->courseId,
            'requestDate' => $request->requestDate,
            'startTime' => $request->startTime, // This will be saved as time string like "09:00"
            'endTime' => $request->endTime,     // This will be saved as time string like "11:00"
            'purpose' => $request->purpose,
            'status' => 'pending',
        ];

        Log::info('Creating BookingRequest with data:', $bookingData);

        $bookingRequest = BookingRequest::create($bookingData);

        Log::info('BookingRequest created successfully:', ['id' => $bookingRequest->id]);

        return redirect()->route('dashboard')->with('success', 'Tạo yêu cầu đặt phòng thành công!');
    }

    /**
     * Update the specified booking request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BookingRequest  $bookingRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, BookingRequest $bookingRequest)
    {
        if (!Auth::user()->isAdmin() && $bookingRequest->userId !== Auth::id()) {
            return redirect()->route('dashboard')->with('error', 'Bạn không có quyền sửa yêu cầu này!');
        }

        if ($bookingRequest->status !== 'pending') {
            return redirect()->route('dashboard')->with('error', 'Không thể sửa yêu cầu đã được xử lý!');
        }

        $request->validate([
            'roomId' => ['required', 'exists:rooms,id'],
            'courseId' => ['required', 'exists:courses,id'],
            'requestDate' => ['required', 'date', 'after_or_equal:today'],
            'startTime' => ['required', 'date_format:H:i'],
            'endTime' => ['required', 'date_format:H:i'],
            'purpose' => ['required', 'string', 'max:1000'],
        ], [
            'requestDate.after_or_equal' => 'Ngày yêu cầu phải từ hôm nay trở đi.',
            'startTime.date_format' => 'Thời gian bắt đầu không đúng định dạng.',
            'endTime.date_format' => 'Thời gian kết thúc không đúng định dạng.',
        ]);

        // Custom validation for time comparison
        if ($request->startTime >= $request->endTime) {
            return redirect()->back()
                ->withErrors(['endTime' => 'Thời gian kết thúc phải sau thời gian bắt đầu.'])
                ->withInput();
        }

        // Combine date and time for datetime comparison using Carbon
        try {
            $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->requestDate . ' ' . $request->startTime);
            $endDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->requestDate . ' ' . $request->endTime);
        } catch (\Exception $e) {
            Log::error('Error parsing datetime in update method', [
                'requestDate' => $request->requestDate,
                'startTime' => $request->startTime,
                'endTime' => $request->endTime,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->withErrors(['time' => 'Lỗi xử lý thời gian. Vui lòng thử lại.'])->withInput();
        }

        // Check for scheduling conflicts (excluding current booking request)
        $conflict = Schedule::where('roomId', $request->roomId)
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('startTime', [$startDateTime, $endDateTime])
                    ->orWhereBetween('endTime', [$startDateTime, $endDateTime])
                    ->orWhere(function ($query) use ($startDateTime, $endDateTime) {
                        $query->where('startTime', '<=', $startDateTime)
                            ->where('endTime', '>=', $endDateTime);
                    });
            })
            ->exists();

        if ($conflict) {
            return redirect()->back()->withErrors(['conflict' => 'Phòng đã được đặt trong khoảng thời gian này!'])->withInput();
        }

        $bookingRequest->update([
            'roomId' => $request->roomId,
            'courseId' => $request->courseId,
            'requestDate' => $request->requestDate,
            'startTime' => $request->startTime,
            'endTime' => $request->endTime,
            'purpose' => $request->purpose,
        ]);

        return redirect()->route('dashboard')->with('success', 'Cập nhật yêu cầu đặt phòng thành công!');
    }

    /**
     * Approve a booking request and create a schedule.
     *
     * @param  \App\Models\BookingRequest  $bookingRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(BookingRequest $bookingRequest)
    {
        if ($bookingRequest->status !== 'pending') {
            return redirect()->route('dashboard')->with('error', 'Yêu cầu này đã được xử lý!');
        }

        // Combine date and time for start and end datetime
        // Convert requestDate (Carbon date) to string format first
        $requestDateString = $bookingRequest->requestDate->format('Y-m-d');

        try {
            $startDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $requestDateString . ' ' . $bookingRequest->startTime);
            $endDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $requestDateString . ' ' . $bookingRequest->endTime);
        } catch (\Exception $e) {
            Log::error('Error parsing datetime in approve method', [
                'requestDate' => $requestDateString,
                'startTime' => $bookingRequest->startTime,
                'endTime' => $bookingRequest->endTime,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('dashboard')->with('error', 'Lỗi xử lý thời gian. Vui lòng thử lại.');
        }

        // Check for scheduling conflicts
        $conflict = Schedule::where('roomId', $bookingRequest->roomId)
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('startTime', [$startDateTime, $endDateTime])
                    ->orWhereBetween('endTime', [$startDateTime, $endDateTime])
                    ->orWhere(function ($query) use ($startDateTime, $endDateTime) {
                        $query->where('startTime', '<=', $startDateTime)
                            ->where('endTime', '>=', $endDateTime);
                    });
            })
            ->exists();

        if ($conflict) {
            return redirect()->route('dashboard')->with('error', 'Phòng đã được đặt trong khoảng thời gian này!');
        }

        // Create a schedule
        Schedule::create([
            'roomId' => $bookingRequest->roomId,
            'userId' => $bookingRequest->userId,
            'startTime' => $startDateTime,
            'endTime' => $endDateTime,
            'purpose' => 'Đặt phòng cho môn học ' . $bookingRequest->course->name,
        ]);

        // Update booking request status
        $bookingRequest->update(['status' => 'approved']);

        return redirect()->route('dashboard')->with('success', 'Đã duyệt yêu cầu đặt phòng!');
    }

    /**
     * Reject a booking request.
     *
     * @param  \App\Models\BookingRequest  $bookingRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(BookingRequest $bookingRequest)
    {
        if ($bookingRequest->status !== 'pending') {
            return redirect()->route('dashboard')->with('error', 'Yêu cầu này đã được xử lý!');
        }

        $bookingRequest->update(['status' => 'rejected']);

        return redirect()->route('dashboard')->with('success', 'Đã từ chối yêu cầu đặt phòng!');
    }

    /**
     * Remove the specified booking request from storage.
     *
     * @param  \App\Models\BookingRequest  $bookingRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(BookingRequest $bookingRequest)
    {
        if (!Auth::user()->isAdmin() && $bookingRequest->userId !== Auth::id()) {
            return redirect()->route('dashboard')->with('error', 'Bạn không có quyền xóa yêu cầu này!');
        }

        $bookingRequest->delete();
        return redirect()->route('dashboard')->with('success', 'Xóa yêu cầu đặt phòng thành công!');
    }
}
