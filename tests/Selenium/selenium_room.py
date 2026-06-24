"""
Selenium UI test: thêm phòng học qua giao diện.

Cách chạy:
1. Chạy Laravel server: php artisan serve
2. Cài Selenium: pip install selenium
3. Chạy file: python tests/Selenium/selenium_room.py

Yêu cầu: có tài khoản admin trong database.
Mặc định theo README: admin / password.
"""

import os
import time
import unittest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

BASE_URL = os.getenv("BASE_URL", "http://127.0.0.1:8000")
ADMIN_USERNAME = os.getenv("ADMIN_USERNAME", "admin")
ADMIN_PASSWORD = os.getenv("ADMIN_PASSWORD", "password")


class RoomSeleniumTest(unittest.TestCase):
    def setUp(self):
        options = webdriver.ChromeOptions()
        # options.add_argument("--headless=new")
        options.add_argument("--window-size=1366,768")
        self.driver = webdriver.Chrome(options=options)
        self.wait = WebDriverWait(self.driver, 10)

    def tearDown(self):
        self.driver.quit()

    def login_admin(self):
        self.driver.get(f"{BASE_URL}/login")
        self.wait.until(EC.presence_of_element_located((By.NAME, "username"))).send_keys(ADMIN_USERNAME)
        self.driver.find_element(By.NAME, "password").send_keys(ADMIN_PASSWORD)
        self.driver.find_element(By.XPATH, "//button[contains(., 'Đăng nhập') or @type='submit']").click()
        self.wait.until(EC.url_contains("/dashboard"))

    def click_button_contains(self, text):
        button = self.wait.until(
            EC.element_to_be_clickable((By.XPATH, f"//*[self::button or self::a][contains(normalize-space(), '{text}')]") )
        )
        self.driver.execute_script("arguments[0].click();", button)

    def fill_input(self, name, value):
        element = self.wait.until(EC.presence_of_element_located((By.NAME, name)))
        element.clear()
        element.send_keys(value)

    def test_admin_can_create_room_from_ui(self):
        self.login_admin()

        room_name = f"Phòng Selenium {int(time.time())}"

        self.click_button_contains("Thêm phòng")
        self.fill_input("name", room_name)
        self.fill_input("capacity", "45")
        self.fill_input("location", "Tầng Selenium")

        # equipment có thể là textarea; nếu không có thì bỏ qua để test vẫn chạy.
        try:
            self.fill_input("equipment", "Máy chiếu, bảng")
        except Exception:
            pass

        submit_button = self.wait.until(
            EC.element_to_be_clickable((By.XPATH, "//button[contains(., 'Thêm phòng') or @type='submit']"))
        )
        self.driver.execute_script("arguments[0].click();", submit_button)

        self.wait.until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        self.assertIn(room_name, self.driver.page_source)


if __name__ == "__main__":
    unittest.main(verbosity=2)
