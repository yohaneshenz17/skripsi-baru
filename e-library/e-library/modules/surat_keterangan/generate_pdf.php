<?php
/**
 * Generator PDF Surat Keterangan
 * Fitur: Auto-delete file PDF > 1 hari & Layout Fix (Logo, QR, TTD)
 */

require_once 'config.php';
require_once SK_LIB_PATH . 'fpdf/fpdf.php';
require_once SK_LIB_PATH . 'phpqrcode/qrlib.php';

cekLoginAdmin();

// ==================================================================
// FUNGSI PEMBERSIH PDF LAMA (AUTO DELETE 1 HARI)
// ==================================================================
function bersihkanPDFLama($directory, $batas_hari = 1) {
    if (!is_dir($directory)) {
        return;
    }

    // Konversi hari ke detik (1 hari * 24 jam * 3600 detik)
    $batas_usia_detik = $batas_hari * 24 * 3600;
    $waktu_sekarang = time();

    $files = glob($directory . '*.pdf');

    foreach ($files as $file) {
        if (is_file($file)) {
            // Jika file lebih tua dari batas waktu, hapus
            if (($waktu_sekarang - filemtime($file)) > $batas_usia_detik) {
                @unlink($file);
            }
        }
    }
}

// JALANKAN PEMBERSIHAN (HAPUS FILE > 1 HARI)
bersihkanPDFLama(SK_PDF_PATH, 1); 
// ==================================================================

// Get surat ID
$surat_id = $_GET['id'] ?? 0;

if (!$surat_id) {
    die('ID Surat tidak valid');
}

// --- QUERY DATABASE (VERSI FIX) ---
$sql = "SELECT sk.id, sk.nomor_surat, sk.nim, sk.tanggal_terbit, sk.jenis_surat, 
               m.nama AS nama_mahasiswa, 
               m.angkatan, 
               m.program_studi AS nama_prodi
        FROM surat_keterangan sk
        JOIN mahasiswa m ON sk.nim = m.nim
        WHERE sk.id = ? AND sk.status = 'terbit'";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Error SQL Prepare: ' . $conn->error);
}

$stmt->bind_param("i", $surat_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    die('Surat tidak ditemukan atau sudah dibatalkan');
}

$stmt->bind_result(
    $db_id, $db_nomor_surat, $db_nim, $db_tanggal, $db_jenis, 
    $db_nama_mhs, $db_angkatan, $db_nama_prodi
);

$stmt->fetch();

$surat = [
    'id'             => $db_id,
    'nomor_surat'    => $db_nomor_surat,
    'nim'            => $db_nim,
    'tanggal_terbit' => $db_tanggal,
    'jenis_surat'    => $db_jenis,
    'nama_mahasiswa' => $db_nama_mhs,
    'angkatan'       => $db_angkatan,
    'nama_prodi'     => $db_nama_prodi
];

// Generate QR Code
$qr_data = generateQRData(
    $surat['nim'],
    $surat['nama_mahasiswa'],
    $surat['nomor_surat'],
    $surat['tanggal_terbit']
);

$qr_filename = 'qr_' . $surat['id'] . '.png';
$qr_path = SK_PDF_PATH . 'temp/' . $qr_filename;

// Buat folder temp jika belum ada
if (!file_exists(SK_PDF_PATH . 'temp/')) {
    mkdir(SK_PDF_PATH . 'temp/', 0777, true);
}

QRcode::png($qr_data, $qr_path, QR_ECLEVEL_L, 3);

// Extend FPDF Class
class PDF_SuratKeterangan extends FPDF {
    
    function Header() {
        // Kosong
    }
    
    function Footer() {
        // Kosong
    }

    function SetDash($black=null, $white=null) {
        if($black!==null)
            $s=sprintf('[%.3F %.3F] 0 d',$black*$this->k,$white*$this->k);
        else
            $s='[] 0 d';
        $this->_out($s);
    }
    
    function buatSurat($data, $qr_path) {
        // Karena hanya 1 surat, kita mulai dari margin atas standar (10mm)
        $start_y = 10;
        $this->SetY($start_y);
        
        // --- 1. INSERT LOGO ---
        // Path gambar relatif dari file script ini
        $path_logo = dirname(__FILE__) . '/../../assets/images/stk.png';
        
        if (file_exists($path_logo)) {
            // Image(file, x, y, w, [h])
            $this->Image($path_logo, 20, $start_y - 1, 17);
        }

        // KOP SURAT
        $this->SetFont('Arial', '', 8);
        $this->SetX(15);
        $this->Cell(0, 3, SK_KEMENTERIAN, 0, 1, 'C');
        
        $this->SetFont('Arial', '', 8);
        $this->SetX(15);
        $this->Cell(0, 3, SK_YAYASAN, 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 10);
        $this->SetX(15);
        $this->Cell(0, 4, SK_INSTITUSI, 0, 1, 'C');
        
        $this->SetFont('Arial', '', 7);
        $this->SetX(15);
        $this->Cell(0, 3, SK_ALAMAT, 0, 1, 'C');
        
        $this->SetX(15);
        $this->Cell(0, 3, SK_KONTAK, 0, 1, 'C');
        
        // Garis pemisah Kop
        $this->SetY($this->GetY() + 1);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->SetLineWidth(0.2);
        $this->Line(15, $this->GetY() + 0.5, 195, $this->GetY() + 0.5);
        
        $this->Ln(5);
        
        // JUDUL
        $this->SetFont('Arial', 'B', 11);
        $this->SetX(15);
        $this->Cell(0, 5, 'SURAT KETERANGAN BEBAS PERPUSTAKAAN', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 10);
        $this->SetX(15);
        $this->Cell(0, 4, 'No. ' . $data['nomor_surat'], 0, 1, 'C');
        
        $this->Ln(5);
        
        // ISI
        $this->SetFont('Arial', '', 10);
        $this->SetX(15);
        $this->MultiCell(0, 4, 'Yang bertanda tangan di bawah ini menerangkan bahwa:', 0, 'J');
        $this->Ln(2);
        
        // TABEL DATA
        $this->SetFont('Arial', '', 10);
        $col1_width = 40;
        $col2_width = 5;
        $col3_width = 110;
        
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Nama Mahasiswa', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($col3_width, 4, strtoupper($data['nama_mahasiswa']), 0, 1);
        
        $this->SetFont('Arial', '', 10);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'NIM', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($col3_width, 4, $data['nim'], 0, 1);
        
        $this->SetFont('Arial', '', 10);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Angkatan', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($col3_width, 4, $data['angkatan'], 0, 1);
        
        $this->SetFont('Arial', '', 10);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Program Studi', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 10);
        $this->MultiCell($col3_width, 4, $data['nama_prodi'], 0, 'L');
        
        $this->Ln(3);
        
        // KETERANGAN
        $keperluan = $data['jenis_surat'] == 'UAS' 
            ? 'Syarat Pengambilan Kartu Ujian Akhir Semester (UAS)'
            : 'Syarat Pendaftaran Peserta Penilaian Pembelajaran Akhir (PPA)';
        
        $this->SetFont('Arial', '', 10);
        $this->SetX(15);
        $keterangan = "yang bersangkutan telah mengembalikan semua peminjaman buku dan menyelesaikan administrasi menyangkut perpustakaan. Untuk itu mahasiswa bersangkutan dinyatakan Bebas Perpustakaan sebagai {$keperluan}.";
        $this->MultiCell(0, 4, $keterangan, 0, 'J');
        
        $this->Ln(3);
        
        $this->SetX(15);
        $this->MultiCell(0, 4, 'Demikian, surat ini dikeluarkan untuk dapat dipergunakan sebagaimana mestinya.', 0, 'J');
        
        $this->Ln(5);
        
        // --- AREA TTD & QR ---
        $ttd_y = $this->GetY();
        
        // --- 2. POSISI QR CODE (Digeser ke Kanan) ---
        // X=105 agar berada di sebelah kiri blok tanda tangan
        if (file_exists($qr_path)) {
            $this->Image($qr_path, 110, $ttd_y, 25, 25);
        }
        
        // TEXT TANDA TANGAN (Kanan)
        // Mulai X=130
        $this->SetFont('Arial', '', 10);
        $this->SetXY(130, $ttd_y);
        $tanggal = formatTanggalIndonesia($data['tanggal_terbit']);
        $this->Cell(60, 4, 'Merauke, ' . $tanggal, 0, 1, 'C');
        
        $this->SetX(130);
        $this->Cell(60, 4, SK_JABATAN_PERPUS, 0, 1, 'C');
        
        // Simpan posisi Y sebelum spasi ttd
        $y_img_ttd = $this->GetY();
        
        // --- 3. INSERT GAMBAR TANDA TANGAN ---
        $path_ttd = dirname(__FILE__) . '/../../assets/images/ttd_yuli.png';
        
        if (file_exists($path_ttd)) {
            // Sesuaikan posisi (X=145 supaya centered di cell 60, Y sedikit turun)
            $this->Image($path_ttd, 145, $y_img_ttd - 1, 27); 
        }
        
        // Space Text (agar nama turun ke bawah)
        $this->Ln(15);
        
        // NAMA & GELAR
        $this->SetFont('Arial', 'BU', 10);
        $this->SetX(130);
        $this->Cell(60, 4, SK_KEPALA_PERPUS, 0, 1, 'C');
    }
}

// Inisialisasi PDF A4
$pdf = new PDF_SuratKeterangan('P', 'mm', 'A4');
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

// Generate Surat (Hanya 1 kali pemanggilan)
$pdf->buatSurat($surat, $qr_path);

// Nama file
$filename = 'Surat_Keterangan_' . $surat['nim'] . '_' . date('YmdHis') . '.pdf';
$filepath = SK_PDF_PATH . $filename;

// Output file & Update Database
$pdf->Output('F', $filepath);

$sql_update = "UPDATE surat_keterangan SET file_pdf = ? WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("si", $filename, $surat_id);
$stmt_update->execute();

if (file_exists($qr_path)) {
    unlink($qr_path);
}

$pdf->Output('I', $filename);

logAktivitas('CETAK_SURAT', "Mencetak surat keterangan {$surat['nomor_surat']} untuk mahasiswa {$surat['nim']}");
?>