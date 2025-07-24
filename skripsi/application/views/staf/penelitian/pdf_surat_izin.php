<?php
// ========================================
// PDF SURAT IZIN PENELITIAN TEMPLATE
// File: application/views/staf/penelitian/pdf_surat_izin.php
// ========================================
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Izin Penelitian - <?= $penelitian->nama_mahasiswa ?></title>
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
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
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
        .alamat {
            font-size: 11px;
            margin: 5px 0;
        }
        .surat-info {
            margin-bottom: 20px;
        }
        .content {
            text-align: justify;
            margin-bottom: 30px;
        }
        .mahasiswa-info {
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
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
        .signature-section {
            margin-top: 40px;
            text-align: right;
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
        .footer-info {
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <!-- Logo bisa ditambahkan di sini -->
        <div class="title">SEKOLAH TINGGI KEGURUAN ST. YAKOBUS</div>
        <div class="subtitle">PROGRAM STUDI <?= strtoupper($penelitian->nama_prodi) ?></div>
        <div class="alamat">Jl. Danau Sunter Utara Blok E3 No. 10, Sunter, Jakarta Utara 14350</div>
        <div class="alamat">Telp: (021) 6404531, Fax: (021) 6404532</div>
        <div class="alamat">Email: info@stkyakobus.ac.id, Website: www.stkyakobus.ac.id</div>
    </div>

    <div class="surat-info">
        <div><strong>Nomor: <?= $nomor_surat ?></strong></div>
        <div>Hal: Surat Izin Penelitian Tugas Akhir</div>
        <div>Lampiran: -</div>
    </div>

    <div>
        <div>Kepada Yth.</div>
        <div><strong>Pimpinan/Kepala Instansi/Perusahaan</strong></div>
        <div>di Tempat</div>
    </div>

    <div class="content">
        <p>Dengan hormat,</p>
        
        <p>Sehubungan dengan pelaksanaan Tugas Akhir mahasiswa Program Studi <?= $penelitian->nama_prodi ?> 
        Sekolah Tinggi Keguruan St. Yakobus, maka dengan ini kami memohon izin kepada Bapak/Ibu untuk 
        memberikan kesempatan kepada mahasiswa kami untuk melakukan penelitian di instansi yang Bapak/Ibu pimpin.</p>

        <div class="mahasiswa-info">
            <div style="text-align: center; font-weight: bold; margin-bottom: 15px;">
                DATA MAHASISWA
            </div>
            
            <div class="info-row">
                <span class="label">Nama</span>: <?= $penelitian->nama_mahasiswa ?>
            </div>
            <div class="info-row">
                <span class="label">NIM</span>: <?= $penelitian->nim ?>
            </div>
            <div class="info-row">
                <span class="label">Program Studi</span>: <?= $penelitian->nama_prodi ?>
            </div>
            <div class="info-row">
                <span class="label">Judul Penelitian</span>: <?= $penelitian->judul ?>
            </div>
            <div class="info-row">
                <span class="label">Dosen Pembimbing</span>: <?= $penelitian->nama_pembimbing ?>
            </div>
            <div class="info-row">
                <span class="label">Email Mahasiswa</span>: <?= $penelitian->email ?>
            </div>
            <div class="info-row">
                <span class="label">No. Telepon</span>: <?= $penelitian->nomor_telepon ?>
            </div>
        </div>

        <p>Adapun penelitian ini dilaksanakan dalam rangka penyusunan Tugas Akhir sebagai salah satu syarat 
        untuk menyelesaikan studi pada Program Studi <?= $penelitian->nama_prodi ?> Sekolah Tinggi Keguruan St. Yakobus.</p>

        <p>Penelitian ini direncanakan akan dilaksanakan pada <strong>__________________</strong> 
        sampai dengan <strong>__________________</strong> dengan ketentuan sebagai berikut:</p>

        <ol>
            <li>Mahasiswa akan mematuhi semua peraturan dan tata tertib yang berlaku di instansi Bapak/Ibu.</li>
            <li>Data dan informasi yang diperoleh hanya akan digunakan untuk kepentingan akademis dan penulisan Tugas Akhir.</li>
            <li>Mahasiswa akan menjaga kerahasiaan data dan informasi yang diperoleh dari instansi.</li>
            <li>Hasil penelitian akan diserahkan kepada instansi sebagai bentuk apresiasi atas kerja sama yang terjalin.</li>
        </ol>

        <p>Demikian surat permohonan ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu, 
        kami ucapkan terima kasih.</p>
    </div>

    <div class="signature-section">
        <div>Jakarta, <?= date('d F Y') ?></div>
        <div>Ketua Program Studi <?= $penelitian->nama_prodi ?></div>
        <br><br><br><br>
        <div><u>_______________________</u></div>
        <div>NIP. ___________________</div>
    </div>

    <div style="margin-top: 30px;">
        <div><strong>Tembusan:</strong></div>
        <ol>
            <li>Ketua STK St. Yakobus</li>
            <li>Dosen Pembimbing</li>
            <li>Mahasiswa yang bersangkutan</li>
            <li>Arsip</li>
        </ol>
    </div>

    <div class="footer-info">
        <em>Dokumen ini digenerate oleh <?= $generated_by ?> pada <?= $generated_at ?></em>
    </div>
</body>
</html>