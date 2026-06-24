Tài liệu này mô tả toàn bộ hệ thống kiểm thử của **Classroom Management System**, bao gồm:

* White-box Testing sử dụng PHPUnit / Laravel Feature Test
* Automated UI Testing sử dụng Selenium WebDriver
* Hướng dẫn cấu hình môi trường kiểm thử
* Danh sách chức năng đã được kiểm thử
* Các hạn chế và chức năng chưa thể tự động hóa

---

```text
tests/
├── TestCase.php
├── Feature/
│   ├── AuthTest.php
│   ├── RoomManagementTest.php
│   ├── CourseManagementTest.php
│   └── BookingRequestTest.php
│
├── Selenium/
│   ├── run_all_tests.py
│   ├── selenium_login.py
│   ├── selenium_room.py
│   ├── selenium_course.py
│   └── selenium_booking_request.py
│ 
└── README.md
```

---

Bộ kiểm thử White-box được xây dựng bằng PHPUnit nhằm đánh giá tính đúng đắn của:

* Models
* Controllers
* Business Logic
* Validation Rules
* Permission Control

| Tiêu chí           | Mô tả                                                |
| ------------------ | ---------------------------------------------------- |
| Statement Coverage | Mỗi câu lệnh được thực thi ít nhất một lần           |
| Branch Coverage    | Mọi nhánh điều kiện được kiểm tra                    |
| Condition Coverage | Mọi biểu thức Boolean được đánh giá cả True và False |

---

Kiểm thử các chức năng xác thực người dùng:

* login_view
* logout_view
* user_login_view

* Đăng nhập thành công
* Sai mật khẩu
* Thiếu thông tin đăng nhập
* Đăng xuất thành công
* Bảo vệ đường dẫn (Chưa đăng nhập không truy cập được dashboard)

---

Kiểm thử chức năng quản lý phòng học.

* Tạo phòng học
* Cập nhật thông tin phòng học
* Xóa phòng học
* Phân quyền quản trị viên (Admin được chỉnh sửa, User thường không có quyền)
* Ràng buộc trùng tên phòng
* Ràng buộc sức chứa hợp lệ

---

Kiểm thử quản lý môn học.

* Tạo môn học
* Cập nhật thông tin môn học
* Xóa môn học
* Phân quyền quản trị viên (Admin được chỉnh sửa, User thường không có quyền)
* Kiểm tra mã môn học duy nhất
* Ràng buộc số lượng tín chỉ hợp lệ

---

Kiểm thử nghiệp vụ đặt phòng học.

* Tạo yêu cầu đặt phòng
* Phân quyền phê duyệt yêu cầu đặt phòng (Chỉ Admin được duyệt)
* Phê duyệt yêu cầu và tự động sinh Lịch học (Schedules) tương ứng
* Từ chối yêu cầu đặt phòng
* Ngăn chặn đặt trùng phòng và thời gian (Conflict schedules)
* Ràng buộc thời gian kết thúc phải sau thời gian bắt đầu
* Ngăn đặt phòng ở thời điểm quá khứ

---

## Tạo cấu hình SQLite cho môi trường test

Các tham số cấu hình SQLite in-memory đã được định nghĩa trong thẻ `<php>` của file `phpunit.xml`:

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="CACHE_STORE" value="array"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
</php>
```

## Chạy toàn bộ test
```bash
vendor/bin/phpunit
```

## Chạy từng module
```bash
vendor/bin/phpunit tests/Feature/AuthTest.php

vendor/bin/phpunit tests/Feature/RoomManagementTest.php

vendor/bin/phpunit tests/Feature/CourseManagementTest.php

vendor/bin/phpunit tests/Feature/BookingRequestTest.php
```

---

## Báo cáo Coverage
Cài đặt Xdebug hoặc PCOV trên máy của bạn, sau đó chạy:

```bash
php artisan test --coverage
```

Báo cáo HTML sẽ được tạo bằng lệnh:

```bash
php artisan test --coverage-html reports/coverage
```

Báo cáo HTML sẽ được tạo trong thư mục:

```text
reports/coverage/
```

---

Kiểm thử giao diện và luồng thao tác thực tế của người dùng trên trình duyệt.

* Selenium WebDriver
* Google Chrome
* ChromeDriver

---

## Cài đặt
```bash
pip install selenium
```

Với các phiên bản Selenium mới, driver Chrome sẽ tự động được tải về qua Selenium Manager.

---

## Chuẩn bị môi trường
Khởi động server Laravel:

```bash
php artisan serve
```

Tạo tài khoản thử nghiệm bằng seeder:

```bash
php artisan db:seed --class=DatabaseDataSeeder
```

Cập nhật thông tin đăng nhập mẫu trong các file Selenium:

```python
ADMIN_USERNAME = "admin"
ADMIN_PASSWORD = "password"
USER_USERNAME = "giaovien1"
USER_PASSWORD = "password"
```

---

### selenium_login.py
* Đăng nhập thành công với admin
* Đăng nhập thất bại khi sai mật khẩu
* Đăng xuất thành công

### selenium_room.py
* Đăng nhập admin
* Xem danh sách phòng học
* Mở modal thêm phòng bằng Javascript
* Điền thông tin và tạo thành công phòng học mới

### selenium_course.py
* Đăng nhập admin
* Chuyển sang tab Môn học
* Mở modal thêm môn học bằng Javascript
* Điền thông tin và tạo thành công môn học mới

### selenium_booking_request.py
* Đăng nhập user (giáo viên)
* Chuyển sang tab Yêu cầu đặt phòng
* Tạo yêu cầu đặt phòng mới
* Kiểm tra thông báo flash và trạng thái chờ duyệt trên giao diện

---

Chạy tất cả kiểm thử Selenium (ở chế độ headless):

```bash
python tests/Selenium/run_all_tests.py --headless
```

Chạy tất cả kiểm thử hiển thị giao diện:

```bash
python tests/Selenium/run_all_tests.py
```

Chạy lẻ từng kiểm thử:

```bash
python tests/Selenium/selenium_login.py
python tests/Selenium/selenium_room.py
python tests/Selenium/selenium_course.py
python tests/Selenium/selenium_booking_request.py
```

---

# Chức năng đã được kiểm thử
Các nhóm chức năng chính đã được bao phủ:

* Authentication & Authorization
* Room Management
* Course Management
* Booking Request Management
* Schedule Processing
* Search & Filtering
* Permission Control

---

# Các chức năng chưa tự động hóa
| Chức năng              | Nguyên nhân                       |
| ---------------------- | --------------------------------- |
| Xuất lịch học ra file Excel (nếu có) | Cần kiểm tra dữ liệu file sinh ra |
| Hiển thị lịch trên thời khóa biểu trực quan | Yêu cầu logic render đồ họa phức tạp |
| Kiểm thử hiệu năng hệ thống đặt lịch đồng thời | Cần thêm công cụ kiểm thử tải chuyên dụng như JMeter |

---

## Database
Môi trường Production sử dụng MySQL hoặc PostgreSQL.

Môi trường Test sử dụng SQLite In-Memory để:

* Tăng tốc độ thực thi
* Không ảnh hưởng dữ liệu thật
* Dễ triển khai CI/CD

## ChromeDriver
ChromeDriver phải tương thích với phiên bản Google Chrome hiện tại.

Có thể chạy chế độ headless bằng cách thêm tham số `--headless` khi chạy lệnh wrapper `run_all_tests.py`.
