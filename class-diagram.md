# Sơ đồ lớp UML - Dự án Quản lý Lớp học

```mermaid
classDiagram
    %% Model Classes
    class User {
        +id: int
        +username: string
        +fullName: string
        +email: string
        +password: string
        +role: string
        +email_verified_at: datetime
        +created_at: datetime
        +updated_at: datetime
        +isAdmin(): bool
        +schedules(): HasMany
        +courses(): HasMany
        +bookingRequests(): HasMany
    }

    class Room {
        +id: int
        +name: string
        +capacity: int
        +location: string
        +equipment: string
        +created_at: datetime
        +updated_at: datetime
        +schedules(): HasMany
        +bookingRequests(): HasMany
    }

    class Course {
        +id: int
        +name: string
        +code: string
        +description: string
        +credits: int
        +instructorId: int
        +created_at: datetime
        +updated_at: datetime
        +instructor(): BelongsTo
        +bookingRequests(): HasMany
    }

    class Schedule {
        +id: int
        +roomId: int
        +userId: int
        +startTime: datetime
        +endTime: datetime
        +purpose: string
        +created_at: datetime
        +updated_at: datetime
        +room(): BelongsTo
        +user(): BelongsTo
    }

    class BookingRequest {
        +id: int
        +userId: int
        +roomId: int
        +courseId: int
        +requestDate: date
        +startTime: string
        +endTime: string
        +purpose: string
        +status: string
        +created_at: datetime
        +updated_at: datetime
        +user(): BelongsTo
        +room(): BelongsTo
        +course(): BelongsTo
    }

    %% Controller Classes
    class BookingRequestController {
        +__construct()
        +index(): View
        +store(Request): RedirectResponse
        +update(Request, BookingRequest): RedirectResponse
        +approve(BookingRequest): RedirectResponse
        +reject(BookingRequest): RedirectResponse
        +destroy(BookingRequest): RedirectResponse
    }

    class UserController {
        +index(): View
        +store(Request): RedirectResponse
        +update(Request, User): RedirectResponse
        +destroy(User): RedirectResponse
    }

    class RoomController {
        +index(): View
        +store(Request): RedirectResponse
        +update(Request, Room): RedirectResponse
        +destroy(Room): RedirectResponse
    }

    class CourseController {
        +index(): View
        +store(Request): RedirectResponse
        +update(Request, Course): RedirectResponse
        +destroy(Course): RedirectResponse
    }

    class LoginController {
        +showLoginForm(): View
        +login(Request): RedirectResponse
        +logout(): RedirectResponse
    }

    %% Middleware Classes
    class AdminMiddleware {
        +handle(Request, Closure): Response
    }

    %% Relationships - Model to Model
    User ||--o{ Schedule : "creates (1:N)"
    User ||--o{ Course : "instructs (1:N)"
    User ||--o{ BookingRequest : "makes (1:N)"
    
    Room ||--o{ Schedule : "hosts (1:N)"
    Room ||--o{ BookingRequest : "requested for (1:N)"
    
    Course ||--o{ BookingRequest : "used in (1:N)"
    Course }o--|| User : "taught by (N:1)"
    
    Schedule }o--|| Room : "in (N:1)"
    Schedule }o--|| User : "created by (N:1)"
    
    BookingRequest }o--|| User : "made by (N:1)"
    BookingRequest }o--|| Room : "for (N:1)"
    BookingRequest }o--|| Course : "involves (N:1)"

    %% Controller Dependencies
    BookingRequestController ..> BookingRequest : uses
    BookingRequestController ..> Room : uses
    BookingRequestController ..> User : uses
    BookingRequestController ..> Course : uses
    BookingRequestController ..> Schedule : uses
    
    UserController ..> User : uses
    RoomController ..> Room : uses
    CourseController ..> Course : uses
    CourseController ..> User : uses
    
    LoginController ..> User : authenticates

    %% Middleware Dependencies
    AdminMiddleware ..> User : checks role

    %% Laravel Framework Classes
    class Model {
        <<abstract>>
        +fillable: array
        +casts(): array
        +create(): Model
        +update(): bool
        +delete(): bool
    }

    class Authenticatable {
        <<abstract>>
        +username: string
        +password: string
        +remember_token: string
    }

    class Controller {
        <<abstract>>
        +middleware(): void
    }

    %% Inheritance
    User --|> Authenticatable
    Authenticatable --|> Model
    Room --|> Model
    Course --|> Model
    Schedule --|> Model
    BookingRequest --|> Model

    BookingRequestController --|> Controller
    UserController --|> Controller
    RoomController --|> Controller
    CourseController --|> Controller
    LoginController --|> Controller

    %% Notes
    note for User "Role: 'admin' or 'user'\nAdmin can manage all data\nUser can only manage own data"
    note for BookingRequest "Status: 'pending', 'approved', 'rejected'\nWhen approved, creates Schedule"
    note for Schedule "Created from approved BookingRequest\nor manually by admin"
```

## Mô tả Sơ đồ Lớp

### **Models (Entities)**

1. **User** - Người dùng hệ thống
   - Admin: quản lý toàn bộ hệ thống
   - User: tạo yêu cầu đặt phòng, xem lịch của mình

2. **Room** - Phòng học
   - Chứa thông tin về phòng: tên, sức chứa, vị trí, thiết bị

3. **Course** - Môn học
   - Có giảng viên (User), được sử dụng trong BookingRequest

4. **Schedule** - Lịch sử dụng phòng
   - Được tạo từ BookingRequest đã được duyệt hoặc tạo thủ công

5. **BookingRequest** - Yêu cầu đặt phòng
   - Trạng thái: pending → approved/rejected
   - Khi approved → tạo Schedule

### **Controllers**

- **BookingRequestController**: CRUD yêu cầu đặt phòng, duyệt/từ chối
- **UserController**: CRUD người dùng
- **RoomController**: CRUD phòng học  
- **CourseController**: CRUD môn học
- **LoginController**: Xác thực đăng nhập

### **Middleware**

- **AdminMiddleware**: Kiểm tra quyền admin cho các route nhạy cảm

### **Quan hệ chính**

1. **User → Schedule** (1:N): User tạo nhiều lịch
2. **User → Course** (1:N): User dạy nhiều môn học
3. **User → BookingRequest** (1:N): User tạo nhiều yêu cầu
4. **Room → Schedule** (1:N): Room có nhiều lịch sử dụng
5. **Room → BookingRequest** (1:N): Room có nhiều yêu cầu đặt
6. **Course → BookingRequest** (1:N): Course được dùng trong nhiều yêu cầu

### **Workflow**

1. User tạo BookingRequest cho Room + Course
2. Admin duyệt/từ chối BookingRequest
3. Nếu approved → tạo Schedule tự động
4. Schedule hiển thị lịch sử dụng phòng thực tế
