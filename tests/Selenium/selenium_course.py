"""
Selenium UI test: thêm môn học qua giao diện.

Cách chạy:
1. Chạy Laravel server: php artisan serve
2. Cài Selenium: pip install selenium
3. Chạy file: python tests/Selenium/selenium_course.py

Yêu cầu:
- Có tài khoản admin trong database, mặc định: admin / password
- Có ít nhất 1 user/giảng viên để chọn ở ô Giảng viên

Có thể đổi tài khoản bằng biến môi trường:
BASE_URL, ADMIN_USERNAME, ADMIN_PASSWORD
"""

import os
import time
import unittest

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select, WebDriverWait
from selenium.webdriver.support import expected_conditions as EC


BASE_URL = os.getenv("BASE_URL", "http://127.0.0.1:8000")
ADMIN_USERNAME = os.getenv("ADMIN_USERNAME", "admin")
ADMIN_PASSWORD = os.getenv("ADMIN_PASSWORD", "password")


class CourseSeleniumTest(unittest.TestCase):
    def setUp(self):
        options = webdriver.ChromeOptions()
        # options.add_argument("--headless=new")
        options.add_argument("--window-size=1366,768")
        self.driver = webdriver.Chrome(options=options)
        self.wait = WebDriverWait(self.driver, 15)

    def tearDown(self):
        self.driver.quit()

    def login_admin(self):
        self.driver.get(f"{BASE_URL}/login")
        self.wait.until(EC.presence_of_element_located((By.NAME, "username"))).send_keys(ADMIN_USERNAME)
        self.driver.find_element(By.NAME, "password").send_keys(ADMIN_PASSWORD)
        self.driver.find_element(By.XPATH, "//button[@type='submit' or contains(normalize-space(), 'Đăng nhập')]").click()
        self.wait.until(EC.url_contains("/dashboard"))

    def click_button_contains(self, text):
        button = self.wait.until(
            EC.element_to_be_clickable((By.XPATH, f"//*[self::button or self::a][contains(normalize-space(), '{text}')]") )
        )
        self.driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", button)
        self.driver.execute_script("arguments[0].click();", button)

    def form_input(self, form, name, value):
        element = form.find_element(By.NAME, name)
        self.driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", element)
        element.clear()
        element.send_keys(value)

    def select_first_real_option(self, form, name):
        select_element = form.find_element(By.NAME, name)
        select = Select(select_element)
        options = [option for option in select.options if option.get_attribute("value")]
        if not options:
            raise AssertionError(f"Select '{name}' không có dữ liệu. Hãy tạo ít nhất 1 user/giảng viên trước.")
        select.select_by_value(options[0].get_attribute("value"))

    def test_admin_can_create_course_from_ui(self):
        self.login_admin()

        # Chuyển sang tab Môn học
        self.click_button_contains("Môn học")

        timestamp = int(time.time())
        course_name = f"Môn Selenium {timestamp}"
        course_code = f"SEL{timestamp}"

        # Mở modal bằng cách gọi script showModal của app
        self.driver.execute_script("showModal('addCourseModal')")

        form = self.wait.until(
            EC.presence_of_element_located((By.XPATH, "//div[@id='addCourseModal']//form[contains(@action, '/courses')]") )
        )
        self.form_input(form, "name", course_name)
        self.form_input(form, "code", course_code)
        self.form_input(form, "description", "Môn học được tạo tự động bằng Selenium")
        self.form_input(form, "credits", "3")
        self.select_first_real_option(form, "instructorId")

        submit_button = form.find_element(By.XPATH, ".//button[@type='submit' or contains(normalize-space(), 'Thêm môn học')]")
        self.driver.execute_script("arguments[0].click();", submit_button)

        self.wait.until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        self.assertIn(course_name, self.driver.page_source)
        self.assertIn(course_code, self.driver.page_source)


if __name__ == "__main__":
    unittest.main(verbosity=2)
