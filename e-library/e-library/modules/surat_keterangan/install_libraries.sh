#!/bin/bash

###########################################################
# Script Helper untuk Download Library
# Modul Surat Keterangan - STK Santo Yakobus Merauke
###########################################################

echo "=========================================="
echo "DOWNLOAD LIBRARY UNTUK MODUL SURAT KETERANGAN"
echo "=========================================="
echo ""

# Warna output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Cek apakah script dijalankan di folder yang benar
if [ ! -f "config.php" ]; then
    echo -e "${RED}Error: Script harus dijalankan di folder surat_keterangan/${NC}"
    exit 1
fi

# Buat folder lib jika belum ada
mkdir -p lib
cd lib

echo -e "${YELLOW}[1/2] Downloading FPDF Library...${NC}"

# Download FPDF
if [ -d "fpdf" ]; then
    echo -e "${YELLOW}FPDF folder sudah ada. Melewati...${NC}"
else
    wget -q http://www.fpdf.org/en/download/fpdf184.zip -O fpdf.zip
    
    if [ $? -eq 0 ]; then
        unzip -q fpdf.zip -d fpdf_temp
        mv fpdf_temp/* fpdf/
        rm -rf fpdf_temp fpdf.zip
        echo -e "${GREEN}✓ FPDF berhasil didownload${NC}"
    else
        echo -e "${RED}✗ Gagal download FPDF. Download manual dari: http://www.fpdf.org/${NC}"
    fi
fi

echo ""
echo -e "${YELLOW}[2/2] Downloading PHP QR Code Library...${NC}"

# Download PHP QR Code
if [ -d "phpqrcode" ]; then
    echo -e "${YELLOW}PHP QR Code folder sudah ada. Melewati...${NC}"
else
    wget -q "https://sourceforge.net/projects/phpqrcode/files/phpqrcode/php-qrcode-2010121412/phpqrcode-2010121412.zip/download" -O phpqrcode.zip
    
    if [ $? -eq 0 ]; then
        unzip -q phpqrcode.zip
        mv phpqrcode phpqrcode_lib
        mkdir phpqrcode
        mv phpqrcode_lib/* phpqrcode/
        rm -rf phpqrcode_lib phpqrcode.zip
        echo -e "${GREEN}✓ PHP QR Code berhasil didownload${NC}"
    else
        echo -e "${RED}✗ Gagal download PHP QR Code. Download manual dari: http://phpqrcode.sourceforge.net/${NC}"
    fi
fi

cd ..

echo ""
echo "=========================================="
echo -e "${GREEN}SELESAI!${NC}"
echo "=========================================="
echo ""

# Verifikasi file
echo "Verifying installation..."
echo ""

if [ -f "lib/fpdf/fpdf.php" ]; then
    echo -e "${GREEN}✓ FPDF: lib/fpdf/fpdf.php${NC}"
else
    echo -e "${RED}✗ FPDF: File tidak ditemukan!${NC}"
fi

if [ -f "lib/phpqrcode/qrlib.php" ]; then
    echo -e "${GREEN}✓ PHP QR Code: lib/phpqrcode/qrlib.php${NC}"
else
    echo -e "${RED}✗ PHP QR Code: File tidak ditemukan!${NC}"
fi

echo ""
echo "=========================================="
echo "LANGKAH SELANJUTNYA:"
echo "=========================================="
echo "1. Set permission folder:"
echo "   chmod 777 pdf_generated/"
echo "   chmod 777 pdf_generated/temp/"
echo ""
echo "2. Upload file assets:"
echo "   - assets/images/stk.png (logo)"
echo "   - assets/images/ttd_yuli.png (tanda tangan)"
echo ""
echo "3. Import database:"
echo "   - File: surat_keterangan_table.sql"
echo ""
echo "4. Edit config.php sesuaikan path"
echo ""
echo "5. Buka browser: http://your-domain/e-library/modules/surat_keterangan/"
echo ""
echo -e "${GREEN}SELAMAT! Modul siap digunakan.${NC}"
echo ""
