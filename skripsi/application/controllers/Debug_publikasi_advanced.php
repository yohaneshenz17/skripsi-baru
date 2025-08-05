<?php
/**
 * ADVANCED DEBUG TOOL - CARI SUMBER URL DETAIL
 * File: application/controllers/Debug_publikasi_advanced.php
 * URL: https://stkyakobus.ac.id/skripsi/debug_publikasi_advanced
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Debug_publikasi_advanced extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'file']);
    }

    public function index() {
        echo "<!DOCTYPE html><html><head><title>Advanced Debug Publikasi</title>";
        echo "<style>
            body { font-family: monospace; margin: 20px; background: #f5f5f5; }
            .debug-section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 5px solid #dc3545; }
            .success { color: #28a745; font-weight: bold; }
            .error { color: #dc3545; font-weight: bold; }
            .warning { color: #ffc107; font-weight: bold; }
            .code { background: #f8f9fa; padding: 10px; border-radius: 3px; margin: 10px 0; overflow-x: auto; white-space: pre-wrap; }
            .highlight-red { background-color: #ffebee; padding: 2px 4px; border-radius: 3px; }
        </style></head><body>";
        
        echo "<h1>🔍 ADVANCED DEBUG - CARI SUMBER URL DETAIL</h1>";
        echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
        
        // 1. SCAN ALL FILES FOR "detail" REFERENCES
        $this->scan_all_files_for_detail();
        
        // 2. CHECK CONTROLLER METHODS THAT REDIRECT
        $this->check_controller_redirects();
        
        // 3. CHECK SPECIFIC VIEW CONTENT
        $this->deep_scan_view_files();
        
        // 4. CHECK FOR CACHED FILES
        $this->check_cache_files();
        
        // 5. MANUAL URL TEST
        $this->manual_url_test();
        
        echo "</body></html>";
    }
    
    private function scan_all_files_for_detail() {
        echo "<div class='debug-section'>";
        echo "<h3>1. 🔍 SCAN ALL PROJECT FILES FOR 'publikasi/detail'</h3>";
        
        // Directories to scan
        $scan_dirs = [
            APPPATH . 'controllers/mahasiswa/',
            APPPATH . 'views/mahasiswa/publikasi/',
            APPPATH . 'models/',
        ];
        
        $found_references = [];
        
        foreach ($scan_dirs as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '*.php');
                foreach ($files as $file) {
                    $content = file_get_contents($file);
                    $lines = explode("\n", $content);
                    
                    foreach ($lines as $line_num => $line) {
                        if (stripos($line, 'publikasi/detail') !== false) {
                            $found_references[] = [
                                'file' => str_replace(APPPATH, 'application/', $file),
                                'line' => $line_num + 1,
                                'content' => trim($line)
                            ];
                        }
                    }
                }
            }
        }
        
        if ($found_references) {
            echo "<p class='error'>❌ FOUND " . count($found_references) . " references to 'publikasi/detail':</p>";
            foreach ($found_references as $ref) {
                echo "<div class='code'>";
                echo "<strong>File:</strong> {$ref['file']} (Line {$ref['line']})\n";
                echo "<strong>Code:</strong> <span class='highlight-red'>" . htmlspecialchars($ref['content']) . "</span>";
                echo "</div>";
            }
        } else {
            echo "<p class='success'>✅ No references to 'publikasi/detail' found in scanned files</p>";
        }
        
        echo "</div>";
    }
    
    private function check_controller_redirects() {
        echo "<div class='debug-section'>";
        echo "<h3>2. 🔄 CHECK CONTROLLER REDIRECTS</h3>";
        
        $controller_file = APPPATH . 'controllers/mahasiswa/Publikasi.php';
        
        if (file_exists($controller_file)) {
            $content = file_get_contents($controller_file);
            $lines = explode("\n", $content);
            
            $redirect_lines = [];
            foreach ($lines as $line_num => $line) {
                if (stripos($line, 'redirect') !== false && stripos($line, 'detail') !== false) {
                    $redirect_lines[] = [
                        'line' => $line_num + 1,
                        'content' => trim($line)
                    ];
                }
            }
            
            if ($redirect_lines) {
                echo "<p class='error'>❌ FOUND redirect statements with 'detail':</p>";
                foreach ($redirect_lines as $redirect) {
                    echo "<div class='code'>";
                    echo "Line {$redirect['line']}: <span class='highlight-red'>" . htmlspecialchars($redirect['content']) . "</span>";
                    echo "</div>";
                }
            } else {
                echo "<p class='success'>✅ No redirect statements to 'detail' found in controller</p>";
            }
            
            // Check for any method that still references detail
            if (stripos($content, 'detail') !== false) {
                echo "<p class='warning'>⚠️ Controller still contains 'detail' references. Scanning...</p>";
                
                $detail_refs = [];
                foreach ($lines as $line_num => $line) {
                    if (stripos($line, 'detail') !== false && !preg_match('/^\s*\/\//', $line)) {
                        $detail_refs[] = [
                            'line' => $line_num + 1,
                            'content' => trim($line)
                        ];
                    }
                }
                
                if ($detail_refs) {
                    echo "<p>Found " . count($detail_refs) . " lines with 'detail':</p>";
                    foreach (array_slice($detail_refs, 0, 10) as $ref) { // Show max 10
                        echo "<div class='code'>";
                        echo "Line {$ref['line']}: " . htmlspecialchars($ref['content']);
                        echo "</div>";
                    }
                }
            }
        }
        
        echo "</div>";
    }
    
    private function deep_scan_view_files() {
        echo "<div class='debug-section'>";
        echo "<h3>3. 📄 DEEP SCAN VIEW FILES</h3>";
        
        $view_files = [
            APPPATH . 'views/mahasiswa/publikasi/index.php',
            APPPATH . 'views/mahasiswa/publikasi/tracking.php',
            APPPATH . 'views/mahasiswa/publikasi/form.php'
        ];
        
        foreach ($view_files as $file) {
            if (file_exists($file)) {
                $filename = basename($file);
                echo "<p><strong>Scanning: $filename</strong></p>";
                
                $content = file_get_contents($file);
                $lines = explode("\n", $content);
                
                $detail_links = [];
                foreach ($lines as $line_num => $line) {
                    if (stripos($line, 'publikasi/detail') !== false) {
                        $detail_links[] = [
                            'line' => $line_num + 1,
                            'content' => trim($line)
                        ];
                    }
                }
                
                if ($detail_links) {
                    echo "<p class='error'>❌ Found 'publikasi/detail' links in $filename:</p>";
                    foreach ($detail_links as $link) {
                        echo "<div class='code'>";
                        echo "Line {$link['line']}: <span class='highlight-red'>" . htmlspecialchars($link['content']) . "</span>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='success'>✅ No 'publikasi/detail' links in $filename</p>";
                }
            } else {
                echo "<p class='error'>❌ File not found: " . basename($file) . "</p>";
            }
        }
        
        echo "</div>";
    }
    
    private function check_cache_files() {
        echo "<div class='debug-section'>";
        echo "<h3>4. 🗂️ CHECK CACHE FILES</h3>";
        
        // Check common cache locations
        $cache_dirs = [
            APPPATH . 'cache/',
            APPPATH . 'logs/',
            FCPATH . 'cache/',
        ];
        
        $cache_found = false;
        foreach ($cache_dirs as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '*');
                if ($files) {
                    $cache_found = true;
                    echo "<p>Cache directory: $dir (" . count($files) . " files)</p>";
                }
            }
        }
        
        if ($cache_found) {
            echo "<p class='warning'>⚠️ Cache files found. Consider clearing cache:</p>";
            echo "<div class='code'>rm -rf " . APPPATH . "cache/*\nrm -rf " . APPPATH . "logs/*</div>";
        } else {
            echo "<p class='success'>✅ No cache files found</p>";
        }
        
        echo "</div>";
    }
    
    private function manual_url_test() {
        echo "<div class='debug-section'>";
        echo "<h3>5. 🧪 MANUAL URL TESTS</h3>";
        
        $test_urls = [
            'mahasiswa/publikasi' => 'Dashboard (should work)',
            'mahasiswa/publikasi/tracking/2' => 'Tracking (should work)',
            'mahasiswa/publikasi/detail/2' => 'Detail (should give 404)',
            'mahasiswa/publikasi/ajukan' => 'Form ajukan (should work)',
            'mahasiswa/publikasi/edit/2' => 'Form edit (should work if status = draft/rejected)'
        ];
        
        echo "<p><strong>Test these URLs manually:</strong></p>";
        foreach ($test_urls as $url => $desc) {
            $full_url = base_url($url);
            echo "<div class='code'>";
            echo "<a href='$full_url' target='_blank'>$full_url</a><br>";
            echo "$desc";
            echo "</div>";
        }
        
        // Current publikasi data
        if ($this->session->userdata('logged_in')) {
            $mahasiswa_id = $this->session->userdata('id');
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $publikasi = $this->db->get('publikasi_tugas_akhir')->result();
            
            if ($publikasi) {
                echo "<p><strong>Your publikasi data:</strong></p>";
                foreach ($publikasi as $pub) {
                    echo "<div class='code'>";
                    echo "ID: {$pub->id} | Status: {$pub->status}";
                    echo "<br>✅ Working URL: " . base_url("mahasiswa/publikasi/tracking/{$pub->id}");
                    echo "<br>❌ Problem URL: " . base_url("mahasiswa/publikasi/detail/{$pub->id}");
                    echo "</div>";
                }
            }
        }
        
        echo "</div>";
    }
}
?>