<?php
// File: application/controllers/Debug_session.php
// Script untuk debug session dan database inconsistency

defined('BASEPATH') OR exit('No direct script access allowed');

class Debug_session extends CI_Controller 
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        
        // Hanya untuk development
        if (ENVIRONMENT !== 'development') {
            show_404();
            return;
        }
    }
    
    public function index()
    {
        echo "<h2>🔍 DEBUG SESSION & DATABASE SYNC</h2>";
        echo "<style>
            table { border-collapse: collapse; width: 100%; margin: 10px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .error { color: red; font-weight: bold; }
            .success { color: green; font-weight: bold; }
            .warning { color: orange; font-weight: bold; }
        </style>";
        
        // 1. CEK SESSION DATA
        echo "<h3>1. Session Data</h3>";
        $mahasiswa_id = $this->session->userdata('id');
        $session_data = $this->session->all_userdata();
        
        echo "<table>";
        echo "<tr><th>Session Key</th><th>Value</th></tr>";
        foreach ($session_data as $key => $value) {
            if (strpos($key, 'ci_') === false) { // Skip CodeIgniter internal session data
                echo "<tr><td>{$key}</td><td>" . (is_array($value) ? json_encode($value) : $value) . "</td></tr>";
            }
        }
        echo "</table>";
        
        if (!$mahasiswa_id) {
            echo "<p class='error'>❌ TIDAK ADA SESSION MAHASISWA - Silakan login ulang</p>";
            return;
        }
        
        // 2. CEK DATA MAHASISWA DI DATABASE
        echo "<h3>2. Data Mahasiswa di Database</h3>";
        $mahasiswa_db = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
        
        if (!$mahasiswa_db) {
            echo "<p class='error'>❌ MAHASISWA TIDAK DITEMUKAN DI DATABASE - Session invalid!</p>";
            return;
        }
        
        echo "<table>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>ID</td><td>{$mahasiswa_db->id}</td></tr>";
        echo "<tr><td>NIM</td><td>{$mahasiswa_db->nim}</td></tr>";
        echo "<tr><td>Nama</td><td>{$mahasiswa_db->nama}</td></tr>";
        echo "<tr><td>Prodi ID</td><td>{$mahasiswa_db->prodi_id}</td></tr>";
        echo "<tr><td>Email</td><td>{$mahasiswa_db->email}</td></tr>";
        echo "</table>";
        
        // 3. CEK PROPOSAL MAHASISWA
        echo "<h3>3. Data Proposal Mahasiswa</h3>";
        $proposals = $this->db->select('*')
                            ->from('proposal_mahasiswa')
                            ->where('mahasiswa_id', $mahasiswa_id)
                            ->order_by('id', 'DESC')
                            ->get()->result();
        
        if (empty($proposals)) {
            echo "<p class='error'>❌ TIDAK ADA PROPOSAL UNTUK MAHASISWA INI</p>";
            return;
        }
        
        echo "<table>";
        echo "<tr><th>Proposal ID</th><th>Judul</th><th>Status Kaprodi</th><th>Status Pembimbing</th><th>Workflow Status</th><th>Dosen ID</th></tr>";
        
        $active_proposal = null;
        foreach ($proposals as $proposal) {
            $class = '';
            if ($proposal->workflow_status == 'bimbingan' && 
                $proposal->status_kaprodi == '1' && 
                $proposal->status_pembimbing == '1') {
                $class = 'style="background-color: #d4edda;"'; // Hijau untuk proposal aktif
                $active_proposal = $proposal;
            }
            
            echo "<tr {$class}>";
            echo "<td>{$proposal->id}</td>";
            echo "<td>" . substr($proposal->judul, 0, 50) . "...</td>";
            echo "<td>" . ($proposal->status_kaprodi == '1' ? '✅ Disetujui' : ($proposal->status_kaprodi == '2' ? '❌ Ditolak' : '⏳ Pending')) . "</td>";
            echo "<td>" . ($proposal->status_pembimbing == '1' ? '✅ Disetujui' : ($proposal->status_pembimbing == '2' ? '❌ Ditolak' : '⏳ Pending')) . "</td>";
            echo "<td>{$proposal->workflow_status}</td>";
            echo "<td>{$proposal->dosen_id}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if (!$active_proposal) {
            echo "<p class='error'>❌ TIDAK ADA PROPOSAL AKTIF UNTUK BIMBINGAN</p>";
            return;
        }
        
        echo "<p class='success'>✅ PROPOSAL AKTIF DITEMUKAN: ID = {$active_proposal->id}</p>";
        
        // 4. CEK JURNAL BIMBINGAN EXISTING
        echo "<h3>4. Jurnal Bimbingan Existing</h3>";
        $jurnal_existing = $this->db->select('*')
                                  ->from('jurnal_bimbingan')
                                  ->where('proposal_id', $active_proposal->id)
                                  ->order_by('pertemuan_ke', 'ASC')
                                  ->get()->result();
        
        if (empty($jurnal_existing)) {
            echo "<p class='warning'>⚠️ BELUM ADA JURNAL BIMBINGAN</p>";
        } else {
            echo "<table>";
            echo "<tr><th>ID</th><th>Pertemuan Ke</th><th>Tanggal</th><th>Status Validasi</th><th>Created By</th></tr>";
            foreach ($jurnal_existing as $jurnal) {
                echo "<tr>";
                echo "<td>{$jurnal->id}</td>";
                echo "<td>{$jurnal->pertemuan_ke}</td>";
                echo "<td>{$jurnal->tanggal_bimbingan}</td>";
                echo "<td>" . ($jurnal->status_validasi == '1' ? '✅ Valid' : ($jurnal->status_validasi == '2' ? '🔄 Revisi' : '⏳ Pending')) . "</td>";
                echo "<td>{$jurnal->created_by}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            $total_jurnal = count($jurnal_existing);
            $next_pertemuan = $total_jurnal + 1;
            echo "<p class='success'>✅ Total Jurnal: {$total_jurnal}, Pertemuan Selanjutnya: {$next_pertemuan}</p>";
        }
        
        // 5. SIMULASI INSERT (DRY RUN)
        echo "<h3>5. Simulasi Insert Jurnal Baru</h3>";
        $next_pertemuan = count($jurnal_existing) + 1;
        
        $test_data = [
            'proposal_id' => $active_proposal->id,
            'pertemuan_ke' => $next_pertemuan,
            'tanggal_bimbingan' => date('Y-m-d'),
            'materi_bimbingan' => 'Test materi bimbingan ke-' . $next_pertemuan,
            'tindak_lanjut' => 'Test tindak lanjut',
            'status_validasi' => '0',
            'created_by' => 'mahasiswa',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        echo "<p><strong>Data yang akan diinsert:</strong></p>";
        echo "<pre>" . print_r($test_data, true) . "</pre>";
        
        // Cek apakah proposal_id valid
        $proposal_exists = $this->db->get_where('proposal_mahasiswa', ['id' => $active_proposal->id])->row();
        if ($proposal_exists) {
            echo "<p class='success'>✅ Proposal ID {$active_proposal->id} EXISTS di database</p>";
        } else {
            echo "<p class='error'>❌ Proposal ID {$active_proposal->id} TIDAK EXISTS di database - INI MASALAHNYA!</p>";
        }
        
        // Cek unique constraint
        $existing_pertemuan = $this->db->get_where('jurnal_bimbingan', [
            'proposal_id' => $active_proposal->id,
            'pertemuan_ke' => $next_pertemuan
        ])->row();
        
        if ($existing_pertemuan) {
            echo "<p class='error'>❌ Pertemuan ke-{$next_pertemuan} sudah ada - Unique constraint violation</p>";
        } else {
            echo "<p class='success'>✅ Pertemuan ke-{$next_pertemuan} belum ada - OK untuk insert</p>";
        }
        
        // 6. LINK UNTUK ACTION
        echo "<h3>6. Actions</h3>";
        echo "<p>";
        echo "<a href='" . base_url('debug_session/clear_session') . "' style='background: red; color: white; padding: 10px; text-decoration: none; margin: 5px;'>🗑️ Clear Session</a> ";
        echo "<a href='" . base_url('auth/logout') . "' style='background: orange; color: white; padding: 10px; text-decoration: none; margin: 5px;'>🚪 Logout</a> ";
        echo "<a href='" . base_url('mahasiswa/bimbingan') . "' style='background: blue; color: white; padding: 10px; text-decoration: none; margin: 5px;'>📚 Ke Halaman Bimbingan</a>";
        echo "</p>";
    }
    
    public function clear_session()
    {
        $this->session->sess_destroy();
        echo "<h2>✅ Session Cleared</h2>";
        echo "<p>Session telah dihapus. <a href='" . base_url('auth/login') . "'>Login Ulang</a></p>";
    }
}
?>