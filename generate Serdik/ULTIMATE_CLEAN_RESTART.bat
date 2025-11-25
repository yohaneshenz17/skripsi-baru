@echo off
cls
color 0E
echo.
echo ============================================================
echo   ULTIMATE CLEAN RESTART - GENERATOR SERTIFIKAT
echo ============================================================
echo.
echo PERINGATAN: Script ini akan menghapus SEMUA cache!
echo.
pause

REM Step 1: Kill ALL Python processes
echo.
echo [1/6] Killing ALL Python processes...
taskkill /F /IM python.exe /T 2>nul
taskkill /F /IM python3.exe /T 2>nul
taskkill /F /IM pythonw.exe /T 2>nul
timeout /t 3 /nobreak >nul
echo      DONE!
echo.

REM Step 2: Hapus folder uploads lokal
echo [2/6] Cleaning LOCAL uploads folder...
if exist uploads (
    rmdir /s /q uploads 2>nul
)
mkdir uploads 2>nul
echo      DONE!
echo.

REM Step 3: Hapus folder output lokal
echo [3/6] Cleaning LOCAL output folder...
if exist output (
    rmdir /s /q output 2>nul
)
mkdir output 2>nul
echo      DONE!
echo.

REM Step 4: Hapus SEMUA temp folders dari aplikasi
echo [4/6] Cleaning TEMP folders...
echo      This may take a while...

REM Cari dan hapus semua folder sertifikat_* di temp
for /d %%G in ("%TEMP%\sertifikat_*") do (
    echo      Deleting: %%G
    rmdir /s /q "%%G" 2>nul
)

REM Cari dan hapus semua folder tmp* yang dibuat Flask
for /d %%G in ("%TEMP%\tmp*") do (
    rmdir /s /q "%%G" 2>nul
)

echo      DONE!
echo.

REM Step 5: Verifikasi file app.py
echo [5/6] Verifying app.py...
if not exist app.py (
    echo      ERROR: app.py not found!
    if exist app_FINAL_v1.3.py (
        echo      Using app_FINAL_v1.3.py...
        copy app_FINAL_v1.3.py app.py >nul
    ) else (
        echo      FATAL ERROR: No app.py found!
        pause
        exit /b 1
    )
)

REM Cek versi app.py
findstr /C:"VERSION 1.3" app.py >nul
if %errorlevel% neq 0 (
    echo      WARNING: app.py bukan versi 1.3!
    echo      Pastikan Anda menggunakan app_FINAL_v1.3.py
    echo.
    pause
)

echo      DONE!
echo.

REM Step 6: Start aplikasi
echo [6/6] Starting application...
echo.
echo ============================================================
echo   CLEAN RESTART COMPLETED!
echo ============================================================
echo.
echo   IMPORTANT STEPS:
echo.
echo   1. Buka browser: http://localhost:5000
echo   2. Hard Refresh: CTRL + SHIFT + R (atau CTRL + F5)
echo   3. Upload SEMUA files dari AWAL
echo   4. Input nomor seri: 0000999
echo   5. Klik "Upload Semua File"
echo   6. Tunggu notifikasi "sukses"
echo   7. Klik "Generate Sertifikat"
echo   8. Download dan cek!
echo.
echo   CRITICAL: Aplikasi sekarang akan menyimpan files di
echo   folder "uploads" dan "output" di direktori ini.
echo   
echo   Jika masih ke TEMP, jalankan aplikasi sebagai Administrator:
echo   Klik kanan file ini ^> "Run as Administrator"
echo.
echo   Press CTRL+C to stop application
echo.
echo ============================================================
echo.

REM Jalankan aplikasi
python app.py

if errorlevel 1 (
    echo.
    echo ============================================================
    echo   ERROR: Python not found or application crashed!
    echo ============================================================
    echo.
    echo Solusi:
    echo 1. Install Python dari: https://www.python.org/downloads/
    echo 2. Centang "Add Python to PATH" saat install
    echo 3. Restart Command Prompt
    echo.
    pause
    exit /b 1
)

echo.
echo ============================================================
echo   APPLICATION STOPPED
echo ============================================================
echo.
pause