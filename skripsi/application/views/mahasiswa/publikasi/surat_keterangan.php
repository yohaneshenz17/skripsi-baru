<?php
// FILE: application/views/mahasiswa/publikasi/surat_keterangan.php
// Template PDF untuk Surat Keterangan Publikasi Tugas Akhir
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan Publikasi Tugas Akhir</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            width: 80px;
            height: 80px;
            float: left;
            margin-right: 20px;
        }
        .title {
            font-weight: bold;
            font-size: 16pt;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14pt;
            margin-bottom: 5px;
        }
        .address {
            font-size: 10pt;
            margin-top: 10px;
        }
        .content {
            margin-top: 30px;
        }
        .letter-title {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            text-decoration: underline;
            margin-bottom: 30px;
        }
        .letter-number {
            text-align: center;
            margin-bottom: 20px;
        }
        .student-data {
            margin: 20px 0;
        }
        .student-data table {
            width: 100%;
            border-collapse: collapse;
        }
        .student-data td {
            padding: 5px 0;
            vertical-align: top;
        }
        .student-data td:first-child {
            width: 200px;
        }
        .student-data td:nth-child(2) {
            width: 20px;
            text-align: center;
        }
        .signature {
            margin-top: 50px;
            float: right;
            width: 300px;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            margin-top: 80px;
            margin-bottom: 5px;
        }
        .clear {
            clear: both;
        }
        .footer {
            margin-top: 100px;
            font-size: 10pt;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header Surat -->
    <div class="header">
        <!-- Logo STK (jika ada) -->
        <div style="float: left; width: 100px;">
            <!-- <img src="<?= base_url('assets/img/logo-stk.png') ?>" class="logo" alt="Logo STK"> -->
        </div>
        
        <div style="margin-left: 120px;">
            <div class="title">SEKOLAH TINGGI KATOLIK SANTO YAKOBUS</div>
            <div class="subtitle">MERAUKE</div>
            <div class="address">
                Jl. Brawijaya No. 121 Merauke 99611<br>
                Telp. (0971) 321195, Fax. (0971) 321195<br>
                Email: stkp.yakobus@gmail.com | Website: www.stkyakobus.ac.id
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Content Surat -->
    <div class="content">
        <!-- Title Surat -->
        <div class="letter-title">
            SURAT KETERANGAN PUBLIKASI TUGAS AKHIR
        </div>
        
        <!-- Nomor Surat -->
        <div class="letter-number">
            Nomor: <?= str_pad($publikasi->id, 3, '0', STR_PAD_LEFT) ?>/STK-SY/PUB/<?= date('m/Y') ?>
        </div>

        <!-- Isi Surat -->
        <p style="text-align: justify; margin-bottom: 20px;">
            Yang bertanda tangan di bawah ini, Ketua Program Studi <?= $publikasi->nama_prodi ?> 
            Sekolah Tinggi Katolik Santo Yakobus Merauke, menerangkan bahwa:
        </p>

        <!-- Data Mahasiswa -->
        <div class="student-data">
            <table>
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><strong><?= strtoupper($publikasi->nama_mahasiswa) ?></strong></td>
                </tr>
                <tr>
                    <td>Nomor Induk Mahasiswa (NIM)</td>
                    <td>:</td>
                    <td><strong><?= $publikasi->nim ?></strong></td>
                </tr>
                <tr>
                    <td>Program Studi</td>
                    <td>:</td>
                    <td><?= $publikasi->nama_prodi ?></td>
                </tr>
                <tr>
                    <td>Judul Tugas Akhir</td>
                    <td>:</td>
                    <td style="font-style: italic;">"<?= $publikasi->judul_skripsi_final ?>"</td>
                </tr>
                <tr>
                    <td>Dosen Pembimbing</td>
                    <td>:</td>
                    <td><?= $publikasi->nama_pembimbing ?></td>
                </tr>
            </table>
        </div>

        <p style="text-align: justify; margin: 20px 0;">
            Telah menyelesaikan seluruh tahapan tugas akhir dan 
            <strong>TUGAS AKHIRNYA TELAH DIPUBLIKASIKAN</strong> 
            pada repositori institusi Sekolah Tinggi Katolik Santo Yakobus Merauke 
            pada tanggal <?= date('d F Y', strtotime($publikasi->tanggal_validasi_staf)) ?>.
        </p>

        <?php if ($publikasi->link_repository): ?>
        <p style="text-align: justify; margin: 20px 0;">
            Publikasi tugas akhir dapat diakses melalui link berikut:<br>
            <strong><?= $publikasi->link_repository ?></strong>
        </p>
        <?php endif; ?>

        <p style="text-align: justify; margin: 20px 0;">
            Surat keterangan ini dibuat untuk keperluan yang bersangkutan 
            dan dapat dipergunakan seperlunya.
        </p>

        <!-- Tanggal dan Tempat -->
        <p style="margin: 30px 0 50px 0;">
            Merauke, <?= $tanggal_surat ?>
        </p>

        <!-- Tanda Tangan -->
        <div class="signature">
            <p>Ketua Program Studi<br><?= $publikasi->nama_prodi ?></p>
            <div class="signature-line"></div>
            <p>
                <strong><?= isset($publikasi->nama_kaprodi) ? $publikasi->nama_kaprodi : 'Dr. [Nama Kaprodi]' ?></strong><br>
                <?php if (isset($publikasi->nip_kaprodi)): ?>
                    NIP. <?= $publikasi->nip_kaprodi ?>
                <?php endif; ?>
            </p>
        </div>

        <div class="clear"></div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Dokumen ini dibuat secara otomatis oleh Sistem Informasi Manajemen Tugas Akhir<br>
        STK Santo Yakobus Merauke - <?= date('Y') ?>
    </div>
</body>
</html>