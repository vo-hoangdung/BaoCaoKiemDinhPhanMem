package vn.edu.classroom;

import io.github.bonigarcia.wdm.WebDriverManager;
import org.junit.jupiter.api.AfterAll;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.MethodOrderer;
import org.junit.jupiter.api.Order;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.TestMethodOrder;
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

import static org.junit.jupiter.api.Assertions.assertTrue;

@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
class SeleniumRoomUiTest {
    private static final String ROOM_NAME = "Selenium Room " + System.currentTimeMillis();
    private static final String UPDATED_ROOM_NAME = ROOM_NAME + " Updated";
    private static WebDriver driver;
    private static WebDriverWait wait;

    @BeforeAll
    static void setUp() throws Exception {
        LaravelServer.ensureStarted();
        WebDriverManager.chromedriver().setup();

        ChromeOptions options = new ChromeOptions();
        options.addArguments("--headless=new", "--window-size=1366,768");
        driver = new ChromeDriver(options);
        wait = new WebDriverWait(driver, Duration.ofSeconds(10));

        loginAsAdmin();
    }

    @AfterAll
    static void tearDown() {
        if (driver != null) {
            driver.quit();
        }
    }

    @Test
    @Order(1)
    @DisplayName("Click 'Thêm phòng học' mở modal nhập liệu")
    void clickAddRoomButtonOpensModal() {
        driver.get(LaravelServer.baseUrl() + "/dashboard");

        driver.findElement(By.xpath("//button[contains(.,'Thêm phòng học')]")).click();

        WebElement modal = wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("addRoomModal")));
        assertTrue(modal.isDisplayed());
        assertTrue(modal.getText().contains("Thêm phòng học mới"));
    }

    @Test
    @Order(2)
    @DisplayName("Nhập dữ liệu sai hiển thị thông báo lỗi")
    void invalidRoomDataShowsValidationError() {
        driver.get(LaravelServer.baseUrl() + "/dashboard");
        driver.findElement(By.xpath("//button[contains(.,'Thêm phòng học')]")).click();

        driver.findElement(By.id("name")).sendKeys("Invalid Room " + System.currentTimeMillis());
        driver.findElement(By.id("capacity")).sendKeys("0");
        driver.findElement(By.id("location")).sendKeys("Tầng Test");
        driver.findElement(By.cssSelector("#addRoomModal button[type='submit']")).click();

        WebElement error = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".alert-error")));
        assertTrue(error.getText().contains("capacity"));
        assertTrue(error.getText().contains("at least 1"));
    }

    @Test
    @Order(3)
    @DisplayName("Nhập dữ liệu đúng thêm phòng thành công")
    void validRoomDataCreatesRoom() {
        driver.get(LaravelServer.baseUrl() + "/dashboard");
        driver.findElement(By.xpath("//button[contains(.,'Thêm phòng học')]")).click();

        driver.findElement(By.id("name")).sendKeys(ROOM_NAME);
        driver.findElement(By.id("capacity")).sendKeys("36");
        driver.findElement(By.id("location")).sendKeys("Tầng Selenium");
        driver.findElement(By.id("equipment")).sendKeys("Máy chiếu, Loa");
        driver.findElement(By.cssSelector("#addRoomModal button[type='submit']")).click();

        WebElement success = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".alert-success")));
        assertTrue(success.getText().contains("Thêm phòng học thành công!"));
        assertTrue(driver.getPageSource().contains(ROOM_NAME));
    }

    @Test
    @Order(4)
    @DisplayName("Sửa phòng bằng giao diện thành công")
    void editRoomByUiSuccessfully() {
        driver.get(LaravelServer.baseUrl() + "/dashboard");

        WebElement editButton = wait.until(ExpectedConditions.elementToBeClickable(By.xpath(
                "//tr[td[contains(normalize-space(.),'" + ROOM_NAME + "')]]//button[contains(normalize-space(.),'Sửa')]"
        )));
        editButton.click();

        wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("editRoomModal")));
        WebElement name = driver.findElement(By.id("edit_room_name"));
        name.clear();
        name.sendKeys(UPDATED_ROOM_NAME);

        WebElement capacity = driver.findElement(By.id("edit_room_capacity"));
        capacity.clear();
        capacity.sendKeys("48");

        WebElement location = driver.findElement(By.id("edit_room_location"));
        location.clear();
        location.sendKeys("Tầng Selenium Updated");

        WebElement equipment = driver.findElement(By.id("edit_room_equipment"));
        equipment.clear();
        equipment.sendKeys("Máy chiếu, Micro, Bảng thông minh");

        driver.findElement(By.cssSelector("#editRoomModal button[type='submit']")).click();

        WebElement success = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".alert-success")));
        assertTrue(success.getText().contains("Cập nhật phòng học thành công!"));
        assertTrue(driver.getPageSource().contains(UPDATED_ROOM_NAME));
    }

    @Test
    @Order(5)
    @DisplayName("Xóa phòng bằng giao diện thành công")
    void deleteRoomByUiSuccessfully() {
        driver.get(LaravelServer.baseUrl() + "/dashboard");

        WebElement deleteButton = wait.until(ExpectedConditions.elementToBeClickable(By.xpath(
                "//tr[td[contains(normalize-space(.),'" + UPDATED_ROOM_NAME + "')]]//button[contains(normalize-space(.),'Xóa')]"
        )));
        ((JavascriptExecutor) driver).executeScript("arguments[0].click();", deleteButton);
        wait.until(ExpectedConditions.alertIsPresent()).accept();

        WebElement success = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".alert-success")));
        assertTrue(success.getText().contains("Xóa phòng học thành công!"));
        assertTrue(!driver.getPageSource().contains(UPDATED_ROOM_NAME));
    }

    private static void loginAsAdmin() {
        driver.get(LaravelServer.baseUrl() + "/login");
        driver.findElement(By.id("username")).sendKeys("admin");
        driver.findElement(By.id("password")).sendKeys("password");
        driver.findElement(By.cssSelector("button[type='submit']")).click();
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("userRole")));
    }
}
