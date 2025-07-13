<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Auth routes
$route['auth'] = 'auth/index';
$route['auth/login'] = 'auth/login';
$route['auth/logout'] = 'auth/logout';
$route['auth/cek/(:num)/(:num)'] = 'auth/cek/$1/$2';

// API routes
$route['api/auth/login'] = 'api/auth/login';

// Admin routes
$route['admin'] = 'admin/dashboard';
$route['lihat-selengkapnya/(:num)'] = 'admin/lihat_selengkapnya/$1'; // <-- BARIS INI DITAMBAHKAN
$route['admin/(:any)'] = 'admin/$1';

// Dosen routes
$route['dosen'] = 'dosen/dashboard';
$route['dosen/(:any)'] = 'dosen/$1';

// Mahasiswa routes
$route['mahasiswa'] = 'mahasiswa/dashboard';
$route['mahasiswa/(:any)'] = 'mahasiswa/$1';