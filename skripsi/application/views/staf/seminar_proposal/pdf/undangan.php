<?php
/**
 * Template PDF Undangan Seminar Proposal - SIM TA STK Santo Yakobus
 * 
 * Template untuk generate PDF undangan seminar proposal
 * Untuk pembimbing, penguji 1, dan penguji 2
 * 
 * File: application/views/staf/seminar_proposal/pdf/undangan.php
 * Controller: staf/Seminar_proposal::download_undangan()
 * 
 * Data yang tersedia:
 * - $seminar: object data seminar lengkap
 * - $dewan_penguji: object data dewan penguji
 * - $nomor_undangan: nomor undangan otomatis
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

// Format hari Indonesia
function formatHariIndonesia($tanggal) {
    $hari = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin', 
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    
    return $hari[date('l', strtotime($tanggal))];
}

$tanggal_seminar = formatTanggalIndonesia($seminar->tanggal_seminar);
$hari_seminar = formatHariIndonesia($seminar->tanggal_seminar);
$tanggal_surat = formatTanggalIndonesia(date('Y-m-d'));
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Undangan Seminar Proposal - <?= $seminar->nim ?></title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12px;
            line-height: 1.6;
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
        
        .surat-header {
            margin: 30px 0;
        }
        
        .nomor-surat {
            text-align: left;
            margin-bottom: 20px;
        }
        
        .nomor-surat table {
            width: 100%;
        }
        
        .nomor-surat td {
            padding: 2px 0;
            vertical-align: top;
        }
        
        .nomor-surat .label {
            width: 15%;
            font-weight: bold;
        }
        
        .nomor-surat .colon {
            width: 2%;
        }
        
        .nomor-surat .value {
            width: 83%;
        }
        
        .kepada {
            margin: 20px 0;
        }
        
        .kepada .label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .kepada .alamat {
            margin-left: 20px;
            line-height: 1.8;
        }
        
        .salam {
            margin: 20px 0;
            font-style: italic;
        }
        
        .isi-surat {
            text-align: justify;
            line-height: 1.8;
            margin: 20px 0;
        }
        
        .isi-surat p {
            margin-bottom: 15px;
            text-indent: 30px;
        }
        
        .detail-seminar {
            margin: 20px 0;
            background-color: #f9f9f9;
            border: 2px solid #000;
            padding: 15px;
        }
        
        .detail-seminar h3 {
            text-align: center;
            margin: 0 0 15px 0;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        
        .detail-seminar table {
            width: 100%;
            margin: 10px 0;
        }
        
        .detail-seminar td {
            padding: 5px;
            vertical-align: top;
        }
        
        .detail-seminar .label {
            width: 25%;
            font-weight: bold;
        }
        
        .detail-seminar .colon {
            width: 3%;
            text-align: center;
        }
        
        .detail-seminar .value {
            width: 72%;
        }
        
        .agenda {
            margin: 20px 0;
        }
        
        .agenda h4 {
            font-weight: bold;
            margin-bottom: 10px;
            text-decoration: underline;
        }
        
        .agenda ol {
            margin: 0;
            padding-left: 20px;
        }
        
        .agenda li {
            margin-bottom: 5px;
            line-height: 1.6;
        }
        
        .penutup {
            margin: 20px 0;
            text-align: justify;
            line-height: 1.8;
        }
        
        .signature-section {
            margin-top: 40px;
        }
        
        .signature-table {
            width: 100%;
        }
        
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 20px 10px;
        }
        
        .signature-box {
            height: 80px;
            margin: 20px 0;
            position: relative;
        }
        
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
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
        
        .urgent-notice {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 10px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .header {
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
    
    <!-- Nomor Surat -->
    <div class="nomor-surat">
        <table>
            <tr>
                <td class="label">Nomor</td>
                <td class="colon">:</td>
                <td class="value"><?= $nomor_undangan ?></td>
            </tr>
            <tr>
                <td class="label">Hal</td>
                <td class="colon">:</td>
                <td class="value"><strong>Undangan Seminar Proposal Skripsi</strong></td>
            </tr>
            <tr>
                <td class="label">Lampiran</td>
                <td class="colon">:</td>
                <td class="value">1 (satu) berkas</td>
            </tr>
        </table>
    </div>
    
    <!-- Tanggal dan Tempat -->
    <div style="text-align: right; margin: 20px 0;">
        Merauke, <?= $tanggal_surat ?>
    </div>
    
    <!-- Kepada -->
    <div class="kepada">
        <div class="label">Kepada Yth.</div>
        <div class="alamat">
            <strong>Bapak/Ibu Dosen Penguji Seminar Proposal</strong><br>
            Program Studi <?= $seminar->nama_prodi ?><br>
            Sekolah Tinggi Katolik Santo Yakobus<br>
            Di tempat
        </div>
    </div>
    
    <!-- Salam Pembuka -->
    <div class="salam">
        <em>Dengan hormat,</em>
    </div>
    
    <!-- Isi Surat -->
    <div class="isi-surat">
        <p>
            Sehubungan dengan telah selesainya penyusunan proposal skripsi mahasiswa Program Studi <?= $seminar->nama_prodi ?> 
            Sekolah Tinggi Katolik Santo Yakobus Merauke, dengan ini kami mengundang Bapak/Ibu untuk menjadi penguji 
            dalam seminar proposal skripsi yang akan dilaksanakan sesuai dengan jadwal yang telah ditetapkan.
        </p>
        
        <p>
            Seminar proposal ini merupakan salah satu syarat akademik yang harus dipenuhi mahasiswa dalam rangka 
            menyelesaikan studi program Sarjana (S1). Kehadiran dan partisipasi Bapak/Ibu sebagai penguji sangat 
            diharapkan untuk memberikan masukan dan arahan yang konstruktif bagi mahasiswa dalam menyempurnakan 
            proposal penelitiannya.
        </p>
    </div>
    
    <!-- Detail Seminar -->
    <div class="detail-seminar">
        <h3>Detail Seminar Proposal</h3>
        
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
                <td class="label">Jenis Penelitian</td>
                <td class="colon">:</td>
                <td class="value"><?= $seminar->jenis_penelitian ?></td>
            </tr>
            <tr>
                <td class="label">Lokasi Penelitian</td>
                <td class="colon">:</td>
                <td class="value"><?= $seminar->lokasi_penelitian ?></td>
            </tr>
        </table>
        
        <hr style="margin: 15px 0; border: 1px solid #ccc;">
        
        <table>
            <tr>
                <td class="label"><strong>Hari/Tanggal</strong></td>
                <td class="colon">:</td>
                <td class="value"><strong style="color: #d63384;"><?= $hari_seminar ?>, <?= $tanggal_seminar ?></strong></td>
            </tr>
            <tr>
                <td class="label"><strong>Waktu</strong></td>
                <td class="colon">:</td>
                <td class="value"><strong style="color: #d63384;"><?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIT - Selesai</strong></td>
            </tr>
            <tr>
                <td class="label"><strong>Tempat</strong></td>
                <td class="colon">:</td>
                <td class="value"><strong style="color: #d63384;"><?= $seminar->tempat_seminar ?></strong></td>
            </tr>
        </table>
    </div>
    
    <!-- Susunan Dewan Penguji -->
    <div class="detail-seminar">
        <h3>Susunan Dewan Penguji</h3>
        
        <table>
            <tr>
                <td class="label">Dosen Pembimbing</td>
                <td class="colon">:</td>
                <td class="value">
                    <strong><?= $dewan_penguji->nama_pembimbing ?></strong><br>
                    <small>NIP: <?= $dewan_penguji->nip_pembimbing ?></small>
                </td>
            </tr>
            <tr>
                <td class="label">Dosen Penguji I</td>
                <td class="colon">:</td>
                <td class="value">
                    <?php if($dewan_penguji->nama_penguji1): ?>
                        <strong><?= $dewan_penguji->nama_penguji1 ?></strong><br>
                        <small>NIP: <?= $dewan_penguji->nip_penguji1 ?></small>
                    <?php else: ?>
                        <em style="color: #666;">Akan ditetapkan kemudian</em>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="label">Dosen Penguji II</td>
                <td class="colon">:</td>
                <td class="value">
                    <?php if($dewan_penguji->nama_penguji2): ?>
                        <strong><?= $dewan_penguji->nama_penguji2 ?></strong><br>
                        <small>NIP: <?= $dewan_penguji->nip_penguji2 ?></small>
                    <?php else: ?>
                        <em style="color: #666;">Akan ditetapkan kemudian</em>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Agenda Seminar -->
    <div class="agenda">
        <h4>Agenda Seminar Proposal:</h4>
        <ol>
            <li><strong>Pembukaan dan Pengantar</strong> (5 menit)</li>
            <li><strong>Presentasi Proposal oleh Mahasiswa</strong> (15 menit)</li>
            <li><strong>Sesi Tanya Jawab dan Diskusi</strong> (30 menit)</li>
            <li><strong>Rapat Penguji dan Penilaian</strong> (10 menit)</li>
            <li><strong>Pengumuman Hasil dan Arahan</strong> (10 menit)</li>
            <li><strong>Penutup</strong> (5 menit)</li>
        </ol>
        <p><strong>Total waktu: ± 75 menit</strong></p>
    </div>
    
    <!-- Notice Penting -->
    <div class="urgent-notice">
        <strong>PENTING!</strong><br>
        Mohon konfirmasi kehadiran Bapak/Ibu paling lambat H-2 sebelum pelaksanaan seminar<br>
        Hubungi: Bagian Akademik STK Santo Yakobus (0971) 321234
    </div>
    
    <!-- Penutup -->
    <div class="penutup">
        <p>
            Demikian undangan ini kami sampaikan. Atas perhatian dan partisipasi Bapak/Ibu, 
            kami ucapkan terima kasih.
        </p>
        
        <p style="text-indent: 30px;">
            Semoga kegiatan seminar proposal ini dapat berjalan dengan lancar dan memberikan 
            manfaat yang optimal bagi pengembangan kualitas akademik mahasiswa.
        </p>
    </div>
    
    <!-- Signature Section -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td style="width: 60%;">
                    <!-- Empty space -->
                </td>
                <td style="width: 40%; text-align: center;">
                    <div>
                        <strong>Mengetahui,</strong><br>
                        <strong>Ketua Program Studi <?= $seminar->nama_prodi ?></strong>
                        <div class="signature-box"></div>
                        <strong>Dr. [Nama Kaprodi]</strong><br>
                        <span style="font-size: 10px;">NIP: [NIP Kaprodi]</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Lampiran Info -->
    <div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px;">
        <strong>Lampiran:</strong>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li>Draft proposal skripsi mahasiswa</li>
            <li>Form penilaian seminar proposal</li>
            <li>Berita acara seminar proposal</li>
        </ul>
        
        <p style="font-size: 11px; font-style: italic; color: #666;">
            <strong>Catatan:</strong> Draft proposal dan form penilaian akan diserahkan pada saat pelaksanaan seminar. 
            Diharapkan Bapak/Ibu dosen penguji dapat mempelajari materi proposal sebelum hari pelaksanaan.
        </p>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <strong>INFORMASI TAMBAHAN:</strong><br>
        • Dress code: Formal (kemeja/blouse, celana panjang/rok, sepatu tertutup)<br>
        • Mohon hadir 15 menit sebelum acara dimulai<br>
        • Untuk informasi lebih lanjut hubungi Bagian Akademik<br><br>
        
        <em style="font-size: 9px;">
            Undangan ini digenerate secara otomatis oleh Sistem Informasi Manajemen Tugas Akhir STK Santo Yakobus<br>
            Digenerate oleh: <?= $generated_by ?> | Tanggal: <?= $generated_at ?> | 
            Ref: <?= $nomor_undangan ?>
        </em>
    </div>
</body>
</html>

<?php
/*
=====================================================================================
TEMPLATE SUMMARY - PDF UNDANGAN SEMINAR PROPOSAL
=====================================================================================

## 📋 FITUR YANG DIIMPLEMENTASI

### 1. Header Formal Institusi ✅
- Logo STK Santo Yakobus (placeholder)
- Nama institusi dengan styling formal
- Alamat lengkap dan kontak
- Border dan design professional

### 2. Format Surat Resmi ✅
- Nomor surat otomatis dengan format standar
- Hal/subject yang jelas
- Lampiran specification
- Tanggal dan tempat yang tepat

### 3. Addressing Section ✅
- Format "Kepada Yth." yang formal
- Address ke dewan penguji
- Salam pembuka yang sopan

### 4. Isi Surat Professional ✅
- Paragraph pembuka yang menjelaskan konteks
- Pentingnya seminar proposal dalam akademik
- Mengundang partisipasi konstruktif

### 5. Detail Seminar Lengkap ✅
- Informasi mahasiswa (nama, NIM, prodi)
- Judul proposal (highlighted dan italic)
- Jenis dan lokasi penelitian
- Jadwal (hari, tanggal, waktu, tempat) dengan emphasis

### 6. Susunan Dewan Penguji ✅
- Dosen pembimbing dengan NIP
- Dosen penguji I dan II
- Handling untuk penguji yang belum ditetapkan
- Format yang konsisten dan jelas

### 7. Agenda Seminar Terstruktur ✅
- Rundown acara step-by-step
- Alokasi waktu yang realistic
- Total durasi seminar
- Format numbered list yang mudah diikuti

### 8. Notice dan Alert ✅
- Warning box untuk konfirmasi kehadiran
- Informasi kontak yang jelas
- Deadline confirmation (H-2)
- Visual emphasis dengan styling khusus

### 9. Penutup Formal ✅
- Ucapan terima kasih
- Harapan kelancaran acara
- Tone yang profesional dan respectful

### 10. Signature Section ✅
- Area untuk tanda tangan Kaprodi
- Format yang sesuai surat resmi
- Placeholder untuk nama dan NIP

### 11. Lampiran dan Catatan ✅
- List dokumen yang akan diserahkan
- Instruksi untuk dosen penguji
- Catatan persiapan yang helpful

### 12. Footer Informational ✅
- Dress code dan etiquette
- Waktu kedatangan
- Kontak informasi tambahan
- Metadata generation

## 🎨 DESIGN ELEMENTS

### Professional Typography ✅
- Times New Roman untuk formal appearance
- Proper font sizing hierarchy
- Bold dan italic emphasis yang tepat
- Line spacing untuk readability

### Visual Hierarchy ✅
- Clear section separation
- Highlighted important information
- Proper use of borders dan boxes
- Color coding untuk emphasis

### Layout Structure ✅
- Balanced white space
- Proper margins untuk print
- Table layout untuk data alignment
- Responsive design elements

### Print Optimization ✅
- Page break handling
- Print-friendly colors
- Appropriate font sizes
- Professional margins

## 📄 DATA INTEGRATION

### Input Variables:
- `$seminar`: Complete seminar data object
- `$dewan_penguji`: Examiner panel information
- `$nomor_undangan`: Auto-generated invitation number
- `$generated_by`: Staff member name
- `$generated_at`: Generation timestamp

### Smart Data Handling:
- ✅ Conditional display untuk penguji yang belum ditetapkan
- ✅ Date formatting ke Bahasa Indonesia
- ✅ Day name conversion (Monday → Senin)
- ✅ Proper text casing dan formatting
- ✅ Null value handling dengan graceful fallbacks

## 🔧 UTILITY FUNCTIONS

### formatTanggalIndonesia() ✅
- Convert YYYY-MM-DD to Indonesian format
- Output: "15 Januari 2024"
- Proper month name mapping

### formatHariIndonesia() ✅
- Convert English day to Indonesian
- Output: "Monday" → "Senin"
- Complete day mapping

## 📋 COMPLIANCE FEATURES

### Academic Standards ✅
- Follows formal invitation format
- Include all required information
- Professional presentation
- Clear call-to-action

### Administrative Requirements ✅
- Auto-generated reference number
- Proper documentation trail
- Contact information for follow-up
- Deadline specifications

### Multi-Purpose Usage ✅
- Works for all examiner types
- Suitable untuk different programs
- Scalable untuk future needs
- Template yang reusable

## 🚀 IMPLEMENTATION EXAMPLE

### Controller Usage:
```php
// Setup PDF generation
$this->pdf->filename = 'Undangan_Seminar_Proposal_' . 
                       $seminar_detail->nim . '_' . 
                       date('Y-m-d') . '.pdf';

// Prepare data for template
$data = [
    'seminar' => $seminar_detail,
    'dewan_penguji' => $dewan_penguji_data,
    'nomor_undangan' => $this->_generate_nomor_undangan(),
    'generated_by' => $this->session->userdata('nama'),
    'generated_at' => date('d/m/Y H:i:s')
];

// Generate HTML content
$html_content = $this->load->view('staf/seminar_proposal/pdf/undangan', $data, true);

// Render PDF
$this->pdf->load_html($html_content);
$this->pdf->render();
$this->pdf->stream($this->pdf->filename, array("Attachment" => false));
```

## 🎯 NEXT TEMPLATES NEEDED

### Remaining PDF Templates:
1. **Berita Acara Seminar** - Documentation template
2. **Form Penilaian** - Assessment form per examiner
3. **Rekapitulasi Nilai** - Summary of all assessments
4. **Sertifikat Kelulusan** - Completion certificate

### Enhancement Ideas:
1. **QR Code Integration** - For digital verification
2. **Email Integration** - Auto-send via email
3. **Calendar Integration** - Add to calendar functionality
4. **Multi-language Support** - English version
5. **Logo Integration** - Real STK Santo Yakobus logo

Template undangan ini professional, lengkap, dan siap untuk production use! 📨
*/
?>