"""
Selenium UI test: tạo yêu cầu đặt phòng qua giao diện.

Cách chạy:
1. Chạy Laravel server: php artisan serve
2. Cài Selenium: pip install selenium
3. Chạy file: python tests/Selenium/selenium_booking_request.py

Yêu cầu dữ liệu:
- Có tài khoản user, mặc định theo README: giaovien1 / password
- Có ít nhất 1 phòng và 1 môn học trong hệ thống để form select có dữ liệu

Có thể đổi bằng biến môi trường:
BASE_URL, USER_USERNAME, USER_PASSWORD
"""

import os
import time
import unittest
from datetime import date, timedelta
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select, WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

BASE_URL = os.getenv("BASE_URL", "http://127.0.0.1:8000")
USER_USERNAME = os.getenv("USER_USERNAME", "giaovien1")
USER_PASSWORD = os.getenv("USER_PASSWORD", "password")


class BookingRequestSeleniumTest(unittest.TestCase):
    def setUp(self):
        options = webdriver.ChromeOptions()
        # options.add_argument("--headless=new")
        options.add_argument("--window-size=1366,768")
        self.driver = webdriver.Chrome(options=options)
        self.wait = WebDriverWait(self.driver, 10)

    def tearDown(self):
        self.driver.quit()

    def login_user(self):
        self.driver.get(f"{BASE_URL}/login")
        self.wait.until(EC.presence_of_element_located((By.NAME, "username"))).send_keys(USER_USERNAME)
        self.driver.find_element(By.NAME, "password").send_keys(USER_PASSWORD)
        self.driver.find_element(By.XPATH, "//button[contains(., 'Đăng nhập') or @type='submit']").click()
        self.wait.until(EC.url_contains("/dashboard"))

    def select_first_real_option(self, element_id):
        # scope selector specifically to the modal to avoid collision with other modals
        select_element = self.wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, f"#addRequestModal #{element_id}")))
        select = Select(select_element)
        options = [option for option in select.options if option.get_attribute("value")]
        if not options:
            raise AssertionError(f"Select '{element_id}' không có dữ liệu. Hãy tạo dữ liệu phòng/môn học trước.")
        select.select_by_value(options[0].get_attribute("value"))

    def fill_input_by_id(self, element_id, value):
        element = self.wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, f"#addRequestModal #{element_id}")))
        # Dùng JS để set value cho date/time input (tránh lỗi định dạng trên Windows)
        self.driver.execute_script(
            "arguments[0].value = arguments[1]; arguments[0].dispatchEvent(new Event('change'));",
            element, value
        )

    def fill_textarea_by_id(self, element_id, value):
        element = self.wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, f"#addRequestModal #{element_id}")))
        element.clear()
        element.send_keys(value)

    def test_user_can_create_booking_request_from_ui(self):
        self.login_user()

        purpose = f"Yêu cầu Selenium {int(time.time())}"
        request_date = (date.today() + timedelta(days=3)).isoformat()  # YYYY-MM-DD

        # Bước 1: Click vào tab "Yêu cầu đặt phòng" để hiện nội dung tab
        requests_tab = self.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, "//button[contains(normalize-space(), 'Yêu cầu đặt phòng')]")
            )
        )
        self.driver.execute_script("arguments[0].click();", requests_tab)

        # Bước 2: Chờ nút "Tạo yêu cầu" xuất hiện trong tab đang active
        create_btn = self.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, "//div[@id='requests']//button[contains(normalize-space(), 'Tạo yêu cầu')]")
            )
        )
        # Bước 3: Mở modal bằng JavaScript
        self.driver.execute_script("showModal('addRequestModal')")

        # Bước 4: Chờ modal hiển thị
        self.wait.until(EC.visibility_of_element_located((By.ID, "addRequestModal")))

        # Bước 5: Điền form
        self.select_first_real_option("roomId")
        self.select_first_real_option("courseId")
        self.fill_input_by_id("requestDate", request_date)
        self.fill_input_by_id("startTime", "08:00")
        self.fill_input_by_id("endTime", "10:00")
        self.fill_textarea_by_id("purpose", purpose)

        # Bước 6: Submit form
        submit_button = self.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, "//div[@id='addRequestModal']//button[@type='submit']")
            )
        )
        self.driver.execute_script("arguments[0].click();", submit_button)

        # Bước 7: Chờ redirect về dashboard
        self.wait.until(EC.url_contains("/dashboard"))
        self.wait.until(EC.presence_of_element_located((By.TAG_NAME, "body")))

        # Bước 8: Kiểm tra yêu cầu mới xuất hiện trong trang
        self.assertIn("Tạo yêu cầu đặt phòng thành công", self.driver.page_source)


if __name__ == "__main__":
    unittest.main(verbosity=2)
