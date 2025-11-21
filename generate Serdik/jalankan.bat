@echo off
echo ============================================================
echo   GENERATOR SERTIFIKAT PENDIDIK - STK YAKOBUS MERAUKE
echo ============================================================
echo.
echo Memulai aplikasi...
echo.

REM Check if Python is installed
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Python tidak terinstall!
    echo.
    echo Silakan install Python terlebih dahulu dari:
    echo https://www.python.org/downloads/
    echo.
    echo PENTING: Centang "Add Python to PATH" saat install!
    echo.
    pause
    exit /b 1
)

REM Create necessary folders (ignore errors)
echo Membuat folder yang diperlukan...
if not exist "uploads" mkdir uploads 2>nul
if not exist "output" mkdir output 2>nul

REM Test write permission (but don't exit if failed)
echo. > uploads\test.tmp 2>nul
if %errorlevel% neq 0 (
    echo.
    echo ============================================================
    echo   PERINGATAN: Permission Issue Detected!
    echo ============================================================
    echo.
    echo Solusi: Jalankan aplikasi sebagai Administrator
    echo   1. Tutup jendela ini
    echo   2. Klik kanan "jalankan.bat"
    echo   3. Pilih "Run as Administrator"
    echo.
    echo ATAU aplikasi akan mencoba berjalan dengan limited permissions...
    echo ============================================================
    echo.
    timeout /t 5 /nobreak
) else (
    del uploads\test.tmp 2>nul
    echo Folder permissions OK!
)

REM Install dependencies
echo.
echo Mengecek dependencies...
python -m pip install -q -r requirements.txt 2>nul

REM Run the application
echo.
echo ============================================================
echo   Aplikasi siap digunakan!
echo   Buka browser dan akses: http://localhost:5000
echo.
echo   Tekan CTRL+C untuk menghentikan aplikasi
echo ============================================================
echo.

python app.py

echo.
echo ============================================================
echo   Aplikasi telah dihentikan
echo ============================================================
pause

