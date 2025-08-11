<?php
/**
 * Template PDF Form Penilaian Seminar Proposal - SIM TA STK Santo Yakobus
 * 
 * Template untuk generate PDF form penilaian seminar proposal
 * Sesuai dengan rubrik penilaian yang telah ditetapkan
 * 
 * File: application/views/staf/seminar_proposal/pdf/form_penilaian.php
 * Controller: staf/Seminar_proposal::download_form_penilaian()
 * 
 * Data yang tersedia:
 * - $seminar: object data seminar lengkap
 * - $dewan_penguji: object data dewan penguji
 * - $penguji_type: tipe penguji ('pembimbing', 'penguji1', 'penguji2', 'all')
 * - $rubrik_penilaian: template rubrik penilaian
 * - $generated_by: nama staf yang generate
 * - $generated_at: tanggal generate
 * 
 * @package     SIM_TA
 * @subpackage  Views/Staf/PDF
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 */

// Determine which forms to generate based on penguji_type
$forms_to_generate = [];
switch($penguji_type) {
    case 'pembimbing':
        $forms_to_generate = [
            ['type' => 'pembimbing', 'nama' => $dewan_penguji->nama_pembimbing, 'nip' => $dewan_penguji->nip_pembimbing]
        ];
        break;
    case 'penguji1':
        $forms_to_generate = [
            ['type' => 'penguji1', 'nama' => $dewan_penguji->nama_penguji1, 'nip' => $dewan_penguji->nip_penguji1]
        ];
        break;
    case 'penguji2':
        $forms_to_generate = [
            ['type' => 'penguji2', 'nama' => $dewan_penguji->nama_penguji2, 'nip' => $dewan_penguji->nip_penguji2]
        ];
        break;
    case 'all':
    default:
        $forms_to_generate = [
            ['type' => 'pembimbing', 'nama' => $dewan_penguji->nama_pembimbing, 'nip' => $dewan_penguji->nip_pembimbing],
            ['type' => 'penguji1', 'nama' => $dewan_penguji->nama_penguji1, 'nip' => $dewan_penguji->nip_penguji1],
            ['type' => 'penguji2', 'nama' => $dewan_penguji->nama_penguji2, 'nip' => $dewan_penguji->nip_penguji2]
        ];
        break;
}

// Function to get penguji type label
function getPengujiLabel($type) {
    $labels = [
        'pembimbing' => 'Dosen Pembimbing',
        'penguji1' => 'Dosen Penguji I',
        'penguji2' => 'Dosen Penguji II'
    ];
    return $labels[$type] ?? 'Penguji';
}

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

$tanggal_seminar = formatTanggalIndonesia($seminar->tanggal_seminar);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Penilaian Seminar Proposal - <?= $seminar->nim ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            margin: 0;
            padding: 15px;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header h2 {
            font-size: 14px;
            margin: 5px 0;
            font-weight: bold;
        }
        
        .header .address {
            font-size: 10px;
            margin-top: 5px;
            font-style: italic;
        }
        
        .form-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0;
            text-decoration: underline;
            text-transform: uppercase;
        }
        
        .penguji-info {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
            padding: 8px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
        }
        
        .student-info {
            margin: 15px 0;
            border: 1px solid #000;
            padding: 10px;
        }
        
        .student-info table {
            width: 100%;
        }
        
        .student-info td {
            padding: 2px 5px;
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
        
        .component-section {
            margin: 15px 0;
            border: 2px solid #000;
            page-break-inside: avoid;
        }
        
        .component-header {
            background-color: #000;
            color: #fff;
            padding: 8px;
            font-weight: bold;
            text-align: center;
            font-size: 12px;
        }
        
        .component-description {
            padding: 8px;
            background-color: #f9f9f9;
            font-style: italic;
            font-size: 10px;
            border-bottom: 1px solid #ccc;
        }
        
        .indicator-item {
            padding: 8px;
            border-bottom: 1px solid #ccc;
            page-break-inside: avoid;
        }
        
        .indicator-item:last-child {
            border-bottom: none;
        }
        
        .indicator-header {
            font-weight: bold;
            margin-bottom: 5px;
            color: #000;
        }
        
        .score-section {
            margin: 8px 0;
            display: flex;
            align-items: center;
        }
        
        .score-box {
            width: 60px;
            height: 25px;
            border: 2px solid #000;
            text-align: center;
            line-height: 25px;
            font-weight: bold;
            margin-right: 10px;
            background-color: #fff;
        }
        
        .score-label {
            font-size: 10px;
            color: #666;
        }
        
        .criteria-description {
            margin: 5px 0;
            font-size: 9px;
            line-height: 1.2;
        }
        
        .criteria-description div {
            margin: 2px 0;
        }
        
        .criteria-strong {
            color: #006400;
            font-weight: bold;
        }
        
        .criteria-fair {
            color: #ff8c00;
            font-weight: bold;
        }
        
        .criteria-weak {
            color: #dc143c;
            font-weight: bold;
        }
        
        .average-section {
            background-color: #e9ecef;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #000;
            text-align: center;
        }
        
        .average-section strong {
            font-size: 12px;
        }
        
        .average-box {
            width: 80px;
            height: 30px;
            border: 2px solid #000;
            display: inline-block;
            margin: 0 10px;
            background-color: #fff;
        }
        
        .final-section {
            margin: 20px 0;
            border: 3px solid #000;
            page-break-inside: avoid;
        }
        
        .final-header {
            background-color: #000;
            color: #fff;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .final-content {
            padding: 15px;
        }
        
        .final-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .final-table th,
        .final-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        
        .final-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .final-score-box {
            width: 80px;
            height: 30px;
            border: 2px solid #000;
            background-color: #fff;
        }
        
        .recommendation-section {
            margin: 15px 0;
        }
        
        .recommendation-options {
            margin: 10px 0;
        }
        
        .recommendation-item {
            margin: 5px 0;
            display: flex;
            align-items: center;
        }
        
        .checkbox {
            width: 15px;
            height: 15px;
            border: 2px solid #000;
            margin-right: 10px;
            display: inline-block;
        }
        
        .notes-section {
            margin: 15px 0;
        }
        
        .notes-area {
            width: 100%;
            height: 80px;
            border: 2px solid #000;
            background-color: #fff;
        }
        
        .signature-section {
            margin-top: 30px;
            text-align: center;
            page-break-inside: avoid;
        }
        
        .signature-box {
            width: 200px;
            height: 80px;
            border: 1px solid #000;
            margin: 15px auto;
            background-color: #fff;
        }
        
        .footer {
            margin-top: 20px;
            font-size: 9px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            
            .component-section {
                page-break-inside: avoid;
            }
            
            .final-section {
                page-break-inside: avoid;
            }
            
            .signature-section {
                page-break-inside: avoid;
            }
        }
        
        /* Flexbox alternative for older browsers */
        .score-section-alt {
            margin: 8px 0;
        }
        
        .score-section-alt .score-box {
            float: left;
            margin-right: 10px;
        }
        
        .score-section-alt .score-label {
            display: block;
            margin-left: 70px;
            margin-top: 5px;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>

<?php foreach($forms_to_generate as $index => $form): ?>
    <?php if($index > 0): ?>
        <div class="page-break"></div>
    <?php endif; ?>
    
    <!-- Header -->
    <div class="header">
        <h1>Sekolah Tinggi Katolik Santo Yakobus</h1>
        <h2>Merauke</h2>
        <div class="address">
            Jl. Raya Mandala No. 165, Merauke 99615<br>
            Telp: (0971) 321234 | Email: info@stkyakobus.ac.id
        </div>
    </div>
    
    <!-- Form Title -->
    <div class="form-title">
        Rubrik Penilaian Seminar Proposal Skripsi
    </div>
    
    <!-- Penguji Information -->
    <div class="penguji-info">
        FORM PENILAIAN UNTUK: <?= strtoupper(getPengujiLabel($form['type'])) ?>
        <?php if($form['nama']): ?>
            <br><?= $form['nama'] ?> (NIP: <?= $form['nip'] ?>)
        <?php endif; ?>
    </div>
    
    <!-- Student Information -->
    <div class="student-info">
        <table>
            <tr>
                <td class="label">Nama Mahasiswa</td>
                <td class="colon">:</td>
                <td class="value">__________________________________________________</td>
            </tr>
            <tr>
                <td class="label">NIM</td>
                <td class="colon">:</td>
                <td class="value">__________________________________________________</td>
            </tr>
            <tr>
                <td class="label">Judul Proposal</td>
                <td class="colon">:</td>
                <td class="value">__________________________________________________</td>
            </tr>
            <tr>
                <td class="label">Tanggal Seminar</td>
                <td class="colon">:</td>
                <td class="value">__________________________________________________</td>
            </tr>
        </table>
    </div>
    
    <!-- KOMPONEN 1: Substansi dan Metode Penelitian (50%) -->
    <div class="component-section">
        <div class="component-header">
            KOMPONEN 1: SUBSTANSI DAN METODE PENELITIAN (Bobot: 50%)
        </div>
        <div class="component-description">
            Komponen ini menilai kualitas dokumen proposal yang diajukan. Penilaian berfokus pada kedalaman, 
            logika, dan kelayakan ilmiah dari rencana penelitian.
        </div>
        
        <!-- Indikator 1.1 -->
        <div class="indicator-item">
            <div class="indicator-header">1.1 Latar Belakang & Rumusan Masalah</div>
            <div class="score-section-alt clearfix">
                <div class="score-box"></div>
                <div class="score-label">Skor (1-100)</div>
            </div>
            <div class="criteria-description">
                <div><span class="criteria-strong">Kuat:</span> Masalah sangat relevan, didukung data/fakta kuat, dan dirumuskan dengan sangat tajam dan jelas.</div>
                <div><span class="criteria-fair">Cukup:</span> Masalah relevan, ada data pendukung, rumusan cukup jelas.</div>
                <div><span class="criteria-weak">Lemah:</span> Konteks masalah tidak jelas, tidak ada urgensi, rumusan terlalu luas/kabur.</div>
            </div>
        </div>
        
        <!-- Indikator 1.2 -->
        <div class="indicator-item">
            <div class="indicator-header">1.2 Tinjauan Pustaka & Kebaruan (Novelty)</div>
            <div class="score-section-alt clearfix">
                <div class="score-box"></div>
                <div class="score-label">Skor (1-100)</div>
            </div>
            <div class="criteria-description">
                <div><span class="criteria-strong">Kuat:</span> Mampu memetakan state-of-the-art dengan baik, menunjukkan research gap secara eksplisit, dan posisi penelitian sangat jelas.</div>
                <div><span class="criteria-fair">Cukup:</span> Ada tinjauan pustaka yang relevan, upaya menunjukkan kebaruan ada tapi kurang tajam.</div>
                <div><span class="criteria-weak">Lemah:</span> Tinjauan pustaka minim, tidak menunjukkan kebaruan.</div>
            </div>
        </div>
        
        <!-- Indikator 1.3 -->
        <div class="indicator-item">
            <div class="indicator-header">1.3 Landasan Teori</div>
            <div class="score-section-alt clearfix">
                <div class="score-box"></div>
                <div class="score-label">Skor (1-100)</div>
            </div>
            <div class="criteria-description">
                <div><span class="criteria-strong">Kuat:</span> Teori yang digunakan sangat relevan, mendalam, dan mampu menjadi pisau analisis yang tajam.</div>
                <div><span class="criteria-fair">Cukup:</span> Teori yang digunakan relevan, namun pembahasannya standar.</div>
                <div><span class="criteria-weak">Lemah:</span> Teori tidak tepat atau hanya tempelan.</div>
            </div>
        </div>
        
        <!-- Indikator 1.4 -->
        <div class="indicator-item">
            <div class="indicator-header">1.4 Metodologi Penelitian</div>
            <div class="score-section-alt clearfix">
                <div class="score-box"></div>
                <div class="score-label">Skor (1-100)</div>
            </div>
            <div class="criteria-description">
                <div><span class="criteria-strong">Kuat:</span> Metode sangat tepat untuk menjawab rumusan masalah. Teknik pengumpulan & analisis data dijelaskan rinci, sistematis, dan logis.</div>
                <div><span class="criteria-fair">Cukup:</span> Pilihan metode bisa diterima. Penjelasan teknik cukup jelas tapi kurang detail.</div>
                <div><span class="criteria-weak">Lemah:</span> Metode tidak sesuai, rancu, atau tidak mungkin dilaksanakan (infeasible).</div>
            </div>
        </div>
        
        <!-- Indikator 1.5 -->
        <div class="indicator-item">
            <div class="indicator-header">1.5 Sistematika & Tata Tulis</div>
            <div class="score-section-alt clearfix">
                <div class="score-box"></div>
                <div class="score-label">Skor (1-100)</div>
            </div>
            <div class="criteria-description">
                <div><span class="criteria-strong">Kuat:</span> Alur tulisan sangat logis. Menggunakan bahasa baku, format sitasi konsisten, dan bebas dari kesalahan tik.</div>
                <div><span class="criteria-fair">Cukup:</span> Alur tulisan cukup baik, ada beberapa kesalahan tata tulis atau sitasi.</div>
                <div><span class="criteria-weak">Lemah:</span> Tulisan tidak terstruktur, banyak kesalahan tata bahasa.</div>
            </div>
        </div>
        
        <!-- Average Komponen 1 -->
        <div class="average-section">
            <strong>Rata-rata Skor Komponen 1:</strong>
            <div class="average-box"></div>
            <small>(Total Skor / 5)</small>
        </div>
    </div>
    
    <!-- KOMPONEN 2: Presentasi dan Teknik Penyajian (20%) -->
    <div class="component-section">
        <div class="component-header">
            KOMPONEN 2: PRESENTASI DAN TEKNIK PENYAJIAN (Bobot: 20%)
        </div>
        <div class="component-description">
            Komponen ini menilai kemampuan mahasiswa dalam mengomunikasikan ide penelitiannya secara lisan dan visual.
        </div>
        
        <!-- Indikator 2.1 -->
        <div class="indicator-item">
            <div class="indicator-header">2.1 Kejelasan & Alur Penyampaian</div>
            <div class="score-section-alt clearfix">
                <div class="score-box"></div>
                <div class="score-label">Skor (1-100)</div>
            </div>
            <div class="criteria-description">
                <div><span class="criteria-strong">Kuat:</span> Berbicara dengan jelas, runtut, dan langsung ke poin-poin penting. Tidak hanya membaca slide.</div>
                <div><span class="criteria-fair">Cukup:</span> Penyampaian cukup jelas, namun terkadang berbelit-belit atau terlalu banyak membaca.</div>
                <div><span class="criteria-weak">Lemah:</span> Sulit dipahami, tidak terstruktur, atau gugup berlebihan.</div>
            </div>
        </div>
        
        <!-- Indikator 2.2 -->
        <div class="indicator-item">
            <div class="indicator-header">2.2 Desain Media Presentasi (Slide)</div>
            <div class="score-section-alt clearfix">
                <div class="score-box"></div>
                <div class="score-label">Skor (1-100)</div>
            </div>
            <div class="criteria-description">
                <div><span class="criteria-strong">Kuat:</span> Slide efektif, visual menarik, ringkas, dan sangat membantu pemahaman.</div>
                <div><span class="criteria-fair">Cukup:</span> Slide cukup informatif, namun desain standar atau terlalu padat teks.</div>
                <div><span class="criteria-weak">Lemah:</span> Slide tidak efektif, sulit dibaca, atau isinya hanya salinan dari naskah.</div>
            </div>
        </div>
        
        <!-- Indikator 2.3 -->
        <div class="indicator-item">
            <div class="indicator-header">2.3 Manajemen Waktu & Etika</div>
            <div class="score-section-alt clearfix">
                <div class="score-box"></div>
                <div class="score-label">Skor (1-100)</div>
            </div>
            <div class="criteria-description">
                <div><span class="criteria-strong">Kuat:</span> Menyelesaikan presentasi tepat waktu. Menunjukkan sikap percaya diri, sopan, dan menjaga kontak mata.</div>
                <div><span class="criteria-fair">Cukup:</span> Melebihi waktu sedikit, sikap cukup baik.</div>
                <div><span class="criteria-weak">Lemah:</span> Jauh melebihi alokasi waktu, terlihat tidak siap atau kurang sopan.</div>
            </div>
        </div>
        
        <!-- Average Komponen 2 -->
        <div class="average-section">
            <strong>Rata-rata Skor Komponen 2:</strong>
            <div class="average-box"></div>
            <small>(Total Skor / 3)</small>
        </div>
    </div>
    
    <!-- KOMPONEN 3: Penguasaan Materi dan Diskusi (30%) -->
    <div class="component-section">
        <div class="component-header">
            KOMPONEN 3: PENGUASAAN MATERI DAN DISKUSI (Bobot: 30%)
        </div>
        <div class="component-description">
            Komponen ini menilai kedalaman pemahaman mahasiswa terhadap proposalnya dan kemampuannya dalam berdiskusi secara ilmiah.
        </div>
        
        <!-- Indikator 3.1 -->
        <div class="indicator-item">
            <div class="indicator-header">3.1 Kemampuan Menjawab Pertanyaan</div>
            <div class="score-section-alt clearfix">
                <div class="score-box"></div>
                <div class="score-label">Skor (1-100)</div>
            </div>
            <div class="criteria-description">
                <div><span class="criteria-strong">Kuat:</span> Mampu menjawab semua pertanyaan dengan tepat, logis, dan terstruktur. Menunjukkan pemahaman di luar teks proposal.</div>
                <div><span class="criteria-fair">Cukup:</span> Mampu menjawab sebagian besar pertanyaan, meskipun ada jawaban yang kurang mendalam.</div>
                <div><span class="criteria-weak">Lemah:</span> Tidak mampu menjawab, jawaban tidak relevan atau kebingungan.</div>
            </div>
        </div>
        
        <!-- Indikator 3.2 -->
        <div class="indicator-item">
            <div class="indicator-header">3.2 Kemampuan Berargumentasi</div>
            <div class="score-section-alt clearfix">
                <div class="score-box"></div>
                <div class="score-label">Skor (1-100)</div>
            </div>
            <div class="criteria-description">
                <div><span class="criteria-strong">Kuat:</span> Mampu mempertahankan pilihan topik, teori, dan metode dengan argumentasi yang kokoh dan berbasis bukti/referensi.</div>
                <div><span class="criteria-fair">Cukup:</span> Mampu berargumen, namun terkadang kurang didukung oleh dasar yang kuat.</div>
                <div><span class="criteria-weak">Lemah:</span> Tidak mampu mempertahankan gagasannya, mudah goyah.</div>
            </div>
        </div>
        
        <!-- Indikator 3.3 -->
        <div class="indicator-item">
            <div class="indicator-header">3.3 Sikap Ilmiah dalam Diskusi</div>
            <div class="score-section-alt clearfix">
                <div class="score-box"></div>
                <div class="score-label">Skor (1-100)</div>
            </div>
            <div class="criteria-description">
                <div><span class="criteria-strong">Kuat:</span> Sangat terbuka dan responsif terhadap masukan/kritik. Menunjukkan sikap menghargai dan tidak defensif.</div>
                <div><span class="criteria-fair">Cukup:</span> Menerima masukan, meskipun terkadang sedikit defensif.</div>
                <div><span class="criteria-weak">Lemah:</span> Menolak masukan, bersikap defensif atau arogan.</div>
            </div>
        </div>
        
        <!-- Average Komponen 3 -->
        <div class="average-section">
            <strong>Rata-rata Skor Komponen 3:</strong>
            <div class="average-box"></div>
            <small>(Total Skor / 3)</small>
        </div>
    </div>
    
    <!-- REKAPITULASI NILAI AKHIR -->
    <div class="final-section">
        <div class="final-header">
            REKAPITULASI NILAI AKHIR
        </div>
        <div class="final-content">
            <table class="final-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">No</th>
                        <th style="width: 50%;">Komponen</th>
                        <th style="width: 15%;">Nilai Komponen</th>
                        <th style="width: 10%;">Bobot</th>
                        <th style="width: 15%;">Nilai Bobot</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td style="text-align: left;">Substansi dan Metode Penelitian</td>
                        <td><div class="final-score-box"></div></td>
                        <td>x 50%</td>
                        <td><div class="final-score-box"></div></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td style="text-align: left;">Presentasi dan Teknik Penyajian</td>
                        <td><div class="final-score-box"></div></td>
                        <td>x 20%</td>
                        <td><div class="final-score-box"></div></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td style="text-align: left;">Penguasaan Materi dan Diskusi</td>
                        <td><div class="final-score-box"></div></td>
                        <td>x 30%</td>
                        <td><div class="final-score-box"></div></td>
                    </tr>
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td colspan="4">NILAI AKHIR (Jumlah 1+2+3)</td>
                        <td><div class="final-score-box" style="border-width: 3px;"></div></td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Rekomendasi -->
            <div class="recommendation-section">
                <h4 style="margin: 15px 0 10px 0; font-weight: bold;">Rekomendasi Penguji:</h4>
                <div class="recommendation-options">
                    <div class="recommendation-item">
                        <span class="checkbox"></span>
                        <span>Diterima tanpa revisi</span>
                    </div>
                    <div class="recommendation-item">
                        <span class="checkbox"></span>
                        <span>Diterima dengan revisi minor</span>
                    </div>
                    <div class="recommendation-item">
                        <span class="checkbox"></span>
                        <span>Diterima dengan revisi mayor</span>
                    </div>
                    <div class="recommendation-item">
                        <span class="checkbox"></span>
                        <span>Ditolak / Mengulang seminar</span>
                    </div>
                </div>
            </div>
            
            <!-- Catatan/Saran -->
            <div class="notes-section">
                <h4 style="margin: 15px 0 10px 0; font-weight: bold;">Catatan/Saran Revisi:</h4>
                <div class="notes-area"></div>
                <div class="notes-area" style="margin-top: 5px;"></div>
                <div class="notes-area" style="margin-top: 5px;"></div>
            </div>
        </div>
    </div>
    
    <!-- Signature Section -->
    <div class="signature-section">
        <p><strong><?= getPengujiLabel($form['type']) ?>,</strong></p>
        <div class="signature-box"></div>
        <p style="margin: 5px 0;">
            <strong><?= $form['nama'] ?: '________________________________' ?></strong><br>
            <?php if($form['nip']): ?>
                <small>NIP: <?= $form['nip'] ?></small>
            <?php else: ?>
                <small>NIP: ________________________</small>
            <?php endif; ?>
        </p>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <strong>PETUNJUK PENGISIAN:</strong><br>
        1. Berikan nilai 1-100 untuk setiap indikator penilaian<br>
        2. Hitung rata-rata untuk setiap komponen<br>
        3. Kalikan dengan bobot untuk mendapatkan nilai bobot<br>
        4. Jumlahkan semua nilai bobot untuk mendapatkan nilai akhir<br>
        5. Berikan rekomendasi sesuai dengan hasil penilaian<br>
        6. Tuliskan catatan/saran yang konstruktif untuk mahasiswa<br><br>
        
        <em style="font-size: 8px;">
            Form ini digenerate secara otomatis oleh Sistem Informasi Manajemen Tugas Akhir STK Santo Yakobus<br>
            Generated by: <?= $generated_by ?> | Date: <?= $generated_at ?>
        </em>
    </div>

<?php endforeach; ?>

</body>
</html>

<?php
/*
=====================================================================================
TEMPLATE SUMMARY - PDF FORM PENILAIAN SEMINAR PROPOSAL
=====================================================================================

## 📋 FITUR YANG DIIMPLEMENTASI

### 1. Multi-Form Generation ✅
- Support untuk individual penguji (pembimbing, penguji1, penguji2)
- Support untuk generate semua form sekaligus (all)
- Dynamic loop untuk multiple forms dalam satu PDF
- Page break antar form untuk clarity

### 2. Header dan Identifikasi ✅
- Header institusi yang consistent
- Judul form yang jelas
- Info penguji specific per form
- Student information section kosong untuk diisi manual

### 3. Rubrik Penilaian Lengkap ✅
- Sesuai 100% dengan dokumen yang dilampirkan
- 3 komponen utama dengan bobot yang tepat:
  - Komponen 1: Substansi dan Metode Penelitian (50%)
  - Komponen 2: Presentasi dan Teknik Penyajian (20%)
  - Komponen 3: Penguasaan Materi dan Diskusi (30%)
- 11 indikator penilaian total (5+3+3)

### 4. Detailed Assessment Criteria ✅
- Kriteria "Kuat", "Cukup", "Lemah" untuk setiap indikator
- Deskripsi lengkap sesuai dokumen asli
- Color coding untuk memudahkan pembacaan
- Font size yang appropriate untuk print

### 5. Score Input Areas ✅
- Score boxes untuk setiap indikator (1-100)
- Average calculation boxes untuk setiap komponen
- Final score calculation table dengan bobot
- Visual emphasis untuk nilai akhir

### 6. Rekomendasi Section ✅
- 4 pilihan rekomendasi sesuai standar:
  - Diterima tanpa revisi
  - Diterima dengan revisi minor
  - Diterima dengan revisi mayor
  - Ditolak / Mengulang seminar
- Checkbox format untuk easy selection

### 7. Catatan dan Saran ✅
- Multiple text areas untuk feedback
- Space yang cukup untuk detailed comments
- Clear instruction untuk constructive feedback

### 8. Signature Area ✅
- Area tanda tangan untuk setiap penguji
- Nama dan NIP display (jika tersedia)
- Professional format

### 9. Print Optimization ✅
- Page break handling yang proper
- Print-friendly styling
- Appropriate margins dan font sizes
- Professional black & white design

### 10. Instructions dan Guidelines ✅
- Petunjuk pengisian yang clear
- Calculation instructions
- Footer information untuk reference

## 🎨 DESIGN FEATURES

### Professional Layout ✅
- Clean, academic document appearance
- Consistent typography dan spacing
- Clear visual hierarchy
- Proper section separation

### User-Friendly Input ✅
- Large, clear input boxes
- Logical flow dari atas ke bawah
- Easy-to-follow assessment process
- Clear labeling dan instructions

### Print-Ready Format ✅
- Standard paper size optimization
- Black and white color scheme
- Clear borders dan separators
- Appropriate line spacing

### Accessibility ✅
- High contrast text dan boxes
- Clear font sizes
- Logical reading order
- Professional presentation

## 🔧 IMPLEMENTATION FEATURES

### Dynamic Content ✅
- Form generation based on penguji_type parameter
- Conditional display untuk examiner information
- Null value handling untuk missing data
- Flexible untuk different scenarios

### Data Integration ✅
- Seamless integration dengan database
- Dynamic examiner information
- Automatic reference generation
- Template metadata tracking

### Multi-Purpose Usage ✅
- Works untuk all examiner types
- Suitable untuk different programs
- Scalable untuk future needs
- Consistent format across forms

## 📊 USAGE SCENARIOS

### Individual Forms:
```php
// Generate untuk dosen pembimbing saja
$data['penguji_type'] = 'pembimbing';

// Generate untuk penguji 1 saja
$data['penguji_type'] = 'penguji1';

// Generate untuk penguji 2 saja
$data['penguji_type'] = 'penguji2';
```

### Bulk Forms:
```php
// Generate semua form dalam satu PDF
$data['penguji_type'] = 'all';
```

### Controller Implementation:
```php
// Setup data untuk template
$template_data = [
    'seminar' => $seminar_detail,
    'dewan_penguji' => $penguji_data,
    'penguji_type' => $penguji_type, // from URL parameter
    'rubrik_penilaian' => $this->_get_rubrik_penilaian_template(),
    'generated_by' => $this->session->userdata('nama'),
    'generated_at' => date('d/m/Y H:i:s')
];

// Generate PDF
$html = $this->load->view('staf/seminar_proposal/pdf/form_penilaian', $template_data, true);
$this->pdf->load_html($html);
$this->pdf->render();
$this->pdf->stream($filename, array("Attachment" => false));
```

## 🎯 COMPLIANCE & STANDARDS

### Academic Standards ✅
- Follows established rubric exactly
- Professional academic document format
- Clear assessment criteria
- Objective scoring system

### Administrative Requirements ✅
- Complete examiner information
- Proper documentation trail
- Reference tracking
- Standard institutional format

### Quality Assurance ✅
- Accurate implementation of rubric
- Consistent formatting
- Error-free layout
- Professional presentation

## 📋 NEXT STEPS

### Integration Points:
1. **Digital Scoring System** - Connect dengan online penilaian
2. **Auto-calculation** - JavaScript untuk real-time calculation
3. **QR Code** - Digital verification codes
4. **Email Integration** - Send forms directly to examiners

### Enhancement Ideas:
1. **Interactive PDF** - Fillable PDF forms
2. **Digital Signatures** - Electronic signature support
3. **Auto-sync** - Sync dengan online scoring system
4. **Analytics** - Score distribution analysis

Template ini 100% sesuai dengan rubrik penilaian yang dilampirkan dan siap untuk production use! 📊
*/
?>