<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// =====================================================
//      PENAMBAHAN ROUTE UNTUK PENGUMUMAN (BARU)
// =====================================================
$route['pengumuman'] = 'home/pengumuman';

// Auth routes
$route['auth'] = 'auth/index';
$route['auth/login'] = 'auth/login';
$route['auth/logout'] = 'auth/logout';
$route['auth/cek/(:num)/(:num)'] = 'auth/cek/$1/$2';

// API routes
$route['api/auth/login'] = 'api/auth/login';

// Admin routes
$route['admin'] = 'admin/dashboard';
$route['lihat-selengkapnya/(:num)'] = 'admin/dosen/lihat_selengkapnya/$1';
$route['admin/(:any)'] = 'admin/$1';

// Dosen routes
$route['dosen'] = 'dosen/dashboard';
$route['dosen/(:any)'] = 'dosen/$1';

// =====================================================
//      MAHASISWA ROUTES - ROUTING SPESIFIK (TAMBAHAN BARU)
// =====================================================

// Dashboard
$route['mahasiswa'] = 'mahasiswa/dashboard';

// =====================================================
//      BIMBINGAN ROUTES - PRIORITAS TINGGI (SEBELUM GENERIC)
// =====================================================

// Bimbingan - Routing Spesifik (HARUS SEBELUM GENERIC!)
$route['mahasiswa/bimbingan'] = 'mahasiswa/bimbingan/index';
$route['mahasiswa/bimbingan/tambah_jurnal'] = 'mahasiswa/bimbingan/tambah_jurnal';
$route['mahasiswa/bimbingan/edit_jurnal/(:num)'] = 'mahasiswa/bimbingan/edit_jurnal/$1';
$route['mahasiswa/bimbingan/hapus_jurnal/(:num)'] = 'mahasiswa/bimbingan/hapus_jurnal/$1';
$route['mahasiswa/bimbingan/detail_jurnal/(:num)'] = 'mahasiswa/bimbingan/detail_jurnal/$1';
$route['mahasiswa/bimbingan/export_jurnal'] = 'mahasiswa/bimbingan/export_jurnal';

// =====================================================
//      GENERIC ROUTING (HARUS DI BAWAH ROUTING SPESIFIK)
// =====================================================

// Generic routing untuk controller/method lainnya
$route['mahasiswa/(:any)'] = 'mahasiswa/$1';

// ⚠️ PENTING: Routing spesifik HARUS ditempatkan SEBELUM routing generic
// agar CodeIgniter bisa resolve URL dengan benar

// =====================================================
//      ROUTE KAPRODI - WORKFLOW TERBARU
// =====================================================

// Dashboard (menggunakan controller Dashboard langsung)
$route['kaprodi'] = 'kaprodi/dashboard';
$route['kaprodi/dashboard'] = 'kaprodi/dashboard';
$route['kaprodi/dashboard/(:any)'] = 'kaprodi/dashboard/$1';

// =====================================================
//      MENU KAPRODI - URUTAN SESUAI SIDEBAR BARU
// =====================================================

// 1. Dashboard (sudah di atas)

// 2. Pengumuman Tahapan
$route['kaprodi/pengumuman'] = 'kaprodi/pengumuman';
$route['kaprodi/pengumuman/(:any)'] = 'kaprodi/pengumuman/$1';
$route['kaprodi/pengumuman/tambah'] = 'kaprodi/pengumuman/tambah';
$route['kaprodi/pengumuman/edit/(:num)'] = 'kaprodi/pengumuman/edit/$1';
$route['kaprodi/pengumuman/hapus/(:num)'] = 'kaprodi/pengumuman/hapus/$1';

// 3. Usulan Proposal (sebelumnya Penetapan Pembimbing)
$route['kaprodi/proposal'] = 'kaprodi/kaprodi/proposal';                    // List proposal
$route['kaprodi/review_proposal/(:num)'] = 'kaprodi/kaprodi/review_proposal/$1';  // Review detail
$route['kaprodi/proses_review'] = 'kaprodi/kaprodi/proses_review';          // Submit review (POST)
$route['kaprodi/penetapan/(:num)'] = 'kaprodi/kaprodi/penetapan/$1';        // Form penetapan LAMA
$route['kaprodi/simpan_penetapan'] = 'kaprodi/kaprodi/simpan_penetapan';    // Simpan penetapan (POST)

// 4. Seminar Proposal (BARU)
$route['kaprodi/seminar_proposal'] = 'kaprodi/seminar_proposal';
$route['kaprodi/seminar_proposal/(:any)'] = 'kaprodi/seminar_proposal/$1';
$route['kaprodi/seminar_proposal/detail/(:num)'] = 'kaprodi/seminar_proposal/detail/$1';
$route['kaprodi/seminar_proposal/validasi'] = 'kaprodi/seminar_proposal/validasi';
$route['kaprodi/seminar_proposal/jadwal'] = 'kaprodi/seminar_proposal/jadwal';
$route['kaprodi/seminar_proposal/tetapkan_penguji'] = 'kaprodi/seminar_proposal/tetapkan_penguji';

// 5. Seminar Skripsi (BARU)
$route['kaprodi/seminar_skripsi'] = 'kaprodi/seminar_skripsi';
$route['kaprodi/seminar_skripsi/(:any)'] = 'kaprodi/seminar_skripsi/$1';
$route['kaprodi/seminar_skripsi/detail/(:num)'] = 'kaprodi/seminar_skripsi/detail/$1';
$route['kaprodi/seminar_skripsi/validasi'] = 'kaprodi/seminar_skripsi/validasi';
$route['kaprodi/seminar_skripsi/jadwal'] = 'kaprodi/seminar_skripsi/jadwal';
$route['kaprodi/seminar_skripsi/tetapkan_penguji'] = 'kaprodi/seminar_skripsi/tetapkan_penguji';

// 6. Publikasi (BARU)
$route['kaprodi/publikasi'] = 'kaprodi/publikasi';
$route['kaprodi/publikasi/(:any)'] = 'kaprodi/publikasi/$1';
$route['kaprodi/publikasi/detail/(:num)'] = 'kaprodi/publikasi/detail/$1';
$route['kaprodi/publikasi/validasi'] = 'kaprodi/publikasi/validasi';
$route['kaprodi/publikasi/input_repository'] = 'kaprodi/publikasi/input_repository';

// 7. Daftar Mahasiswa
$route['kaprodi/mahasiswa'] = 'kaprodi/kaprodi/mahasiswa';                  // Daftar mahasiswa
$route['kaprodi/mahasiswa/detail/(:num)'] = 'kaprodi/kaprodi/mahasiswa_detail/$1';
$route['kaprodi/mahasiswa/export'] = 'kaprodi/kaprodi/mahasiswa_export';

// 8. Daftar Dosen
$route['kaprodi/dosen'] = 'kaprodi/kaprodi/dosen';                          // Daftar dosen
$route['kaprodi/dosen/detail/(:num)'] = 'kaprodi/kaprodi/dosen_detail/$1';
$route['kaprodi/dosen/export'] = 'kaprodi/kaprodi/dosen_export';

// 9. Laporan
$route['kaprodi/laporan'] = 'kaprodi/kaprodi/laporan';                      // Laporan
$route['kaprodi/laporan/export'] = 'kaprodi/kaprodi/laporan_export';
$route['kaprodi/laporan/filter'] = 'kaprodi/kaprodi/laporan_filter';

// 10. Profil (BARU - sesuai role dosen)
$route['kaprodi/profil'] = 'kaprodi/profil';                                // Profil kaprodi
$route['kaprodi/profil/update'] = 'kaprodi/profil/update';                  // Update profil kaprodi
$route['kaprodi/profil/hapus_foto'] = 'kaprodi/profil/hapus_foto';          // Hapus foto profil kaprodi

// =====================================================
//      WORKFLOW TERBARU - PENETAPAN PEMBIMBING (FALLBACK)
// =====================================================

// Penetapan Pembimbing (Workflow Terbaru) - FALLBACK ke sistem lama
$route['kaprodi/penetapan_pembimbing'] = 'kaprodi/penetapan_pembimbing';
$route['kaprodi/penetapan_pembimbing/(:any)'] = 'kaprodi/penetapan_pembimbing/$1';
$route['kaprodi/penetapan_pembimbing/detail/(:num)'] = 'kaprodi/penetapan_pembimbing/detail/$1';

// Riwayat Penetapan - DIHAPUS DARI SIDEBAR, tapi route tetap ada untuk compatibility
$route['kaprodi/riwayat_penetapan'] = 'kaprodi/riwayat_penetapan';
$route['kaprodi/riwayat'] = 'kaprodi/riwayat_penetapan'; // Alias

// =====================================================
//      ROUTE UNTUK FILE PROPOSAL
// =====================================================
$route['kaprodi/download_proposal/(:num)'] = 'kaprodi/kaprodi/download_proposal/$1'; // Download file proposal
$route['kaprodi/view_proposal/(:num)'] = 'kaprodi/kaprodi/view_proposal/$1';         // View file proposal

// =====================================================
//      DEBUG DAN DEVELOPMENT (HANYA DEVELOPMENT)
// =====================================================
if (ENVIRONMENT === 'development') {
    $route['kaprodi/debug/(:any)'] = 'kaprodi/debug/$1';
    $route['kaprodi/test/(:any)'] = 'kaprodi/test/$1';
}

// Catch-all fallback (harus di paling bawah)
$route['kaprodi/(:any)'] = 'kaprodi/kaprodi/$1';
$route['kaprodi/(:any)/(:num)'] = 'kaprodi/kaprodi/$1/$2';

// =====================================================
//      ROUTE DOSEN - WORKFLOW TERBARU
// =====================================================

// Dashboard
$route['dosen'] = 'dosen/dashboard';
$route['dosen/dashboard'] = 'dosen/dashboard';

// =====================================================
//      PHASE 1: USULAN PROPOSAL
// =====================================================

// Usulan Proposal - Penunjukan Pembimbing
$route['dosen/usulan_proposal'] = 'dosen/usulan_proposal';
$route['dosen/usulan_proposal/detail/(:num)'] = 'dosen/usulan_proposal/detail/$1';
$route['dosen/usulan_proposal/proses_persetujuan'] = 'dosen/usulan_proposal/proses_persetujuan';

// =====================================================
//      PHASE 2: BIMBINGAN
// =====================================================

// Bimbingan Mahasiswa
$route['dosen/bimbingan'] = 'dosen/bimbingan';
$route['dosen/bimbingan/detail_mahasiswa/(:num)'] = 'dosen/bimbingan/detail_mahasiswa/$1';
$route['dosen/bimbingan/validasi_jurnal'] = 'dosen/bimbingan/validasi_jurnal';
$route['dosen/bimbingan/validasi_batch'] = 'dosen/bimbingan/validasi_batch';
$route['dosen/bimbingan/tambah_jurnal'] = 'dosen/bimbingan/tambah_jurnal';
$route['dosen/bimbingan/edit_jurnal/(:num)'] = 'dosen/bimbingan/edit_jurnal/$1';
$route['dosen/bimbingan/hapus_jurnal/(:num)'] = 'dosen/bimbingan/hapus_jurnal/$1';
$route['dosen/bimbingan/detail_jurnal/(:num)'] = 'dosen/bimbingan/detail_jurnal/$1';
$route['dosen/bimbingan/export_jurnal/(:num)'] = 'dosen/bimbingan/export_jurnal/$1';

// =====================================================
//      PHASE 3: SEMINAR PROPOSAL
// =====================================================

// Seminar Proposal
$route['dosen/seminar_proposal'] = 'dosen/seminar_proposal';
$route['dosen/seminar_proposal/detail/(:num)'] = 'dosen/seminar_proposal/detail/$1';
$route['dosen/seminar_proposal/rekomendasi'] = 'dosen/seminar_proposal/rekomendasi';
$route['dosen/seminar_proposal/input_nilai'] = 'dosen/seminar_proposal/input_nilai';
$route['dosen/seminar_proposal/berita_acara/(:num)'] = 'dosen/seminar_proposal/berita_acara/$1';

// =====================================================
//      PHASE 4: PENELITIAN
// =====================================================

// Penelitian - Surat Ijin Penelitian
$route['dosen/penelitian'] = 'dosen/penelitian';
$route['dosen/penelitian/detail/(:num)'] = 'dosen/penelitian/detail/$1';
$route['dosen/penelitian/rekomendasi'] = 'dosen/penelitian/rekomendasi';

// =====================================================
//      PHASE 5: SEMINAR SKRIPSI
// =====================================================

// Seminar Akhir/Skripsi
$route['dosen/seminar_skripsi'] = 'dosen/seminar_skripsi';
$route['dosen/seminar_skripsi/detail/(:num)'] = 'dosen/seminar_skripsi/detail/$1';
$route['dosen/seminar_skripsi/rekomendasi'] = 'dosen/seminar_skripsi/rekomendasi';
$route['dosen/seminar_skripsi/input_nilai'] = 'dosen/seminar_skripsi/input_nilai';
$route['dosen/seminar_skripsi/berita_acara/(:num)'] = 'dosen/seminar_skripsi/berita_acara/$1';

// =====================================================
//      PHASE 6: PUBLIKASI
// =====================================================

// Publikasi Tugas Akhir
$route['dosen/publikasi'] = 'dosen/publikasi';
$route['dosen/publikasi/detail/(:num)'] = 'dosen/publikasi/detail/$1';
$route['dosen/publikasi/rekomendasi'] = 'dosen/publikasi/rekomendasi';

// =====================================================
//      PROFIL DAN UTILITAS
// =====================================================

// Profil Dosen
$route['dosen/profil'] = 'dosen/profil';
$route['dosen/profil/update'] = 'dosen/profil/update';
$route['dosen/profil/hapus_foto'] = 'dosen/profil/hapus_foto';
$route['dosen/profil/debug'] = 'dosen/profil/debug';
$route['dosen/profil/test_upload'] = 'dosen/profil/test_upload';

// Notifikasi dan Laporan
$route['dosen/notifikasi'] = 'dosen/notifikasi';
$route['dosen/laporan'] = 'dosen/laporan';
$route['dosen/laporan/mahasiswa_bimbingan'] = 'dosen/laporan/mahasiswa_bimbingan';
$route['dosen/laporan/statistik_bimbingan'] = 'dosen/laporan/statistik_bimbingan';

// API untuk DataTables dan AJAX calls
$route['api/dosen/mahasiswa_bimbingan'] = 'api/dosen/mahasiswa_bimbingan';
$route['api/dosen/jurnal_bimbingan'] = 'api/dosen/jurnal_bimbingan';
$route['api/dosen/proposal_pending'] = 'api/dosen/proposal_pending';

// =====================================================
//      FALLBACK UNTUK SISTEM LAMA (COMPATIBILITY)
// =====================================================

// Fallback ke sistem lama jika ada controller yang belum diupdate
$route['dosen/proposal'] = 'dosen/usulan_proposal'; // Redirect ke usulan_proposal
$route['dosen/konsultasi'] = 'dosen/bimbingan';     // Redirect ke bimbingan
$route['dosen/seminar'] = 'dosen/seminar_proposal'; // Redirect ke seminar_proposal
$route['dosen/skripsi'] = 'dosen/seminar_skripsi';  // Redirect ke seminar_skripsi

// Catch-all untuk dosen routes yang belum didefinisikan
$route['dosen/(:any)'] = 'dosen/$1';

// =====================================================
//      KAPRODI ROUTES SUMMARY - MENU BARU
// =====================================================

/*
KAPRODI MENU ROUTES (URUTAN SESUAI SIDEBAR):

1. Dashboard:
   - kaprodi/dashboard

2. Pengumuman Tahapan:
   - kaprodi/pengumuman
   - kaprodi/pengumuman/tambah
   - kaprodi/pengumuman/edit/(:num)
   - kaprodi/pengumuman/hapus/(:num)

3. Usulan Proposal:
   - kaprodi/proposal                          // List usulan proposal
   - kaprodi/review_proposal/(:num)            // Review & penetapan pembimbing
   - kaprodi/proses_review                     // POST - setuju/tolak proposal

4. Seminar Proposal:
   - kaprodi/seminar_proposal                  // List pengajuan seminar proposal
   - kaprodi/seminar_proposal/detail/(:num)    // Detail & validasi
   - kaprodi/seminar_proposal/validasi         // POST - setuju/tolak seminar
   - kaprodi/seminar_proposal/jadwal           // POST - set jadwal seminar
   - kaprodi/seminar_proposal/tetapkan_penguji // POST - tetapkan penguji

5. Seminar Skripsi:
   - kaprodi/seminar_skripsi                   // List pengajuan seminar skripsi
   - kaprodi/seminar_skripsi/detail/(:num)     // Detail & validasi
   - kaprodi/seminar_skripsi/validasi          // POST - setuju/tolak seminar
   - kaprodi/seminar_skripsi/jadwal            // POST - set jadwal seminar
   - kaprodi/seminar_skripsi/tetapkan_penguji  // POST - tetapkan penguji

6. Publikasi:
   - kaprodi/publikasi                         // List pengajuan publikasi
   - kaprodi/publikasi/detail/(:num)           // Detail & validasi
   - kaprodi/publikasi/validasi                // POST - setuju/tolak publikasi
   - kaprodi/publikasi/input_repository        // POST - input link repository

7. Daftar Mahasiswa:
   - kaprodi/mahasiswa                         // List mahasiswa
   - kaprodi/mahasiswa/detail/(:num)           // Detail mahasiswa
   - kaprodi/mahasiswa/export                  // Export data

8. Daftar Dosen:
   - kaprodi/dosen                            // List dosen
   - kaprodi/dosen/detail/(:num)              // Detail dosen
   - kaprodi/dosen/export                     // Export data

9. Laporan:
   - kaprodi/laporan                          // Laporan & statistik
   - kaprodi/laporan/export                   // Export laporan
   - kaprodi/laporan/filter                   // Filter laporan

10. Profil:
    - kaprodi/profil                          // Profil kaprodi
    - kaprodi/profil/update                   // Update profil
    - kaprodi/profil/hapus_foto               // Hapus foto profil

CATATAN PENTING:
- Menu "Riwayat Penetapan" sudah DIHAPUS dari sidebar
- Menu "Penetapan Pembimbing" diubah nama menjadi "Usulan Proposal"
- Menu "Pengumuman Tahapan" dipindah ke posisi kedua setelah Dashboard
- Menambah 3 menu baru: Seminar Proposal, Seminar Skripsi, Publikasi
- Menambah menu Profil seperti pada role dosen
*/