package vn.edu.classroom;

import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.MethodOrderer;
import org.junit.jupiter.api.Order;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.TestMethodOrder;
import vn.edu.classroom.Support.LaravelHttpClient;
import vn.edu.classroom.Support.LaravelServer;
import vn.edu.classroom.Support.RoomHtml;

import java.net.http.HttpResponse;
import java.util.LinkedHashMap;
import java.util.Map;

import static org.junit.jupiter.api.Assertions.assertFalse;
import static org.junit.jupiter.api.Assertions.assertTrue;

@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
class RoomFunctionTest {
    private static final String ROOM_NAME = "JUnit Room " + System.currentTimeMillis();
    private static final String UPDATED_ROOM_NAME = ROOM_NAME + " Updated";
    private static LaravelHttpClient http;

    @BeforeAll
    static void setUp() throws Exception {
        LaravelServer.ensureStarted();
        http = new LaravelHttpClient();
        http.loginAsAdmin();
    }

    @Test
    @Order(1)
    @DisplayName("Thêm phòng thành công")
    void addRoomSuccessfully() throws Exception {
        String token = http.csrfToken("/dashboard");
        HttpResponse<String> response = http.postForm("/rooms", roomData(token, ROOM_NAME, "45", "Tầng Test", "Máy chiếu"));

        assertTrue(response.body().contains("Thêm phòng học thành công!"));
        assertTrue(response.body().contains(ROOM_NAME));
    }

    @Test
    @Order(2)
    @DisplayName("Sửa phòng thành công")
    void editRoomSuccessfully() throws Exception {
        String dashboard = http.get("/dashboard").body();
        String roomId = RoomHtml.roomIdByName(dashboard, ROOM_NAME);
        String token = http.csrfToken("/dashboard");

        Map<String, String> data = roomData(token, UPDATED_ROOM_NAME, "55", "Tầng Test 2", "Máy chiếu, Micro");
        data.put("_method", "PUT");
        HttpResponse<String> response = http.postForm("/rooms/" + roomId, data);

        assertTrue(response.body().contains("Cập nhật phòng học thành công!"));
        assertTrue(response.body().contains(UPDATED_ROOM_NAME));
        assertTrue(response.body().contains("55"));
    }

    @Test
    @Order(3)
    @DisplayName("Xóa phòng thành công")
    void deleteRoomSuccessfully() throws Exception {
        String dashboard = http.get("/dashboard").body();
        String roomId = RoomHtml.roomIdByName(dashboard, UPDATED_ROOM_NAME);
        String token = http.csrfToken("/dashboard");

        HttpResponse<String> response = http.postForm("/rooms/" + roomId, new LinkedHashMap<>(Map.of(
                "_token", token,
                "_method", "DELETE"
        )));

        assertTrue(response.body().contains("Xóa phòng học thành công!"));
        assertFalse(response.body().contains(UPDATED_ROOM_NAME));
    }

    private static Map<String, String> roomData(String token, String name, String capacity, String location, String equipment) {
        Map<String, String> data = new LinkedHashMap<>();
        data.put("_token", token);
        data.put("name", name);
        data.put("capacity", capacity);
        data.put("location", location);
        data.put("equipment", equipment);
        return data;
    }
}
