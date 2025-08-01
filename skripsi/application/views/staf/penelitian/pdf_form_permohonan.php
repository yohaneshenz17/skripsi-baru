<!DOCTYPE html>
<html>
<head>
    <title>Form Permohonan Izin Penelitian</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 15px; }
        .title { font-size: 16px; font-weight: bold; margin: 10px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .table td { padding: 8px; vertical-align: top; }
        .bold { font-weight: bold; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">SEKOLAH TINGGI KATOLIK SANTO YAKOBUS MERAUKE</div>
        <div style="font-size: 14px;">FORM PERMOHONAN IZIN PENELITIAN</div>
        <div style="font-size: 12px;">Tahun Akademik 2024/2025</div>
    </div>

    <table class="table">
        <tr><td style="width: 25%;">Nama Mahasiswa</td><td>:</td><td class="bold"><?= strtoupper($permohonan->nama_mahasiswa) ?></td></tr>
        <tr><td>NIM</td><td>:</td><td class="bold"><?= $permohonan->nim ?></td></tr>
        <tr><td>Semester</td><td>:</td><td class="bold"><?= $permohonan->semester ?></td></tr>
        <tr><td>Program Studi</td><td>:</td><td class="bold"><?= $permohonan->program_studi ?></td></tr>
        <tr><td>Dosen Pembimbing</td><td>:</td><td class="bold"><?= $permohonan->nama_pembimbing ?></td></tr>
        <tr><td>Judul Penelitian</td><td>:</td><td class="bold"><?= $permohonan->judul_skripsi_terbaru ?></td></tr>
        <tr><td>Lokasi Penelitian</td><td>:</td><td class="bold"><?= $permohonan->tempat_penelitian ?></td></tr>
        <tr><td>Periode Penelitian</td><td>:</td><td class="bold">
            <?= date('d F Y', strtotime($permohonan->tanggal_mulai_penelitian)) ?> s/d 
            <?= date('d F Y', strtotime($permohonan->tanggal_selesai_penelitian)) ?></td></tr>
    </table>

    <p style="margin-top: 30px; text-align: justify;">
        Dengan ini saya memohon izin untuk melaksanakan penelitian dalam rangka penyusunan skripsi 
        sesuai dengan data di atas. Penelitian akan dilaksanakan sesuai dengan etika penelitian 
        dan tidak akan mengganggu aktivitas normal di lokasi penelitian.
    </p>

    <table style="width: 100%; margin-top: 50px;">
        <tr>
            <td style="width: 50%; text-align: center;">
                <p>Mengetahui,<br>Dosen Pembimbing</p><br><br><br>
                <p style="text-decoration: underline; font-weight: bold;"><?= $permohonan->nama_pembimbing ?></p>
                <p>NIP. <?= $permohonan->nip_pembimbing ?: '_______________' ?></p>
            </td>
            <td style="width: 50%; text-align: center;">
                <p>Merauke, <?= $tanggal_cetak ?><br>Pemohon</p><br><br><br>
                <p style="text-decoration: underline; font-weight: bold;"><?= strtoupper($permohonan->nama_mahasiswa) ?></p>
                <p>NIM. <?= $permohonan->nim ?></p>
            </td>
        </tr>
    </table>

    <div style="margin-top: 40px; border: 1px solid #000; padding: 15px; background: #f9f9f9;">
        <p class="bold">Untuk Keperluan Administrasi:</p>
        <table style="width: 100%; margin-top: 10px;">
            <tr><td style="width: 30%;">Tanggal Dicetak</td><td>:</td><td><?= $tanggal_cetak ?></td></tr>
            <tr><td>Status Permohonan</td><td>:</td><td><?= ucfirst($permohonan->status) ?></td></tr>
            <tr><td>Dicetak Oleh</td><td>:</td><td><?= $dicetak_oleh ?></td></tr>
        </table>
    </div>
</body>
</html>