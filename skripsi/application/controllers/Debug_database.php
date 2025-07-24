<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Debug_database extends CI_Controller 
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
        echo "<h2>🔍 DEBUG DATABASE - SEBELUM PERBAIKAN</h2>";
        echo "<hr>";
        
        try {
            // 1. CEK STRUKTUR TABEL DOSEN
            echo "<h3>1. Struktur Tabel Dosen (Level Check)</h3>";
            $query = $this->db->query("SHOW COLUMNS FROM dosen WHERE Field = 'level'");
            $level_column = $query->row_array();
            echo "<strong>Current level enum:</strong> {$level_column['Type']}<br>";
            
            if (strpos($level_column['Type'], "'5'") !== false) {
                echo "<span style='color: green;'>✅ Level '5' untuk staf sudah ada</span><br>";
            } else {
                echo "<span style='color: red;'>❌ Level '5' untuk staf belum ada</span><br>";
            }
            
            // CEK DATA STAF
            $staf_count = $this->db->where('level', '5')->count_all_results('dosen');
            echo "<strong>Total Staf (level 5):</strong> $staf_count<br>";
            
            // 2. CEK AUTO INCREMENT STATUS
            echo "<hr><h3>2. Auto Increment Status</h3>";
            $db_name = $this->db->database;
            $query = $this->db->query("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db_name' AND TABLE_NAME = 'proposal_mahasiswa'");
            $auto_inc = $query->row()->AUTO_INCREMENT;
            
            $max_id = $this->db->select_max('id')->get('proposal_mahasiswa')->row()->id;
            if (!$max_id) $max_id = 0;
            
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Metric</th><th>Value</th><th>Status</th></tr>";
            echo "<tr><td>Current AUTO_INCREMENT</td><td>$auto_inc</td><td>" . ($auto_inc > $max_id ? '✅ OK' : '❌ Problem') . "</td></tr>";
            echo "<tr><td>MAX ID in table</td><td>$max_id</td><td>-</td></tr>";
            echo "<tr><td>Expected AUTO_INCREMENT</td><td>" . ($max_id + 1) . "</td><td>" . ($auto_inc == ($max_id + 1) ? '✅ Perfect' : '⚠️ Needs fix') . "</td></tr>";
            echo "</table>";
            
            // 3. CEK DATA PROPOSAL MAHASISWA
            echo "<hr><h3>3. Data Proposal Mahasiswa (Status Workflow)</h3>";
            $this->db->select('
                pm.id,
                m.nim,
                m.nama as nama_mahasiswa,
                LEFT(pm.judul, 30) as judul_short,
                pm.status_kaprodi,
                pm.status_pembimbing,
                pm.dosen_id,
                d.nama as nama_dosen,
                pm.workflow_status
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'left');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->order_by('pm.id', 'DESC');
            $proposals = $this->db->get()->result();
            
            echo "<table border='1' cellpadding='5' cellspacing='0' style='font-size: 12px;'>";
            echo "<tr style='background: #f0f0f0;'>";
            echo "<th>ID</th><th>NIM</th><th>Nama</th><th>Judul</th><th>Status Kaprodi</th><th>Status Pembimbing</th><th>Dosen</th><th>Workflow</th><th>Can Add Jurnal?</th>";
            echo "</tr>";
            
            foreach ($proposals as $row) {
                $status_kaprodi_text = $row->status_kaprodi == '1' ? '✅ Disetujui' : ($row->status_kaprodi == '2' ? '❌ Ditolak' : '⏳ Pending');
                $status_pembimbing_text = $row->status_pembimbing == '1' ? '✅ Disetujui' : ($row->status_pembimbing == '2' ? '❌ Ditolak' : '⏳ Pending');
                
                // CEK APAKAH BISA TAMBAH JURNAL
                $can_add_jurnal = ($row->status_kaprodi == '1' && $row->status_pembimbing == '1' && $row->dosen_id != null);
                $can_add_text = $can_add_jurnal ? '✅ YES' : '❌ NO';
                
                echo "<tr>";
                echo "<td><strong>{$row->id}</strong></td>";
                echo "<td>{$row->nim}</td>";
                echo "<td>{$row->nama_mahasiswa}</td>";
                echo "<td>{$row->judul_short}...</td>";
                echo "<td>$status_kaprodi_text</td>";
                echo "<td>$status_pembimbing_text</td>";
                echo "<td>{$row->nama_dosen}</td>";
                echo "<td>{$row->workflow_status}</td>";
                echo "<td style='font-weight: bold;'>$can_add_text</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // 4. CEK JURNAL BIMBINGAN
            echo "<hr><h3>4. Data Jurnal Bimbingan (Current Status)</h3>";
            $this->db->select('
                jb.id,
                jb.proposal_id,
                pm.id as proposal_exists,
                m.nim,
                m.nama as nama_mahasiswa,
                jb.pertemuan_ke,
                jb.tanggal_bimbingan,
                jb.status_validasi,
                CASE jb.status_validasi
                    WHEN "0" THEN "Pending"
                    WHEN "1" THEN "Valid"
                    WHEN "2" THEN "Revisi"
                    ELSE "Unknown"
                END as status_text
            ');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id', 'left');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'left');
            $this->db->order_by('jb.proposal_id, jb.pertemuan_ke');
            $jurnal = $this->db->get()->result();
            
            echo "<table border='1' cellpadding='5' cellspacing='0' style='font-size: 12px;'>";
            echo "<tr style='background: #f0f0f0;'>";
            echo "<th>Jurnal ID</th><th>Proposal ID</th><th>NIM</th><th>Nama</th><th>Pertemuan</th><th>Tanggal</th><th>Status</th><th>Valid?</th>";
            echo "</tr>";
            
            foreach ($jurnal as $row) {
                $is_valid = $row->proposal_exists ? '✅ Valid' : '❌ Orphaned';
                
                echo "<tr>";
                echo "<td>{$row->id}</td>";
                echo "<td><strong>{$row->proposal_id}</strong></td>";
                echo "<td>{$row->nim}</td>";
                echo "<td>{$row->nama_mahasiswa}</td>";
                echo "<td>Ke-{$row->pertemuan_ke}</td>";
                echo "<td>{$row->tanggal_bimbingan}</td>";
                echo "<td>{$row->status_text}</td>";
                echo "<td>$is_valid</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // 5. CEK FOREIGN KEY CONSTRAINTS
            echo "<hr><h3>5. Foreign Key Constraints Status</h3>";
            $db_name = $this->db->database;
            $constraints = $this->db->query("
                SELECT 
                    rc.CONSTRAINT_NAME,
                    kcu.TABLE_NAME,
                    kcu.COLUMN_NAME,
                    kcu.REFERENCED_TABLE_NAME,
                    kcu.REFERENCED_COLUMN_NAME,
                    rc.DELETE_RULE,
                    rc.UPDATE_RULE
                FROM information_schema.REFERENTIAL_CONSTRAINTS rc
                JOIN information_schema.KEY_COLUMN_USAGE kcu ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                WHERE rc.CONSTRAINT_SCHEMA = '$db_name' 
                AND kcu.TABLE_NAME = 'jurnal_bimbingan'
                AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
            ")->result();
            
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<tr style='background: #f0f0f0;'>";
            echo "<th>Constraint</th><th>Column</th><th>References</th><th>Delete Rule</th><th>Update Rule</th>";
            echo "</tr>";
            
            foreach ($constraints as $row) {
                echo "<tr>";
                echo "<td>{$row->CONSTRAINT_NAME}</td>";
                echo "<td>{$row->COLUMN_NAME}</td>";
                echo "<td>{$row->REFERENCED_TABLE_NAME}.{$row->REFERENCED_COLUMN_NAME}</td>";
                echo "<td>{$row->DELETE_RULE}</td>";
                echo "<td>{$row->UPDATE_RULE}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // 6. SIMULASI INSERT JURNAL
            echo "<hr><h3>6. Simulasi Insert Jurnal (Test Cases)</h3>";
            
            // Test case untuk setiap mahasiswa yang memiliki proposal
            $this->db->select('
                m.id as mahasiswa_id,
                m.nim,
                m.nama,
                pm.id as proposal_id,
                pm.status_kaprodi,
                pm.status_pembimbing,
                pm.dosen_id
            ');
            $this->db->from('mahasiswa m');
            $this->db->join('proposal_mahasiswa pm', 'm.id = pm.mahasiswa_id', 'left');
            $this->db->where('pm.id IS NOT NULL');
            $this->db->order_by('m.id');
            $test_cases = $this->db->get()->result();
            
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<tr style='background: #f0f0f0;'>";
            echo "<th>Mahasiswa</th><th>Proposal ID</th><th>Status</th><th>Test Insert</th><th>Result</th>";
            echo "</tr>";
            
            foreach ($test_cases as $row) {
                // Test apakah bisa insert jurnal
                $can_insert = ($row->status_kaprodi == '1' && $row->status_pembimbing == '1' && $row->dosen_id != null);
                
                if ($can_insert) {
                    // Test insert query (tanpa benar-benar insert)
                    try {
                        $test_result = $this->db->get_where('proposal_mahasiswa', ['id' => $row->proposal_id])->row();
                        $test_result_text = $test_result ? "✅ INSERT WILL SUCCESS" : "❌ INSERT WILL FAIL: Proposal not found";
                    } catch (Exception $e) {
                        $test_result_text = "❌ INSERT WILL FAIL: " . $e->getMessage();
                    }
                } else {
                    $test_result_text = "⏳ WAITING FOR APPROVAL";
                }
                
                echo "<tr>";
                echo "<td>{$row->nim} - {$row->nama}</td>";
                echo "<td><strong>{$row->proposal_id}</strong></td>";
                echo "<td>";
                echo "Kaprodi: " . ($row->status_kaprodi == '1' ? '✅' : ($row->status_kaprodi == '2' ? '❌' : '⏳')) . " ";
                echo "Pembimbing: " . ($row->status_pembimbing == '1' ? '✅' : ($row->status_pembimbing == '2' ? '❌' : '⏳'));
                echo "</td>";
                echo "<td>proposal_id = {$row->proposal_id}</td>";
                echo "<td style='font-weight: bold;'>$test_result_text</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // 7. REKOMENDASI PERBAIKAN
            echo "<hr><h3>7. Rekomendasi Perbaikan</h3>";
            echo "<div style='background: #f9f9f9; padding: 15px; border-radius: 5px;'>";
            echo "<h4>🔧 Langkah Perbaikan yang Disarankan:</h4>";
            echo "<ol>";
            
            if ($auto_inc != ($max_id + 1)) {
                echo "<li><strong>Fix AUTO_INCREMENT:</strong> ALTER TABLE proposal_mahasiswa AUTO_INCREMENT = " . ($max_id + 1) . ";</li>";
            }
            
            if (strpos($level_column['Type'], "'5'") === false) {
                echo "<li><strong>Add Staf Level:</strong> ALTER TABLE dosen MODIFY COLUMN level enum('1','2','4','5') NOT NULL DEFAULT '2';</li>";
            }
            
            if ($staf_count == 0) {
                echo "<li><strong>Insert Staf Data:</strong> INSERT INTO dosen (nip, nama, email, level) VALUES (...);</li>";
            }
            
            echo "<li><strong>Create staf_aktivitas table:</strong> CREATE TABLE staf_aktivitas (...);</li>";
            echo "<li><strong>Create staf_v view:</strong> CREATE VIEW staf_v AS SELECT ... WHERE level = '5';</li>";
            echo "<li><strong>Optimize tables:</strong> OPTIMIZE TABLE proposal_mahasiswa, jurnal_bimbingan;</li>";
            echo "</ol>";
            
            echo "<h4>⚡ Next Steps:</h4>";
            echo "<p>1. Jalankan SQL perbaikan bertahap<br>";
            echo "2. Update controller Bimbingan.php<br>";
            echo "3. Test functionality<br>";
            echo "4. Hapus debug controller ini</p>";
            echo "</div>";
            
            echo "<hr>";
            echo "<h3 style='color: green;'>✅ DEBUG COMPLETED - DATABASE READY FOR REPAIR</h3>";
            
        } catch (Exception $e) {
            echo "<span style='color: red;'>❌ Database error: " . $e->getMessage() . "</span>";
        }
        
        // Add CSS
        echo "<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th { background: #f0f0f0; padding: 8px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        code { background: #f8f8f8; padding: 2px 4px; border-radius: 3px; }
        </style>";
    }
    
    // Method untuk menjalankan SQL perbaikan
    public function fix_database()
    {
        // SECURITY CHECK
        if ($this->session->userdata('level') != '1') {
            show_404();
        }
        
        echo "<h2>🔧 DATABASE FIX - EXECUTION</h2>";
        echo "<hr>";
        
        try {
            // Step 1: Fix AUTO_INCREMENT
            $max_id = $this->db->select_max('id')->get('proposal_mahasiswa')->row()->id;
            if (!$max_id) $max_id = 0;
            $next_increment = $max_id + 1;
            
            $this->db->query("ALTER TABLE proposal_mahasiswa AUTO_INCREMENT = $next_increment");
            echo "✅ AUTO_INCREMENT fixed to $next_increment<br>";
            
            // Step 2: Add staf level if not exists
            $level_column = $this->db->query("SHOW COLUMNS FROM dosen WHERE Field = 'level'")->row_array();
            if (strpos($level_column['Type'], "'5'") === false) {
                $this->db->query("ALTER TABLE dosen MODIFY COLUMN level enum('1','2','4','5') NOT NULL DEFAULT '2' COMMENT '1 = admin, 2 = dosen, 4 = kaprodi, 5 = staf'");
                echo "✅ Staf level '5' added to enum<br>";
            } else {
                echo "✅ Staf level '5' already exists<br>";
            }
            
            // Step 3: Check and fix foreign key constraints
            $db_name = $this->db->database;
            $constraints = $this->db->query("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = '$db_name' 
                AND TABLE_NAME = 'jurnal_bimbingan' 
                AND CONSTRAINT_NAME = 'fk_jurnal_proposal'
            ")->result();
            
            if (empty($constraints)) {
                $this->db->query("
                    ALTER TABLE jurnal_bimbingan 
                    ADD CONSTRAINT fk_jurnal_proposal 
                    FOREIGN KEY (proposal_id) REFERENCES proposal_mahasiswa(id) 
                    ON DELETE CASCADE ON UPDATE CASCADE
                ");
                echo "✅ Foreign key constraint added<br>";
            } else {
                echo "✅ Foreign key constraint already exists<br>";
            }
            
            // Step 4: Remove orphaned data if any
            $orphaned = $this->db->query("
                SELECT COUNT(*) as count
                FROM jurnal_bimbingan jb 
                LEFT JOIN proposal_mahasiswa pm ON jb.proposal_id = pm.id 
                WHERE pm.id IS NULL
            ")->row()->count;
            
            if ($orphaned > 0) {
                $this->db->query("
                    DELETE jb FROM jurnal_bimbingan jb 
                    LEFT JOIN proposal_mahasiswa pm ON jb.proposal_id = pm.id 
                    WHERE pm.id IS NULL
                ");
                echo "✅ Removed $orphaned orphaned jurnal records<br>";
            } else {
                echo "✅ No orphaned data found<br>";
            }
            
            echo "<hr>";
            echo "<h3 style='color: green;'>🎉 DATABASE FIX COMPLETED SUCCESSFULLY!</h3>";
            echo "<p><strong>Next Steps:</strong></p>";
            echo "<ol>";
            echo "<li>Update Bimbingan controller</li>";
            echo "<li>Test mahasiswa jurnal functionality</li>";
            echo "<li>Add staf users if needed</li>";
            echo "<li>Remove this debug controller</li>";
            echo "</ol>";
            
        } catch (Exception $e) {
            echo "<span style='color: red;'>❌ Error during fix: " . $e->getMessage() . "</span>";
        }
    }
}