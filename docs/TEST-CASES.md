# Danh sách test case

| ID | Nhóm | Mô tả | Kết quả mong đợi |
|---|---|---|---|
| AUTH-01 | Login | Đăng nhập admin đúng | Chuyển đến dashboard |
| AUTH-02 | Login | Sai mật khẩu | Hiển thị lỗi, không đăng nhập |
| AUTH-03 | Login | Bỏ trống dữ liệu | Validation username/password |
| AUTH-04 | Security | Guest vào dashboard | Chuyển về login |
| ROOM-01 | Room | Thêm phòng hợp lệ | Lưu database |
| ROOM-02 | Room | Capacity bằng 0 | Validation lỗi |
| ROOM-03 | Room | Tên phòng trùng | Validation lỗi |
| ROOM-04 | Room | Sửa phòng | Dữ liệu được cập nhật |
| ROOM-05 | Room | Xóa phòng không dùng | Xóa thành công |
| ROOM-06 | Room | Xóa phòng đang có lịch | Hệ thống từ chối |
| ROOM-07 | Authorization | User thường thêm phòng | HTTP 403 |
| COURSE-01 | Course | Thêm môn hợp lệ | Lưu database |
| COURSE-02 | Course | Mã môn trùng | Validation lỗi |
| COURSE-03 | Course | Tín chỉ > 10 | Validation lỗi |
| COURSE-04 | Course | Xóa môn có yêu cầu | Hệ thống từ chối |
| SCHEDULE-01 | Schedule | Tạo lịch hợp lệ | Lưu lịch |
| SCHEDULE-02 | Schedule | End trước start | Validation lỗi |
| SCHEDULE-03 | Schedule | Hai lịch trùng phòng | Hệ thống từ chối |
| SCHEDULE-04 | Authorization | Sửa lịch người khác | Hệ thống từ chối |
| USER-01 | User | Thêm người dùng | Mật khẩu được hash |
| USER-02 | User | Trùng username/email | Validation lỗi |
| USER-03 | User | Password confirmation sai | Validation lỗi |
| USER-04 | User | Admin tự xóa mình | Hệ thống từ chối |
| BOOK-01 | Booking | Tạo yêu cầu hợp lệ | Trạng thái pending |
| BOOK-02 | Booking | Ngày trong quá khứ | Validation lỗi |
| BOOK-03 | Booking | End trước start | Validation lỗi |
| BOOK-04 | Booking | Yêu cầu trùng lịch | Hệ thống từ chối |
| BOOK-05 | Booking | Admin duyệt yêu cầu | Approved và tạo schedule |
| BOOK-06 | Booking | Admin từ chối yêu cầu | Trạng thái rejected |
| BOOK-07 | Authorization | User thường duyệt yêu cầu | HTTP 403 |
| UI-01 | Selenium | Login đúng | Dashboard hiển thị |
| UI-02 | Selenium | Login sai | Thông báo lỗi |
| UI-03 | Selenium | Thêm phòng | Thông báo thành công |
| UI-04 | Selenium | Sửa phòng | Thông báo thành công |
| UI-05 | Selenium | Xóa phòng | Phòng biến mất |
| PERF-01 | JMeter | 10 users tải trang chủ | Không lỗi |
| PERF-02 | JMeter | 10 users đăng nhập/dashboard | Không lỗi |
