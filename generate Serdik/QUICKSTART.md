# 🚀 QUICK START GUIDE
## Panduan Cepat 5 Menit

---

## ✅ LANGKAH 1: Install Python (Skip jika sudah punya)

### Windows:
1. Download Python: https://www.python.org/downloads/
2. Jalankan installer
3. **PENTING:** ✅ Centang "Add Python to PATH"
4. Klik "Install Now"
5. Tunggu hingga selesai

### macOS:
Buka Terminal, ketik:
```bash
brew install python3
```

### Linux:
Buka Terminal, ketik:
```bash
sudo apt update
sudo apt install python3 python3-pip
```

---

## ✅ LANGKAH 2: Jalankan Aplikasi

### Windows:
1. Extract folder `sertifikat-generator`
2. **Double-click** file `jalankan.bat`
3. Tunggu hingga muncul "Aplikasi siap digunakan"
4. Buka browser, ketik: `http://localhost:5000`

### macOS/Linux:
1. Extract folder `sertifikat-generator`
2. Buka Terminal di folder tersebut
3. Ketik: `./jalankan.sh`
4. Buka browser, ketik: `http://localhost:5000`

---

## ✅ LANGKAH 3: Upload & Generate

1. **Upload Blanko** → File PDF blanko kosong
2. **Upload PDF UKMPPG** → Pilih semua file PDF (Ctrl+A atau Cmd+A)
3. **Upload TTD** → 2 file PNG tanda tangan (Ketua & Kaprodi)
4. **Upload QR Code** → 2 file PNG QR code
5. **Input Nomor Seri** → 7 digit, contoh: `0000900`
6. Klik **"📤 Upload Semua File"**
7. Klik **"⚙️ Generate Sertifikat"**
8. Klik **"📥 Download Semua (ZIP)"**

---

## ✅ SELESAI! 🎉

File ZIP berisi semua sertifikat siap digunakan.

---

## ❓ Masalah?

### "Python tidak dikenali"
→ Python belum terinstall atau belum masuk PATH
→ Install ulang Python, centang "Add Python to PATH"

### "Port 5000 sudah digunakan"
→ Ada aplikasi lain di port 5000
→ Edit `app.py` baris terakhir, ganti `port=5000` jadi `port=5001`
→ Akses via `http://localhost:5001`

### File tidak bisa diupload
→ File terlalu besar (max 500MB)
→ Upload dalam batch lebih kecil

---

## 📞 Butuh Bantuan Lebih?

Baca **README.md** untuk panduan lengkap.

---

**🎉 Happy Generating! 🎉**
