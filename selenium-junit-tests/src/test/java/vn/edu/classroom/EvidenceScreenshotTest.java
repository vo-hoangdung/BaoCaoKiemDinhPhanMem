package vn.edu.classroom;

import io.github.bonigarcia.wdm.WebDriverManager;
import org.junit.jupiter.api.AfterAll;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.condition.EnabledIfSystemProperty;
import org.openqa.selenium.By;
import org.openqa.selenium.OutputType;
import org.openqa.selenium.TakesScreenshot;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import vn.edu.classroom.Support.LaravelServer;

import java.nio.file.Files;
import java.nio.file.Path;
import java.time.Duration;

import static org.junit.jupiter.api.Assertions.assertTrue;

@EnabledIfSystemProperty(named = "capture.evidence", matches = "true")
class EvidenceScreenshotTest {
    private static WebDriver driver;
    private static WebDriverWait wait;
    private static Path evidenceDir;

    @BeforeAll
    static void setUp() throws Exception {
        LaravelServer.ensureStarted();
        WebDriverManager.chromedriver().setup();

        ChromeOptions options = new ChromeOptions();
        options.addArguments("--headless=new", "--window-size=1366,900");
        driver = new ChromeDriver(options);
        wait = new WebDriverWait(driver, Duration.ofSeconds(10));
        evidenceDir = Path.of("..", "test-evidence").toAbsolutePath().normalize();
        Files.createDirectories(evidenceDir);
    }

    @AfterAll
    static void tearDown() {
        if (driver != null) {
            driver.quit();
        }
    }

    @Test
    @DisplayName("Chụp JMeter Dashboard sinh từ results.jtl")
    void captureReports() throws Exception {
        driver.get("http://127.0.0.1:8765/jmeter/report/index.html");
        wait.until(ExpectedConditions.titleContains("JMeter Dashboard"));
        save("02-jmeter-dashboard.png");
    }

    @Test
    @DisplayName("Chụp trường hợp đăng nhập sai")
    void captureInvalidLogin() throws Exception {
        driver.get(LaravelServer.baseUrl() + "/login");
        driver.findElement(By.id("username")).sendKeys("admin");
        driver.findElement(By.id("password")).sendKeys("wrong-password");
        driver.findElement(By.cssSelector("button[type='submit']")).click();

        WebElement error = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".error-message")));
        assertTrue(error.getText().contains("Thông tin đăng nhập không chính xác."));
        save("03-invalid-login.png");
    }

    @Test
    @DisplayName("Chụp dashboard sau đăng nhập hợp lệ")
    void captureValidLoginDashboard() throws Exception {
        loginAsAdmin();
        assertTrue(driver.getCurrentUrl().contains("/dashboard"));
        save("04-admin-dashboard.png");
    }

    @Test
    @DisplayName("Chụp validation khi sức chứa phòng bằng 0")
    void captureRoomValidation() throws Exception {
        loginAsAdmin();
        driver.findElement(By.xpath("//button[contains(.,'Thêm phòng học')]")).click();
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("addRoomModal")));

        driver.findElement(By.id("name")).sendKeys("Invalid Evidence Room");
        driver.findElement(By.id("capacity")).sendKeys("0");
        driver.findElement(By.id("location")).sendKeys("Tầng Test");
        driver.findElement(By.cssSelector("#addRoomModal button[type='submit']")).click();

        WebElement error = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".alert-error")));
        assertTrue(error.getText().contains("capacity"));
        save("05-room-validation.png");
    }

    private static void loginAsAdmin() {
        driver.get(LaravelServer.baseUrl() + "/login");
        driver.findElement(By.id("username")).sendKeys("admin");
        driver.findElement(By.id("password")).sendKeys("password");
        driver.findElement(By.cssSelector("button[type='submit']")).click();
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("userRole")));
    }

    private static void save(String filename) throws Exception {
        byte[] png = ((TakesScreenshot) driver).getScreenshotAs(OutputType.BYTES);
        Files.write(evidenceDir.resolve(filename), png);
    }
}
