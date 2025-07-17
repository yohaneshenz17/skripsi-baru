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
//      PENAMBAHAN ROUTE UNTUK KAPRODI (BARU)
// =====================================================
$route['kaprodi'] = 'kaprodi/dashboard';
$route['kaprodi/(:any)'] = 'kaprodi/$1';
$route['kaprodi/(:any)/(:num)'] = 'kaprodi/$1/$2';