<?php
// DEBUG SCRIPT: Cek status Mahasiswa Contoh untuk Seminar Skripsi
// Jalankan ini untuk memverifikasi apakah mahasiswa contoh benar-benar memenuhi syarat

defined('BASEPATH') OR exit('No direct script access allowed');

class Debug_mahasiswa_contoh extends CI_Controller 
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        
        // SECURITY: Hanya allow di development atau untuk admin
        if (ENVIRONMENT !== 'development' && $this->session->userdata('level') != '1') {
            show_404();
        }
    }

    public function index()
    {
        echo "<h2>🔍 DEBUG: Status Mahasiswa Contoh untuk Seminar Skripsi</h2>";
        echo "<hr>";
        
        $mahasiswa_id = 44;
        $proposal_id = 44;
        
        try {
            // 1. CEK DATA PROPOSAL_MAHASISWA
            echo "<h3>1. Data Proposal Mahasiswa (ID: $proposal_id)</h3>";
            $query = $this->db->get_where('proposal_mahasiswa', ['id' => $proposal_id]);
            $proposal = $query->row();
            
            if ($proposal) {
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-bottom: 20px;'>";
                echo "<tr><th style='background: #f0f0f0;'>Field</th><th style='background: #f0f0f0;'>Value</th><th style='background: #f0f0f0;'>Status</th></tr>";
                
                echo "<tr>";
                echo "<td><strong>workflow_status</strong></td>";
                echo "<td>" . $proposal->workflow_status . "</td>";
                $workflow_ok = in_array($proposal->workflow_status, ['penelitian', 'seminar_skripsi']);
                echo "<td>" . ($workflow_ok ? "✅ OK" : "❌ BELUM ('penelitian' atau 'seminar_skripsi' required)") . "</td>";
                echo "</tr>";
                
                echo "<tr>";
                echo "<td><strong>status_pembimbing</strong></td>";
                echo "<td>" . $proposal->status_pembimbing . "</td>";
                echo "<td>" . ($proposal->status_pembimbing == '1' ? "✅ Disetujui" : "❌ Belum disetujui") . "</td>";
                echo "</tr>";
                
                echo "<tr>";
                echo "<td><strong>status_kaprodi</strong></td>";
                echo "<td>" . $proposal->status_kaprodi . "</td>";
                echo "<td>" . ($proposal->status_kaprodi == '1' ? "✅ Disetujui" : "❌ Belum disetujui") . "</td>";
                echo "</tr>";
                
                echo "</table>";
            } else {
                echo "<p style='color: red;'>❌ Proposal tidak ditemukan!</p>";
            }
            
            // 2. CEK JURNAL BIMBINGAN
            echo "<h3>2. Jurnal Bimbingan (Min 14 tervalidasi)</h3>";
            $this->db->select('COUNT(*) as total');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status_validasi', '1');
            $jurnal_result = $this->db->get()->row();
            $jurnal_count = $jurnal_result ? $jurnal_result->total : 0;
            
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-bottom: 20px;'>";
            echo "<tr><th style='background: #f0f0f0;'>Metric</th><th style='background: #f0f0f0;'>Value</th><th style='background: #f0f0f0;'>Status</th></tr>";
            echo "<tr>";
            echo "<td><strong>Jurnal Tervalidasi</strong></td>";
            echo "<td>{$jurnal_count} dari 14 required</td>";
            $jurnal_ok = $jurnal_count >= 14;
            echo "<td>" . ($jurnal_ok ? "✅ Memenuhi syarat" : "❌ Kurang " . (14 - $jurnal_count) . " jurnal") . "</td>";
            echo "</tr>";
            echo "</table>";
            
            // Detail jurnal
            echo "<h4>Detail Jurnal Tervalidasi:</h4>";
            $this->db->select('pertemuan_ke, tanggal_bimbingan, status_validasi, validasi_oleh, tanggal_validasi');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->order_by('pertemuan_ke', 'ASC');
            $jurnal_detail = $this->db->get()->result();
            
            echo "<table border='1' cellpadding='3' style='border-collapse: collapse; font-size: 12px; margin-bottom: 20px;'>";
            echo "<tr style='background: #f0f0f0;'>";
            echo "<th>Pertemuan</th><th>Tanggal</th><th>Status</th><th>Validasi Oleh</th><th>Tanggal Validasi</th>";
            echo "</tr>";
            
            foreach ($jurnal_detail as $j) {
                $status_color = $j->status_validasi == '1' ? 'green' : ($j->status_validasi == '2' ? 'orange' : 'red');
                $status_text = $j->status_validasi == '1' ? 'Tervalidasi' : ($j->status_validasi == '2' ? 'Revisi' : 'Pending');
                
                echo "<tr>";
                echo "<td>{$j->pertemuan_ke}</td>";
                echo "<td>{$j->tanggal_bimbingan}</td>";
                echo "<td style='color: {$status_color}; font-weight: bold;'>{$status_text}</td>";
                echo "<td>{$j->validasi_oleh}</td>";
                echo "<td>{$j->tanggal_validasi}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // 3. CEK SEMINAR PROPOSAL
            echo "<h3>3. Status Seminar Proposal</h3>";
            $this->db->select('*');
            $this->db->from('seminar_proposal_mahasiswa');
            $this->db->where('proposal_id', $proposal_id);
            $semprop_result = $this->db->get()->row();
            
            if ($semprop_result) {
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-bottom: 20px;'>";
                echo "<tr><th style='background: #f0f0f0;'>Field</th><th style='background: #f0f0f0;'>Value</th><th style='background: #f0f0f0;'>Status</th></tr>";
                
                echo "<tr>";
                echo "<td><strong>Status Seminar</strong></td>";
                echo "<td>" . $semprop_result->status . "</td>";
                $semprop_ok = $semprop_result->status == 'completed';
                echo "<td>" . ($semprop_ok ? "✅ Completed" : "❌ Belum completed (required: 'completed')") . "</td>";
                echo "</tr>";
                
                echo "<tr>";
                echo "<td><strong>Current Step</strong></td>";
                echo "<td>" . $semprop_result->current_step . "</td>";
                echo "<td>-</td>";
                echo "</tr>";
                
                echo "<tr>";
                echo "<td><strong>Status Pembimbing</strong></td>";
                echo "<td>" . $semprop_result->status_pembimbing . "</td>";
                echo "<td>" . ($semprop_result->status_pembimbing == 'approved' ? "✅ Approved" : "❌ " . $semprop_result->status_pembimbing) . "</td>";
                echo "</tr>";
                
                echo "<tr>";
                echo "<td><strong>Status Kaprodi</strong></td>";
                echo "<td>" . $semprop_result->status_kaprodi . "</td>";
                echo "<td>" . ($semprop_result->status_kaprodi == 'approved' ? "✅ Approved" : "❌ " . $semprop_result->status_kaprodi) . "</td>";
                echo "</tr>";
                
                echo "</table>";
            } else {
                echo "<p style='color: red;'>❌ Belum ada pengajuan seminar proposal!</p>";
                $semprop_ok = false;
            }
            
            // 4. CEK PENELITIAN (FIXED)
            echo "<h3>4. Status Surat Izin Penelitian</h3>";
            
            $penelitian_count = 0;
            $penelitian_detail = [];
            
            // Cek di tabel permohonan_izin_penelitian (tabel yang benar)
            if ($this->db->table_exists('permohonan_izin_penelitian')) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('permohonan_izin_penelitian');
                $this->db->where('proposal_mahasiswa_id', $proposal_id);
                $penelitian_result = $this->db->get()->row();
                $penelitian_count = $penelitian_result ? $penelitian_result->total : 0;
                
                if ($penelitian_count > 0) {
                    // Ambil detail
                    $this->db->select('*');
                    $this->db->from('permohonan_izin_penelitian');
                    $this->db->where('proposal_mahasiswa_id', $proposal_id);
                    $this->db->order_by('created_at', 'DESC');
                    $penelitian_detail = $this->db->get()->result();
                }
            }
            
            // Fallback: cek di tabel penelitian lama
            if ($penelitian_count == 0 && $this->db->table_exists('penelitian')) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('penelitian');
                $this->db->where('proposal_mahasiswa_id', $proposal_id);
                $penelitian_result = $this->db->get()->row();
                $penelitian_count = $penelitian_result ? $penelitian_result->total : 0;
                
                if ($penelitian_count > 0) {
                    $this->db->select('*');
                    $this->db->from('penelitian');
                    $this->db->where('proposal_mahasiswa_id', $proposal_id);
                    $this->db->order_by('created_at', 'DESC');
                    $penelitian_detail = $this->db->get()->result();
                }
            }
            
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-bottom: 20px;'>";
            echo "<tr><th style='background: #f0f0f0;'>Metric</th><th style='background: #f0f0f0;'>Value</th><th style='background: #f0f0f0;'>Status</th></tr>";
            echo "<tr>";
            echo "<td><strong>Pengajuan Penelitian</strong></td>";
            echo "<td>{$penelitian_count} pengajuan</td>";
            $penelitian_ok = $penelitian_count >= 1;
            echo "<td>" . ($penelitian_ok ? "✅ Ada pengajuan" : "❌ Belum ada pengajuan") . "</td>";
            echo "</tr>";
            echo "</table>";
            
            // Detail penelitian jika ada
            if ($penelitian_count > 0) {
                echo "<h4>Detail Pengajuan Penelitian:</h4>";
                
                echo "<table border='1' cellpadding='3' style='border-collapse: collapse; font-size: 12px; margin-bottom: 20px;'>";
                echo "<tr style='background: #f0f0f0;'>";
                echo "<th>ID</th><th>Status</th><th>Status Pembimbing</th><th>Tanggal Upload Surat</th><th>Created At</th>";
                echo "</tr>";
                
                foreach ($penelitian_detail as $p) {
                    echo "<tr>";
                    echo "<td>{$p->id}</td>";
                    echo "<td>" . ($p->status ?? 'N/A') . "</td>";
                    
                    if (isset($p->status_pembimbing)) {
                        echo "<td>" . ($p->status_pembimbing == 'approved' ? 'Disetujui' : $p->status_pembimbing) . "</td>";
                    } else {
                        echo "<td>" . (isset($p->persetujuan_pembimbing) && $p->persetujuan_pembimbing == '1' ? 'Disetujui' : 'Pending') . "</td>";
                    }
                    
                    echo "<td>" . ($p->tanggal_upload_surat_staf ?? 'Belum upload') . "</td>";
                    echo "<td>{$p->created_at}</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                // Info tabel yang digunakan
                echo "<p><small><strong>Sumber data:</strong> Tabel ";
                echo ($this->db->table_exists('permohonan_izin_penelitian') && $penelitian_count > 0) ? 
                     "permohonan_izin_penelitian" : "penelitian";
                echo "</small></p>";
            }
            
            // 5. KESIMPULAN
            echo "<hr>";
            echo "<h3>📊 KESIMPULAN ELIGIBILITY</h3>";
            
            $requirements = [
                'Workflow Status' => $workflow_ok ?? false,
                'Jurnal Bimbingan (≥14)' => $jurnal_ok,
                'Seminar Proposal Completed' => $semprop_ok ?? false,
                'Surat Izin Penelitian' => $penelitian_ok
            ];
            
            $all_met = true;
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-bottom: 20px;'>";
            echo "<tr><th style='background: #f0f0f0;'>Syarat</th><th style='background: #f0f0f0;'>Status</th></tr>";
            
            foreach ($requirements as $req => $met) {
                echo "<tr>";
                echo "<td><strong>{$req}</strong></td>";
                echo "<td style='color: " . ($met ? 'green' : 'red') . "; font-weight: bold;'>";
                echo ($met ? "✅ MEMENUHI" : "❌ BELUM MEMENUHI");
                echo "</td>";
                echo "</tr>";
                if (!$met) $all_met = false;
            }
            echo "</table>";
            
            echo "<div style='padding: 15px; border-radius: 5px; margin: 20px 0; " . 
                 ($all_met ? "background: #d4edda; border: 1px solid #c3e6cb; color: #155724;" : "background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;") . "'>";
            echo "<h4>" . ($all_met ? "🎉 HASIL: MEMENUHI SYARAT" : "⚠️ HASIL: BELUM MEMENUHI SYARAT") . "</h4>";
            
            if (!$all_met) {
                echo "<p><strong>Langkah yang perlu dilakukan:</strong></p>";
                echo "<ol>";
                if (!($workflow_ok ?? false)) {
                    echo "<li>Update workflow_status dari 'bimbingan' ke 'penelitian' (manual via database atau setelah seminar proposal completed)</li>";
                }
                if (!$jurnal_ok) {
                    echo "<li>Tambah dan validasi jurnal bimbingan sampai minimal 14</li>";
                }
                if (!($semprop_ok ?? false)) {
                    echo "<li>Selesaikan seminar proposal sampai status 'completed'</li>";
                }
                if (!$penelitian_ok) {
                    echo "<li>Ajukan surat izin penelitian melalui menu Penelitian</li>";
                }
                echo "</ol>";
            }
            echo "</div>";
            
            // 6. SOLUSI QUICK FIX (UNTUK TESTING)
            if (!$all_met) {
                echo "<hr>";
                echo "<h3>🛠️ QUICK FIX untuk Testing (DEVELOPMENT ONLY)</h3>";
                echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
                echo "<p><strong>Untuk testing fitur seminar skripsi, bisa jalankan query ini:</strong></p>";
                echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
                
                if (!($workflow_ok ?? false)) {
                    echo "-- 1. Update workflow status\n";
                    echo "UPDATE proposal_mahasiswa SET workflow_status = 'penelitian' WHERE id = {$proposal_id};\n\n";
                }
                
                if (!$jurnal_ok && $jurnal_count < 14) {
                    $needed = 14 - $jurnal_count;
                    echo "-- 2. Tambah jurnal dummy (perlu {$needed} jurnal lagi)\n";
                    for ($i = 1; $i <= $needed; $i++) {
                        $pertemuan = $jurnal_count + $i;
                        echo "INSERT INTO jurnal_bimbingan (proposal_id, pertemuan_ke, tanggal_bimbingan, materi_bimbingan, status_validasi, validasi_oleh, created_at) \n";
                        echo "VALUES ({$proposal_id}, {$pertemuan}, '2025-07-" . (20 + $i) . "', 'Dummy jurnal untuk testing', '1', 25, NOW());\n";
                    }
                    echo "\n";
                }
                
                if (!($semprop_ok ?? false)) {
                    echo "-- 3. Update seminar proposal ke completed\n";
                    echo "UPDATE seminar_proposal_mahasiswa SET status = 'completed' WHERE proposal_id = {$proposal_id};\n\n";
                }
                
                if (!$penelitian_ok) {
                    echo "-- 4. Insert dummy pengajuan penelitian\n";
                    echo "INSERT INTO penelitian (proposal_mahasiswa_id, status, persetujuan_pembimbing, created_at) \n";
                    echo "VALUES ({$proposal_id}, '1', '1', NOW());\n\n";
                }
                
                echo "</pre>";
                echo "<p><strong>⚠️ PERINGATAN:</strong> Query di atas hanya untuk testing di development!</p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<span style='color: red;'>❌ Error: " . $e->getMessage() . "</span>";
        }
    }
}