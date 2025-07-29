<?php
// ================================================================
// UPDATED FILE: application/views/staf/seminar_proposal/pdf_berita_acara.php
// Update bagian hasil table untuk sistem 3 dosen
// ================================================================
?>

<!-- Update table hasil seminar -->
<table class="hasil-table">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="25%">Aspek Penilaian</th>
            <th width="20%">Nilai Penguji 1</th>
            <th width="20%">Nilai Penguji 2</th>
            <th width="20%">Nilai Pembimbing</th>
            <th width="10%">Rata-rata</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align: center;">1</td>
            <td>Sistematika Penulisan</td>
            <td style="text-align: center;">
                <?= isset($penilaian->nilai_penguji1) ? number_format($penilaian->nilai_penguji1, 1) : '_______' ?>
            </td>
            <td style="text-align: center;">
                <?= isset($penilaian->nilai_penguji2) ? number_format($penilaian->nilai_penguji2, 1) : '_______' ?>
            </td>
            <td style="text-align: center;">
                <?= isset($penilaian->nilai_pembimbing) ? number_format($penilaian->nilai_pembimbing, 1) : '_______' ?>
            </td>
            <td style="text-align: center;">
                <?= isset($penilaian->nilai_akhir) ? number_format($penilaian->nilai_akhir, 1) : '_______' ?>
            </td>
        </tr>
        <!-- Baris lainnya tetap serupa dengan struktur yang sama -->
        <tr style="background-color: #f0f0f0;">
            <td colspan="2" style="text-align: center; font-weight: bold;">NILAI AKHIR</td>
            <td style="text-align: center; font-weight: bold;">
                <?= isset($penilaian->nilai_penguji1) ? number_format($penilaian->nilai_penguji1, 1) : '_______' ?>
            </td>
            <td style="text-align: center; font-weight: bold;">
                <?= isset($penilaian->nilai_penguji2) ? number_format($penilaian->nilai_penguji2, 1) : '_______' ?>
            </td>
            <td style="text-align: center; font-weight: bold;">
                <?= isset($penilaian->nilai_pembimbing) ? number_format($penilaian->nilai_pembimbing, 1) : '_______' ?>
            </td>
            <td style="text-align: center; font-weight: bold;">
                <?php if (isset($penilaian->nilai_akhir)): ?>
                    <?= number_format($penilaian->nilai_akhir, 1) ?> (<?= $penilaian->nilai_huruf ?>)
                <?php else: ?>
                    _______
                <?php endif; ?>
            </td>
        </tr>
    </tbody>
</table>

<div style="margin-top: 10px; font-size: 12px;">
    <strong>Catatan:</strong> Nilai Akhir = (Nilai Penguji 1 + Nilai Penguji 2 + Nilai Pembimbing) ÷ 3
</div>

<!-- Signature section update -->
<div class="signature-section">
    <p>Demikian Berita Acara ini dibuat dengan sebenarnya.</p>
    
    <table class="signature-table">
        <tr>
            <td width="33%">
                <strong>Dosen Penguji 1</strong>
                <br><br><br><br>
                <u><?= $proposal->nama_penguji1 ?: '______________________' ?></u><br>
                NIP. <?= $proposal->nip_penguji1 ?: '________________' ?>
            </td>
            <td width="33%">
                <strong>Dosen Penguji 2</strong>
                <br><br><br><br>
                <u><?= $proposal->nama_penguji2 ?: '______________________' ?></u><br>
                NIP. <?= $proposal->nip_penguji2 ?: '________________' ?>
            </td>
            <td width="33%">
                <strong>Dosen Pembimbing</strong>
                <br><br><br><br>
                <u><?= $proposal->nama_pembimbing ?: '______________________' ?></u><br>
                NIP. <?= $proposal->nip_pembimbing ?: '________________' ?>
            </td>
        </tr>
    </table>
</div>