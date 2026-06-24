# Classroom Management System Testing Documentation

## Tổng quan

Tài liệu này mô tả hệ thống kiểm thử cho web app **Quản lý phòng học / Classroom Management System** được xây dựng bằng Laravel, bao gồm:

- White-box Testing sử dụng PHPUnit / Laravel Feature Test
- Automated UI Testing sử dụng Selenium WebDriver
- Hướng dẫn cấu hình môi trường kiểm thử
- Danh sách chức năng đã được kiểm thử
- Các hạn chế và chức năng chưa tự động hóa

---

# Cấu trúc thư mục

```text
tests/
├── Feature/
│   ├── AuthTest.php
│   ├── RoomManagementTest.php
│   ├── CourseManagementTest.php
│   └── BookingRequestTest.php
│
└── Selenium/
    ├── selenium_login.py
    ├── selenium_room.py
    └── selenium_booking_request.py
```

---

# 1. White-box Testing

## Mục tiêu

Bộ kiểm thử White-box được xây dựng bằng **PHPUnit tích hợp trong Laravel** nhằm đánh giá tính đúng đắn của:

- Routes
- Controllers
- Models
- Validation Rules
- Business Logic
- Authentication & Authorization
- Permission Control
- Database Processing

## Tiêu chí bao phủ mã nguồn

| Tiêu chí | Mô tả |
|---|---|
| Statement Coverage | Mỗi câu lệnh quan trọng được thực thi ít nhất một lần |
| Branch Coverage | Các nhánh điều kiện đúng/sai được kiểm tra |
| Condition Coverage | Các điều kiện validation được kiểm tra với dữ liệu hợp lệ và không hợp lệ |
| Permission Coverage | Kiểm tra quyền truy cập giữa admin và user thường |

---

## AuthTest.php

Kiểm thử các chức năng xác thực người dùng.

### Nội dung kiểm thử

- Trang đăng nhập hiển thị thành công
- Admin đăng nhập thành công
- User đăng nhập thành công
- Đăng nhập thất bại khi sai mật khẩu
- Đăng nhập thất bại khi thiếu username/password
- Người dùng đã đăng nhập có thể đăng xuất
- Người chưa đăng nhập không được truy cập dashboard

### Mục tiêu kiểm thử

Đảm bảo chức năng xác thực hoạt động chính xác, chỉ cho phép người dùng hợp lệ truy cập vào hệ thống.

---

## RoomManagementTest.php

Kiểm thử chức năng quản lý phòng học.

### Nội dung kiểm thử

- Admin có thể thêm phòng học mới
- User thường không được thêm phòng học
- Không cho phép thêm phòng trùng tên
- Không cho phép sức chứa phòng nhỏ hơn 1
- Admin có thể cập nhật thông tin phòng
- Admin có thể xóa phòng khi phòng chưa có lịch sử dụng
- Không cho phép xóa phòng đã có lịch hoặc yêu cầu đặt phòng liên quan

### Mục tiêu kiểm thử

Đảm bảo dữ liệu phòng học được quản lý đúng, tránh trùng lặp và đảm bảo phân quyền giữa admin và user.

---

## CourseManagementTest.php

Kiểm thử chức năng quản lý môn học.

### Nội dung kiểm thử

- Admin có thể thêm môn học mới
- User thường không được thêm môn học
- Không cho phép mã môn học bị trùng
- Không cho phép số tín chỉ nhỏ hơn 1 hoặc lớn hơn 10
- Admin có thể cập nhật thông tin môn học
- Admin có thể xóa môn học khi môn chưa được sử dụng

### Mục tiêu kiểm thử

Đảm bảo thông tin môn học được thêm, sửa, xóa đúng quy tắc và không làm sai lệch dữ liệu hệ thống.

---

## BookingRequestTest.php

Kiểm thử chức năng yêu cầu đặt phòng.

### Nội dung kiểm thử

- User có thể gửi yêu cầu đặt phòng
- Không cho phép gửi yêu cầu với ngày trong quá khứ
- Không cho phép thời gian kết thúc nhỏ hơn hoặc bằng thời gian bắt đầu
- Không cho phép đặt phòng bị trùng lịch
- Admin có thể duyệt yêu cầu đặt phòng
- Khi duyệt yêu cầu, hệ thống tạo lịch học tương ứng
- Admin có thể từ chối yêu cầu đặt phòng
- User không được tự duyệt hoặc từ chối yêu cầu

### Mục tiêu kiểm thử

Đảm bảo quy trình đặt phòng hoạt động đúng, hạn chế xung đột lịch và đảm bảo quyền xử lý yêu cầu thuộc về admin.

---

# Chạy White-box Tests

## Chuẩn bị môi trường

Cài đặt thư viện PHP bằng Composer:

```bash
composer install
```

Tạo file môi trường:

```bash
cp .env.example .env
php artisan key:generate
```

Chạy migration:

```bash
php artisan migrate
```

Nếu muốn chạy test trên database riêng, tạo file `.env.testing`:

```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_STORE=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
```

## Chạy toàn bộ test

```bash
php artisan test
```

## Chạy từng module

```bash
php artisan test --filter=AuthTest
php artisan test --filter=RoomManagementTest
php artisan test --filter=CourseManagementTest
php artisan test --filter=BookingRequestTest
```

---

## Báo cáo Coverage

Laravel có thể xuất báo cáo coverage nếu máy đã cài **Xdebug** hoặc **PCOV**.

Chạy coverage trên terminal:

```bash
php artisan test --coverage
```

Xuất báo cáo HTML:

```bash
php artisan test --coverage-html reports/coverage
```

Báo cáo HTML sẽ được tạo trong thư mục:

```text
reports/coverage/
```

Lưu ý: Nếu máy báo lỗi chưa có coverage driver, cần bật Xdebug hoặc cài PCOV trước khi chạy lệnh coverage.

---

# 2. Selenium Testing

## Mục tiêu

Kiểm thử giao diện và luồng thao tác thực tế của người dùng trên trình duyệt.

Selenium Testing tập trung vào việc mô phỏng hành vi người dùng thật:

- Mở trình duyệt
- Truy cập website
- Nhập dữ liệu vào form
- Bấm nút chức năng
- Kiểm tra kết quả hiển thị trên giao diện

## Công nghệ sử dụng

- Selenium WebDriver
- Python
- Google Chrome
- ChromeDriver / Selenium Manager
- Laravel Development Server

---

## Cài đặt Selenium

Cài Python package:

```bash
pip install selenium
```

Kiểm tra Google Chrome đã được cài trên máy.

Với Selenium phiên bản mới, Selenium Manager có thể tự xử lý driver. Nếu không chạy được, cần tải ChromeDriver phù hợp với phiên bản Chrome đang sử dụng.

---

## Chuẩn bị môi trường

Khởi động server Laravel:

```bash
php artisan serve
```

Mặc định server chạy tại:

```text
http://127.0.0.1:8000
```

Cập nhật thông tin đăng nhập trong các file Selenium nếu tài khoản mẫu khác với database hiện tại:

```python
ADMIN_USERNAME = "admin"
ADMIN_PASSWORD = "password"
USER_USERNAME = "giaovien1"
USER_PASSWORD = "password"
```

---

## Các bộ kiểm thử Selenium

## selenium_login.py

Kiểm thử giao diện đăng nhập.

### Nội dung kiểm thử

- Mở trang login
- Nhập đúng username/password
- Bấm nút đăng nhập
- Kiểm tra chuyển hướng vào dashboard
- Đăng nhập sai mật khẩu
- Kiểm tra thông báo lỗi hoặc không vào được dashboard

---

## selenium_room.py

Kiểm thử giao diện quản lý phòng học.

### Nội dung kiểm thử

- Đăng nhập bằng tài khoản admin
- Mở trang danh sách phòng học
- Bấm nút thêm phòng
- Nhập tên phòng, sức chứa, loại phòng, trạng thái
- Bấm lưu
- Kiểm tra phòng mới xuất hiện trên giao diện

---

## selenium_booking_request.py

Kiểm thử giao diện yêu cầu đặt phòng.

### Nội dung kiểm thử

- Đăng nhập bằng tài khoản user
- Mở trang yêu cầu đặt phòng
- Tạo yêu cầu đặt phòng mới
- Kiểm tra yêu cầu hiển thị trên danh sách
- Đăng nhập bằng tài khoản admin
- Duyệt hoặc từ chối yêu cầu
- Kiểm tra trạng thái yêu cầu đã thay đổi

---

# Chạy Selenium Tests

Chạy từng file Selenium:

```bash
python tests/Selenium/selenium_login.py
python tests/Selenium/selenium_room.py
python tests/Selenium/selenium_booking_request.py
```

Nếu muốn chạy bằng Python module:

```bash
python -m tests.Selenium.selenium_login
python -m tests.Selenium.selenium_room
python -m tests.Selenium.selenium_booking_request
```

---

# Chức năng đã được kiểm thử

Các nhóm chức năng chính đã được bao phủ:

- Authentication & Authorization
- Login / Logout
- Room Management
- Course Management
- Booking Request Management
- Request Approval / Rejection
- Validation dữ liệu đầu vào
- Permission Control
- Database Processing
- UI Form Interaction

---

# Các chức năng chưa tự động hóa

| Chức năng | Nguyên nhân |
|---|---|
| Kiểm thử hiệu năng nhiều người dùng | Cần dùng thêm JMeter hoặc công cụ load testing |
| Kiểm thử bảo mật chuyên sâu | Cần công cụ như OWASP ZAP hoặc Burp Suite |
| Kiểm thử giao diện responsive trên nhiều thiết bị | Cần cấu hình nhiều kích thước màn hình hoặc BrowserStack |
| Kiểm thử export file nếu có | Cần kiểm tra nội dung file sinh ra |
| Kiểm thử email/thông báo nếu có | Phụ thuộc cấu hình mail server |

---

# Lưu ý kỹ thuật

## Database

Môi trường phát triển có thể sử dụng MySQL.

Môi trường test nên sử dụng SQLite hoặc database test riêng để:

- Tăng tốc độ thực thi
- Không ảnh hưởng dữ liệu thật
- Dễ reset dữ liệu sau mỗi lần chạy test
- Phù hợp khi chạy CI/CD

## RefreshDatabase

Các test Laravel có thể dùng trait `RefreshDatabase` để tự động reset dữ liệu test.

Không nên chạy test trực tiếp trên database thật có dữ liệu quan trọng vì dữ liệu có thể bị xóa hoặc thay đổi trong quá trình kiểm thử.

## Selenium

Selenium cần server Laravel đang chạy trước khi thực thi test.

Nếu giao diện thay đổi class, id hoặc text button, cần cập nhật lại selector trong các file Selenium.

Có thể chạy Chrome ở chế độ headless bằng cách thêm:

```python
options.add_argument("--headless")
```

---

# Kết luận

Dự án sử dụng kết hợp **PHPUnit/Laravel Feature Test** và **Selenium WebDriver** để kiểm thử hệ thống quản lý phòng học.

- PHPUnit được dùng cho kiểm thử White-box, tập trung vào logic bên trong hệ thống như route, controller, validation, phân quyền và database.
- Selenium được dùng cho kiểm thử giao diện, mô phỏng thao tác thực tế của người dùng trên trình duyệt.

Cách kết hợp này giúp hệ thống được kiểm thử cả về mặt logic xử lý bên trong và trải nghiệm thao tác bên ngoài.
