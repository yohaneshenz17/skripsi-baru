# 📦 Sample Data untuk Testing

Folder ini berisi file-file contoh untuk testing aplikasi.

## 📁 Isi Folder:

### 1. **Blanko_Serdik.pdf**
- Blanko sertifikat pendidik kosong
- Digunakan sebagai background/latar sertifikat
- **Cara pakai:** Upload di STEP 1

### 2. **File_PDF_Sertifikat_Pendidik_dari_website_UKMPPG.pdf**
- Contoh file PDF dari website UKMPPG
- Berisi data mahasiswa (nama, NIM, tempat/tanggal lahir, dll)
- **Cara pakai:** Upload di STEP 2 (bisa upload multiple files sekaligus)

### 3. **TTD_Ketua.png**
- Contoh tanda tangan Ketua
- Format: PNG dengan background transparan
- **Cara pakai:** Upload di STEP 3 (TTD Ketua)

### 4. **TTD_Kaprodi.png**
- Contoh tanda tangan Ketua Program Studi
- Format: PNG dengan background transparan
- **Cara pakai:** Upload di STEP 3 (TTD Kaprodi)

### 5. **QR_Code_Sample.png**
- Contoh QR Code segel elektronik
- **Cara pakai:** Upload di STEP 4 untuk QR Ketua DAN QR Kaprodi (bisa pakai file yang sama untuk testing)

---

## 🧪 Cara Test dengan Sample Data:

1. Jalankan aplikasi: `python app.py` atau double-click `jalankan.bat`
2. Buka browser: `http://localhost:5000`
3. Upload file-file di atas sesuai step yang diminta
4. Input nomor seri: `0000001`
5. Klik "Upload Semua File"
6. Klik "Generate Sertifikat"
7. Download hasilnya!

---

## ⚠️ Catatan:

- File-file ini hanya untuk **TESTING** aplikasi
- Untuk **PRODUCTION**, gunakan file asli Anda:
  - Blanko resmi dari institusi
  - PDF dari UKMPPG yang sebenarnya
  - Tanda tangan asli (scan dengan resolusi tinggi)
  - QR Code yang valid dari sistem segel elektronik

---

## 💡 Tips:

### Untuk Blanko:
- Gunakan scan dengan resolusi minimal 150 DPI
- Format PDF lebih baik dari JPG
- Pastikan warna dan kualitas cukup baik

### Untuk Tanda Tangan:
- Scan dengan resolusi 300 DPI
- Gunakan format PNG dengan background transparan
- Atau edit JPG untuk remove background (gunakan tool online)

### Untuk QR Code:
- Generate dari sistem segel elektronik resmi
- Minimal 200x200 pixels
- Background putih atau transparan

---

**🎉 Happy Testing! 🎉**
