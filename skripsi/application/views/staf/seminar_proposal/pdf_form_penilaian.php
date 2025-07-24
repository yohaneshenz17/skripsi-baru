<?php
// ========================================
// 5. FORM PENILAIAN SEMINAR PROPOSAL PDF TEMPLATE
// File: application/views/staf/seminar_proposal/pdf_form_penilaian.php
// ========================================
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Penilaian Seminar Proposal</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .info-mahasiswa {
            margin-bottom: 20px;
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .penilaian-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .penilaian-table th,
        .penilaian-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .penilaian-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .score-cell {
            text-align: center;
            width: 80px;
        }
        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .catatan-box {
            border: 1px solid #000;
            min-height: 80px;
            padding: 10px;
            margin: 10px 0;
        }
        .signature-area {
            margin-top: 40px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">FORM PENILAIAN SEMINAR PROPOSAL</div>
        <div>SEKOLAH TINGGI KEGURUAN ST. YAKOBUS</div>
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

    <table class="penilaian-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="40%">Aspek Penilaian</th>
                <th width="15%">Bobot (%)</th>
                <th width="15%">Nilai (0-100)</th>
                <th width="15%">Nilai × Bobot</th>
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
                <td class="score-cell">15%</td>
                <td class="score-cell">_____</td>
                <td class="score-cell">_____</td>
                <td>_____</td>
            </tr>
            <tr>
                <td class="score-cell">2</td>
                <td><strong>Latar Belakang dan Perumusan Masalah</strong><br>
                    - Kejelasan latar belakang<br>
                    - Ketepatan rumusan masalah<br>
                    - Kesesuaian tujuan penelitian</td>
                <td class="score-cell">20%</td>
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
                <td class="score-cell">20%</td>
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
                <td class="score-cell">25%</td>
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
                <td class="score-cell">20%</td>
                <td class="score-cell">_____</td>
                <td class="score-cell">_____</td>
                <td>_____</td>
            </tr>
            <tr class="total-row">
                <td colspan="2" style="text-align: center;">TOTAL NILAI</td>
                <td class="score-cell">100%</td>
                <td class="score-cell">_____</td>
                <td class="score-cell">_____</td>
                <td>_____</td>
            </tr>
        </tbody>
    </table>

    <div>
        <strong>KRITERIA PENILAIAN:</strong><br>
        A (85-100) : Sangat Baik &nbsp;&nbsp;&nbsp;&nbsp;
        B (70-84) : Baik &nbsp;&nbsp;&nbsp;&nbsp;
        C (60-69) : Cukup &nbsp;&nbsp;&nbsp;&nbsp;
        D (50-59) : Kurang &nbsp;&nbsp;&nbsp;&nbsp;
        E (0-49) : Sangat Kurang
    </div>

    <div style="margin-top: 20px;">
        <strong>KEPUTUSAN:</strong><br>
        ☐ Proposal DITERIMA tanpa revisi<br>
        ☐ Proposal DITERIMA dengan revisi minor (< 2 minggu)<br>
        ☐ Proposal DITERIMA dengan revisi mayor (2-4 minggu)<br>
        ☐ Proposal DITOLAK
    </div>

    <div style="margin-top: 20px;">
        <strong>CATATAN DAN SARAN PERBAIKAN:</strong>
        <div class="catatan-box"></div>
    </div>

    <div class="signature-area">
        <div>Jakarta, <?= date('d F Y') ?></div>
        <div>Penilai,</div>
        <br><br><br>
        <div><u>________________________</u></div>
        <div>NIP. ____________________</div>
    </div>

    <div style="margin-top: 30px; font-size: 10px; color: #666;">
        <em>Dokumen ini digenerate oleh <?= $generated_by ?> pada <?= $generated_at ?></em>
    </div>
</body>
</html>