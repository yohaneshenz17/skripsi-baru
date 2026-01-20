<?php
// --- 1. PENGATURAN KONEKSI & LIBRARY ---
require_once '../../config/database.php';
// require_once '../../config/functions.php'; 

require('../../modules/surat_keterangan/lib/fpdf/fpdf.php');

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

// Ambil Data dari Form
$bulan = isset($_POST['bulan']) ? $_POST['bulan'] : date('m');
$tahun = isset($_POST['tahun']) ? $_POST['tahun'] : date('Y');

$nama_bulan_ind = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$periode = $nama_bulan_ind[$bulan] . " " . $tahun;

// --- 2. EXTEND CLASS FPDF (KOP SURAT BARU) ---
class PDF extends FPDF {
function Header() {
        // --- 1. LOGO ---
        // Posisi: X=10, Y=8, Lebar=23
        $this->Image('../../assets/images/stk.png', 13, 12, 23);
        
        // --- 2. TEKS KOP SURAT (DIGESER KE KANAN) ---
        
        // TRIK: Set Margin Kiri sementara ke 35mm agar teks kop tidak menabrak logo
        // dan titik tengahnya (Center) bergeser menyesuaikan sisa ruang kanan.
        $this->SetLeftMargin(25); 
        
        // Baris 1: KEMENTERIAN AGAMA
        $this->SetFont('Times','',12); 
        $this->Cell(0,5,'KEMENTERIAN AGAMA REPUBLIK INDONESIA',0,1,'C');
        
        // Baris 2: NAMA KAMPUS
        $this->SetFont('Times','B',13);
        $this->Cell(0,6,'SEKOLAH TINGGI KATOLIK SANTO YAKOBUS MERAUKE',0,1,'C');
        
        // Baris 3: UNIT PERPUSTAKAAN
        $this->SetFont('Times','B',12);
        $this->Cell(0,6,'UNIT PELAKSANA TEKNIS PERPUSTAKAAN',0,1,'C');
        
        // Baris 4: ALAMAT
        $this->SetFont('Times','',10);
        $this->Cell(0,5,'Jalan Missi II Merauke Papua Selatan 99616',0,1,'C');
        
        // Baris 5: KONTAK
        $this->SetFont('Times','',10);
        $this->Cell(0,5,'Telp. 0971-3330264, Email: humas@stkyakobus.ac.id, Web: www.stkyakobus.ac.id',0,1,'C');
        
        // PENTING: Kembalikan Margin Kiri ke 10mm untuk badan laporan (agar tabel full lebar)
        $this->SetLeftMargin(10); 
        
        // --- 3. GARIS PEMBATAS (DINAKKAN KE ATAS) ---
        // Jarak spasi kecil dari teks terakhir
        $this->Ln(2); 
        
        // Koordinat Y diubah dari 42 menjadi 38 (Lebih naik/dekat ke teks)
        $this->SetLineWidth(1);
        $this->Line(10, 38, 200, 38); // Garis Tebal
        
        $this->SetLineWidth(0.5);
        $this->Line(10, 39, 200, 39); // Garis Tipis
        
        // --- 4. JUDUL LAPORAN (DINAIKKAN KE ATAS) ---
        // Jarak spasi ke judul diubah dari 10 menjadi 5 (Lebih dekat ke garis)
        $this->Ln(3); 
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Times','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb} - Digenerate otomatis melalui aplikasi https://stkyakobus.ac.id/e-library/ pada: '.date('d/m/Y H:i'),0,0,'C');
    }

    function SectionTitle($label) {
        $this->Ln(5);
        $this->SetFont('Times','B',11); // Ubah ke Times
        $this->SetFillColor(230,230,230);
        $this->Cell(0,8, $label, 0, 1, 'L', true);
        $this->Ln(2);
    }

    function TableHeader($header, $widths) {
        $this->SetFont('Times','B',9); // Ubah ke Times
        $this->SetFillColor(52, 152, 219); 
        $this->SetTextColor(255);
        for($i=0; $i<count($header); $i++) {
            $this->Cell($widths[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        $this->SetTextColor(0);
        $this->SetFont('Times','',9); // Ubah ke Times
    }
}

// Inisialisasi PDF
$pdf = new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();

// JUDUL UTAMA (Font Times)
$pdf->SetFont('Times','B',16);
$pdf->Cell(0,10,'LAPORAN BULANAN PERPUSTAKAAN',0,1,'C');
$pdf->SetFont('Times','B',12);
$pdf->Cell(0,5,"Periode: $periode",0,1,'C');
$pdf->Ln(5);

// --- BAGIAN A: RINGKASAN DATA ---
$pdf->SectionTitle("A. RINGKASAN DATA & ASET");

$q_buku   = mysqli_query($conn, "SELECT COUNT(*) as c FROM buku");
$t_buku   = ($q_buku) ? mysqli_fetch_assoc($q_buku)['c'] : 0;

$q_stok   = mysqli_query($conn, "SELECT SUM(stok) as c FROM buku");
$t_stok   = ($q_stok) ? mysqli_fetch_assoc($q_stok)['c'] : 0;

$q_pinjam = mysqli_query($conn, "SELECT COUNT(*) as c FROM peminjaman WHERE status IN ('dipinjam','diperpanjang')");
$t_pinjam = ($q_pinjam) ? mysqli_fetch_assoc($q_pinjam)['c'] : 0;

$q_dosen  = mysqli_query($conn, "SELECT COUNT(*) as c FROM dosen");
$t_dosen  = ($q_dosen) ? mysqli_fetch_assoc($q_dosen)['c'] : 0;

$q_mhs    = mysqli_query($conn, "SELECT COUNT(*) as c FROM mahasiswa");
$t_mhs    = ($q_mhs) ? mysqli_fetch_assoc($q_mhs)['c'] : 0;

$pdf->SetFont('Times','',10);
$pdf->Cell(95, 8, "Total Judul Buku: $t_buku Judul", 1);
$pdf->Cell(95, 8, "Total Anggota Dosen: $t_dosen Orang", 1);
$pdf->Ln();
$pdf->Cell(95, 8, "Total Exemplar Buku: $t_stok Buku", 1);
$pdf->Cell(95, 8, "Total Anggota Mahasiswa: $t_mhs Orang", 1);
$pdf->Ln();
$pdf->Cell(190, 8, "Buku Sedang Dipinjam (Realtime): $t_pinjam Buku", 1, 1, 'C');

// --- BAGIAN B: DAFTAR PEMINJAMAN BERJALAN ---
$pdf->SectionTitle("B. DAFTAR PEMINJAMAN BERJALAN (BELUM KEMBALI)");
$q_berjalan = mysqli_query($conn, "
    SELECT p.kode_peminjaman, b.judul, p.tanggal_pinjam, p.tanggal_jatuh_tempo,
    COALESCE(m.nama, d.nama) as nama_peminjam
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    LEFT JOIN mahasiswa m ON (p.peminjam_id = m.id AND p.jenis_peminjam='mahasiswa')
    LEFT JOIN dosen d ON (p.peminjam_id = d.id AND p.jenis_peminjam='dosen')
    WHERE p.status IN ('dipinjam','diperpanjang')
    ORDER BY p.tanggal_pinjam ASC
");

$header = ['No', 'Peminjam', 'Judul Buku', 'Tgl Pinjam', 'Jatuh Tempo'];
$w = [10, 60, 80, 20, 20];
$pdf->TableHeader($header, $w);

$no = 1;
if(mysqli_num_rows($q_berjalan) > 0){
    while($row = mysqli_fetch_assoc($q_berjalan)) {
        $pdf->Cell($w[0], 6, $no++, 1, 0, 'C');
        $pdf->Cell($w[1], 6, substr($row['nama_peminjam'] ?? '-', 0, 30), 1, 0, 'L');
        $pdf->Cell($w[2], 6, substr($row['judul'], 0, 45), 1, 0, 'L');
        $pdf->Cell($w[3], 6, date('d/m/y', strtotime($row['tanggal_pinjam'])), 1, 0, 'C');
        $pdf->Cell($w[4], 6, date('d/m/y', strtotime($row['tanggal_jatuh_tempo'])), 1, 0, 'C');
        $pdf->Ln();
    }
} else {
    $pdf->Cell(190, 6, 'Tidak ada data peminjaman berjalan.', 1, 1, 'C');
}

// --- BAGIAN C: TRANSAKSI BULAN INI ---
$pdf->AddPage();
$pdf->SectionTitle("C. AKTIVITAS TRANSAKSI BULAN INI ($periode)");

// 1. Peminjaman Baru
$pdf->SetFont('Times','B',10);
$pdf->Cell(0,8,'1. Peminjaman Baru',0,1);
$q_pinjam_baru = mysqli_query($conn, "
    SELECT p.kode_peminjaman, b.judul, p.tanggal_pinjam,
    COALESCE(m.nama, d.nama) as nama_peminjam
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    LEFT JOIN mahasiswa m ON (p.peminjam_id = m.id AND p.jenis_peminjam='mahasiswa')
    LEFT JOIN dosen d ON (p.peminjam_id = d.id AND p.jenis_peminjam='dosen')
    WHERE MONTH(p.tanggal_pinjam) = '$bulan' AND YEAR(p.tanggal_pinjam) = '$tahun'
");

$header = ['No', 'Kode', 'Peminjam', 'Judul Buku', 'Tgl Pinjam'];
$w = [10, 30, 60, 70, 20];
$pdf->TableHeader($header, $w);
$no = 1;
if(mysqli_num_rows($q_pinjam_baru) > 0){
    while($row = mysqli_fetch_assoc($q_pinjam_baru)){
        $pdf->Cell($w[0], 6, $no++, 1, 0, 'C');
        $pdf->Cell($w[1], 6, $row['kode_peminjaman'], 1, 0, 'C');
        $pdf->Cell($w[2], 6, substr($row['nama_peminjam'] ?? '-', 0, 30), 1, 0, 'L');
        $pdf->Cell($w[3], 6, substr($row['judul'], 0, 40), 1, 0, 'L');
        $pdf->Cell($w[4], 6, date('d/m/y', strtotime($row['tanggal_pinjam'])), 1, 0, 'C');
        $pdf->Ln();
    }
} else {
    $pdf->Cell(190, 6, 'Tidak ada peminjaman baru bulan ini.', 1, 1, 'C');
}

// 2. Pengembalian
$pdf->Ln(5);
$pdf->SetFont('Times','B',10);
$pdf->Cell(0,8,'2. Pengembalian Buku',0,1);
$q_kembali = mysqli_query($conn, "
    SELECT pg.tanggal_kembali, b.judul, pg.denda,
    COALESCE(m.nama, d.nama) as nama_peminjam
    FROM pengembalian pg
    JOIN peminjaman p ON pg.peminjaman_id = p.id
    JOIN buku b ON p.buku_id = b.id
    LEFT JOIN mahasiswa m ON (p.peminjam_id = m.id AND p.jenis_peminjam='mahasiswa')
    LEFT JOIN dosen d ON (p.peminjam_id = d.id AND p.jenis_peminjam='dosen')
    WHERE MONTH(pg.tanggal_kembali) = '$bulan' AND YEAR(pg.tanggal_kembali) = '$tahun'
");
$header = ['No', 'Tgl Kembali', 'Peminjam', 'Judul Buku', 'Denda (Rp)'];
$w = [10, 25, 60, 65, 30];
$pdf->TableHeader($header, $w);
$no = 1;
$total_denda = 0;
if(mysqli_num_rows($q_kembali) > 0){
    while($row = mysqli_fetch_assoc($q_kembali)){
        $pdf->Cell($w[0], 6, $no++, 1, 0, 'C');
        $pdf->Cell($w[1], 6, date('d/m/y', strtotime($row['tanggal_kembali'])), 1, 0, 'C');
        $pdf->Cell($w[2], 6, substr($row['nama_peminjam'] ?? '-', 0, 30), 1, 0, 'L');
        $pdf->Cell($w[3], 6, substr($row['judul'], 0, 35), 1, 0, 'L');
        $pdf->Cell($w[4], 6, number_format($row['denda'],0,',','.'), 1, 0, 'R');
        $total_denda += $row['denda'];
        $pdf->Ln();
    }
} else {
    $pdf->Cell(190, 6, 'Tidak ada pengembalian bulan ini.', 1, 1, 'C');
}
// Total Denda Row
$pdf->SetFont('Times','B',9);
$pdf->Cell(160, 6, 'Total Denda Bulan Ini', 1, 0, 'R');
$pdf->Cell(30, 6, 'Rp '.number_format($total_denda,0,',','.'), 1, 1, 'R');

// --- BAGIAN D: KETERLAMBATAN & STOK ---
$pdf->AddPage();
$pdf->SectionTitle("D. KETERLAMBATAN & STOK BUKU");

// Keterlambatan
$pdf->SetFont('Times','B',10);
$pdf->Cell(0,8,'1. Daftar Keterlambatan Saat Ini',0,1);
$q_telat = mysqli_query($conn, "
    SELECT p.kode_peminjaman, b.judul, p.tanggal_jatuh_tempo,
    COALESCE(m.nama, d.nama) as nama_peminjam,
    DATEDIFF(CURDATE(), p.tanggal_jatuh_tempo) as lewat_hari
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    LEFT JOIN mahasiswa m ON (p.peminjam_id = m.id AND p.jenis_peminjam='mahasiswa')
    LEFT JOIN dosen d ON (p.peminjam_id = d.id AND p.jenis_peminjam='dosen')
    WHERE (p.status = 'terlambat' OR (p.status IN ('dipinjam') AND CURDATE() > p.tanggal_jatuh_tempo))
");
$header = ['No', 'Peminjam', 'Judul Buku', 'Jatuh Tempo', 'Telat (Hari)'];
$w = [10, 60, 80, 25, 15];
$pdf->TableHeader($header, $w);
$no = 1;
if(mysqli_num_rows($q_telat) > 0){
    while($row = mysqli_fetch_assoc($q_telat)){
        $pdf->Cell($w[0], 6, $no++, 1, 0, 'C');
        $pdf->Cell($w[1], 6, substr($row['nama_peminjam'] ?? '-', 0, 30), 1, 0, 'L');
        $pdf->Cell($w[2], 6, substr($row['judul'], 0, 45), 1, 0, 'L');
        $pdf->Cell($w[3], 6, date('d/m/y', strtotime($row['tanggal_jatuh_tempo'])), 1, 0, 'C');
        $pdf->SetTextColor(192, 57, 43); // Merah
        $pdf->Cell($w[4], 6, $row['lewat_hari'], 1, 0, 'C');
        $pdf->SetTextColor(0);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(190, 6, 'Tidak ada keterlambatan saat ini.', 1, 1, 'C');
}

// Stok Habis
$pdf->Ln(5);
$pdf->SetFont('Times','B',10);
$pdf->Cell(0,8,'2. Daftar Buku Stok Fisik Habis (0)',0,1);
$q_habis = mysqli_query($conn, "SELECT judul, pengarang, stok FROM buku WHERE stok_tersedia = 0");
$header = ['No', 'Judul Buku', 'Pengarang', 'Total Aset'];
$w = [10, 100, 60, 20];
$pdf->TableHeader($header, $w);
$no = 1;
if(mysqli_num_rows($q_habis) > 0){
    while($row = mysqli_fetch_assoc($q_habis)){
        $pdf->Cell($w[0], 6, $no++, 1, 0, 'C');
        $pdf->Cell($w[1], 6, substr($row['judul'], 0, 55), 1, 0, 'L');
        $pdf->Cell($w[2], 6, substr($row['pengarang'], 0, 30), 1, 0, 'L');
        $pdf->Cell($w[3], 6, $row['stok'], 1, 0, 'C');
        $pdf->Ln();
    }
} else {
    $pdf->Cell(190, 6, 'Tidak ada buku dengan stok 0.', 1, 1, 'C');
}

// --- BAGIAN E: STATISTIK TOP 10 ---
$pdf->SectionTitle("E. STATISTIK TERPOPULER BULAN INI");

// Top Buku
$pdf->SetFont('Times','B',10);
$pdf->Cell(0,8,'1. Top 10 Buku Sering Dipinjam',0,1);
$q_top_buku = mysqli_query($conn, "
    SELECT b.judul, COUNT(p.id) as frekuensi
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    WHERE MONTH(p.tanggal_pinjam) = '$bulan' AND YEAR(p.tanggal_pinjam) = '$tahun'
    GROUP BY p.buku_id
    ORDER BY frekuensi DESC LIMIT 10
");
$header = ['Rank', 'Judul Buku', 'Frekuensi Pinjam'];
$w = [15, 140, 35];
$pdf->TableHeader($header, $w);
$rank = 1;
if(mysqli_num_rows($q_top_buku) > 0){
    while($row = mysqli_fetch_assoc($q_top_buku)){
        $pdf->Cell($w[0], 6, $rank++, 1, 0, 'C');
        $pdf->Cell($w[1], 6, substr($row['judul'], 0, 75), 1, 0, 'L');
        $pdf->Cell($w[2], 6, $row['frekuensi'].' x', 1, 0, 'C');
        $pdf->Ln();
    }
} else {
    $pdf->Cell(190, 6, 'Belum ada data statistik buku bulan ini.', 1, 1, 'C');
}

// Top User
$pdf->Ln(5);
$pdf->SetFont('Times','B',10);
$pdf->Cell(0,8,'2. Top 10 Peminjam Paling Aktif',0,1);
$q_top_user = mysqli_query($conn, "
    SELECT
        COALESCE(m.nama, d.nama) as nama_peminjam,
        CASE WHEN p.jenis_peminjam = 'mahasiswa' THEN 'Mahasiswa' ELSE 'Dosen' END as tipe,
        COUNT(p.id) as jumlah_pinjam
    FROM peminjaman p
    LEFT JOIN mahasiswa m ON (p.peminjam_id = m.id AND p.jenis_peminjam='mahasiswa')
    LEFT JOIN dosen d ON (p.peminjam_id = d.id AND p.jenis_peminjam='dosen')
    WHERE MONTH(p.tanggal_pinjam) = '$bulan' AND YEAR(p.tanggal_pinjam) = '$tahun'
    GROUP BY p.peminjam_id, p.jenis_peminjam
    ORDER BY jumlah_pinjam DESC
    LIMIT 10
");
$header = ['Rank', 'Nama Peminjam', 'Status', 'Jumlah Transaksi'];
$w = [15, 100, 40, 35];
$pdf->TableHeader($header, $w);
$rank = 1;
if(mysqli_num_rows($q_top_user) > 0){
    while($row = mysqli_fetch_assoc($q_top_user)){
        $pdf->Cell($w[0], 6, $rank++, 1, 0, 'C');
        $pdf->Cell($w[1], 6, substr($row['nama_peminjam'] ?? '-', 0, 50), 1, 0, 'L');
        $pdf->Cell($w[2], 6, $row['tipe'], 1, 0, 'C');
        $pdf->Cell($w[3], 6, $row['jumlah_pinjam'].' x', 1, 0, 'C');
        $pdf->Ln();
    }
} else {
    $pdf->Cell(190, 6, 'Belum ada data peminjam aktif bulan ini.', 1, 1, 'C');
}

// --- BAGIAN F: SURAT KETERANGAN ---
$pdf->AddPage();
$pdf->SectionTitle("F. REKAP SURAT KETERANGAN BEBAS PUSTAKA");
$q_surat = mysqli_query($conn, "
    SELECT s.nomor_surat, s.nim, m.nama, s.jenis_surat, s.tanggal_terbit
    FROM surat_keterangan s
    JOIN mahasiswa m ON s.nim = m.nim
    WHERE MONTH(s.tanggal_terbit) = '$bulan' AND YEAR(s.tanggal_terbit) = '$tahun'
");
$header = ['No', 'Nomor Surat', 'NIM', 'Nama Mahasiswa', 'Keperluan'];
$w = [10, 50, 30, 70, 30];
$pdf->TableHeader($header, $w);
$no = 1;
if(mysqli_num_rows($q_surat) > 0){
    while($row = mysqli_fetch_assoc($q_surat)){
        $pdf->Cell($w[0], 6, $no++, 1, 0, 'C');
        $pdf->Cell($w[1], 6, $row['nomor_surat'], 1, 0, 'L');
        $pdf->Cell($w[2], 6, $row['nim'], 1, 0, 'C');
        $pdf->Cell($w[3], 6, substr($row['nama'], 0, 35), 1, 0, 'L');
        $pdf->Cell($w[4], 6, $row['jenis_surat'], 1, 0, 'C');
        $pdf->Ln();
    }
} else {
    $pdf->Cell(190, 6, 'Belum ada surat keterangan diterbitkan bulan ini.', 1, 1, 'C');
}

// --- TANDA TANGAN ---
$pdf->Ln(20);
$pdf->SetFont('Times','',11);

// Kolom Tanggal & Jabatan
$pdf->Cell(120); 
$pdf->Cell(70, 6, 'Merauke, '.date('d F Y'), 0, 1, 'C');

$pdf->Cell(120);
$pdf->Cell(70, 6, 'Kepala Perpustakaan,', 0, 1, 'C');

$pdf->Ln(25); // Spasi

// Nama Kepala Perpustakaan (Bold & Underline Style)
$pdf->Cell(120);
$pdf->SetFont('Times','BU',11); 
$pdf->Cell(70, 6, 'Yuliana Mangera, S.S.I', 0, 1, 'C');

// Output PDF
$pdf->Output('I', "Laporan_Perpustakaan_$periode.pdf");
?>