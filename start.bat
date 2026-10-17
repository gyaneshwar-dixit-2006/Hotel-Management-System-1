@echo off
title Grand Lux Hotel Management System Server
color 0A
echo ===================================================
echo     Grand Lux Hotel Management System
echo ===================================================
echo.
echo Starting local PHP Development Server on http://127.0.0.1:8000 ...
echo Press Ctrl+C anytime to stop the server.
echo.
php -c php.ini -S 127.0.0.1:8000
pause
