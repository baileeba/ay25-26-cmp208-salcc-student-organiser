@echo off
REM Windows Scheduled Task Setup Script for StudSort Reminders
REM This script creates a Windows scheduled task to trigger reminders hourly

setlocal enabledelayedexpansion

echo.
echo ======================================
echo StudSort Reminder Scheduler Setup
echo ======================================
echo.

REM Create scheduled task to run every hour
echo Creating scheduled task to trigger reminders every hour...

REM First, delete any existing task with this name
schtasks /delete /tn "StudSort Reminders" /f 2>nul

REM Create the new task
schtasks /create /tn "StudSort Reminders" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\ay25-26-cmp208-salcc-student-organiser\api\test_reminders.php" /sc hourly /st 08:00 /sd 05/21/2026 /ru SYSTEM /f

echo.
if !errorlevel! equ 0 (
    echo Task created successfully!
    echo Task Name: StudSort Reminders
    echo Frequency: Every hour starting at 08:00
    echo.
    echo To view the task:
    echo   schtasks /query /tn "StudSort Reminders" /v
    echo.
    echo To disable the task:
    echo   schtasks /change /tn "StudSort Reminders" /disable
    echo.
    echo To delete the task:
    echo   schtasks /delete /tn "StudSort Reminders" /f
) else (
    echo C:\xampp\htdocs\ay25-26-cmp208-salcc-student-organiser\setup_reminders.batError creating task. Run as Administrator.
)

pause
