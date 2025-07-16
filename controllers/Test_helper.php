<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_helper extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        // Helper email sudah autoload
    }
    
    public function index() {
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
            .container { max-width: 1000px; margin: 0 auto; }
            .success { color: #28a745; font-weight: bold; }
            .error { color: #dc3545; font-weight: bold; }
            .warning { color: #ffc107; font-weight: bold; }
            .info { color: #007bff; font-weight: bold; }
            .section { 
                background: white; 
                padding: 20px; 
                margin: 15px 0; 
                border-radius: 8px; 
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .section h2 { margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 10px; }
            pre { 
                background: #f8f9fa; 
                padding: 15px; 
                border: 1px solid #ddd; 
                border-radius: 5px; 
                font-size: 12px; 
                overflow-x: auto;
            }
            .status-badge {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: bold;
                margin-left: 10px;
            }
            .badge-success { background: #d4edda; color: #155724; }
            .badge-error { background: #f8d7da; color: #721c24; }
            .badge-warning { background: #fff3cd; color: #856404; }
            .config-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            .config-table th, .config-table td { 
                padding: 8px 12px; 
                text-align: left; 
                border-bottom: 1px solid #ddd; 
            }
            .config-table th { background: #f8f9fa; font-weight: bold; }
            .btn { 
                background: #007bff; 
                color: white; 
                padding: 8px 15px; 
                text-decoration: none; 
                border-radius: 4px; 
                margin: 5px; 
                display: inline-block;
                font-size: 14px;
            }
            .btn:hover { background: #0056b3; }
            .btn-success { background: #28a745; }
            .btn-warning { background: #ffc107; color: #212529; }
            .alert { padding: 15px; margin: 15px 0; border-radius: 5px; }
            .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
            .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        </style>";
        
        echo "<div class='container'>";
        echo "<h1>🧪 Email System Test - STK St. Yakobus</h1>";
        
        echo "<div class='alert alert-info'>";
        echo "<strong>ℹ️ Info:</strong> Sistem menggunakan Gmail SMTP Port 587 + TLS yang sudah terbukti bekerja. ";
        echo "Test ini memverifikasi semua komponen email notification ready untuk production.";
        echo "</div>";
        
        // Test 1: Helper Status
        $this->test_helper_status();
        
        // Test 2: Current Configuration
        $this->test_current_configuration();
        
        // Test 3: Validasi dengan Standard Baru
        $this->test_updated_validation();
        
        // Test 4: Email Connection Test
        $this->test_email_functionality();
        
        // Test 5: Production Readiness
        $this->test_production_readiness();
        
        echo "</div>";
    }
    
    private function test_helper_status() {
        echo "<div class='section'>";
        echo "<h2>📦 Test 1: Email Helper Status</h2>";
        
        $helper_functions = [
            'send_email_notification' => 'Core email sending function',
            'validate_email_config' => 'Configuration validation',
            'test_email_connection' => 'Connection testing',
            'get_email_template' => 'Email template generation',
            'validate_current_email_setup' => 'Current setup validation'
        ];
        
        $all_loaded = true;
        foreach ($helper_functions as $func => $desc) {
            if (function_exists($func)) {
                echo "<span class='success'>✅ {$func}</span> - {$desc}<br>";
            } else {
                echo "<span class='error'>❌ {$func}</span> - {$desc}<br>";
                $all_loaded = false;
            }
        }
        
        if ($all_loaded) {
            echo "<div class='alert alert-success' style='margin-top: 15px;'>";
            echo "<strong>✅ Status:</strong> Semua fungsi email helper berhasil dimuat dan siap digunakan.";
            echo "</div>";
        }
        echo "</div>";
    }
    
    private function test_current_configuration() {
        echo "<div class='section'>";
        echo "<h2>⚙️ Test 2: Current Email Configuration</h2>";
        
        if (function_exists('validate_current_email_setup')) {
            $setup = validate_current_email_setup();
            
            echo "<table class='config-table'>";
            echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";
            
            foreach ($setup['current_config'] as $key => $value) {
                $display_value = ($key === 'smtp_pass') ? str_repeat('*', strlen($value)) : $value;
                $status_class = !empty($value) ? 'success' : 'error';
                $status_text = !empty($value) ? '✅ OK' : '❌ Missing';
                
                echo "<tr>";
                echo "<td><strong>{$key}</strong></td>";
                echo "<td>{$display_value}</td>";
                echo "<td><span class='{$status_class}'>{$status_text}</span></td>";
                echo "</tr>";
            }
            echo "</table>";
            
            if ($setup['is_proven_working']) {
                echo "<div class='alert alert-success'>";
                echo "<strong>🎯 Excellent!</strong> Konfigurasi menggunakan setup yang sudah terbukti bekerja (Port 587 + TLS).";
                echo "</div>";
            } else {
                echo "<div class='alert alert-warning'>";
                echo "<strong>⚠️ Recommendation:</strong> " . $setup['recommendation'];
                echo "</div>";
            }
        }
        echo "</div>";
    }
    
    private function test_updated_validation() {
        echo "<div class='section'>";
        echo "<h2>🔍 Test 3: Email Configuration Validation</h2>";
        
        if (function_exists('validate_email_config')) {
            $validation = validate_email_config();
            
            if ($validation['status']) {
                echo "<span class='success'>✅ Validasi konfigurasi: PASSED</span><br>";
                
                if (isset($validation['info'])) {
                    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                    echo "<strong>ℹ️ Info Konfigurasi:</strong><br>";
                    echo "• Type: " . $validation['info']['config_type'] . "<br>";
                    echo "• Status: " . $validation['info']['status'] . "<br>";
                    echo "• Last Tested: " . $validation['info']['last_tested'] . "<br>";
                    echo "</div>";
                }
            } else {
                echo "<span class='error'>❌ Validasi konfigurasi: FAILED</span><br>";
                echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                echo "<strong>🚨 Issues Found:</strong><br>";
                foreach ($validation['errors'] as $error) {
                    echo "• {$error}<br>";
                }
                echo "</div>";
            }
            
            echo "<details style='margin-top: 15px;'>";
            echo "<summary>📋 Show Detailed Validation Results</summary>";
            echo "<pre>" . print_r($validation, true) . "</pre>";
            echo "</details>";
        }
        echo "</div>";
    }
    
    private function test_email_functionality() {
        echo "<div class='section'>";
        echo "<h2>📧 Test 4: Email Functionality Test</h2>";
        
        echo "<p>Test pengiriman email menggunakan konfigurasi yang sudah terbukti bekerja:</p>";
        
        if (isset($_GET['test_email']) && $_GET['test_email'] === 'yes') {
            echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "🔄 <strong>Mengirim email test...</strong>";
            echo "</div>";
            
            if (function_exists('test_email_connection')) {
                $test_result = test_email_connection('sipd@stkyakobus.ac.id');
                
                if ($test_result['status']) {
                    echo "<div class='alert alert-success'>";
                    echo "<strong>✅ SUCCESS!</strong> " . $test_result['message'] . "<br>";
                    if (isset($test_result['config_used'])) {
                        echo "<strong>Config:</strong> " . $test_result['config_used'] . "<br>";
                    }
                    if (isset($test_result['timestamp'])) {
                        echo "<strong>Time:</strong> " . $test_result['timestamp'];
                    }
                    echo "</div>";
                } else {
                    echo "<div class='alert alert-error'>";
                    echo "<strong>❌ FAILED!</strong> " . $test_result['message'];
                    echo "</div>";
                }
                
                echo "<details style='margin-top: 15px;'>";
                echo "<summary>📋 Show Detailed Test Results</summary>";
                echo "<pre>" . print_r($test_result, true) . "</pre>";
                echo "</details>";
            }
        } else {
            echo "<a href='?test_email=yes' class='btn btn-success'>🚀 Run Email Test</a>";
            echo "<p><small>⚠️ This will send a real test email to sipd@stkyakobus.ac.id</small></p>";
        }
        echo "</div>";
    }
    
    private function test_production_readiness() {
        echo "<div class='section'>";
        echo "<h2>🚀 Test 5: Production Readiness Check</h2>";
        
        $checks = [
            'helper_loaded' => function_exists('send_email_notification'),
            'config_valid' => function_exists('validate_email_config') ? validate_email_config()['status'] : false,
            'openssl_available' => extension_loaded('openssl'),
            'logs_writable' => is_writable(APPPATH . 'logs/'),
            'email_config_exists' => file_exists(APPPATH . 'config/email.php')
        ];
        
        $ready_count = 0;
        $total_checks = count($checks);
        
        echo "<table class='config-table'>";
        echo "<tr><th>Check</th><th>Status</th><th>Description</th></tr>";
        
        foreach ($checks as $check => $result) {
            $status = $result ? '✅ PASS' : '❌ FAIL';
            $class = $result ? 'success' : 'error';
            if ($result) $ready_count++;
            
            $descriptions = [
                'helper_loaded' => 'Email helper functions available',
                'config_valid' => 'Email configuration is valid',
                'openssl_available' => 'OpenSSL extension for TLS/SSL',
                'logs_writable' => 'Log directory is writable',
                'email_config_exists' => 'Email config file exists'
            ];
            
            echo "<tr>";
            echo "<td><strong>" . ucwords(str_replace('_', ' ', $check)) . "</strong></td>";
            echo "<td><span class='{$class}'>{$status}</span></td>";
            echo "<td>{$descriptions[$check]}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        $percentage = round(($ready_count / $total_checks) * 100);
        
        if ($percentage >= 100) {
            echo "<div class='alert alert-success'>";
            echo "<strong>🎉 READY FOR PRODUCTION!</strong><br>";
            echo "Semua checks passed ({$ready_count}/{$total_checks}). ";
            echo "Email notification system siap untuk implementasi production.";
            echo "</div>";
        } else if ($percentage >= 80) {
            echo "<div class='alert alert-warning'>";
            echo "<strong>⚠️ MOSTLY READY</strong><br>";
            echo "Score: {$ready_count}/{$total_checks} ({$percentage}%). ";
            echo "Perbaiki issues yang ada sebelum production deployment.";
            echo "</div>";
        } else {
            echo "<div class='alert alert-error'>";
            echo "<strong>❌ NOT READY</strong><br>";
            echo "Score: {$ready_count}/{$total_checks} ({$percentage}%). ";
            echo "Perlu perbaikan serius sebelum bisa digunakan di production.";
            echo "</div>";
        }
        
        // Environment info
        echo "<h3>🌍 Environment Information</h3>";
        echo "<table class='config-table'>";
        $env_info = [
            'PHP Version' => phpversion(),
            'CodeIgniter Version' => CI_VERSION,
            'Environment' => ENVIRONMENT,
            'Base URL' => base_url(),
            'Current Time' => date('Y-m-d H:i:s T'),
            'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
        ];
        
        foreach ($env_info as $key => $value) {
            echo "<tr><td><strong>{$key}</strong></td><td>{$value}</td></tr>";
        }
        echo "</table>";
        
        // Quick Actions
        echo "<h3>🎯 Quick Actions</h3>";
        echo "<a href='" . base_url('mahasiswa/proposal') . "' class='btn'>📝 Test Proposal Submission</a>";
        echo "<a href='" . base_url('email_diagnostic') . "' class='btn'>🔧 Email Diagnostic</a>";
        echo "<a href='?test_email=yes' class='btn btn-warning'>📧 Send Test Email</a>";
        
        echo "</div>";
    }
}