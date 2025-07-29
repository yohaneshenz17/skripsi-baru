
<?php
// ================================================================
// UPDATED FILE: application/views/staf/seminar_proposal/pdf_form_penilaian.php
// Update bagian table penilaian untuk menggunakan sistem 3 dosen
// ================================================================
?>

<div class="header">
    <div>SEKOLAH TINGGI KEGURUAN DAN ILMU PENDIDIKAN SANTO YAKOBUS</div>
    <div>PROGRAM STUDI <?= strtoupper($proposal->nama_prodi) ?></div>
</div>

<div class="info-mahasiswa">
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
        <span class="label">Tanggal Seminar</span>: 
        <?= $proposal->tanggal_seminar_proposal ? 
            date('d F Y', strtotime($proposal->tanggal_seminar_proposal)) : 
            '______________________' ?>
    </div>
</div>

<!-- UPDATED TABLE - Sistem Penilaian 3 Dosen -->
<table class="penilaian-table">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="40%">Aspek Penilaian</th>
            <th width="15%">Penguji 1</th>
            <th width="15%">Penguji 2</th>
            <th width="15%">Pembimbing</th>
            <th width="10%">Catatan</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="score-cell">1</td>
            <td><strong>Sistematika Penulisan</strong><br>
                - Format penulisan sesuai panduan<br>
                - Kelengkapan bab dan sub bab<br>
                - Konsistensi penulisan</td>
            <td class="score-cell">
                <?= isset($penilaian->nilai_penguji1) ? number_format($penilaian->nilai_penguji1, 1) : '_____' ?>
            </td>
            <td class="score-cell">
                <?= isset($penilaian->nilai_penguji2) ? number_format($penilaian->nilai_penguji2, 1) : '_____' ?>
            </td>
            <td class="score-cell">
                <?= isset($penilaian->nilai_pembimbing) ? number_format($penilaian->nilai_pembimbing, 1) : '_____' ?>
            </td>
            <td>_____</td>
        </tr>
        <tr>
            <td class="score-cell">2</td>
            <td><strong>Latar Belakang dan Perumusan Masalah</strong><br>
                - Kejelasan latar belakang<br>
                - Ketepatan rumusan masalah<br>
                - Kesesuaian tujuan penelitian</td>
            <td class="score-cell">_____</td>
            <td class="score-cell">_____</td>
            <td class="score-cell">_____</td>
            <td>_____</td>
        </tr>
        <tr>
            <td class="score-cell">3</td>
            <td><strong>Tinjauan Pustaka</strong><br>
                - Kesesuaian referensi<br>
                - Kemutakhiran sumber<br>
                - Analisis dan sintesis teori</td>
            <td class="score-cell">_____</td>
            <td class="score-cell">_____</td>
            <td class="score-cell">_____</td>
            <td>_____</td>
        </tr>
        <tr>
            <td class="score-cell">4</td>
            <td><strong>Metodologi Penelitian</strong><br>
                - Ketepatan metode<br>
                - Kejelasan prosedur<br>
                - Kelayakan instrumen</td>
            <td class="score-cell">_____</td>
            <td class="score-cell">_____</td>
            <td class="score-cell">_____</td>
            <td>_____</td>
        </tr>
        <tr>
            <td class="score-cell">5</td>
            <td><strong>Presentasi dan Komunikasi</strong><br>
                - Kejelasan penyampaian<br>
                - Penguasaan materi<br>
                - Kemampuan menjawab pertanyaan</td>
            <td class="score-cell">_____</td>
            <td class="score-cell">_____</td>
            <td class="score-cell">_____</td>
            <td>_____</td>
        </tr>
        <tr class="total-row">
            <td colspan="2" style="text-align: center; font-weight: bold;">RATA-RATA NILAI</td>
            <td class="score-cell" style="font-weight: bold;">
                <?= isset($penilaian->nilai_penguji1) ? number_format($penilaian->nilai_penguji1, 1) : '_____' ?>
            </td>
            <td class="score-cell" style="font-weight: bold;">
                <?= isset($penilaian->nilai_penguji2) ? number_format($penilaian->nilai_penguji2, 1) : '_____' ?>
            </td>
            <td class="score-cell" style="font-weight: bold;">
                <?= isset($penilaian->nilai_pembimbing) ? number_format($penilaian->nilai_pembimbing, 1) : '_____' ?>
            </td>
            <td style="font-weight: bold;">
                <?php if (isset($penilaian->nilai_akhir)): ?>
                    <?= number_format($penilaian->nilai_akhir, 1) ?> (<?= $penilaian->nilai_huruf ?>)
                <?php else: ?>
                    _____
                <?php endif; ?>
            </td>
        </tr>
    </tbody>
</table>

<div style="margin-top: 15px;">
    <strong>RUMUS PERHITUNGAN NILAI AKHIR:</strong><br>
    Nilai Akhir = (Nilai Penguji 1 + Nilai Penguji 2 + Nilai Pembimbing) ÷ 3
</div>

<div>
    <strong>KRITERIA PENILAIAN:</strong><br>
    A (80-100) : Sangat Baik &nbsp;&nbsp;&nbsp;&nbsp;
    B (70-79) : Baik &nbsp;&nbsp;&nbsp;&nbsp;
    C (60-69) : Cukup &nbsp;&nbsp;&nbsp;&nbsp;
    D (50-59) : Kurang &nbsp;&nbsp;&nbsp;&nbsp;
    E (0-49) : Sangat Kurang
</div>

<div style="margin-top: 20px;">
    <strong>KEPUTUSAN:</strong><br>
    <?php if (isset($penilaian->rekomendasi)): ?>
        <?php if ($penilaian->rekomendasi == 'diterima_tanpa_revisi'): ?>
            ☑ Proposal DITERIMA tanpa revisi<br>
        <?php else: ?>
            ☐ Proposal DITERIMA tanpa revisi<br>
        <?php endif; ?>
        
        <?php if ($penilaian->rekomendasi == 'revisi_minor'): ?>
            ☑ Proposal DITERIMA dengan revisi minor (< 2 minggu)<br>
        <?php else: ?>
            ☐ Proposal DITERIMA dengan revisi minor (< 2 minggu)<br>
        <?php endif; ?>
        
        <?php if ($penilaian->rekomendasi == 'revisi_mayor'): ?>
            ☑ Proposal DITERIMA dengan revisi mayor (2-4 minggu)<br>
        <?php else: ?>
            ☐ Proposal DITERIMA dengan revisi mayor (2-4 minggu)<br>
        <?php endif; ?>
        
        <?php if ($penilaian->rekomendasi == 'ditolak'): ?>
            ☑ Proposal DITOLAK<br>
        <?php else: ?>
            ☐ Proposal DITOLAK<br>
        <?php endif; ?>
    <?php else: ?>
        ☐ Proposal DITERIMA tanpa revisi<br>
        ☐ Proposal DITERIMA dengan revisi minor (< 2 minggu)<br>
        ☐ Proposal DITERIMA dengan revisi mayor (2-4 minggu)<br>
        ☐ Proposal DITOLAK
    <?php endif; ?>
</div>

<div style="margin-top: 20px;">
    <strong>CATATAN DAN SARAN PERBAIKAN:</strong>
    <div class="catatan-box">
        <?= isset($penilaian->catatan_umum) ? nl2br(htmlspecialchars($penilaian->catatan_umum)) : '' ?>
        
        <?php if (isset($penilaian->keterangan_rekomendasi) && !empty($penilaian->keterangan_rekomendasi)): ?>
            <br><br><strong>Keterangan Rekomendasi:</strong><br>
            <?= nl2br(htmlspecialchars($penilaian->keterangan_rekomendasi)) ?>
        <?php endif; ?>
    </div>
</div>

<div class="signature-area">
    <div>Jakarta, <?= date('d F Y') ?></div>
    
    <!-- Signature section untuk 3 dosen -->
    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td style="width: 33%; text-align: center;">
                <div>Dosen Penguji 1,</div>
                <br><br><br>
                <div><u><?= isset($proposal->nama_penguji1) ? $proposal->nama_penguji1 : '________________________' ?></u></div>
                <div>NIP. <?= isset($proposal->nip_penguji1) ? $proposal->nip_penguji1 : '________________' ?></div>
            </td>
            <td style="width: 33%; text-align: center;">
                <div>Dosen Penguji 2,</div>
                <br><br><br>
                <div><u><?= isset($proposal->nama_penguji2) ? $proposal->nama_penguji2 : '________________________' ?></u></div>
                <div>NIP. <?= isset($proposal->nip_penguji2) ? $proposal->nip_penguji2 : '________________' ?></div>
            </td>
            <td style="width: 33%; text-align: center;">
                <div>Dosen Pembimbing,</div>
                <br><br><br>
                <div><u><?= isset($proposal->nama_pembimbing) ? $proposal->nama_pembimbing : '________________________' ?></u></div>
                <div>NIP. <?= isset($proposal->nip_pembimbing) ? $proposal->nip_pembimbing : '________________' ?></div>
            </td>
        </tr>
    </table>
</div>