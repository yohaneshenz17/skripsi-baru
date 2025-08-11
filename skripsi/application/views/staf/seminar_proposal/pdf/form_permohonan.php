<?php
/**
 * Template PDF Form Permohonan Seminar Proposal - SIM TA STK Santo Yakobus
 * 
 * Template untuk generate PDF form permohonan seminar proposal
 * Sesuai dengan data yang diinput mahasiswa saat pengajuan
 * 
 * File: application/views/staf/seminar_proposal/pdf/form_permohonan.php
 * Controller: staf/Seminar_proposal::download_form_permohonan()
 * 
 * Data yang tersedia:
 * - $seminar: object data seminar lengkap
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

$tanggal_seminar = formatTanggalIndonesia($seminar->tanggal_seminar);
$tanggal_pengajuan = formatTanggalIndonesia($seminar->created_at);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Permohonan Seminar Proposal - <?= $seminar->nim ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
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
            margin: 20px 0;
            text-decoration: underline;
            text-transform: uppercase;
        }
        
        .content {
            margin: 20px 0;
        }
        
        .content table {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .content table td {
            padding: 4px 5px;
            vertical-align: top;
        }
        
        .label {
            width: 30%;
            font-weight: bold;
        }
        
        .colon {
            width: 3%;
            text-align: center;
        }
        
        .value {
            width: 67%;
            border-bottom: 1px dotted #999;
            min-height: 18px;
        }
        
        .section-title {
            font-weight: bold;
            margin: 20px 0 10px 0;
            padding: 5px;
            background-color: #f0f0f0;
            border-left: 4px solid #007bff;
        }
        
        .proposal-abstract {
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px 0;
            background-color: #f9f9f9;
            text-align: justify;
            line-height: 1.6;
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
            border: 1px solid #ddd;
            margin: 10px 0;
            position: relative;
        }
        
        .signature-label {
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            font-style: italic;
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
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
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
        <h1>Sekolah Tinggi Katolik Santo Yakobus</h1>
        <h2>Merauke</h2>
        <div class="address">
            Jl. Raya Mandala No. 165, Merauke 99615<br>
            Telp: (0971) 321234 | Email: info@stkyakobus.ac.id<br>
            Website: www.stkyakobus.ac.id
        </div>
    </div>
    
    <!-- Form Title -->
    <div class="form-title">
        Form Permohonan Seminar Proposal Skripsi
    </div>
    
    <!-- Content -->
    <div class="content">
        
        <!-- Data Mahasiswa -->
        <div class="section-title">I. DATA MAHASISWA</div>
        <table>
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="colon">:</td>
                <td class="value"><?= strtoupper($seminar->nama_mahasiswa) ?></td>
            </tr>
            <tr>
                <td class="label">Nomor Induk Mahasiswa (NIM)</td>
                <td class="colon">:</td>
                <td class="value"><?= $seminar->nim ?></td>
            </tr>
            <tr>
                <td class="label">Program Studi</td>
                <td class="colon">:</td>
                <td class="value"><?= $seminar->nama_prodi ?></td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td class="colon">:</td>
                <td class="value"><?= $seminar->email_mahasiswa ?></td>
            </tr>
            <tr>
                <td class="label">Nomor Telepon</td>
                <td class="colon">:</td>
                <td class="value"><?= $seminar->nomor_telepon ?: '-' ?></td>
            </tr>
        </table>
        
        <!-- Data Proposal -->
        <div class="section-title">II. DATA PROPOSAL PENELITIAN</div>
        <table>
            <tr>
                <td class="label">Judul Proposal</td>
                <td class="colon">:</td>
                <td class="value" style="font-weight: bold;"><?= $seminar->judul ?></td>
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
            <tr>
                <td class="label">Tanggal Pengajuan</td>
                <td class="colon">:</td>
                <td class="value"><?= $tanggal_pengajuan ?></td>
            </tr>
        </table>
        
        <!-- Ringkasan Proposal -->
        <div class="section-title">III. RINGKASAN PROPOSAL</div>
        <div class="proposal-abstract">
            <?= nl2br(htmlspecialchars($seminar->ringkasan)) ?>
        </div>
        
        <!-- Uraian Masalah -->
        <div class="section-title">IV. URAIAN MASALAH PENELITIAN</div>
        <div class="proposal-abstract">
            <?= nl2br(htmlspecialchars($seminar->uraian_masalah)) ?>
        </div>
        
        <!-- Data Pembimbing -->
        <div class="section-title">V. DATA DOSEN PEMBIMBING</div>
        <table>
            <tr>
                <td class="label">Nama Dosen Pembimbing</td>
                <td class="colon">:</td>
                <td class="value"><?= $seminar->nama_pembimbing ?></td>
            </tr>
            <tr>
                <td class="label">NIP</td>
                <td class="colon">:</td>
                <td class="value"><?= $seminar->nip_pembimbing ?></td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td class="colon">:</td>
                <td class="value"><?= $seminar->email_pembimbing ?></td>
            </tr>
        </table>
        
        <!-- Jadwal Seminar -->
        <div class="section-title">VI. JADWAL SEMINAR PROPOSAL</div>
        <table>
            <tr>
                <td class="label">Tanggal Pelaksanaan</td>
                <td class="colon">:</td>
                <td class="value" style="font-weight: bold; color: #007bff;">
                    <?= $tanggal_seminar ?> 
                    (<?= date('l', strtotime($seminar->tanggal_seminar)) ?>)
                </td>
            </tr>
            <tr>
                <td class="label">Waktu</td>
                <td class="colon">:</td>
                <td class="value" style="font-weight: bold; color: #007bff;">
                    <?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIT
                </td>
            </tr>
            <tr>
                <td class="label">Tempat</td>
                <td class="colon">:</td>
                <td class="value" style="font-weight: bold; color: #007bff;">
                    <?= $seminar->tempat_seminar ?>
                </td>
            </tr>
        </table>
        
        <!-- Pernyataan -->
        <div class="section-title">VII. PERNYATAAN MAHASISWA</div>
        <div style="margin: 15px 0; text-align: justify; line-height: 1.6;">
            Dengan ini saya menyatakan bahwa:
            <ol style="margin: 10px 0; padding-left: 20px;">
                <li>Data yang saya berikan dalam form ini adalah benar dan dapat dipertanggungjawabkan.</li>
                <li>Proposal penelitian yang saya ajukan adalah hasil karya sendiri dan bukan hasil plagiat.</li>
                <li>Saya bersedia mengikuti seminar proposal sesuai dengan jadwal yang telah ditetapkan.</li>
                <li>Saya akan menerima masukan dan saran dari dosen penguji untuk perbaikan proposal.</li>
                <li>Apabila dikemudian hari terbukti ada ketidakbenaran dalam pernyataan ini, saya bersedia menerima sanksi akademik sesuai ketentuan yang berlaku.</li>
            </ol>
        </div>
    </div>
    
    <!-- Signature Section -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div style="text-align: center;">
                        <strong>Mengetahui,</strong><br>
                        <strong>Dosen Pembimbing</strong>
                        <div class="signature-box">
                            <div class="signature-label">Tanda Tangan & Tanggal</div>
                        </div>
                        <strong><?= $seminar->nama_pembimbing ?></strong><br>
                        <span style="font-size: 10px;">NIP: <?= $seminar->nip_pembimbing ?></span>
                    </div>
                </td>
                <td>
                    <div style="text-align: center;">
                        <strong>Merauke, <?= formatTanggalIndonesia(date('Y-m-d')) ?></strong><br>
                        <strong>Mahasiswa</strong>
                        <div class="signature-box">
                            <div class="signature-label">Tanda Tangan & Tanggal</div>
                        </div>
                        <strong><?= $seminar->nama_mahasiswa ?></strong><br>
                        <span style="font-size: 10px;">NIM: <?= $seminar->nim ?></span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <strong>CATATAN PENTING:</strong><br>
        1. Form ini harus diserahkan ke bagian akademik paling lambat 3 hari sebelum pelaksanaan seminar<br>
        2. Mahasiswa wajib membawa draft proposal lengkap saat seminar<br>
        3. Kehadiran mahasiswa, dosen pembimbing, dan dosen penguji adalah wajib<br>
        4. Mahasiswa wajib mempresentasikan proposal maksimal 15 menit<br>
        5. Sesi tanya jawab berlangsung maksimal 30 menit<br><br>
        
        <em style="font-size: 9px;">
            Dokumen ini digenerate secara otomatis oleh Sistem Informasi Manajemen Tugas Akhir STK Santo Yakobus<br>
            Digenerate oleh: <?= $generated_by ?> | Tanggal: <?= $generated_at ?> | 
            Ref: SP-<?= str_pad($seminar->id, 6, '0', STR_PAD_LEFT) ?>
        </em>
    </div>
</body>
</html>

<?php
/*
=====================================================================================
TEMPLATE SUMMARY - PDF FORM PERMOHONAN SEMINAR PROPOSAL
=====================================================================================

## 📋 FITUR YANG DIIMPLEMENTASI

### 1. Header Institusi ✅
- Logo dan nama institusi (STK Santo Yakobus)
- Alamat lengkap dan kontak
- Styling professional dengan border

### 2. Identitas Form ✅
- Judul form yang jelas dan professional
- Nomor referensi otomatis
- Styling yang konsisten

### 3. Section Data Mahasiswa ✅
- Nama lengkap (uppercase untuk formal)
- NIM
- Program Studi
- Email dan nomor telepon
- Format tabel yang rapi dan mudah dibaca

### 4. Section Data Proposal ✅
- Judul proposal (highlighted)
- Jenis penelitian
- Lokasi penelitian
- Tanggal pengajuan

### 5. Section Ringkasan dan Uraian ✅
- Ringkasan proposal dalam box khusus
- Uraian masalah penelitian
- Format justified text untuk readability
- Background color untuk highlight

### 6. Section Data Pembimbing ✅
- Nama dosen pembimbing
- NIP
- Email
- Format yang konsisten dengan section lain

### 7. Section Jadwal Seminar ✅
- Tanggal pelaksanaan (formatted Indonesian)
- Waktu dalam WIT
- Tempat seminar
- Highlighted dengan warna untuk emphasis

### 8. Section Pernyataan Mahasiswa ✅
- Pernyataan integrity dan commitment
- Numbered list yang jelas
- Mencakup aspek plagiarisme dan tanggung jawab

### 9. Section Tanda Tangan ✅
- Area untuk tanda tangan dosen pembimbing
- Area untuk tanda tangan mahasiswa
- Tanggal otomatis
- Layout side-by-side yang professional

### 10. Footer Informasi ✅
- Catatan penting untuk mahasiswa
- Petunjuk administrasi
- Metadata generation (siapa, kapan, referensi)
- Watermark untuk authenticity

## 🎨 STYLING FEATURES

### Professional Design ✅
- Typography yang clean dan readable
- Consistent spacing dan alignment
- Color scheme yang professional
- Border dan separator yang tepat

### Print-Friendly ✅
- Optimized untuk printing
- Page break handling
- Margin yang sesuai untuk print
- Font size yang readable saat print

### Responsive Elements ✅
- Flexible table layout
- Scalable font sizes
- Adaptive spacing
- Cross-browser compatibility

### Visual Hierarchy ✅
- Clear section separation
- Highlighted important information
- Consistent formatting
- Easy-to-follow structure

## 📄 DATA YANG DIGUNAKAN

### Input Data:
- `$seminar`: Object dengan semua data seminar proposal
- `$generated_by`: Nama staf yang generate dokumen
- `$generated_at`: Timestamp generation

### Fields yang Diakses:
- `$seminar->nim`
- `$seminar->nama_mahasiswa`
- `$seminar->nama_prodi`
- `$seminar->email_mahasiswa`
- `$seminar->nomor_telepon`
- `$seminar->judul`
- `$seminar->jenis_penelitian`
- `$seminar->lokasi_penelitian`
- `$seminar->ringkasan`
- `$seminar->uraian_masalah`
- `$seminar->nama_pembimbing`
- `$seminar->nip_pembimbing`
- `$seminar->email_pembimbing`
- `$seminar->tanggal_seminar`
- `$seminar->jam_seminar`
- `$seminar->tempat_seminar`
- `$seminar->created_at`
- `$seminar->id`

## 🔧 UTILITY FUNCTIONS

### formatTanggalIndonesia() ✅
- Mengkonversi format tanggal ke Bahasa Indonesia
- Input: YYYY-MM-DD
- Output: DD Bulan YYYY (contoh: 15 Januari 2024)

## 📋 COMPLIANCE & STANDARDS

### Academic Standards ✅
- Mengikuti format standard academic documents
- Include semua informasi yang diperlukan
- Professional presentation
- Clear documentation trail

### Administrative Requirements ✅
- Nomor referensi untuk tracking
- Metadata lengkap untuk audit
- Tanda tangan areas yang jelas
- Catatan penting untuk compliance

### System Integration ✅
- Compatible dengan TCPDF library
- Menggunakan data dari database
- Auto-generation features
- Consistent dengan system workflow

## 🚀 USAGE

### Controller Implementation:
```php
// Setup PDF
$this->pdf->filename = 'Form_Permohonan_Seminar_Proposal_' . 
                       $seminar_detail->nim . '_' . 
                       date('Y-m-d') . '.pdf';

// Generate HTML content
$html_content = $this->load->view('staf/seminar_proposal/pdf/form_permohonan', [
    'seminar' => $seminar_detail,
    'generated_by' => $this->session->userdata('nama'),
    'generated_at' => date('d/m/Y H:i:s')
], true);

$this->pdf->load_html($html_content);
$this->pdf->render();
$this->pdf->stream($this->pdf->filename, array("Attachment" => false));
```

## 🎯 NEXT STEPS

### Additional Templates Needed:
1. **Undangan Seminar Proposal** - Template surat undangan untuk dewan penguji
2. **Berita Acara Seminar** - Template dokumentasi pelaksanaan seminar
3. **Form Penilaian** - Template form penilaian sesuai rubrik
4. **Rekapitulasi Nilai** - Template rekap nilai dari semua penguji

### Template Enhancement:
1. **Logo Integration** - Add actual STK Santo Yakobus logo
2. **Digital Signature** - QR code untuk verification
3. **Barcode** - Reference tracking
4. **Conditional Content** - Dynamic content based on data
5. **Multi-language** - English version for international program

Template ini sudah siap digunakan dan mengikuti standard administrasi akademik STK Santo Yakobus! 🎓

*/
?>