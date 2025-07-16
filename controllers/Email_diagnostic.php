<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_diagnostic extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .success { color: #28a745; font-weight: bold; }
            .error { color: #dc3545; font-weight: bold; }
            .warning { color: #ffc107; font-weight: bold; }
            .info { color: #007bff; font-weight: bold; }
            .section { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .test-box { border: 2px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px; }
            pre { background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 3px; font-size: 12px; }
            .button { background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; margin: 5px; display: inline-block; }
        </style>";
        
        echo "<h1>🔧 Email Diagnostic Tool - STK St. Yakobus</h1>";
        
        // Test 1: Network Connectivity
        $this->test_network_connectivity();
        
        // Test 2: DNS Resolution
        $this->test_dns_resolution();
        
        // Test 3: Port Connectivity
        $this->test_port_connectivity();
        
        // Test 4: Multiple SMTP Configurations
        $this->test_multiple_smtp_configs();
        
        // Test 5: Alternative Email Methods
        $this->test_alternative_methods();
        
        echo "<div class='section'>";
        echo "<h2>🚀 Quick Actions</h2>";
        echo "<a href='?action=test_port_587' class='button'>Test Port 587</a>";
        echo "<a href='?action=test_port_465' class='button'>Test Port 465</a>";
        echo "<a href='?action=test_without_ssl' class='button'>Test Without SSL</a>";
        echo "<a href='?action=test_cpanel_mail' class='button'>Test cPanel Mail</a>";
        echo "</div>";
        
        $this->handle_actions();
    }
    
    private function test_network_connectivity() {
        echo "<div class='section'>";
        echo "<h2>🌐 Test 1: Network Connectivity</h2>";
        
        // Test internet connection
        $google_test = @file_get_contents('http://www.google.com', false, stream_context_create([
            'http' => ['timeout' => 10]
        ]));
        
        if ($google_test !== false) {
            echo "<span class='success'>✅ Internet connection: OK</span><br>";
        } else {
            echo "<span class='error'>❌ Internet connection: FAILED</span><br>";
        }
        
        // Test HTTPS capability
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.google.com');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                echo "<span class='success'>✅ HTTPS/SSL capability: OK</span><br>";
            } else {
                echo "<span class='error'>❌ HTTPS/SSL capability: FAILED (Code: {$http_code})</span><br>";
            }
        } else {
            echo "<span class='warning'>⚠️ cURL not available</span><br>";
        }
        echo "</div>";
    }
    
    private function test_dns_resolution() {
        echo "<div class='section'>";
        echo "<h2>🔍 Test 2: DNS Resolution</h2>";
        
        $hosts = [
            'smtp.gmail.com',
            'google.com',
            'stkyakobus.ac.id'
        ];
        
        foreach ($hosts as $host) {
            $ip = gethostbyname($host);
            if ($ip !== $host) {
                echo "<span class='success'>✅ {$host} → {$ip}</span><br>";
            } else {
                echo "<span class='error'>❌ {$host} → DNS resolution failed</span><br>";
            }
        }
        echo "</div>";
    }
    
    private function test_port_connectivity() {
        echo "<div class='section'>";
        echo "<h2>🔌 Test 3: Port Connectivity</h2>";
        
        $ports = [
            ['host' => 'smtp.gmail.com', 'port' => 587, 'name' => 'Gmail SMTP TLS'],
            ['host' => 'smtp.gmail.com', 'port' => 465, 'name' => 'Gmail SMTP SSL'],
            ['host' => 'smtp.gmail.com', 'port' => 25, 'name' => 'Gmail SMTP Plain'],
        ];
        
        foreach ($ports as $test) {
            $start = microtime(true);
            $connection = @fsockopen($test['host'], $test['port'], $errno, $errstr, 10);
            $end = microtime(true);
            $time = round(($end - $start) * 1000, 2);
            
            if ($connection) {
                echo "<span class='success'>✅ {$test['name']} ({$test['host']}:{$test['port']}) - {$time}ms</span><br>";
                fclose($connection);
            } else {
                echo "<span class='error'>❌ {$test['name']} ({$test['host']}:{$test['port']}) - Error: {$errstr} ({$errno})</span><br>";
            }
        }
        echo "</div>";
    }
    
    private function test_multiple_smtp_configs() {
        echo "<div class='section'>";
        echo "<h2>📧 Test 4: SMTP Configuration Tests</h2>";
        
        $configs = [
            [
                'name' => 'Gmail Port 587 + TLS',
                'config' => [
                    'protocol' => 'smtp',
                    'smtp_host' => 'smtp.gmail.com',
                    'smtp_port' => 587,
                    'smtp_crypto' => 'tls',
                    'smtp_user' => 'stkyakobus@gmail.com',
                    'smtp_pass' => 'yonroxhraathnaug'
                ]
            ],
            [
                'name' => 'Gmail Port 465 + SSL',
                'config' => [
                    'protocol' => 'smtp',
                    'smtp_host' => 'ssl://smtp.gmail.com',
                    'smtp_port' => 465,
                    'smtp_crypto' => 'ssl',
                    'smtp_user' => 'stkyakobus@gmail.com',
                    'smtp_pass' => 'yonroxhraathnaug'
                ]
            ],
            [
                'name' => 'Gmail Port 465 (No SSL Prefix)',
                'config' => [
                    'protocol' => 'smtp',
                    'smtp_host' => 'smtp.gmail.com',
                    'smtp_port' => 465,
                    'smtp_crypto' => 'ssl',
                    'smtp_user' => 'stkyakobus@gmail.com',
                    'smtp_pass' => 'yonroxhraathnaug'
                ]
            ]
        ];
        
        foreach ($configs as $test) {
            echo "<div class='test-box'>";
            echo "<h4>{$test['name']}</h4>";
            
            try {
                $this->load->library('email');
                $this->email->initialize($test['config']);
                $this->email->clear();
                
                echo "<span class='success'>✅ Configuration loaded successfully</span><br>";
                echo "<small>Host: {$test['config']['smtp_host']}:{$test['config']['smtp_port']}</small><br>";
                
            } catch (Exception $e) {
                echo "<span class='error'>❌ Configuration failed: {$e->getMessage()}</span><br>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
    
    private function test_alternative_methods() {
        echo "<div class='section'>";
        echo "<h2>🔄 Test 5: Alternative Email Methods</h2>";
        
        // Test PHP mail() function
        if (function_exists('mail')) {
            echo "<span class='success'>✅ PHP mail() function available</span><br>";
            
            // Try to send test email using mail()
            if (isset($_GET['test_php_mail'])) {
                $to = 'sipd@stkyakobus.ac.id';
                $subject = 'Test PHP mail() - STK St. Yakobus';
                $message = 'Test email menggunakan PHP mail() function';
                $headers = "From: stkyakobus@gmail.com\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                
                if (mail($to, $subject, $message, $headers)) {
                    echo "<span class='success'>✅ PHP mail() test sent successfully</span><br>";
                } else {
                    echo "<span class='error'>❌ PHP mail() test failed</span><br>";
                }
            } else {
                echo "<a href='?test_php_mail=1' class='button'>Test PHP mail()</a><br>";
            }
        } else {
            echo "<span class='error'>❌ PHP mail() function not available</span><br>";
        }
        
        // Check for sendmail
        $sendmail_path = ini_get('sendmail_path');
        if (!empty($sendmail_path)) {
            echo "<span class='success'>✅ Sendmail path: {$sendmail_path}</span><br>";
        } else {
            echo "<span class='warning'>⚠️ Sendmail path not configured</span><br>";
        }
        echo "</div>";
    }
    
    private function handle_actions() {
        if (isset($_GET['action'])) {
            echo "<div class='section'>";
            echo "<h2>🎯 Action Result: " . strtoupper($_GET['action']) . "</h2>";
            
            switch ($_GET['action']) {
                case 'test_port_587':
                    $this->quick_email_test('smtp.gmail.com', 587, 'tls');
                    break;
                case 'test_port_465':
                    $this->quick_email_test('smtp.gmail.com', 465, 'ssl');
                    break;
                case 'test_without_ssl':
                    $this->quick_email_test('smtp.gmail.com', 587, '');
                    break;
                case 'test_cpanel_mail':
                    $this->quick_email_test('mail.stkyakobus.ac.id', 587, 'tls');
                    break;
            }
            echo "</div>";
        }
    }
    
    private function quick_email_test($host, $port, $crypto) {
        try {
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => $host,
                'smtp_port' => $port,
                'smtp_timeout' => 10,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'text',
                'validation' => FALSE
            ];
            
            if (!empty($crypto)) {
                $config['smtp_crypto'] = $crypto;
            }
            
            $this->load->library('email');
            $this->email->initialize($config);
            $this->email->clear();
            
            $this->email->from('stkyakobus@gmail.com', 'STK Test');
            $this->email->to('sipd@stkyakobus.ac.id');
            $this->email->subject('Quick Test - ' . date('Y-m-d H:i:s'));
            $this->email->message('Test email from diagnostic tool');
            
            if ($this->email->send()) {
                echo "<span class='success'>✅ Email sent successfully!</span><br>";
                echo "<strong>Configuration:</strong> {$host}:{$port} " . ($crypto ? "({$crypto})" : "(no encryption)") . "<br>";
            } else {
                echo "<span class='error'>❌ Email failed to send</span><br>";
                echo "<pre>" . $this->email->print_debugger() . "</pre>";
            }
            
        } catch (Exception $e) {
            echo "<span class='error'>❌ Exception: {$e->getMessage()}</span><br>";
        }
    }
}