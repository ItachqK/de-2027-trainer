@echo off
cd /d "%~dp0"

if not exist "DE-2027.exe" (
    echo DE-2027.exe not found.
    echo Extract the archive and run this file from the DE-2027 folder.
    echo.
    pause
    exit /b 1
)

start "" "DE-2027.exe"
exit /b 0
