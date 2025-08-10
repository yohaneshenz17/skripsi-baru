<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            color: #7f8c8d;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h3 {
            background-color: #3498db;
            color: white;
            padding: 8px 12px;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 5px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .info-table td.label {
            background-color: #f8f9fa;
            font-weight: bold;
            width: 30%;
        }
        .status-box {
            background-color: #f8f9fa;
            border: 2px solid #dee2e6;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
            border-radius: 5px;
        }
        .status-approved {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .status-rejected {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .timeline {
            margin-top: 20px;
        }
        .timeline-item {
            margin-bottom: 15px;
            padding-left: 25px;
            position: relative;
        }
        .timeline-item:before {
            content: "●";
            position: absolute;
            left: 0;
            top: 2px;
            color: #3498db;
            font-size: 16px;
        }
        .timeline-date {
            font-weight: bold;
            color: #2c3e50;
        }
        .timeline-desc {
            color: #7f8c8d;
            margin-top: 2px;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SISTEM INFORMASI MANAJEMEN TUGAS AKHIR</h1>
        <h2>STK SANTO YAKOBUS MERAUKE</h2>
        <p style="margin: 10px 0 0 0; font-size: 11px;">
            Detail Riwayat Usulan Proposal - Dicetak pada: <?= date('d F Y, H:i') ?> WIT
        </p>
    </div>

    <!-- Status Respon -->
    <div class="status-box <?= ($proposal->status_pembimbing == '1') ? 'status-approved' : 'status-rejected' ?>">
        <h3 style="margin: 0; font-size: 16px;">
            STATUS RESPON: <?= ($proposal->status_pembimbing == '1') ? 'DISETUJUI' : 'DITOLAK' ?>
        </h3>
        <p style="margin: 5px 0 0 0;">
            Tanggal Respon: <?= date('d F Y, H:i', strtotime($proposal->tanggal_respon_pembimbing)) ?> WIT
        </p>
    </div>

    <!-- Data Mahasiswa -->
    <div class="info-section">
        <h3>DATA MAHASISWA</h3>
        <table class="info-table">
            <tr>
                <td class="label">Nama Lengkap</td>
                <td><?= $proposal->nama_mahasiswa ?></td>
            </tr>
            <tr>
                <td class="label">NIM</td>
                <td><?= $proposal->nim ?></td>
            </tr>
            <tr>
                <td class="label">Program Studi</td>
                <td><?= $proposal->nama_prodi ?></td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td><?= $proposal->email_mahasiswa ?></td>
            </tr>
            <tr>
                <td class="label">No. Telepon</td>
                <td><?= $proposal->nomor_telepon ?? '-' ?></td>
            </tr>
            <tr>
                <td class="label">Tempat, Tanggal Lahir</td>
                <td><?= $proposal->tempat_lahir ?>, <?= date('d F Y', strtotime($proposal->tanggal_lahir)) ?></td>
            </tr>
            <tr>
                <td class="label">Jenis Kelamin</td>
                <td><?= ($proposal->jenis_kelamin == 'L') ? 'Laki-laki' : 'Perempuan' ?></td>
            </tr>
            <tr>
                <td class="label">Alamat</td>
                <td><?= $proposal->alamat ?></td>
            </tr>
        </table>
    </div>

    <!-- Detail Proposal -->
    <div class="info-section">
        <h3>DETAIL PROPOSAL SKRIPSI</h3>
        <table class="info-table">
            <tr>
                <td class="label">ID Proposal</td>
                <td>#<?= str_pad($proposal->id, 4, '0', STR_PAD_LEFT) ?></td>
            </tr>
            <tr>
                <td class="label">Judul Proposal</td>
                <td style="font-weight: bold;"><?= $proposal->judul ?></td>
            </tr>
            <?php if(!empty($proposal->deskripsi)): ?>
            <tr>
                <td class="label">Deskripsi/Abstrak</td>
                <td><?= nl2br(htmlspecialchars($proposal->deskripsi)) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="label">Dosen Pembimbing</td>
                <td><?= $proposal->nama_dosen_pembimbing ?></td>
            </tr>
            <tr>
                <td class="label">Kaprodi</td>
                <td><?= $proposal->nama_kaprodi ?></td>
            </tr>
        </table>
    </div>

    <!-- Respon Pembimbing -->
    <div class="info-section">
        <h3>RESPON DOSEN PEMBIMBING</h3>
        <table class="info-table">
            <tr>
                <td class="label">Tanggal Respon</td>
                <td><?= date('d F Y, H:i', strtotime($proposal->tanggal_respon_pembimbing)) ?> WIT</td>
            </tr>
            <tr>
                <td class="label">Keputusan</td>
                <td style="font-weight: bold; color: <?= ($proposal->status_pembimbing == '1') ? '#28a745' : '#dc3545' ?>;">
                    <?= ($proposal->status_pembimbing == '1') ? 'DISETUJUI' : 'DITOLAK' ?>
                </td>
            </tr>
            <?php if($proposal->status_pembimbing == '2' && !empty($proposal->komentar_pembimbing)): ?>
            <tr>
                <td class="label">Alasan Penolakan</td>
                <td><?= nl2br(htmlspecialchars($proposal->komentar_pembimbing)) ?></td>
            </tr>
            <?php elseif($proposal->status_pembimbing == '1' && !empty($proposal->komentar_pembimbing)): ?>
            <tr>
                <td class="label">Catatan</td>
                <td><?= nl2br(htmlspecialchars($proposal->komentar_pembimbing)) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Timeline -->
    <div class="info-section">
        <h3>TIMELINE AKTIVITAS</h3>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-date"><?= date('d F Y, H:i', strtotime($proposal->tanggal_pengajuan)) ?> WIT</div>
                <div class="timeline-desc">Proposal diajukan oleh mahasiswa</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date"><?= date('d F Y, H:i', strtotime($proposal->tanggal_disetujui)) ?> WIT</div>
                <div class="timeline-desc">Proposal disetujui oleh Kaprodi (<?= $proposal->nama_kaprodi ?>)</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date"><?= date('d F Y, H:i', strtotime($proposal->tanggal_penetapan)) ?> WIT</div>
                <div class="timeline-desc">Dosen pembimbing ditetapkan</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date"><?= date('d F Y, H:i', strtotime($proposal->tanggal_respon_pembimbing)) ?> WIT</div>
                <div class="timeline-desc">
                    <strong>Respon dosen pembimbing: <?= ($proposal->status_pembimbing == '1') ? 'DISETUJUI' : 'DITOLAK' ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            <strong>STK Santo Yakobus Merauke</strong><br>
            Jl. Missi 2, Mandala, Merauke, Papua Selatan<br>
            Telp: (0971) 333-0264 | Email: sipd@stkyakobus.ac.id<br>
            <br>
            <em>Dokumen ini dicetak dari Sistem Informasi Manajemen Tugas Akhir</em>
        </p>
    </div>

    <script>
        // Auto print saat halaman dimuat
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>