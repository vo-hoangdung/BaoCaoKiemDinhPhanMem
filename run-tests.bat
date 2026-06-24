@echo off
setlocal

set "PROJECT_DIR=%~dp0"
set "MAVEN=%PROJECT_DIR%..\tools\apache-maven-3.9.16\bin\mvn.cmd"

cd /d "%PROJECT_DIR%"

if not exist "%MAVEN%" (
  echo Khong tim thay Maven tai: %MAVEN%
  exit /b 1
)

echo Dang chay JUnit + Selenium tests...
"%MAVEN%" -f "selenium-junit-tests\pom.xml" test
if errorlevel 1 exit /b %errorlevel%

echo.
echo Dang tao Test Report...
powershell -ExecutionPolicy Bypass -File "selenium-junit-tests\generate-test-report.ps1"
if errorlevel 1 exit /b %errorlevel%

echo.
echo Hoan tat. Report nam tai:
echo %PROJECT_DIR%selenium-junit-tests\TEST-REPORT.md
