<?php
// ========================================
// PDF JURNAL BIMBINGAN TEMPLATE - FIXED KAPRODI & KETERANGAN FORMAL
// File: application/views/staf/bimbingan/pdf_jurnal.php
// FIXED: Data kaprodi otomatis & keterangan formal
// ========================================
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jurnal Bimbingan - <?= $proposal->nama_mahasiswa ?></title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
            color: #333;
        }
        
        /* Header dengan Logo */
        .header {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 3px solid #2c5aa0;
            padding-bottom: 20px;
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .logo-section {
            display: table-cell;
            width: 120px;
            vertical-align: top;
            text-align: center;
            padding-right: 20px;
        }
        
        .logo-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        
        .header-text {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        
        .header-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 5px 0;
            color: #2c5aa0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .header-subtitle {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 3px 0;
            color: #333;
        }
        
        .header-prodi {
            font-size: 14px;
            margin: 0;
            color: #666;
            font-style: italic;
        }
        
        .header-address {
            font-size: 11px;
            margin: 5px 0 0 0;
            color: #666;
            text-align: center;
        }
        
        /* Info Mahasiswa */
        .info-mahasiswa {
            margin-bottom: 25px;
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        
        .info-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c5aa0;
            border-bottom: 1px solid #2c5aa0;
            padding-bottom: 5px;
        }
        
        .info-row {
            margin-bottom: 8px;
            display: table;
            width: 100%;
        }
        
        .info-label {
            font-weight: bold;
            display: table-cell;
            width: 180px;
            vertical-align: top;
            color: #555;
        }
        
        .info-value {
            display: table-cell;
            color: #333;
        }
        
        /* Tabel Jurnal */
        .jurnal-title {
            font-size: 16px;
            font-weight: bold;
            margin: 30px 0 15px 0;
            color: #2c5aa0;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .jurnal-subtitle {
            font-size: 12px;
            text-align: center;
            color: #666;
            margin-bottom: 20px;
            font-style: italic;
        }
        
        .jurnal-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 11px;
        }
        
        .jurnal-table th,
        .jurnal-table td {
            border: 1px solid #333;
            padding: 8px 6px;
            text-align: left;
            vertical-align: top;
        }
        
        .jurnal-table th {
            background-color: #2c5aa0;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 11px;
        }
        
        .no-column {
            width: 4%;
            text-align: center;
        }
        
        .tanggal-column {
            width: 12%;
            text-align: center;
        }
        
        .materi-column {
            width: 40%;
        }
        
        .catatan-column {
            width: 30%;
        }
        
        .status-column {
            width: 14%;
            text-align: center;
        }
        
        /* Row styling */
        .jurnal-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .jurnal-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        
        .status-valid {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        
        .status-revisi {
            color: #dc3545;
            font-weight: bold;
        }
        
        /* ✅ FIXED: Signature Area dengan keterangan formal */
        .signature-area {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .signature-table td {
            padding: 20px;
            text-align: center;
            vertical-align: top;
            width: 33.33%;
        }
        
        .signature-title {
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        
        .signature-name {
            font-weight: bold;
            margin-bottom: 5px;
            padding: 5px;
            min-height: 25px;
        }
        
        .signature-nip {
            font-size: 11px;
            color: #666;
        }
        
        /* ✅ BARU: Style untuk keterangan formal */
        .formal-notice {
            background-color: #e8f4fd;
            border: 2px solid #2c5aa0;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        
        .formal-notice-title {
            font-weight: bold;
            color: #2c5aa0;
            font-size: 13px;
            margin-bottom: 8px;
        }
        
        .formal-notice-text {
            font-size: 11px;
            color: #555;
            line-height: 1.4;
            font-style: italic;
        }
        
        /* Footer */
        .footer-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            color: #666;
            text-align: center;
            line-height: 1.5;
        }
        
        .footer-info .generated-info {
            margin-bottom: 10px;
            font-style: italic;
        }
        
        .footer-info .copyright {
            font-weight: bold;
            color: #2c5aa0;
        }
        
        /* Print specific styles */
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            
            .header {
                margin-bottom: 20px;
            }
            
            .signature-area {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <!-- Header dengan Logo Kampus -->
    <div class="header">
        <div class="header-content">
            <!-- Logo Section -->
            <div class="logo-section">
                <img src="https://stkyakobus.ac.id/skripsi/cdn/img/icons/20250703062346.png" 
                     alt="Logo STK Santo Yakobus" 
                     class="logo-img">
            </div>
            
            <!-- Header Text -->
            <div class="header-text">
                <div class="header-title">Jurnal Bimbingan Tugas Akhir</div>
                <div class="header-subtitle">Sekolah Tinggi Katolik Santo Yakobus Merauke</div>
                <div class="header-prodi">Program Studi <?= $proposal->nama_prodi ?></div>
                <div class="header-address">
                    Jl. Missi 2, Mandala, Merauke, Papua Selatan<br>
                    Telp. (0971) 3330264 | Email: sipd@stkyakobus.ac.id
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Mahasiswa -->
    <div class="info-mahasiswa">
        <div class="info-title">📋 Informasi Mahasiswa dan Tugas Akhir</div>
        
        <div class="info-row">
            <div class="info-label">Nama Mahasiswa</div>
            <div class="info-value">: <strong><?= $proposal->nama_mahasiswa ?></strong></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">NIM</div>
            <div class="info-value">: <strong><?= $proposal->nim ?></strong></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Program Studi</div>
            <div class="info-value">: <?= $proposal->nama_prodi ?></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Judul Tugas Akhir</div>
            <div class="info-value">: <em><?= $proposal->judul ?: 'Belum ditentukan' ?></em></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Dosen Pembimbing</div>
            <div class="info-value">: <strong><?= $proposal->nama_pembimbing ?: 'Belum ditentukan' ?></strong></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Status Workflow</div>
            <div class="info-value">: <?= ucfirst(str_replace('_', ' ', $proposal->workflow_status)) ?></div>
        </div>
    </div>

    <!-- Judul Tabel -->
    <div class="jurnal-title">📚 Riwayat Bimbingan Tugas Akhir</div>
    <div class="jurnal-subtitle">
        Minimal 16 pertemuan bimbingan diperlukan untuk menyelesaikan tugas akhir<br>
        Status: ✓ Valid (Disetujui Dosen) | ⏳ Pending (Menunggu Validasi) | ⚠️ Revisi (Perlu Perbaikan)
    </div>

    <!-- Tabel Jurnal Bimbingan -->
    <table class="jurnal-table">
        <thead>
            <tr>
                <th class="no-column">No</th>
                <th class="tanggal-column">Tanggal</th>
                <th class="materi-column">Materi Bimbingan</th>
                <th class="catatan-column">Catatan Pembimbing</th>
                <th class="status-column">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($jurnal_bimbingan)): ?>
                <?php $no = 1; ?>
                <?php foreach($jurnal_bimbingan as $jurnal): ?>
                    <tr>
                        <td class="no-column"><?= $no++ ?></td>
                        <td class="tanggal-column">
                            <?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?>
                        </td>
                        <td class="materi-column">
                            <?= nl2br(htmlspecialchars($jurnal->materi_bimbingan)) ?>
                            <?php if (!empty($jurnal->catatan_mahasiswa)): ?>
                                <br><em><small>Catatan mahasiswa: <?= htmlspecialchars($jurnal->catatan_mahasiswa) ?></small></em>
                            <?php endif; ?>
                        </td>
                        <td class="catatan-column">
                            <?= nl2br(htmlspecialchars($jurnal->catatan_dosen ?: '-')) ?>
                            <?php if (!empty($jurnal->tindak_lanjut)): ?>
                                <br><strong><small>Tindak lanjut: <?= htmlspecialchars($jurnal->tindak_lanjut) ?></small></strong>
                            <?php endif; ?>
                        </td>
                        <td class="status-column">
                            <?php if($jurnal->status_validasi == '1'): ?>
                                <span class="status-valid">✓ VALID</span><br>
                                <small><?= $jurnal->tanggal_validasi ? date('d/m/Y', strtotime($jurnal->tanggal_validasi)) : '' ?></small>
                            <?php elseif($jurnal->status_validasi == '2'): ?>
                                <span class="status-revisi">⚠️ REVISI</span><br>
                                <small>Perlu perbaikan</small>
                            <?php else: ?>
                                <span class="status-pending">⏳ PENDING</span><br>
                                <small>Menunggu validasi</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                
                <!-- FIXED: Tambahkan baris kosong untuk mencapai 16 total -->
                <?php 
                $sisa_baris = 16 - count($jurnal_bimbingan);
                for($i = 0; $i < $sisa_baris; $i++): 
                ?>
                    <tr>
                        <td class="no-column"><?= $no + $i ?></td>
                        <td class="tanggal-column">___________</td>
                        <td class="materi-column">&nbsp;</td>
                        <td class="catatan-column">&nbsp;</td>
                        <td class="status-column">&nbsp;</td>
                    </tr>
                <?php endfor; ?>
            <?php else: ?>
                <!-- FIXED: Jika belum ada jurnal, tampilkan 16 baris kosong -->
                <?php for($i = 1; $i <= 16; $i++): ?>
                    <tr>
                        <td class="no-column"><?= $i ?></td>
                        <td class="tanggal-column">___________</td>
                        <td class="materi-column">&nbsp;</td>
                        <td class="catatan-column">&nbsp;</td>
                        <td class="status-column">&nbsp;</td>
                    </tr>
                <?php endfor; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Summary Box -->
    <div style="background-color: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; margin: 20px 0; border-radius: 5px;">
        <div style="font-weight: bold; color: #2c5aa0; margin-bottom: 10px;">📊 Ringkasan Bimbingan:</div>
        <div style="font-size: 11px; line-height: 1.6;">
            <?php 
            $total_bimbingan = count($jurnal_bimbingan);
            $total_valid = 0;
            $total_pending = 0;
            $total_revisi = 0;
            
            foreach ($jurnal_bimbingan as $jurnal) {
                if ($jurnal->status_validasi == '1') $total_valid++;
                elseif ($jurnal->status_validasi == '2') $total_revisi++;
                else $total_pending++;
            }
            ?>
            • <strong>Total Pertemuan:</strong> <?= $total_bimbingan ?> dari 16 minimal yang diperlukan<br>
            • <strong>Status:</strong> <?= $total_valid ?> Valid | <?= $total_pending ?> Pending | <?= $total_revisi ?> Perlu Revisi<br>
            • <strong>Progress:</strong> <?= $total_bimbingan >= 16 ? '✅ Memenuhi syarat minimal' : '⚠️ Masih perlu ' . (16 - $total_bimbingan) . ' pertemuan lagi' ?>
        </div>
    </div>

    <!-- ✅ KETERANGAN FORMAL - BARU -->
    <div class="formal-notice">
        <div class="formal-notice-title">📋 KETERANGAN RESMI</div>
        <div class="formal-notice-text">
            Dokumen ini digenerate secara otomatis oleh Sistem Informasi Manajemen Tugas Akhir 
            STK Santo Yakobus Merauke. Sesuai kebijakan institusi, dokumen yang digenerate otomatis 
            tidak memerlukan tanda tangan fisik karena telah terverifikasi melalui sistem digital 
            dan dapat dipertanggungjawabkan secara akademik.
        </div>
    </div>

    <!-- ✅ FIXED: Area Tanda Tangan dengan Data Kaprodi -->
    <div class="signature-area">
        <div style="text-align: right; margin-bottom: 20px;">
            Merauke, <?= date('d F Y') ?>
        </div>
        
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-title">Mahasiswa</div>
                    <div class="signature-name"><?= $proposal->nama_mahasiswa ?></div>
                    <div class="signature-nip">NIM. <?= $proposal->nim ?></div>
                    <div style="font-size: 10px; color: #999; margin-top: 10px; font-style: italic;">
                        *Terverifikasi sistem digital
                    </div>
                </td>
                <td>
                    <div class="signature-title">Dosen Pembimbing</div>
                    <div class="signature-name"><?= $proposal->nama_pembimbing ?: 'Belum ditetapkan' ?></div>
                    <div class="signature-nip">NIDN. <?= isset($proposal->nip_pembimbing) ? $proposal->nip_pembimbing : 'Belum ditetapkan' ?></div>
                    <div style="font-size: 10px; color: #999; margin-top: 10px; font-style: italic;">
                        *Terverifikasi sistem digital
                    </div>
                </td>
                <td>
                    <div class="signature-title">Ketua Program Studi</div>
                    <!-- ✅ FIXED: Tampilkan data kaprodi dari database -->
                    <div class="signature-name">
                        <?php if (!empty($proposal->nama_kaprodi)): ?>
                            <?= $proposal->nama_kaprodi ?>
                        <?php else: ?>
                            Belum ditetapkan
                        <?php endif; ?>
                    </div>
                    <div class="signature-nip">
                        NIDN. <?php if (!empty($proposal->nip_kaprodi)): ?>
                            <?= $proposal->nip_kaprodi ?>
                        <?php else: ?>
                            Belum ditetapkan
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 10px; color: #999; margin-top: 10px; font-style: italic;">
                        *Terverifikasi sistem digital
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer Info -->
    <div class="footer-info">
        <div class="generated-info">
            📄 Dokumen ini digenerate secara otomatis oleh <strong><?= $generated_by ?></strong> pada <strong><?= $generated_at ?></strong><br>
            📊 Total Bimbingan: <strong><?= count($jurnal_bimbingan) ?> sesi</strong> | 
            📈 Status Workflow: <strong><?= ucfirst(str_replace('_', ' ', $proposal->workflow_status)) ?></strong><br>
            🎯 Target Minimal: <strong>16 pertemuan bimbingan</strong> untuk menyelesaikan tugas akhir
        </div>
        <div class="copyright">
            © <?= date('Y') ?> Sekolah Tinggi Katolik Santo Yakobus Merauke<br>
            Sistem Informasi Manajemen Tugas Akhir (SIM-TA) | Versi Digital Terverifikasi
        </div>
    </div>
</body>
</html>