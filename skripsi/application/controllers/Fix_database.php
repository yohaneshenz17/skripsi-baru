<?php
// File: application/controllers/Fix_database.php
// Script untuk memperbaiki data inconsistency setelah database restore

defined('BASEPATH') OR exit('No direct script access allowed');

class Fix_database extends CI_Controller 
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        
        // Only for development/debugging
        if (ENVIRONMENT !== 'development') {
            show_404();
            return;
        }
    }
    
    public function index()
    {
        // PERBAIKAN: Set header content-type terlebih dahulu
        header('Content-Type: text/html; charset=utf-8');
        
        echo "<h2>🔧 DATABASE INCONSISTENCY FIX - POST RESTORE</h2>";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { border-collapse: collapse; width: 100%; margin: 10px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .error { color: red; font-weight: bold; }
            .success { color: green; font-weight: bold; }
            .warning { color: orange; font-weight: bold; }
            .info { color: blue; font-weight: bold; }
            .fix-btn { background: #28a745; color: white; padding: 10px 15px; text-decoration: none; margin: 5px; border-radius: 5px; display: inline-block; }
            .danger-btn { background: #dc3545; color: white; padding: 10px 15px; text-decoration: none; margin: 5px; border-radius: 5px; display: inline-block; }
        </style>";
        
        // 1. DIAGNOSIS - CEK SEMUA PROPOSAL MAHASISWA
        echo "<h3>1. 🔍 Diagnosis - Status Proposal Mahasiswa</h3>";
        
        $proposals = $this->db->select('
                pm.id as proposal_id,
                pm.mahasiswa_id,
                m.nama as nama_mahasiswa,
                m.nim,
                pm.workflow_status,
                pm.status_kaprodi,
                pm.status_pembimbing,
                pm.dosen_id,
                d.nama as nama_dosen,
                pm.komentar_pembimbing,
                pm.tanggal_respon_pembimbing
            ')
            ->from('proposal_mahasiswa pm')
            ->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'inner')
            ->join('dosen d', 'pm.dosen_id = d.id', 'left')  // PERBAIKAN: gunakan join dengan parameter ketiga
            ->where('pm.status_kaprodi', '1') // Hanya yang sudah disetujui kaprodi
            ->order_by('pm.id', 'DESC')
            ->get()->result();
        
        echo "<table>";
        echo "<tr>
                <th>Proposal ID</th>
                <th>Mahasiswa</th>
                <th>NIM</th>
                <th>Workflow Status</th>
                <th>Status Pembimbing</th>
                <th>Dosen</th>
                <th>Diagnosis</th>
              </tr>";
        
        $problematic_proposals = [];
        
        foreach ($proposals as $proposal) {
            $diagnosis = '';
            $class = '';
            
            if ($proposal->status_kaprodi == '1' && $proposal->dosen_id && $proposal->status_pembimbing == '0') {
                $diagnosis = '❌ NEEDS FIX - Ready for bimbingan but not approved';
                $class = 'error';
                $problematic_proposals[] = $proposal->proposal_id;
            } elseif ($proposal->status_kaprodi == '1' && $proposal->dosen_id && $proposal->status_pembimbing == '1' && $proposal->workflow_status != 'bimbingan') {
                $diagnosis = '❌ NEEDS FIX - Approved but wrong workflow';
                $class = 'error';
                $problematic_proposals[] = $proposal->proposal_id;
            } elseif ($proposal->status_kaprodi == '1' && $proposal->dosen_id && $proposal->status_pembimbing == '1' && $proposal->workflow_status == 'bimbingan') {
                $diagnosis = '✅ OK - Ready for bimbingan';
                $class = 'success';
            } else {
                $diagnosis = '⏳ PENDING - Not ready yet';
                $class = 'warning';
            }
            
            echo "<tr>";
            echo "<td>{$proposal->proposal_id}</td>";
            echo "<td>{$proposal->nama_mahasiswa}</td>";
            echo "<td>{$proposal->nim}</td>";
            echo "<td>" . ($proposal->workflow_status ?: '<em>KOSONG</em>') . "</td>";
            echo "<td>" . ($proposal->status_pembimbing == '1' ? '✅ Disetujui' : ($proposal->status_pembimbing == '2' ? '❌ Ditolak' : '⏳ Pending')) . "</td>";
            echo "<td>" . ($proposal->nama_dosen ?: 'Belum ada') . "</td>";
            echo "<td class='{$class}'>{$diagnosis}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 2. SUMMARY
        echo "<h3>2. 📊 Summary</h3>";
        $total_proposals = count($proposals);
        $problematic_count = count($problematic_proposals);
        $ok_count = $total_proposals - $problematic_count;
        
        echo "<table>";
        echo "<tr><th>Status</th><th>Count</th><th>Percentage</th></tr>";
        echo "<tr><td class='success'>✅ OK</td><td>{$ok_count}</td><td>" . round(($ok_count/$total_proposals)*100, 1) . "%</td></tr>";
        echo "<tr><td class='error'>❌ NEEDS FIX</td><td>{$problematic_count}</td><td>" . round(($problematic_count/$total_proposals)*100, 1) . "%</td></tr>";
        echo "<tr><td class='info'><strong>TOTAL</strong></td><td><strong>{$total_proposals}</strong></td><td><strong>100%</strong></td></tr>";
        echo "</table>";
        
        if ($problematic_count > 0) {
            echo "<p class='error'><strong>⚠️ DITEMUKAN {$problematic_count} PROPOSAL BERMASALAH!</strong></p>";
            echo "<p>Proposal IDs yang bermasalah: " . implode(', ', $problematic_proposals) . "</p>";
        } else {
            echo "<p class='success'><strong>✅ SEMUA PROPOSAL SUDAH KONSISTEN!</strong></p>";
        }
        
        // 3. ACTIONS
        echo "<h3>3. 🔧 Fix Actions</h3>";
        
        if ($problematic_count > 0) {
            echo "<p><strong>Pilihan perbaikan:</strong></p>";
            echo "<p>";
            echo "<a href='" . base_url('fix_database/auto_fix_all') . "' class='fix-btn' onclick='return confirm(\"Yakin ingin memperbaiki semua proposal bermasalah? Ini akan mengubah status_pembimbing menjadi disetujui dan workflow_status menjadi bimbingan.\")'>🔧 Auto Fix All ({$problematic_count} proposals)</a>";
            echo "<a href='" . base_url('fix_database/fix_specific/45') . "' class='fix-btn' onclick='return confirm(\"Yakin ingin memperbaiki proposal Mahasiswa Contoh 2 (ID: 45)?\")'>🔧 Fix Mahasiswa Contoh 2 Only</a>";
            echo "</p>";
            
            echo "<p><strong>Manual fix untuk proposal tertentu:</strong></p>";
            foreach ($problematic_proposals as $pid) {
                echo "<a href='" . base_url('fix_database/fix_specific/' . $pid) . "' class='fix-btn' onclick='return confirm(\"Fix proposal ID {$pid}?\")'>Fix Proposal {$pid}</a> ";
            }
        }
        
        echo "<p>";
        echo "<a href='" . base_url('fix_database/view_fixed') . "' class='info'>📊 View All Fixed Proposals</a>";
        echo "<a href='" . base_url('debug_session') . "' class='info'>🔍 Debug Session</a>";
        echo "</p>";
        
        // 4. JURNAL BIMBINGAN CHECK
        echo "<h3>4. 📚 Jurnal Bimbingan Status</h3>";
        
        $jurnal_stats = $this->db->query("
            SELECT 
                pm.id as proposal_id,
                m.nama as nama_mahasiswa,
                m.nim,
                pm.workflow_status,
                COUNT(jb.id) as total_jurnal,
                SUM(CASE WHEN jb.status_validasi = '1' THEN 1 ELSE 0 END) as jurnal_valid,
                SUM(CASE WHEN jb.status_validasi = '0' THEN 1 ELSE 0 END) as jurnal_pending
            FROM proposal_mahasiswa pm
            JOIN mahasiswa m ON pm.mahasiswa_id = m.id
            LEFT JOIN jurnal_bimbingan jb ON pm.id = jb.proposal_id
            WHERE pm.workflow_status = 'bimbingan'
            GROUP BY pm.id, m.nama, m.nim, pm.workflow_status
            ORDER BY pm.id DESC
        ")->result();
        
        if (!empty($jurnal_stats)) {
            echo "<table>";
            echo "<tr><th>Proposal ID</th><th>Mahasiswa</th><th>NIM</th><th>Total Jurnal</th><th>Jurnal Valid</th><th>Jurnal Pending</th><th>Status</th></tr>";
            
            foreach ($jurnal_stats as $stat) {
                $status = $stat->total_jurnal > 0 ? '✅ Ada Jurnal' : '⚠️ Belum Ada Jurnal';
                echo "<tr>";
                echo "<td>{$stat->proposal_id}</td>";
                echo "<td>{$stat->nama_mahasiswa}</td>";
                echo "<td>{$stat->nim}</td>";
                echo "<td>{$stat->total_jurnal}</td>";
                echo "<td>{$stat->jurnal_valid}</td>";
                echo "<td>{$stat->jurnal_pending}</td>";
                echo "<td>{$status}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warning'>⚠️ Tidak ada mahasiswa di fase bimbingan atau tidak ada jurnal.</p>";
        }
    }
    
    public function auto_fix_all()
    {
        echo "<h3>🔧 AUTO FIX ALL - Memperbaiki Semua Proposal Bermasalah</h3>";
        
        // Update semua proposal yang bermasalah
        $update_query = "
            UPDATE proposal_mahasiswa 
            SET 
                workflow_status = 'bimbingan',
                status_pembimbing = '1',
                komentar_pembimbing = 'Auto-approved after database restore - System consistency fix',
                tanggal_respon_pembimbing = NOW()
            WHERE 
                status_kaprodi = '1'
                AND dosen_id IS NOT NULL
                AND status_pembimbing = '0'
                AND (workflow_status != 'bimbingan' OR workflow_status = '' OR workflow_status IS NULL)
        ";
        
        $this->db->query($update_query);
        $affected_rows = $this->db->affected_rows();
        
        if ($affected_rows > 0) {
            echo "<p class='success'>✅ BERHASIL! {$affected_rows} proposal telah diperbaiki.</p>";
            
            // Show fixed proposals
            $fixed_proposals = $this->db->select('
                    pm.id as proposal_id,
                    m.nama as nama_mahasiswa,
                    m.nim,
                    pm.workflow_status,
                    pm.status_pembimbing,
                    d.nama as nama_dosen
                ')
                ->from('proposal_mahasiswa pm')
                ->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'inner')
                ->join('dosen d', 'pm.dosen_id = d.id', 'left')  // PERBAIKAN: gunakan join dengan parameter ketiga
                ->where('pm.komentar_pembimbing', 'Auto-approved after database restore - System consistency fix')
                ->get()->result();
            
            echo "<h4>📋 Proposal yang Diperbaiki:</h4>";
            echo "<table>";
            echo "<tr><th>Proposal ID</th><th>Mahasiswa</th><th>NIM</th><th>Dosen Pembimbing</th><th>Status Baru</th></tr>";
            
            foreach ($fixed_proposals as $proposal) {
                echo "<tr>";
                echo "<td>{$proposal->proposal_id}</td>";
                echo "<td>{$proposal->nama_mahasiswa}</td>";
                echo "<td>{$proposal->nim}</td>";
                echo "<td>{$proposal->nama_dosen}</td>";
                echo "<td class='success'>✅ Ready for Bimbingan</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } else {
            echo "<p class='warning'>⚠️ Tidak ada proposal yang perlu diperbaiki atau sudah diperbaiki sebelumnya.</p>";
        }
        
        echo "<p><a href='" . base_url('fix_database') . "' class='fix-btn'>🔙 Back to Diagnosis</a></p>";
        echo "<p><a href='" . base_url('mahasiswa/bimbingan') . "' class='fix-btn'>📚 Test Bimbingan Page</a></p>";
    }
    
    public function fix_specific($proposal_id)
    {
        if (!$proposal_id || !is_numeric($proposal_id)) {
            echo "<p class='error'>❌ Invalid proposal ID</p>";
            return;
        }
        
        echo "<h3>🔧 FIX SPECIFIC - Proposal ID: {$proposal_id}</h3>";
        
        // Get proposal info first
        $proposal = $this->db->select('
                pm.*,
                m.nama as nama_mahasiswa,
                m.nim,
                d.nama as nama_dosen
            ')
            ->from('proposal_mahasiswa pm')
            ->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'inner')
            ->join('dosen d', 'pm.dosen_id = d.id', 'left')  // PERBAIKAN: gunakan join dengan parameter ketiga
            ->where('pm.id', $proposal_id)
            ->get()->row();
        
        if (!$proposal) {
            echo "<p class='error'>❌ Proposal ID {$proposal_id} tidak ditemukan!</p>";
            return;
        }
        
        echo "<h4>📋 Info Proposal:</h4>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Before</th><th>After</th></tr>";
        echo "<tr><td>Mahasiswa</td><td colspan='2'>{$proposal->nama_mahasiswa} ({$proposal->nim})</td></tr>";
        echo "<tr><td>Dosen Pembimbing</td><td colspan='2'>{$proposal->nama_dosen}</td></tr>";
        echo "<tr><td>workflow_status</td><td>{$proposal->workflow_status}</td><td class='success'>bimbingan</td></tr>";
        echo "<tr><td>status_pembimbing</td><td>" . ($proposal->status_pembimbing == '1' ? 'Disetujui' : 'Pending') . "</td><td class='success'>Disetujui</td></tr>";
        echo "</table>";
        
        // Update specific proposal
        $update_data = [
            'workflow_status' => 'bimbingan',
            'status_pembimbing' => '1',
            'komentar_pembimbing' => 'Manual fix - Database consistency repair for proposal ' . $proposal_id,
            'tanggal_respon_pembimbing' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $proposal_id);
        $result = $this->db->update('proposal_mahasiswa', $update_data);
        
        if ($result) {
            echo "<p class='success'>✅ BERHASIL! Proposal {$proposal_id} telah diperbaiki.</p>";
            echo "<p>Mahasiswa <strong>{$proposal->nama_mahasiswa}</strong> sekarang dapat mengakses fitur jurnal bimbingan.</p>";
        } else {
            echo "<p class='error'>❌ GAGAL memperbaiki proposal {$proposal_id}!</p>";
        }
        
        echo "<p><a href='" . base_url('fix_database') . "' class='fix-btn'>🔙 Back to Diagnosis</a></p>";
    }
    
    public function view_fixed()
    {
        echo "<h3>📊 VIEW FIXED PROPOSALS</h3>";
        
        // Get all proposals that were fixed
        $fixed_proposals = $this->db->select('
                pm.id as proposal_id,
                m.nama as nama_mahasiswa,
                m.nim,
                pm.workflow_status,
                pm.status_pembimbing,
                pm.komentar_pembimbing,
                pm.tanggal_respon_pembimbing,
                d.nama as nama_dosen
            ')
            ->from('proposal_mahasiswa pm')
            ->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'inner')
            ->join('dosen d', 'pm.dosen_id = d.id', 'left')  // PERBAIKAN: gunakan join dengan parameter ketiga
            ->where("pm.komentar_pembimbing LIKE '%fix%' OR pm.komentar_pembimbing LIKE '%Auto-approved%'")
            ->order_by('pm.tanggal_respon_pembimbing', 'DESC')
            ->get()->result();
        
        if (!empty($fixed_proposals)) {
            echo "<table>";
            echo "<tr><th>Proposal ID</th><th>Mahasiswa</th><th>NIM</th><th>Dosen</th><th>Tanggal Fix</th><th>Komentar</th></tr>";
            
            foreach ($fixed_proposals as $proposal) {
                echo "<tr>";
                echo "<td>{$proposal->proposal_id}</td>";
                echo "<td>{$proposal->nama_mahasiswa}</td>";
                echo "<td>{$proposal->nim}</td>";
                echo "<td>{$proposal->nama_dosen}</td>";
                echo "<td>{$proposal->tanggal_respon_pembimbing}</td>";
                echo "<td>" . substr($proposal->komentar_pembimbing, 0, 50) . "...</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warning'>⚠️ Belum ada proposal yang diperbaiki melalui script ini.</p>";
        }
        
        echo "<p><a href='" . base_url('fix_database') . "' class='fix-btn'>🔙 Back to Diagnosis</a></p>";
    }
}
?>