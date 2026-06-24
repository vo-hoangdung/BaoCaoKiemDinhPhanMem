# Classroom Management System Testing Documentation

## Tổng quan

Tài liệu này mô tả hệ thống kiểm thử cho web app **Quản lý phòng học / Classroom Management System** được xây dựng bằng Laravel, bao gồm:

- White-box Testing sử dụng PHPUnit / Laravel Feature Test (Đã sửa & Đạt 100%)
- Automated UI Testing sử dụng Selenium WebDriver (Đã sửa & Đạt 100%)
- Hướng dẫn cấu hình môi trường kiểm thử
- Danh sách chức năng đã được kiểm thử
- Các lỗi kỹ thuật đã được khắc phục để chạy test thành công

---

# Cấu trúc thư mục

```text
tests/
├── TestCase.php              <-- File cơ sở cho Laravel Feature test (đã được bổ sung)
├── Feature/
│   ├── AuthTest.php
│   ├── RoomManagementTest.php
│   ├── CourseManagementTest.php
│   └── BookingRequestTest.php
│
└── Selenium/
    ├── run_all_tests.py      <-- Script chạy toàn bộ test Selenium tự động
    ├── selenium_login.py
    ├── selenium_room.py
    ├── selenium_course.py    <-- File test giao diện Thêm môn học (đã được viết mới)
    └── selenium_booking_request.py
```

---

# 1. White-box Testing (PHPUnit)

## Mục tiêu

Bộ kiểm thử Feature Test được xây dựng bằng **PHPUnit tích hợp trong Laravel** nhằm đánh giá tính đúng đắn của logic backend, Routes, Controllers, Validation Rules, Phân quyền người dùng và Database.

### Các module đã chạy kiểm thử thành công:
- **`AuthTest.php`**: Xác thực đăng nhập thành công/thất bại, đăng xuất và kiểm tra phân quyền truy cập.
- **`RoomManagementTest.php`**: Admin tạo, sửa, xóa phòng học; kiểm tra quy tắc trùng tên, sức chứa và kiểm tra phân quyền user thường.
- **`CourseManagementTest.php`**: Quản lý môn học, số lượng tín chỉ, ràng buộc trùng mã môn và gán giảng viên.
- **`BookingRequestTest.php`**: Tạo yêu cầu đặt phòng, ngăn chặn trùng lịch đặt học, Admin duyệt/từ chối yêu cầu và tự tạo Lịch học (Schedules) tương ứng.

## Hướng dẫn chạy Feature Tests

1. **Chuẩn bị môi trường**:
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```
2. **Chạy toàn bộ Feature test**:
   ```bash
   php artisan test
   # Hoặc dùng PHPUnit trực tiếp:
   vendor/bin/phpunit
   ```

---

# 2. Automated UI Testing (Selenium)

## Mục tiêu

Kiểm thử giao diện thực tế của người dùng trên trình duyệt Google Chrome mô phỏng hành vi thật (nhập form, click nút, mở modal javascript và kiểm tra kết quả hiển thị).

### Các file test UI đã chạy thành công:
- **`selenium_login.py`**: Test giao diện đăng nhập thành công, thất bại và đăng xuất.
- **`selenium_room.py`**: Test luồng Admin thêm phòng học qua Modal UI và chuyển hướng.
- **`selenium_course.py`** *(Mới)*: Test luồng Admin thêm môn học mới qua Modal UI của tab Môn học.
- **`selenium_booking_request.py`**: Test Giáo viên chọn phòng, môn học để tạo yêu cầu đặt phòng, kiểm tra thông báo flash và hiển thị trạng thái chờ duyệt.

## Hướng dẫn chạy Selenium Tests

### Yêu cầu cài đặt thư viện:
```bash
pip install selenium
```

### Chạy kiểm thử:
Trước khi chạy, hãy chắc chắn Laravel server đang chạy trên cổng mặc định: `php artisan serve --port=8000`.

* **Cách 1: Chạy tất cả tự động (Headless - chạy ngầm)**:
  ```bash
  python tests/Selenium/run_all_tests.py --headless
  ```
* **Cách 2: Chạy trực tiếp hiển thị giao diện Chrome (Từng file)**:
  ```bash
  python tests/Selenium/run_all_tests.py
  # Hoặc chạy lẻ từng file:
  python tests/Selenium/selenium_login.py
  python tests/Selenium/selenium_room.py
  python tests/Selenium/selenium_course.py
  python tests/Selenium/selenium_booking_request.py
  ```

---

# 3. Các lỗi kỹ thuật đã được khắc phục (Bug Fixes)

Để bộ test suite chạy thành công 100%, các thay đổi sau đã được áp dụng:

### Backend & Feature Tests
1. **Bổ sung file [TestCase.php](file:///C:/Users/ngocd/Downloads/BaoCaoKiemDinhPhanMem_Fixed/tests/TestCase.php)**: Tái tạo lại file class cơ sở của Laravel Test bị thiếu để khắc phục lỗi hệ thống `Class "Tests\TestCase" not found`.
2. **Sửa định dạng thời gian của Controller**: Trong [BookingRequestController.php](file:///C:/Users/ngocd/Downloads/BaoCaoKiemDinhPhanMem_Fixed/BookingRequestController.php) (hàm `approve`), hàm gốc bắt buộc định dạng giờ phải có giây (`H:i:s`). Đã được cập nhật để tự động điền thêm giây `:00` nếu dữ liệu gửi lên là định dạng 5 ký tự `H:i` (ví dụ `"08:00"`), giúp test case duyệt phòng hoạt động đúng logic.

### Selenium UI Tests
1. **Lỗi trùng ID phần tử HTML**: Các modal "Tạo lịch học" và "Tạo yêu cầu đặt phòng" trên dashboard gốc của app đều dùng chung ID HTML (`roomId`, `startTime`, `endTime`, `courseId`). Đã sửa các file test Selenium bằng cách giới hạn tầm vực CSS selector (CSS Scoping) cụ thể vào ID của từng modal tương ứng (ví dụ `#addRequestModal #roomId`) để tránh Selenium điền nhầm thông tin vào modal đang ẩn.
2. **Cơ chế gọi Modal bằng Javascript**: Ứng dụng Laravel sử dụng mã JS tùy chỉnh `showModal('ModalId')` thay vì button standard. Các file test Selenium đã được cập nhật để gọi trực tiếp lệnh script `self.driver.execute_script("showModal('...')")`.
3. **Chờ tải lại trang (Page Reload Sync)**: Thay thế việc chờ tĩnh (`time.sleep`) bằng cách đợi trạng thái cũ biến mất `staleness_of` và kiểm tra sự xuất hiện của thông báo flash thành công / thông báo lỗi trên DOM thực tế để tăng tốc độ chạy test.
