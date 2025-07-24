<?php
// ========================================
// 1. JURNAL BIMBINGAN PDF TEMPLATE
// File: application/views/staf/bimbingan/pdf_jurnal.php
// ========================================
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jurnal Bimbingan</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
        }
        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 10px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .subtitle {
            font-size: 14px;
            margin: 5px 0;
        }
        .info-mahasiswa {
            margin-bottom: 20px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .table-jurnal {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table-jurnal th,
        .table-jurnal td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .table-jurnal th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
        }
        .signature {
            margin-top: 50px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">JURNAL BIMBINGAN TUGAS AKHIR</div>
        <div class="subtitle">SEKOLAH TINGGI KEGURUAN ST. YAKOBUS</div>
        <div class="subtitle">PROGRAM STUDI <?= strtoupper($proposal->nama_prodi) ?></div>
    </div>

    <div class="info-mahasiswa">
        <div class="info-row">
            <span class="label">Nama Mahasiswa</span>: <?= $proposal->nama_mahasiswa ?>
        </div>
        <div class="info-row">
            <span class="label">NIM</span>: <?= $proposal->nim ?>
        </div>
        <div class="info-row">
            <span class="label">Program Studi</span>: <?= $proposal->nama_prodi ?>
        </div>
        <div class="info-row">
            <span class="label">Judul Tugas Akhir</span>: <?= $proposal->judul ?>
        </div>
        <div class="info-row">
            <span class="label">Dosen Pembimbing</span>: <?= $proposal->nama_pembimbing ?>
        </div>
        <div class="info-row">
            <span class="label">NIP Pembimbing</span>: <?= $proposal->nip_pembimbing ?>
        </div>
    </div>

    <table class="table-jurnal">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="25%">Materi Bimbingan</th>
                <th width="25%">Catatan Pembimbing</th>
                <th width="15%">Status</th>
                <th width="15%">Paraf Pembimbing</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($jurnal_bimbingan)): ?>
                <?php $no = 1; foreach($jurnal_bimbingan as $jurnal): ?>
                    <tr>
                        <td style="text-align: center;"><?= $no++ ?></td>
                        <td><?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?></td>
                        <td><?= $jurnal->materi_bimbingan ?></td>
                        <td><?= $jurnal->catatan_pembimbing ?: '-' ?></td>
                        <td style="text-align: center;">
                            <?= $jurnal->status_validasi == '1' ? 'Valid' : 'Pending' ?>
                        </td>
                        <td style="text-align: center;">
                            <?= $jurnal->status_validasi == '1' ? '✓' : '-' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; font-style: italic;">
                        Belum ada jurnal bimbingan
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <div>Jakarta, <?= date('d F Y') ?></div>
        <div class="signature">
            <div>Dosen Pembimbing</div>
            <br><br><br>
            <div><u><?= $proposal->nama_pembimbing ?></u></div>
            <div>NIP. <?= $proposal->nip_pembimbing ?></div>
        </div>
    </div>

    <div style="margin-top: 30px; font-size: 10px; color: #666;">
        <em>Dokumen ini digenerate oleh <?= $generated_by ?> pada <?= $generated_at ?></em>
    </div>
</body>
</html>