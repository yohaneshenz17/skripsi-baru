<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Jurnal Bimbingan - <?= $proposal->nama_mahasiswa ?></title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #000;
        }
        
        .header h1 {
            margin: 0;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 14pt;
            font-weight: normal;
        }
        
        .header .address {
            font-size: 10pt;
            margin-top: 10px;
            line-height: 1.2;
        }
        
        .document-title {
            text-align: center;
            margin: 30px 0;
        }
        
        .document-title h3 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .student-info {
            margin-bottom: 25px;
        }
        
        .student-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .student-info td {
            padding: 4px 0;
            vertical-align: top;
        }
        
        .student-info .label {
            width: 150px;
            font-weight: bold;
        }
        
        .student-info .colon {
            width: 20px;
            text-align: center;
        }
        
        .jurnal-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 11pt;
        }
        
        .jurnal-table th,
        .jurnal-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        .jurnal-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .jurnal-table .no-col {
            width: 8%;
            text-align: center;
        }
        
        .jurnal-table .date-col {
            width: 12%;
        }
        
        .jurnal-table .meeting-col {
            width: 8%;
            text-align: center;
        }
        
        .jurnal-table .material-col {
            width: 35%;
        }
        
        .jurnal-table .notes-col {
            width: 37%;
        }
        
        .jurnal-table .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-validated {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-revision {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .summary {
            margin: 30px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        
        .summary table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary td {
            padding: 5px 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .summary .summary-label {
            width: 200px;
            font-weight: bold;
        }
        
        .signatures {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        
        .signature-row {
            display: table;
            width: 100%;
            margin-top: 30px;
        }
        
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }
        
        .signature-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .signature-date {
            margin-bottom: 60px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 60px;
            margin-bottom: 5px;
        }
        
        .signature-name {
            font-weight: bold;
        }
        
        .signature-position {
            font-size: 10pt;
            color: #666;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    
    <!-- Header Kop Perguruan Tinggi -->
    <div class="header">
        <h1>Sekolah Tinggi Katolik Santo Yakobus Merauke</h1>
        <h2>Sistem Informasi Manajemen Tugas Akhir</h2>
        <div class="address">
            Jl. Missi 2, Mandala, Merauke, Papua Selatan<br>
            Telp: (0971) 333 0264 | Email: sipd@stkyakobus.ac.id<br>
            Website: https://www.stkyakobus.ac.id
        </div>
    </div>
    
    <!-- Judul Dokumen -->
    <div class="document-title">
        <h3>JURNAL BIMBINGAN TUGAS AKHIR</h3>
    </div>
    
    <!-- Informasi Mahasiswa -->
    <div class="student-info">
        <table>
            <tr>
                <td class="label">Nama Mahasiswa</td>
                <td class="colon">:</td>
                <td><?= $proposal->nama_mahasiswa ?></td>
            </tr>
            <tr>
                <td class="label">NIM</td>
                <td class="colon">:</td>
                <td><?= $proposal->nim ?></td>
            </tr>
            <tr>
                <td class="label">Program Studi</td>
                <td class="colon">:</td>
                <td><?= $proposal->nama_prodi ?></td>
            </tr>
            <tr>
                <td class="label">Judul Tugas Akhir</td>
                <td class="colon">:</td>
                <td><strong><?= $proposal->judul ?></strong></td>
            </tr>
            <tr>
                <td class="label">Pembimbing</td>
                <td class="colon">:</td>
                <td><?= $proposal->nama_dosen ?></td>
            </tr>
            <tr>
                <td class="label">Periode</td>
                <td class="colon">:</td>
                <td>
                    <?php 
                    $tanggal_awal = !empty($jurnal_list) ? min(array_column($jurnal_list, 'tanggal_bimbingan')) : date('Y-m-d');
                    $tanggal_akhir = !empty($jurnal_list) ? max(array_column($jurnal_list, 'tanggal_bimbingan')) : date('Y-m-d');
                    echo date('d F Y', strtotime($tanggal_awal)) . ' - ' . date('d F Y', strtotime($tanggal_akhir));
                    ?>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Ringkasan Bimbingan -->
    <div class="summary">
        <table>
            <tr>
                <td class="summary-label">Total Pertemuan Bimbingan</td>
                <td><strong><?= count($jurnal_list) ?> pertemuan</strong></td>
            </tr>
            <tr>
                <td class="summary-label">Jurnal Tervalidasi</td>
                <td><?= count(array_filter($jurnal_list, function($j) { return $j->status_validasi == '1'; })) ?> jurnal</td>
            </tr>
            <tr>
                <td class="summary-label">Progress Bimbingan</td>
                <td>
                    <?php 
                    $progress = round((count($jurnal_list) / 16) * 100, 1);
                    echo $progress . '% dari target 16 pertemuan';
                    ?>
                </td>
            </tr>
            <tr>
                <td class="summary-label">Status Kelengkapan</td>
                <td>
                    <?php if (count($jurnal_list) >= 16): ?>
                        <span class="status-badge status-validated">LENGKAP</span>
                    <?php elseif (count($jurnal_list) >= 8): ?>
                        <span class="status-badge status-pending">SIAP SEMINAR PROPOSAL</span>
                    <?php else: ?>
                        <span class="status-badge status-revision">DALAM PROSES</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Tabel Jurnal Bimbingan -->
    <table class="jurnal-table">
        <thead>
            <tr>
                <th class="no-col">No</th>
                <th class="meeting-col">Pert. Ke-</th>
                <th class="date-col">Tanggal</th>
                <th class="material-col">Materi Bimbingan</th>
                <th class="notes-col">Catatan Dosen & Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($jurnal_list)): ?>
                <?php foreach ($jurnal_list as $index => $jurnal): ?>
                <tr>
                    <td class="no-col"><?= $index + 1 ?></td>
                    <td class="meeting-col"><?= $jurnal->pertemuan_ke ?></td>
                    <td class="date-col">
                        <?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?>
                        <?php if ($jurnal->status_validasi == '1'): ?>
                            <br><span class="status-badge status-validated">✓ Valid</span>
                        <?php elseif ($jurnal->status_validasi == '2'): ?>
                            <br><span class="status-badge status-revision">⚠ Revisi</span>
                        <?php else: ?>
                            <br><span class="status-badge status-pending">⏳ Pending</span>
                        <?php endif; ?>
                    </td>
                    <td class="material-col">
                        <?= nl2br(htmlspecialchars($jurnal->materi_bimbingan)) ?>
                    </td>
                    <td class="notes-col">
                        <?php if (!empty($jurnal->catatan_dosen)): ?>
                            <strong>Catatan Dosen:</strong><br>
                            <?= nl2br(htmlspecialchars($jurnal->catatan_dosen)) ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($jurnal->tindak_lanjut)): ?>
                            <?php if (!empty($jurnal->catatan_dosen)): ?><br><br><?php endif; ?>
                            <strong>Tindak Lanjut:</strong><br>
                            <?= nl2br(htmlspecialchars($jurnal->tindak_lanjut)) ?>
                        <?php endif; ?>
                        
                        <?php if (empty($jurnal->catatan_dosen) && empty($jurnal->tindak_lanjut)): ?>
                            <em>-</em>
                        <?php endif; ?>
                        
                        <?php if ($jurnal->status_validasi == '1' && !empty($jurnal->tanggal_validasi)): ?>
                            <br><br><small><em>Divalidasi: <?= date('d/m/Y H:i', strtotime($jurnal->tanggal_validasi)) ?></em></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #666;">
                        <em>Belum ada data jurnal bimbingan</em>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Catatan Tambahan -->
    <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #007bff;">
        <strong>Catatan:</strong>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li>Mahasiswa wajib melakukan minimal 16 kali pertemuan bimbingan sebelum mengajukan ujian tugas akhir</li>
            <li>Setiap jurnal bimbingan harus mendapat validasi dari dosen pembimbing</li>
            <li>Minimal 8 pertemuan tervalidasi diperlukan untuk mengajukan seminar proposal</li>
            <li>Dokumen ini dicetak pada tanggal <?= date('d F Y') ?></li>
        </ul>
    </div>
    
    <!-- Tanda Tangan -->
    <div class="signatures">
        <div class="signature-row">
            <div class="signature-box">
                <div class="signature-title">Mengetahui,</div>
                <div class="signature-title">Ketua Program Studi</div>
                <div class="signature-date">Merauke, <?= date('d F Y') ?></div>
                <div class="signature-line"></div>
                <div class="signature-name">(...........................)</div>
                <div class="signature-position">Ketua Prodi <?= $proposal->nama_prodi ?></div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Dosen Pembimbing</div>
                <div class="signature-date">Merauke, <?= date('d F Y') ?></div>
                <div class="signature-line"></div>
                <div class="signature-name"><?= $proposal->nama_dosen ?></div>
                <div class="signature-position">Pembimbing Tugas Akhir</div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>
            <strong>Sistem Informasi Manajemen Tugas Akhir</strong><br>
            Sekolah Tinggi Katolik Santo Yakobus Merauke<br>
            Dokumen ini digenerate secara otomatis pada <?= date('d F Y H:i:s') ?> WIT
        </p>
    </div>

</body>
</html>