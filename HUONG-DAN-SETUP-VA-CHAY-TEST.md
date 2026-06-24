# Hướng Dẫn Setup Và Chạy Test

## 1. JDK 17

Máy đã có JDK 17 tại:

```text
C:\Program Files\Eclipse Adoptium\jdk-17.0.19.10-hotspot
```

Kiểm tra:

```powershell
java -version
javac -version
```

## 2. IntelliJ IDEA

IntelliJ IDEA Community bản ZIP đã được chuẩn bị tại:

```text
C:\Users\ngocd\Documents\New project\tools\IntelliJ-IDEA-Community\bin\idea64.exe
```

Mở project test bằng file:

```text
C:\Users\ngocd\Documents\New project\classroom-management\open-tests-in-intellij.bat
```

Hoặc mở trực tiếp thư mục:

```text
C:\Users\ngocd\Documents\New project\classroom-management\selenium-junit-tests
```

Trong IntelliJ:

1. Chọn JDK 17.
2. Chờ Maven import dependencies.
3. Mở tab Maven và chạy `test`, hoặc chạy từng class test trong `src/test/java`.

## 3. Source code dự án

Source chính:

```text
C:\Users\ngocd\Documents\New project\classroom-management
```

Bộ test Java:

```text
C:\Users\ngocd\Documents\New project\classroom-management\selenium-junit-tests
```

## 4. Chạy test bằng PowerShell

Từ thư mục `classroom-management`:

```powershell
..\tools\apache-maven-3.9.16\bin\mvn.cmd -f selenium-junit-tests\pom.xml test
```

Tạo report:

```powershell
powershell -ExecutionPolicy Bypass -File selenium-junit-tests\generate-test-report.ps1
```

Report được tạo tại:

```text
C:\Users\ngocd\Documents\New project\classroom-management\selenium-junit-tests\TEST-REPORT.md
```

## 5. Ghi chú

- Test tự khởi động Laravel server bằng `php artisan serve`.
- Nếu port `8000` đang bận, hãy tắt server cũ trước khi chạy.
- Selenium dùng Chrome headless nên không cần mở trình duyệt thủ công.
