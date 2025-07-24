<?php
// ========================================
// 4. BERITA ACARA SEMINAR PROPOSAL PDF TEMPLATE
// File: application/views/staf/seminar_proposal/pdf_berita_acara.php
// ========================================
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Berita Acara Seminar Proposal</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
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
        .ba-info {
            margin-bottom: 20px;
        }
        .content {
            text-align: justify;
            margin-bottom: 30px;
        }
        .seminar-info {
            margin: 20px 0;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .hasil-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .hasil-table th,
        .hasil-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .hasil-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .signature-section {
            margin-top: 40px;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">BERITA ACARA SEMINAR PROPOSAL</div>
        <div class="subtitle">SEKOLAH TINGGI KEGURUAN ST. YAKOBUS</div>
        <div class="subtitle">PROGRAM STUDI <?= strtoupper($proposal->nama_prodi) ?></div>
    </div>

    <div class="ba-info">
        <div><strong>Nomor: <?= $nomor_berita_acara ?></strong></div>
    </div>

    <div class="content">
        <p>Pada hari ini, <?= $proposal->tanggal_seminar_proposal ? 
            date('l', strtotime($proposal->tanggal_seminar_proposal)) : '____________' ?>, 
        tanggal <?= $proposal->tanggal_seminar_proposal ? 
            date('d', strtotime($proposal->tanggal_seminar_proposal)) : '____' ?> 
        bulan <?= $proposal->tanggal_seminar_proposal ? 
            date('F', strtotime($proposal->tanggal_seminar_proposal)) : '____________' ?> 
        tahun <?= $proposal->tanggal_seminar_proposal ? 
            date('Y', strtotime($proposal->tanggal_seminar_proposal)) : '________' ?>, 
        telah dilaksanakan Seminar Proposal Tugas Akhir dengan data sebagai berikut:</p>

        <div class="seminar-info">
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
                <span class="label">Judul Proposal</span>: <?= $proposal->judul ?>
            </div>
            <div class="info-row">
                <span class="label">Dosen Pembimbing</span>: <?= $proposal->nama_pembimbing ?>
            </div>
            <div class="info-row">
                <span class="label">Dosen Penguji</span>: <?= $proposal->nama_penguji ?: '________________' ?>
            </div>
            <div class="info-row">
                <span class="label">Waktu Pelaksanaan</span>: 
                <?= $proposal->tanggal_seminar_proposal ? 
                    date('H:i', strtotime($proposal->tanggal_seminar_proposal)) . ' - selesai WIB' : 
                    '_______ - _______ WIB' ?>
            </div>
            <div class="info-row">
                <span class="label">Tempat</span>: <?= $proposal->nama_ruangan ?: '________________' ?>
            </div>
        </div>

        <p><strong>HASIL SEMINAR:</strong></p>
        
        <table class="hasil-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="30%">Aspek Penilaian</th>
                    <th width="20%">Nilai Pembimbing</th>
                    <th width="20%">Nilai Penguji</th>
                    <th width="25%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">1</td>
                    <td>Sistematika Penulisan</td>
                    <td style="text-align: center;">_______</td>
                    <td style="text-align: center;">_______</td>
                    <td>_________________</td>
                </tr>
                <tr>
                    <td style="text-align: center;">2</td>
                    <td>Kesesuaian Judul dengan Isi</td>
                    <td style="text-align: center;">_______</td>
                    <td style="text-align: center;">_______</td>
                    <td>_________________</td>
                </tr>
                <tr>
                    <td style="text-align: center;">3</td>
                    <td>Landasan Teori</td>
                    <td style="text-align: center;">_______</td>
                    <td style="text-align: center;">_______</td>
                    <td>_________________</td>
                </tr>
                <tr>
                    <td style="text-align: center;">4</td>
                    <td>Metodologi Penelitian</td>
                    <td style="text-align: center;">_______</td>
                    <td style="text-align: center;">_______</td>
                    <td>_________________</td>
                </tr>
                <tr>
                    <td style="text-align: center;">5</td>
                    <td>Presentasi dan Komunikasi</td>
                    <td style="text-align: center;">_______</td>
                    <td style="text-align: center;">_______</td>
                    <td>_________________</td>
                </tr>
                <tr style="background-color: #f0f0f0;">
                    <td colspan="2" style="text-align: center; font-weight: bold;">TOTAL NILAI</td>
                    <td style="text-align: center; font-weight: bold;">_______</td>
                    <td style="text-align: center; font-weight: bold;">_______</td>
                    <td style="text-align: center; font-weight: bold;">_______</td>
                </tr>
            </tbody>
        </table>

        <p><strong>KEPUTUSAN:</strong></p>
        <p>☐ Proposal <strong>DITERIMA</strong> tanpa revisi</p>
        <p>☐ Proposal <strong>DITERIMA</strong> dengan revisi minor</p>
        <p>☐ Proposal <strong>DITERIMA</strong> dengan revisi mayor</p>
        <p>☐ Proposal <strong>DITOLAK</strong></p>

        <p><strong>CATATAN DAN SARAN:</strong></p>
        <div style="border: 1px solid #000; min-height: 100px; padding: 10px; margin: 10px 0;">
            <?= $berita_acara->catatan ?? '' ?>
        </div>
    </div>

    <div class="signature-section">
        <p>Demikian Berita Acara ini dibuat dengan sebenarnya.</p>
        
        <table class="signature-table">
            <tr>
                <td width="50%">
                    <strong>Dosen Pembimbing</strong>
                    <br><br><br><br>
                    <u><?= $proposal->nama_pembimbing ?></u><br>
                    NIP. <?= $proposal->nip_pembimbing ?>
                </td>
                <td width="50%">
                    <strong>Dosen Penguji</strong>
                    <br><br><br><br>
                    <u><?= $proposal->nama_penguji ?: '______________________' ?></u><br>
                    NIP. <?= $proposal->nip_penguji ?: '______________________' ?>
                </td>
            </tr>
        </table>
        
        <div style="text-align: center; margin-top: 30px;">
            <strong>Mengetahui,</strong><br>
            <strong>Ketua Program Studi <?= $proposal->nama_prodi ?></strong>
            <br><br><br><br>
            <u>_______________________</u><br>
            NIP. ___________________
        </div>
    </div>

    <div style="margin-top: 30px; font-size: 10px; color: #666;">
        <em>Dokumen ini digenerate oleh <?= $generated_by ?> pada <?= $generated_at ?></em>
    </div>
</body>
</html>