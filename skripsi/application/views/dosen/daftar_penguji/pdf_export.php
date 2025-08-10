<?php
/**
 * Template PDF Export untuk Daftar Penguji
 * 
 * File: application/views/dosen/daftar_penguji/pdf_export.php
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Penguji - <?= $dosen->nama ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #7f8c8d;
            font-weight: normal;
        }
        
        .info-section {
            background-color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            width: 150px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .info-value {
            flex: 1;
            color: #34495e;
        }
        
        .section-title {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            margin: 30px 0 15px 0;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th {
            background-color: #2c3e50;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        
        table td {
            padding: 8px;
            border-bottom: 1px solid #bdc3c7;
            font-size: 10px;
            vertical-align: top;
        }
        
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-terjadwal { background-color: #27ae60; color: white; }
        .status-selesai { background-color: #3498db; color: white; }
        .status-proses { background-color: #f39c12; color: white; }
        .status-approved { background-color: #1abc9c; color: white; }
        
        .penguji-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .penguji-1 { background-color: #3498db; color: white; }
        .penguji-2 { background-color: #9b59b6; color: white; }
        
        .summary-stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            display: block;
        }
        
        .stat-label {
            font-size: 10px;
            color: #7f8c8d;
            text-transform: uppercase;
        }
        
        .footer {
            position: fixed;
            bottom: 20px;
            right: 20px;
            font-size: 10px;
            color: #7f8c8d;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-style: italic;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .header {
                border-bottom: 2px solid #2c3e50;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Daftar Tugas Penguji</h1>
        <h2>Sekolah Tinggi Katolik Santo Yakobus Merauke</h2>
    </div>
    
    <!-- Info Dosen -->
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Dosen Penguji:</div>
            <div class="info-value"><?= $dosen->nama ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">NIP:</div>
            <div class="info-value"><?= $dosen->nip ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Periode Laporan:</div>
            <div class="info-value">
                <?php if ($type == 'proposal'): ?>
                    Seminar Proposal
                <?php elseif ($type == 'skripsi'): ?>
                    Seminar Skripsi
                <?php else: ?>
                    Seminar Proposal dan Seminar Skripsi
                <?php endif; ?>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Cetak:</div>
            <div class="info-value"><?= $tanggal_cetak ?></div>
        </div>
    </div>
    
    <!-- Summary Statistics -->
    <div class="summary-stats">
        <div class="stat-item">
            <span class="stat-number">
                <?php 
                $total_proposal = isset($data['proposal']) ? count($data['proposal']) : 0;
                echo $total_proposal;
                ?>
            </span>
            <span class="stat-label">Seminar Proposal</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">
                <?php 
                $total_skripsi = isset($data['skripsi']) ? count($data['skripsi']) : 0;
                echo $total_skripsi;
                ?>
            </span>
            <span class="stat-label">Seminar Skripsi</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= $total_proposal + $total_skripsi ?></span>
            <span class="stat-label">Total Tugas</span>
        </div>
    </div>
    
    <!-- Seminar Proposal Section -->
    <?php if (isset($data['proposal']) && !empty($data['proposal'])): ?>
    <div class="section-title">Daftar Penguji Seminar Proposal</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 20%">Mahasiswa</th>
                <th style="width: 30%">Judul Proposal</th>
                <th style="width: 15%">Jadwal Seminar</th>
                <th style="width: 10%">Posisi</th>
                <th style="width: 10%">Status</th>
                <th style="width: 10%">Pembimbing</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($data['proposal'] as $proposal): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td>
                    <strong><?= $proposal->nama_mahasiswa ?></strong><br>
                    <small><?= $proposal->nim ?></small><br>
                    <small><?= $proposal->nama_prodi ?></small>
                </td>
                <td><?= $proposal->judul ?></td>
                <td>
                    <?php if ($proposal->tanggal_seminar): ?>
                        <?= date('d M Y', strtotime($proposal->tanggal_seminar)) ?><br>
                        <?= date('H:i', strtotime($proposal->jam_seminar)) ?> WIT<br>
                        <small><?= $proposal->tempat_seminar ?></small>
                    <?php else: ?>
                        <em>Belum dijadwalkan</em>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="penguji-badge <?= $proposal->posisi_penguji == 'Penguji 1' ? 'penguji-1' : 'penguji-2' ?>">
                        <?= $proposal->posisi_penguji ?>
                    </span>
                </td>
                <td>
                    <?php
                    $status_class = '';
                    $status_text = '';
                    switch($proposal->status) {
                        case 'scheduled':
                            $status_class = 'status-terjadwal';
                            $status_text = 'Terjadwal';
                            break;
                        case 'completed':
                            $status_class = 'status-selesai';
                            $status_text = 'Selesai';
                            break;
                        case 'approved':
                            $status_class = 'status-approved';
                            $status_text = 'Disetujui';
                            break;
                        default:
                            $status_class = 'status-proses';
                            $status_text = 'Proses';
                    }
                    ?>
                    <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                </td>
                <td><?= $proposal->nama_pembimbing ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php elseif ($type == 'proposal' || $type == 'all'): ?>
    <div class="section-title">Daftar Penguji Seminar Proposal</div>
    <div class="no-data">Tidak ada tugas penguji seminar proposal</div>
    <?php endif; ?>
    
    <!-- Seminar Skripsi Section -->
    <?php if (isset($data['skripsi']) && !empty($data['skripsi'])): ?>
    <?php if (isset($data['proposal']) && !empty($data['proposal'])): ?>
    <div class="page-break"></div>
    <?php endif; ?>
    
    <div class="section-title">Daftar Penguji Seminar Skripsi</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 20%">Mahasiswa</th>
                <th style="width: 30%">Judul Skripsi</th>
                <th style="width: 15%">Jadwal Seminar</th>
                <th style="width: 10%">Posisi</th>
                <th style="width: 10%">Status</th>
                <th style="width: 10%">Pembimbing</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($data['skripsi'] as $skripsi): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td>
                    <strong><?= $skripsi->nama_mahasiswa ?></strong><br>
                    <small><?= $skripsi->nim ?></small><br>
                    <small><?= $skripsi->nama_prodi ?></small>
                </td>
                <td><?= $skripsi->judul ?></td>
                <td>
                    <?php if ($skripsi->tanggal_seminar): ?>
                        <?= date('d M Y', strtotime($skripsi->tanggal_seminar)) ?><br>
                        <?= date('H:i', strtotime($skripsi->jam_seminar)) ?> WIT<br>
                        <small><?= $skripsi->tempat_seminar ?></small>
                    <?php else: ?>
                        <em>Belum dijadwalkan</em>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="penguji-badge <?= $skripsi->posisi_penguji == 'Penguji 1' ? 'penguji-1' : 'penguji-2' ?>">
                        <?= $skripsi->posisi_penguji ?>
                    </span>
                </td>
                <td>
                    <?php
                    $status_class = '';
                    $status_text = '';
                    switch($skripsi->status) {
                        case 'scheduled':
                            $status_class = 'status-terjadwal';
                            $status_text = 'Terjadwal';
                            break;
                        case 'completed':
                            $status_class = 'status-selesai';
                            $status_text = 'Selesai';
                            break;
                        case 'approved':
                            $status_class = 'status-approved';
                            $status_text = 'Disetujui';
                            break;
                        default:
                            $status_class = 'status-proses';
                            $status_text = 'Proses';
                    }
                    ?>
                    <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                </td>
                <td><?= $skripsi->nama_pembimbing ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php elseif ($type == 'skripsi' || $type == 'all'): ?>
    <div class="section-title">Daftar Penguji Seminar Skripsi</div>
    <div class="no-data">Tidak ada tugas penguji seminar skripsi</div>
    <?php endif; ?>
    
    <!-- Footer -->
    <div class="footer">
        Dicetak pada: <?= $tanggal_cetak ?> | SIM-TA STK Santo Yakobus
    </div>
</body>
</html>