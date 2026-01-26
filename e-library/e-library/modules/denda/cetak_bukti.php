<?php
/**
 * Generator PDF Bukti Pembayaran Denda
 * Format: A5 (Hemat Kertas) - Fixed Layout
 */

require_once '../../config/database.php';
require_once '../../config/functions.php';
require_once '../surat_keterangan/lib/fpdf/fpdf.php';

requireLogin();

// Fungsi pembersih PDF lama
function bersihkanPDFLama($directory, $batas_hari = 1) {
    if (!is_dir($directory)) return;
    $files = glob($directory . '*.pdf');
    $now = time();
    foreach ($files as $file) {
        if (is_file($file) && ($now - filemtime($file)) > ($batas_hari * 86400)) {
            @unlink($file);
        }
    }
}

// Setup Folder
$pdf_path = '../../storage/bukti_denda/';
if (!file_exists($pdf_path)) mkdir($pdf_path, 0777, true);
bersihkanPDFLama($pdf_path, 1);

// Get ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// =================================================================================
// 1. QUERY DATA LENGKAP (Termasuk Status Buku)
// =================================================================================
$query = "SELECT 
            pg.id, 
            pg.peminjaman_id, 
            pg.tanggal_kembali, 
            pg.keterlambatan_hari, 
            pg.denda, 
            pg.denda_dibayar, 
            pg.uang_kembali, 
            pg.sisa_denda, 
            pg.metode_pembayaran, 
            pg.keterangan, 
            pg.created_at, 
            pg.nomor_bukti, 
            pg.tanggal_lunas,
            pg.status_buku, 
            pg.nominal_denda_buku,
            
            p.kode_peminjaman, 
            b.judul, 
            b.nomor_buku,
            
            m.nama as nama_mhs, m.nim, m.program_studi as prodi_mhs,
            d.nama as nama_dosen, d.nuptk, d.program_studi as prodi_dosen,
            p.jenis_peminjam
          FROM pengembalian pg
          JOIN peminjaman p ON pg.peminjaman_id = p.id
          JOIN buku b ON p.buku_id = b.id
          LEFT JOIN mahasiswa m ON (p.peminjam_id = m.id AND p.jenis_peminjam = 'mahasiswa')
          LEFT JOIN dosen d ON (p.peminjam_id = d.id AND p.jenis_peminjam = 'dosen')
          WHERE pg.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result(
    $id, 
    $pjm_id, 
    $tgl_kembali, 
    $telat, 
    $denda, 
    $dibayar, 
    $kembali, 
    $sisa, 
    $metode, 
    $ket, 
    $created, 
    $no_bukti, 
    $tgl_lunas,
    $status_buku, 
    $nom_denda_buku,
    
    $kode, 
    $judul, 
    $nomor_buku,
    
    $nama_mhs, $nim, $prodi_mhs,
    $nama_dosen, $nuptk, $prodi_dosen,
    $jenis
);

if (!$stmt->fetch()) die("Data pembayaran tidak ditemukan.");
$stmt->close();

// Identitas Peminjam (FULL UPPERCASE)
if ($jenis == 'mahasiswa') {
    $nama_peminjam = strtoupper($nama_mhs);
    $identitas = $nim;
    $prodi = $prodi_mhs;
    $tipe_user = "Mahasiswa";
} else {
    $nama_peminjam = strtoupper($nama_dosen);
    $identitas = $nuptk;
    $prodi = $prodi_dosen;
    $tipe_user = "Dosen";
}

// Generate Nomor & Tanggal
if (empty($no_bukti)) $no_bukti = 'BP/'.date('Y').'/'.str_pad($id, 4, '0', STR_PAD_LEFT);
if (empty($tgl_lunas)) $tgl_lunas = $created;

// =================================================================================
// 2. CLASS PDF (UKURAN A5)
// =================================================================================
class PDF extends FPDF {
    function Header() {
        // Logo Kampus (stk.png)
        $path_logo = dirname(__FILE__) . '/../../assets/images/stk.png';
        if (file_exists($path_logo)) {
            $this->Image($path_logo, 10, 8, 18); 
        }

        // KOP TEKS
        $this->SetFont('Arial','B',11);
        $this->Cell(8); // Indent logo
        $this->Cell(0,5,'PERPUSTAKAAN STK ST. YAKOBUS MERAUKE',0,1,'C');
        
        $this->SetFont('Arial','',8);
        $this->Cell(8);
        $this->Cell(0,4,'Jl. Missi 2, Mandala, Merauke, Papua Selatan',0,1,'C');
        $this->Cell(8);
        $this->Cell(0,4,'Website: stkyakobus.ac.id/e-library',0,1,'C');
        
        // Garis Pembatas
        $this->SetLineWidth(0.4);
        $this->Line(10, 26, 138, 26);
        $this->Ln(8);
        
        // Judul
        $this->SetFont('Arial','B',11);
        $this->Cell(0,5,'BUKTI PEMBAYARAN DENDA',0,1,'C');
        $this->Ln(3);
    }
}

// Init PDF A5 Portrait
$pdf = new PDF('P','mm','A5');
$pdf->SetMargins(10, 10, 10);
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','',9);

// --- INFO TRANSAKSI ---
$pdf->Cell(30, 5, 'No. Bukti', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(50, 5, $no_bukti, 0, 0);

$pdf->Cell(20, 5, 'Tanggal', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(0, 5, date('d/m/Y', strtotime($tgl_lunas)), 0, 1);

$pdf->Cell(30, 5, 'Kode Pinjam', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(0, 5, $kode, 0, 1);
$pdf->Ln(3);

// --- DATA PEMINJAM ---
$pdf->SetFillColor(230, 230, 230);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(0, 6, 'DATA PEMINJAM', 1, 1, 'L', true);

$pdf->SetFont('Arial','',9);
$pdf->Cell(30, 5, 'Nama', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(0, 5, $nama_peminjam, 0, 1);

$pdf->Cell(30, 5, 'NIM/NUPTK', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(0, 5, $identitas . ' (' . $tipe_user . ')', 0, 1);

$pdf->Cell(30, 5, 'Prodi', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(0, 5, $prodi, 0, 1);
$pdf->Ln(3);

// --- DETAIL DENDA ---
$pdf->SetFont('Arial','B',9);
$pdf->Cell(0, 6, 'DETAIL BUKU & JENIS DENDA', 1, 1, 'L', true);

$pdf->SetFont('Arial','',9);
// Judul Buku
$pdf->Cell(30, 5, 'Judul Buku', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->MultiCell(0, 5, $judul, 0, 'L');

// --- LOGIC PENENTUAN LABEL & KETERANGAN ---
// Default (Terlambat)
$label_denda = "KETERLAMBATAN";
$keterangan_denda = "Terlambat: " . $telat . " Hari";

// Cek Jika Buku Hilang/Rusak (Prioritas Tinggi)
if ($status_buku === 'hilang') {
    $label_denda = "GANTI RUGI BUKU HILANG";
    $keterangan_denda = "Status Buku: Hilang (Denda Full)";
} elseif ($status_buku === 'rusak_parah') {
    $label_denda = "GANTI RUGI BUKU RUSAK";
    $keterangan_denda = "Status Buku: Rusak Parah";
}

// Tampilkan Jenis Denda
$pdf->Cell(30, 5, 'Jenis Denda', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(0, 5, $label_denda, 0, 1);

// Tampilkan Keterangan/Hitungan
$pdf->SetFont('Arial','',9);
$pdf->Cell(30, 5, 'Keterangan', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(0, 5, $keterangan_denda, 0, 1);
$pdf->Ln(3);


// --- RINCIAN PEMBAYARAN ---
$pdf->SetFont('Arial','B',9);
$pdf->Cell(0, 6, 'RINCIAN PEMBAYARAN', 1, 1, 'L', true);

$pdf->SetFont('Arial','',9);

// 1. TOTAL TAGIHAN
$pdf->Cell(85, 6, 'TOTAL TAGIHAN', 0, 0);
$pdf->Cell(3, 6, ':', 0, 0);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(0, 6, 'Rp ' . number_format($denda, 0, ',', '.'), 0, 1, 'R');

// 2. LOGIC LABEL PEMBAYARAN
$metode_display = strtoupper(str_replace('_', ' ', $metode));
$nominal_display = $dibayar;
$label_status_lunas = "LUNAS";

// Override Label Jika Tagihan Studi
if ($metode == 'tagihan_studi') {
    $metode_display = "TAGIHAN STUDI (AKADEMIK)";
    $label_status_lunas = "LUNAS (DIALIHKAN KE TAGIHAN AKADEMIK)";
    // Jika via tagihan studi, nominal yang ditampilkan adalah total dendanya
    $nominal_display = $denda; 
}

$pdf->SetFont('Arial','',9);
$pdf->Cell(85, 6, 'JUMLAH DIBAYAR (' . $metode_display . ')', 0, 0);
$pdf->Cell(3, 6, ':', 0, 0);
$pdf->Cell(0, 6, 'Rp ' . number_format($nominal_display, 0, ',', '.'), 0, 1, 'R');

// Garis Total
$pdf->Line(10, $pdf->GetY(), 138, $pdf->GetY());

// 3. STATUS SISA
$pdf->SetFont('Arial','B',10);
if ($sisa > 0 && $metode != 'tagihan_studi') {
    $pdf->SetTextColor(192, 0, 0); // Merah
    $pdf->Cell(85, 8, 'SISA KEKURANGAN', 0, 0);
    $pdf->Cell(3, 8, ':', 0, 0);
    $pdf->Cell(0, 8, 'Rp ' . number_format($sisa, 0, ',', '.'), 0, 1, 'R');
    
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0, 8, '[ BELUM LUNAS ]', 0, 1, 'C');
} else {
    $pdf->SetTextColor(0, 100, 0); // Hijau Tua
    $pdf->Cell(85, 8, 'STATUS', 0, 0);
    $pdf->Cell(3, 8, ':', 0, 0);
    $pdf->Cell(0, 8, $label_status_lunas, 0, 1, 'R');
}
$pdf->SetTextColor(0);
$pdf->Ln(5);

// Catatan Tambahan
if (!empty($ket)) {
    $pdf->SetFont('Arial','I',8);
    $pdf->MultiCell(0, 4, 'Catatan: ' . $ket, 0, 'L');
}

$pdf->Ln(8);

// --- TANDA TANGAN (COMPACT A5) ---
$ttd_y = $pdf->GetY();

// Kiri: Penyetor
$pdf->SetXY(10, $ttd_y);
$pdf->SetFont('Arial','',9);
$pdf->Cell(50, 4, 'Penyetor', 0, 1, 'C');
$pdf->Ln(15);
// NAMA FULL TANPA KURUNG TANPA SINGKATAN
$pdf->Cell(50, 4, $nama_peminjam, 0, 1, 'C'); 

// Kanan: Petugas
$pdf->SetXY(80, $ttd_y);
// Tanggal Indo
$bulan_indo = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
$tgl_parts = explode('-', date('Y-n-d', strtotime($tgl_lunas)));
$tgl_cetak = $tgl_parts[2] . ' ' . $bulan_indo[(int)$tgl_parts[1]] . ' ' . $tgl_parts[0];

$pdf->Cell(50, 4, 'Merauke, ' . $tgl_cetak, 0, 1, 'C');
$pdf->SetX(80);
$pdf->Cell(50, 4, 'Kepala Perpustakaan', 0, 1, 'C');

// TTD Image
$y_img = $pdf->GetY();
$path_ttd = dirname(__FILE__) . '/../../assets/images/ttd_yuli.png';
if (file_exists($path_ttd)) {
    // Resize image TTD agar pas di A5
    $pdf->Image($path_ttd, 93, $y_img, 22); 
}

$pdf->Ln(15);
$pdf->SetX(80);
$pdf->SetFont('Arial', 'BU', 9);
$pdf->Cell(50, 4, 'Yuliana Mangera, S.S.I', 0, 1, 'C');

// Output PDF
$pdf->Output('I', 'Bukti_A5_'.$id.'.pdf');
?>