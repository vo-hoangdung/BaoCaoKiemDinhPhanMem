package vn.edu.classroom;

import io.github.bonigarcia.wdm.WebDriverManager;
import org.junit.jupiter.api.AfterEach;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import vn.edu.classroom.Support.LaravelServer;

import java.time.Duration;

import static org.junit.jupiter.api.Assertions.assertTrue;

class SeleniumLoginUiTest {
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
        options.addArguments("--headless=new", "--window-size=1366,768");
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
    @DisplayName("Selenium đăng nhập đúng vào dashboard")
    void validLoginGoesToDashboard() {
        driver.get(LaravelServer.baseUrl() + "/login");
        driver.findElement(By.id("username")).sendKeys("admin");
        driver.findElement(By.id("password")).sendKeys("password");
        driver.findElement(By.cssSelector("button[type='submit']")).click();

        wait.until(ExpectedConditions.urlContains("/dashboard"));
        assertTrue(driver.findElement(By.id("userRole")).getText().contains("Quản trị viên"));
    }

    @Test
    @DisplayName("Selenium đăng nhập sai hiển thị lỗi")
    void invalidLoginShowsErrorMessage() {
        driver.get(LaravelServer.baseUrl() + "/login");
        driver.findElement(By.id("username")).sendKeys("admin");
        driver.findElement(By.id("password")).sendKeys("wrong-password");
        driver.findElement(By.cssSelector("button[type='submit']")).click();

        String error = wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector(".error-message"))).getText();
        assertTrue(error.contains("Thông tin đăng nhập không chính xác."));
    }
}
