<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['protocol']     = 'smtp';
$config['smtp_host']    = 'ssl://smtp.gmail.com';
$config['smtp_port']    = '465';
$config['smtp_timeout'] = '7';
$config['smtp_user']    = 'stkyakobus@gmail.com'; // Email Anda
$config['smtp_pass']    = 'yonroxhraathnaug'; // Gunakan App Password dari Gmail
$config['charset']      = 'utf-8';
$config['newline']      = "\r\n";
$config['mailtype']     = 'html';
$config['validation']   = TRUE;