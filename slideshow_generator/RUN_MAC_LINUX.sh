#!/bin/bash

echo "========================================"
echo "GENERATOR SLIDESHOW YUDISIUM PPG 2025"
echo "========================================"
echo

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo "ERROR: Python3 tidak ditemukan!"
    echo "Silakan install Python dari: https://www.python.org/downloads/"
    echo
    read -p "Tekan ENTER untuk keluar..."
    exit 1
fi

echo "[1/3] Mengecek dependency..."
if ! python3 -c "import pandas" 2>/dev/null; then
    echo
    echo "Installing dependencies..."
    pip3 install -r requirements.txt
    if [ $? -ne 0 ]; then
        echo
        echo "ERROR: Gagal install dependency!"
        read -p "Tekan ENTER untuk keluar..."
        exit 1
    fi
fi

echo "[2/3] Dependency OK!"
echo "[3/3] Menjalankan script..."
echo

python3 generate_slideshow.py

echo
read -p "Tekan ENTER untuk keluar..."
