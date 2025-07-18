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