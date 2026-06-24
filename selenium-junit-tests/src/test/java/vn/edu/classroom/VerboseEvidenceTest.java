package vn.edu.classroom;

import io.github.bonigarcia.wdm.WebDriverManager;
import org.junit.jupiter.api.AfterEach;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import vn.edu.classroom.Support.LaravelServer;

import java.time.Duration;

import static org.junit.jupiter.api.Assertions.assertFalse;
import static org.junit.jupiter.api.Assertions.assertTrue;

class VerboseEvidenceTest {
    private WebDriver driver;
    private WebDriverWait wait;

    @BeforeAll
    static void startServer() throws Exception {
        LaravelServer.ensureStarted();
        WebDriverManager.chromedriver().setup();
    }

    @BeforeEach
    void openBrowser() {
        ChromeOptions options = new ChromeOptions();
        options.addArguments("--headless=new", "--window-size=1366,900");
        driver = new ChromeDriver(options);
        wait = new WebDriverWait(driver, Duration.ofSeconds(10));
    }

    @AfterEach
    void closeBrowser() {
        if (driver != null) {
            driver.quit();
        }
    }

    @Test
    @DisplayName("Verbose - kiểm thử đăng nhập đúng và sai")
    void loginCases() {
        banner("TEST: ĐĂNG NHẬP ADMIN - THÔNG TIN ĐÚNG VÀ SAI");
        log("LOGIN", "Mở trang đăng nhập: " + LaravelServer.baseUrl() + "/login");
        driver.get(LaravelServer.baseUrl() + "/login");
        log("LOGIN", "Đã tải form đăng nhập");
        log("LOGIN", "Nhập username: admin");
        driver.findElement(By.id("username")).sendKeys("admin");
        log("LOGIN", "Nhập password hợp lệ");
        driver.findElement(By.id("password")).sendKeys("password");
        log("LOGIN", "Click nút Đăng nhập");
        driver.findElement(By.cssSelector("button[type='submit']")).click();
        wait.until(ExpectedConditions.urlContains("/dashboard"));
        String role = driver.findElement(By.id("userRole")).getText();
        log("LOGIN", "Đăng nhập thành công: " + driver.getCurrentUrl());
        log("VERIFY", "Vai trò hiển thị: " + role);
        assertTrue(role.contains("Quản trị viên"));

        log("INVALID", "Thử lại với mật khẩu sai");
        driver.get(LaravelServer.baseUrl() + "/login");
        driver.findElement(By.id("username")).sendKeys("admin");
        driver.findElement(By.id("password")).sendKeys("wrong-password");
        driver.findElement(By.cssSelector("button[type='submit']")).click();
        String error = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".error-message"))).getText();
        log("INVALID", "Thông báo nhận được: " + error);
        assertTrue(error.contains("Thông tin đăng nhập không chính xác."));
        log("FINAL", "TEST HOÀN THÀNH - ĐĂNG NHẬP ĐÚNG/SAI ĐỀU ĐẠT");
    }

    @Test
    @DisplayName("Verbose - kiểm thử validation phòng học")
    void roomValidation() {
        banner("TEST: VALIDATION KHI THÊM PHÒNG HỌC KHÔNG HỢP LỆ");
        login();
        log("ROOM", "Mở chức năng Thêm phòng học");
        driver.findElement(By.xpath("//button[contains(.,'Thêm phòng học')]")).click();
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("addRoomModal")));
        log("FORM", "Đã hiển thị modal thêm phòng");
        driver.findElement(By.id("name")).sendKeys("Invalid Evidence Room");
        driver.findElement(By.id("capacity")).sendKeys("0");
        driver.findElement(By.id("location")).sendKeys("Tầng Test");
        log("FORM", "Tên phòng: Invalid Evidence Room");
        log("FORM", "Sức chứa: 0 (dữ liệu không hợp lệ)");
        log("FORM", "Vị trí: Tầng Test");
        log("FORM", "Click nút Thêm phòng");
        driver.findElement(By.cssSelector("#addRoomModal button[type='submit']")).click();
        String error = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".alert-error"))).getText();
        log("VALIDATE", "Thông báo nhận được: " + error);
        assertTrue(error.contains("capacity"));
        assertTrue(error.contains("at least 1"));
        log("FINAL", "TEST HOÀN THÀNH - HỆ THỐNG ĐÃ CHẶN DỮ LIỆU SAI");
    }

    @Test
    @DisplayName("Verbose - kiểm thử thêm phòng học")
    void addRoom() {
        String roomName = "Evidence Add Room " + System.currentTimeMillis();
        banner("TEST: ADMIN THÊM PHÒNG HỌC VÀ KIỂM TRA DANH SÁCH");
        login();
        log("ADD", "Truy cập dashboard quản lý phòng");
        driver.findElement(By.xpath("//button[contains(.,'Thêm phòng học')]")).click();
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("addRoomModal")));
        log("ADD", "Modal thêm phòng đã mở");
        driver.findElement(By.id("name")).sendKeys(roomName);
        driver.findElement(By.id("capacity")).sendKeys("45");
        driver.findElement(By.id("location")).sendKeys("Tầng Selenium");
        driver.findElement(By.id("equipment")).sendKeys("Máy chiếu, Loa, Micro");
        log("ADD", "Tên phòng: " + roomName);
        log("ADD", "Sức chứa: 45");
        log("ADD", "Vị trí: Tầng Selenium");
        log("ADD", "Thiết bị: Máy chiếu, Loa, Micro");
        driver.findElement(By.cssSelector("#addRoomModal button[type='submit']")).click();
        String success = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".alert-success"))).getText();
        log("ADD", "Thông báo: " + success);
        assertTrue(success.contains("Thêm phòng học thành công!"));
        assertTrue(driver.getPageSource().contains(roomName));
        log("LIST", "Tìm thấy phòng mới trong danh sách");

        deleteRoom(roomName);
        log("CLEANUP", "Đã xóa dữ liệu test: " + roomName);
        log("FINAL", "TEST HOÀN THÀNH - THÊM PHÒNG THÀNH CÔNG");
    }

    @Test
    @DisplayName("Verbose - kiểm thử sửa và xóa phòng học")
    void editAndDeleteRoom() {
        String original = "Evidence Lifecycle " + System.currentTimeMillis();
        String updated = original + " Updated";
        banner("TEST: ADMIN THÊM - SỬA - XÓA PHÒNG HỌC");
        login();
        createRoom(original);
        log("SETUP", "Đã tạo phòng test: " + original);

        WebElement editButton = wait.until(ExpectedConditions.elementToBeClickable(By.xpath(
                "//tr[td[contains(normalize-space(.),'" + original + "')]]//button[contains(normalize-space(.),'Sửa')]"
        )));
        log("EDIT", "Tìm thấy nút Sửa của phòng test");
        editButton.click();
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("editRoomModal")));
        WebElement name = driver.findElement(By.id("edit_room_name"));
        name.clear();
        name.sendKeys(updated);
        WebElement capacity = driver.findElement(By.id("edit_room_capacity"));
        capacity.clear();
        capacity.sendKeys("60");
        log("EDIT", "Đổi tên phòng thành: " + updated);
        log("EDIT", "Đổi sức chứa thành: 60");
        driver.findElement(By.cssSelector("#editRoomModal button[type='submit']")).click();
        String editSuccess = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".alert-success"))).getText();
        log("EDIT", "Thông báo: " + editSuccess);
        assertTrue(editSuccess.contains("Cập nhật phòng học thành công!"));
        assertTrue(driver.getPageSource().contains(updated));

        log("DELETE", "Tìm phòng vừa cập nhật và click Xóa");
        deleteRoom(updated);
        String deleteSuccess = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".alert-success"))).getText();
        log("DELETE", "Thông báo: " + deleteSuccess);
        assertTrue(deleteSuccess.contains("Xóa phòng học thành công!"));
        assertFalse(driver.getPageSource().contains(updated));
        log("VERIFY", "Phòng đã biến mất khỏi danh sách");
        log("FINAL", "TEST HOÀN THÀNH - THÊM/SỬA/XÓA PHÒNG ĐỀU THÀNH CÔNG");
    }

    private void login() {
        log("LOGIN", "Đăng nhập admin tại: " + LaravelServer.baseUrl() + "/login");
        driver.get(LaravelServer.baseUrl() + "/login");
        driver.findElement(By.id("username")).sendKeys("admin");
        driver.findElement(By.id("password")).sendKeys("password");
        driver.findElement(By.cssSelector("button[type='submit']")).click();
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("userRole")));
        log("LOGIN", "Đăng nhập thành công: " + driver.getCurrentUrl());
    }

    private void createRoom(String roomName) {
        driver.findElement(By.xpath("//button[contains(.,'Thêm phòng học')]")).click();
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("addRoomModal")));
        driver.findElement(By.id("name")).sendKeys(roomName);
        driver.findElement(By.id("capacity")).sendKeys("35");
        driver.findElement(By.id("location")).sendKeys("Tầng Test");
        driver.findElement(By.id("equipment")).sendKeys("Máy chiếu");
        driver.findElement(By.cssSelector("#addRoomModal button[type='submit']")).click();
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".alert-success")));
    }

    private void deleteRoom(String roomName) {
        WebElement deleteButton = wait.until(ExpectedConditions.elementToBeClickable(By.xpath(
                "//tr[td[contains(normalize-space(.),'" + roomName + "')]]//button[contains(normalize-space(.),'Xóa')]"
        )));
        ((JavascriptExecutor) driver).executeScript("arguments[0].click();", deleteButton);
        wait.until(ExpectedConditions.alertIsPresent()).accept();
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".alert-success")));
    }

    private static void banner(String text) {
        System.out.println("========================================================================");
        System.out.println(text);
        System.out.println("========================================================================");
    }

    private static void log(String section, String message) {
        System.out.println("[" + section + "] " + message);
    }
}
