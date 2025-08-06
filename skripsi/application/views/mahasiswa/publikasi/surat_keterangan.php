<?php
/**
 * Template Surat Keterangan Publikasi Tugas Akhir
 * STK Santo Yakobus Merauke
 * 
 * File: application/views/mahasiswa/publikasi/surat_keterangan.php
 * Update: Agustus 2025
 */

// Format tanggal Indonesia
function formatTanggalIndonesia($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $pecahkan = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

// Generate nomor surat otomatis
$nomor_surat = str_pad($publikasi->id, 3, '0', STR_PAD_LEFT);
$bulan_tahun = date('m/Y');
$tanggal_surat = formatTanggalIndonesia(date('Y-m-d'));
$tanggal_publikasi = formatTanggalIndonesia($publikasi->tanggal_validasi_staf);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Publikasi Tugas Akhir - <?= $publikasi->nama_mahasiswa ?></title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #000;
            background: white;
        }
        
        @media print {
            body { margin: 0; padding: 15px; }
            .no-print { display: none; }
            @page { margin: 1cm; }
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin-right: 20px;
            background: url('<?= base_url('assets/images/logo-stk.png') ?>') no-repeat center center;
            background-size: contain;
        }

        .header-text {
            text-align: center;
        }

        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
        }

        .header .contact-info {
            font-size: 10pt;
            margin-top: 8px;
        }

        .content {
            margin: 30px 0;
        }

        .letter-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .letter-number {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 25px;
        }

        .student-data {
            margin: 20px 0;
        }

        .student-data table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-data td {
            padding: 3px 0;
            vertical-align: top;
        }

        .student-data td:first-child {
            width: 25%;
        }

        .student-data td:nth-child(2) {
            width: 5%;
            text-align: center;
        }

        .publication-statement {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }

        .repository-link {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
            word-break: break-all;
            font-family: 'Courier New', monospace;
        }

        .signature-section {
            margin-top: 50px;
            display: table;
            width: 100%;
        }

        .signature-left, .signature-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .signature-right {
            text-align: center;
        }

        .signature-title {
            margin-bottom: 60px;
            font-weight: normal;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }

        .signature-nip {
            font-size: 10pt;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 48pt;
            color: rgba(0,0,0,0.05);
            z-index: -1;
            pointer-events: none;
        }

        .field {
            font-weight: bold;
        }

        .italic {
            font-style: italic;
        }

        /* Print specific styles */
        @media print {
            .watermark { display: block; }
            .no-break { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">STK SANTO YAKOBUS</div>

    <!-- Header -->
    <div class="header">
        <div class="logo-container">
            <div class="logo"></div>
            <div class="header-text">
                <h1>Sekolah Tinggi Katolik Santo Yakobus</h1>
                <h2>Merauke</h2>
                <div class="contact-info">
                    Jl. Brawijaya No. 121 Merauke 99611<br>
                    Telp. (0971) 321195, Fax. (0971) 321195<br>
                    Email: stkp.yakobus@gmail.com | Website: www.stkyakobus.ac.id
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="content">
        <!-- Letter Title -->
        <div class="letter-title">
            Surat Keterangan Publikasi Tugas Akhir
        </div>
        
        <!-- Letter Number -->
        <div class="letter-number">
            Nomor: <?= $nomor_surat ?>/STK-SY/PUB/<?= $bulan_tahun ?>
        </div>

        <!-- Opening Statement -->
        <p style="text-align: justify; margin-bottom: 20px;">
            Yang bertanda tangan di bawah ini, Ketua Program Studi <?= $publikasi->nama_prodi ?> 
            Sekolah Tinggi Katolik Santo Yakobus Merauke, menerangkan bahwa:
        </p>

        <!-- Student Data -->
        <div class="student-data no-break">
            <table>
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><span class="field"><?= strtoupper($publikasi->nama_mahasiswa) ?></span></td>
                </tr>
                <tr>
                    <td>Nomor Induk Mahasiswa (NIM)</td>
                    <td>:</td>
                    <td><span class="field"><?= $publikasi->nim ?></span></td>
                </tr>
                <tr>
                    <td>Program Studi</td>
                    <td>:</td>
                    <td><span class="field"><?= $publikasi->nama_prodi ?></span></td>
                </tr>
                <tr>
                    <td>Judul Tugas Akhir</td>
                    <td>:</td>
                    <td><span class="italic">"<span class="field"><?= $publikasi->judul_skripsi_final ?></span>"</span></td>
                </tr>
                <tr>
                    <td>Dosen Pembimbing</td>
                    <td>:</td>
                    <td><span class="field"><?= $publikasi->nama_pembimbing ?></span></td>
                </tr>
            </table>
        </div>

        <!-- Publication Statement -->
        <div class="publication-statement no-break">
            <p style="margin: 0; font-size: 13pt;">
                <strong>TELAH MENYELESAIKAN SELURUH TAHAPAN TUGAS AKHIR<br>
                DAN TUGAS AKHIRNYA TELAH DIPUBLIKASIKAN</strong>
            </p>
        </div>

        <p style="text-align: justify; margin: 20px 0;">
            pada repositori institusi Sekolah Tinggi Katolik Santo Yakobus Merauke 
            pada tanggal <span class="field"><?= $tanggal_publikasi ?></span>.
        </p>

        <!-- Repository Link (conditional) -->
        <?php if (!empty($publikasi->link_repository)): ?>
        <p style="text-align: justify; margin: 20px 0;">
            Publikasi tugas akhir dapat diakses melalui link berikut:
        </p>
        <div class="repository-link no-break">
            <strong><?= $publikasi->link_repository ?></strong>
        </div>
        <?php endif; ?>

        <p style="text-align: justify; margin: 20px 0;">
            Surat keterangan ini dibuat untuk keperluan yang bersangkutan 
            dan dapat dipergunakan seperlunya.
        </p>

        <!-- Date and Place -->
        <p style="margin: 30px 0 20px 0; text-align: right;">
            Merauke, <?= $tanggal_surat ?>
        </p>

        <!-- Signature Section -->
        <div class="signature-section no-break">
            <div class="signature-left">
                <!-- Empty space for additional signatures if needed -->
            </div>
            <div class="signature-right">
                <div class="signature-title">
                    Ketua Program Studi <?= $publikasi->nama_prodi ?>
                </div>
                <div class="signature-name">
                    <?= isset($publikasi->nama_kaprodi) ? $publikasi->nama_kaprodi : '(..............................)' ?>
                </div>
                <?php if (isset($publikasi->nip_kaprodi) && !empty($publikasi->nip_kaprodi)): ?>
                <div class="signature-nip">
                    NIP. <?= $publikasi->nip_kaprodi ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Dokumen ini dibuat secara otomatis oleh Sistem Informasi Manajemen Tugas Akhir<br>
        STK Santo Yakobus Merauke - <?= date('Y') ?><br>
        Digenerate pada: <?= date('d F Y H:i:s') ?> WIT
    </div>

    <!-- Auto Print Script (optional - dapat dihapus jika tidak diperlukan) -->
    <script class="no-print">
        // Auto print when page loads (optional)
        // window.onload = function() { 
        //     window.print(); 
        // };

        // Print button functionality
        function printDocument() {
            window.print();
        }
        
        // Add print button (optional)
        if (!window.matchMedia || !window.matchMedia('print').matches) {
            document.body.insertAdjacentHTML('afterbegin', 
                '<div class="no-print" style="position:fixed;top:10px;right:10px;z-index:1000;">' +
                '<button onclick="printDocument()" style="padding:10px;background:#007bff;color:white;border:none;border-radius:5px;cursor:pointer;">' +
                '🖨️ Cetak Dokumen</button></div>'
            );
        }
    </script>
</body>
</html>