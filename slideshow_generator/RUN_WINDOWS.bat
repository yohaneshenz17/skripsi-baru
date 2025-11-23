@echo off
echo ========================================
echo GENERATOR SLIDESHOW YUDISIUM PPG 2025
echo ========================================
echo.

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python tidak ditemukan!
    echo Silakan install Python dari: https://www.python.org/downloads/
    echo.
    pause
    exit /b 1
)

echo [1/3] Mengecek dependency...
pip show pandas >nul 2>&1
if errorlevel 1 (
    echo.
    echo Installing dependencies...
    pip install -r requirements.txt
    if errorlevel 1 (
        echo.
        echo ERROR: Gagal install dependency!
        pause
        exit /b 1
    )
)

echo [2/3] Dependency OK!
echo [3/3] Menjalankan script...
echo.

python generate_slideshow.py

echo.
pause
