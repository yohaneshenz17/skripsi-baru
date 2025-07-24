<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jurnal Bimbingan - <?= $mahasiswa->nama ?></title>
    <style>
        @page {
            margin: 2cm;
            @bottom-center {
                content: "Halaman " counter(page) " dari " counter(pages);
            }
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin: 0;
            color: #2c3e50;
        }
        
        .header h2 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
            color: #34495e;
        }
        
        .header p {
            margin: 3px 0;
            font-size: 11pt;
            color: #7f8c8d;
        }
        
        .info-mahasiswa {
            margin-bottom: 25px;
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 5px solid #3498db;
        }
        
        .info-mahasiswa table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-mahasiswa td {
            padding: 5px 10px;
            vertical-align: top;
        }
        
        .info-mahasiswa .label {
            font-weight: bold;
            width: 150px;
            color: #2c3e50;
        }
        
        .info-mahasiswa .separator {
            width: 10px;
            text-align: center;
        }
        
        .info-mahasiswa .value {
            color: #34495e;
        }
        
        .jurnal-container {
            margin-bottom: 30px;
        }
        
        .jurnal-item {
            border: 1px solid #bdc3c7;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .jurnal-header {
            background-color: #34495e;
            color: white;
            padding: 10px;
            font-weight: bold;
        }
        
        .jurnal-content {
            padding: 15px;
        }
        
        .jurnal-content table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .jurnal-content td {
            padding: 8px;
            vertical-align: top;
            border-bottom: 1px dotted #bdc3c7;
        }
        
        .jurnal-content .field-label {
            font-weight: bold;
            width: 120px;
            color: #2c3e50;
        }
        
        .jurnal-content .field-separator {
            width: 10px;
            text-align: center;
        }
        
        .jurnal-content .field-value {
            color: #34495e;
        }
        
        .status-valid {
            color: #27ae60;
            font-weight: bold;
        }
        
        .status-pending {
            color: #f39c12;
            font-weight: bold;
        }
        
        .status-revisi {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            border-top: 2px solid #34495e;
            padding-top: 20px;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-bottom: 1px solid #2c3e50;
            height: 60px;
            margin-bottom: 10px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-style: italic;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .summary-table th,
        .summary-table td {
            border: 1px solid #bdc3c7;
            padding: 8px;
            text-align: center;
        }
        
        .summary-table th {
            background-color: #ecf0f1;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .meta-info {
            margin-top: 30px;
            font-size: 10pt;
            color: #7f8c8d;
            text-align: center;
            border-top: 1px solid #ecf0f1;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>SEKOLAH TINGGI KATOLIK SANTO YAKOBUS MERAUKE</h1>
        <h2>JURNAL BIMBINGAN TUGAS AKHIR</h2>
        <p>Jl. Missi 2, Mandala, Merauke, Papua Selatan</p>
        <p>Telp: (0971) 325264 | Email: stkyakobus@gmail.com</p>
    </div>
    
    <!-- Informasi Mahasiswa -->
    <div class="info-mahasiswa">
        <table>
            <tr>
                <td class="label">Nama Mahasiswa</td>
                <td class="separator">:</td>
                <td class="value"><?= $mahasiswa->nama ?></td>
            </tr>
            <tr>
                <td class="label">NIM</td>
                <td class="separator">:</td>
                <td class="value"><?= $mahasiswa->nim ?></td>
            </tr>
            <tr>
                <td class="label">Program Studi</td>
                <td class="separator">:</td>
                <td class="value"><?= $mahasiswa->nama_prodi ?></td>
            </tr>
            <tr>
                <td class="label">Judul Proposal</td>
                <td class="separator">:</td>
                <td class="value"><?= $mahasiswa->judul ?></td>
            </tr>
            <tr>
                <td class="label">Dosen Pembimbing</td>
                <td class="separator">:</td>
                <td class="value"><?= isset($jurnal[0]) && $jurnal[0]->nama_pembimbing ? $jurnal[0]->nama_pembimbing : '-' ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Jurnal Bimbingan -->
    <div class="jurnal-container">
        <?php if (!empty($jurnal)): ?>
            <?php foreach ($jurnal as $item): ?>
            <div class="jurnal-item">
                <div class="jurnal-header">
                    Pertemuan Ke-<?= $item->pertemuan_ke ?> | 
                    <?= date('d F Y', strtotime($item->tanggal_bimbingan)) ?>
                    <?php if ($item->durasi_bimbingan): ?>
                        | Durasi: <?= $item->durasi_bimbingan ?> menit
                    <?php endif; ?>
                </div>
                <div class="jurnal-content">
                    <table>
                        <tr>
                            <td class="field-label">Materi Bimbingan</td>
                            <td class="field-separator">:</td>
                            <td class="field-value"><?= nl2br(htmlspecialchars($item->materi_bimbingan)) ?></td>
                        </tr>
                        <?php if ($item->catatan_mahasiswa): ?>
                        <tr>
                            <td class="field-label">Catatan Mahasiswa</td>
                            <td class="field-separator">:</td>
                            <td class="field-value"><?= nl2br(htmlspecialchars($item->catatan_mahasiswa)) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($item->catatan_dosen): ?>
                        <tr>
                            <td class="field-label">Catatan Dosen</td>
                            <td class="field-separator">:</td>
                            <td class="field-value"><?= nl2br(htmlspecialchars($item->catatan_dosen)) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($item->tindak_lanjut): ?>
                        <tr>
                            <td class="field-label">Tindak Lanjut</td>
                            <td class="field-separator">:</td>
                            <td class="field-value"><?= nl2br(htmlspecialchars($item->tindak_lanjut)) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="field-label">Status Validasi</td>
                            <td class="field-separator">:</td>
                            <td class="field-value">
                                <?php
                                switch($item->status_validasi) {
                                    case '0':
                                        echo '<span class="status-pending">MENUNGGU VALIDASI</span>';
                                        break;
                                    case '1':
                                        echo '<span class="status-valid">VALID</span>';
                                        if ($item->tanggal_validasi) {
                                            echo ' (' . date('d/m/Y H:i', strtotime($item->tanggal_validasi)) . ')';
                                        }
                                        if ($item->nama_validator) {
                                            echo ' oleh ' . $item->nama_validator;
                                        }
                                        break;
                                    case '2':
                                        echo '<span class="status-revisi">PERLU REVISI</span>';
                                        if ($item->tanggal_validasi) {
                                            echo ' (' . date('d/m/Y H:i', strtotime($item->tanggal_validasi)) . ')';
                                        }
                                        break;
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Summary Table -->
            <div style="margin-top: 30px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;">
                    Ringkasan Bimbingan
                </h3>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Pertemuan</th>
                            <th>Durasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jurnal as $i => $item): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= date('d/m/Y', strtotime($item->tanggal_bimbingan)) ?></td>
                            <td>Ke-<?= $item->pertemuan_ke ?></td>
                            <td><?= $item->durasi_bimbingan ? $item->durasi_bimbingan . ' menit' : '-' ?></td>
                            <td>
                                <?php
                                switch($item->status_validasi) {
                                    case '0': echo 'Pending'; break;
                                    case '1': echo 'Valid'; break;
                                    case '2': echo 'Revisi'; break;
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #ecf0f1; font-weight: bold;">
                            <td colspan="2">Total Pertemuan</td>
                            <td><?= count($jurnal) ?> kali</td>
                            <td>
                                <?php
                                $total_durasi = array_sum(array_filter(array_column($jurnal, 'durasi_bimbingan')));
                                echo $total_durasi ? $total_durasi . ' menit' : '-';
                                ?>
                            </td>
                            <td>
                                <?php
                                $valid_count = array_count_values(array_column($jurnal, 'status_validasi'))['1'] ?? 0;
                                echo $valid_count . ' valid';
                                ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
        <?php else: ?>
            <div class="no-data">
                <p><strong>Belum ada jurnal bimbingan untuk mahasiswa ini.</strong></p>
                <p>Mahasiswa dapat mulai mencatat jurnal bimbingan setelah proposal disetujui dan pembimbing ditunjuk.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Footer dengan tanda tangan -->
    <div class="footer">
        <div class="signature-section" style="display: table; width: 100%;">
            <div class="signature-box" style="display: table-cell; width: 50%; vertical-align: top;">
                <p><strong>Mahasiswa</strong></p>
                <div class="signature-line"></div>
                <p><?= $mahasiswa->nama ?><br>NIM: <?= $mahasiswa->nim ?></p>
            </div>
            <div class="signature-box" style="display: table-cell; width: 50%; vertical-align: top;">
                <p><strong>Dosen Pembimbing</strong></p>
                <div class="signature-line"></div>
                <p><?= isset($jurnal[0]) && $jurnal[0]->nama_pembimbing ? $jurnal[0]->nama_pembimbing : '________________' ?></p>
            </div>
        </div>
    </div>
    
    <!-- Meta Information -->
    <div class="meta-info">
        <p>
            Dokumen ini digenerate secara otomatis oleh Sistem Informasi Manajemen Tugas Akhir<br>
            STK Santo Yakobus Merauke pada <?= $generated_at ?> oleh <?= $generated_by ?>
        </p>
    </div>
</body>
</html>