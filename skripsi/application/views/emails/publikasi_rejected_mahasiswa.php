<?php
// =================================================================
// 3. EMAIL TEMPLATE KE MAHASISWA (KETIKA DOSEN REJECT)
// File: application/views/emails/publikasi_rejected_mahasiswa.php
// =================================================================
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Publikasi Perlu Diperbaiki</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f8f9fa; }
        .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .button { display: inline-block; padding: 12px 24px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .warning-box { background: #f8d7da; border: 1px solid #f1aeb5; border-radius: 5px; padding: 15px; margin: 20px 0; }
        .action-box { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📝 Publikasi Perlu Diperbaiki</h2>
            <p>SIM Tugas Akhir - STK Santo Yakobus Merauke</p>
        </div>
        
        <div class="content">
            <p>Kepada Yth. Saudara/i <strong><?= htmlspecialchars($publikasi->nama_mahasiswa) ?></strong>,</p>
            
            <div class="warning-box">
                <h4 style="color: #721c24; margin-top: 0;">⚠️ Pengajuan Publikasi Perlu Diperbaiki</h4>
                <p style="margin-bottom: 0;">Dosen pembimbing telah melakukan review terhadap pengajuan publikasi Anda dan meminta beberapa perbaikan sebelum dapat disetujui.</p>
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
                        <td style="padding: 5px;"><span style="color: #dc3545; font-weight: bold;">❌ PERLU PERBAIKAN</span></td>
                    </tr>
                </table>
            </div>
            
            <div class="info-box">
                <h4>💬 Catatan dan Saran Perbaikan</h4>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;">
                    <?php if (!empty($komentar)): ?>
                        <p style="font-style: italic; margin: 0;">
                            "<?= nl2br(htmlspecialchars($komentar)) ?>"
                        </p>
                    <?php else: ?>
                        <p style="font-style: italic; margin: 0; color: #666;">
                            Dosen pembimbing belum memberikan komentar spesifik. Silakan hubungi dosen pembimbing untuk penjelasan lebih detail.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="action-box">
                <h4 style="color: #856404; margin-top: 0;">📋 Langkah Selanjutnya</h4>
                <ol style="color: #856404;">
                    <li><strong>Pelajari catatan perbaikan</strong> dari dosen pembimbing dengan seksama</li>
                    <li><strong>Lakukan perbaikan</strong> sesuai dengan saran yang diberikan</li>
                    <li><strong>Persiapkan dokumen terbaru</strong> yang sudah diperbaiki</li>
                    <li><strong>Ajukan ulang publikasi</strong> melalui sistem dengan dokumen yang sudah diperbaiki</li>
                    <li><strong>Konsultasi langsung</strong> dengan dosen pembimbing jika ada hal yang kurang jelas</li>
                </ol>
            </div>
            
            <div style="text-align: center; margin: 20px 0;">
                <p><strong>Ajukan ulang publikasi setelah perbaikan:</strong></p>
                <a href="<?= base_url('mahasiswa/publikasi') ?>" class="button">
                    📝 Ajukan Ulang Publikasi
                </a>
            </div>
            
            <div style="background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; padding: 15px; margin: 20px 0;">
                <h4 style="color: #0c5460; margin-top: 0;">💡 Tips Perbaikan</h4>
                <ul style="color: #0c5460; margin-bottom: 0;">
                    <li>Pastikan semua dokumen sesuai dengan format yang ditetapkan</li>
                    <li>Periksa kembali kelengkapan berkas yang diupload</li>
                    <li>Konsultasikan perbaikan dengan dosen pembimbing</li>
                    <li>Simpan versi dokumen yang sudah diperbaiki dengan baik</li>
                </ul>
            </div>
            
            <p>Jangan berkecil hati! Proses perbaikan adalah bagian normal dalam penyelesaian tugas akhir. Dengan perbaikan yang tepat, publikasi Anda akan dapat disetujui.</p>
            
            <p><strong>Kontak Dosen Pembimbing:</strong><br>
            <?= $dosen_nama ?><br>
            Email: <?= $publikasi->email_pembimbing ?></p>
        </div>
        
        <div class="footer">
            <p>Email otomatis dari Sistem Informasi Manajemen Tugas Akhir<br>
            STK Santo Yakobus Merauke<br>
            <em>Mohon tidak membalas email ini</em></p>
        </div>
    </div>
</body>
</html>