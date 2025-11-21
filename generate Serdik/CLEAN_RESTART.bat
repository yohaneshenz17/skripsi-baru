@echo off
cls
color 0A
echo.
echo ============================================================
echo   CLEAN RESTART - GENERATOR SERTIFIKAT
echo ============================================================
echo.

REM Step 1: Kill Python process (jika masih jalan)
echo [1/5] Stopping Python processes...
taskkill /F /IM python.exe 2>nul
taskkill /F /IM python3.exe 2>nul
timeout /t 2 /nobreak >nul
echo      DONE!
echo.

REM Step 2: Hapus semua isi folder uploads
echo [2/5] Cleaning uploads folder...
if exist uploads (
    del /Q uploads\* 2>nul
    for /d %%p in (uploads\*) do rmdir /s /q "%%p" 2>nul
)
if not exist uploads mkdir uploads
echo      DONE!
echo.

REM Step 3: Hapus semua isi folder output
echo [3/5] Cleaning output folder...
if exist output (
    del /Q output\* 2>nul
    for /d %%p in (output\*) do rmdir /s /q "%%p" 2>nul
)
if not exist output mkdir output
echo      DONE!
echo.

REM Step 4: Cek file app.py
echo [4/5] Checking app.py...
if not exist app.py (
    if exist app_FIXED_v1.2.py (
        echo      app.py not found, using app_FIXED_v1.2.py
        copy app_FIXED_v1.2.py app.py >nul
    ) else (
        echo      ERROR: No app.py or app_FIXED_v1.2.py found!
        pause
        exit /b 1
    )
)
echo      DONE!
echo.

REM Step 5: Start aplikasi
echo [5/5] Starting application...
echo.
echo ============================================================
echo   APPLICATION STARTED
echo ============================================================
echo.
echo   Open browser and go to: http://localhost:5000
echo.
echo   IMPORTANT:
echo   1. Press CTRL+F5 in browser to hard refresh
echo   2. Upload ALL files again
echo   3. Input new serial number
echo   4. Click Upload Semua File
echo   5. Click Generate Sertifikat
echo.
echo   Press CTRL+C to stop application
echo.
echo ============================================================
echo.

python app.py || python3 app.py || py app.py

if errorlevel 1 (
    echo.
    echo ============================================================
    echo   ERROR: Python not found!
    echo ============================================================
    echo.
    echo Please install Python from: https://www.python.org/downloads/
    echo Make sure to check "Add Python to PATH" during installation
    echo.
    pause
)

echo.
echo ============================================================
echo   APPLICATION STOPPED
echo ============================================================
pause