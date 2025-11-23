@echo off
echo ========================================
echo GENERATOR SLIDESHOW YUDISIUM PPG 2025
echo ========================================
echo.

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python tidak ditemukan!
    echo.
    echo SOLUSI:
    echo 1. Download Python dari: https://www.python.org/downloads/
    echo 2. Saat install, CENTANG "Add Python to PATH"
    echo 3. Restart komputer setelah install
    echo 4. Jalankan script ini lagi
    echo.
    pause
    exit /b 1
)

echo [OK] Python terdeteksi!
echo.

echo [1/3] Menginstall dependency...
echo.

python -m pip install --upgrade pip >nul 2>&1

echo Installing pandas...
python -m pip install pandas --quiet
if errorlevel 1 goto error

echo Installing openpyxl...
python -m pip install openpyxl --quiet
if errorlevel 1 goto error

echo Installing python-pptx...
python -m pip install python-pptx --quiet
if errorlevel 1 goto error

echo Installing pillow...
python -m pip install pillow --quiet
if errorlevel 1 goto error

echo Installing requests...
python -m pip install requests --quiet
if errorlevel 1 goto error

echo.
echo [2/3] Dependency OK!
echo [3/3] Menjalankan script...
echo.

python generate_slideshow.py

if errorlevel 1 (
    echo.
    echo ERROR: Script gagal dijalankan!
    echo Periksa error message di atas.
    pause
    exit /b 1
)

echo.
echo ========================================
echo SELESAI!
echo ========================================
pause
exit /b 0

:error
echo.
echo ERROR: Gagal install dependency!
echo.
echo SOLUSI:
echo 1. Pastikan internet tersambung
echo 2. Nonaktifkan antivirus sementara
echo 3. Buka Command Prompt sebagai Administrator
echo 4. Jalankan: python -m pip install pandas openpyxl python-pptx pillow requests
echo.
pause
exit /b 1
