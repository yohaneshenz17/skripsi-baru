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

// Mahasiswa routes
$route['mahasiswa'] = 'mahasiswa/dashboard';
$route['mahasiswa/(:any)'] = 'mahasiswa/$1';

// =====================================================
//      ROUTE KAPRODI - WORKFLOW TERBARU
// =====================================================

// Dashboard (menggunakan controller Dashboard langsung)
$route['kaprodi'] = 'kaprodi/dashboard';
$route['kaprodi/dashboard'] = 'kaprodi/dashboard';
$route['kaprodi/dashboard/(:any)'] = 'kaprodi/dashboard/$1';

// =====================================================
//      WORKFLOW TERBARU - PENETAPAN PEMBIMBING
// =====================================================

// Penetapan Pembimbing (Workflow Terbaru)
$route['kaprodi/penetapan_pembimbing'] = 'kaprodi/penetapan_pembimbing';
$route['kaprodi/penetapan_pembimbing/(:any)'] = 'kaprodi/penetapan_pembimbing/$1';
$route['kaprodi/penetapan_pembimbing/detail/(:num)'] = 'kaprodi/penetapan_pembimbing/detail/$1';

// Riwayat Penetapan
$route['kaprodi/riwayat_penetapan'] = 'kaprodi/riwayat_penetapan';
$route['kaprodi/riwayat'] = 'kaprodi/riwayat_penetapan'; // Alias

// =====================================================
//      ROUTE KAPRODI - SISTEM LAMA (FALLBACK)
// =====================================================

// Proposal System - Method di controller Kaprodi (sistem lama)
$route['kaprodi/proposal'] = 'kaprodi/kaprodi/proposal';                    // List proposal
$route['kaprodi/review_proposal/(:num)'] = 'kaprodi/kaprodi/review_proposal/$1';  // Review detail
$route['kaprodi/proses_review'] = 'kaprodi/kaprodi/proses_review';          // Submit review (POST)
$route['kaprodi/penetapan/(:num)'] = 'kaprodi/kaprodi/penetapan/$1';        // Form penetapan LAMA
$route['kaprodi/simpan_penetapan'] = 'kaprodi/kaprodi/simpan_penetapan';    // Simpan penetapan (POST)

// =====================================================
//      ROUTE UNTUK FILE PROPOSAL
// =====================================================
$route['kaprodi/download_proposal/(:num)'] = 'kaprodi/kaprodi/download_proposal/$1'; // Download file proposal
$route['kaprodi/view_proposal/(:num)'] = 'kaprodi/kaprodi/view_proposal/$1';         // View file proposal

// =====================================================
//      ROUTE KAPRODI - MENU LAINNYA
// =====================================================

// Pengumuman Tahapan
$route['kaprodi/pengumuman'] = 'kaprodi/pengumuman';
$route['kaprodi/pengumuman/(:any)'] = 'kaprodi/pengumuman/$1';

// Other Methods
$route['kaprodi/mahasiswa'] = 'kaprodi/kaprodi/mahasiswa';                  // Daftar mahasiswa
$route['kaprodi/dosen'] = 'kaprodi/kaprodi/dosen';                          // Daftar dosen
$route['kaprodi/laporan'] = 'kaprodi/kaprodi/laporan';                      // Laporan
$route['kaprodi/profil'] = 'kaprodi/kaprodi/profil';                        // Profil kaprodi

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
//      ROUTES UPDATE SUMMARY
// =====================================================

/*
WORKFLOW ROUTES SUMMARY:

Phase 1 - Usulan Proposal:
- dosen/usulan_proposal                     // List penunjukan pembimbing
- dosen/usulan_proposal/detail/(:num)       // Detail proposal untuk persetujuan
- dosen/usulan_proposal/proses_persetujuan  // POST - setuju/tolak pembimbing

Phase 2 - Bimbingan:
- dosen/bimbingan                           // List mahasiswa bimbingan
- dosen/bimbingan/detail_mahasiswa/(:num)   // Detail jurnal bimbingan mahasiswa
- dosen/bimbingan/validasi_jurnal           // POST - validasi jurnal
- dosen/bimbingan/validasi_batch            // POST - validasi batch
- dosen/bimbingan/tambah_jurnal             // POST - tambah jurnal bimbingan
- dosen/bimbingan/export_jurnal/(:num)      // Export PDF jurnal

Phase 3 - Seminar Proposal:
- dosen/seminar_proposal                    // List pengajuan seminar proposal
- dosen/seminar_proposal/detail/(:num)      // Detail pengajuan
- dosen/seminar_proposal/rekomendasi        // POST - beri rekomendasi
- dosen/seminar_proposal/input_nilai        // POST - input nilai seminar

Phase 4 - Penelitian:
- dosen/penelitian                          // List surat ijin penelitian
- dosen/penelitian/detail/(:num)            // Detail pengajuan
- dosen/penelitian/rekomendasi              // POST - beri rekomendasi

Phase 5 - Seminar Skripsi:
- dosen/seminar_skripsi                     // List pengajuan seminar skripsi
- dosen/seminar_skripsi/detail/(:num)       // Detail pengajuan
- dosen/seminar_skripsi/rekomendasi         // POST - beri rekomendasi
- dosen/seminar_skripsi/input_nilai         // POST - input nilai seminar

Phase 6 - Publikasi:
- dosen/publikasi                           // List pengajuan publikasi
- dosen/publikasi/detail/(:num)             // Detail pengajuan
- dosen/publikasi/rekomendasi               // POST - beri rekomendasi

Support:
- dosen/profil                              // Profil dosen
- dosen/notifikasi                          // Notifikasi
- dosen/laporan                             // Laporan dan statistik
*/