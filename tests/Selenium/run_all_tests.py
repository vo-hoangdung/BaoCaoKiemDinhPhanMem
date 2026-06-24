"""
Chạy toàn bộ Selenium tests cho Classroom Management System.
Usage: python tests/Selenium/run_all_tests.py [--headless]
"""

import sys
import unittest
import importlib.util
import os

# Patch ChromeOptions để thêm --headless nếu muốn
HEADLESS = "--headless" in sys.argv

if HEADLESS:
    from selenium import webdriver
    _orig_init = webdriver.ChromeOptions.__init__

    def _patched_init(self):
        _orig_init(self)
        self.add_argument("--headless=new")
        self.add_argument("--no-sandbox")
        self.add_argument("--disable-dev-shm-usage")

    webdriver.ChromeOptions.__init__ = _patched_init

TESTS_DIR = os.path.dirname(__file__)

modules = [
    ("selenium_login",          "tests/Selenium/selenium_login.py"),
    ("selenium_room",           "tests/Selenium/selenium_room.py"),
    ("selenium_booking_request","tests/Selenium/selenium_booking_request.py"),
    ("selenium_course",          "tests/Selenium/selenium_course.py"),
]

loader = unittest.TestLoader()
suite  = unittest.TestSuite()

for mod_name, rel_path in modules:
    abs_path = os.path.join(os.path.dirname(__file__), os.path.basename(rel_path))
    spec = importlib.util.spec_from_file_location(mod_name, abs_path)
    mod  = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    suite.addTests(loader.loadTestsFromModule(mod))

runner = unittest.TextTestRunner(verbosity=2, stream=sys.stdout)
result = runner.run(suite)

sys.exit(0 if result.wasSuccessful() else 1)
