<!DOCTYPE html>
<html>
<head>
    <title>Template Surat Izin Penelitian</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.6; }
        .template-note { background: #e3f2fd; border: 2px solid #2196f3; padding: 20px; margin: 20px 0; }
        .field { background: #fff3e0; border: 1px dashed #ff9800; padding: 5px 10px; display: inline-block; }
        .bold { font-weight: bold; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="template-note">
        <h3 class="text-center bold">TEMPLATE SURAT IZIN PENELITIAN</h3>
        <p class="text-center">STK Santo Yakobus Merauke</p>
        <hr>
        <p><strong>Petunjuk Penggunaan:</strong></p>
        <ul>
            <li>Template ini dapat digunakan untuk membuat surat izin penelitian</li>
            <li>Bagian yang berwarna orange adalah field yang harus diisi sesuai data mahasiswa</li>
            <li>Setelah diisi, template ini dapat dicetak dan ditandatangani oleh pimpinan</li>
            <li>Scan/foto hasil surat yang sudah ditandatangani kemudian upload kembali ke sistem</li>
        </ul>
        <p><strong>Generated:</strong> <?= $tanggal_template ?> oleh <?= $generated_by ?></p>
    </div>

    <div style="text-align: center; margin-bottom: 30px; border-bottom: 3px solid #000; padding-bottom: 15px;">
        <h2>SEKOLAH TINGGI KATOLIK SANTO YAKOBUS MERAUKE</h2>
        <p>Jl. Brawijaya No. 3 Merauke 99611<br>Telp. (0971) 321362, Email: info@stkyakobus.ac.id</p>
    </div>

    <div style="margin-bottom: 30px;">
        <table style="width: 100%;">
            <tr>
                <td>Nomor</td><td>:</td><td><span class="field">[NOMOR SURAT]</span></td>
                <td style="text-align: right;">Merauke, <span class="field">[TANGGAL]</span></td>
            </tr>
            <tr><td>Hal</td><td>:</td><td class="bold">Izin Penelitian</td><td></td></tr>
        </table>
    </div>

    <div style="margin-bottom: 20px;">
        <p>Kepada Yth.<br><span class="bold">Pimpinan/Kepala Instansi<br><span class="field">[TEMPAT PENELITIAN]</span></span><br>di Tempat</p>
    </div>

    <p>Dengan hormat,</p>

    <div style="margin-left: 30px; text-align: justify;">
        <p>Dalam rangka penyelesaian tugas akhir (skripsi) sebagai salah satu persyaratan untuk memperoleh gelar Sarjana di Sekolah Tinggi Katolik Santo Yakobus Merauke, bersama ini kami sampaikan permohonan izin penelitian untuk mahasiswa:</p>

        <table style="width: 100%; margin: 20px 0;">
            <tr><td style="width: 25%;">Nama</td><td>:</td><td><span class="field">[NAMA MAHASISWA]</span></td></tr>
            <tr><td>NIM</td><td>:</td><td><span class="field">[NIM]</span></td></tr>
            <tr><td>Program Studi</td><td>:</td><td><span class="field">[PROGRAM STUDI]</span></td></tr>
            <tr><td>Judul Penelitian</td><td>:</td><td><span class="field">[JUDUL SKRIPSI]</span></td></tr>
            <tr><td>Dosen Pembimbing</td><td>:</td><td><span class="field">[NAMA DOSEN PEMBIMBING]</span></td></tr>
            <tr><td>Waktu Penelitian</td><td>:</td><td><span class="field">[TANGGAL MULAI - SELESAI]</span></td></tr>
        </table>

        <p>Demikian surat permohonan ini kami sampaikan. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>
    </div>

    <table style="width: 100%; margin-top: 50px;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; text-align: center;">
                <p>Hormat kami,<br><span class="bold">Ketua STK Santo Yakobus Merauke</span></p>
                <br><br><br><br>
                <p class="bold">(...............................)</p>
                <p style="font-size: 10px; color: #666;">[TTD dan Cap Resmi]</p>
            </td>
        </tr>
    </table>
</body>
</html>