@echo off
REM Migration and Seeding Script for Translation Management Service
REM This script runs migrations and seeders

echo ========================================
echo Translation Management Service
echo Migration and Seeding Script
echo ========================================
echo.

REM Try to find PHP in Laragon
set PHP_PATH=
if exist "C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe" (
    set PHP_PATH=C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe
) else if exist "C:\laragon\bin\php\php-8.3.0-Win32-vs16-x64\php.exe" (
    set PHP_PATH=C:\laragon\bin\php\php-8.3.0-Win32-vs16-x64\php.exe
) else (
    REM Try to find any PHP version in Laragon
    for /d %%i in ("C:\laragon\bin\php\*") do (
        if exist "%%i\php.exe" (
            set PHP_PATH=%%i\php.exe
            goto :found
        )
    )
)

:found
if "%PHP_PATH%"=="" (
    echo ERROR: PHP not found in Laragon!
    echo Please ensure Laragon is installed and PHP is available.
    echo.
    echo You can also run these commands manually:
    echo   1. Open Laragon Terminal
    echo   2. Navigate to: cd c:\laragon\www\translation-management-service
    echo   3. Run: composer install (if not already done)
    echo   4. Run: php artisan migrate
    echo   5. Run: php artisan db:seed
    pause
    exit /b 1
)

echo Found PHP at: %PHP_PATH%
echo.

REM Check if vendor directory exists
if not exist "vendor" (
    echo WARNING: vendor directory not found!
    echo Please run 'composer install' first.
    echo.
    echo In Laragon Terminal, run:
    echo   cd c:\laragon\www\translation-management-service
    echo   composer install
    pause
    exit /b 1
)

echo Running migrations...
echo.
%PHP_PATH% artisan migrate

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERROR: Migration failed!
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo Running seeders...
echo.
%PHP_PATH% artisan db:seed

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERROR: Seeding failed!
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo ========================================
echo SUCCESS: Migrations and seeders completed!
echo ========================================
echo.
pause
