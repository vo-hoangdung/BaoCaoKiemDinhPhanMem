# Test Plan - Classroom Management

## Mục tiêu

Kiểm thử các chức năng chính của hệ thống quản lý phòng học:

- Đăng nhập
- Thêm phòng
- Sửa phòng
- Xóa phòng
- Kiểm tra giao diện thêm phòng học bằng Selenium
- Kiểm tra thông báo lỗi khi nhập dữ liệu sai

## Môi trường

- OS: Windows
- JDK: 17
- Build tool: Maven 3.9.16
- Test framework: JUnit 5
- UI automation: Selenium WebDriver
- Browser: Chrome headless
- Application: Laravel chạy tại `http://127.0.0.1:8000`
- Database: SQLite

## Tài khoản kiểm thử

| Vai trò | Username | Password |
|---|---|---|
| Admin | admin | password |
| Giáo viên | giaovien1 | password |

## Danh sách test case

| ID | Nhóm | Test case | Dữ liệu | Kết quả mong đợi |
|---|---|---|---|---|
| TC-LOGIN-01 | JUnit | Đăng nhập đúng | admin / password | Chuyển vào dashboard, thấy vai trò Quản trị viên |
| TC-LOGIN-02 | JUnit | Đăng nhập sai | admin / wrong-password | Hiển thị lỗi đăng nhập |
| TC-ROOM-01 | JUnit | Thêm phòng | tên phòng mới, sức chứa 45 | Thông báo thêm phòng thành công |
| TC-ROOM-02 | JUnit | Sửa phòng | đổi tên, sức chứa 55 | Thông báo cập nhật thành công |
| TC-ROOM-03 | JUnit | Xóa phòng | phòng vừa tạo | Thông báo xóa thành công |
| TC-UI-LOGIN-01 | Selenium | Đăng nhập đúng trên UI | admin / password | Vào dashboard |
| TC-UI-LOGIN-02 | Selenium | Đăng nhập sai trên UI | admin / wrong-password | Hiển thị thông báo lỗi |
| TC-UI-ROOM-01 | Selenium | Click Thêm phòng học | nút Thêm phòng học | Modal thêm phòng mở |
| TC-UI-ROOM-02 | Selenium | Nhập dữ liệu sai | capacity = 0 | Hiển thị lỗi validation |
| TC-UI-ROOM-03 | Selenium | Nhập dữ liệu đúng | phòng Selenium mới | Thêm phòng thành công |
| TC-UI-ROOM-04 | Selenium | Sửa phòng trên UI | đổi tên phòng Selenium | Cập nhật phòng thành công |
| TC-UI-ROOM-05 | Selenium | Xóa phòng trên UI | phòng Selenium đã sửa | Xóa phòng thành công |

## Lệnh chạy

```powershell
..\tools\apache-maven-3.9.16\bin\mvn.cmd -f selenium-junit-tests\pom.xml test
powershell -ExecutionPolicy Bypass -File selenium-junit-tests\generate-test-report.ps1
```
