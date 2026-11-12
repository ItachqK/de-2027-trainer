@echo off
chcp 65001 >nul
setlocal
cd /d "%~dp0"
set PORT=8100

where php >nul 2>nul
if %errorlevel%==0 (
    if not exist "database\database.sqlite" (
        type nul > "database\database.sqlite"
        if exist "storage\app\installed" del "storage\app\installed"
    )
    if not exist "storage\app\installed" (
        php artisan migrate --seed --force
        echo installed > "storage\app\installed"
    ) else (
        php artisan migrate --force
    )
    start "" cmd /c "timeout /t 3 >nul & start http://127.0.0.1:%PORT%/"
    php artisan serve --host=127.0.0.1 --port=%PORT%
    goto :end
)

if exist "C:\OSPanel\bin\php.bat" (
    if not exist "database\database.sqlite" (
        type nul > "database\database.sqlite"
        if exist "storage\app\installed" del "storage\app\installed"
    )
    if not exist "storage\app\installed" (
        call "C:\OSPanel\bin\php.bat" artisan migrate --seed --force
        echo installed > "storage\app\installed"
    ) else (
        call "C:\OSPanel\bin\php.bat" artisan migrate --force
    )
    start "" cmd /c "timeout /t 3 >nul & start http://127.0.0.1:%PORT%/"
    call "C:\OSPanel\bin\php.bat" artisan serve --host=127.0.0.1 --port=%PORT%
    goto :end
)

echo PHP не найден. Установите PHP 8.3+ и Composer.
pause

:end
endlocal
