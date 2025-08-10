<?php
/**
 * Simple Print View untuk Detail Seminar
 * 
 * File: application/views/dosen/daftar_penguji/print_detail.php
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail <?= ucfirst($type) ?> - <?= $seminar->nama_mahasiswa ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .info-table td {
            padding: 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        .info-table .label {
            width: 200px;
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        @page { 
            size: A4; 
            margin: 2cm; 
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="header">
        <h2>Detail Seminar <?= ucfirst($type) ?></h2>
        <h3>Sekolah Tinggi Katolik Santo Yakobus Merauke</h3>
    </div>
    
    <table class="info-table">
        <tr>
            <td class="label">Nama Mahasiswa</td>
            <td><?= $seminar->nama_mahasiswa ?></td>
        </tr>
        <tr>
            <td class="label">NIM</td>
            <td><?= $seminar->nim ?></td>
        </tr>
        <tr>
            <td class="label">Program Studi</td>
            <td><?= $seminar->nama_prodi ?></td>
        </tr>
        <tr>
            <td class="label">Judul</td>
            <td><?= $seminar->judul ?></td>
        </tr>
        <tr>
            <td class="label">Pembimbing</td>
            <td><?= $seminar->nama_pembimbing ?></td>
        </tr>
        <?php if ($seminar->tanggal_seminar): ?>
        <tr>
            <td class="label">Tanggal Seminar</td>
            <td><?= date('d F Y', strtotime($seminar->tanggal_seminar)) ?></td>
        </tr>
        <tr>
            <td class="label">Waktu</td>
            <td><?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIT</td>
        </tr>
        <tr>
            <td class="label">Tempat</td>
            <td><?= $seminar->tempat_seminar ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="label">Status</td>
            <td><?= ucfirst($seminar->status) ?></td>
        </tr>
        <tr>
            <td class="label">Dicetak pada</td>
            <td><?= $tanggal_cetak ?></td>
        </tr>
    </table>
    
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>