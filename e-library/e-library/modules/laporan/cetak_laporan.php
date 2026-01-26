<?php
/**
 * LAPORAN BULANAN PERPUSTAKAAN LENGKAP
 * Update: Menambahkan Fitur Analisis Lengkap (A-I)
 */

require_once '../../config/database.php';
require('../../modules/surat_keterangan/lib/fpdf/fpdf.php');

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

// FILTER INPUT
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$nama_bulan_ind = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$periode_teks = strtoupper($nama_bulan_ind[$bulan]) . " " . $tahun;

// =========================================================================
// KELAS PDF CUSTOM
// =========================================================================
class PDF extends FPDF {
    function Header() {
        $path_logo = '../../assets/images/stk.png';
        if (file_exists($path_logo)) {
            $this->Image($path_logo, 15, 10, 25);
        }
        
        $this->SetFont('Arial','B',14);
        $this->Cell(30);
        $this->Cell(0,5,'SEKOLAH TINGGI KATOLIK SANTO YAKOBUS MERAUKE',0,1,'C');
        
        $this->SetFont('Arial','B',13);
        $this->Cell(30);
        $this->Cell(0,10,'UNIT PELAKSANA TEKNIS PERPUSTAKAAN',0,1,'C');
        
        $this->SetFont('Arial','I',10);
        $this->Cell(30);
        $this->Cell(0,5,'Jl. Missi 2, Mandala, Merauke, Papua Selatan, 99616',0,1,'C');
        $this->Cell(30);
        $this->Cell(0,5,'Website: www.stkyakobus.ac.id | Email: humas@stkyakobus.ac.id',0,1,'C');
        
        $this->SetLineWidth(1);
        $this->Line(10, 37, 200, 37);
        $this->SetLineWidth(0.2);
        $this->Line(10, 38, 200, 38);
        $this->Ln(8);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Laporan Bulanan Digenerate Otomatis dari https://stkyakobus.ac.id/e-library | Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }

    function SectionTitle($text) {
        $this->Ln(5);
        $this->SetFont('Arial','B',11);
        $this->SetFillColor(240,240,240);
        $this->Cell(0, 8, strtoupper($text), 1, 1, 'L', true);
        $this->Ln(2);
    }

    function CheckPageBreak($h) {
        if($this->GetY()+$h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function TableHeader($headers, $widths) {
        $this->SetFont('Arial','B',8); // Font Header Tabel
        $this->SetFillColor(230,230,230);
        for($i=0; $i<count($headers); $i++) {
            $this->Cell($widths[$i], 7, $headers[$i], 1, 0, 'C', true);
        }
        $this->Ln();
    }
}

$pdf = new PDF('P','mm','A4');
$pdf->SetMargins(15, 10, 15);
$pdf->AliasNbPages();
$pdf->AddPage();

// JUDUL LAPORAN
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0, 8, 'LAPORAN BULANAN PERPUSTAKAAN', 0, 1, 'C');
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0, 8, 'PERIODE: ' . $periode_teks, 0, 1, 'C');
$pdf->Ln(5);


// =========================================================================
// A. RINGKASAN DATA ASET & TRANSAKSI
// =========================================================================
$pdf->SectionTitle('A. RINGKASAN DATA ASET & TRANSAKSI BULAN INI');

// [FIX] Menggunakan COUNT(*) murni tanpa WHERE status='aktif' untuk memastikan angka keluar
$total_judul = $conn->query("SELECT COUNT(*) FROM buku")->fetch_row()[0];
$total_eksemplar = $conn->query("SELECT SUM(stok) FROM buku")->fetch_row()[0]; // Pastikan kolom 'stok' benar
$jml_mhs = $conn->query("SELECT COUNT(*) FROM mahasiswa")->fetch_row()[0];
$jml_dosen = $conn->query("SELECT COUNT(*) FROM dosen")->fetch_row()[0];

// Transaksi Bulan Ini
$pinjam_bln = $conn->query("SELECT COUNT(*) FROM peminjaman WHERE MONTH(tanggal_pinjam) = '$bulan' AND YEAR(tanggal_pinjam) = '$tahun'")->fetch_row()[0];
$kembali_bln = $conn->query("SELECT COUNT(*) FROM pengembalian WHERE MONTH(tanggal_kembali) = '$bulan' AND YEAR(tanggal_kembali) = '$tahun'")->fetch_row()[0];

// [UPDATE] Query Keuangan
$denda_cash = $conn->query("SELECT SUM(denda_dibayar) FROM pengembalian WHERE MONTH(tanggal_kembali) = '$bulan' AND YEAR(tanggal_kembali) = '$tahun' AND metode_pembayaran IN ('cash', 'transfer')")->fetch_row()[0];
$denda_tagihan = $conn->query("SELECT SUM(denda) FROM pengembalian WHERE MONTH(tanggal_kembali) = '$bulan' AND YEAR(tanggal_kembali) = '$tahun' AND metode_pembayaran = 'tagihan_studi'")->fetch_row()[0];
// [BARU] Query Waive
$denda_waive = $conn->query("SELECT SUM(denda) FROM pengembalian WHERE MONTH(tanggal_kembali) = '$bulan' AND YEAR(tanggal_kembali) = '$tahun' AND metode_pembayaran = 'waive'")->fetch_row()[0];

// Display Tabel Ringkasan
$pdf->SetFont('Arial','',10);
$h_cell = 6;
$w_label = 80; $w_val = 40; $w_satuan = 60;

$pdf->SetFont('Arial','B',9);
$pdf->Cell($w_label, $h_cell, 'Keterangan', 1, 0, 'C');
$pdf->Cell($w_val, $h_cell, 'Jumlah', 1, 0, 'C');
$pdf->Cell($w_satuan, $h_cell, 'Satuan', 1, 1, 'C');

$pdf->SetFont('Arial','',9);
// Rows [UPDATE: Menambahkan baris Waive]
$data_aset = [
    ['Total Judul Buku', number_format($total_judul), 'Judul'],
    ['Total Eksemplar Buku', number_format((float)$total_eksemplar), 'Eksemplar'],
    ['Total Mahasiswa Terdaftar', number_format($jml_mhs), 'Orang'],
    ['Total Dosen/Staf Terdaftar', number_format($jml_dosen), 'Orang'],
    ['Peminjaman Baru (Bulan Ini)', number_format($pinjam_bln), 'Transaksi'],
    ['Pengembalian (Bulan Ini)', number_format($kembali_bln), 'Transaksi'],
    ['Pemasukan Denda (Cash)', 'Rp ' . number_format((float)$denda_cash, 0, ',', '.'), 'Rupiah (Kas)'],
    ['Tagihan Studi (Piutang)', 'Rp ' . number_format((float)$denda_tagihan, 0, ',', '.'), 'Rupiah (Akademik)'],
    ['Tagihan Dihapuskan (Waive)', 'Rp ' . number_format((float)$denda_waive, 0, ',', '.'), 'Rupiah (Non-Tunai)'],
];

foreach ($data_aset as $row) {
    $pdf->Cell($w_label, $h_cell, $row[0], 1, 0, 'L');
    $pdf->Cell($w_val, $h_cell, $row[1], 1, 0, 'C');
    $pdf->Cell($w_satuan, $h_cell, $row[2], 1, 1, 'L');
}


// =========================================================================
// B. DAFTAR PEMINJAMAN BERJALAN (AKTIF HINGGA SAAT INI)
// =========================================================================
$pdf->SectionTitle('B. DAFTAR PEMINJAMAN BERJALAN (AKTIF/TERLAMBAT)');
$pdf->SetFont('Arial','I',8);
$pdf->Cell(0, 5, 'Menampilkan semua buku yang belum dikembalikan s.d. saat ini (termasuk dari bulan sebelumnya).', 0, 1);

$header_b = ['No', 'Tgl Pinjam', 'Peminjam', 'Judul Buku', 'Jatuh Tempo', 'Status'];
$width_b = [8, 20, 50, 65, 20, 27];
$pdf->TableHeader($header_b, $width_b);

// Query: Ambil yg statusnya bukan 'dikembalikan' atau 'selesai'
// Sort: Dipinjam (1) -> Diperpanjang (2) -> Terlambat (3)
$q_berjalan = $conn->query("
    SELECT p.*, b.judul,
           m.nama as mhs, d.nama as dsn
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    LEFT JOIN mahasiswa m ON p.peminjam_id=m.id AND p.jenis_peminjam='mahasiswa'
    LEFT JOIN dosen d ON p.peminjam_id=d.id AND p.jenis_peminjam='dosen'
    WHERE p.status IN ('dipinjam', 'diperpanjang', 'terlambat')
    ORDER BY 
        CASE 
            WHEN p.status = 'dipinjam' THEN 1 
            WHEN p.status = 'diperpanjang' THEN 2 
            WHEN p.status = 'terlambat' THEN 3 
        END ASC, 
        p.tanggal_pinjam ASC
");

$pdf->SetFont('Arial','',8);
$no = 1;
if ($q_berjalan && $q_berjalan->num_rows > 0) {
    while ($r = $q_berjalan->fetch_assoc()) {
        $nama = ($r['jenis_peminjam']=='mahasiswa') ? $r['mhs'] : $r['dsn'];
        
        // Status Badge Logic (Color simulation)
        $status_upper = strtoupper($r['status']);
        $pdf->SetTextColor(0);
        if($r['status'] == 'terlambat') $pdf->SetTextColor(192,0,0);
        
        $pdf->CheckPageBreak(6);
        $pdf->Cell($width_b[0], 6, $no++, 1, 0, 'C');
        $pdf->Cell($width_b[1], 6, date('d/m/y', strtotime($r['tanggal_pinjam'])), 1, 0, 'C');
        $pdf->Cell($width_b[2], 6, substr($nama, 0, 28), 1, 0, 'L');
        $pdf->Cell($width_b[3], 6, substr($r['judul'], 0, 40), 1, 0, 'L');
        $pdf->Cell($width_b[4], 6, date('d/m/y', strtotime($r['tanggal_jatuh_tempo'])), 1, 0, 'C');
        $pdf->Cell($width_b[5], 6, $status_upper, 1, 0, 'C');
        $pdf->Ln();
    }
    $pdf->SetTextColor(0);
} else {
    $pdf->Cell(array_sum($width_b), 6, 'Tidak ada peminjaman berjalan saat ini.', 1, 1, 'C');
}


// =========================================================================
// C. DAFTAR PEMINJAMAN PERIODE INI (SEMUA)
// =========================================================================
$pdf->SectionTitle('C. DAFTAR PEMINJAMAN BUKU PERIODE INI');
$pdf->TableHeader($header_b, $width_b); // Pakai header yg sama dgn B

$q_periode = $conn->query("
    SELECT p.*, b.judul, m.nama as mhs, d.nama as dsn
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    LEFT JOIN mahasiswa m ON p.peminjam_id=m.id AND p.jenis_peminjam='mahasiswa'
    LEFT JOIN dosen d ON p.peminjam_id=d.id AND p.jenis_peminjam='dosen'
    WHERE MONTH(p.tanggal_pinjam) = '$bulan' AND YEAR(p.tanggal_pinjam) = '$tahun'
    ORDER BY p.tanggal_pinjam ASC
");

$pdf->SetFont('Arial','',8);
$no = 1;
if ($q_periode && $q_periode->num_rows > 0) {
    while ($r = $q_periode->fetch_assoc()) {
        $nama = ($r['jenis_peminjam']=='mahasiswa') ? $r['mhs'] : $r['dsn'];
        
        $pdf->CheckPageBreak(6);
        $pdf->Cell($width_b[0], 6, $no++, 1, 0, 'C');
        $pdf->Cell($width_b[1], 6, date('d/m/y', strtotime($r['tanggal_pinjam'])), 1, 0, 'C');
        $pdf->Cell($width_b[2], 6, substr($nama, 0, 28), 1, 0, 'L');
        $pdf->Cell($width_b[3], 6, substr($r['judul'], 0, 40), 1, 0, 'L');
        $pdf->Cell($width_b[4], 6, date('d/m/y', strtotime($r['tanggal_jatuh_tempo'])), 1, 0, 'C');
        $pdf->Cell($width_b[5], 6, strtoupper($r['status']), 1, 0, 'C');
        $pdf->Ln();
    }
} else {
    $pdf->Cell(array_sum($width_b), 6, 'Tidak ada transaksi peminjaman baru bulan ini.', 1, 1, 'C');
}


// =========================================================================
// D. LAPORAN DENDA & STATUS BUKU
// =========================================================================
$pdf->SectionTitle('D. LAPORAN DENDA, KETERLAMBATAN & STATUS BUKU');
$header_d = ['No', 'Tgl Kembali', 'Nama', 'Status Buku', 'Tagihan', 'Dibayar', 'Metode'];
$width_d = [8, 20, 50, 30, 25, 25, 32];
$pdf->TableHeader($header_d, $width_d);

$q_denda = $conn->query("
    SELECT pg.*, pg.status_buku, m.nama as mhs, d.nama as dsn, p.jenis_peminjam
    FROM pengembalian pg
    JOIN peminjaman p ON pg.peminjaman_id = p.id
    LEFT JOIN mahasiswa m ON p.peminjam_id=m.id AND p.jenis_peminjam='mahasiswa'
    LEFT JOIN dosen d ON p.peminjam_id=d.id AND p.jenis_peminjam='dosen'
    WHERE pg.denda > 0 
      AND MONTH(pg.tanggal_kembali) = '$bulan' 
      AND YEAR(pg.tanggal_kembali) = '$tahun'
    ORDER BY pg.tanggal_kembali ASC
");

$pdf->SetFont('Arial','',8);
$no = 1;
$total_terbayar = 0; // Ubah nama variabel biar jelas

    if ($q_denda && $q_denda->num_rows > 0) {
    while ($r = $q_denda->fetch_assoc()) {
        $nama = ($r['jenis_peminjam']=='mahasiswa') ? $r['mhs'] : $r['dsn'];
        
        $status_lbl = ($r['status_buku'] == 'hilang') ? "HILANG" : (($r['status_buku'] == 'rusak_parah') ? "RUSAK" : "Terlambat");
        
        // Logika Tampilan & Hitungan
        $tampil_dibayar = $r['denda_dibayar'];
        $metode = ucfirst(str_replace('_',' ',$r['metode_pembayaran']));
        
        if ($r['metode_pembayaran'] == 'tagihan_studi') {
            $tampil_dibayar = $r['denda']; // Tagihan studi dianggap 'terbayar' secara administratif
        } 
        if ($r['metode_pembayaran'] == 'waive') {
            $tampil_dibayar = 0; // Waive tidak dihitung
        }

        // [UPDATE] Penjumlahan Total hanya dari kolom 'Dibayar' yang valid
        $total_terbayar += $tampil_dibayar;

        if ($status_lbl != "Terlambat") $pdf->SetFont('Arial','B',8); else $pdf->SetFont('Arial','',8);

        $pdf->CheckPageBreak(6);
        $pdf->Cell($width_d[0], 6, $no++, 1, 0, 'C');
        $pdf->Cell($width_d[1], 6, date('d/m/y', strtotime($r['tanggal_kembali'])), 1, 0, 'C');
        $pdf->Cell($width_d[2], 6, substr($nama, 0, 28), 1, 0, 'L');
        $pdf->Cell($width_d[3], 6, $status_lbl, 1, 0, 'C');
        $pdf->Cell($width_d[4], 6, number_format($r['denda'],0,',','.'), 1, 0, 'R');
        $pdf->Cell($width_d[5], 6, number_format($tampil_dibayar,0,',','.'), 1, 0, 'R'); // Kolom Dibayar
        $pdf->Cell($width_d[6], 6, $metode, 1, 0, 'L');
        $pdf->Ln();
    }
    
    // [UPDATE] Footer Total: Menampilkan total di bawah kolom "Dibayar" (Index 5)
    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(array_sum(array_slice($width_d,0,5)), 6, 'TOTAL PEMASUKAN (Cash + Tagihan Studi):', 1, 0, 'R');
    $pdf->Cell($width_d[5], 6, number_format($total_terbayar,0,',','.'), 1, 0, 'R');
    $pdf->Cell($width_d[6], 6, '', 1, 0, 'R');
    $pdf->Ln();
} else {
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(array_sum($width_d), 6, 'Tidak ada denda diproses bulan ini.', 1, 1, 'C');
}


// =========================================================================
// E. DAFTAR BUKU STOK HABIS
// =========================================================================
$pdf->SectionTitle('E. DAFTAR BUKU STOK HABIS (SAAT INI)');
$header_e = ['No', 'Nomor Buku', 'Judul Buku', 'Kategori', 'Total Aset'];
$width_e = [10, 30, 90, 40, 20];
$pdf->TableHeader($header_e, $width_e);

$q_stok = $conn->query("SELECT * FROM buku WHERE stok_tersedia <= 0 ORDER BY judul ASC");

$pdf->SetFont('Arial','',8);
$no = 1;
if ($q_stok && $q_stok->num_rows > 0) {
    while ($r = $q_stok->fetch_assoc()) {
        $pdf->CheckPageBreak(6);
        $pdf->Cell($width_e[0], 6, $no++, 1, 0, 'C');
        $pdf->Cell($width_e[1], 6, $r['nomor_buku'], 1, 0, 'L');
        $pdf->Cell($width_e[2], 6, substr($r['judul'], 0, 55), 1, 0, 'L');
        $pdf->Cell($width_e[3], 6, substr($r['kategori'], 0, 22), 1, 0, 'L');
        $pdf->Cell($width_e[4], 6, $r['stok'], 1, 0, 'C');
        $pdf->Ln();
    }
} else {
    $pdf->Cell(array_sum($width_e), 6, 'Semua judul buku memiliki stok tersedia.', 1, 1, 'C');
}


// =========================================================================
// F. DAFTAR BUKU HILANG (PERIODE INI)
// =========================================================================
$pdf->SectionTitle('F. DAFTAR BUKU HILANG (DILAPORKAN PERIODE INI)');
$header_f = ['No', 'Tgl Lapor', 'Judul Buku', 'Nomor Buku', 'Peminjam Terakhir', 'Ket'];
$width_f = [10, 20, 65, 25, 45, 25];
$pdf->TableHeader($header_f, $width_f);

$q_hilang = $conn->query("
    SELECT pg.tanggal_kembali, b.judul, b.nomor_buku, pg.keterangan_kehilangan,
           m.nama as mhs, d.nama as dsn, p.jenis_peminjam
    FROM pengembalian pg
    JOIN peminjaman p ON pg.peminjaman_id = p.id
    JOIN buku b ON p.buku_id = b.id
    LEFT JOIN mahasiswa m ON p.peminjam_id=m.id AND p.jenis_peminjam='mahasiswa'
    LEFT JOIN dosen d ON p.peminjam_id=d.id AND p.jenis_peminjam='dosen'
    WHERE pg.status_buku = 'hilang'
      AND MONTH(pg.tanggal_kembali) = '$bulan' 
      AND YEAR(pg.tanggal_kembali) = '$tahun'
");

$pdf->SetFont('Arial','',8);
$no = 1;
if ($q_hilang && $q_hilang->num_rows > 0) {
    while ($r = $q_hilang->fetch_assoc()) {
        $nama = ($r['jenis_peminjam']=='mahasiswa') ? $r['mhs'] : $r['dsn'];
        $pdf->CheckPageBreak(6);
        $pdf->Cell($width_f[0], 6, $no++, 1, 0, 'C');
        $pdf->Cell($width_f[1], 6, date('d/m/y', strtotime($r['tanggal_kembali'])), 1, 0, 'C');
        $pdf->Cell($width_f[2], 6, substr($r['judul'], 0, 40), 1, 0, 'L');
        $pdf->Cell($width_f[3], 6, $r['nomor_buku'], 1, 0, 'L');
        $pdf->Cell($width_f[4], 6, substr($nama, 0, 25), 1, 0, 'L');
        $pdf->Cell($width_f[5], 6, 'Ganti Rugi', 1, 0, 'C');
        $pdf->Ln();
    }
} else {
    $pdf->Cell(array_sum($width_f), 6, 'Tidak ada buku hilang dilaporkan bulan ini.', 1, 1, 'C');
}


// =========================================================================
// G. TOP 15 PEMINJAM (PERIODE INI)
// =========================================================================
$pdf->SectionTitle('G. TOP 15 PEMINJAM TERAKTIF BULAN INI');
$header_g = ['Rank', 'Nama Peminjam', 'Status', 'Jumlah Peminjaman'];
$width_g = [15, 90, 40, 45];
$pdf->TableHeader($header_g, $width_g);

$q_top_user = $conn->query("
    SELECT p.peminjam_id, p.jenis_peminjam, COUNT(*) as total,
           MAX(CASE WHEN p.jenis_peminjam = 'mahasiswa' THEN m.nama ELSE d.nama END) as nama
    FROM peminjaman p
    LEFT JOIN mahasiswa m ON p.peminjam_id = m.id AND p.jenis_peminjam = 'mahasiswa'
    LEFT JOIN dosen d ON p.peminjam_id = d.id AND p.jenis_peminjam = 'dosen'
    WHERE MONTH(p.tanggal_pinjam) = '$bulan' AND YEAR(p.tanggal_pinjam) = '$tahun'
    GROUP BY p.peminjam_id, p.jenis_peminjam
    ORDER BY total DESC
    LIMIT 15
");

$pdf->SetFont('Arial','',8);
$rank = 1;
if ($q_top_user && $q_top_user->num_rows > 0) {
    while ($r = $q_top_user->fetch_assoc()) {
        $pdf->CheckPageBreak(6);
        $pdf->Cell($width_g[0], 6, $rank++, 1, 0, 'C');
        $pdf->Cell($width_g[1], 6, substr($r['nama'], 0, 50), 1, 0, 'L');
        $pdf->Cell($width_g[2], 6, ucfirst($r['jenis_peminjam']), 1, 0, 'L');
        $pdf->Cell($width_g[3], 6, $r['total'] . ' Kali', 1, 0, 'C');
        $pdf->Ln();
    }
} else {
    $pdf->Cell(array_sum($width_g), 6, 'Belum ada data peminjam.', 1, 1, 'C');
}


// =========================================================================
// H. TOP 15 BUKU DIPINJAM (PERIODE INI)
// =========================================================================
$pdf->SectionTitle('H. TOP 15 BUKU PALING BANYAK DIPINJAM BULAN INI');
// Ganti Header 'Kategori' jadi 'Pengarang'
$header_h = ['Rank', 'Judul Buku', 'Pengarang', 'Dipinjam'];
$width_h = [15, 100, 45, 30];
$pdf->TableHeader($header_h, $width_h);

// FIX QUERY: 
// 1. Ganti b.kategori (Error) menjadi b.pengarang (Valid)
// 2. Gunakan MAX() untuk judul & pengarang agar aman dari error Group By
$q_top_book = $conn->query("
    SELECT b.id, 
           MAX(b.judul) as judul, 
           MAX(b.pengarang) as pengarang, 
           COUNT(p.id) as total
    FROM peminjaman p
    LEFT JOIN buku b ON p.buku_id = b.id
    WHERE MONTH(p.tanggal_pinjam) = '$bulan' 
      AND YEAR(p.tanggal_pinjam) = '$tahun'
    GROUP BY b.id
    ORDER BY total DESC
    LIMIT 15
");

$pdf->SetFont('Arial','',8);
$rank = 1;

// Error Handling Visual di PDF
if (!$q_top_book) {
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(0, 10, 'Error Database: ' . $conn->error, 1, 1, 'L');
    $pdf->SetTextColor(0);
} elseif ($q_top_book->num_rows > 0) {
    while ($r = $q_top_book->fetch_assoc()) {
        $judul = !empty($r['judul']) ? substr($r['judul'], 0, 60) : '[Tanpa Judul]';
        $pengarang = !empty($r['pengarang']) ? substr($r['pengarang'], 0, 25) : '-';

        $pdf->CheckPageBreak(6);
        $pdf->Cell($width_h[0], 6, $rank++, 1, 0, 'C');
        $pdf->Cell($width_h[1], 6, $judul, 1, 0, 'L');
        $pdf->Cell($width_h[2], 6, $pengarang, 1, 0, 'L'); // Tampilkan Pengarang
        $pdf->Cell($width_h[3], 6, $r['total'] . ' Kali', 1, 0, 'C');
        $pdf->Ln();
    }
} else {
    $pdf->Cell(array_sum($width_h), 6, 'Belum ada data peminjaman buku bulan ini.', 1, 1, 'C');
}
$pdf->Ln(10);


// =========================================================================
// I. REKAP SURAT BEBAS PUSTAKA
// =========================================================================
$pdf->SectionTitle('I. REKAP SURAT KETERANGAN BEBAS PUSTAKA');
$header_i = ['No', 'Tanggal', 'No. Surat', 'NIM', 'Nama Mahasiswa', 'Keperluan'];
$width_i = [10, 20, 35, 25, 50, 50];
$pdf->TableHeader($header_i, $width_i);

$q_surat = $conn->query("
    SELECT s.*, m.nama 
    FROM surat_keterangan s
    LEFT JOIN mahasiswa m ON s.nim = m.nim
    WHERE MONTH(s.tanggal_terbit) = '$bulan' AND YEAR(s.tanggal_terbit) = '$tahun'
    ORDER BY s.tanggal_terbit ASC
");

$pdf->SetFont('Arial','',8);
$no=1;
if ($q_surat && $q_surat->num_rows > 0) {
    while ($r = $q_surat->fetch_assoc()) {
        $pdf->CheckPageBreak(6);
        $pdf->Cell($width_i[0], 6, $no++, 1, 0, 'C');
        $pdf->Cell($width_i[1], 6, date('d/m/y', strtotime($r['tanggal_terbit'])), 1, 0, 'C');
        $pdf->Cell($width_i[2], 6, $r['nomor_surat'], 1, 0, 'L');
        $pdf->Cell($width_i[3], 6, $r['nim'], 1, 0, 'C');
        $pdf->Cell($width_i[4], 6, substr($r['nama'], 0, 28), 1, 0, 'L');
        $pdf->Cell($width_i[5], 6, substr($r['jenis_surat'], 0, 28), 1, 0, 'L');
        $pdf->Ln();
    }
} else {
    $pdf->Cell(array_sum($width_i), 6, 'Tidak ada surat keterangan diterbitkan.', 1, 1, 'C');
}
$pdf->Ln(10);


// =========================================================================
// TANDA TANGAN
// =========================================================================
$pdf->CheckPageBreak(40); // Pastikan cukup ruang untuk TTD
$pdf->SetFont('Arial','',11);
$x_ttd = 120; 

$pdf->SetX($x_ttd);
$pdf->Cell(60, 6, 'Merauke, ' . date('d F Y'), 0, 1, 'C');
$pdf->SetX($x_ttd);
$pdf->Cell(60, 6, 'Kepala Perpustakaan,', 0, 1, 'C');

$y_ttd_start = $pdf->GetY();
$path_ttd_img = '../../assets/images/ttd_yuli.png';

if (file_exists($path_ttd_img)) {
    $pdf->Image($path_ttd_img, $x_ttd + 10, $y_ttd_start + 2, 35); 
    $pdf->Ln(30);
} else {
    $pdf->Ln(25);
}

$pdf->SetX($x_ttd);
$pdf->SetFont('Arial','BU',11);
$pdf->Cell(60, -1, 'Yuliana Mangera, S.S.I', 0, 1, 'C');

// OUTPUT
$pdf->Output('I', 'Laporan_Bulanan_'.$bulan.'_'.$tahun.'.pdf');
?>