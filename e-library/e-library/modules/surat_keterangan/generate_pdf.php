<?php
/**
 * Generator PDF Surat Keterangan
 * Format: A5 (2 surat dalam 1 lembar A4 untuk hemat kertas)
 */

require_once 'config.php';
require_once SK_LIB_PATH . 'fpdf/fpdf.php';
require_once SK_LIB_PATH . 'phpqrcode/qrlib.php'; // QR Code library

cekLoginAdmin();

// Get surat ID
$surat_id = $_GET['id'] ?? 0;

if (!$surat_id) {
    die('ID Surat tidak valid');
}

// Get data surat
$sql = "SELECT sk.*, m.nama_mahasiswa, m.angkatan, ps.nama_prodi
        FROM surat_keterangan sk
        JOIN mahasiswa m ON sk.nim = m.nim
        LEFT JOIN program_studi ps ON m.id_prodi = ps.id_prodi
        WHERE sk.id = ? AND sk.status = 'terbit'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $surat_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('Surat tidak ditemukan atau sudah dibatalkan');
}

$surat = $result->fetch_assoc();

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

// Generate QR Code image
QRcode::png($qr_data, $qr_path, QR_ECLEVEL_L, 3);

// Extend FPDF Class
class PDF_SuratKeterangan extends FPDF {
    
    function Header() {
        // Tidak ada header di sini karena akan dibuat manual
    }
    
    function Footer() {
        // Tidak ada footer
    }
    
    /**
     * Buat surat keterangan di posisi tertentu (atas atau bawah)
     */
    function buatSurat($data, $qr_path, $posisi = 'atas') {
        $start_y = $posisi == 'atas' ? 10 : 158; // A4 = 297mm, dibagi 2 = 148.5, + margin
        
        $this->SetY($start_y);
        
        // KOP SURAT - Header
        $this->SetFont('Arial', '', 8);
        $this->SetX(15);
        $this->Cell(0, 3, SK_KEMENTERIAN, 0, 1, 'C');
        
        $this->SetFont('Arial', '', 7);
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
        
        // Garis pemisah
        $this->SetY($this->GetY() + 1);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->SetLineWidth(0.2);
        $this->Line(15, $this->GetY() + 0.5, 195, $this->GetY() + 0.5);
        
        $this->Ln(5);
        
        // JUDUL SURAT
        $this->SetFont('Arial', 'B', 11);
        $this->SetX(15);
        $this->Cell(0, 5, 'SURAT KETERANGAN BEBAS PERPUSTAKAAN', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell(0, 4, 'No. ' . $data['nomor_surat'], 0, 1, 'C');
        
        $this->Ln(3);
        
        // ISI SURAT
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->MultiCell(0, 4, 'Yang bertanda tangan di bawah ini menerangkan bahwa:', 0, 'J');
        
        $this->Ln(2);
        
        // DATA MAHASISWA - Format table
        $this->SetFont('Arial', '', 9);
        $col1_width = 40;
        $col2_width = 5;
        $col3_width = 110;
        
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Nama Mahasiswa', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($col3_width, 4, strtoupper($data['nama_mahasiswa']), 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'NIM', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($col3_width, 4, $data['nim'], 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Angkatan', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($col3_width, 4, $data['angkatan'], 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Program Studi', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 9);
        $this->MultiCell($col3_width, 4, $data['nama_prodi'], 0, 'L');
        
        $this->Ln(2);
        
        // KETERANGAN
        $keperluan = $data['jenis_surat'] == 'UAS' 
            ? 'Syarat Pengambilan Kartu Ujian Akhir Semester (UAS)'
            : 'Syarat Pendaftaran Peserta Penilaian Pembelajaran Akhir (PPA)';
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $keterangan = "yang bersangkutan telah mengembalikan semua peminjaman buku dan menyelesaikan administrasi menyangkut perpustakaan. Untuk itu mahasiswa bersangkutan dinyatakan Bebas Perpustakaan sebagai {$keperluan}.";
        $this->MultiCell(0, 4, $keterangan, 0, 'J');
        
        $this->Ln(2);
        
        $this->SetX(15);
        $this->MultiCell(0, 4, 'Demikian, surat ini dikeluarkan untuk dapat dipergunakan sebagaimana mestinya.', 0, 'J');
        
        $this->Ln(3);
        
        // TANDA TANGAN & QR CODE
        $ttd_y = $this->GetY();
        
        // QR Code di kiri
        if (file_exists($qr_path)) {
            $this->Image($qr_path, 20, $ttd_y, 20, 20);
        }
        
        // Tanda tangan di kanan
        $this->SetFont('Arial', '', 9);
        $this->SetXY(130, $ttd_y);
        $tanggal = formatTanggalIndonesia($data['tanggal_terbit']);
        $this->Cell(60, 4, 'Merauke, ' . $tanggal, 0, 1, 'C');
        
        $this->SetX(130);
        $this->Cell(60, 4, SK_JABATAN_PERPUS, 0, 1, 'C');
        
        // Space untuk tanda tangan
        $this->Ln(12);
        
        // Nama dan gelar
        $this->SetFont('Arial', 'BU', 9);
        $this->SetX(130);
        $this->Cell(60, 4, SK_KEPALA_PERPUS, 0, 1, 'C');
        
        // Garis putus-putus untuk pemisah (jika ada 2 surat)
        if ($posisi == 'atas') {
            $this->SetLineWidth(0.1);
            $this->SetDash(2, 2);
            $this->Line(10, 148.5, 200, 148.5);
            $this->SetDash();
        }
    }
}

// Buat PDF
$pdf = new PDF_SuratKeterangan('P', 'mm', 'A4'); // Portrait, millimeter, A4
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

// Surat Pertama (Atas) - untuk mahasiswa
$pdf->buatSurat($surat, $qr_path, 'atas');

// Surat Kedua (Bawah) - untuk arsip perpustakaan
$pdf->buatSurat($surat, $qr_path, 'bawah');

// Nama file PDF
$filename = 'Surat_Keterangan_' . $surat['nim'] . '_' . date('YmdHis') . '.pdf';
$filepath = SK_PDF_PATH . $filename;

// Save PDF
$pdf->Output('F', $filepath);

// Update path di database
$sql_update = "UPDATE surat_keterangan SET file_pdf = ? WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("si", $filename, $surat_id);
$stmt_update->execute();

// Hapus QR code temp
if (file_exists($qr_path)) {
    unlink($qr_path);
}

// Output PDF ke browser
$pdf->Output('I', $filename); // I = Inline display

// Log aktivitas
logAktivitas('CETAK_SURAT', "Mencetak surat keterangan {$surat['nomor_surat']} untuk mahasiswa {$surat['nim']}");
?>
