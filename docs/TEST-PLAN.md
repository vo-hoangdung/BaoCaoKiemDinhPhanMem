# Kế hoạch kiểm thử giữa kỳ

## Mục tiêu

Đánh giá tính đúng đắn, ổn định và an toàn phân quyền của hệ thống quản lý lớp học.

## Môi trường

| Thành phần | Cấu hình |
|---|---|
| Hệ điều hành | Windows 10/11 |
| Backend | PHP 8.2+, Laravel 12 |
| Database test | SQLite in-memory |
| Unit/Feature | PHPUnit 11 |
| UI automation | Selenium WebDriver + JUnit 5 |
| Performance | Apache JMeter 5.6.3 |
| Browser | Google Chrome headless |

## Chiến lược

1. Unit test model và quan hệ.
2. Feature test controller, validation, database và middleware.
3. Selenium test các luồng người dùng quan trọng.
4. JMeter test HTTP và tải đồng thời nhẹ.
5. Regression test sau khi sửa lỗi.

## Tiêu chí hoàn thành

- Tất cả ca test bắt buộc pass.
- Không có lỗi HTTP ngoài dự kiến.
- Các thao tác trái quyền bị chặn.
- Dữ liệu test không làm thay đổi dữ liệu nghiệp vụ lâu dài.
- Bằng chứng gồm log gốc, XML/JTL, ảnh UI và báo cáo HTML.
