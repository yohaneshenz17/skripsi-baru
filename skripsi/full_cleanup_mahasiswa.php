<?php
/**
 * FULL CLEANUP MAHASISWA DATABASE - STK St. Yakobus
 * 
 * Upload ke: /public_html/skripsi/full_cleanup_mahasiswa.php
 * Akses: https://stkyakobus.ac.id/skripsi/full_cleanup_mahasiswa.php
 * 
 * TUJUAN: Hapus SEMUA data mahasiswa dan workflow terkait untuk membersihkan sistem
 * testing dan memulai fresh testing dari awal.
 * 
 * ⚠️ HAPUS FILE INI SETELAH SELESAI UNTUK KEAMANAN!
 */

date_default_timezone_set('Asia/Jakarta');

// Database configuration
$db_host = 'localhost';
$db_user = 'stkp7133_skripsi';
$db_pass = 'stkmerauke01';
$db_name = 'stkp7133_skripsi';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Full Cleanup Mahasiswa Database - STK St. Yakobus</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .danger { background: #f5c6cb; color: #721c24; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #dc3545; }
        .section { background: #f8f9fa; border-radius: 10px; padding: 20px; margin: 20px 0; border: 1px solid #dee2e6; }
        .btn { background: #007bff; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; border: none; cursor: pointer; font-weight: bold; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        .btn-warning { background: #ffc107; color: #212529; }
        .header { background: linear-gradient(135deg, #dc3545, #fd7e14); color: white; padding: 25px; text-align: center; border-radius: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .highlight { background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 5px solid #ffc107; margin: 15px 0; }
        .delete { background: #f8d7da; }
        .progress { width: 100%; height: 25px; background: #e9ecef; border-radius: 12px; overflow: hidden; margin: 15px 0; }
        .progress-bar { height: 100%; background: linear-gradient(45deg, #dc3545, #fd7e14); transition: width 0.3s; }
        .step { margin: 10px 0; padding: 10px; border-left: 4px solid #007bff; background: #f8f9fa; }
        .step.completed { border-left-color: #28a745; }
        .step.failed { border-left-color: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🗑️ Full Cleanup Mahasiswa Database</h1>
        <p>STK Santo Yakobus Merauke</p>
        <p><strong>OPERASI:</strong> Hapus SEMUA data mahasiswa dan workflow terkait</p>
        <p><strong>WAKTU:</strong> <?= date('d F Y, H:i:s') ?> WIT</p>
    </div>

    <div class="danger">
        <h2>⚠️ PERINGATAN KRITIS!</h2>
        <p><strong>Script ini akan MENGHAPUS PERMANEN semua data mahasiswa dan semua workflow terkait!</strong></p>
        <ul>
            <li>Semua data mahasiswa (profil, password, foto)</li>
            <li>Semua proposal tugas akhir</li>
            <li>Semua jurnal bimbingan</li>
            <li>Semua data konsultasi</li>
            <li>Semua data penelitian dan hasil</li>
            <li>Semua data seminar dan hasil</li>
            <li>Semua workflow proposal</li>
            <li>Semua notifikasi untuk mahasiswa</li>
            <li>Semua aktivitas staf terkait mahasiswa</li>
        </ul>
        <p><strong style="color: #721c24;">OPERASI INI TIDAK BISA DIBATALKAN!</strong></p>
        <p><strong>Data dosen, admin, staf, prodi, dan pengaturan sistem TIDAK akan terpengaruh.</strong></p>
    </div>

    <?php
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // Connect to database
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($mysqli->connect_error) {
        echo "<div class='error'>❌ Database connection failed: " . $mysqli->connect_error . "</div>";
        exit;
    }
    
    $mysqli->set_charset("utf8");
    
    if ($action) {
        handleAction($action, $mysqli);
    } else {
        showMainPage($mysqli);
    }
    
    $mysqli->close();
    
    function showMainPage($mysqli) {
        echo "<div class='section'>";
        echo "<h2>🎯 Analisis Data Saat Ini</h2>";
        
        // Show current data summary
        showCurrentDataSummary($mysqli);
        
        // Show affected tables detail
        showAffectedTablesDetail($mysqli);
        
        // Show action buttons
        echo "<h3>🛠️ Pilihan Aksi:</h3>";
        echo "<div style='text-align: center; margin: 20px 0;'>";
        echo "<a href='?action=analyze' class='btn btn-warning'>📊 Analisis Detail</a>";
        echo "<a href='?action=backup' class='btn btn-success'>💾 Backup Dulu</a>";
        echo "<a href='?action=cleanup' class='btn btn-danger' onclick='return confirm(\"YAKIN INGIN MENGHAPUS SEMUA DATA MAHASISWA? Operasi ini TIDAK BISA DIBATALKAN!\")'>🗑️ CLEANUP SEKARANG</a>";
        echo "</div>";
        
        echo "</div>";
    }
    
    function showCurrentDataSummary($mysqli) {
        echo "<h3>📊 Ringkasan Data yang Akan Dihapus:</h3>";
        
        $summary_queries = [
            'Mahasiswa' => "SELECT COUNT(*) as total FROM mahasiswa",
            'Proposal Tugas Akhir' => "SELECT COUNT(*) as total FROM proposal_mahasiswa",
            'Jurnal Bimbingan' => "SELECT COUNT(*) as total FROM jurnal_bimbingan",
            'Data Konsultasi' => "SELECT COUNT(*) as total FROM konsultasi",
            'Data Penelitian' => "SELECT COUNT(*) as total FROM penelitian",
            'Data Seminar' => "SELECT COUNT(*) as total FROM seminar",
            'Data Skripsi' => "SELECT COUNT(*) as total FROM skripsi",
            'Workflow Proposal' => "SELECT COUNT(*) as total FROM proposal_workflow",
            'Hasil Kegiatan' => "SELECT COUNT(*) as total FROM hasil_kegiatan",
            'Notifikasi Mahasiswa' => "SELECT COUNT(*) as total FROM notifikasi WHERE untuk_role = 'mahasiswa'",
            'Aktivitas Staf terkait Mahasiswa' => "SELECT COUNT(*) as total FROM staf_aktivitas WHERE mahasiswa_id IS NOT NULL"
        ];
        
        echo "<table>";
        echo "<tr><th>Jenis Data</th><th>Jumlah Record</th><th>Status</th></tr>";
        
        $total_records = 0;
        foreach ($summary_queries as $label => $query) {
            $result = $mysqli->query($query);
            $count = $result ? $result->fetch_assoc()['total'] : 0;
            $total_records += $count;
            
            $status_class = $count > 0 ? 'delete' : '';
            $status_text = $count > 0 ? "❌ AKAN DIHAPUS" : "✅ Kosong";
            
            echo "<tr class='{$status_class}'>";
            echo "<td><strong>{$label}</strong></td>";
            echo "<td>{$count}</td>";
            echo "<td><strong>{$status_text}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div class='warning'>";
        echo "<h4>⚠️ Total Record yang Akan Dihapus: <strong style='color: red;'>{$total_records}</strong></h4>";
        echo "</div>";
    }
    
    function showAffectedTablesDetail($mysqli) {
        echo "<h3>🗄️ Detail Tabel yang Akan Terpengaruh:</h3>";
        
        // Show mahasiswa data
        $result = $mysqli->query("SELECT id, nim, nama, email, prodi_id FROM mahasiswa ORDER BY id");
        
        if ($result && $result->num_rows > 0) {
            echo "<h4>👥 Data Mahasiswa:</h4>";
            echo "<table>";
            echo "<tr><th>ID</th><th>NIM</th><th>Nama</th><th>Email</th><th>Prodi</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr class='delete'>";
                echo "<td>{$row['id']}</td>";
                echo "<td>" . htmlspecialchars($row['nim']) . "</td>";
                echo "<td><strong>" . htmlspecialchars($row['nama']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>{$row['prodi_id']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='info'>ℹ️ Tidak ada data mahasiswa ditemukan.</div>";
        }
        
        // Show proposal data
        $result = $mysqli->query("
            SELECT pm.id, pm.judul, m.nama, pm.workflow_status 
            FROM proposal_mahasiswa pm 
            JOIN mahasiswa m ON pm.mahasiswa_id = m.id 
            ORDER BY pm.id
        ");
        
        if ($result && $result->num_rows > 0) {
            echo "<h4>📝 Data Proposal:</h4>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Judul (Ringkas)</th><th>Mahasiswa</th><th>Status Workflow</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                $judul_ringkas = strlen($row['judul']) > 50 ? substr($row['judul'], 0, 50) . '...' : $row['judul'];
                echo "<tr class='delete'>";
                echo "<td>{$row['id']}</td>";
                echo "<td>" . htmlspecialchars($judul_ringkas) . "</td>";
                echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                echo "<td>{$row['workflow_status']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    function handleAction($action, $mysqli) {
        switch ($action) {
            case 'analyze':
                analyzeDetailData($mysqli);
                break;
            case 'backup':
                backupData($mysqli);
                break;
            case 'cleanup':
                performCleanup($mysqli);
                break;
            default:
                echo "<div class='error'>Unknown action: {$action}</div>";
        }
    }
    
    function analyzeDetailData($mysqli) {
        echo "<div class='section'>";
        echo "<h2>🔍 Analisis Detail Data</h2>";
        
        // Get detailed breakdown
        $tables_to_analyze = [
            'mahasiswa' => [
                'query' => "SELECT COUNT(*) as total FROM mahasiswa",
                'description' => 'Semua akun mahasiswa dan profil'
            ],
            'proposal_mahasiswa' => [
                'query' => "SELECT COUNT(*) as total FROM proposal_mahasiswa",
                'description' => 'Semua proposal tugas akhir'
            ],
            'jurnal_bimbingan' => [
                'query' => "SELECT COUNT(*) as total FROM jurnal_bimbingan",
                'description' => 'Semua jurnal bimbingan mahasiswa'
            ],
            'konsultasi' => [
                'query' => "SELECT COUNT(*) as total FROM konsultasi",
                'description' => 'Semua data konsultasi mahasiswa'
            ],
            'penelitian' => [
                'query' => "SELECT COUNT(*) as total FROM penelitian",
                'description' => 'Semua data penelitian'
            ],
            'seminar' => [
                'query' => "SELECT COUNT(*) as total FROM seminar",
                'description' => 'Semua data seminar'
            ],
            'skripsi' => [
                'query' => "SELECT COUNT(*) as total FROM skripsi",
                'description' => 'Semua data skripsi'
            ],
            'hasil_kegiatan' => [
                'query' => "SELECT COUNT(*) as total FROM hasil_kegiatan",
                'description' => 'Semua hasil kegiatan mahasiswa'
            ],
            'proposal_workflow' => [
                'query' => "SELECT COUNT(*) as total FROM proposal_workflow",
                'description' => 'Semua workflow proposal'
            ],
            'hasil_seminar' => [
                'query' => "SELECT COUNT(*) as total FROM hasil_seminar",
                'description' => 'Semua hasil seminar'
            ],
            'hasil_penelitian' => [
                'query' => "SELECT COUNT(*) as total FROM hasil_penelitian",
                'description' => 'Semua hasil penelitian'
            ],
            'notifikasi' => [
                'query' => "SELECT COUNT(*) as total FROM notifikasi WHERE untuk_role = 'mahasiswa'",
                'description' => 'Notifikasi untuk mahasiswa'
            ],
            'staf_aktivitas' => [
                'query' => "SELECT COUNT(*) as total FROM staf_aktivitas WHERE mahasiswa_id IS NOT NULL",
                'description' => 'Aktivitas staf terkait mahasiswa'
            ]
        ];
        
        echo "<h3>📈 Breakdown Detail per Tabel:</h3>";
        echo "<table>";
        echo "<tr><th>Tabel</th><th>Deskripsi</th><th>Jumlah Record</th><th>Dampak</th></tr>";
        
        $total_all_records = 0;
        foreach ($tables_to_analyze as $table => $info) {
            $result = $mysqli->query($info['query']);
            $count = $result ? $result->fetch_assoc()['total'] : 0;
            $total_all_records += $count;
            
            $impact = $count > 0 ? "❌ {$count} record akan dihapus" : "✅ Tidak ada dampak";
            $row_class = $count > 0 ? 'delete' : '';
            
            echo "<tr class='{$row_class}'>";
            echo "<td><code>{$table}</code></td>";
            echo "<td>{$info['description']}</td>";
            echo "<td><strong>{$count}</strong></td>";
            echo "<td>{$impact}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div class='danger'>";
        echo "<h4>⚠️ TOTAL KESELURUHAN RECORD YANG AKAN DIHAPUS: <strong>{$total_all_records}</strong></h4>";
        echo "<p>Setelah cleanup, sistem akan bersih dari semua data mahasiswa dan testing data.</p>";
        echo "</div>";
        
        echo "<div style='text-align: center; margin: 20px 0;'>";
        echo "<a href='?action=backup' class='btn btn-success'>Lanjut ke Backup</a>";
        echo "<a href='?' class='btn'>Kembali</a>";
        echo "</div>";
        
        echo "</div>";
    }
    
    function backupData($mysqli) {
        echo "<div class='section'>";
        echo "<h2>📦 Backup Data Sebelum Hapus</h2>";
        
        if (isset($_GET['do_backup'])) {
            performBackup($mysqli);
        } else {
            echo "<div class='info'>";
            echo "<h3>📋 Backup akan mencakup:</h3>";
            echo "<ul>";
            echo "<li>Semua data mahasiswa</li>";
            echo "<li>Semua proposal dan workflow</li>";
            echo "<li>Semua data bimbingan dan konsultasi</li>";
            echo "<li>Semua data penelitian dan seminar</li>";
            echo "<li>Semua hasil dan kegiatan</li>";
            echo "<li>Semua notifikasi dan aktivitas terkait</li>";
            echo "</ul>";
            echo "</div>";
            
            echo "<div class='warning'>";
            echo "<h3>⚠️ PENTING:</h3>";
            echo "<p>Backup akan dibuat dalam bentuk tabel baru dengan suffix <code>_backup_full_" . date('Ymd_His') . "</code></p>";
            echo "<p>Ini memungkinkan recovery data jika diperlukan di masa depan.</p>";
            echo "</div>";
            
            echo "<div style='text-align: center; margin: 20px 0;'>";
            echo "<a href='?action=backup&do_backup=1' class='btn btn-success'>Mulai Backup</a>";
            echo "<a href='?' class='btn'>Batal</a>";
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    function performBackup($mysqli) {
        echo "<h3>🔄 Melakukan Backup Lengkap...</h3>";
        
        $backup_suffix = '_backup_full_' . date('Ymd_His');
        $backup_success = 0;
        $backup_total = 0;
        
        // Tables to backup in order
        $backup_tables = [
            'mahasiswa' => "CREATE TABLE mahasiswa{$backup_suffix} AS SELECT * FROM mahasiswa",
            'proposal_mahasiswa' => "CREATE TABLE proposal_mahasiswa{$backup_suffix} AS SELECT * FROM proposal_mahasiswa",
            'jurnal_bimbingan' => "CREATE TABLE jurnal_bimbingan{$backup_suffix} AS SELECT * FROM jurnal_bimbingan",
            'konsultasi' => "CREATE TABLE konsultasi{$backup_suffix} AS SELECT * FROM konsultasi",
            'penelitian' => "CREATE TABLE penelitian{$backup_suffix} AS SELECT * FROM penelitian",
            'seminar' => "CREATE TABLE seminar{$backup_suffix} AS SELECT * FROM seminar",
            'skripsi' => "CREATE TABLE skripsi{$backup_suffix} AS SELECT * FROM skripsi",
            'hasil_kegiatan' => "CREATE TABLE hasil_kegiatan{$backup_suffix} AS SELECT * FROM hasil_kegiatan",
            'proposal_workflow' => "CREATE TABLE proposal_workflow{$backup_suffix} AS SELECT * FROM proposal_workflow",
            'hasil_seminar' => "CREATE TABLE hasil_seminar{$backup_suffix} AS SELECT * FROM hasil_seminar",
            'hasil_penelitian' => "CREATE TABLE hasil_penelitian{$backup_suffix} AS SELECT * FROM hasil_penelitian",
            'notifikasi' => "CREATE TABLE notifikasi{$backup_suffix} AS SELECT * FROM notifikasi WHERE untuk_role = 'mahasiswa'",
            'staf_aktivitas' => "CREATE TABLE staf_aktivitas{$backup_suffix} AS SELECT * FROM staf_aktivitas WHERE mahasiswa_id IS NOT NULL"
        ];
        
        foreach ($backup_tables as $table => $query) {
            $backup_total++;
            echo "<div class='info'>📦 Backup {$table}...</div>";
            
            if ($mysqli->query($query)) {
                $backup_success++;
                echo "<div class='success'>✅ Backup {$table} berhasil: {$table}{$backup_suffix}</div>";
            } else {
                echo "<div class='error'>❌ Backup {$table} gagal: " . $mysqli->error . "</div>";
            }
            flush();
        }
        
        if ($backup_success == $backup_total) {
            echo "<div class='success'>";
            echo "<h3>✅ Backup Lengkap Berhasil!</h3>";
            echo "<p>Semua data telah di-backup dengan suffix: <code>{$backup_suffix}</code></p>";
            echo "<p>Total tabel backup: <strong>{$backup_success}</strong></p>";
            echo "</div>";
            
            echo "<div style='text-align: center; margin: 20px 0;'>";
            echo "<a href='?action=cleanup' class='btn btn-danger' onclick='return confirm(\"Yakin ingin melanjutkan cleanup?\")'>Lanjut ke Cleanup</a>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<h3>❌ Backup Tidak Lengkap!</h3>";
            echo "<p>Berhasil: {$backup_success}/{$backup_total}. Cleanup dibatalkan untuk keamanan.</p>";
            echo "</div>";
        }
    }
    
    function performCleanup($mysqli) {
        echo "<div class='section'>";
        echo "<h2>🗑️ Melakukan Cleanup Data</h2>";
        
        if (isset($_GET['confirm']) && $_GET['confirm'] == 'final') {
            executeFullCleanup($mysqli);
        } else {
            echo "<div class='danger'>";
            echo "<h3>⚠️ KONFIRMASI TERAKHIR!</h3>";
            echo "<p>Anda akan <strong>MENGHAPUS PERMANEN</strong> semua data berikut:</p>";
            echo "<ul>";
            echo "<li>Semua akun mahasiswa dan profil</li>";
            echo "<li>Semua proposal tugas akhir</li>";
            echo "<li>Semua jurnal bimbingan</li>";
            echo "<li>Semua data konsultasi dan penelitian</li>";
            echo "<li>Semua data seminar dan skripsi</li>";
            echo "<li>Semua workflow dan hasil kegiatan</li>";
            echo "<li>Semua notifikasi untuk mahasiswa</li>";
            echo "</ul>";
            echo "<p><strong style='color: red; font-size: 18px;'>OPERASI INI TIDAK BISA DIBATALKAN!</strong></p>";
            echo "<p>Sistem akan kembali bersih seperti fresh installation untuk testing ulang.</p>";
            echo "</div>";
            
            echo "<div style='text-align: center; margin: 20px 0;'>";
            echo "<a href='?action=cleanup&confirm=final' class='btn btn-danger' onclick='return confirm(\"TERAKHIR KALI: Yakin ingin hapus SEMUA data mahasiswa?\")'>YA, HAPUS SEMUA SEKARANG!</a>";
            echo "<a href='?' class='btn btn-success'>BATAL</a>";
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    function executeFullCleanup($mysqli) {
        echo "<h3>🔄 Executing Full Cleanup...</h3>";
        
        // Progress tracking
        $total_steps = 13;
        $current_step = 0;
        $success_count = 0;
        $error_count = 0;
        
        // Start transaction
        $mysqli->autocommit(FALSE);
        
        try {
            // Step 1: Delete notifikasi for mahasiswa
            $current_step++;
            showProgress($current_step, $total_steps, "Hapus notifikasi mahasiswa...");
            $result = $mysqli->query("DELETE FROM notifikasi WHERE untuk_role = 'mahasiswa'");
            if ($result) {
                $affected = $mysqli->affected_rows;
                echo "<div class='success'>✅ Hapus {$affected} notifikasi mahasiswa</div>";
                $success_count++;
            } else {
                throw new Exception("Error deleting notifikasi: " . $mysqli->error);
            }
            
            // Step 2: Delete staf_aktivitas related to mahasiswa
            $current_step++;
            showProgress($current_step, $total_steps, "Hapus aktivitas staf terkait mahasiswa...");
            $result = $mysqli->query("DELETE FROM staf_aktivitas WHERE mahasiswa_id IS NOT NULL");
            if ($result) {
                $affected = $mysqli->affected_rows;
                echo "<div class='success'>✅ Hapus {$affected} aktivitas staf</div>";
                $success_count++;
            } else {
                throw new Exception("Error deleting staf_aktivitas: " . $mysqli->error);
            }
            
            // Step 3: Delete hasil_seminar (via seminar)
            $current_step++;
            showProgress($current_step, $total_steps, "Hapus hasil seminar...");
            $result = $mysqli->query("DELETE FROM hasil_seminar WHERE seminar_id IN (SELECT id FROM seminar)");
            if ($result) {
                $affected = $mysqli->affected_rows;
                echo "<div class='success'>✅ Hapus {$affected} hasil seminar</div>";
                $success_count++;
            } else {
                throw new Exception("Error deleting hasil_seminar: " . $mysqli->error);
            }
            
            // Step 4: Delete hasil_penelitian (via penelitian)
            $current_step++;
            showProgress($current_step, $total_steps, "Hapus hasil penelitian...");
            $result = $mysqli->query("DELETE FROM hasil_penelitian WHERE penelitian_id IN (SELECT id FROM penelitian)");
            if ($result) {
                $affected = $mysqli->affected_rows;
                echo "<div class='success'>✅ Hapus {$affected} hasil penelitian</div>";
                $success_count++;
            } else {
                throw new Exception("Error deleting hasil_penelitian: " . $mysqli->error);
            }
            
            // Step 5: Delete jurnal_bimbingan
            $current_step++;
            showProgress($current_step, $total_steps, "Hapus jurnal bimbingan...");
            $result = $mysqli->query("DELETE FROM jurnal_bimbingan");
            if ($result) {
                $affected = $mysqli->affected_rows;
                echo "<div class='success'>✅ Hapus {$affected} jurnal bimbingan</div>";
                $success_count++;
            } else {
                throw new Exception("Error deleting jurnal_bimbingan: " . $mysqli->error);
            }
            
            // Step 6: Delete konsultasi
            $current_step++;
            showProgress($current_step, $total_steps, "Hapus data konsultasi...");
            $result = $mysqli->query("DELETE FROM konsultasi");
            if ($result) {
                $affected = $mysqli->affected_rows;
                echo "<div class='success'>✅ Hapus {$affected} data konsultasi</div>";
                $success_count++;
            } else {
                throw new Exception("Error deleting konsultasi: " . $mysqli->error);
            }
            
            // Step 7: Delete seminar
            $current_step++;
            showProgress($current_step, $total_steps, "Hapus data seminar...");
            $result = $mysqli->query("DELETE FROM seminar");
            if ($result) {
                $affected = $mysqli->affected_rows;
                echo "<div class='success'>✅ Hapus {$affected} data seminar</div>";
                $success_count++;
            } else {
                throw new Exception("Error deleting seminar: " . $mysqli->error);
            }
            
            // Step 8: Delete penelitian
            $current_step++;
            showProgress($current_step, $total_steps, "Hapus data penelitian...");
            $result = $mysqli->query("DELETE FROM penelitian");
            if ($result) {
                $affected = $mysqli->affected_rows;
                echo "<div class='success'>✅ Hapus {$affected} data penelitian</div>";
                $success_count++;
            } else {
                throw new Exception("Error deleting penelitian: " . $mysqli->error);
            }
            
            // Step 9: Delete skripsi
            $current_step++;
            showProgress($current_step, $total_steps, "Hapus data skripsi...");
            $result = $mysqli->query("DELETE FROM skripsi");
            if ($result) {
                $affected = $mysqli->affected_rows;
                echo "<div class='success'>✅ Hapus {$affected} data skripsi</div>";
                $success_count++;
            } else {
                throw new Exception("Error deleting skripsi: " . $mysqli->error);
            }
            
            // Step 10: Delete hasil_kegiatan
            $current_step++;
            showProgress($current_step, $total_steps, "Hapus hasil kegiatan...");
            $result = $mysqli->query("DELETE FROM hasil_kegiatan");
            if ($result) {
                $affected = $mysqli->affected_rows;
                echo "<div class='success'>✅ Hapus {$affected} hasil kegiatan</div>";
                $success_count++;
            } else {
                throw new Exception("Error deleting hasil_kegiatan: " . $mysqli->error);
            }
            
            // Step 11: Delete proposal_workflow
            $current_step++;
            showProgress($current_step, $total_steps, "Hapus workflow proposal...");
            $result = $mysqli->query("DELETE FROM proposal_workflow");
            if ($result) {
                $affected = $mysqli->affected_rows;
                echo "<div class='success'>✅ Hapus {$affected} workflow proposal</div>";
                $success_count++;
            } else {
                throw new Exception("Error deleting proposal_workflow: " . $mysqli->error);
            }
            
            // Step 12: Delete proposal_mahasiswa
            $current_step++;
            showProgress($current_step, $total_steps, "Hapus proposal mahasiswa...");
            $result = $mysqli->query("DELETE FROM proposal_mahasiswa");
            if ($result) {
                $affected = $mysqli->affected_rows;
                echo "<div class='success'>✅ Hapus {$affected} proposal mahasiswa</div>";
                $success_count++;
            } else {
                throw new Exception("Error deleting proposal_mahasiswa: " . $mysqli->error);
            }
            
            // Step 13: Delete mahasiswa (final step)
            $current_step++;
            showProgress($current_step, $total_steps, "Hapus data mahasiswa...");
            $result = $mysqli->query("DELETE FROM mahasiswa");
            if ($result) {
                $deleted_mahasiswa = $mysqli->affected_rows;
                echo "<div class='success'>✅ Hapus {$deleted_mahasiswa} data mahasiswa</div>";
                $success_count++;
            } else {
                throw new Exception("Error deleting mahasiswa: " . $mysqli->error);
            }
            
            // Commit transaction
            $mysqli->commit();
            
            echo "<div class='success'>";
            echo "<h3>🎉 FULL CLEANUP BERHASIL!</h3>";
            echo "<p><strong>Total mahasiswa dihapus:</strong> {$deleted_mahasiswa}</p>";
            echo "<p><strong>Steps berhasil:</strong> {$success_count}/{$total_steps}</p>";
            echo "<p><strong>Sistem sekarang bersih dari semua data testing mahasiswa!</strong></p>";
            echo "</div>";
            
            // Show final verification
            showFinalVerification($mysqli);
            
        } catch (Exception $e) {
            $mysqli->rollback();
            echo "<div class='error'>";
            echo "<h3>❌ CLEANUP GAGAL!</h3>";
            echo "<p>Error: " . $e->getMessage() . "</p>";
            echo "<p>Transaksi di-rollback, tidak ada data yang dihapus.</p>";
            echo "</div>";
        }
        
        $mysqli->autocommit(TRUE);
        
        echo "<div style='text-align: center; margin: 20px 0;'>";
        echo "<a href='?' class='btn btn-success'>Selesai</a>";
        echo "</div>";
    }
    
    function showProgress($current, $total, $message) {
        $percentage = ($current / $total) * 100;
        echo "<div class='progress'>";
        echo "<div class='progress-bar' style='width: {$percentage}%'></div>";
        echo "</div>";
        echo "<div class='info'>📋 Step {$current}/{$total}: {$message}</div>";
        flush();
    }
    
    function showFinalVerification($mysqli) {
        echo "<h3>✅ Verifikasi Akhir Sistem:</h3>";
        
        $verification_queries = [
            'Mahasiswa' => "SELECT COUNT(*) as total FROM mahasiswa",
            'Proposal' => "SELECT COUNT(*) as total FROM proposal_mahasiswa",
            'Jurnal Bimbingan' => "SELECT COUNT(*) as total FROM jurnal_bimbingan",
            'Konsultasi' => "SELECT COUNT(*) as total FROM konsultasi",
            'Penelitian' => "SELECT COUNT(*) as total FROM penelitian",
            'Seminar' => "SELECT COUNT(*) as total FROM seminar",
            'Skripsi' => "SELECT COUNT(*) as total FROM skripsi",
            'Workflow' => "SELECT COUNT(*) as total FROM proposal_workflow"
        ];
        
        echo "<table>";
        echo "<tr><th>Tabel</th><th>Sisa Record</th><th>Status</th></tr>";
        
        $all_clean = true;
        foreach ($verification_queries as $label => $query) {
            $result = $mysqli->query($query);
            $count = $result ? $result->fetch_assoc()['total'] : 0;
            
            $status = $count == 0 ? "✅ BERSIH" : "⚠️ Masih ada {$count}";
            $row_class = $count == 0 ? 'success' : 'warning';
            
            if ($count > 0) $all_clean = false;
            
            echo "<tr class='{$row_class}'>";
            echo "<td><strong>{$label}</strong></td>";
            echo "<td>{$count}</td>";
            echo "<td><strong>{$status}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if ($all_clean) {
            echo "<div class='success'>";
            echo "<h4>🎉 SISTEM SEKARANG BERSIH!</h4>";
            echo "<p>Semua data mahasiswa dan workflow terkait telah berhasil dihapus.</p>";
            echo "<p>Sistem siap untuk testing fresh dari awal dengan proses registrasi mahasiswa baru.</p>";
            echo "</div>";
        } else {
            echo "<div class='warning'>";
            echo "<h4>⚠️ Ada data yang tersisa!</h4>";
            echo "<p>Periksa manual jika diperlukan pembersihan tambahan.</p>";
            echo "</div>";
        }
        
        // Show preserved data
        echo "<h4>🔒 Data yang Dipertahankan:</h4>";
        $preserved_queries = [
            'Dosen' => "SELECT COUNT(*) as total FROM dosen",
            'Program Studi' => "SELECT COUNT(*) as total FROM prodi",
            'Fakultas' => "SELECT COUNT(*) as total FROM fakultas",
            'Notifikasi Non-Mahasiswa' => "SELECT COUNT(*) as total FROM notifikasi WHERE untuk_role != 'mahasiswa'"
        ];
        
        echo "<table>";
        echo "<tr><th>Data</th><th>Jumlah</th><th>Status</th></tr>";
        
        foreach ($preserved_queries as $label => $query) {
            $result = $mysqli->query($query);
            $count = $result ? $result->fetch_assoc()['total'] : 0;
            
            echo "<tr class='success'>";
            echo "<td><strong>{$label}</strong></td>";
            echo "<td>{$count}</td>";
            echo "<td>✅ DIPERTAHANKAN</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    ?>

    <div style="margin-top: 50px; text-align: center; color: #6c757d;">
        <p><small>STK Santo Yakobus Merauke - Full Mahasiswa Database Cleanup v2.0 | <?= date('Y') ?></small></p>
        <p><small>⚠️ Hapus file ini setelah cleanup selesai untuk keamanan!</small></p>
        <p><small>💡 Setelah cleanup, sistem siap untuk testing fresh mulai dari registrasi mahasiswa baru</small></p>
    </div>

</body>
</html>