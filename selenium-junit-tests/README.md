# Selenium + JUnit Tests

Bộ test Java này kiểm thử app Laravel `classroom-management` bằng JUnit 5 và Selenium WebDriver.

## Nội dung đã làm

- Cấu hình Maven project chạy bằng JDK 17.
- Viết JUnit test chức năng đăng nhập đúng/sai.
- Viết JUnit test chức năng thêm phòng, sửa phòng, xóa phòng.
- Viết Selenium test giao diện đăng nhập đúng/sai.
- Viết Selenium test click nút `Thêm phòng học`.
- Viết Selenium test nhập dữ liệu phòng đúng/sai.
- Viết Selenium test kiểm tra thông báo lỗi validation.
- Viết Selenium test sửa phòng và xóa phòng trên giao diện.
- Tạo script sinh `TEST-REPORT.md` Pass/Fail.

## Cấu trúc

```text
selenium-junit-tests
├── pom.xml
├── TEST-PLAN.md
├── TEST-REPORT.md
├── generate-test-report.ps1
└── src/test/java/vn/edu/classroom
    ├── LoginFunctionTest.java
    ├── RoomFunctionTest.java
    ├── SeleniumLoginUiTest.java
    ├── SeleniumRoomUiTest.java
    └── Support
        ├── LaravelHttpClient.java
        ├── LaravelServer.java
        └── RoomHtml.java
```

## Chạy test

Từ thư mục `classroom-management`:

```powershell
..\tools\apache-maven-3.9.16\bin\mvn.cmd -f selenium-junit-tests\pom.xml test
powershell -ExecutionPolicy Bypass -File selenium-junit-tests\generate-test-report.ps1
```

Test sẽ tự khởi động `php artisan serve` trên `http://127.0.0.1:8000` nếu server chưa chạy.

## Tài khoản test

- Admin: `admin / password`
- Giáo viên: `giaovien1 / password`

## Mở trong IntelliJ IDEA

Chạy file:

```text
classroom-management\open-tests-in-intellij.bat
```

Hoặc mở trực tiếp folder:

```text
classroom-management\selenium-junit-tests
```

Sau đó chọn JDK 17 và chạy Maven goal `test`.
