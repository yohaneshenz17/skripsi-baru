#!/bin/bash

echo "============================================================"
echo "  GENERATOR SERTIFIKAT PENDIDIK - STK YAKOBUS MERAUKE"
echo "============================================================"
echo ""
echo "Memulai aplikasi..."
echo ""

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo "[ERROR] Python tidak terinstall!"
    echo ""
    echo "Silakan install Python terlebih dahulu:"
    echo "  - macOS: brew install python3"
    echo "  - Linux: sudo apt install python3 python3-pip"
    echo ""
    exit 1
fi

# Create necessary directories
mkdir -p uploads output

# Install requirements
echo "Mengecek dependencies..."
pip3 install -q -r requirements.txt

# Run the application
echo ""
echo "============================================================"
echo "  Aplikasi siap digunakan!"
echo "  Buka browser dan akses: http://localhost:5000"
echo ""
echo "  Tekan CTRL+C untuk menghentikan aplikasi"
echo "============================================================"
echo ""

python3 app.py
