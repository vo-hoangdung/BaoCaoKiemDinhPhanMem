package vn.edu.classroom.Support;

import java.io.IOException;
import java.net.CookieManager;
import java.net.CookiePolicy;
import java.net.URI;
import java.net.URLEncoder;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.nio.charset.StandardCharsets;
import java.time.Duration;
import java.util.Map;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class LaravelHttpClient {
    private static final Pattern TOKEN_PATTERN = Pattern.compile("name=\"_token\"\\s+value=\"([^\"]+)\"");
    private final HttpClient client;
    private final String baseUrl;

    public LaravelHttpClient() {
        CookieManager cookies = new CookieManager(null, CookiePolicy.ACCEPT_ALL);
        this.client = HttpClient.newBuilder()
                .cookieHandler(cookies)
                .followRedirects(HttpClient.Redirect.ALWAYS)
                .connectTimeout(Duration.ofSeconds(10))
                .build();
        this.baseUrl = LaravelServer.baseUrl();
    }

    public HttpResponse<String> get(String path) throws IOException, InterruptedException {
        return client.send(HttpRequest.newBuilder(URI.create(baseUrl + path)).GET().build(), HttpResponse.BodyHandlers.ofString());
    }

    public HttpResponse<String> postForm(String path, Map<String, String> data) throws IOException, InterruptedException {
        HttpRequest request = HttpRequest.newBuilder(URI.create(baseUrl + path))
                .header("Content-Type", "application/x-www-form-urlencoded")
                .POST(HttpRequest.BodyPublishers.ofString(formEncode(data)))
                .build();
        return client.send(request, HttpResponse.BodyHandlers.ofString());
    }

    public String csrfToken(String path) throws IOException, InterruptedException {
        String html = get(path).body();
        Matcher matcher = TOKEN_PATTERN.matcher(html);
        if (!matcher.find()) {
            throw new IllegalStateException("Không tìm thấy CSRF token tại " + path);
        }
        return matcher.group(1);
    }

    public void loginAsAdmin() throws IOException, InterruptedException {
        String token = csrfToken("/login");
        HttpResponse<String> response = postForm("/login", Map.of(
                "_token", token,
                "username", "admin",
                "password", "password"
        ));
        if (!response.body().contains("Quản trị viên")) {
            throw new AssertionError("Đăng nhập admin thất bại.");
        }
    }

    private static String formEncode(Map<String, String> data) {
        StringBuilder builder = new StringBuilder();
        data.forEach((key, value) -> {
            if (!builder.isEmpty()) {
                builder.append('&');
            }
            builder.append(URLEncoder.encode(key, StandardCharsets.UTF_8));
            builder.append('=');
            builder.append(URLEncoder.encode(value, StandardCharsets.UTF_8));
        });
        return builder.toString();
    }
}
