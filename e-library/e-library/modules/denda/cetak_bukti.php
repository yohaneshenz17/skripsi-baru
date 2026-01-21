<?php
/**
 * Generator PDF Bukti Pembayaran Denda
 * Format: BP-DENDA/XXX/MM/YYYY
 */

require_once '../../config/database.php';
require_once '../../config/functions.php';
require_once '../surat_keterangan/lib/fpdf/fpdf.php';

requireLogin();

// Fungsi pembersih PDF lama (> 1 hari)
function bersihkanPDFLama($directory, $batas_hari = 1) {
    if (!is_dir($directory)) {
        return;
    }
    
    $batas_usia_detik = $batas_hari * 24 * 3600;
    $waktu_sekarang = time();
    $files = glob($directory . '*.pdf');
    
    foreach ($files as $file) {
        if (is_file($file)) {
            if (($waktu_sekarang - filemtime($file)) > $batas_usia_detik) {
                @unlink($file);
            }
        }
    }
}

// Bersihkan PDF lama
$pdf_path = '../../storage/bukti_denda/';
if (!file_exists($pdf_path)) {
    mkdir($pdf_path, 0777, true);
}
bersihkanPDFLama($pdf_path, 1);

// Get pengembalian ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    die('ID tidak valid');
}

// Query data pengembalian - FIXED untuk PHP tanpa mysqlnd
$query = "SELECT pg.*, p.kode_peminjaman, p.jenis_peminjam, p.peminjam_id, p.tanggal_pinjam,
          b.judul, b.nomor_buku, b.pengarang
          FROM pengembalian pg
          JOIN peminjaman p ON pg.peminjaman_id = p.id
          JOIN buku b ON p.buku_id = b.id
          WHERE pg.id = ? AND pg.sisa_denda = 0 AND pg.nomor_bukti IS NOT NULL";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result(
    $pg_id, $pg_peminjaman_id, $pg_tanggal_kembali, $pg_keterlambatan_hari,
    $pg_denda, $pg_denda_dibayar, $pg_uang_kembali, $pg_sisa_denda,
    $pg_metode_pembayaran, $pg_keterangan, $pg_created_at, $pg_nomor_bukti, $pg_tanggal_lunas,
    $kode_peminjaman, $jenis_peminjam, $peminjam_id, $tanggal_pinjam,
    $judul, $nomor_buku, $pengarang
);

if (!$stmt->fetch()) {
    $stmt->close();
    die('Data tidak ditemukan atau denda belum lunas!');
}

$data = [
    'id' => $pg_id,
    'peminjaman_id' => $pg_peminjaman_id,
    'tanggal_kembali' => $pg_tanggal_kembali,
    'keterlambatan_hari' => $pg_keterlambatan_hari,
    'denda' => $pg_denda,
    'denda_dibayar' => $pg_denda_dibayar,
    'uang_kembali' => $pg_uang_kembali,
    'sisa_denda' => $pg_sisa_denda,
    'metode_pembayaran' => $pg_metode_pembayaran,
    'keterangan' => $pg_keterangan,
    'nomor_bukti' => $pg_nomor_bukti,
    'tanggal_lunas' => $pg_tanggal_lunas,
    'kode_peminjaman' => $kode_peminjaman,
    'jenis_peminjam' => $jenis_peminjam,
    'peminjam_id' => $peminjam_id,
    'tanggal_pinjam' => $tanggal_pinjam,
    'judul' => $judul,
    'nomor_buku' => $nomor_buku,
    'pengarang' => $pengarang
];
$stmt->close();

// Get data peminjam
$nama_peminjam = getNamaPeminjam($conn, $data['jenis_peminjam'], $data['peminjam_id']);
$identifier = getIdentifierPeminjam($conn, $data['jenis_peminjam'], $data['peminjam_id']);

// Get detail info peminjam
if ($data['jenis_peminjam'] == 'mahasiswa') {
    $query = "SELECT nim, angkatan, program_studi FROM mahasiswa WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $data['peminjam_id']);
    $stmt->execute();
    $stmt->bind_result($nim, $angkatan, $prodi);
    $stmt->fetch();
    $stmt->close();
    
    $peminjam_detail = [
        'label' => 'NIM',
        'identifier' => $nim,
        'angkatan' => $angkatan,
        'unit' => $prodi
    ];
} else {
    $query = "SELECT nip, jabatan FROM dosen WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $data['peminjam_id']);
    $stmt->execute();
    $stmt->bind_result($nip, $jabatan);
    $stmt->fetch();
    $stmt->close();
    
    $peminjam_detail = [
        'label' => 'NIP',
        'identifier' => $nip,
        'angkatan' => '-',
        'unit' => $jabatan
    ];
}

// Get riwayat pembayaran - FIXED
$query_detail = "SELECT tanggal_bayar, nominal, metode_pembayaran, keterangan 
                 FROM pembayaran_denda_detail WHERE pengembalian_id = ? ORDER BY created_at ASC";
$stmt_detail = $conn->prepare($query_detail);
$stmt_detail->bind_param("i", $id);
$stmt_detail->execute();
$stmt_detail->bind_result($det_tanggal, $det_nominal, $det_metode, $det_keterangan);

$riwayat_bayar = [];
while ($stmt_detail->fetch()) {
    $riwayat_bayar[] = [
        'tanggal_bayar' => $det_tanggal,
        'nominal' => $det_nominal,
        'metode_pembayaran' => $det_metode,
        'keterangan' => $det_keterangan
    ];
}
$stmt_detail->close();

// Extend FPDF
class PDF_BuktiDenda extends FPDF {
    
    function Header() {
        // Kosong
    }
    
    function Footer() {
        // Kosong
    }
    
    function buatBukti($data, $peminjam_detail, $nama_peminjam, $riwayat_bayar) {
        $start_y = 10;
        $this->SetY($start_y);
        
        // --- LOGO ---
        $path_logo = dirname(__FILE__) . '/../../assets/images/stk.png';
        if (file_exists($path_logo)) {
            $this->Image($path_logo, 20, $start_y - 1, 17);
        }
        
        // KOP SURAT
        $this->SetFont('Arial', '', 8);
        $this->SetX(15);
        $this->Cell(0, 3, 'KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 8);
        $this->SetX(15);
        $this->Cell(0, 3, 'YAYASAN PENDIDIKAN KATOLIK SANTO YAKOBUS MERAUKE', 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 10);
        $this->SetX(15);
        $this->Cell(0, 4, 'SEKOLAH TINGGI KEGURUAN SANTO YAKOBUS MERAUKE', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 7);
        $this->SetX(15);
        $this->Cell(0, 3, 'Jl. Dr. Soetomo Rimba Jaya Merauke 99615, Telp. (0971) 321512', 0, 1, 'C');
        
        $this->SetX(15);
        $this->Cell(0, 3, 'Email: stkyakobus@gmail.com, Website: www.stkyakobus.ac.id', 0, 1, 'C');
        
        // Garis pemisah
        $this->SetY($this->GetY() + 1);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->SetLineWidth(0.2);
        $this->Line(15, $this->GetY() + 0.5, 195, $this->GetY() + 0.5);
        
        $this->Ln(5);
        
        // JUDUL
        $this->SetFont('Arial', 'B', 12);
        $this->SetX(15);
        $this->Cell(0, 5, 'BUKTI PEMBAYARAN DENDA PERPUSTAKAAN', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 10);
        $this->SetX(15);
        $this->Cell(0, 4, 'No. ' . $data['nomor_bukti'], 0, 1, 'C');
        
        $this->Ln(5);
        
        // KOLOM KIRI & KANAN
        $col1_width = 35;
        $col2_width = 3;
        $col3_width = 60;
        
        // --- DATA PEMINJAM ---
        $this->SetFont('Arial', 'B', 10);
        $this->SetX(15);
        $this->Cell(0, 5, 'DATA PEMINJAM', 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Nama', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($col3_width, 4, strtoupper($nama_peminjam), 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell($col1_width, 4, $peminjam_detail['label'], 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($col3_width, 4, $peminjam_detail['identifier'], 0, 1);
        
        if ($peminjam_detail['angkatan'] != '-') {
            $this->SetFont('Arial', '', 9);
            $this->SetX(15);
            $this->Cell($col1_width, 4, 'Angkatan', 0, 0);
            $this->Cell($col2_width, 4, ':', 0, 0);
            $this->SetFont('Arial', '', 9);
            $this->Cell($col3_width, 4, $peminjam_detail['angkatan'], 0, 1);
        }
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell($col1_width, 4, $peminjam_detail['angkatan'] != '-' ? 'Program Studi' : 'Jabatan', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->MultiCell($col3_width, 4, $peminjam_detail['unit'], 0, 'L');
        
        $this->Ln(3);
        
        // --- DATA PEMINJAMAN ---
        $this->SetFont('Arial', 'B', 10);
        $this->SetX(15);
        $this->Cell(0, 5, 'DATA PEMINJAMAN', 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Kode Peminjaman', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($col3_width, 4, $data['kode_peminjaman'], 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Judul Buku', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->MultiCell($col3_width, 4, $data['judul'], 0, 'L');
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Pengarang', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->Cell($col3_width, 4, $data['pengarang'], 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Nomor Buku', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->Cell($col3_width, 4, $data['nomor_buku'], 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Tanggal Pinjam', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->Cell($col3_width, 4, date('d/m/Y', strtotime($data['tanggal_pinjam'])), 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Tanggal Kembali', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->Cell($col3_width, 4, date('d/m/Y', strtotime($data['tanggal_kembali'])), 0, 1);
        
        $this->Ln(3);
        
        // --- DETAIL DENDA ---
        $this->SetFont('Arial', 'B', 10);
        $this->SetX(15);
        $this->Cell(0, 5, 'DETAIL DENDA', 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Keterlambatan', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($col3_width, 4, $data['keterlambatan_hari'] . ' hari @ Rp 1.000', 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetX(15);
        $this->Cell($col1_width, 4, 'Total Denda', 0, 0);
        $this->Cell($col2_width, 4, ':', 0, 0);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($col3_width, 4, 'Rp ' . number_format($data['denda'], 0, ',', '.'), 0, 1);
        
        $this->Ln(3);
        
        // --- RIWAYAT PEMBAYARAN ---
        if (count($riwayat_bayar) > 0) {
            $this->SetFont('Arial', 'B', 10);
            $this->SetX(15);
            $this->Cell(0, 5, 'RIWAYAT PEMBAYARAN', 0, 1);
            
            // Header tabel
            $this->SetFont('Arial', 'B', 8);
            $this->SetFillColor(230, 230, 230);
            $this->SetX(15);
            $this->Cell(10, 6, 'No', 1, 0, 'C', true);
            $this->Cell(30, 6, 'Tanggal', 1, 0, 'C', true);
            $this->Cell(35, 6, 'Nominal', 1, 0, 'C', true);
            $this->Cell(30, 6, 'Metode', 1, 0, 'C', true);
            $this->Cell(75, 6, 'Keterangan', 1, 1, 'C', true);
            
            // Isi tabel
            $this->SetFont('Arial', '', 8);
            $no = 1;
            foreach ($riwayat_bayar as $row) {
                $this->SetX(15);
                $this->Cell(10, 5, $no++, 1, 0, 'C');
                $this->Cell(30, 5, date('d/m/Y', strtotime($row['tanggal_bayar'])), 1, 0, 'C');
                $this->Cell(35, 5, 'Rp ' . number_format($row['nominal'], 0, ',', '.'), 1, 0, 'R');
                
                $metode_label = [
                    'cash' => 'Cash',
                    'transfer' => 'Transfer',
                    'tagihan_studi' => 'Tagihan Studi',
                    'waive' => 'Waive'
                ];
                $this->Cell(30, 5, $metode_label[$row['metode_pembayaran']] ?? $row['metode_pembayaran'], 1, 0, 'C');
                $this->Cell(75, 5, $row['keterangan'], 1, 1, 'L');
            }
            
            $this->Ln(2);
        }
        
        // --- TOTAL PEMBAYARAN ---
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(220, 240, 220);
        $this->SetX(15);
        $this->Cell(105, 6, 'TOTAL PEMBAYARAN (LUNAS)', 1, 0, 'C', true);
        $this->Cell(75, 6, 'Rp ' . number_format($data['denda_dibayar'], 0, ',', '.'), 1, 1, 'C', true);
        
        // Uang kembali (jika ada)
        if ($data['uang_kembali'] > 0) {
            $this->SetFont('Arial', '', 9);
            $this->SetX(15);
            $this->Cell(105, 5, 'Uang Kembali', 1, 0, 'C');
            $this->Cell(75, 5, 'Rp ' . number_format($data['uang_kembali'], 0, ',', '.'), 1, 1, 'C');
        }
        
        $this->Ln(3);
        
        // --- KETERANGAN KHUSUS ---
        if ($data['metode_pembayaran'] == 'tagihan_studi') {
            $this->SetFont('Arial', 'I', 9);
            $this->SetX(15);
            $this->Cell(0, 4, '* Denda dialihkan ke tagihan studi berjalan', 0, 1);
        } elseif ($data['metode_pembayaran'] == 'waive') {
            $this->SetFont('Arial', 'I', 9);
            $this->SetX(15);
            $this->MultiCell(0, 4, '* Denda dibebaskan dengan alasan: ' . $data['keterangan'], 0, 'L');
        }
        
        $this->Ln(5);
        
        // --- TANDA TANGAN ---
        $ttd_y = $this->GetY();
        
        $this->SetFont('Arial', '', 10);
        $this->SetXY(130, $ttd_y);
        $tanggal = date('d F Y', strtotime($data['tanggal_lunas']));
        $bulan_id = ['January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April',
                     'May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus',
                     'September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'];
        foreach ($bulan_id as $en => $id) {
            $tanggal = str_replace($en, $id, $tanggal);
        }
        $this->Cell(60, 4, 'Merauke, ' . $tanggal, 0, 1, 'C');
        
        $this->SetX(130);
        $this->Cell(60, 4, 'Kepala Perpustakaan', 0, 1, 'C');
        
        $y_img_ttd = $this->GetY();
        
        // --- TTD IMAGE ---
        $path_ttd = dirname(__FILE__) . '/../../assets/images/ttd_yuli.png';
        if (file_exists($path_ttd)) {
            $this->Image($path_ttd, 145, $y_img_ttd - 1, 27);
        }
        
        $this->Ln(15);
        
        $this->SetFont('Arial', 'BU', 10);
        $this->SetX(130);
        $this->Cell(60, 4, 'Yuliana Mangera, S.S.I', 0, 1, 'C');
    }
}

// Generate PDF
$pdf = new PDF_BuktiDenda('P', 'mm', 'A4');
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

$pdf->buatBukti($data, $peminjam_detail, $nama_peminjam, $riwayat_bayar);

// Output
$filename = 'Bukti_Denda_' . $data['nomor_bukti'] . '.pdf';
$pdf->Output('I', str_replace('/', '_', $filename));
?>