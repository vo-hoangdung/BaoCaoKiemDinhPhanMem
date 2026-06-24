"""
Selenium UI test: đăng nhập / đăng xuất.

Cách chạy:
1. Chạy Laravel server: php artisan serve
2. Cài Selenium: pip install selenium
3. Chạy file: python tests/Selenium/selenium_login.py

Mặc định dùng tài khoản trong README:
- admin / password
Có thể đổi bằng biến môi trường:
- BASE_URL, ADMIN_USERNAME, ADMIN_PASSWORD
"""

import os
import unittest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

BASE_URL = os.getenv("BASE_URL", "http://127.0.0.1:8000")
ADMIN_USERNAME = os.getenv("ADMIN_USERNAME", "admin")
ADMIN_PASSWORD = os.getenv("ADMIN_PASSWORD", "password")


class LoginSeleniumTest(unittest.TestCase):
    def setUp(self):
        options = webdriver.ChromeOptions()
        # Bỏ comment dòng dưới nếu muốn chạy ẩn trình duyệt.
        # options.add_argument("--headless=new")
        options.add_argument("--window-size=1366,768")
        self.driver = webdriver.Chrome(options=options)
        self.wait = WebDriverWait(self.driver, 10)

    def tearDown(self):
        self.driver.quit()

    def login(self, username=ADMIN_USERNAME, password=ADMIN_PASSWORD):
        self.driver.get(f"{BASE_URL}/login")
        self.wait.until(EC.presence_of_element_located((By.NAME, "username"))).send_keys(username)
        self.driver.find_element(By.NAME, "password").send_keys(password)
        self.driver.find_element(By.XPATH, "//button[contains(., 'Đăng nhập') or @type='submit']").click()

    def test_login_success(self):
        self.login()

        self.wait.until(EC.url_contains("/dashboard"))
        self.assertIn("dashboard", self.driver.current_url)
        self.assertIn("Hệ thống Quản lý Lớp học", self.driver.page_source)

    def test_login_wrong_password(self):
        self.login(ADMIN_USERNAME, "sai_mat_khau")

        self.wait.until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        self.assertIn("Thông tin đăng nhập không chính xác", self.driver.page_source)

    def test_logout_success(self):
        self.login()
        self.wait.until(EC.url_contains("/dashboard"))

        logout_button = self.wait.until(
            EC.element_to_be_clickable((By.XPATH, "//button[contains(., 'Đăng xuất') or contains(., 'Logout')]"))
        )
        logout_button.click()

        self.wait.until(lambda driver: "/dashboard" not in driver.current_url)
        self.assertNotIn("/dashboard", self.driver.current_url)


if __name__ == "__main__":
    unittest.main(verbosity=2)
