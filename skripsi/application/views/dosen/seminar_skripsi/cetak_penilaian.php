<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian Seminar Skripsi - <?= $penilaian->nama_mahasiswa ?></title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        
        .header h2 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 10px 0;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 11px;
        }
        
        .mahasiswa-info {
            margin-bottom: 25px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .info-table td {
            padding: 5px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        .info-table .label {
            background-color: #f5f5f5;
            font-weight: bold;
            width: 25%;
        }
        
        .nilai-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            padding: 5px 10px;
            background-color: #f0f0f0;
            border-left: 4px solid #007bff;
        }
        
        .nilai-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .nilai-box {
            flex: 1;
            text-align: center;
            border: 2px solid #007bff;
            padding: 15px;
            margin: 0 5px;
            border-radius: 5px;
        }
        
        .nilai-box .nilai {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .nilai-box .label-nilai {
            font-size: 11px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .nilai-box .nama-penguji {
            font-size: 10px;
            font-weight: bold;
            color: #333;
        }
        
        .rekapitulasi {
            background-color: #f8f9fa;
            border: 2px solid #007bff;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
        }
        
        .rekapitulasi h3 {
            margin: 0 0 10px 0;
            color: #007bff;
        }
        
        .final-scores {
            display: flex;
            justify-content: space-around;
            margin-top: 10px;
        }
        
        .final-score {
            text-align: center;
        }
        
        .final-score .score {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
        }
        
        .final-score .label {
            font-size: 11px;
            color: #666;
        }
        
        .rekomendasi-box {
            border: 2px solid #28a745;
            background-color: #f8fff9;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        
        .rekomendasi-badge {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .catatan-section {
            margin-top: 25px;
        }
        
        .catatan-item {
            margin-bottom: 15px;
            border-left: 3px solid #007bff;
            padding-left: 10px;
        }
        
        .catatan-label {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .catatan-content {
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 3px;
            border: 1px solid #e9ecef;
        }
        
        .footer {
            margin-top: 40px;
            text-align: right;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .signature-area {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
            min-height: 40px;
        }
        
        .nip-text {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
            font-style: italic;
        }
        
        @media print {
            body {
                padding: 15px;
            }
            
            .nilai-grid {
                display: table;
                width: 100%;
            }
            
            .nilai-box {
                display: table-cell;
                margin: 0;
            }
            
            .final-scores {
                display: table;
                width: 100%;
            }
            
            .final-score {
                display: table-cell;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Sekolah Tinggi Katolik Santo Yakobus Merauke</h1>
        <h2>Berita Acara Penilaian Seminar Skripsi</h2>
        <p>Jl. Raya Mandala KM. 15, Merauke, Papua Selatan</p>
        <p>Telp: (0971) 325756 | Email: info@stkyakobus.ac.id</p>
    </div>

    <!-- Informasi Mahasiswa -->
    <div class="mahasiswa-info">
        <table class="info-table">
            <tr>
                <td class="label">Nama Mahasiswa</td>
                <td><?= $penilaian->nama_mahasiswa ?></td>
                <td class="label">NIM</td>
                <td><?= $penilaian->nim ?></td>
            </tr>
            <tr>
                <td class="label">Program Studi</td>
                <td><?= $penilaian->nama_prodi ?></td>
                <td class="label">Tanggal Seminar</td>
                <td><?= date('d F Y', strtotime($penilaian->tanggal_seminar)) ?></td>
            </tr>
            <tr>
                <td class="label">Judul Skripsi</td>
                <td colspan="3"><?= $penilaian->judul ?></td>
            </tr>
            <?php if (!empty($penilaian->jam_seminar)): ?>
            <tr>
                <td class="label">Waktu</td>
                <td><?= date('H:i', strtotime($penilaian->jam_seminar)) ?> WIB</td>
                <td class="label">Tempat</td>
                <td><?= $penilaian->tempat_seminar ?? 'N/A' ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Nilai Per Penguji -->
    <div class="section-title">NILAI PER PENGUJI</div>
    <div class="nilai-grid">
        <div class="nilai-box">
            <div class="nilai"><?= $penilaian->nilai_penguji1 ?? '0' ?></div>
            <div class="label-nilai">Penguji 1</div>
            <div class="nama-penguji"><?= $penilaian->nama_penguji1 ?? 'N/A' ?></div>
        </div>
        
        <div class="nilai-box">
            <div class="nilai"><?= $penilaian->nilai_penguji2 ?? '0' ?></div>
            <div class="label-nilai">Penguji 2</div>
            <div class="nama-penguji"><?= $penilaian->nama_penguji2 ?? 'N/A' ?></div>
        </div>
        
        <div class="nilai-box">
            <div class="nilai"><?= $penilaian->nilai_pembimbing ?? '0' ?></div>
            <div class="label-nilai">Pembimbing</div>
            <div class="nama-penguji"><?= $penilaian->nama_pembimbing ?></div>
        </div>
    </div>

    <!-- Rekapitulasi Final -->
    <div class="rekapitulasi">
        <h3>REKAPITULASI NILAI AKHIR</h3>
        <div class="final-scores">
            <div class="final-score">
                <div class="score"><?= $penilaian->nilai_akhir ?? '0' ?></div>
                <div class="label">Nilai Akhir</div>
            </div>
            <div class="final-score">
                <div class="score"><?= $penilaian->nilai_huruf ?? 'N/A' ?></div>
                <div class="label">Nilai Huruf</div>
            </div>
            <div class="final-score">
                <div class="score">
                    <?php
                    $nilai = $penilaian->nilai_akhir ?? 0;
                    if ($nilai >= 85) echo 'Sangat Baik';
                    elseif ($nilai >= 75) echo 'Baik';
                    elseif ($nilai >= 65) echo 'Cukup';
                    elseif ($nilai >= 55) echo 'Kurang';
                    else echo 'Sangat Kurang';
                    ?>
                </div>
                <div class="label">Predikat</div>
            </div>
        </div>
    </div>

    <!-- Rekomendasi -->
    <?php if (!empty($penilaian->rekomendasi)): ?>
    <div class="rekomendasi-box">
        <div class="rekomendasi-badge">
            <?= str_replace('_', ' ', strtoupper($penilaian->rekomendasi)) ?>
        </div>
        <?php if (!empty($penilaian->keterangan_rekomendasi)): ?>
        <div><strong>Keterangan:</strong> <?= $penilaian->keterangan_rekomendasi ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Catatan Detail -->
    <div class="section-title">CATATAN PENILAIAN</div>
    <div class="catatan-section">
        <?php
        $catatan_items = [
            'Pendahuluan' => $penilaian->catatan_pendahuluan,
            'Tinjauan Pustaka' => $penilaian->catatan_tinjauan_pustaka,
            'Metodologi' => $penilaian->catatan_metodologi,
            'Hasil & Pembahasan' => $penilaian->catatan_hasil_pembahasan,
            'Kesimpulan' => $penilaian->catatan_kesimpulan,
            'Catatan Umum' => $penilaian->catatan_umum
        ];
        
        $has_catatan = false;
        foreach ($catatan_items as $label => $content) {
            if (!empty($content) && trim($content) !== '') {
                $has_catatan = true;
                echo '<div class="catatan-item">';
                echo '<div class="catatan-label">' . $label . '</div>';
                echo '<div class="catatan-content">' . nl2br(htmlspecialchars($content)) . '</div>';
                echo '</div>';
            }
        }
        
        if (!$has_catatan) {
            echo '<p><em>Tidak ada catatan khusus.</em></p>';
        }
        ?>
    </div>

    <!-- Footer dan Tanda Tangan -->
    <div class="footer">
        <p>Dicetak pada: <?= date('d F Y, H:i:s') ?> WIB</p>
        
        <div class="signature-area">
            <div class="signature-box">
                <div>Mengetahui,</div>
                <div><strong>Ketua Program Studi</strong></div>
                <div class="signature-line">
                    <?php if (isset($ketua_prodi) && !empty($ketua_prodi->nama)): ?>
                        <div><strong><?= $ketua_prodi->nama ?></strong></div>
                        <?php if (!empty($ketua_prodi->nip)): ?>
                        <div class="nip-text">NIP: <?= $ketua_prodi->nip ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div>(_________________________)</div>
                        <div class="nip-text">NIP: ___________________</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="signature-box">
                <div>Merauke, <?= date('d F Y') ?></div>
                <div><strong>Dosen Pembimbing</strong></div>
                <div class="signature-line">
                    <div><strong><?= $penilaian->nama_pembimbing ?></strong></div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript untuk auto print -->
    <script>
        window.onload = function() {
            // Auto focus untuk print dialog
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Handle after print event
        window.onafterprint = function() {
            // Optional: close window atau redirect
            // window.close();
        };
    </script>
</body>
</html>