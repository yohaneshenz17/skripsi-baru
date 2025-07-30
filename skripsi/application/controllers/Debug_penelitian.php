<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Debug Controller untuk Troubleshooting Masalah Penelitian
 * 
 * Temporary controller untuk debugging masalah redirect ke dashboard
 * HAPUS SETELAH MASALAH SOLVED!
 * 
 * URL: /debug_penelitian
 */
class Debug_penelitian extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Hanya izinkan di development
        if (ENVIRONMENT !== 'development') {
            show_404();
        }
    }

    /**
     * Main debug page
     * URL: /debug_penelitian
     */
    public function index() {
        echo "<h1>🔧 DEBUG PENELITIAN MODULE</h1>";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .success { color: green; font-weight: bold; }
            .error { color: red; font-weight: bold; }
            .warning { color: orange; font-weight: bold; }
            .info { color: blue; }
            .debug-section { 
                border: 1px solid #ddd; 
                padding: 15px; 
                margin: 10px 0; 
                background: #f9f9f9; 
                border-radius: 5px;
            }
            table { border-collapse: collapse; width: 100%; margin: 10px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
        </style>";

        // 1. CHECK SESSION DATA
        echo "<div class='debug-section'>";
        echo "<h3>1. 📋 SESSION DATA CHECK</h3>";
        
        $session_data = $this->session->userdata();
        echo "<table>";
        echo "<tr><th>Key</th><th>Value</th><th>Status</th></tr>";
        
        $required_session = ['logged_in', 'id', 'level', 'nama'];
        foreach ($required_session as $key) {
            $value = $this->session->userdata($key);
            $status = $value ? '✅' : '❌';
            echo "<tr><td><strong>$key</strong></td><td>$value</td><td>$status</td></tr>";
        }
        echo "</table>";
        
        $logged_in = $this->session->userdata('logged_in');
        $level = $this->session->userdata('level');
        
        echo "<p><strong>Authentication Status:</strong> ";
        if ($logged_in && $level == '4') {
            echo "<span class='success'>✅ MAHASISWA AUTHENTICATED (Level: $level)</span>";
        } elseif ($logged_in && $level == '3') {
            echo "<span class='warning'>⚠️ MAHASISWA AUTHENTICATED (Level: $level) - Controller expects level '4'</span>";
        } elseif ($logged_in) {
            echo "<span class='error'>❌ WRONG LEVEL: $level (Expected: 4 or 3)</span>";
        } else {
            echo "<span class='error'>❌ NOT AUTHENTICATED</span>";
        }
        echo "</p>";
        echo "</div>";

        // 2. CHECK DATABASE CONNECTION
        echo "<div class='debug-section'>";
        echo "<h3>2. 🗄️ DATABASE CONNECTION CHECK</h3>";
        
        try {
            $db_check = $this->db->get('mahasiswa', 1)->row();
            echo "<p class='success'>✅ Database connection OK</p>";
            echo "<p><strong>Sample mahasiswa data:</strong> " . ($db_check ? "ID: {$db_check->id}, Nama: {$db_check->nama}" : "No data") . "</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
        }
        echo "</div>";

        // 3. CHECK PENELITIAN MODEL
        echo "<div class='debug-section'>";
        echo "<h3>3. 🔧 PENELITIAN MODEL CHECK</h3>";
        
        try {
            $this->load->model('Penelitian_model', 'penelitian');
            echo "<p class='success'>✅ Penelitian_model loaded successfully</p>";
            
            // Test basic method
            $mahasiswa_id = $this->session->userdata('id');
            if ($mahasiswa_id) {
                $test_result = $this->penelitian->get_permohonan_by_mahasiswa($mahasiswa_id);
                echo "<p><strong>Test get_permohonan_by_mahasiswa:</strong> ";
                if ($test_result['error']) {
                    echo "<span class='warning'>⚠️ " . $test_result['message'] . "</span>";
                } else {
                    echo "<span class='success'>✅ Success (" . count($test_result['data']) . " records)</span>";
                }
                echo "</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Model error: " . $e->getMessage() . "</p>";
        }
        echo "</div>";

        // 4. CHECK PROPOSAL DATA
        echo "<div class='debug-section'>";
        echo "<h3>4. 📄 PROPOSAL DATA CHECK</h3>";
        
        $mahasiswa_id = $this->session->userdata('id');
        if ($mahasiswa_id) {
            try {
                // Check proposal mahasiswa
                $this->db->select('pm.*, m.nama as nama_mahasiswa, d.nama as nama_pembimbing');
                $this->db->from('proposal_mahasiswa pm');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
                $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
                $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
                $this->db->order_by('pm.id', 'DESC');
                
                $proposals = $this->db->get()->result();
                
                echo "<p><strong>Total proposals:</strong> " . count($proposals) . "</p>";
                
                if ($proposals) {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Judul</th><th>Status Kaprodi</th><th>Status Pembimbing</th><th>Workflow Status</th><th>Pembimbing</th></tr>";
                    
                    foreach ($proposals as $proposal) {
                        $status_kaprodi = $proposal->status_kaprodi == '1' ? '✅ Disetujui' : ($proposal->status_kaprodi == '2' ? '❌ Ditolak' : '⏳ Pending');
                        $status_pembimbing = $proposal->status_pembimbing == '1' ? '✅ Disetujui' : ($proposal->status_pembimbing == '2' ? '❌ Ditolak' : '⏳ Pending');
                        
                        echo "<tr>";
                        echo "<td>{$proposal->id}</td>";
                        echo "<td>" . substr($proposal->judul, 0, 50) . "...</td>";
                        echo "<td>$status_kaprodi</td>";
                        echo "<td>$status_pembimbing</td>";
                        echo "<td>{$proposal->workflow_status}</td>";
                        echo "<td>" . ($proposal->nama_pembimbing ?? 'Belum ditetapkan') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    
                    // Check proposal aktif criteria
                    echo "<h4>📋 PROPOSAL AKTIF CRITERIA CHECK:</h4>";
                    $proposal_aktif = null;
                    foreach ($proposals as $proposal) {
                        if ($proposal->status_kaprodi == '1') {  // Disetujui kaprodi
                            $proposal_aktif = $proposal;
                            break;
                        }
                    }
                    
                    if ($proposal_aktif) {
                        echo "<p class='success'>✅ Found ACTIVE PROPOSAL: ID {$proposal_aktif->id}</p>";
                        echo "<p><strong>Details:</strong><br>";
                        echo "- Judul: {$proposal_aktif->judul}<br>";
                        echo "- Status Kaprodi: {$proposal_aktif->status_kaprodi}<br>";
                        echo "- Status Pembimbing: {$proposal_aktif->status_pembimbing}<br>";
                        echo "- Workflow Status: {$proposal_aktif->workflow_status}<br>";
                        echo "- Pembimbing: {$proposal_aktif->nama_pembimbing}</p>";
                        
                        // Test eligibility
                        if (class_exists('Penelitian_model')) {
                            try {
                                $this->load->model('Penelitian_model', 'penelitian');
                                $eligibility = $this->penelitian->check_eligibility($proposal_aktif->id, $mahasiswa_id);
                                
                                echo "<h4>🔍 ELIGIBILITY CHECK:</h4>";
                                if ($eligibility['error']) {
                                    echo "<p class='error'>❌ Error: {$eligibility['message']}</p>";
                                } else {
                                    echo "<p><strong>Eligible:</strong> " . ($eligibility['eligible'] ? '✅ YES' : '❌ NO') . "</p>";
                                    
                                    if (isset($eligibility['requirements'])) {
                                        echo "<table>";
                                        echo "<tr><th>Requirement</th><th>Status</th><th>Detail</th></tr>";
                                        foreach ($eligibility['requirements'] as $req_name => $req_data) {
                                            $status_icon = $req_data['status'] == 'OK' ? '✅' : '❌';
                                            echo "<tr>";
                                            echo "<td>" . ucwords(str_replace('_', ' ', $req_name)) . "</td>";
                                            echo "<td>$status_icon {$req_data['status']}</td>";
                                            echo "<td>{$req_data['detail']}</td>";
                                            echo "</tr>";
                                        }
                                        echo "</table>";
                                    }
                                }
                            } catch (Exception $e) {
                                echo "<p class='error'>❌ Eligibility check error: {$e->getMessage()}</p>";
                            }
                        }
                        
                    } else {
                        echo "<p class='error'>❌ NO ACTIVE PROPOSAL FOUND (Status Kaprodi = '1')</p>";
                        echo "<p class='info'>💡 This is why you're redirected to dashboard!</p>";
                    }
                    
                } else {
                    echo "<p class='error'>❌ No proposals found for this mahasiswa</p>";
                }
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Proposal check error: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='error'>❌ No mahasiswa_id in session</p>";
        }
        echo "</div>";

        // 5. CHECK CONTROLLER FILE
        echo "<div class='debug-section'>";
        echo "<h3>5. 📁 CONTROLLER FILE CHECK</h3>";
        
        $controller_path = APPPATH . 'controllers/mahasiswa/Penelitian.php';
        if (file_exists($controller_path)) {
            echo "<p class='success'>✅ Controller file exists: $controller_path</p>";
            echo "<p><strong>File size:</strong> " . number_format(filesize($controller_path)) . " bytes</p>";
            echo "<p><strong>Last modified:</strong> " . date('Y-m-d H:i:s', filemtime($controller_path)) . "</p>";
        } else {
            echo "<p class='error'>❌ Controller file NOT FOUND: $controller_path</p>";
        }

        // Check views
        $view_files = [
            'mahasiswa/penelitian/index.php',
            'mahasiswa/penelitian/form_ajukan.php',
            'mahasiswa/penelitian/detail.php'
        ];
        
        echo "<p><strong>View files check:</strong></p>";
        echo "<ul>";
        foreach ($view_files as $view_file) {
            $view_path = APPPATH . 'views/' . $view_file;
            if (file_exists($view_path)) {
                echo "<li class='success'>✅ $view_file</li>";
            } else {
                echo "<li class='error'>❌ $view_file - NOT FOUND</li>";
            }
        }
        echo "</ul>";
        echo "</div>";

        // 6. CHECK ROUTES
        echo "<div class='debug-section'>";
        echo "<h3>6. 🛤️ ROUTES CHECK</h3>";
        
        // Load routes
        include(APPPATH . 'config/routes.php');
        
        $penelitian_routes = [];
        foreach ($route as $pattern => $target) {
            if (strpos($pattern, 'penelitian') !== false || strpos($target, 'penelitian') !== false) {
                $penelitian_routes[$pattern] = $target;
            }
        }
        
        echo "<p><strong>Penelitian-related routes:</strong></p>";
        if ($penelitian_routes) {
            echo "<table>";
            echo "<tr><th>URL Pattern</th><th>Target</th></tr>";
            foreach ($penelitian_routes as $pattern => $target) {
                echo "<tr><td>$pattern</td><td>$target</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warning'>⚠️ No penelitian routes found</p>";
        }
        echo "</div>";

        // 7. RECOMMENDATIONS
        echo "<div class='debug-section'>";
        echo "<h3>7. 💡 RECOMMENDATIONS & FIXES</h3>";
        
        $recommendations = [];
        
        // Check authentication
        if (!$this->session->userdata('logged_in')) {
            $recommendations[] = "🔐 <strong>Login Issue:</strong> User not authenticated. Check auth system.";
        }
        
        // Check level
        $level = $this->session->userdata('level');
        if ($level != '4' && $level != '3') {
            $recommendations[] = "👤 <strong>Level Issue:</strong> Current level is '$level'. Expected '4' or '3' for mahasiswa.";
        }
        
        // Check proposal
        if (!$mahasiswa_id) {
            $recommendations[] = "🆔 <strong>Session Issue:</strong> No mahasiswa_id in session.";
        }
        
        // Check controller file
        if (!file_exists($controller_path)) {
            $recommendations[] = "📁 <strong>Controller Missing:</strong> Create Penelitian.php controller.";
        }
        
        // Check model
        try {
            $this->load->model('Penelitian_model');
        } catch (Exception $e) {
            $recommendations[] = "🔧 <strong>Model Issue:</strong> Penelitian_model error - " . $e->getMessage();
        }
        
        if ($recommendations) {
            echo "<ol>";
            foreach ($recommendations as $rec) {
                echo "<li>$rec</li>";
            }
            echo "</ol>";
        } else {
            echo "<p class='success'>✅ No major issues detected. System should work.</p>";
        }
        
        // Quick fixes
        echo "<h4>🔧 Quick Fixes:</h4>";
        echo "<ol>";
        echo "<li><strong>Update Controller Authentication:</strong> Change level check from '4' to '3' if mahasiswa level is '3'</li>";
        echo "<li><strong>Create Proposal:</strong> If no active proposal, create one through proper workflow</li>";
        echo "<li><strong>Test Simple URL:</strong> Try direct access: <a href='" . base_url('mahasiswa/penelitian') . "' target='_blank'>" . base_url('mahasiswa/penelitian') . "</a></li>";
        echo "<li><strong>Check Error Logs:</strong> Look at application/logs/ for PHP errors</li>";
        echo "</ol>";
        echo "</div>";

        echo "<hr>";
        echo "<h3 style='color: green;'>✅ DEBUG COMPLETED</h3>";
        echo "<p><strong>Next steps:</strong></p>";
        echo "<ol>";
        echo "<li>Apply recommended fixes above</li>";
        echo "<li>Replace complex Penelitian_model with simplified version</li>";
        echo "<li>Test the /mahasiswa/penelitian URL again</li>";
        echo "<li>Remove this debug controller after fixing</li>";
        echo "</ol>";
    }

    /**
     * Test direct controller access
     * URL: /debug_penelitian/test_controller
     */
    public function test_controller() {
        echo "<h2>🧪 CONTROLLER TEST</h2>";
        
        try {
            // Try to instantiate controller manually
            $controller_path = APPPATH . 'controllers/mahasiswa/Penelitian.php';
            
            if (file_exists($controller_path)) {
                echo "<p class='success'>✅ Controller file found</p>";
                
                // Try to include it
                require_once($controller_path);
                
                if (class_exists('Penelitian')) {
                    echo "<p class='success'>✅ Penelitian class loaded</p>";
                    
                    // Try to instantiate (but don't run constructor to avoid redirects)
                    echo "<p class='info'>💡 Controller class exists and is valid</p>";
                    
                } else {
                    echo "<p class='error'>❌ Penelitian class not found in file</p>";
                }
                
            } else {
                echo "<p class='error'>❌ Controller file not found</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Controller test error: " . $e->getMessage() . "</p>";
        }
        
        echo "<p><a href='" . base_url('debug_penelitian') . "'>← Back to main debug</a></p>";
    }
}

/* End of file Debug_penelitian.php */