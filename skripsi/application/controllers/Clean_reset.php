<?php
// File: application/controllers/Clean_reset.php
// Script untuk menghapus semua data mahasiswa dan memulai testing dari awal
// HANYA untuk development/testing environment

defined('BASEPATH') OR exit('No direct script access allowed');

class Clean_reset extends CI_Controller 
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        
        // SAFETY: Hanya untuk development
        if (ENVIRONMENT !== 'development') {
            show_404();
            return;
        }
    }
    
    public function index()
    {
        header('Content-Type: text/html; charset=utf-8');
        
        echo "<h2>🧹 CLEAN RESET - Data Mahasiswa System</h2>";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { border-collapse: collapse; width: 100%; margin: 10px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .danger { color: red; font-weight: bold; }
            .success { color: green; font-weight: bold; }
            .warning { color: orange; font-weight: bold; }
            .info { color: blue; font-weight: bold; }
            .btn { padding: 10px 15px; text-decoration: none; margin: 5px; border-radius: 5px; display: inline-block; }
            .btn-danger { background: #dc3545; color: white; }
            .btn-warning { background: #ffc107; color: black; }
            .btn-success { background: #28a745; color: white; }
            .btn-info { background: #17a2b8; color: white; }
            .alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
            .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; }
            .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; }
        </style>";
        
        // WARNING KEAMANAN
        echo "<div class='alert alert-danger'>";
        echo "<h3>⚠️ PERINGATAN KEAMANAN</h3>";
        echo "<p><strong>Script ini akan MENGHAPUS SEMUA DATA MAHASISWA secara PERMANEN!</strong></p>";
        echo "<p>Yang akan dihapus:</p>";
        echo "<ul>";
        echo "<li>❌ Semua akun mahasiswa</li>";
        echo "<li>❌ Semua proposal/tugas akhir mahasiswa</li>";
        echo "<li>❌ Semua jurnal bimbingan</li>";
        echo "<li>❌ Semua riwayat konsultasi, seminar, penelitian</li>";
        echo "<li>❌ Semua notifikasi dan aktivitas mahasiswa</li>";
        echo "</ul>";
        echo "<p><strong>Yang TIDAK akan dihapus:</strong></p>";
        echo "<ul>";
        echo "<li>✅ Data dosen, admin, kaprodi, staf</li>";
        echo "<li>✅ Struktur database dan tabel</li>";
        echo "<li>✅ Konfigurasi sistem</li>";
        echo "<li>✅ Data prodi dan fakultas</li>";
        echo "</ul>";
        echo "</div>";
        
        // CURRENT DATA STATISTICS
        echo "<h3>📊 Current Data Statistics</h3>";
        $this->show_current_stats();
        
        // BACKUP OPTIONS
        echo "<h3>💾 Backup Options</h3>";
        echo "<div class='alert alert-warning'>";
        echo "<p><strong>SANGAT DISARANKAN</strong> untuk backup data sebelum menghapus!</p>";
        echo "<p>";
        echo "<a href='" . base_url('clean_reset/create_backup') . "' class='btn btn-warning'>📦 Create Full Backup</a>";
        echo "<a href='" . base_url('clean_reset/download_backup') . "' class='btn btn-info'>⬇️ Download Existing Backup</a>";
        echo "</p>";
        echo "</div>";
        
        // RESET OPTIONS
        echo "<h3>🧹 Reset Options</h3>";
        echo "<table>";
        echo "<tr><th>Option</th><th>Description</th><th>Action</th></tr>";
        
        echo "<tr>";
        echo "<td><strong>Preview Mode</strong></td>";
        echo "<td>Lihat data apa saja yang akan dihapus tanpa menghapus</td>";
        echo "<td><a href='" . base_url('clean_reset/preview_deletion') . "' class='btn btn-info'>👁️ Preview</a></td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Soft Reset</strong></td>";
        echo "<td>Hapus hanya data mahasiswa, simpan struktur proposal kosong</td>";
        echo "<td><a href='" . base_url('clean_reset/soft_reset') . "' class='btn btn-warning' onclick='return confirm(\"Yakin ingin Soft Reset? Data mahasiswa akan dihapus!\")'>🔄 Soft Reset</a></td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Hard Reset</strong></td>";
        echo "<td>Hapus SEMUA data mahasiswa dan riwayat terkait</td>";
        echo "<td><a href='" . base_url('clean_reset/hard_reset') . "' class='btn btn-danger' onclick='return confirm(\"YAKIN INGIN HARD RESET? Semua data mahasiswa akan HILANG PERMANEN! Ketik YES untuk konfirmasi.\") && prompt(\"Ketik: DELETE_ALL_MAHASISWA\") === \"DELETE_ALL_MAHASISWA\"'>💀 Hard Reset</a></td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Custom Reset</strong></td>";
        echo "<td>Pilih mahasiswa tertentu untuk dihapus</td>";
        echo "<td><a href='" . base_url('clean_reset/custom_reset') . "' class='btn btn-warning'>⚙️ Custom</a></td>";
        echo "</tr>";
        echo "</table>";
        
        // RESTORE OPTIONS
        echo "<h3>🔄 Restore Options</h3>";
        echo "<p>";
        echo "<a href='" . base_url('clean_reset/restore_from_backup') . "' class='btn btn-success'>📥 Restore from Backup</a>";
        echo "<a href='" . base_url('clean_reset/create_sample_data') . "' class='btn btn-info'>👤 Create Sample Mahasiswa</a>";
        echo "</p>";
    }
    
    private function show_current_stats()
    {
        $stats = [
            'mahasiswa' => $this->db->count_all('mahasiswa'),
            'proposal_mahasiswa' => $this->db->count_all('proposal_mahasiswa'),
            'jurnal_bimbingan' => $this->db->count_all('jurnal_bimbingan'),
            'konsultasi' => $this->db->count_all('konsultasi'),
            'hasil_kegiatan' => $this->db->count_all('hasil_kegiatan'),
            'seminar' => $this->db->count_all('seminar'),
            'penelitian' => $this->db->count_all('penelitian'),
            'skripsi' => $this->db->count_all('skripsi'),
            'notifikasi_mahasiswa' => $this->db->where('untuk_role', 'mahasiswa')->count_all_results('notifikasi'),
            'dosen' => $this->db->where_in('level', ['1', '2', '4', '5'])->count_all_results('dosen'),
        ];
        
        echo "<table>";
        echo "<tr><th>Table</th><th>Current Records</th><th>Will be affected</th></tr>";
        echo "<tr><td>👤 Mahasiswa</td><td>{$stats['mahasiswa']}</td><td class='danger'>WILL BE DELETED</td></tr>";
        echo "<tr><td>📋 Proposal Mahasiswa</td><td>{$stats['proposal_mahasiswa']}</td><td class='danger'>WILL BE DELETED</td></tr>";
        echo "<tr><td>📚 Jurnal Bimbingan</td><td>{$stats['jurnal_bimbingan']}</td><td class='danger'>WILL BE DELETED</td></tr>";
        echo "<tr><td>💬 Konsultasi</td><td>{$stats['konsultasi']}</td><td class='danger'>WILL BE DELETED</td></tr>";
        echo "<tr><td>📊 Hasil Kegiatan</td><td>{$stats['hasil_kegiatan']}</td><td class='danger'>WILL BE DELETED</td></tr>";
        echo "<tr><td>🎓 Seminar</td><td>{$stats['seminar']}</td><td class='danger'>WILL BE DELETED</td></tr>";
        echo "<tr><td>🔬 Penelitian</td><td>{$stats['penelitian']}</td><td class='danger'>WILL BE DELETED</td></tr>";
        echo "<tr><td>📖 Skripsi</td><td>{$stats['skripsi']}</td><td class='danger'>WILL BE DELETED</td></tr>";
        echo "<tr><td>🔔 Notifikasi Mahasiswa</td><td>{$stats['notifikasi_mahasiswa']}</td><td class='danger'>WILL BE DELETED</td></tr>";
        echo "<tr><td>👨‍🏫 Dosen/Admin/Staff</td><td>{$stats['dosen']}</td><td class='success'>WILL BE PRESERVED</td></tr>";
        echo "</table>";
    }
    
    public function preview_deletion()
    {
        echo "<h3>👁️ PREVIEW DELETION - Data yang akan dihapus</h3>";
        
        // Show detailed data that will be deleted
        $this->show_detailed_mahasiswa_data();
        
        echo "<p><a href='" . base_url('clean_reset') . "' class='btn btn-info'>🔙 Back to Main Menu</a></p>";
    }
    
    public function create_backup()
    {
        echo "<h3>💾 Creating Backup...</h3>";
        
        $backup_timestamp = date('Y-m-d_H-i-s');
        
        try {
            // Create backup tables with timestamp
            $tables_to_backup = [
                'mahasiswa',
                'proposal_mahasiswa', 
                'jurnal_bimbingan',
                'konsultasi',
                'hasil_kegiatan',
                'hasil_penelitian',
                'hasil_seminar',
                'penelitian',
                'seminar',
                'skripsi',
                'notifikasi',
                'staf_aktivitas'
            ];
            
            foreach ($tables_to_backup as $table) {
                $backup_table = $table . '_backup_' . $backup_timestamp;
                
                $this->db->query("CREATE TABLE {$backup_table} AS SELECT * FROM {$table}");
                echo "<p class='success'>✅ Backup created: {$backup_table}</p>";
            }
            
            echo "<div class='alert alert-success'>";
            echo "<h4>✅ Backup Complete!</h4>";
            echo "<p>Backup tables created with timestamp: <strong>{$backup_timestamp}</strong></p>";
            echo "<p>Anda sekarang dapat melakukan reset dengan aman.</p>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h4>❌ Backup Failed!</h4>";
            echo "<p>Error: " . $e->getMessage() . "</p>";
            echo "</div>";
        }
        
        echo "<p><a href='" . base_url('clean_reset') . "' class='btn btn-info'>🔙 Back to Main Menu</a></p>";
    }
    
    public function soft_reset()
    {
        echo "<h3>🔄 SOFT RESET - Menghapus Data Mahasiswa...</h3>";
        
        $this->db->trans_start();
        
        try {
            // Get count before deletion
            $count_before = $this->get_deletion_counts();
            
            // Delete in correct order (respect foreign keys)
            $this->delete_mahasiswa_related_data();
            
            // Get count after deletion
            $count_after = $this->get_deletion_counts();
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }
            
            echo "<div class='alert alert-success'>";
            echo "<h4>✅ Soft Reset Complete!</h4>";
            echo "<p>Data mahasiswa berhasil dihapus:</p>";
            echo "<ul>";
            foreach ($count_before as $table => $count) {
                $deleted = $count - $count_after[$table];
                echo "<li>{$table}: {$deleted} records deleted</li>";
            }
            echo "</ul>";
            echo "</div>";
            
            // Reset AUTO_INCREMENT
            $this->reset_auto_increment();
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo "<div class='alert alert-danger'>";
            echo "<h4>❌ Reset Failed!</h4>";
            echo "<p>Error: " . $e->getMessage() . "</p>";
            echo "<p>Database telah di-rollback ke kondisi semula.</p>";
            echo "</div>";
        }
        
        echo "<p><a href='" . base_url('clean_reset') . "' class='btn btn-info'>🔙 Back to Main Menu</a></p>";
        echo "<p><a href='" . base_url('clean_reset/create_sample_data') . "' class='btn btn-success'>👤 Create Sample Data</a></p>";
    }
    
    public function hard_reset()
    {
        echo "<h3>💀 HARD RESET - Menghapus SEMUA Data Mahasiswa...</h3>";
        
        // Extra confirmation for hard reset
        echo "<div class='alert alert-danger'>";
        echo "<h4>⚠️ FINAL WARNING</h4>";
        echo "<p>Anda akan menghapus SEMUA data mahasiswa SECARA PERMANEN!</p>";
        echo "</div>";
        
        // Same as soft reset but more thorough
        $this->soft_reset();
        
        // Additional cleanup for hard reset
        $this->cleanup_orphaned_data();
        
        echo "<div class='alert alert-success'>";
        echo "<h4>💀 HARD RESET COMPLETE!</h4>";
        echo "<p>Sistem telah dibersihkan total dari data mahasiswa.</p>";
        echo "<p>Database siap untuk testing dari awal.</p>";
        echo "</div>";
    }
    
    private function delete_mahasiswa_related_data()
    {
        // Delete in order that respects foreign key constraints
        
        echo "<p>🗑️ Deleting jurnal_bimbingan...</p>";
        $this->db->query("DELETE jb FROM jurnal_bimbingan jb 
                          JOIN proposal_mahasiswa pm ON jb.proposal_id = pm.id");
        
        echo "<p>🗑️ Deleting hasil_seminar...</p>";
        $this->db->query("DELETE hs FROM hasil_seminar hs 
                          JOIN seminar s ON hs.seminar_id = s.id
                          JOIN proposal_mahasiswa pm ON s.proposal_mahasiswa_id = pm.id");
        
        echo "<p>🗑️ Deleting hasil_penelitian...</p>";
        $this->db->query("DELETE hp FROM hasil_penelitian hp 
                          JOIN penelitian p ON hp.penelitian_id = p.id
                          JOIN proposal_mahasiswa pm ON p.proposal_mahasiswa_id = pm.id");
        
        echo "<p>🗑️ Deleting hasil_kegiatan...</p>";
        $this->db->query("DELETE FROM hasil_kegiatan");
        
        echo "<p>🗑️ Deleting seminar...</p>";
        $this->db->query("DELETE s FROM seminar s 
                          JOIN proposal_mahasiswa pm ON s.proposal_mahasiswa_id = pm.id");
        
        echo "<p>🗑️ Deleting penelitian...</p>";
        $this->db->query("DELETE p FROM penelitian p 
                          JOIN proposal_mahasiswa pm ON p.proposal_mahasiswa_id = pm.id");
        
        echo "<p>🗑️ Deleting skripsi...</p>";
        $this->db->query("DELETE FROM skripsi");
        
        echo "<p>🗑️ Deleting konsultasi...</p>";
        $this->db->query("DELETE k FROM konsultasi k 
                          JOIN proposal_mahasiswa pm ON k.proposal_mahasiswa_id = pm.id");
        
        echo "<p>🗑️ Deleting proposal_workflow...</p>";
        $this->db->query("DELETE pw FROM proposal_workflow pw 
                          JOIN proposal_mahasiswa pm ON pw.proposal_id = pm.id");
        
        echo "<p>🗑️ Deleting staf_aktivitas (mahasiswa related)...</p>";
        $this->db->query("DELETE FROM staf_aktivitas WHERE mahasiswa_id IS NOT NULL");
        
        echo "<p>🗑️ Deleting notifikasi (mahasiswa)...</p>";
        $this->db->where('untuk_role', 'mahasiswa')->delete('notifikasi');
        
        echo "<p>🗑️ Deleting proposal_mahasiswa...</p>";
        $this->db->empty_table('proposal_mahasiswa');
        
        echo "<p>🗑️ Deleting mahasiswa...</p>";
        $this->db->empty_table('mahasiswa');
    }
    
    private function get_deletion_counts()
    {
        return [
            'mahasiswa' => $this->db->count_all('mahasiswa'),
            'proposal_mahasiswa' => $this->db->count_all('proposal_mahasiswa'),
            'jurnal_bimbingan' => $this->db->count_all('jurnal_bimbingan'),
            'konsultasi' => $this->db->count_all('konsultasi'),
            'hasil_kegiatan' => $this->db->count_all('hasil_kegiatan'),
            'seminar' => $this->db->count_all('seminar'),
            'penelitian' => $this->db->count_all('penelitian'),
            'skripsi' => $this->db->count_all('skripsi'),
        ];
    }
    
    private function reset_auto_increment()
    {
        echo "<p>🔄 Resetting AUTO_INCREMENT...</p>";
        
        $tables = [
            'mahasiswa',
            'proposal_mahasiswa',
            'jurnal_bimbingan',
            'konsultasi',
            'hasil_kegiatan',
            'hasil_seminar',
            'hasil_penelitian',
            'seminar',
            'penelitian',
            'skripsi',
            'notifikasi',
            'staf_aktivitas'
        ];
        
        foreach ($tables as $table) {
            $this->db->query("ALTER TABLE {$table} AUTO_INCREMENT = 1");
        }
        
        echo "<p class='success'>✅ AUTO_INCREMENT reset complete</p>";
    }
    
    private function cleanup_orphaned_data()
    {
        echo "<p>🧹 Cleaning orphaned data...</p>";
        
        // Clean any remaining orphaned foreign key references
        $this->db->query("DELETE FROM notifikasi WHERE proposal_id NOT IN (SELECT id FROM proposal_mahasiswa)");
        $this->db->query("DELETE FROM staf_aktivitas WHERE proposal_id NOT IN (SELECT id FROM proposal_mahasiswa)");
        
        echo "<p class='success'>✅ Orphaned data cleanup complete</p>";
    }
    
    public function create_sample_data()
    {
        echo "<h3>👤 Creating Sample Mahasiswa Data...</h3>";
        
        $this->db->trans_start();
        
        try {
            // Create 2 sample mahasiswa
            $sample_mahasiswa = [
                [
                    'nim' => '12345001',
                    'nama' => 'Sample Mahasiswa 1',
                    'prodi_id' => 10, // PKK
                    'jenis_kelamin' => 'laki-laki',
                    'tempat_lahir' => 'Merauke',
                    'tanggal_lahir' => '2000-01-01',
                    'email' => 'sample1@test.com',
                    'alamat' => 'Alamat Sample 1',
                    'nomor_telepon' => '081234567001',
                    'nomor_telepon_orang_dekat' => '081234567002',
                    'ipk' => '3.50',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password123
                    'status' => '1'
                ],
                [
                    'nim' => '12345002',
                    'nama' => 'Sample Mahasiswa 2',
                    'prodi_id' => 11, // PGSD
                    'jenis_kelamin' => 'perempuan',
                    'tempat_lahir' => 'Merauke',
                    'tanggal_lahir' => '2000-02-02',
                    'email' => 'sample2@test.com',
                    'alamat' => 'Alamat Sample 2',
                    'nomor_telepon' => '081234567003',
                    'nomor_telepon_orang_dekat' => '081234567004',
                    'ipk' => '3.75',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password123
                    'status' => '1'
                ]
            ];
            
            foreach ($sample_mahasiswa as $mahasiswa) {
                $this->db->insert('mahasiswa', $mahasiswa);
                echo "<p class='success'>✅ Created: {$mahasiswa['nama']} (NIM: {$mahasiswa['nim']})</p>";
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Failed to create sample data');
            }
            
            echo "<div class='alert alert-success'>";
            echo "<h4>✅ Sample Data Created!</h4>";
            echo "<p>Login credentials untuk testing:</p>";
            echo "<ul>";
            echo "<li><strong>Sample Mahasiswa 1:</strong> NIM: 12345001, Password: password123</li>";
            echo "<li><strong>Sample Mahasiswa 2:</strong> NIM: 12345002, Password: password123</li>";
            echo "</ul>";
            echo "</div>";
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo "<div class='alert alert-danger'>";
            echo "<h4>❌ Failed to create sample data!</h4>";
            echo "<p>Error: " . $e->getMessage() . "</p>";
            echo "</div>";
        }
        
        echo "<p><a href='" . base_url('clean_reset') . "' class='btn btn-info'>🔙 Back to Main Menu</a></p>";
    }
    
    private function show_detailed_mahasiswa_data()
    {
        // Show current mahasiswa with their proposals
        $mahasiswa_data = $this->db->query("
            SELECT 
                m.id, m.nim, m.nama, m.email,
                COUNT(pm.id) as total_proposals,
                COUNT(jb.id) as total_jurnal
            FROM mahasiswa m
            LEFT JOIN proposal_mahasiswa pm ON m.id = pm.mahasiswa_id
            LEFT JOIN jurnal_bimbingan jb ON pm.id = jb.proposal_id
            GROUP BY m.id
            ORDER BY m.id
        ")->result();
        
        if (!empty($mahasiswa_data)) {
            echo "<h4>👤 Mahasiswa yang akan dihapus:</h4>";
            echo "<table>";
            echo "<tr><th>ID</th><th>NIM</th><th>Nama</th><th>Email</th><th>Proposals</th><th>Jurnal</th></tr>";
            
            foreach ($mahasiswa_data as $m) {
                echo "<tr>";
                echo "<td>{$m->id}</td>";
                echo "<td>{$m->nim}</td>";
                echo "<td>{$m->nama}</td>";
                echo "<td>{$m->email}</td>";
                echo "<td>{$m->total_proposals}</td>";
                echo "<td>{$m->total_jurnal}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='info'>ℹ️ Tidak ada data mahasiswa untuk dihapus.</p>";
        }
    }
}
?>