<?php
// =================================================================
// 2. EMAIL TEMPLATE KE MAHASISWA (KETIKA DOSEN APPROVE)
// File: application/views/emails/publikasi_approved_mahasiswa.php
// =================================================================
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Publikasi Disetujui Dosen Pembimbing</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f8f9fa; }
        .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #28a745; }
        .button { display: inline-block; padding: 12px 24px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .success-box { background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🎉 Selamat! Publikasi Disetujui</h2>
            <p>SIM Tugas Akhir - STK Santo Yakobus Merauke</p>
        </div>
        
        <div class="content">
            <p>Kepada Yth. Saudara/i <strong><?= htmlspecialchars($publikasi->nama_mahasiswa) ?></strong>,</p>
            
            <div class="success-box">
                <h4 style="color: #155724; margin-top: 0;">✅ Publikasi Anda Telah Disetujui!</h4>
                <p style="margin-bottom: 0;">Dosen pembimbing telah menyetujui pengajuan publikasi tugas akhir Anda. Proses selanjutnya adalah validasi oleh tim staf akademik.</p>
            </div>
            
            <div class="info-box">
                <h4>📋 Detail Pengajuan</h4>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 5px; font-weight: bold;">NIM:</td>
                        <td style="padding: 5px;"><?= $publikasi->nim ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px; font-weight: bold;">Program Studi:</td>
                        <td style="padding: 5px;"><?= $publikasi->nama_prodi ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px; font-weight: bold;">Judul Skripsi:</td>
                        <td style="padding: 5px;"><?= htmlspecialchars($publikasi->judul_skripsi) ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="info-box">
                <h4>👨‍🏫 Review Dosen Pembimbing</h4>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 5px; font-weight: bold;">Dosen Pembimbing:</td>
                        <td style="padding: 5px;"><?= $dosen_nama ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px; font-weight: bold;">Tanggal Review:</td>
                        <td style="padding: 5px;"><?= date('d/m/Y H:i', strtotime($publikasi->tanggal_review_pembimbing)) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px; font-weight: bold;">Status:</td>
                        <td style="padding: 5px;"><span style="color: #28a745; font-weight: bold;">✅ DISETUJUI</span></td>
                    </tr>
                </table>
            </div>
            
            <?php if (!empty($publikasi->komentar_pembimbing)): ?>
                <div class="info-box">
                    <h4>💬 Komentar Dosen</h4>
                    <p style="font-style: italic; background: #f8f9fa; padding: 10px; border-radius: 5px;">
                        "<?= nl2br(htmlspecialchars($publikasi->komentar_pembimbing)) ?>"
                    </p>
                </div>
            <?php endif; ?>
            
            <div style="background: #cce5ff; border: 1px solid #99d6ff; border-radius: 5px; padding: 15px; margin: 20px 0;">
                <h4 style="color: #004085; margin-top: 0;">📋 Tahap Selanjutnya</h4>
                <p><strong>Publikasi Anda sekarang dalam tahap validasi staf.</strong></p>
                <ol style="color: #004085;">
                    <li>Tim staf akan memvalidasi dokumen yang Anda upload</li>
                    <li>Staf akan menginput link repository publikasi</li>
                    <li>Setelah validasi selesai, publikasi Anda akan selesai</li>
                    <li>Anda akan mendapat notifikasi email saat proses selesai</li>
                </ol>
            </div>
            
            <div style="text-align: center; margin: 20px 0;">
                <p><strong>Pantau status publikasi Anda:</strong></p>
                <a href="<?= base_url('mahasiswa/publikasi') ?>" class="button">
                    📊 Cek Status Publikasi
                </a>
            </div>
            
            <p>Terima kasih atas kesabaran Anda dalam proses publikasi tugas akhir. Jika ada pertanyaan, silakan hubungi bagian akademik.</p>
        </div>
        
        <div class="footer">
            <p>Email otomatis dari Sistem Informasi Manajemen Tugas Akhir<br>
            STK Santo Yakobus Merauke<br>
            <em>Mohon tidak membalas email ini</em></p>
        </div>
    </div>
</body>
</html>