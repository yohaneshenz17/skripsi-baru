<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jurnal Bimbingan - <?= $proposal->nama_mahasiswa ?></title>
    <style>
        /* ===== PDF-ONLY CLEAN TEMPLATE ===== */
        @page {
            size: A4 landscape;
            margin: 12mm 8mm;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11px;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            color: #000;
        }
        
        /* Header dengan Logo - COMPACT */
        .header {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #2c5aa0;
            padding-bottom: 10px;
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .logo-section {
            display: table-cell;
            width: 80px;
            vertical-align: top;
            text-align: center;
            padding-right: 15px;
        }
        
        .logo-img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        
        .header-text {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        
        .header-title {
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 3px 0;
            color: #2c5aa0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .header-subtitle {
            font-size: 12px;
            font-weight: bold;
            margin: 0 0 2px 0;
            color: #333;
        }
        
        .header-prodi {
            font-size: 10px;
            margin: 0 0 2px 0;
            color: #666;
            font-style: italic;
        }
        
        .header-address {
            font-size: 9px;
            margin: 0;
            color: #666;
        }
        
        /* Info Mahasiswa - TWO COLUMN COMPACT */
        .info-mahasiswa {
            margin-bottom: 12px;
            background-color: #f8f9fa;
            padding: 8px;
            border: 1px solid #dee2e6;
        }
        
        .info-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 6px;
            color: #2c5aa0;
            border-bottom: 1px solid #2c5aa0;
            padding-bottom: 2px;
        }
        
        .info-columns {
            display: table;
            width: 100%;
        }
        
        .info-column-left, .info-column-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .info-column-right {
            padding-left: 15px;
        }
        
        .info-row {
            margin-bottom: 4px;
            display: table;
            width: 100%;
        }
        
        .info-label {
            font-weight: bold;
            display: table-cell;
            width: 120px;
            vertical-align: top;
            color: #333;
            font-size: 9px;
        }
        
        .info-value {
            display: table-cell;
            color: #000;
            font-size: 9px;
        }
        
        /* Tabel Jurnal - OPTIMIZED */
        .jurnal-title {
            font-size: 12px;
            font-weight: bold;
            margin: 15px 0 8px 0;
            color: #2c5aa0;
            text-align: center;
            text-transform: uppercase;
        }
        
        .jurnal-subtitle {
            font-size: 9px;
            text-align: center;
            color: #666;
            margin-bottom: 10px;
            font-style: italic;
        }
        
        .jurnal-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 8px;
        }
        
        .jurnal-table th,
        .jurnal-table td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: left;
            vertical-align: top;
        }
        
        .jurnal-table th {
            background-color: #2c5aa0;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
        }
        
        /* LANDSCAPE COLUMN WIDTHS - OPTIMIZED */
        .no-column {
            width: 3%;
            text-align: center;
        }
        
        .tanggal-column {
            width: 8%;
            text-align: center;
        }
        
        .materi-column {
            width: 45%;
        }
        
        .catatan-column {
            width: 32%;
        }
        
        .status-column {
            width: 12%;
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
            font-size: 7px;
        }
        
        .status-pending {
            color: #ffc107;
            font-weight: bold;
            font-size: 7px;
        }
        
        .status-revisi {
            color: #dc3545;
            font-weight: bold;
            font-size: 7px;
        }
        
        /* Summary Box - COMPACT */
        .summary-box {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 6px;
            margin: 10px 0;
            font-size: 8px;
            line-height: 1.3;
        }
        
        .summary-title {
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 4px;
        }
        
        /* Formal Notice - COMPACT */
        .formal-notice {
            background-color: #e8f4fd;
            border: 1px solid #2c5aa0;
            padding: 6px;
            margin: 10px 0;
            text-align: center;
        }
        
        .formal-notice-title {
            font-weight: bold;
            color: #2c5aa0;
            font-size: 9px;
            margin-bottom: 4px;
        }
        
        .formal-notice-text {
            font-size: 7px;
            color: #555;
            line-height: 1.2;
            font-style: italic;
        }
        
        /* Signature Area - HORIZONTAL COMPACT */
        .signature-area {
            margin-top: 15px;
            page-break-inside: avoid;
        }
        
        .signature-date {
            text-align: right;
            margin-bottom: 10px;
            font-size: 9px;
        }
        
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .signature-table td {
            padding: 8px 5px;
            text-align: center;
            vertical-align: top;
            width: 33.33%;
            font-size: 8px;
        }
        
        .signature-title {
            font-weight: bold;
            margin-bottom: 6px;
            color: #333;
            font-size: 9px;
        }
        
        .signature-name {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 8px;
            min-height: 15px;
        }
        
        .signature-nip {
            font-size: 7px;
            color: #666;
        }
        
        .signature-note {
            font-size: 6px;
            color: #999;
            margin-top: 5px;
            font-style: italic;
        }
        
        /* Footer - COMPACT */
        .footer-info {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            font-size: 7px;
            color: #666;
            text-align: center;
            line-height: 1.2;
        }
        
        .footer-info .generated-info {
            margin-bottom: 4px;
            font-style: italic;
        }
        
        .footer-info .copyright {
            font-weight: bold;
            color: #2c5aa0;
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
                <div class="header-prodi">Program Studi <?= isset($proposal->nama_prodi) ? $proposal->nama_prodi : 'Pendidikan Keagamaan Katolik' ?></div>
                <div class="header-address">
                    Jl. Missi 2, Mandala, Merauke, Papua Selatan | Telp. (0971) 3330264 | Email: sipd@stkyakobus.ac.id
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Mahasiswa - TWO COLUMN LAYOUT -->
    <div class="info-mahasiswa">
        <div class="info-title">📋 Informasi Mahasiswa dan Tugas Akhir</div>
        
        <div class="info-columns">
            <!-- Left Column -->
            <div class="info-column-left">
                <div class="info-row">
                    <div class="info-label">Nama Mahasiswa</div>
                    <div class="info-value">: <strong><?= isset($proposal->nama_mahasiswa) ? $proposal->nama_mahasiswa : 'Mahasiswa Contoh 2' ?></strong></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">NIM</div>
                    <div class="info-value">: <strong><?= isset($proposal->nim) ? $proposal->nim : '12345679' ?></strong></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Program Studi</div>
                    <div class="info-value">: <?= isset($proposal->nama_prodi) ? $proposal->nama_prodi : 'Pendidikan Keagamaan Katolik' ?></div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="info-column-right">
                <div class="info-row">
                    <div class="info-label">Judul Tugas Akhir</div>
                    <div class="info-value">: <em><?= isset($proposal->judul) && !empty($proposal->judul) ? $proposal->judul : 'PENGARUH PENGGUNAAN MEDIA TEKNOLOGI PEMBELAJARAN TERHADAP HASIL BELAJAR SISWA SMPN 2 MERAUKE' ?></em></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Dosen Pembimbing</div>
                    <div class="info-value">: <strong><?= isset($proposal->nama_pembimbing) ? $proposal->nama_pembimbing : 'Yohanes Hendro Pranyoto, S.Pd., M.Pd.' ?></strong></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Status Workflow</div>
                    <div class="info-value">: <?= isset($proposal->workflow_status) ? ucfirst(str_replace('_', ' ', $proposal->workflow_status)) : 'Bimbingan' ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Judul Tabel -->
    <div class="jurnal-title">📚 Riwayat Bimbingan Tugas Akhir</div>
    <div class="jurnal-subtitle">
        Minimal 16 pertemuan bimbingan diperlukan untuk menyelesaikan tugas akhir | 
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
            <?php if(isset($jurnal_bimbingan) && !empty($jurnal_bimbingan)): ?>
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
                
                <!-- Tambahkan baris kosong untuk mencapai 16 total -->
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
                <!-- Jika belum ada jurnal, tampilkan 16 baris kosong -->
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
    <div class="summary-box">
        <div class="summary-title">📊 Ringkasan Bimbingan:</div>
        <div>
            <?php 
            $total_bimbingan = isset($jurnal_bimbingan) ? count($jurnal_bimbingan) : 0;
            $total_valid = 0;
            $total_pending = 0;
            $total_revisi = 0;
            
            if (isset($jurnal_bimbingan)) {
                foreach ($jurnal_bimbingan as $jurnal) {
                    if ($jurnal->status_validasi == '1') $total_valid++;
                    elseif ($jurnal->status_validasi == '2') $total_revisi++;
                    else $total_pending++;
                }
            }
            ?>
            • <strong>Total Pertemuan:</strong> <?= $total_bimbingan ?> dari 16 minimal yang diperlukan |
            • <strong>Status:</strong> <?= $total_valid ?> Valid | <?= $total_pending ?> Pending | <?= $total_revisi ?> Perlu Revisi |
            • <strong>Progress:</strong> <?= $total_bimbingan >= 16 ? '✅ Memenuhi syarat minimal' : '⚠️ Masih perlu ' . (16 - $total_bimbingan) . ' pertemuan lagi' ?>
        </div>
    </div>

    <!-- Keterangan Formal -->
    <div class="formal-notice">
        <div class="formal-notice-title">📋 KETERANGAN RESMI</div>
        <div class="formal-notice-text">
            Dokumen ini digenerate secara otomatis oleh Sistem Informasi Manajemen Tugas Akhir 
            STK Santo Yakobus Merauke. Sesuai kebijakan institusi, dokumen yang digenerate otomatis 
            tidak memerlukan tanda tangan fisik karena telah terverifikasi melalui sistem digital 
            dan dapat dipertanggungjawabkan secara akademik.
        </div>
    </div>

    <!-- Area Tanda Tangan -->
    <div class="signature-area">
        <div class="signature-date">
            Merauke, <?= date('d F Y') ?>
        </div>
        
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-title">Mahasiswa</div>
                    <div class="signature-name"><?= isset($proposal->nama_mahasiswa) ? $proposal->nama_mahasiswa : 'Mahasiswa Contoh 2' ?></div>
                    <div class="signature-nip">NIM. <?= isset($proposal->nim) ? $proposal->nim : '12345679' ?></div>
                    <div class="signature-note">*Terverifikasi sistem digital</div>
                </td>
                <td>
                    <div class="signature-title">Dosen Pembimbing</div>
                    <div class="signature-name"><?= isset($proposal->nama_pembimbing) ? $proposal->nama_pembimbing : 'Yohanes Hendro Pranyoto, S.Pd., M.Pd.' ?></div>
                    <div class="signature-nip">NIDN. <?= isset($proposal->nip_pembimbing) ? $proposal->nip_pembimbing : '2717069001' ?></div>
                    <div class="signature-note">*Terverifikasi sistem digital</div>
                </td>
                <td>
                    <div class="signature-title">Ketua Program Studi</div>
                    <div class="signature-name">
                        <?php if (!empty($proposal->nama_kaprodi)): ?>
                            <?= $proposal->nama_kaprodi ?>
                        <?php else: ?>
                            Dedimus Berangka, S.Pd., M.Pd.
                        <?php endif; ?>
                    </div>
                    <div class="signature-nip">
                        NIDN. <?php if (!empty($proposal->nip_kaprodi)): ?>
                            <?= $proposal->nip_kaprodi ?>
                        <?php else: ?>
                            2721128601
                        <?php endif; ?>
                    </div>
                    <div class="signature-note">*Terverifikasi sistem digital</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer Info -->
    <div class="footer-info">
        <div class="generated-info">
            📄 Dokumen ini digenerate secara otomatis oleh <strong><?= isset($generated_by) ? $generated_by : 'Sistem' ?></strong> pada <strong><?= isset($generated_at) ? $generated_at : date('d F Y H:i:s') ?></strong><br>
            📊 Total Bimbingan: <strong><?= isset($jurnal_bimbingan) ? count($jurnal_bimbingan) : 0 ?> sesi</strong> | 
            📈 Status Workflow: <strong><?= isset($proposal->workflow_status) ? ucfirst(str_replace('_', ' ', $proposal->workflow_status)) : 'Bimbingan' ?></strong><br>
            🎯 Target Minimal: <strong>16 pertemuan bimbingan</strong> untuk menyelesaikan tugas akhir
        </div>
        <div class="copyright">
            © <?= date('Y') ?> Sekolah Tinggi Katolik Santo Yakobus Merauke<br>
            Sistem Informasi Manajemen Tugas Akhir (SIM-TA) | Versi Digital Terverifikasi
        </div>
    </div>
</body>
</html>