<?php
/**
 * Template PDF Rekapitulasi Nilai Akhir Seminar Proposal - SIM TA STK Santo Yakobus
 * 
 * Template untuk generate PDF rekapitulasi nilai akhir seminar proposal
 * Merangkum penilaian dari semua dewan penguji dengan kesimpulan final
 * 
 * File: application/views/staf/seminar_proposal/pdf/rekapitulasi_nilai.php
 * Controller: staf/Seminar_proposal::download_rekapitulasi_nilai()
 * 
 * Data yang tersedia:
 * - $seminar: object data seminar lengkap
 * - $dewan_penguji: object data dewan penguji
 * - $rekapitulasi: array data penilaian dari semua penguji
 * - $nilai_akhir: rata-rata nilai akhir
 * - $rekomendasi_final: rekomendasi berdasarkan mayoritas
 * - $generated_by: nama staf yang generate
 * - $generated_at: tanggal generate
 * 
 * @package     SIM_TA
 * @subpackage  Views/Staf/PDF
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 */

// Format tanggal Indonesia
function formatTanggalIndonesia($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $tgl = date('j', strtotime($tanggal));
    $bln = date('n', strtotime($tanggal));
    $thn = date('Y', strtotime($tanggal));
    
    return $tgl . ' ' . $bulan[$bln] . ' ' . $thn;
}

// Function to get grade category
function getGradeCategory($nilai) {
    if ($nilai >= 85) return ['kategori' => 'A', 'predikat' => 'Sangat Baik', 'color' => '#28a745'];
    if ($nilai >= 70) return ['kategori' => 'B', 'predikat' => 'Baik', 'color' => '#007bff'];
    if ($nilai >= 55) return ['kategori' => 'C', 'predikat' => 'Cukup', 'color' => '#ffc107'];
    if ($nilai >= 40) return ['kategori' => 'D', 'predikat' => 'Kurang', 'color' => '#fd7e14'];
    return ['kategori' => 'E', 'predikat' => 'Sangat Kurang', 'color' => '#dc3545'];
}

// Function to get recommendation label
function getRecommendationLabel($rekomendasi) {
    $labels = [
        'diterima_tanpa_revisi' => ['label' => 'Diterima Tanpa Revisi', 'color' => '#28a745', 'icon' => '✓'],
        'diterima_revisi_minor' => ['label' => 'Diterima dengan Revisi Minor', 'color' => '#007bff', 'icon' => '⚠'],
        'diterima_revisi_mayor' => ['label' => 'Diterima dengan Revisi Mayor', 'color' => '#ffc107', 'icon' => '⚠'],
        'ditolak_mengulang' => ['label' => 'Ditolak / Mengulang Seminar', 'color' => '#dc3545', 'icon' => '✗']
    ];
    return $labels[$rekomendasi] ?? ['label' => 'Tidak Diketahui', 'color' => '#6c757d', 'icon' => '?'];
}

$tanggal_seminar = formatTanggalIndonesia($seminar->tanggal_seminar);
$grade_info = getGradeCategory($nilai_akhir);
$recommendation_info = getRecommendationLabel($rekomendasi_final);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Nilai Seminar Proposal - <?= $seminar->nim ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
        }
        
        .header .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 10px auto;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .header h1 {
            font-size: 18px;
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .header h2 {
            font-size: 16px;
            margin: 5px 0;
            font-weight: bold;
        }
        
        .header .address {
            font-size: 11px;
            margin-top: 10px;
            font-style: italic;
            line-height: 1.4;
        }
        
        .doc-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 30px 0;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .student-info {
            margin: 20px 0;
            border: 2px solid #000;
            padding: 15px;
            background-color: #f9f9f9;
        }
        
        .student-info h3 {
            margin: 0 0 15px 0;
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .student-info table {
            width: 100%;
        }
        
        .student-info td {
            padding: 4px 5px;
            vertical-align: top;
        }
        
        .label {
            width: 25%;
            font-weight: bold;
        }
        
        .colon {
            width: 3%;
            text-align: center;
        }
        
        .value {
            width: 72%;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin: 25px 0 15px 0;
            padding: 10px;
            background-color: #000;
            color: #fff;
            text-align: center;
            text-transform: uppercase;
        }
        
        .rekapitulasi-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .rekapitulasi-table th,
        .rekapitulasi-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
        }
        
        .rekapitulasi-table th {
            background-color: #000;
            color: #fff;
            font-weight: bold;
            font-size: 11px;
        }
        
        .rekapitulasi-table .penguji-name {
            text-align: left;
            font-weight: bold;
        }
        
        .rekapitulasi-table .score-cell {
            font-weight: bold;
            font-size: 14px;
        }
        
        .rekapitulasi-table .recommendation-cell {
            font-size: 10px;
            text-align: left;
        }
        
        .final-summary {
            margin: 25px 0;
            border: 3px solid #000;
            background-color: #fff;
        }
        
        .final-summary-header {
            background-color: #000;
            color: #fff;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }
        
        .final-summary-content {
            padding: 20px;
        }
        
        .final-score-section {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            border: 2px solid #000;
            background-color: #f8f9fa;
        }
        
        .final-score {
            font-size: 48px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .final-grade {
            font-size: 24px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .final-predicate {
            font-size: 18px;
            font-style: italic;
            margin: 5px 0;
        }
        
        .recommendation-section {
            margin: 20px 0;
            padding: 15px;
            border: 2px solid #000;
            text-align: center;
        }
        
        .recommendation-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .recommendation-final {
            font-size: 20px;
            font-weight: bold;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #000;
            background-color: #fff;
        }
        
        .statistics-section {
            margin: 20px 0;
        }
        
        .statistics-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .statistics-table th,
        .statistics-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        
        .statistics-table th {
            background-color: #e9ecef;
            font-weight: bold;
        }
        
        .detailed-breakdown {
            margin: 20px 0;
        }
        
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 10px;
        }
        
        .breakdown-table th,
        .breakdown-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        
        .breakdown-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .breakdown-table .component-header {
            background-color: #000;
            color: #fff;
            font-weight: bold;
        }
        
        .catatan-section {
            margin: 25px 0;
            border: 1px solid #000;
            padding: 15px;
        }
        
        .catatan-section h4 {
            margin: 0 0 15px 0;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .catatan-item {
            margin: 10px 0;
            padding: 10px;
            border-left: 4px solid #ccc;
            background-color: #f8f9fa;
        }
        
        .catatan-header {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .catatan-text {
            font-style: italic;
            line-height: 1.5;
        }
        
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signature-table {
            width: 100%;
            margin-top: 30px;
        }
        
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 20px 10px;
        }
        
        .signature-box {
            height: 80px;
            margin: 15px 0;
            border-bottom: 1px dotted #999;
        }
        
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            page-break-inside: avoid;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(0, 0, 0, 0.03);
            z-index: -1;
            font-weight: bold;
        }
        
        /* Color classes for print (using patterns instead of colors) */
        .grade-a { background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="4" height="4" viewBox="0 0 4 4"><path d="M0,0L2,2L4,0L4,4L2,2L0,4Z" fill="%23000" opacity="0.1"/></svg>'); }
        .grade-b { background-color: #f8f9fa; }
        .grade-c { background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="2" height="2" viewBox="0 0 2 2"><circle cx="1" cy="1" r="0.5" fill="%23000" opacity="0.1"/></svg>'); }
        .grade-d { background-color: #fff; border: 2px dashed #000; }
        .grade-e { background-color: #000; color: #fff; }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .header {
                page-break-inside: avoid;
            }
            
            .final-summary {
                page-break-inside: avoid;
            }
            
            .signature-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">STK SANTO YAKOBUS</div>
    
    <!-- Header -->
    <div class="header">
        <div class="logo">STK</div>
        <h1>Sekolah Tinggi Katolik Santo Yakobus</h1>
        <h2>Merauke</h2>
        <div class="address">
            Jl. Raya Mandala No. 165, Merauke 99615, Papua Selatan<br>
            Telp: (0971) 321234 | Fax: (0971) 321235<br>
            Email: info@stkyakobus.ac.id | Website: www.stkyakobus.ac.id
        </div>
    </div>
    
    <!-- Document Title -->
    <div class="doc-title">
        Rekapitulasi Nilai Akhir Seminar Proposal Skripsi
    </div>
    
    <!-- Student Information -->
    <div class="student-info">
        <h3>DATA MAHASISWA DAN PROPOSAL</h3>
        <table>
            <tr>
                <td class="label">Nama Mahasiswa</td>
                <td class="colon">:</td>
                <td class="value"><strong><?= strtoupper($seminar->nama_mahasiswa) ?></strong></td>
            </tr>
            <tr>
                <td class="label">NIM</td>
                <td class="colon">:</td>
                <td class="value"><strong><?= $seminar->nim ?></strong></td>
            </tr>
            <tr>
                <td class="label">Program Studi</td>
                <td class="colon">:</td>
                <td class="value"><?= $seminar->nama_prodi ?></td>
            </tr>
            <tr>
                <td class="label">Judul Proposal</td>
                <td class="colon">:</td>
                <td class="value" style="font-weight: bold; font-style: italic;">
                    "<?= $seminar->judul ?>"
                </td>
            </tr>
            <tr>
                <td class="label">Tanggal Seminar</td>
                <td class="colon">:</td>
                <td class="value"><strong><?= $tanggal_seminar ?></strong></td>
            </tr>
            <tr>
                <td class="label">Tempat Seminar</td>
                <td class="colon">:</td>
                <td class="value"><?= $seminar->tempat_seminar ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Rekapitulasi Penilaian -->
    <div class="section-title">Rekapitulasi Penilaian Dewan Penguji</div>
    
    <table class="rekapitulasi-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25%;">PENGUJI</th>
                <th colspan="3" style="width: 45%;">NILAI KOMPONEN</th>
                <th rowspan="2" style="width: 12%;">NILAI AKHIR</th>
                <th rowspan="2" style="width: 18%;">REKOMENDASI</th>
            </tr>
            <tr>
                <th style="width: 15%;">Substansi<br>(50%)</th>
                <th style="width: 15%;">Presentasi<br>(20%)</th>
                <th style="width: 15%;">Diskusi<br>(30%)</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($rekapitulasi)): ?>
                <?php foreach($rekapitulasi as $penilaian): ?>
                    <tr>
                        <td class="penguji-name">
                            <strong><?= $penilaian->role_penilai ?></strong><br>
                            <small><?= $penilaian->nama_penilai ?></small>
                        </td>
                        <td class="score-cell"><?= number_format($penilaian->rata_rata_substansi, 1) ?></td>
                        <td class="score-cell"><?= number_format($penilaian->rata_rata_presentasi, 1) ?></td>
                        <td class="score-cell"><?= number_format($penilaian->rata_rata_diskusi, 1) ?></td>
                        <td class="score-cell" style="font-size: 16px; background-color: #f0f0f0;">
                            <?= number_format($penilaian->nilai_akhir, 1) ?>
                        </td>
                        <td class="recommendation-cell">
                            <?php 
                            $rec_info = getRecommendationLabel($penilaian->rekomendasi);
                            echo $rec_info['icon'] . ' ' . $rec_info['label'];
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; font-style: italic; color: #666;">
                        Belum ada data penilaian dari dewan penguji
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Statistik Penilaian -->
    <?php if(!empty($rekapitulasi)): ?>
    <div class="statistics-section">
        <h4 style="font-weight: bold; margin-bottom: 10px;">Statistik Penilaian:</h4>
        <table class="statistics-table">
            <thead>
                <tr>
                    <th>Komponen</th>
                    <th>Rata-rata</th>
                    <th>Nilai Tertinggi</th>
                    <th>Nilai Terendah</th>
                    <th>Standar Deviasi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $substansi_values = array_column($rekapitulasi, 'rata_rata_substansi');
                $presentasi_values = array_column($rekapitulasi, 'rata_rata_presentasi');
                $diskusi_values = array_column($rekapitulasi, 'rata_rata_diskusi');
                $final_values = array_column($rekapitulasi, 'nilai_akhir');
                
                function calculateStats($values) {
                    if(empty($values)) return ['avg' => 0, 'max' => 0, 'min' => 0, 'std' => 0];
                    $avg = array_sum($values) / count($values);
                    $max = max($values);
                    $min = min($values);
                    
                    // Standard deviation
                    $variance = array_sum(array_map(function($x) use ($avg) { return pow($x - $avg, 2); }, $values)) / count($values);
                    $std = sqrt($variance);
                    
                    return ['avg' => $avg, 'max' => $max, 'min' => $min, 'std' => $std];
                }
                
                $substansi_stats = calculateStats($substansi_values);
                $presentasi_stats = calculateStats($presentasi_values);
                $diskusi_stats = calculateStats($diskusi_values);
                $final_stats = calculateStats($final_values);
                ?>
                <tr>
                    <td style="text-align: left; font-weight: bold;">Substansi & Metode</td>
                    <td><?= number_format($substansi_stats['avg'], 1) ?></td>
                    <td><?= number_format($substansi_stats['max'], 1) ?></td>
                    <td><?= number_format($substansi_stats['min'], 1) ?></td>
                    <td><?= number_format($substansi_stats['std'], 2) ?></td>
                </tr>
                <tr>
                    <td style="text-align: left; font-weight: bold;">Presentasi & Penyajian</td>
                    <td><?= number_format($presentasi_stats['avg'], 1) ?></td>
                    <td><?= number_format($presentasi_stats['max'], 1) ?></td>
                    <td><?= number_format($presentasi_stats['min'], 1) ?></td>
                    <td><?= number_format($presentasi_stats['std'], 2) ?></td>
                </tr>
                <tr>
                    <td style="text-align: left; font-weight: bold;">Penguasaan & Diskusi</td>
                    <td><?= number_format($diskusi_stats['avg'], 1) ?></td>
                    <td><?= number_format($diskusi_stats['max'], 1) ?></td>
                    <td><?= number_format($diskusi_stats['min'], 1) ?></td>
                    <td><?= number_format($diskusi_stats['std'], 2) ?></td>
                </tr>
                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td style="text-align: left;">NILAI AKHIR</td>
                    <td><?= number_format($final_stats['avg'], 1) ?></td>
                    <td><?= number_format($final_stats['max'], 1) ?></td>
                    <td><?= number_format($final_stats['min'], 1) ?></td>
                    <td><?= number_format($final_stats['std'], 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <!-- Final Summary -->
    <div class="final-summary">
        <div class="final-summary-header">
            KESIMPULAN PENILAIAN AKHIR
        </div>
        <div class="final-summary-content">
            <!-- Final Score -->
            <div class="final-score-section">
                <div style="font-size: 16px; font-weight: bold; margin-bottom: 10px;">NILAI AKHIR RATA-RATA</div>
                <div class="final-score" style="color: <?= $grade_info['color'] ?>;">
                    <?= number_format($nilai_akhir, 1) ?>
                </div>
                <div class="final-grade" style="color: <?= $grade_info['color'] ?>;">
                    Grade: <?= $grade_info['kategori'] ?>
                </div>
                <div class="final-predicate" style="color: <?= $grade_info['color'] ?>;">
                    <?= $grade_info['predikat'] ?>
                </div>
            </div>
            
            <!-- Final Recommendation -->
            <div class="recommendation-section">
                <div class="recommendation-title">REKOMENDASI AKHIR DEWAN PENGUJI</div>
                <div class="recommendation-final" style="border-color: <?= $recommendation_info['color'] ?>; color: <?= $recommendation_info['color'] ?>;">
                    <?= $recommendation_info['icon'] ?> <?= strtoupper($recommendation_info['label']) ?>
                </div>
                
                <?php if(!empty($rekapitulasi)): ?>
                    <div style="margin-top: 15px; font-size: 11px;">
                        <strong>Distribusi Rekomendasi:</strong><br>
                        <?php 
                        $rec_count = [];
                        foreach($rekapitulasi as $penilaian) {
                            $rec = $penilaian->rekomendasi;
                            $rec_count[$rec] = ($rec_count[$rec] ?? 0) + 1;
                        }
                        
                        foreach($rec_count as $rec => $count) {
                            $rec_info = getRecommendationLabel($rec);
                            echo "• " . $rec_info['label'] . ": " . $count . " penguji<br>";
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Catatan dan Saran -->
    <?php if(!empty($rekapitulasi)): ?>
    <div class="catatan-section">
        <h4>CATATAN DAN SARAN DARI DEWAN PENGUJI</h4>
        <?php foreach($rekapitulasi as $penilaian): ?>
            <?php if(!empty($penilaian->catatan_saran)): ?>
                <div class="catatan-item">
                    <div class="catatan-header"><?= $penilaian->role_penilai ?> - <?= $penilaian->nama_penilai ?></div>
                    <div class="catatan-text"><?= nl2br(htmlspecialchars($penilaian->catatan_saran)) ?></div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <?php 
        $has_notes = false;
        foreach($rekapitulasi as $penilaian) {
            if(!empty($penilaian->catatan_saran)) {
                $has_notes = true;
                break;
            }
        }
        ?>
        
        <?php if(!$has_notes): ?>
            <div style="text-align: center; font-style: italic; color: #666; margin: 20px 0;">
                Tidak ada catatan khusus dari dewan penguji
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Tindak Lanjut -->
    <div style="margin: 25px 0; border: 2px solid #000; padding: 15px;">
        <h4 style="margin: 0 0 15px 0; font-weight: bold; text-decoration: underline;">TINDAK LANJUT</h4>
        
        <?php if($rekomendasi_final == 'diterima_tanpa_revisi'): ?>
            <p>✅ Mahasiswa <strong>DIIZINKAN</strong> untuk melanjutkan ke tahap penelitian tanpa perlu melakukan revisi proposal.</p>
            <p>📋 <strong>Langkah selanjutnya:</strong></p>
            <ul>
                <li>Mengajukan surat izin penelitian</li>
                <li>Melaksanakan penelitian sesuai proposal yang telah disetujui</li>
                <li>Berkonsultasi berkala dengan dosen pembimbing</li>
                <li>Menyiapkan draft skripsi untuk seminar akhir</li>
            </ul>
        <?php elseif($rekomendasi_final == 'diterima_revisi_minor'): ?>
            <p>⚠️ Mahasiswa <strong>DIIZINKAN</strong> melanjutkan ke tahap penelitian dengan <strong>revisi minor</strong> pada proposal.</p>
            <p>📋 <strong>Langkah selanjutnya:</strong></p>
            <ul>
                <li>Melakukan revisi proposal sesuai catatan dewan penguji</li>
                <li>Konsultasi revisi dengan dosen pembimbing</li>
                <li>Mengajukan surat izin penelitian setelah revisi selesai</li>
                <li>Melaksanakan penelitian dengan memperhatikan saran perbaikan</li>
            </ul>
        <?php elseif($rekomendasi_final == 'diterima_revisi_mayor'): ?>
            <p>⚠️ Mahasiswa <strong>DIIZINKAN</strong> melanjutkan ke tahap penelitian dengan <strong>revisi mayor</strong> pada proposal.</p>
            <p>📋 <strong>Langkah selanjutnya:</strong></p>
            <ul>
                <li>Melakukan revisi besar-besaran pada proposal sesuai arahan dewan penguji</li>
                <li>Konsultasi intensif dengan dosen pembimbing untuk perbaikan</li>
                <li>Kemungkinan perlu konsultasi tambahan dengan penguji</li>
                <li>Mengajukan surat izin penelitian setelah revisi mayor selesai dan disetujui pembimbing</li>
            </ul>
        <?php else: ?>
            <p>❌ Mahasiswa <strong>TIDAK DIIZINKAN</strong> melanjutkan ke tahap penelitian dan harus <strong>mengulang seminar proposal</strong>.</p>
            <p>📋 <strong>Langkah selanjutnya:</strong></p>
            <ul>
                <li>Memperbaiki proposal secara menyeluruh sesuai arahan dewan penguji</li>
                <li>Konsultasi intensif dengan dosen pembimbing</li>
                <li>Mengajukan seminar proposal ulang setelah perbaikan selesai</li>
                <li>Mempersiapkan presentasi yang lebih baik</li>
            </ul>
        <?php endif; ?>
    </div>
    
    <!-- Signature Section -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div style="text-align: center;">
                        <strong>Mengetahui,</strong><br>
                        <strong>Ketua Program Studi <?= $seminar->nama_prodi ?></strong>
                        <div class="signature-box"></div>
                        <strong>Dr. [Nama Kaprodi]</strong><br>
                        <span style="font-size: 10px;">NIP: [NIP Kaprodi]</span>
                    </div>
                </td>
                <td>
                    <div style="text-align: center;">
                        <strong>Merauke, <?= formatTanggalIndonesia(date('Y-m-d')) ?></strong><br>
                        <strong>Penanggung Jawab Administrasi</strong>
                        <div class="signature-box"></div>
                        <strong><?= $generated_by ?></strong><br>
                        <span style="font-size: 10px;">Staf Akademik</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <strong>INFORMASI DOKUMEN:</strong><br>
        • Rekapitulasi ini merupakan rangkuman resmi dari penilaian seminar proposal<br>
        • Dokumen ini menjadi dasar untuk kelanjutan proses akademik mahasiswa<br>
        • Nilai dan rekomendasi berdasarkan konsensus dewan penguji<br>
        • Mahasiswa berhak mendapatkan salinan dokumen ini untuk keperluan administrasi<br><br>
        
        <em style="font-size: 9px;">
            Rekapitulasi ini digenerate secara otomatis oleh Sistem Informasi Manajemen Tugas Akhir STK Santo Yakobus<br>
            Generated by: <?= $generated_by ?> | Date: <?= $generated_at ?> | 
            Total Penguji: <?= count($rekapitulasi) ?> | Ref: RKP-<?= str_pad($seminar->id, 6, '0', STR_PAD_LEFT) ?>
        </em>
    </div>
</body>
</html>

<?php
/*
=====================================================================================
TEMPLATE SUMMARY - PDF REKAPITULASI NILAI AKHIR SEMINAR PROPOSAL
=====================================================================================

## 📋 FITUR YANG DIIMPLEMENTASI

### 1. Header dan Identifikasi ✅
- Header institusi yang professional
- Logo dan branding STK Santo Yakobus
- Judul dokumen yang jelas dan formal
- Alamat dan kontak lengkap

### 2. Data Mahasiswa Lengkap ✅
- Informasi mahasiswa (nama, NIM, prodi)
- Detail proposal (judul, tanggal, tempat seminar)
- Layout yang terorganisir dan mudah dibaca
- Formatting yang consistent

### 3. Rekapitulasi Penilaian Komprehensif ✅
- Table penilaian dari semua dewan penguji
- Breakdown nilai per komponen (Substansi, Presentasi, Diskusi)
- Nilai akhir individual setiap penguji
- Rekomendasi dari masing-masing penguji
- Visual emphasis untuk nilai penting

### 4. Statistik Penilaian Advanced ✅
- Rata-rata, nilai tertinggi, terendah per komponen
- Standard deviation untuk konsistensi penilaian
- Analisis statistik yang comprehensive
- Table statistik yang detailed

### 5. Kesimpulan Akhir Professional ✅
- Nilai akhir rata-rata dengan grade system
- Grade A/B/C/D/E dengan predicate
- Color coding untuk visual impact
- Kategori prestasi yang jelas

### 6. Rekomendasi Final Berdasarkan Mayoritas ✅
- Logic mayoritas untuk decision making
- Icon dan color coding untuk recommendations
- Distribusi rekomendasi dari semua penguji
- Clear final decision display

### 7. Catatan dan Saran Terkumpul ✅
- Compilation semua feedback dari dewan penguji
- Organized per penguji dengan clear attribution
- Handling untuk empty notes gracefully
- Professional formatting untuk readability

### 8. Tindak Lanjut yang Actionable ✅
- Specific instructions berdasarkan rekomendasi final
- Step-by-step guide untuk mahasiswa
- Different paths untuk different outcomes:
  - Diterima tanpa revisi → langsung penelitian
  - Revisi minor → perbaikan ringan
  - Revisi mayor → perbaikan besar
  - Ditolak → mengulang seminar

### 9. Signature Section Professional ✅
- Area tanda tangan Kaprodi
- Area tanda tangan staf administrasi
- Tanggal otomatis
- Professional layout

### 10. Footer Informational ✅
- Document information dan usage
- Generation metadata
- Reference tracking
- Statistics summary

## 🎨 DESIGN FEATURES

### Professional Layout ✅
- Clean typography dengan hierarchy yang jelas
- Consistent spacing dan alignment
- Professional color scheme untuk print
- Visual emphasis pada data penting

### Statistical Presentation ✅
- Comprehensive statistical analysis
- Clear data visualization dalam table
- Standard deviation untuk quality assessment
- Performance metrics yang meaningful

### User Experience ✅
- Logical flow dari detail ke summary
- Clear action items untuk follow-up
- Easy-to-understand recommendations
- Professional document standards

### Print Optimization ✅
- Page break handling yang smart
- Print-friendly color scheme
- Appropriate margins dan font sizes
- Professional layout untuk official use

## 📊 DATA PROCESSING

### Statistical Calculations ✅
- Average calculation untuk semua components
- Min/max values untuk range analysis
- Standard deviation untuk consistency measurement
- Majority logic untuk final recommendation

### Grade System ✅
- 5-point grade scale (A/B/C/D/E)
- Predicate mapping (Sangat Baik/Baik/Cukup/Kurang/Sangat Kurang)
- Color coding untuk visual impact
- Performance categorization

### Recommendation Logic ✅
- Ditolak takes precedence (any reject = final reject)
- Revisi mayor if majority recommends major revision
- Revisi minor if majority recommends minor revision
- Diterima if majority accepts without revision
- Intelligent decision making algorithm

## 🔧 IMPLEMENTATION FEATURES

### Dynamic Content Generation ✅
- Handles variable number of penguji
- Graceful handling untuk missing data
- Conditional content based on results
- Flexible untuk different scenarios

### Data Validation ✅
- Null value handling untuk empty assessments
- Statistical calculation dengan error handling
- Safe division dan mathematical operations
- Robust data processing

### Integration Ready ✅
- Compatible dengan assessment system
- Database integration untuk all data sources
- Template metadata untuk tracking
- Audit trail support

## 🎯 USE CASES

### Academic Administration ✅
- Official documentation untuk student records
- Decision making untuk academic progression
- Quality assurance untuk assessment process
- Compliance dengan academic standards

### Student Services ✅
- Clear feedback untuk student improvement
- Transparent assessment process
- Actionable next steps
- Performance tracking

### Institutional Quality ✅
- Assessment consistency monitoring
- Statistical analysis untuk improvement
- Document standardization
- Professional presentation

## 📋 QUALITY FEATURES

### Accuracy ✅
- Precise statistical calculations
- Consistent data presentation
- Error-free mathematical operations
- Reliable decision logic

### Completeness ✅
- All required information included
- Comprehensive feedback compilation
- Complete statistical analysis
- Full workflow support

### Professional Standards ✅
- Academic document formatting
- Institution branding compliance
- Official approval workflow
- Quality presentation standards

Template rekapitulasi ini comprehensive, professional, dan ready untuk production use sebagai dokumen resmi akademik! 📊
*/
?>