# 🎓 Generator Sertifikat Pendidik
## Sekolah Tinggi Katolik Santo Yakobus Merauke

Aplikasi standalone untuk generate sertifikat pendidik secara batch dengan fitur lengkap:
- ✅ Batch processing (generate ratusan sertifikat sekaligus)
- ✅ Auto-increment nomor seri
- ✅ Overlay tanda tangan & QR code digital
- ✅ Extract data dari PDF UKMPPG
- ✅ User-friendly web interface
- ✅ Download individual atau batch (ZIP)

---

## 📋 Persyaratan Sistem

- **Windows 10/11**, **macOS**, atau **Linux**
- **Python 3.8 atau lebih baru**
- **Browser modern** (Chrome, Firefox, Edge, Safari)
- **RAM minimal 4GB** (8GB direkomendasikan untuk batch besar)

---

## 🚀 Cara Instalasi & Menjalankan

### 1️⃣ Install Python

**Jika belum punya Python:**

**Windows:**
- Download Python dari: https://www.python.org/downloads/
- Jalankan installer
- ✅ **PENTING:** Centang "Add Python to PATH"
- Klik "Install Now"

**macOS:**
```bash
brew install python3
```

**Linux (Ubuntu/Debian):**
```bash
sudo apt update
sudo apt install python3 python3-pip
```

### 2️⃣ Verifikasi Python Terinstall

Buka **Terminal** (macOS/Linux) atau **Command Prompt** (Windows), ketik:

```bash
python --version
```

atau

```bash
python3 --version
```

Harus muncul: `Python 3.x.x`

### 3️⃣ Extract Aplikasi

Extract file `sertifikat-generator.zip` ke folder yang mudah diakses, misalnya:
- Windows: `C:\sertifikat-generator`
- macOS/Linux: `~/sertifikat-generator`

### 4️⃣ Install Dependencies

Buka Terminal/Command Prompt, masuk ke folder aplikasi:

**Windows:**
```cmd
cd C:\sertifikat-generator
python -m pip install -r requirements.txt
```

**macOS/Linux:**
```bash
cd ~/sertifikat-generator
pip3 install -r requirements.txt
```

⏱️ **Proses ini memakan waktu 2-5 menit tergantung koneksi internet.**

### 5️⃣ Jalankan Aplikasi

Di folder yang sama, jalankan:

**Windows:**
```cmd
python app.py
```

**macOS/Linux:**
```bash
python3 app.py
```

✅ **Aplikasi berjalan!** Anda akan melihat:

```
============================================================
🎓 GENERATOR SERTIFIKAT PENDIDIK - STK YAKOBUS MERAUKE
============================================================

✅ Aplikasi berjalan di: http://localhost:5000
📝 Buka browser dan akses URL di atas

⚠️  Tekan CTRL+C untuk menghentikan aplikasi
============================================================
```

### 6️⃣ Buka di Browser

Buka browser dan ketik di address bar:
```
http://localhost:5000
```

🎉 **Selesai!** Aplikasi siap digunakan.

---

## 📖 Cara Penggunaan

### Step-by-Step:

#### **STEP 1: Upload Blanko Sertifikat**
- Klik area upload atau drag & drop
- Upload file **blanko sertifikat kosong** (PDF) sebagai background

#### **STEP 2: Upload PDF dari UKMPPG**
- Klik area upload atau drag & drop
- Pilih **SEMUA file PDF** dari UKMPPG sekaligus (bisa 10, 50, 100+ files)
- **Cara pilih multiple files:**
  - Windows: Tahan `Ctrl` sambil klik file-file yang mau dipilih
  - macOS: Tahan `Cmd` sambil klik file-file yang mau dipilih
  - Atau: `Ctrl+A` / `Cmd+A` untuk pilih semua

#### **STEP 3: Upload Tanda Tangan**
- Upload **TTD Ketua** (format PNG/JPG)
- Upload **TTD Ketua Program Studi** (format PNG/JPG)
- **Tips:** Gunakan tanda tangan dengan background transparan (PNG) untuk hasil terbaik

#### **STEP 4: Upload QR Code Segel Elektronik**
- Upload **QR Code Ketua** (format PNG/JPG)
- Upload **QR Code Kaprodi** (format PNG/JPG)
- QR code yang sama akan digunakan untuk semua sertifikat

#### **STEP 5: Input Nomor Seri**
- Masukkan nomor seri awal (7 digit)
- Contoh: `0000900`
- Nomor akan otomatis increment:
  - File 1: 0000900
  - File 2: 0000901
  - File 3: 0000902
  - dst...

#### **STEP 6: Generate!**
1. Klik **"📤 Upload Semua File"**
   - Tunggu hingga muncul notifikasi sukses
2. Klik **"⚙️ Generate Sertifikat"**
   - Tunggu proses generate selesai (1-2 menit untuk 100 files)
3. Klik **"📥 Download Semua (ZIP)"** untuk download semua sertifikat
   - Atau download individual dari tabel

#### **STEP 7: Selesai!**
- File ZIP berisi semua sertifikat dalam format PDF
- Extract ZIP dan file siap digunakan

---

## 📁 Struktur File Hasil

Setelah generate, file akan tersimpan di folder `output/`:

```
output/
├── Sertifikat_24869050864_ABALINDA_THEODOSIA_MANIPADA.pdf
├── Sertifikat_24869051234_AGNES_KUSI_SNOE_OKI.pdf
├── ...
└── Sertifikat_Batch_20241120_143022.zip  (semua file dalam 1 ZIP)
```

**Format nama file:**
- `Sertifikat_{NIM}_{NAMA}.pdf`

---

## 🔧 Troubleshooting

### ❌ "Python tidak dikenali" atau "command not found"

**Solusi:**
- Python belum terinstall atau belum masuk PATH
- Install ulang Python, pastikan centang "Add Python to PATH"
- Restart Command Prompt/Terminal setelah install

### ❌ "pip tidak dikenali"

**Solusi:**
- Gunakan: `python -m pip install -r requirements.txt`
- Atau: `python3 -m pip install -r requirements.txt`

### ❌ "Permission denied" (Linux/macOS)

**Solusi:**
```bash
chmod +x app.py
python3 app.py
```

### ❌ "Port 5000 sudah digunakan"

**Solusi:**
- Ada aplikasi lain yang menggunakan port 5000
- Edit file `app.py`, ubah baris terakhir:
  ```python
  app.run(debug=True, host='0.0.0.0', port=5001)  # ganti 5000 ke 5001
  ```
- Akses via: `http://localhost:5001`

### ❌ "File tidak bisa diupload" atau "Max file size exceeded"

**Solusi:**
- File terlalu besar (max 500MB total)
- Upload dalam batch yang lebih kecil (misalnya 50 files per batch)

### ❌ "Data tidak terextract dengan benar"

**Solusi:**
- Format PDF dari UKMPPG berbeda dari expected
- Hubungi developer untuk adjustment parsing logic

---

## 💡 Tips & Best Practices

### ✅ Persiapan File

1. **Blanko Sertifikat:**
   - Gunakan blanko yang sudah dalam format PDF
   - Pastikan resolusi cukup tinggi (minimal 150 DPI)

2. **Tanda Tangan:**
   - Format PNG dengan background transparan (recommended)
   - Ukuran file tidak terlalu besar (< 1MB per file)
   - Scan dengan resolusi 300 DPI

3. **QR Code:**
   - Format PNG atau JPG
   - Ukuran minimal 200x200 pixels
   - Background putih atau transparan

4. **PDF UKMPPG:**
   - Pastikan semua file valid dan bisa dibuka
   - Cek satu-satu jika ada file yang corrupt

### ✅ Batch Processing

- **Untuk < 50 files:** Generate sekaligus, cepat (1-2 menit)
- **Untuk 50-100 files:** Generate sekaligus, sekitar 3-5 menit
- **Untuk > 100 files:** 
  - Bagi menjadi beberapa batch
  - Setiap batch 50-100 files
  - Sesuaikan nomor seri untuk batch berikutnya

### ✅ Verifikasi Hasil

Setelah generate, **selalu cek sample sertifikat** untuk memastikan:
- ✅ Posisi nomor seri benar
- ✅ Data mahasiswa terextract dengan benar
- ✅ Tanda tangan & QR code muncul dengan jelas
- ✅ Layout tidak berantakan

---

## 🆘 Butuh Bantuan?

Jika mengalami masalah:

1. **Screenshoot error message** yang muncul
2. **Catat langkah-langkah** yang dilakukan sebelum error
3. Hubungi admin IT STK Yakobus

---

## 📞 Kontak

**STK Santo Yakobus Merauke**
- Website: https://stkyakobus.ac.id
- Email: admin@stkyakobus.ac.id

---

## 📄 Lisensi

© 2024 STK Santo Yakobus Merauke. All rights reserved.

Aplikasi ini dikembangkan khusus untuk internal STK Yakobus Merauke.

---

## 🔄 Changelog

### Version 1.0 (2024-11-20)
- ✅ Initial release
- ✅ Batch PDF processing
- ✅ Auto-increment nomor seri
- ✅ Overlay TTD & QR code
- ✅ Web interface
- ✅ ZIP download

---

**🎉 Selamat Menggunakan! 🎉**
