# Hệ thống kiểm thử - Classroom Management

## 1. Phạm vi

Bộ kiểm thử bao phủ các nghiệp vụ chính:

- Xác thực: đăng nhập, đăng xuất, bảo vệ dashboard.
- Phòng học: thêm, sửa, xóa, validation và ràng buộc dữ liệu.
- Môn học: CRUD, mã môn duy nhất, giới hạn tín chỉ.
- Lịch đặt phòng: tạo lịch, kiểm tra thời gian, phát hiện xung đột, phân quyền chủ sở hữu.
- Người dùng: CRUD, username/email duy nhất, xác nhận mật khẩu, bảo vệ tài khoản hiện tại.
- Yêu cầu đặt phòng: tạo, sửa, duyệt, từ chối, kiểm tra xung đột và phân quyền.
- Quan hệ model Eloquent.
- Giao diện trình duyệt bằng Selenium WebDriver.
- HTTP/load test nhẹ bằng Apache JMeter.

## 2. Cấu trúc

```text
tests/
├── Feature/
│   ├── AuthenticationTest.php
│   ├── BookingRequestManagementTest.php
│   ├── CourseManagementTest.php
│   ├── RoomManagementTest.php
│   ├── ScheduleManagementTest.php
│   └── UserManagementTest.php
├── Unit/
│   └── ModelRelationshipTest.php
└── Support/
    └── CreatesClassroomData.php

selenium-junit-tests/
├── src/test/java/vn/edu/classroom/
├── TEST-PLAN.md
└── TEST-REPORT.md

jmeter/
├── classroom-management-test-plan.jmx
├── results/
└── report/
```

## 3. White-box và feature test

Chạy:

```powershell
php artisan test
```

Kết quả xác nhận ngày 24/06/2026:

```text
Tests: 50 passed (117 assertions)
```

### Ma trận bao phủ

| Nhóm | Trường hợp chính |
|---|---|
| Authentication | Trang login, đúng/sai mật khẩu, thiếu dữ liệu, guest redirect, logout |
| Room | Create/update/delete, tên trùng, capacity=0, phòng đang được sử dụng, quyền admin |
| Course | Create/update/delete, code trùng, credits ngoài miền, course có booking request |
| Schedule | Tạo/sửa/xóa, end trước start, trùng phòng, chủ sở hữu và người khác |
| User | Create/update/delete, trùng username/email, password confirmation, tự xóa tài khoản |
| Booking Request | Tạo/sửa/duyệt/từ chối, ngày quá khứ, giờ sai, trùng lịch, quyền admin/user |
| Model | `isAdmin()` và các quan hệ Eloquent |

## 4. Selenium WebDriver

Yêu cầu:

- JDK 17.
- Chrome.
- Maven 3.9+.
- PHP/Laravel chạy được tại `http://127.0.0.1:8000`.

Chạy:

```powershell
..\tools\apache-maven-3.9.16\bin\mvn.cmd `
  -f selenium-junit-tests\pom.xml test
```

Các kịch bản:

- Đăng nhập đúng và sai.
- Mở modal thêm phòng.
- Validation sức chứa bằng 0.
- Thêm, sửa và xóa phòng bằng giao diện.
- Ghi ảnh trực tiếp từ trình duyệt cho báo cáo.

## 5. Apache JMeter

Chạy:

```powershell
..\tools\apache-jmeter-5.6.3\bin\jmeter.bat `
  -n `
  -t jmeter\classroom-management-test-plan.jmx `
  -l jmeter\results\results.jtl `
  -e -o jmeter\report
```

Kịch bản:

- Trang chủ với 10 người dùng đồng thời.
- Chặn dashboard khi chưa đăng nhập.
- Đăng nhập admin và tải dashboard.
- Sai mật khẩu và kiểm tra thông báo lỗi.

Kết quả xác nhận ngày 24/06/2026: 65 samples, error rate 0.00%.

## 6. Lỗi được phát hiện bằng test

Test `admin_can_approve_request_and_create_schedule` ban đầu thất bại vì dữ liệu cột `TIME`
có thể được database trả về ở dạng `HH:mm`, trong khi controller chỉ chấp nhận `HH:mm:ss`.

Đã sửa bằng cách parse linh hoạt với `Carbon::parse()`. Sau khi sửa, toàn bộ 50 test đều pass.

## 7. Nguyên tắc bằng chứng

- Không tự điền kết quả pass.
- File XML/JTL/log được giữ lại để đối chiếu.
- Ảnh UI do Selenium chụp trực tiếp.
- JMeter Dashboard được sinh từ `results.jtl`.
- Nếu có test fail, pipeline CI trả trạng thái fail.
