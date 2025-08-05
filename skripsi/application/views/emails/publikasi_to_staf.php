<?php
// =================================================================
// 1. EMAIL TEMPLATE KE STAF (KETIKA DOSEN APPROVE)
// File: application/views/emails/publikasi_to_staf.php
// =================================================================
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Publikasi Siap Validasi</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f8f9fa; }
        .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; }
        .button { display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🎓 Publikasi Tugas Akhir Siap Validasi</h2>
            <p>SIM Tugas Akhir - STK Santo Yakobus Merauke</p>
        </div>
        
        <div class="content">
            <p>Kepada Yth. Tim Staf Akademik,</p>
            
            <p>Publikasi tugas akhir mahasiswa berikut telah <strong>DISETUJUI</strong> oleh dosen pembimbing dan siap untuk tahap validasi staf:</p>
            
            <div class="info-box">
                <h4>📋 Detail Mahasiswa</h4>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 5px; font-weight: bold;">Nama Mahasiswa:</td>
                        <td style="padding: 5px;"><?= htmlspecialchars($publikasi->nama_mahasiswa) ?></td>
                    </tr>
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
                <h4>👨‍🏫 Detail Review Dosen</h4>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 5px; font-weight: bold;">Dosen Pembimbing:</td>
                        <td style="padding: 5px;"><?= $dosen_nama ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px; font-weight: bold;">Tanggal Approval:</td>
                        <td style="padding: 5px;"><?= date('d/m/Y H:i', strtotime($publikasi->tanggal_review_pembimbing)) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px; font-weight: bold;">Status:</td>
                        <td style="padding: 5px;"><span style="color: #28a745; font-weight: bold;">✅ DISETUJUI</span></td>
                    </tr>
                </table>
            </div>
            
            <div class="info-box">
                <h4>📄 Dokumen yang Diupload</h4>
                <ul>
                    <?php if (!empty($publikasi->file_skripsi_final)): ?>
                        <li>📖 Skripsi Final: <?= $publikasi->file_skripsi_final ?></li>
                    <?php endif; ?>
                    <?php if (!empty($publikasi->file_surat_revisi)): ?>
                        <li>📋 Surat Revisi: <?= $publikasi->file_surat_revisi ?></li>
                    <?php endif; ?>
                    <?php if (!empty($publikasi->file_surat_perpustakaan)): ?>
                        <li>📚 Surat Perpustakaan: <?= $publikasi->file_surat_perpustakaan ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div style="text-align: center; margin: 20px 0;">
                <p><strong>Silakan lakukan validasi publikasi melalui sistem:</strong></p>
                <a href="<?= base_url('staf/publikasi/validasi/' . $publikasi->id) ?>" class="button">
                    🔍 Validasi Publikasi Sekarang
                </a>
            </div>
            
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0;">
                <p><strong>⚠️ Tindakan Selanjutnya:</strong></p>
                <ol>
                    <li>Review dokumen yang diupload mahasiswa</li>
                    <li>Validasi kelengkapan dan kesesuaian dokumen</li>
                    <li>Input link repository publikasi</li>
                    <li>Selesaikan proses publikasi</li>
                </ol>
            </div>
        </div>
        
        <div class="footer">
            <p>Email otomatis dari Sistem Informasi Manajemen Tugas Akhir<br>
            STK Santo Yakobus Merauke<br>
            <em>Mohon tidak membalas email ini</em></p>
        </div>
    </div>
</body>
</html>