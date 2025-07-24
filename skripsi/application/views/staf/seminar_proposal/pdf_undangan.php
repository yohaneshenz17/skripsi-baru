<?php
// ========================================
// 3. UNDANGAN SEMINAR PROPOSAL PDF TEMPLATE
// File: application/views/staf/seminar_proposal/pdf_undangan.php
// ========================================
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Undangan Seminar Proposal</title>
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
        .undangan-info {
            margin-bottom: 20px;
        }
        .content {
            text-align: justify;
            margin-bottom: 30px;
        }
        .jadwal-info {
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
            width: 120px;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
        }
        .signature {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">UNDANGAN SEMINAR PROPOSAL</div>
        <div class="subtitle">SEKOLAH TINGGI KEGURUAN ST. YAKOBUS</div>
        <div class="subtitle">PROGRAM STUDI <?= strtoupper($proposal->nama_prodi) ?></div>
    </div>

    <div class="undangan-info">
        <div><strong>Nomor: <?= $nomor_undangan ?></strong></div>
        <div>Hal: Undangan Seminar Proposal Tugas Akhir</div>
    </div>

    <div>
        <div>Kepada Yth.</div>
        <div><strong>Dosen Penguji Seminar Proposal</strong></div>
        <div>di Tempat</div>
    </div>

    <div class="content">
        <p>Dengan hormat,</p>
        
        <p>Sehubungan dengan akan dilaksanakannya Seminar Proposal Tugas Akhir mahasiswa Program Studi 
        <?= $proposal->nama_prodi ?>, maka dengan ini kami mengundang Bapak/Ibu untuk hadir sebagai penguji 
        dalam kegiatan tersebut.</p>

        <div class="jadwal-info">
            <div style="text-align: center; font-weight: bold; margin-bottom: 15px;">
                JADWAL SEMINAR PROPOSAL
            </div>
            
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
                <span class="label">Pembimbing</span>: <?= $proposal->nama_pembimbing ?>
            </div>
            <div class="info-row">
                <span class="label">Penguji</span>: <?= $proposal->nama_penguji ?: 'Akan ditentukan' ?>
            </div>
            <div class="info-row">
                <span class="label">Hari/Tanggal</span>: 
                <?= $proposal->tanggal_seminar_proposal ? 
                    date('l, d F Y', strtotime($proposal->tanggal_seminar_proposal)) : 
                    'Akan ditentukan' ?>
            </div>
            <div class="info-row">
                <span class="label">Waktu</span>: 
                <?= $proposal->tanggal_seminar_proposal ? 
                    date('H:i', strtotime($proposal->tanggal_seminar_proposal)) . ' WIB' : 
                    'Akan ditentukan' ?>
            </div>
            <div class="info-row">
                <span class="label">Tempat</span>: <?= $proposal->nama_ruangan ?: 'Akan ditentukan' ?>
            </div>
        </div>

        <p>Demikian undangan ini kami sampaikan, atas perhatian dan kehadiran Bapak/Ibu kami ucapkan terima kasih.</p>
    </div>

    <div class="footer">
        <div>Jakarta, <?= date('d F Y') ?></div>
        <div>Ketua Program Studi <?= $proposal->nama_prodi ?></div>
        <div class="signature">
            <br><br><br>
            <div><u>_______________________</u></div>
            <div>NIP. ___________________</div>
        </div>
    </div>

    <div style="margin-top: 30px; font-size: 10px; color: #666;">
        <em>Dokumen ini digenerate oleh <?= $generated_by ?> pada <?= $generated_at ?></em>
    </div>
</body>
</html>
