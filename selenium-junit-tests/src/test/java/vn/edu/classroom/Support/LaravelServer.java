package vn.edu.classroom.Support;

import java.io.IOException;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.nio.file.Path;
import java.time.Duration;

public final class LaravelServer {
    private static Process process;

    private LaravelServer() {
    }

    public static String baseUrl() {
        return System.getProperty("app.baseUrl", "http://127.0.0.1:8000");
    }

    public static synchronized void ensureStarted() throws Exception {
        if (isResponding()) {
            return;
        }

        Path appDir = Path.of(System.getProperty("app.dir", "..")).toAbsolutePath().normalize();
        process = new ProcessBuilder("php", "artisan", "serve", "--host=127.0.0.1", "--port=8000")
                .directory(appDir.toFile())
                .redirectErrorStream(true)
                .start();

        Runtime.getRuntime().addShutdownHook(new Thread(LaravelServer::stop));

        long deadline = System.currentTimeMillis() + 60_000;
        while (System.currentTimeMillis() < deadline) {
            if (isResponding()) {
                return;
            }
            Thread.sleep(500);
        }

        throw new IllegalStateException("Laravel server không phản hồi tại " + baseUrl());
    }

    public static synchronized void stop() {
        if (process != null && process.isAlive()) {
            process.destroy();
        }
    }

    private static boolean isResponding() {
        try {
            HttpClient client = HttpClient.newBuilder()
                    .connectTimeout(Duration.ofSeconds(2))
                    .build();
            HttpRequest request = HttpRequest.newBuilder(URI.create(baseUrl() + "/login"))
                    .timeout(Duration.ofSeconds(2))
                    .GET()
                    .build();
            int status = client.send(request, HttpResponse.BodyHandlers.discarding()).statusCode();
            return status >= 200 && status < 500;
        } catch (IOException | InterruptedException ex) {
            if (ex instanceof InterruptedException) {
                Thread.currentThread().interrupt();
            }
            return false;
        }
    }
}
