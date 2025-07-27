<?php
// ========================================
// PDF JURNAL BIMBINGAN TEMPLATE - MAHASISWA
// File: application/views/mahasiswa/pdf/jurnal_bimbingan.php
// ========================================
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jurnal Bimbingan - <?= $mahasiswa->nama ?></title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
            color: #333;
        }
        
        /* Header dengan Logo */
        .header {
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 3px solid #2c5aa0;
            padding-bottom: 15px;
        }
        
        .header-content {
            text-align: center;
        }
        
        .header-title {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 5px 0;
            color: #2c5aa0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .header-subtitle {
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 3px 0;
            color: #333;
        }
        
        .header-address {
            font-size: 10px;
            margin: 0;
            color: #666;
            font-style: italic;
        }
        
        /* Info Mahasiswa */
        .info-section {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        
        .info-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .info-row {
            margin-bottom: 6px;
            display: table;
            width: 100%;
        }
        
        .info-label {
            display: table-cell;
            width: 150px;
            font-weight: bold;
            color: #555;
            vertical-align: top;
        }
        
        .info-value {
            display: table-cell;
            color: #333;
            vertical-align: top;
        }
        
        .info-colon {
            display: table-cell;
            width: 10px;
            text-align: center;
            vertical-align: top;
        }
        
        /* Tabel Jurnal */
        .table-jurnal {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        
        .table-jurnal th {
            background-color: #2c5aa0;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 8px 4px;
            border: 1px solid #1a365d;
        }
        
        .table-jurnal td {
            border: 1px solid #ddd;
            padding: 6px 4px;
            vertical-align: top;
            text-align: left;
        }
        
        .table-jurnal .text-center {
            text-align: center;
        }
        
        .table-jurnal .no-col {
            width: 25px;
            text-align: center;
        }
        
        .table-jurnal .pertemuan-col {
            width: 35px;
            text-align: center;
        }
        
        .table-jurnal .tanggal-col {
            width: 70px;
            text-align: center;
        }
        
        .table-jurnal .materi-col {
            width: 200px;
        }
        
        .table-jurnal .tindak-lanjut-col {
            width: 150px;
        }
        
        .table-jurnal .status-col {
            width: 60px;
            text-align: center;
        }
        
        .table-jurnal .validasi-col {
            width: 70px;
            text-align: center;
        }
        
        /* Status badges */
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }
        
        .status-pending {
            background-color: #6c757d;
        }
        
        .status-approved {
            background-color: #28a745;
        }
        
        .status-revision {
            background-color: #ffc107;
            color: #333;
        }
        
        /* Summary section */
        .summary-section {
            margin-top: 20px;
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #b6d7ff;
        }
        
        .summary-title {
            font-size: 12px;
            font-weight: bold;
            color: #0c5460;
            margin-bottom: 10px;
        }
        
        .summary-item {
            margin-bottom: 5px;
            font-size: 10px;
        }
        
        .summary-label {
            font-weight: bold;
            color: #0c5460;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .signature-section {
            display: table;
            width: 100%;
        }
        
        .signature-left {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        
        .signature-right {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        
        .signature-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 50px;
        }
        
        .signature-name {
            font-size: 11px;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .signature-detail {
            font-size: 10px;
            margin-top: 3px;
        }
        
        /* Page break */
        .page-break {
            page-break-after: always;
        }
        
        /* Empty row styling */
        .empty-row {
            background-color: #f8f9fa;
        }
        
        .empty-row td {
            color: #6c757d;
            font-style: italic;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="header-title">Jurnal Bimbingan Tugas Akhir</div>
            <div class="header-subtitle">Sekolah Tinggi Katolik Santo Yakobus Merauke</div>
            <div class="header-address">Jl. Missi 2, Mandala, Merauke, Papua Selatan | Telp: 09713330264</div>
        </div>
    </div>

    <!-- Info Mahasiswa -->
    <div class="info-section">
        <div class="info-title">📋 INFORMASI MAHASISWA</div>
        
        <div class="info-row">
            <div class="info-label">Nama Mahasiswa</div>
            <div class="info-colon">:</div>
            <div class="info-value"><?= $mahasiswa->nama ?></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">NIM</div>
            <div class="info-colon">:</div>
            <div class="info-value"><?= $mahasiswa->nim ?></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Program Studi</div>
            <div class="info-colon">:</div>
            <div class="info-value"><?= $mahasiswa->nama_prodi ?></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Judul Tugas Akhir</div>
            <div class="info-colon">:</div>
            <div class="info-value"><?= $proposal->judul ?></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Dosen Pembimbing</div>
            <div class="info-colon">:</div>
            <div class="info-value"><?= $proposal->nama_dosen ?> (NIP: <?= $proposal->nip_dosen ?>)</div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Tanggal Export</div>
            <div class="info-colon">:</div>
            <div class="info-value"><?= $tanggal_export ?></div>
        </div>
    </div>

    <!-- Tabel Jurnal Bimbingan -->
    <table class="table-jurnal">
        <thead>
            <tr>
                <th class="no-col">No</th>
                <th class="pertemuan-col">Pert.</th>
                <th class="tanggal-col">Tanggal</th>
                <th class="materi-col">Materi Bimbingan</th>
                <th class="tindak-lanjut-col">Tindak Lanjut</th>
                <th class="status-col">Status</th>
                <th class="validasi-col">Validasi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jurnal_list as $index => $jurnal): ?>
            <tr>
                <td class="text-center"><?= $index + 1 ?></td>
                <td class="text-center"><?= $jurnal->pertemuan_ke ?></td>
                <td class="text-center"><?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?></td>
                <td><?= htmlspecialchars($jurnal->materi_bimbingan) ?></td>
                <td><?= htmlspecialchars($jurnal->tindak_lanjut ?: '-') ?></td>
                <td class="text-center">
                    <?php if ($jurnal->status_validasi == '1'): ?>
                        <span class="status-badge status-approved">VALID</span>
                    <?php elseif ($jurnal->status_validasi == '2'): ?>
                        <span class="status-badge status-revision">REVISI</span>
                    <?php else: ?>
                        <span class="status-badge status-pending">PENDING</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?= $jurnal->tanggal_validasi ? date('d/m/Y', strtotime($jurnal->tanggal_validasi)) : '-' ?>
                </td>
            </tr>
            
            <!-- Tambah catatan dosen jika ada -->
            <?php if (!empty($jurnal->catatan_dosen)): ?>
            <tr>
                <td colspan="7" style="background-color: #fff3cd; padding: 5px 8px; font-size: 9px;">
                    <strong>Catatan Dosen:</strong> <?= htmlspecialchars($jurnal->catatan_dosen) ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
            
            <!-- Fill empty rows untuk standar 16 bimbingan -->
            <?php for ($i = count($jurnal_list); $i < 16; $i++): ?>
            <tr class="empty-row">
                <td class="text-center"><?= $i + 1 ?></td>
                <td class="text-center"><?= $i + 1 ?></td>
                <td class="text-center">-</td>
                <td>Belum ada bimbingan</td>
                <td>-</td>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-title">📊 RINGKASAN BIMBINGAN</div>
        
        <div class="summary-item">
            <span class="summary-label">Total Pertemuan:</span> <?= $total_bimbingan ?> dari 16 target
        </div>
        
        <div class="summary-item">
            <span class="summary-label">Sudah Tervalidasi:</span> <?= $bimbingan_tervalidasi ?> pertemuan
        </div>
        
        <div class="summary-item">
            <span class="summary-label">Progress Bimbingan:</span> <?= round(($total_bimbingan/16)*100, 1) ?>%
        </div>
        
        <div class="summary-item">
            <span class="summary-label">Status Kelayakan Seminar:</span> 
            <?php if ($bimbingan_tervalidasi >= 8): ?>
                <strong style="color: #28a745;">SIAP SEMINAR PROPOSAL</strong>
            <?php else: ?>
                <strong style="color: #dc3545;">BELUM SIAP (Kurang <?= 8 - $bimbingan_tervalidasi ?> bimbingan)</strong>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer dengan Tanda Tangan -->
    <div class="footer">
        <div class="signature-section">
            <div class="signature-left">
                <div class="signature-title">Mahasiswa</div>
                <div class="signature-name"><?= $mahasiswa->nama ?></div>
                <div class="signature-detail">NIM: <?= $mahasiswa->nim ?></div>
            </div>
            <div class="signature-right">
                <div class="signature-title">Dosen Pembimbing</div>
                <div class="signature-name"><?= $proposal->nama_dosen ?></div>
                <div class="signature-detail">NIP: <?= $proposal->nip_dosen ?></div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px; font-size: 9px; color: #666;">
            <i>Dokumen ini digenerate secara otomatis oleh Sistem Informasi Manajemen Tugas Akhir STK Santo Yakobus Merauke pada <?= date('d F Y H:i:s') ?></i>
        </div>
    </div>
</body>
</html>