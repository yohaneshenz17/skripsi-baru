<?php
// File: application/controllers/Compare_debug.php
// Script untuk membandingkan data mahasiswa yang working vs not working

defined('BASEPATH') OR exit('No direct script access allowed');

class Compare_debug extends CI_Controller 
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        
        if (ENVIRONMENT !== 'development') {
            show_404();
            return;
        }
    }
    
    public function index()
    {
        header('Content-Type: text/html; charset=utf-8');
        
        echo "<h2>🔍 COMPARE DEBUG - Working vs Not Working Mahasiswa</h2>";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { border-collapse: collapse; width: 100%; margin: 10px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .working { background-color: #d4edda; }
            .not-working { background-color: #f8d7da; }
            .same { background-color: #fff3cd; }
            .different { background-color: #f1c0c7; font-weight: bold; }
        </style>";
        
        // Get data untuk kedua mahasiswa
        $mahasiswa_44 = $this->get_mahasiswa_complete_data(44); // Mahasiswa Contoh (Working)
        $mahasiswa_45 = $this->get_mahasiswa_complete_data(45); // Mahasiswa Contoh 2 (Not Working)
        
        echo "<h3>1. 👤 Data Mahasiswa Comparison</h3>";
        echo "<table>";
        echo "<tr><th>Field</th><th class='working'>Mahasiswa Contoh (Working)</th><th class='not-working'>Mahasiswa Contoh 2 (Not Working)</th><th>Status</th></tr>";
        
        $mahasiswa_fields = ['id', 'nim', 'nama', 'prodi_id', 'email', 'status'];
        foreach ($mahasiswa_fields as $field) {
            $val_44 = $mahasiswa_44['mahasiswa']->$field ?? 'NULL';
            $val_45 = $mahasiswa_45['mahasiswa']->$field ?? 'NULL';
            $status = ($val_44 == $val_45) ? 'SAME' : 'DIFFERENT';
            $class = ($val_44 == $val_45) ? 'same' : 'different';
            
            echo "<tr>";
            echo "<td><strong>{$field}</strong></td>";
            echo "<td>{$val_44}</td>";
            echo "<td>{$val_45}</td>";
            echo "<td class='{$class}'>{$status}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>2. 📋 Data Proposal Comparison</h3>";
        echo "<table>";
        echo "<tr><th>Field</th><th class='working'>Proposal ID 44 (Working)</th><th class='not-working'>Proposal ID 45 (Not Working)</th><th>Status</th></tr>";
        
        $proposal_fields = ['id', 'mahasiswa_id', 'workflow_status', 'status_kaprodi', 'status_pembimbing', 'dosen_id'];
        foreach ($proposal_fields as $field) {
            $val_44 = $mahasiswa_44['proposal']->$field ?? 'NULL';
            $val_45 = $mahasiswa_45['proposal']->$field ?? 'NULL';
            $status = ($val_44 == $val_45) ? 'SAME' : 'DIFFERENT';
            $class = ($val_44 == $val_45) ? 'same' : 'different';
            
            echo "<tr>";
            echo "<td><strong>{$field}</strong></td>";
            echo "<td>{$val_44}</td>";
            echo "<td>{$val_45}</td>";
            echo "<td class='{$class}'>{$status}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>3. 📚 Jurnal Bimbingan Comparison</h3>";
        echo "<table>";
        echo "<tr><th>Metric</th><th class='working'>Mahasiswa Contoh (Working)</th><th class='not-working'>Mahasiswa Contoh 2 (Not Working)</th><th>Status</th></tr>";
        
        $jurnal_44 = $mahasiswa_44['jurnal_stats'];
        $jurnal_45 = $mahasiswa_45['jurnal_stats'];
        
        echo "<tr>";
        echo "<td><strong>Total Jurnal</strong></td>";
        echo "<td>{$jurnal_44['total']}</td>";
        echo "<td>{$jurnal_45['total']}</td>";
        echo "<td class='" . ($jurnal_44['total'] == $jurnal_45['total'] ? 'same' : 'different') . "'>" . ($jurnal_44['total'] == $jurnal_45['total'] ? 'SAME' : 'DIFFERENT') . "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Jurnal Valid</strong></td>";
        echo "<td>{$jurnal_44['valid']}</td>";
        echo "<td>{$jurnal_45['valid']}</td>";
        echo "<td class='" . ($jurnal_44['valid'] == $jurnal_45['valid'] ? 'same' : 'different') . "'>" . ($jurnal_44['valid'] == $jurnal_45['valid'] ? 'SAME' : 'DIFFERENT') . "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Next Pertemuan</strong></td>";
        echo "<td>" . ($jurnal_44['total'] + 1) . "</td>";
        echo "<td>" . ($jurnal_45['total'] + 1) . "</td>";
        echo "<td class='same'>SAME</td>";
        echo "</tr>";
        echo "</table>";
        
        echo "<h3>4. 🔍 Controller Access Test</h3>";
        echo "<p><strong>Test apakah controller bimbingan bisa diakses oleh kedua mahasiswa:</strong></p>";
        
        // Test controller access logic
        $access_44 = $this->test_bimbingan_access(44);
        $access_45 = $this->test_bimbingan_access(45);
        
        echo "<table>";
        echo "<tr><th>Test</th><th class='working'>Mahasiswa 44</th><th class='not-working'>Mahasiswa 45</th><th>Analysis</th></tr>";
        
        echo "<tr>";
        echo "<td><strong>Proposal Found</strong></td>";
        echo "<td>" . ($access_44['proposal_found'] ? '✅ YES' : '❌ NO') . "</td>";
        echo "<td>" . ($access_45['proposal_found'] ? '✅ YES' : '❌ NO') . "</td>";
        echo "<td>" . ($access_44['proposal_found'] == $access_45['proposal_found'] ? 'SAME' : 'DIFFERENT - INI MASALAHNYA!') . "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Workflow Valid</strong></td>";
        echo "<td>" . ($access_44['workflow_valid'] ? '✅ YES' : '❌ NO') . "</td>";
        echo "<td>" . ($access_45['workflow_valid'] ? '✅ YES' : '❌ NO') . "</td>";
        echo "<td>" . ($access_44['workflow_valid'] == $access_45['workflow_valid'] ? 'SAME' : 'DIFFERENT - INI MASALAHNYA!') . "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Can Add Jurnal</strong></td>";
        echo "<td>" . ($access_44['can_add_jurnal'] ? '✅ YES' : '❌ NO') . "</td>";
        echo "<td>" . ($access_45['can_add_jurnal'] ? '✅ YES' : '❌ NO') . "</td>";
        echo "<td>" . ($access_44['can_add_jurnal'] == $access_45['can_add_jurnal'] ? 'SAME' : 'DIFFERENT - INI MASALAHNYA!') . "</td>";
        echo "</tr>";
        echo "</table>";
        
        echo "<h3>5. 🎯 Diagnosis & Recommendations</h3>";
        
        if ($access_44['can_add_jurnal'] && !$access_45['can_add_jurnal']) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>❌ PROBLEM IDENTIFIED!</h4>";
            echo "<p><strong>Mahasiswa Contoh 2 tidak bisa akses jurnal bimbingan karena:</strong></p>";
            echo "<ul>";
            if (!$access_45['proposal_found']) echo "<li>❌ Proposal tidak ditemukan</li>";
            if (!$access_45['workflow_valid']) echo "<li>❌ Workflow status tidak valid</li>";
            echo "</ul>";
            echo "</div>";
        } else if ($access_44['can_add_jurnal'] && $access_45['can_add_jurnal']) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>✅ KEDUA MAHASISWA SUDAH OK!</h4>";
            echo "<p>Jika masih ada masalah, kemungkinan di <strong>session</strong> atau <strong>browser cache</strong>.</p>";
            echo "</div>";
        }
        
        echo "<h3>6. 🔧 Next Steps</h3>";
        echo "<p>";
        echo "<a href='" . base_url('compare_debug/test_session/44') . "' style='background: #28a745; color: white; padding: 10px; text-decoration: none; margin: 5px; border-radius: 5px;'>🧪 Test Session Mahasiswa 44</a>";
        echo "<a href='" . base_url('compare_debug/test_session/45') . "' style='background: #dc3545; color: white; padding: 10px; text-decoration: none; margin: 5px; border-radius: 5px;'>🧪 Test Session Mahasiswa 45</a>";
        echo "</p>";
    }
    
    private function get_mahasiswa_complete_data($mahasiswa_id)
    {
        $data = [];
        
        // Get mahasiswa data
        $data['mahasiswa'] = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
        
        // Get proposal data
        $data['proposal'] = $this->db->get_where('proposal_mahasiswa', ['mahasiswa_id' => $mahasiswa_id])->row();
        
        // Get jurnal bimbingan stats
        if ($data['proposal']) {
            $jurnal_all = $this->db->get_where('jurnal_bimbingan', ['proposal_id' => $data['proposal']->id])->result();
            $data['jurnal_stats'] = [
                'total' => count($jurnal_all),
                'valid' => count(array_filter($jurnal_all, function($j) { return $j->status_validasi == '1'; })),
                'pending' => count(array_filter($jurnal_all, function($j) { return $j->status_validasi == '0'; }))
            ];
        } else {
            $data['jurnal_stats'] = ['total' => 0, 'valid' => 0, 'pending' => 0];
        }
        
        return $data;
    }
    
    private function test_bimbingan_access($mahasiswa_id)
    {
        $result = [
            'proposal_found' => false,
            'workflow_valid' => false,
            'can_add_jurnal' => false
        ];
        
        // Simulasi logic dari controller mahasiswa/Bimbingan.php
        $proposal = $this->db->select('pm.*, m.nama as nama_mahasiswa, m.nim')
                            ->from('proposal_mahasiswa pm')
                            ->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'inner')
                            ->where('pm.mahasiswa_id', $mahasiswa_id)
                            ->where('pm.workflow_status', 'bimbingan')
                            ->where('pm.status_kaprodi', '1')
                            ->where('pm.status_pembimbing', '1')
                            ->get()->row();
        
        if ($proposal) {
            $result['proposal_found'] = true;
            
            if ($proposal->workflow_status == 'bimbingan') {
                $result['workflow_valid'] = true;
                $result['can_add_jurnal'] = true;
            }
        }
        
        return $result;
    }
    
    public function test_session($mahasiswa_id)
    {
        echo "<h3>🧪 Test Session untuk Mahasiswa ID: {$mahasiswa_id}</h3>";
        
        // Simulate login for this mahasiswa
        $mahasiswa = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
        
        if (!$mahasiswa) {
            echo "<p style='color: red;'>❌ Mahasiswa tidak ditemukan!</p>";
            return;
        }
        
        // Set temporary session
        $session_data = [
            'id' => $mahasiswa->id,
            'nim' => $mahasiswa->nim,
            'nama' => $mahasiswa->nama,
            'prodi_id' => $mahasiswa->prodi_id,
            'email' => $mahasiswa->email,
            'role' => 'mahasiswa',
            'logged_in' => TRUE,
            'test_session' => true
        ];
        
        $this->session->set_userdata($session_data);
        
        echo "<p style='color: green;'>✅ Test session set untuk {$mahasiswa->nama}</p>";
        echo "<p><a href='" . base_url('debug_session') . "'>🔍 Check Debug Session</a></p>";
        echo "<p><a href='" . base_url('mahasiswa/bimbingan') . "'>📚 Test Access Bimbingan</a></p>";
        echo "<p><a href='" . base_url('compare_debug') . "'>🔙 Back to Compare Debug</a></p>";
    }
}
?>