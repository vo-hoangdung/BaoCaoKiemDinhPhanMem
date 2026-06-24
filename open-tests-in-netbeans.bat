@echo off
setlocal
set "NETBEANS=%~dp0..\tools\NetBeans-30\netbeans\bin\netbeans64.exe"
set "JDK_HOME=C:\Program Files\Java\jdk-26"
set "PROJECT=%~dp0selenium-junit-tests"

if not exist "%NETBEANS%" (
  echo Khong tim thay NetBeans tai: %NETBEANS%
  exit /b 1
)

if exist "%JDK_HOME%\bin\java.exe" (
  start "" "%NETBEANS%" --jdkhome "%JDK_HOME%" --open "%PROJECT%"
) else (
  start "" "%NETBEANS%" --open "%PROJECT%"
)
