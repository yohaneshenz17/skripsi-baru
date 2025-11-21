# 🔧 TROUBLESHOOTING: Permission Denied Error

## ❌ Error: "Access is denied: 'uploads'"

Ini adalah masalah permission di Windows. Aplikasi tidak bisa membuat atau menulis ke folder.

---

## ✅ SOLUSI (Pilih salah satu yang paling mudah):

### **SOLUSI 1: Run as Administrator** ⭐ (Paling Mudah)

1. **Tutup aplikasi** jika sedang berjalan (CTRL+C)
2. **Klik kanan** file `jalankan.bat`
3. Pilih **"Run as Administrator"**
4. Klik "Yes" jika ada prompt UAC
5. Aplikasi akan jalan dengan permission penuh

✅ **Ini solusi tercepat dan paling aman!**

---

### **SOLUSI 2: Manual Create Folders**

1. **Tutup aplikasi** jika sedang berjalan
2. Buka folder `sertifikat-generator`
3. **Buat 2 folder baru** (klik kanan → New → Folder):
   - Nama: `uploads`
   - Nama: `output`
4. **Klik kanan folder `uploads`** → Properties
5. Tab **Security** → Klik **Edit**
6. Pilih user Anda → Centang **"Full control"**
7. Klik OK
8. Ulangi langkah 4-7 untuk folder `output`
9. Jalankan ulang aplikasi

---

### **SOLUSI 3: Change Folder Location**

Jika masih error, pindahkan folder aplikasi ke lokasi yang lebih permissive:

**DARI:**
```
C:\Program Files\sertifikat-generator
```

**KE:**
```
C:\Users\[YourUsername]\Documents\sertifikat-generator
```

Atau:
```
D:\sertifikat-generator
```

✅ **Folder di Documents atau drive D: biasanya tidak ada masalah permission**

---

### **SOLUSI 4: Disable Read-Only**

1. Klik kanan folder `sertifikat-generator`
2. Properties
3. **Uncheck "Read-only"**
4. Klik **Apply**
5. Pilih **"Apply to subfolders and files"**
6. Klik OK
7. Jalankan ulang aplikasi

---

## 🔍 **PENYEBAB UMUM:**

### 1. Folder di lokasi protected (C:\Program Files)
→ Pindahkan ke Documents atau drive lain

### 2. Antivirus memblokir
→ Tambahkan folder ke whitelist antivirus
→ Atau disable sementara (jangan lupa enable lagi!)

### 3. OneDrive sync conflict
→ Pindahkan folder keluar dari OneDrive

### 4. User tidak punya admin rights
→ Run as Administrator atau minta IT admin

---

## ⚠️ **JIKA SEMUA SOLUSI GAGAL:**

### Plan B: Edit app.py untuk custom location

1. Buka `app.py` dengan Notepad
2. Cari baris (sekitar baris 22-23):
   ```python
   app.config['UPLOAD_FOLDER'] = 'uploads'
   app.config['OUTPUT_FOLDER'] = 'output'
   ```
3. Ubah menjadi path absolut:
   ```python
   app.config['UPLOAD_FOLDER'] = 'C:/Users/YourUsername/Desktop/uploads'
   app.config['OUTPUT_FOLDER'] = 'C:/Users/YourUsername/Desktop/output'
   ```
4. Ganti `YourUsername` dengan username Windows Anda
5. Buat folder `uploads` dan `output` di Desktop
6. Save dan jalankan ulang

---

## 📝 **CATATAN PENTING:**

### Windows Permission Levels:
- ✅ **Documents** → Safe, no permission issues
- ✅ **Desktop** → Safe, no permission issues
- ✅ **D:\ or E:\** → Safe, no permission issues
- ⚠️ **Downloads** → Sometimes protected by browser
- ❌ **C:\Program Files** → Protected, need admin
- ❌ **C:\Windows** → Protected, need admin

### Best Practice:
1. **Selalu jalankan dari folder di Documents atau Desktop**
2. **Avoid C:\Program Files**
3. **Run as Administrator jika ragu**

---

## 🆘 **MASIH BERMASALAH?**

Jika setelah semua solusi di atas masih error:

1. **Screenshot error message**
2. **Screenshot lokasi folder** (address bar di File Explorer)
3. **Catat Windows version** (Win 10 atau Win 11)
4. **Cek apakah user account punya admin rights**
5. Hubungi IT support dengan info di atas

---

## ✅ **PREVENTION (Untuk Next Time):**

1. **Extract aplikasi di Documents atau Desktop**
2. **Run as Administrator dari awal**
3. **Check folder permissions sebelum jalankan**
4. **Disable OneDrive sync untuk folder aplikasi**

---

**🎯 Recommended: SELALU gunakan "Run as Administrator" untuk aplikasi ini!**
