# GENERATOR SLIDESHOW YUDISIUM PPG 2025

Script otomatis untuk membuat slideshow PowerPoint yudisium dengan foto dan data mahasiswa.

## 📋 FITUR

- ✅ Download otomatis 2,254 foto dari Google Drive
- ✅ Generate PowerPoint dengan 2 mahasiswa per slide
- ✅ Format profesional sesuai contoh
- ✅ Total 1,127 slide
- ✅ Urutan sesuai SK Yudisium

## 🖥️ CARA INSTALL

### Opsi 1: Menggunakan Python (Windows/Mac/Linux)

1. **Install Python 3.8 atau lebih baru**
   - Download dari: https://www.python.org/downloads/
   - Saat install di Windows, centang "Add Python to PATH"

2. **Install dependency**
   
   Buka Command Prompt (Windows) atau Terminal (Mac/Linux), lalu jalankan:
   
   ```bash
   pip install pandas openpyxl python-pptx pillow requests
   ```

3. **Download script**
   - Download semua file ke satu folder:
     * generate_slideshow.py
     * Formulir_Lapor_Diri_Mahasiswa_PPG_2025_Batch_3.xlsx
     * README.txt (file ini)

## 🚀 CARA MENJALANKAN

### Windows:

1. Buka Command Prompt
2. Masuk ke folder tempat script berada:
   ```
   cd C:\Users\NamaAnda\Downloads\slideshow_generator
   ```
3. Jalankan script:
   ```
   python generate_slideshow.py
   ```

### Mac/Linux:

1. Buka Terminal
2. Masuk ke folder tempat script berada:
   ```
   cd ~/Downloads/slideshow_generator
   ```
3. Jalankan script:
   ```
   python3 generate_slideshow.py
   ```

## ⏱️ ESTIMASI WAKTU

- Download 2,254 foto: ~20-30 menit (tergantung kecepatan internet)
- Generate PowerPoint: ~5-10 menit
- **Total: ~25-40 menit**

## 📁 OUTPUT

Setelah selesai, Anda akan mendapat:

1. **Slideshow_Yudisium_2025.pptx** - File PowerPoint final (1,127 slide)
2. **foto_mahasiswa/** - Folder berisi 2,254 foto mahasiswa

## 🔧 TROUBLESHOOTING

### Error: "ModuleNotFoundError"
**Solusi:** Install ulang dependency:
```bash
pip install --upgrade pandas openpyxl python-pptx pillow requests
```

### Error: "Excel file not found"
**Solusi:** Pastikan file Excel ada di folder yang sama dengan script

### Download foto lambat/gagal
**Solusi:** 
- Cek koneksi internet
- Coba jalankan ulang script (foto yang sudah terdownload akan di-skip)
- Tutup aplikasi lain yang menggunakan bandwidth

### Foto tidak muncul di slide
**Solusi:**
- Cek folder `foto_mahasiswa/` apakah foto berhasil di-download
- Coba download manual foto yang gagal
- Letakkan di folder `foto_mahasiswa/` dengan nama: 0001.jpg, 0002.jpg, dst.

## 📞 SUPPORT

Jika ada masalah:
1. Screenshot error yang muncul
2. Kirim ke Claude AI untuk troubleshooting

## 📝 CATATAN

- Script akan membuat folder `foto_mahasiswa/` otomatis
- Jika download terputus, jalankan ulang script (akan melanjutkan dari terakhir)
- File PowerPoint final berukuran ~500MB - 1GB
- Pastikan ada ruang disk minimal 2GB

## ✅ CHECKLIST

Sebelum menjalankan script, pastikan:

- [ ] Python sudah terinstall
- [ ] Semua dependency sudah terinstall
- [ ] File Excel ada di folder yang sama
- [ ] Koneksi internet stabil
- [ ] Ruang disk tersedia minimal 2GB

---

**Dibuat oleh:** Claude AI Assistant
**Tanggal:** 22 November 2025
**Versi:** 1.0
