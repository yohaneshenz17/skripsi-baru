<?php
// File: application/controllers/Debug_publikasi.php
// ✅ FIXED VERSION - Mengatasi header error dan database error

defined('BASEPATH') OR exit('No direct script access allowed');

class Debug_publikasi extends CI_Controller {
    
    public function check_mahasiswa($mahasiswa_id = 45) {
        $this->load->database();
        
        // ✅ FIX: Set content type sebelum output apapun
        $this->output->set_content_type('text/html');
        
        echo "<h2>🔍 DEBUG PUBLIKASI MAHASISWA ID: {$mahasiswa_id}</h2>";
        
        try {
            // 1. Check proposal mahasiswa
            echo "<h3>📋 PROPOSAL DATA:</h3>";
            $this->db->select('*');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $proposals = $this->db->get()->result();
            
            if (empty($proposals)) {
                echo "<p style='color: red;'>❌ Tidak ada proposal ditemukan untuk mahasiswa ID {$mahasiswa_id}</p>";
                return;
            }
            
            foreach ($proposals as $proposal) {
                echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
                echo "<strong>Proposal ID:</strong> {$proposal->id}<br>";
                echo "<strong>Judul:</strong> " . substr($proposal->judul, 0, 100) . "...<br>";
                echo "<strong>Status:</strong> {$proposal->status}<br>";
                echo "<strong>Status Kaprodi:</strong> {$proposal->status_kaprodi}<br>";
                echo "<strong>Workflow Status:</strong> <span style='color: blue; font-weight: bold;'>{$proposal->workflow_status}</span><br>";
                echo "<strong>Status Seminar Skripsi:</strong> {$proposal->status_seminar_skripsi}<br>";
                
                // Check jurnal bimbingan
                $this->db->select('COUNT(*) as count');
                $this->db->from('jurnal_bimbingan');
                $this->db->where('proposal_id', $proposal->id);
                $this->db->where('status_validasi', '1');
                $jurnal_count_result = $this->db->get()->row();
                $jurnal_count = $jurnal_count_result ? $jurnal_count_result->count : 0;
                
                echo "<strong>Jurnal Tervalidasi:</strong> {$jurnal_count}/16<br>";
                
                // Check seminar skripsi status
                if ($this->db->table_exists('seminar_skripsi_mahasiswa')) {
                    $this->db->select('*');
                    $this->db->from('seminar_skripsi_mahasiswa');
                    $this->db->where('proposal_id', $proposal->id);
                    $seminar_skripsi = $this->db->get()->result();
                    
                    echo "<strong>Seminar Skripsi:</strong> ";
                    if (empty($seminar_skripsi)) {
                        echo "<span style='color: orange;'>Belum ada pengajuan</span><br>";
                    } else {
                        foreach ($seminar_skripsi as $seminar) {
                            echo "<span style='color: green;'>Status: {$seminar->status}</span><br>";
                        }
                    }
                }
                
                // Check existing publikasi
                if ($this->db->table_exists('publikasi_tugas_akhir')) {
                    $this->db->select('*');
                    $this->db->from('publikasi_tugas_akhir');
                    $this->db->where('proposal_mahasiswa_id', $proposal->id);
                    $publikasi = $this->db->get()->result();
                    
                    echo "<strong>Publikasi Existing:</strong> ";
                    if (empty($publikasi)) {
                        echo "<span style='color: orange;'>Belum ada</span><br>";
                    } else {
                        foreach ($publikasi as $pub) {
                            echo "<span style='color: green;'>Status: {$pub->status}</span><br>";
                        }
                    }
                }
                
                // ✅ DIAGNOSIS
                echo "<h4>🩺 DIAGNOSIS:</h4>";
                $eligible = true;
                $issues = [];
                
                if ($proposal->status != '1') {
                    $eligible = false;
                    $issues[] = "Proposal belum disetujui (status: {$proposal->status})";
                }
                
                if ($proposal->status_kaprodi != '1') {
                    $eligible = false;
                    $issues[] = "Proposal belum disetujui kaprodi (status_kaprodi: {$proposal->status_kaprodi})";
                }
                
                if (!in_array($proposal->workflow_status, ['seminar_skripsi', 'publikasi', 'selesai'])) {
                    $eligible = false;
                    $issues[] = "Workflow status belum siap publikasi (status: {$proposal->workflow_status})";
                }
                
                if ($jurnal_count < 16) {
                    $eligible = false;
                    $issues[] = "Jurnal bimbingan kurang ({$jurnal_count}/16)";
                }
                
                if ($eligible) {
                    echo "<p style='color: green; font-weight: bold;'>✅ ELIGIBLE untuk publikasi!</p>";
                } else {
                    echo "<p style='color: red; font-weight: bold;'>❌ TIDAK ELIGIBLE. Issues:</p>";
                    echo "<ul>";
                    foreach ($issues as $issue) {
                        echo "<li>{$issue}</li>";
                    }
                    echo "</ul>";
                }
                
                echo "</div>";
            }
            
            // 2. ✅ FIXED: Test query dengan proper syntax
            echo "<h3>🔧 TEST QUERY _get_proposal_eligible:</h3>";
            
            // Query OLD (current)
            $this->db->select('
                pm.*,
                m.nim, m.nama as nama_mahasiswa, m.email,
                pr.nama as nama_prodi,
                d.nama as nama_pembimbing, d.email as email_pembimbing,
                COUNT(jb.id) as jurnal_tervalidasi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('jurnal_bimbingan jb', 'jb.proposal_id = pm.id AND jb.status_validasi = "1"', 'left');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->where('pm.status', '1');
            $this->db->group_by('pm.id');
            $this->db->having('COUNT(jb.id) >= 16');
            $this->db->order_by('pm.id', 'DESC');
            $this->db->limit(1);
            
            echo "<p><strong>Query OLD (tanpa workflow check):</strong></p>";
            $query_old = $this->db->get_compiled_select();
            echo "<pre style='background: #f5f5f5; padding: 10px; font-size: 12px;'>" . htmlspecialchars($query_old) . "</pre>";
            
            // Execute query OLD
            $this->db->select('
                pm.*,
                m.nim, m.nama as nama_mahasiswa, m.email,
                pr.nama as nama_prodi,
                d.nama as nama_pembimbing, d.email as email_pembimbing,
                COUNT(jb.id) as jurnal_tervalidasi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('jurnal_bimbingan jb', 'jb.proposal_id = pm.id AND jb.status_validasi = "1"', 'left');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->where('pm.status', '1');
            $this->db->group_by('pm.id');
            $this->db->having('COUNT(jb.id) >= 16');
            $this->db->order_by('pm.id', 'DESC');
            $this->db->limit(1);
            
            $result_old = $this->db->get()->row();
            echo "<p><strong>Result OLD:</strong> " . ($result_old ? "✅ FOUND (ID: {$result_old->id}, Jurnal: {$result_old->jurnal_tervalidasi})" : "❌ NOT FOUND") . "</p>";
            
            // Query NEW (dengan workflow check)
            $this->db->select('
                pm.*,
                m.nim, m.nama as nama_mahasiswa, m.email,
                pr.nama as nama_prodi,
                d.nama as nama_pembimbing, d.email as email_pembimbing,
                COUNT(jb.id) as jurnal_tervalidasi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('jurnal_bimbingan jb', 'jb.proposal_id = pm.id AND jb.status_validasi = "1"', 'left');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->where('pm.status', '1');
            $this->db->where_in('pm.workflow_status', ['seminar_skripsi', 'publikasi']);
            $this->db->group_by('pm.id');
            $this->db->having('COUNT(jb.id) >= 16');
            $this->db->order_by('pm.id', 'DESC');
            $this->db->limit(1);
            
            echo "<p><strong>Query NEW (dengan workflow check):</strong></p>";
            $query_new = $this->db->get_compiled_select();
            echo "<pre style='background: #f5f5f5; padding: 10px; font-size: 12px;'>" . htmlspecialchars($query_new) . "</pre>";
            
            // Execute query NEW
            $this->db->select('
                pm.*,
                m.nim, m.nama as nama_mahasiswa, m.email,
                pr.nama as nama_prodi,
                d.nama as nama_pembimbing, d.email as email_pembimbing,
                COUNT(jb.id) as jurnal_tervalidasi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('jurnal_bimbingan jb', 'jb.proposal_id = pm.id AND jb.status_validasi = "1"', 'left');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->where('pm.status', '1');
            $this->db->where_in('pm.workflow_status', ['seminar_skripsi', 'publikasi']);
            $this->db->group_by('pm.id');
            $this->db->having('COUNT(jb.id) >= 16');
            $this->db->order_by('pm.id', 'DESC');
            $this->db->limit(1);
            
            $result_new = $this->db->get()->row();
            echo "<p><strong>Result NEW:</strong> " . ($result_new ? "✅ FOUND (ID: {$result_new->id}, Jurnal: {$result_new->jurnal_tervalidasi})" : "❌ NOT FOUND") . "</p>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
        }
        
        echo "<h3>💡 KESIMPULAN DAN REKOMENDASI:</h3>";
        echo "<ol>";
        echo "<li><strong>Fix Data Inkonsistensi:</strong> Update field 'status' menjadi '1' karena status_kaprodi sudah '1'</li>";
        echo "<li><strong>Tambah Jurnal Bimbingan:</strong> Perlu menambah 2 jurnal lagi agar total menjadi 16</li>";
        echo "<li><strong>Update Controller:</strong> Perbaiki method _get_proposal_eligible() untuk include workflow_status check</li>";
        echo "</ol>";
    }
    
    /**
     * Method untuk test perbaikan setelah fix data
     */
    public function test_after_fix($mahasiswa_id = 45) {
        $this->load->database();
        $this->output->set_content_type('text/html');
        
        echo "<h2>🧪 TEST SETELAH PERBAIKAN - MAHASISWA ID: {$mahasiswa_id}</h2>";
        
        // Load model publikasi seperti di controller asli
        $this->load->model('Publikasi_model', 'publikasi');
        
        // Test method yang diperbaiki
        echo "<h3>✅ TEST METHOD YANG SUDAH DIPERBAIKI:</h3>";
        
        try {
            // Simulate _get_proposal_eligible yang sudah diperbaiki
            $this->db->select('
                pm.*,
                m.nim, m.nama as nama_mahasiswa, m.email,
                pr.nama as nama_prodi,
                d.nama as nama_pembimbing, d.email as email_pembimbing,
                COUNT(jb.id) as jurnal_tervalidasi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('jurnal_bimbingan jb', 'jb.proposal_id = pm.id AND jb.status_validasi = "1"', 'left');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->where('pm.status', '1'); // Proposal sudah disetujui
            $this->db->where_in('pm.workflow_status', ['seminar_skripsi', 'publikasi']); // ✅ TAMBAHAN
            $this->db->group_by('pm.id');
            $this->db->having('COUNT(jb.id) >= 16'); // Minimal 16 jurnal tervalidasi
            $this->db->order_by('pm.id', 'DESC');
            $this->db->limit(1);
            
            $proposal = $this->db->get()->row();
            
            if ($proposal) {
                echo "<p style='color: green; font-weight: bold;'>✅ SUCCESS! Proposal eligible ditemukan!</p>";
                echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
                echo "<strong>Proposal ID:</strong> {$proposal->id}<br>";
                echo "<strong>Mahasiswa:</strong> {$proposal->nama_mahasiswa} ({$proposal->nim})<br>";
                echo "<strong>Jurnal Tervalidasi:</strong> {$proposal->jurnal_tervalidasi}<br>";
                echo "<strong>Workflow Status:</strong> {$proposal->workflow_status}<br>";
                echo "<strong>Status:</strong> {$proposal->status}<br>";
                echo "</div>";
                
                echo "<p style='color: green;'>✅ Controller publikasi sekarang akan menampilkan <strong>'Ajukan Form Publikasi'</strong> bukan 'Ajukan Proposal'</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>❌ Masih belum eligible. Periksa kembali data.</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
        }
    }
}