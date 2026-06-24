package vn.edu.classroom;

import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;
import vn.edu.classroom.Support.LaravelHttpClient;
import vn.edu.classroom.Support.LaravelServer;

import java.net.http.HttpResponse;
import java.util.Map;

import static org.junit.jupiter.api.Assertions.assertTrue;

class LoginFunctionTest {
    @BeforeAll
    static void startServer() throws Exception {
        LaravelServer.ensureStarted();
    }

    @Test
    @DisplayName("Đăng nhập đúng chuyển vào dashboard")
    void loginWithValidCredentials() throws Exception {
        LaravelHttpClient http = new LaravelHttpClient();
        http.loginAsAdmin();

        HttpResponse<String> dashboard = http.get("/dashboard");
        assertTrue(dashboard.body().contains("Quản trị viên"));
        assertTrue(dashboard.body().contains("Phòng học"));
    }

    @Test
    @DisplayName("Đăng nhập sai hiển thị thông báo lỗi")
    void loginWithInvalidCredentialsShowsError() throws Exception {
        LaravelHttpClient http = new LaravelHttpClient();
        String token = http.csrfToken("/login");

        HttpResponse<String> response = http.postForm("/login", Map.of(
                "_token", token,
                "username", "admin",
                "password", "wrong-password"
        ));

        assertTrue(response.body().contains("Thông tin đăng nhập không chính xác."));
    }
}
