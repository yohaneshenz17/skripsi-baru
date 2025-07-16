<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Email Configuration - PRODUCTION READY
|--------------------------------------------------------------------------
| Konfigurasi yang sudah TERBUKTI BEKERJA pada server STK St. Yakobus
| Tested: 16 July 2025 - Port 587 + TLS ✅ SUCCESS
|--------------------------------------------------------------------------
*/

// WORKING CONFIGURATION - Port 587 + TLS
$config['protocol']     = 'smtp';
$config['smtp_host']    = 'smtp.gmail.com';
$config['smtp_port']    = 587;
$config['smtp_timeout'] = 30;
$config['smtp_user']    = 'stkyakobus@gmail.com';
$config['smtp_pass']    = 'yonroxhraathnaug';
$config['charset']      = 'utf-8';
$config['newline']      = "\r\n";
$config['mailtype']     = 'html';
$config['validation']   = TRUE;
$config['priority']     = 3;
$config['crlf']         = "\r\n";
$config['smtp_crypto']  = 'tls';
$config['wordwrap']     = TRUE;
$config['wrapchars']    = 76;

// Production settings
$config['smtp_debug'] = FALSE;  // Set TRUE only for debugging
$config['smtp_keepalive'] = FALSE;
$config['smtp_auto_tls'] = TRUE;

// Default email settings
$config['email_from'] = 'stkyakobus@gmail.com';
$config['email_from_name'] = 'SIM Tugas Akhir STK St. Yakobus';

// Email template settings
$config['email_template'] = [
    'header_color' => '#007bff',
    'footer_color' => '#6c757d', 
    'institution_name' => 'STK Santo Yakobus Merauke',
    'institution_address' => 'Jl. Missi 2, Mandala, Merauke, Papua Selatan',
    'institution_phone' => '09713330264',
    'system_name' => 'Sistem Informasi Manajemen Tugas Akhir'
];

/*
|--------------------------------------------------------------------------
| Backup configurations (jika diperlukan)
|--------------------------------------------------------------------------
*/
$config['backup_configs'] = [
    'port_465_ssl' => [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 465,
        'smtp_crypto' => 'ssl'
    ],
    'cpanel_mail' => [
        'smtp_host' => 'mail.stkyakobus.ac.id',
        'smtp_port' => 587,
        'smtp_crypto' => 'tls'
    ]
];

/*
|--------------------------------------------------------------------------
| Performance & Security Settings
|--------------------------------------------------------------------------
*/
$config['smtp_set_time_limit'] = 0;
$config['smtp_verify_peer'] = FALSE;
$config['smtp_verify_peer_name'] = FALSE;
$config['smtp_allow_self_signed'] = TRUE;

/* 
 * STATUS: ✅ TESTED & WORKING
 * Last Test: 16 July 2025, 10:22 WIT
 * Test Result: SUCCESS - Email sent via Port 587 + TLS
 * Server: STK St. Yakobus Hosting
 */

/* End of file email.php */
/* Location: ./application/config/email.php */