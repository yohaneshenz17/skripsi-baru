<?php
/**
 * DEBUG TOOL PUBLIKASI
 * File: application/controllers/Debug_publikasi.php
 * URL: https://stkyakobus.ac.id/skripsi/debug_publikasi
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Debug_publikasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'file']);
    }

    public function index() {
        // Only allow in development or specific IP
        if (ENVIRONMENT !== 'development' && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
            // For production, you can add your IP here for debugging
            // show_404();
        }
        
        echo "<!DOCTYPE html><html><head><title>Debug Publikasi</title>";
        echo "<style>
            body { font-family: monospace; margin: 20px; background: #f5f5f5; }
            .debug-section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 5px solid #007bff; }
            .success { color: #28a745; font-weight: bold; }
            .error { color: #dc3545; font-weight: bold; }
            .warning { color: #ffc107; font-weight: bold; }
            table { border-collapse: collapse; width: 100%; margin: 10px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .code { background: #f8f9fa; padding: 10px; border-radius: 3px; margin: 10px 0; overflow-x: auto; }
        </style></head><body>";
        
        echo "<h1>🔍 DEBUG PUBLIKASI SYSTEM</h1>";
        echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
        
        // 1. CHECK CONTROLLER STRUCTURE
        $this->debug_controller_methods();
        
        // 2. CHECK VIEW FILES
        $this->debug_view_files();
        
        // 3. CHECK ROUTES
        $this->debug_routes();
        
        // 4. CHECK DATABASE STRUCTURE
        $this->debug_database();
        
        // 5. CHECK CURRENT USER SESSION
        $this->debug_session();
        
        // 6. CHECK LINKS IN VIEWS
        $this->debug_view_links();
        
        // 7. TEST PUBLIKASI DATA
        $this->debug_publikasi_data();
        
        echo "</body></html>";
    }
    
    private function debug_controller_methods() {
        echo "<div class='debug-section'>";
        echo "<h3>1. 📁 CONTROLLER METHODS CHECK</h3>";
        
        $controller_file = APPPATH . 'controllers/mahasiswa/Publikasi.php';
        
        if (file_exists($controller_file)) {
            echo "<p class='success'>✅ Controller exists: $controller_file</p>";
            
            // Get methods using reflection
            require_once $controller_file;
            
            try {
                $reflection = new ReflectionClass('Publikasi');
                $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
                
                echo "<p><strong>Available public methods:</strong></p>";
                echo "<ul>";
                foreach ($methods as $method) {
                    if ($method->class === 'Publikasi') {
                        echo "<li>";
                        if (in_array($method->name, ['index', 'ajukan', 'edit', 'tracking', 'submit'])) {
                            echo "<span class='success'>✅ {$method->name}()</span>";
                        } elseif ($method->name === 'detail') {
                            echo "<span class='error'>❌ {$method->name}() - PROBLEMATIC</span>";
                        } else {
                            echo "<span class='warning'>⚠️ {$method->name}()</span>";
                        }
                        echo "</li>";
                    }
                }
                echo "</ul>";
                
                // Check if detail method exists
                if ($reflection->hasMethod('detail')) {
                    echo "<p class='error'>❌ detail() method EXISTS - This might be causing 404 issues</p>";
                } else {
                    echo "<p class='success'>✅ detail() method NOT FOUND - Good for routing</p>";
                }
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Error analyzing controller: " . $e->getMessage() . "</p>";
            }
            
        } else {
            echo "<p class='error'>❌ Controller NOT FOUND: $controller_file</p>";
        }
        
        echo "</div>";
    }
    
    private function debug_view_files() {
        echo "<div class='debug-section'>";
        echo "<h3>2. 📄 VIEW FILES CHECK</h3>";
        
        $view_base = APPPATH . 'views/mahasiswa/publikasi/';
        $expected_views = [
            'index.php' => 'Dashboard publikasi',
            'tracking.php' => 'Detail tracking (replacement for detail)',
            'ajukan.php' => 'Form pengajuan',
            'edit.php' => 'Form edit'
        ];
        
        $problematic_views = [
            'detail.php' => 'Problematic view causing 404'
        ];
        
        echo "<p><strong>Expected view files:</strong></p>";
        echo "<ul>";
        foreach ($expected_views as $file => $desc) {
            $path = $view_base . $file;
            if (file_exists($path)) {
                echo "<li class='success'>✅ $file - $desc (" . number_format(filesize($path)) . " bytes)</li>";
            } else {
                echo "<li class='error'>❌ $file - $desc (MISSING)</li>";
            }
        }
        echo "</ul>";
        
        echo "<p><strong>Problematic view files:</strong></p>";
        echo "<ul>";
        foreach ($problematic_views as $file => $desc) {
            $path = $view_base . $file;
            if (file_exists($path)) {
                echo "<li class='error'>❌ $file - $desc (EXISTS - Should be removed or renamed)</li>";
            } else {
                echo "<li class='success'>✅ $file - $desc (NOT FOUND - Good!)</li>";
            }
        }
        echo "</ul>";
        
        echo "</div>";
    }
    
    private function debug_routes() {
        echo "<div class='debug-section'>";
        echo "<h3>3. 🛤️ ROUTES ANALYSIS</h3>";
        
        // Load routes
        $routes_file = APPPATH . 'config/routes.php';
        if (file_exists($routes_file)) {
            echo "<p class='success'>✅ Routes file exists</p>";
            
            // Get route content
            $route_content = file_get_contents($routes_file);
            
            // Check publikasi routes
            if (strpos($route_content, "publikasi/detail") !== false) {
                echo "<p class='error'>❌ FOUND 'publikasi/detail' route - This is causing the 404 issue!</p>";
                
                // Extract the problematic line
                $lines = explode("\n", $route_content);
                foreach ($lines as $i => $line) {
                    if (strpos($line, "publikasi/detail") !== false && strpos($line, "//") !== 0) {
                        echo "<div class='code'>Line " . ($i + 1) . ": $line</div>";
                    }
                }
                
                echo "<p class='warning'>⚠️ Solution: Comment out or remove the detail route</p>";
            } else {
                echo "<p class='success'>✅ No 'publikasi/detail' route found - Good!</p>";
            }
            
            if (strpos($route_content, "publikasi/tracking") !== false) {
                echo "<p class='success'>✅ Found 'publikasi/tracking' route - Good!</p>";
            } else {
                echo "<p class='error'>❌ No 'publikasi/tracking' route found</p>";
            }
            
        } else {
            echo "<p class='error'>❌ Routes file not found</p>";
        }
        
        echo "</div>";
    }
    
    private function debug_database() {
        echo "<div class='debug-section'>";
        echo "<h3>4. 🗄️ DATABASE STRUCTURE</h3>";
        
        try {
            // Check if publikasi table exists
            $tables = $this->db->list_tables();
            
            if (in_array('publikasi_tugas_akhir', $tables)) {
                echo "<p class='success'>✅ Table 'publikasi_tugas_akhir' exists</p>";
                
                // Get table structure
                $fields = $this->db->field_data('publikasi_tugas_akhir');
                
                echo "<p><strong>Table columns:</strong></p>";
                echo "<table>";
                echo "<tr><th>Column</th><th>Type</th><th>Status</th></tr>";
                
                $critical_columns = ['id', 'mahasiswa_id', 'status', 'created_at'];
                $expected_columns = ['judul_skripsi_final', 'nama_dosen_pembimbing'];
                
                foreach ($fields as $field) {
                    echo "<tr>";
                    echo "<td>{$field->name}</td>";
                    echo "<td>{$field->type}</td>";
                    
                    if (in_array($field->name, $critical_columns)) {
                        echo "<td class='success'>✅ Critical</td>";
                    } elseif (in_array($field->name, $expected_columns)) {
                        echo "<td class='success'>✅ Expected</td>";
                    } elseif ($field->name === 'judul_skripsi') {
                        echo "<td class='error'>❌ Old column name</td>";
                    } else {
                        echo "<td>⚪ Standard</td>";
                    }
                    
                    echo "</tr>";
                }
                echo "</table>";
                
                // Check if there's data
                $count = $this->db->count_all('publikasi_tugas_akhir');
                echo "<p><strong>Total records:</strong> $count</p>";
                
            } else {
                echo "<p class='error'>❌ Table 'publikasi_tugas_akhir' not found</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
        }
        
        echo "</div>";
    }
    
    private function debug_session() {
        echo "<div class='debug-section'>";
        echo "<h3>5. 👤 SESSION & USER DATA</h3>";
        
        if ($this->session->userdata('logged_in')) {
            echo "<p class='success'>✅ User is logged in</p>";
            echo "<p><strong>User ID:</strong> " . $this->session->userdata('id') . "</p>";
            echo "<p><strong>Level:</strong> " . $this->session->userdata('level') . "</p>";
            echo "<p><strong>Username:</strong> " . $this->session->userdata('username') . "</p>";
            
            $level = $this->session->userdata('level');
            if ($level == '4') {
                echo "<p class='success'>✅ Level 4 (Mahasiswa) - Access OK</p>";
            } else {
                echo "<p class='warning'>⚠️ Level $level - Not mahasiswa</p>";
            }
        } else {
            echo "<p class='error'>❌ User not logged in</p>";
        }
        
        echo "</div>";
    }
    
    private function debug_view_links() {
        echo "<div class='debug-section'>";
        echo "<h3>6. 🔗 VIEW LINKS ANALYSIS</h3>";
        
        $view_files = [
            APPPATH . 'views/mahasiswa/publikasi/index.php',
            APPPATH . 'views/mahasiswa/publikasi/tracking.php'
        ];
        
        foreach ($view_files as $file) {
            if (file_exists($file)) {
                $filename = basename($file);
                echo "<p><strong>Analyzing: $filename</strong></p>";
                
                $content = file_get_contents($file);
                
                // Check for problematic links
                if (strpos($content, 'publikasi/detail') !== false) {
                    echo "<p class='error'>❌ Found 'publikasi/detail' links in $filename</p>";
                    
                    // Find line numbers
                    $lines = explode("\n", $content);
                    foreach ($lines as $i => $line) {
                        if (strpos($line, 'publikasi/detail') !== false) {
                            echo "<div class='code'>Line " . ($i + 1) . ": " . htmlspecialchars(trim($line)) . "</div>";
                        }
                    }
                } else {
                    echo "<p class='success'>✅ No 'publikasi/detail' links in $filename</p>";
                }
                
                if (strpos($content, 'publikasi/tracking') !== false) {
                    echo "<p class='success'>✅ Found 'publikasi/tracking' links in $filename</p>";
                }
            }
        }
        
        echo "</div>";
    }
    
    private function debug_publikasi_data() {
        echo "<div class='debug-section'>";
        echo "<h3>7. 📊 PUBLIKASI DATA TEST</h3>";
        
        try {
            if ($this->session->userdata('logged_in')) {
                $mahasiswa_id = $this->session->userdata('id');
                
                // Check if user has publikasi data
                $this->db->where('mahasiswa_id', $mahasiswa_id);
                $publikasi = $this->db->get('publikasi_tugas_akhir')->result();
                
                if ($publikasi) {
                    echo "<p class='success'>✅ Found " . count($publikasi) . " publikasi records for current user</p>";
                    
                    foreach ($publikasi as $pub) {
                        echo "<div class='code'>";
                        echo "ID: {$pub->id} | Status: {$pub->status} | Created: {$pub->created_at}";
                        echo "<br>Problem URL would be: " . base_url("mahasiswa/publikasi/detail/{$pub->id}");
                        echo "<br>Correct URL should be: " . base_url("mahasiswa/publikasi/tracking/{$pub->id}");
                        echo "</div>";
                    }
                } else {
                    echo "<p class='warning'>⚠️ No publikasi records for current user</p>";
                }
                
                // Test URL construction
                echo "<p><strong>URL Tests:</strong></p>";
                echo "<p>Base URL: " . base_url() . "</p>";
                echo "<p>Publikasi Index: " . base_url('mahasiswa/publikasi') . "</p>";
                echo "<p>Tracking URL (ID 1): " . base_url('mahasiswa/publikasi/tracking/1') . "</p>";
                echo "<p class='error'>Problematic Detail URL (ID 1): " . base_url('mahasiswa/publikasi/detail/1') . "</p>";
                
            } else {
                echo "<p class='warning'>⚠️ Cannot test - user not logged in</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error testing publikasi data: " . $e->getMessage() . "</p>";
        }
        
        echo "</div>";
    }
}
?>