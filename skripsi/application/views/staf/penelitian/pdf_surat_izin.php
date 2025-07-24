<?php
// ========================================
// 2. SURAT IZIN PENELITIAN PDF TEMPLATE
// File: application/views/staf/penelitian/pdf_surat_izin.php
// ========================================
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Izin Penelitian</title>
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
        .surat-info {
            margin-bottom: 20px;
        }
        .nomor-surat {
            font-weight: bold;
        }
        .content {
            text-align: justify;
            margin-bottom: 30px;
        }
        .mahasiswa-info {
            margin: 20px 0;
            padding-left: 40px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
        }
        .signature {
            margin-top: 50px;
        }
        .tembusan {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">SURAT IZIN PENELITIAN</div>
        <div class="subtitle">SEKOLAH TINGGI KEGURUAN ST. YAKOBUS</div>
        <div class="subtitle">Jl. Danau Sunter Utara No. 95, Jakarta Utara 14350</div>
        <div class="subtitle">Telp. (021) 6402142, Fax. (021) 6402141</div>
    </div>

    <div class="surat-info">
        <div class="nomor-surat">Nomor: <?= $nomor_surat ?></div>
        <div>Hal: Izin Penelitian Tugas Akhir</div>
        <div>Lampiran: 1 (satu) berkas</div>
    </div>

    <div>
        <div>Kepada Yth.</div>
        <div><strong><?= $proposal->instansi_penelitian ?: 'Kepala [Nama Instansi/Perusahaan]' ?></strong></div>
        <div><?= $proposal->alamat_penelitian ?: 'di Tempat' ?></div>
    </div>

    <div class="content">
        <p>Dengan hormat,</p>
        
        <p>Sehubungan dengan penyelesaian Tugas Akhir mahasiswa Sekolah Tinggi Keguruan St. Yakobus, 
        maka dengan ini kami mohon bantuan Bapak/Ibu untuk memberikan izin penelitian kepada mahasiswa:</p>

        <div class="mahasiswa-info">
            <div class="info-row">
                <span class="label">Nama</span>: <?= $proposal->nama_mahasiswa ?>
            </div>
            <div class="info-row">
                <span class="label">NIM</span>: <?= $proposal->nim ?>
            </div>
            <div class="info-row">
                <span class="label">Program Studi</span>: <?= $proposal->nama_prodi ?>
            </div>
            <div class="info-row">
                <span class="label">Judul Penelitian</span>: <?= $proposal->judul ?>
            </div>
            <div class="info-row">
                <span class="label">Dosen Pembimbing</span>: <?= $proposal->nama_pembimbing ?>
            </div>
            <div class="info-row">
                <span class="label">Waktu Penelitian</span>: <?= $proposal->periode_penelitian ?: 'Disesuaikan dengan kebutuhan' ?>
            </div>
        </div>

        <p>Penelitian ini dilakukan dalam rangka penyelesaian Tugas Akhir sebagai syarat untuk memperoleh 
        gelar Sarjana di Sekolah Tinggi Keguruan St. Yakobus.</p>

        <p>Demikian surat permohonan ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>
    </div>

    <div class="footer">
        <div>Jakarta, <?= $tanggal_surat ?></div>
        <div>Ketua Program Studi <?= $proposal->nama_prodi ?></div>
        <div class="signature">
            <br><br><br>
            <div><u>_______________________</u></div>
            <div>NIP. ___________________</div>
        </div>
    </div>

    <div class="tembusan">
        <div><strong>Tembusan:</strong></div>
        <div>1. Ketua STK St. Yakobus</div>
        <div>2. Dosen Pembimbing</div>
        <div>3. Mahasiswa yang bersangkutan</div>
        <div>4. Arsip</div>
    </div>

    <div style="margin-top: 30px; font-size: 10px; color: #666;">
        <em>Dokumen ini digenerate oleh <?= $generated_by ?> pada <?= $generated_at ?></em>
    </div>
</body>
</html>
