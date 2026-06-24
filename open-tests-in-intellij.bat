@echo off
setlocal
set "IDEA=%~dp0..\tools\IntelliJ-IDEA-Community\bin\idea64.exe"
set "PROJECT=%~dp0selenium-junit-tests"

if not exist "%IDEA%" (
  echo Khong tim thay IntelliJ IDEA tai: %IDEA%
  exit /b 1
)

start "" "%IDEA%" "%PROJECT%"
