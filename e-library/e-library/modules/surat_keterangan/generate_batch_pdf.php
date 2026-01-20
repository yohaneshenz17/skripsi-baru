<?php
/**
 * Generator PDF Surat Keterangan - BATCH MODE
 * Fitur: Mencetak banyak surat sekaligus, 2 surat per halaman A4 (Hemat Kertas)
 */

require_once 'config.php';
require_once SK_LIB_PATH . 'fpdf/fpdf.php';
require_once SK_LIB_PATH . 'phpqrcode/qrlib.php';

cekLoginAdmin();

// Ambil ID dari parameter URL (format: 1,2,3,4)
$ids_raw = $_GET['ids'] ?? '';
if (empty($ids_raw)) {
    die('Tidak ada surat yang dipilih');
}

// Sanitasi ID agar aman dari SQL Injection
$ids_arr = explode(',', $ids_raw);
$ids_clean = array_map('intval', $ids_arr);
$ids_string = implode(',', $ids_clean);

if (empty($ids_string)) {
    die('ID tidak valid');
}

// Query Ambil Semua Data Terpilih
// ORDER BY FIELD penting agar urutan cetak sesuai urutan klik (opsional, tapi bagus)
$sql = "SELECT sk.id, sk.nomor_surat, sk.nim, sk.tanggal_terbit, sk.jenis_surat, 
               m.nama AS nama_mahasiswa, 
               m.angkatan, 
               m.program_studi AS nama_prodi
        FROM surat_keterangan sk
        JOIN mahasiswa m ON sk.nim = m.nim
        WHERE sk.id IN ($ids_string) AND sk.status = 'terbit'
        ORDER BY FIELD(sk.id, $ids_string)"; 

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die('Data tidak ditemukan');
}

// Extend FPDF
class PDF_Batch extends FPDF {
    function SetDash($black=null, $white=null) {
        if($black!==null)
            $s=sprintf('[%.3F %.3F] 0 d',$black*$this->k,$white*$this->k);
        else
            $s='[] 0 d';
        $this->_out($s);
    }

    // Fungsi menggambar garis potong di tengah
    function drawCutLine() {
        $this->SetLineWidth(0.2);
        $this->SetDash(2, 2); // Garis putus-putus
        // Garis horizontal tepat di tengah A4 (297mm / 2 = 148.5mm)
        $this->Line(5, 148.5, 205, 148.5); 
        $this->SetDash(); // Reset ke garis solid
        
        // Ikon Gunting (Teks penanda)
        $this->SetFont('Arial', 'I', 8);
        $this->SetXY(180, 145);
        $this->Cell(20, 4, '- Potong di sini -', 0, 0, 'R');
    }

    function buatSurat($data, $posisi) {
        // Tentukan Koordinat Y Awal
        // Atas mulai di 10mm, Bawah mulai di 158.5mm (148.5 + margin 10)
        $start_y = ($posisi == 'atas') ? 10 : 158.5; 
        
        $this->SetY($start_y);

        // --- 1. LOGO STK ---
        // Sesuaikan path ini jika folder gambar Anda berbeda
        $path_logo = dirname(__FILE__) . '/../../assets/images/stk.png';
        if (file_exists($path_logo)) {
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
        $current_y = $this->GetY() + 1;
        $this->SetLineWidth(0.5);
        $this->Line(15, $current_y, 195, $current_y);
        $this->SetLineWidth(0.2);
        $this->Line(15, $current_y + 0.5, 195, $current_y + 0.5);
        
        $this->SetY($current_y + 5);
        
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
        $col1 = 40; $col2 = 5; $col3 = 110;
        $this->SetX(15);
        $this->Cell($col1, 4, 'Nama Mahasiswa', 0, 0); $this->Cell($col2, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 10); $this->Cell($col3, 4, strtoupper($data['nama_mahasiswa']), 0, 1);
        
        $this->SetFont('Arial', '', 10);
        $this->SetX(15);
        $this->Cell($col1, 4, 'NIM', 0, 0); $this->Cell($col2, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 10); $this->Cell($col3, 4, $data['nim'], 0, 1);
        
        $this->SetFont('Arial', '', 10);
        $this->SetX(15);
        $this->Cell($col1, 4, 'Angkatan', 0, 0); $this->Cell($col2, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 10); $this->Cell($col3, 4, $data['angkatan'], 0, 1);
        
        $this->SetFont('Arial', '', 10);
        $this->SetX(15);
        $this->Cell($col1, 4, 'Program Studi', 0, 0); $this->Cell($col2, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 10); $this->MultiCell($col3, 4, $data['nama_prodi'], 0, 'L');
        
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
        
        // GENERATE QR ON THE FLY (Langsung hapus setelah dipakai)
        $qr_content = generateQRData($data['nim'], $data['nama_mahasiswa'], $data['nomor_surat'], $data['tanggal_terbit']);
        // Gunakan nama file unik agar tidak bentrok jika banyak admin akses bersamaan
        $qr_temp = SK_PDF_PATH . 'temp/qr_batch_' . $data['id'] . '_' . uniqid() . '.png';
        
        if (!file_exists(dirname($qr_temp))) mkdir(dirname($qr_temp), 0777, true);
        QRcode::png($qr_content, $qr_temp, QR_ECLEVEL_L, 3);
        
        // Insert QR (Geser ke Kanan di 110mm)
        $this->Image($qr_temp, 110, $ttd_y, 25, 25);
        
        // Hapus file temp QR
        if(file_exists($qr_temp)) unlink($qr_temp); 
        
        // TTD Text
        $this->SetFont('Arial', '', 10);
        $this->SetXY(130, $ttd_y);
        $tanggal = formatTanggalIndonesia($data['tanggal_terbit']);
        $this->Cell(60, 4, 'Merauke, ' . $tanggal, 0, 1, 'C');
        $this->SetX(130);
        $this->Cell(60, 4, SK_JABATAN_PERPUS, 0, 1, 'C');
        
        $y_img_ttd = $this->GetY();
        // Insert Gambar TTD (Sesuaikan path)
        $path_ttd = dirname(__FILE__) . '/../../assets/images/ttd_yuli.png';
        if (file_exists($path_ttd)) {
            $this->Image($path_ttd, 145, $y_img_ttd - 1, 27); 
        }
        
        $this->Ln(15);
        $this->SetFont('Arial', 'BU', 10);
        $this->SetX(130);
        $this->Cell(60, 4, SK_KEPALA_PERPUS, 0, 1, 'C');
    }
}

// Init PDF
$pdf = new PDF_Batch('P', 'mm', 'A4');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(false); // Penting agar tidak auto break page sembarangan

$counter = 0;

// Loop Data dari Database
while ($row = $result->fetch_assoc()) {
    // Logika:
    // Jika $counter genap (0, 2, 4...) -> Halaman Baru -> Cetak di ATAS
    // Jika $counter ganjil (1, 3, 5...) -> Halaman Sama -> Cetak di BAWAH
    
    if ($counter % 2 == 0) {
        $pdf->AddPage();
        $pdf->buatSurat($row, 'atas');
        $pdf->drawCutLine(); // Gambar garis potong di tengah
    } else {
        $pdf->buatSurat($row, 'bawah');
    }
    
    $counter++;
}

// Output PDF (Tampilkan di browser, jangan disimpan di server agar hemat storage)
$filename = 'Batch_Surat_' . date('dmY_His') . '.pdf';
$pdf->Output('I', $filename);
?>