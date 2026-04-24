@echo off
title AHFix StartLauncher Worker
cd /d D:\JOKO\xampp8.2\htdocs\AHFix

:loop
echo [%date% %time%] Running worker...
"D:\JOKO\xampp8.2\php\php.exe" worker_start_launcher.php
echo [%date% %time%] Done. Waiting 10s...
timeout /t 10 /nobreak >nul
goto loop