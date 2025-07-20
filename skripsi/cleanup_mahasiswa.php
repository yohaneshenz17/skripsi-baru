<?php
/**
 * CLEANUP MAHASISWA DATABASE - STK St. Yakobus
 * 
 * Upload ke: /public_html/skripsi/cleanup_mahasiswa.php
 * Akses: https://stkyakobus.ac.id/skripsi/cleanup_mahasiswa.php
 * 
 * TUJUAN: Hapus semua data mahasiswa kecuali:
 * 1. "Hendro Mahasiswa" (ID: 32)
 * 2. "Herybertus Oktaviani" (ID: 33)
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
    <title>Cleanup Mahasiswa Database - STK St. Yakobus</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
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
        .keep { background: #d4edda; }
        .delete { background: #f8d7da; }
        .progress { width: 100%; height: 25px; background: #e9ecef; border-radius: 12px; overflow: hidden; margin: 15px 0; }
        .progress-bar { height: 100%; background: linear-gradient(45deg, #28a745, #20c997); transition: width 0.3s; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🗑️ Cleanup Mahasiswa Database</h1>
        <p>STK Santo Yakobus Merauke</p>
        <p><strong>OPERASI:</strong> Hapus semua data mahasiswa kecuali 2 yang ditentukan</p>
        <p><strong>WAKTU:</strong> <?= date('d F Y, H:i:s') ?> WIT</p>
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
    
    // Mahasiswa yang akan DIPERTAHANKAN
    $keep_mahasiswa = [
        'Hendro Mahasiswa',
        'Herybertus Oktaviani'
    ];
    
    if ($action) {
        handleAction($action, $mysqli, $keep_mahasiswa);
    } else {
        showMainPage($mysqli, $keep_mahasiswa);
    }
    
    $mysqli->close();
    
    function showMainPage($mysqli, $keep_mahasiswa) {
        echo "<div class='section'>";
        echo "<h2>🎯 Analisis Data Mahasiswa</h2>";
        
        echo "<div class='warning'>";
        echo "<h3>⚠️ OPERASI BERBAHAYA!</h3>";
        echo "<p>Script ini akan <strong>MENGHAPUS PERMANEN</strong> semua data mahasiswa dan data terkait, kecuali:</p>";
        echo "<ul>";
        foreach ($keep_mahasiswa as $nama) {
            echo "<li><strong>{$nama}</strong></li>";
        }
        echo "</ul>";
        echo "<p><strong>Data yang akan dihapus meliputi:</strong> profil, proposal, bimbingan, penelitian, seminar, publikasi, dll.</p>";
        echo "</div>";
        
        // Show current data
        showCurrentData($mysqli, $keep_mahasiswa);
        
        // Show affected tables
        showAffectedTables($mysqli);
        
        // Show action buttons
        echo "<h3>🛠️ Pilihan Aksi:</h3>";
        echo "<div style='text-align: center; margin: 20px 0;'>";
        echo "<a href='?action=analyze' class='btn btn-warning'>Analisis Detail</a>";
        echo "<a href='?action=backup' class='btn btn-success'>Backup Dulu</a>";
        echo "<a href='?action=cleanup' class='btn btn-danger' onclick='return confirm(\"YAKIN INGIN MENGHAPUS DATA? Operasi ini TIDAK BISA DIBATALKAN!\")'>CLEANUP SEKARANG</a>";
        echo "</div>";
        
        echo "</div>";
    }
    
    function showCurrentData($mysqli, $keep_mahasiswa) {
        echo "<h3>📊 Data Mahasiswa Saat Ini:</h3>";
        
        $result = $mysqli->query("SELECT id, nim, nama, email, prodi_id, status FROM mahasiswa ORDER BY id");
        
        if ($result && $result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>NIM</th><th>Nama</th><th>Email</th><th>Prodi ID</th><th>Status</th><th>Aksi</th></tr>";
            
            $total = 0;
            $will_delete = 0;
            $will_keep = 0;
            
            while ($row = $result->fetch_assoc()) {
                $total++;
                $is_keep = in_array($row['nama'], $keep_mahasiswa);
                $row_class = $is_keep ? 'keep' : 'delete';
                $action_text = $is_keep ? '✅ PERTAHANKAN' : '❌ HAPUS';
                
                if ($is_keep) {
                    $will_keep++;
                } else {
                    $will_delete++;
                }
                
                echo "<tr class='{$row_class}'>";
                echo "<td>{$row['id']}</td>";
                echo "<td>" . htmlspecialchars($row['nim']) . "</td>";
                echo "<td><strong>" . htmlspecialchars($row['nama']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>{$row['prodi_id']}</td>";
                echo "<td>" . ($row['status'] == '1' ? 'Aktif' : 'Non-Aktif') . "</td>";
                echo "<td><strong>{$action_text}</strong></td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<div class='info'>";
            echo "<h4>📈 Ringkasan:</h4>";
            echo "<ul>";
            echo "<li><strong>Total Mahasiswa:</strong> {$total}</li>";
            echo "<li><strong>Akan Dipertahankan:</strong> <span style='color: green;'>{$will_keep}</span></li>";
            echo "<li><strong>Akan Dihapus:</strong> <span style='color: red;'>{$will_delete}</span></li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<div class='warning'>⚠️ Tidak ada data mahasiswa ditemukan.</div>";
        }
    }
    
    function showAffectedTables($mysqli) {
        echo "<h3>🗄️ Tabel yang Akan Terpengaruh:</h3>";
        
        $tables_to_check = [
            'mahasiswa' => 'Data profil mahasiswa',
            'proposal_mahasiswa' => 'Proposal tugas akhir',
            'konsultasi' => 'Data konsultasi/bimbingan',
            'penelitian' => 'Data penelitian',
            'hasil_kegiatan' => 'Hasil kegiatan mahasiswa',
            'proposal_workflow' => 'Workflow proposal',
            'jurnal_bimbingan' => 'Jurnal bimbingan',
            'notifikasi' => 'Notifikasi sistem'
        ];
        
        echo "<table>";
        echo "<tr><th>Tabel</th><th>Deskripsi</th><th>Jumlah Data</th></tr>";
        
        foreach ($tables_to_check as $table => $desc) {
            $count_query = "SELECT COUNT(*) as total FROM {$table}";
            if ($table != 'mahasiswa') {
                // Untuk tabel lain, cek yang terkait mahasiswa_id
                $count_query = "SELECT COUNT(*) as total FROM {$table} WHERE mahasiswa_id IS NOT NULL";
            }
            
            $result = $mysqli->query($count_query);
            $count = $result ? $result->fetch_assoc()['total'] : 0;
            
            echo "<tr>";
            echo "<td><code>{$table}</code></td>";
            echo "<td>{$desc}</td>";
            echo "<td>{$count}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    function handleAction($action, $mysqli, $keep_mahasiswa) {
        switch ($action) {
            case 'analyze':
                analyzeDetailData($mysqli, $keep_mahasiswa);
                break;
            case 'backup':
                backupData($mysqli, $keep_mahasiswa);
                break;
            case 'cleanup':
                performCleanup($mysqli, $keep_mahasiswa);
                break;
            case 'confirm_cleanup':
                confirmCleanup($mysqli, $keep_mahasiswa);
                break;
            default:
                echo "<div class='error'>Unknown action: {$action}</div>";
        }
    }
    
    function analyzeDetailData($mysqli, $keep_mahasiswa) {
        echo "<div class='section'>";
        echo "<h2>🔍 Analisis Detail Data</h2>";
        
        // Get mahasiswa IDs that will be deleted
        $keep_ids = [];
        $delete_ids = [];
        
        $result = $mysqli->query("SELECT id, nama FROM mahasiswa");
        while ($row = $result->fetch_assoc()) {
            if (in_array($row['nama'], $keep_mahasiswa)) {
                $keep_ids[] = $row['id'];
            } else {
                $delete_ids[] = $row['id'];
            }
        }
        
        echo "<div class='info'>";
        echo "<h3>🎯 Mahasiswa yang Akan Dihapus:</h3>";
        echo "<p><strong>ID:</strong> " . implode(', ', $delete_ids) . "</p>";
        echo "</div>";
        
        echo "<div class='success'>";
        echo "<h3>✅ Mahasiswa yang Akan Dipertahankan:</h3>";
        echo "<p><strong>ID:</strong> " . implode(', ', $keep_ids) . "</p>";
        echo "</div>";
        
        // Analyze related data
        if (!empty($delete_ids)) {
            $delete_ids_str = implode(',', $delete_ids);
            
            $related_data = [
                'proposal_mahasiswa' => "SELECT COUNT(*) as total FROM proposal_mahasiswa WHERE mahasiswa_id IN ({$delete_ids_str})",
                'konsultasi' => "SELECT COUNT(*) as total FROM konsultasi WHERE proposal_mahasiswa_id IN (SELECT id FROM proposal_mahasiswa WHERE mahasiswa_id IN ({$delete_ids_str}))",
                'penelitian' => "SELECT COUNT(*) as total FROM penelitian WHERE proposal_mahasiswa_id IN (SELECT id FROM proposal_mahasiswa WHERE mahasiswa_id IN ({$delete_ids_str}))",
                'hasil_kegiatan' => "SELECT COUNT(*) as total FROM hasil_kegiatan WHERE mahasiswa_id IN ({$delete_ids_str})",
                'proposal_workflow' => "SELECT COUNT(*) as total FROM proposal_workflow WHERE proposal_id IN (SELECT id FROM proposal_mahasiswa WHERE mahasiswa_id IN ({$delete_ids_str}))"
            ];
            
            echo "<h3>📈 Data Terkait yang Akan Dihapus:</h3>";
            echo "<table>";
            echo "<tr><th>Tabel</th><th>Jumlah Record</th></tr>";
            
            $total_records = 0;
            foreach ($related_data as $table => $query) {
                $result = $mysqli->query($query);
                $count = $result ? $result->fetch_assoc()['total'] : 0;
                $total_records += $count;
                
                echo "<tr>";
                echo "<td><code>{$table}</code></td>";
                echo "<td>{$count}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<div class='warning'>";
            echo "<h4>⚠️ Total Record yang Akan Dihapus: <strong>{$total_records}</strong></h4>";
            echo "</div>";
        }
        
        echo "<div style='text-align: center; margin: 20px 0;'>";
        echo "<a href='?action=backup' class='btn btn-success'>Lanjut ke Backup</a>";
        echo "<a href='?' class='btn'>Kembali</a>";
        echo "</div>";
        
        echo "</div>";
    }
    
    function backupData($mysqli, $keep_mahasiswa) {
        echo "<div class='section'>";
        echo "<h2>📦 Backup Data Sebelum Hapus</h2>";
        
        if (isset($_GET['do_backup'])) {
            performBackup($mysqli, $keep_mahasiswa);
        } else {
            echo "<div class='info'>";
            echo "<h3>📋 Backup akan mencakup:</h3>";
            echo "<ul>";
            echo "<li>Semua data mahasiswa yang akan dihapus</li>";
            echo "<li>Semua proposal terkait</li>";
            echo "<li>Semua data konsultasi/bimbingan</li>";
            echo "<li>Semua data penelitian</li>";
            echo "<li>Semua data workflow</li>";
            echo "</ul>";
            echo "</div>";
            
            echo "<div class='warning'>";
            echo "<h3>⚠️ PENTING:</h3>";
            echo "<p>Backup akan dibuat dalam bentuk tabel baru dengan suffix <code>_backup_" . date('Ymd_His') . "</code></p>";
            echo "<p>Ini memungkinkan recovery data jika diperlukan.</p>";
            echo "</div>";
            
            echo "<div style='text-align: center; margin: 20px 0;'>";
            echo "<a href='?action=backup&do_backup=1' class='btn btn-success'>Mulai Backup</a>";
            echo "<a href='?' class='btn'>Batal</a>";
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    function performBackup($mysqli, $keep_mahasiswa) {
        echo "<h3>🔄 Melakukan Backup...</h3>";
        
        $backup_suffix = '_backup_' . date('Ymd_His');
        $backup_success = 0;
        $backup_total = 0;
        
        // Get IDs to delete
        $delete_ids = [];
        $result = $mysqli->query("SELECT id, nama FROM mahasiswa");
        while ($row = $result->fetch_assoc()) {
            if (!in_array($row['nama'], $keep_mahasiswa)) {
                $delete_ids[] = $row['id'];
            }
        }
        
        if (empty($delete_ids)) {
            echo "<div class='warning'>⚠️ Tidak ada data yang perlu di-backup.</div>";
            return;
        }
        
        $delete_ids_str = implode(',', $delete_ids);
        
        // Backup tables
        $backup_tables = [
            'mahasiswa' => "CREATE TABLE mahasiswa{$backup_suffix} AS SELECT * FROM mahasiswa WHERE id IN ({$delete_ids_str})",
            'proposal_mahasiswa' => "CREATE TABLE proposal_mahasiswa{$backup_suffix} AS SELECT * FROM proposal_mahasiswa WHERE mahasiswa_id IN ({$delete_ids_str})",
            'konsultasi' => "CREATE TABLE konsultasi{$backup_suffix} AS SELECT * FROM konsultasi WHERE proposal_mahasiswa_id IN (SELECT id FROM proposal_mahasiswa WHERE mahasiswa_id IN ({$delete_ids_str}))",
            'penelitian' => "CREATE TABLE penelitian{$backup_suffix} AS SELECT * FROM penelitian WHERE proposal_mahasiswa_id IN (SELECT id FROM proposal_mahasiswa WHERE mahasiswa_id IN ({$delete_ids_str}))",
            'hasil_kegiatan' => "CREATE TABLE hasil_kegiatan{$backup_suffix} AS SELECT * FROM hasil_kegiatan WHERE mahasiswa_id IN ({$delete_ids_str})"
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
        }
        
        if ($backup_success == $backup_total) {
            echo "<div class='success'>";
            echo "<h3>✅ Backup Lengkap Berhasil!</h3>";
            echo "<p>Semua data telah di-backup dengan suffix: <code>{$backup_suffix}</code></p>";
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
    
    function performCleanup($mysqli, $keep_mahasiswa) {
        echo "<div class='section'>";
        echo "<h2>🗑️ Melakukan Cleanup Data</h2>";
        
        if (isset($_GET['confirm']) && $_GET['confirm'] == 'final') {
            executeCleanup($mysqli, $keep_mahasiswa);
        } else {
            echo "<div class='error'>";
            echo "<h3>⚠️ KONFIRMASI TERAKHIR!</h3>";
            echo "<p>Anda akan <strong>MENGHAPUS PERMANEN</strong> semua data mahasiswa kecuali:</p>";
            echo "<ul>";
            foreach ($keep_mahasiswa as $nama) {
                echo "<li><strong>{$nama}</strong></li>";
            }
            echo "</ul>";
            echo "<p><strong style='color: red;'>OPERASI INI TIDAK BISA DIBATALKAN!</strong></p>";
            echo "</div>";
            
            echo "<div style='text-align: center; margin: 20px 0;'>";
            echo "<a href='?action=cleanup&confirm=final' class='btn btn-danger' onclick='return confirm(\"TERAKHIR KALI: Yakin ingin hapus data?\")'>YA, HAPUS SEKARANG!</a>";
            echo "<a href='?' class='btn btn-success'>BATAL</a>";
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    function executeCleanup($mysqli, $keep_mahasiswa) {
        echo "<h3>🔄 Executing Cleanup...</h3>";
        
        // Progress tracking
        $progress = 0;
        $total_steps = 6;
        
        // Get IDs to delete
        $delete_ids = [];
        $result = $mysqli->query("SELECT id, nama FROM mahasiswa");
        while ($row = $result->fetch_assoc()) {
            if (!in_array($row['nama'], $keep_mahasiswa)) {
                $delete_ids[] = $row['id'];
            }
        }
        
        if (empty($delete_ids)) {
            echo "<div class='warning'>⚠️ Tidak ada data yang perlu dihapus.</div>";
            return;
        }
        
        $delete_ids_str = implode(',', $delete_ids);
        $success_count = 0;
        
        // Start transaction
        $mysqli->autocommit(FALSE);
        
        try {
            // Step 1: Delete jurnal_bimbingan
            $progress++;
            showProgress($progress, $total_steps, "Hapus jurnal bimbingan...");
            $mysqli->query("DELETE FROM jurnal_bimbingan WHERE proposal_id IN (SELECT id FROM proposal_mahasiswa WHERE mahasiswa_id IN ({$delete_ids_str}))");
            $success_count++;
            
            // Step 2: Delete konsultasi
            $progress++;
            showProgress($progress, $total_steps, "Hapus data konsultasi...");
            $mysqli->query("DELETE FROM konsultasi WHERE proposal_mahasiswa_id IN (SELECT id FROM proposal_mahasiswa WHERE mahasiswa_id IN ({$delete_ids_str}))");
            $success_count++;
            
            // Step 3: Delete penelitian
            $progress++;
            showProgress($progress, $total_steps, "Hapus data penelitian...");
            $mysqli->query("DELETE FROM penelitian WHERE proposal_mahasiswa_id IN (SELECT id FROM proposal_mahasiswa WHERE mahasiswa_id IN ({$delete_ids_str}))");
            $success_count++;
            
            // Step 4: Delete proposal_workflow
            $progress++;
            showProgress($progress, $total_steps, "Hapus workflow proposal...");
            $mysqli->query("DELETE FROM proposal_workflow WHERE proposal_id IN (SELECT id FROM proposal_mahasiswa WHERE mahasiswa_id IN ({$delete_ids_str}))");
            $success_count++;
            
            // Step 5: Delete proposal_mahasiswa
            $progress++;
            showProgress($progress, $total_steps, "Hapus proposal mahasiswa...");
            $mysqli->query("DELETE FROM proposal_mahasiswa WHERE mahasiswa_id IN ({$delete_ids_str})");
            $success_count++;
            
            // Step 6: Delete mahasiswa
            $progress++;
            showProgress($progress, $total_steps, "Hapus data mahasiswa...");
            $result = $mysqli->query("DELETE FROM mahasiswa WHERE id IN ({$delete_ids_str})");
            if ($result) {
                $deleted_count = $mysqli->affected_rows;
                $success_count++;
            }
            
            // Commit transaction
            $mysqli->commit();
            
            echo "<div class='success'>";
            echo "<h3>🎉 CLEANUP BERHASIL!</h3>";
            echo "<p><strong>Data mahasiswa yang dihapus:</strong> {$deleted_count}</p>";
            echo "<p><strong>Steps berhasil:</strong> {$success_count}/{$total_steps}</p>";
            echo "</div>";
            
            // Show remaining data
            showRemainingData($mysqli);
            
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
    
    function showRemainingData($mysqli) {
        echo "<h3>✅ Data Mahasiswa yang Tersisa:</h3>";
        
        $result = $mysqli->query("SELECT id, nim, nama, email FROM mahasiswa ORDER BY id");
        
        if ($result && $result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>NIM</th><th>Nama</th><th>Email</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr class='keep'>";
                echo "<td>{$row['id']}</td>";
                echo "<td>" . htmlspecialchars($row['nim']) . "</td>";
                echo "<td><strong>" . htmlspecialchars($row['nama']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<div class='success'>";
            echo "<h4>🎯 Total mahasiswa tersisa: " . $result->num_rows . "</h4>";
            echo "</div>";
        } else {
            echo "<div class='error'>❌ Tidak ada data mahasiswa tersisa!</div>";
        }
    }
    ?>

    <div style="margin-top: 50px; text-align: center; color: #6c757d;">
        <p><small>STK Santo Yakobus Merauke - Mahasiswa Database Cleanup v1.0 | <?= date('Y') ?></small></p>
        <p><small>⚠️ Hapus file ini setelah cleanup selesai untuk keamanan!</small></p>
    </div>

</body>
</html>