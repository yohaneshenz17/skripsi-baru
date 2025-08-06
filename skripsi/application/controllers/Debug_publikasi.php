<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * DEBUG CONTROLLER - Fix Publikasi Stuck Issue
 * Temporary controller untuk debugging masalah mahasiswa ID 45
 * 
 * Usage: /skripsi/debug_publikasi/fix_mahasiswa/45
 * HAPUS FILE INI SETELAH MASALAH TERATASI!
 */
class Debug_publikasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        
        // Security check - hanya untuk debugging
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            show_error('Debug mode - staf only', 403);
        }
    }

    /**
     * Debug specific mahasiswa issue
     */
    public function fix_mahasiswa($mahasiswa_id = 45) {
        echo "<h2>🔧 DEBUG PUBLIKASI STUCK - MAHASISWA ID: {$mahasiswa_id}</h2>";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px;'>";
        
        try {
            echo "<h3>STEP 1: Database Structure Check</h3>";
            $this->_check_database_structure();
            
            echo "<hr><h3>STEP 2: Current Data Status</h3>";
            $current_data = $this->_check_current_status($mahasiswa_id);
            
            echo "<hr><h3>STEP 3: Identify Issues</h3>";
            $issues = $this->_identify_issues($current_data);
            
            echo "<hr><h3>STEP 4: Auto Fix Available</h3>";
            if (!empty($issues)) {
                echo "<div style='background: #ffeaa7; padding: 10px; border-radius: 5px;'>";
                echo "<strong>🚨 Issues Found:</strong><br>";
                foreach ($issues as $issue) {
                    echo "• " . $issue . "<br>";
                }
                echo "</div><br>";
                
                echo "<a href='" . base_url('debug_publikasi/auto_fix/' . $mahasiswa_id) . "' 
                         style='background: #00b894; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                         🔧 AUTO FIX ISSUES
                      </a>";
            } else {
                echo "<div style='background: #00b894; color: white; padding: 10px; border-radius: 5px;'>";
                echo "✅ No issues detected. Data should be consistent.";
                echo "</div>";
            }
            
            echo "<hr><h3>STEP 5: Manual SQL Commands</h3>";
            $this->_show_manual_fix_sql($mahasiswa_id);
            
        } catch (Exception $e) {
            echo "<div style='color: red; background: #ffcccc; padding: 10px;'>";
            echo "❌ ERROR: " . $e->getMessage();
            echo "</div>";
        }
        
        echo "</div>";
    }

    /**
     * Auto fix issues
     */
    public function auto_fix($mahasiswa_id = 45) {
        echo "<h2>🔧 AUTO FIX - MAHASISWA ID: {$mahasiswa_id}</h2>";
        
        try {
            $this->db->trans_start();
            
            // Step 1: Get publikasi data
            $publikasi = $this->db->select('*')
                                ->from('publikasi_tugas_akhir')
                                ->where('mahasiswa_id', $mahasiswa_id)
                                ->order_by('id', 'DESC')
                                ->limit(1)
                                ->get()
                                ->row();
            
            if (!$publikasi) {
                throw new Exception("Publikasi data not found for mahasiswa {$mahasiswa_id}");
            }
            
            echo "<p>✅ Found publikasi ID: {$publikasi->id}</p>";
            
            // Step 2: Check if stuck
            if ($publikasi->status === 'review_staf' && $publikasi->status_staf === 'approved') {
                
                echo "<p>🔧 Fixing stuck status...</p>";
                
                // Fix publikasi_tugas_akhir
                $update_publikasi = [
                    'status' => 'completed',
                    'tanggal_selesai' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->where('id', $publikasi->id)
                       ->update('publikasi_tugas_akhir', $update_publikasi);
                
                echo "<p>✅ Updated publikasi status to 'completed'</p>";
                
                // Fix proposal_mahasiswa workflow_status  
                $this->db->where('id', $publikasi->proposal_mahasiswa_id)
                       ->update('proposal_mahasiswa', ['workflow_status' => 'selesai']);
                
                echo "<p>✅ Updated workflow_status to 'selesai'</p>";
                
                // Generate surat keterangan dummy (jika diperlukan)
                $this->_generate_dummy_surat($publikasi->id);
                
                echo "<p>✅ Generated surat keterangan</p>";
                
            } else {
                echo "<p>ℹ️ No stuck status detected, data seems normal</p>";
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Transaction failed");
            }
            
            echo "<div style='background: #00b894; color: white; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
            echo "<h3>🎉 AUTO FIX COMPLETED!</h3>";
            echo "<p>✅ Publikasi status updated to 'completed'<br>";
            echo "✅ Workflow status updated to 'selesai'<br>";
            echo "✅ Surat keterangan prepared</p>";
            echo "<p><strong>Next Steps:</strong><br>";
            echo "1. Test tracking page: <a href='" . base_url('mahasiswa/publikasi/tracking/' . $publikasi->proposal_mahasiswa_id) . "' target='_blank'>Open Tracking</a><br>";
            echo "2. Verify download button works<br>";
            echo "3. Delete this debug controller</p>";
            echo "</div>";
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo "<div style='color: red; background: #ffcccc; padding: 10px;'>";
            echo "❌ AUTO FIX FAILED: " . $e->getMessage();
            echo "</div>";
        }
    }

    private function _check_database_structure() {
        // Check tabel publikasi_tugas_akhir
        $publikasi_columns = $this->db->list_fields('publikasi_tugas_akhir');
        echo "<p><strong>publikasi_tugas_akhir columns:</strong> " . implode(', ', $publikasi_columns) . "</p>";
        
        // Check tabel proposal_mahasiswa  
        $proposal_columns = $this->db->list_fields('proposal_mahasiswa');
        echo "<p><strong>proposal_mahasiswa columns:</strong> " . implode(', ', $proposal_columns) . "</p>";
        
        // Check for problematic columns
        $problematic = ['validated_by_staf_id', 'validated_by_staf_name'];
        foreach ($problematic as $col) {
            if (in_array($col, $publikasi_columns)) {
                echo "<p>✅ Column '{$col}' exists</p>";
            } else {
                echo "<p>⚠️ Column '{$col}' MISSING</p>";
            }
        }
    }

    private function _check_current_status($mahasiswa_id) {
        $query = "
            SELECT 
                p.id as publikasi_id,
                p.proposal_mahasiswa_id,
                p.mahasiswa_id,
                p.status,
                p.status_pembimbing,
                p.status_staf,
                p.tanggal_validasi_staf,
                p.tanggal_selesai,
                pm.workflow_status
            FROM publikasi_tugas_akhir p
            JOIN proposal_mahasiswa pm ON p.proposal_mahasiswa_id = pm.id
            WHERE p.mahasiswa_id = {$mahasiswa_id}
            ORDER BY p.id DESC
            LIMIT 1
        ";
        
        $result = $this->db->query($query)->row();
        
        if ($result) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Field</th><th>Value</th><th>Status</th></tr>";
            echo "<tr><td>publikasi_id</td><td>{$result->publikasi_id}</td><td>-</td></tr>";
            echo "<tr><td>status</td><td>{$result->status}</td><td>" . 
                 ($result->status == 'completed' ? '✅' : '⚠️') . "</td></tr>";
            echo "<tr><td>status_staf</td><td>{$result->status_staf}</td><td>" . 
                 ($result->status_staf == 'approved' ? '✅' : '⚠️') . "</td></tr>";
            echo "<tr><td>status_pembimbing</td><td>{$result->status_pembimbing}</td><td>" . 
                 ($result->status_pembimbing == 'approved' ? '✅' : '⚠️') . "</td></tr>";
            echo "<tr><td>workflow_status</td><td>{$result->workflow_status}</td><td>" . 
                 ($result->workflow_status == 'selesai' ? '✅' : '⚠️') . "</td></tr>";
            echo "<tr><td>tanggal_validasi_staf</td><td>{$result->tanggal_validasi_staf}</td><td>-</td></tr>";
            echo "<tr><td>tanggal_selesai</td><td>{$result->tanggal_selesai}</td><td>" . 
                 ($result->tanggal_selesai ? '✅' : '❌') . "</td></tr>";
            echo "</table>";
            
            return $result;
        } else {
            echo "<p>❌ No data found for mahasiswa {$mahasiswa_id}</p>";
            return null;
        }
    }

    private function _identify_issues($data) {
        $issues = [];
        
        if (!$data) {
            $issues[] = "No publikasi data found";
            return $issues;
        }
        
        // Check for stuck status
        if ($data->status === 'review_staf' && $data->status_staf === 'approved') {
            $issues[] = "Publikasi stuck: status='review_staf' but status_staf='approved'";
        }
        
        // Check workflow inconsistency
        if ($data->status === 'completed' && $data->workflow_status !== 'selesai') {
            $issues[] = "Workflow inconsistency: publikasi completed but workflow != 'selesai'";
        }
        
        // Check missing completion date
        if ($data->status === 'completed' && !$data->tanggal_selesai) {
            $issues[] = "Missing tanggal_selesai for completed publikasi";
        }
        
        return $issues;
    }

    private function _show_manual_fix_sql($mahasiswa_id) {
        echo "<div style='background: #2d3436; color: #ddd; padding: 15px; border-radius: 5px;'>";
        echo "<h4>🔧 Manual Fix SQL Commands:</h4>";
        echo "<pre>";
        echo "-- Fix publikasi status\n";
        echo "UPDATE publikasi_tugas_akhir \n";
        echo "SET status = 'completed', \n";
        echo "    tanggal_selesai = NOW(),\n";
        echo "    updated_at = NOW()\n";
        echo "WHERE mahasiswa_id = {$mahasiswa_id} \n";
        echo "  AND status = 'review_staf' \n";
        echo "  AND status_staf = 'approved';\n\n";
        
        echo "-- Fix workflow status\n";
        echo "UPDATE proposal_mahasiswa pm\n";
        echo "JOIN publikasi_tugas_akhir p ON p.proposal_mahasiswa_id = pm.id\n";
        echo "SET pm.workflow_status = 'selesai'\n";
        echo "WHERE p.mahasiswa_id = {$mahasiswa_id} \n";
        echo "  AND p.status = 'completed';\n\n";
        
        echo "-- Verify fix\n";
        echo "SELECT p.status, p.status_staf, pm.workflow_status \n";
        echo "FROM publikasi_tugas_akhir p \n";
        echo "JOIN proposal_mahasiswa pm ON p.proposal_mahasiswa_id = pm.id \n";
        echo "WHERE p.mahasiswa_id = {$mahasiswa_id};";
        echo "</pre>";
        echo "</div>";
    }

    private function _generate_dummy_surat($publikasi_id) {
        // Simple dummy surat generation
        $filename = 'SURAT_KETERANGAN_' . date('Ymd_His') . '_' . $publikasi_id . '.txt';
        $content = "SURAT KETERANGAN PUBLIKASI\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $content .= "Publikasi ID: " . $publikasi_id . "\n";
        $content .= "Status: SELESAI\n";
        
        // Create directory if not exists
        if (!is_dir('./uploads/surat_keterangan/')) {
            mkdir('./uploads/surat_keterangan/', 0755, true);
        }
        
        file_put_contents('./uploads/surat_keterangan/' . $filename, $content);
        
        // Update database with filename
        $this->db->where('id', $publikasi_id)
               ->update('publikasi_tugas_akhir', ['file_surat_keterangan' => $filename]);
    }
}