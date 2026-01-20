<?php
/**
 * AJAX Handler untuk Modul Surat Keterangan
 * VERSION: 1.1 - DISESUAIKAN DENGAN DATABASE REAL
 */

require_once 'config.php';
cekLoginAdmin();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'load_data':
        loadData();
        break;
    
    case 'cek_mahasiswa':
        cekMahasiswaValidasi();
        break;
    
    case 'preview_surat':
        previewSurat();
        break;
    
    case 'terbitkan_surat':
        terbitkanSurat();
        break;
    
    case 'detail_surat':
        detailSurat();
        break;
    
    case 'batalkan_surat':
        batalkanSurat();
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

/**
 * Load data surat keterangan
 * DISESUAIKAN: Join ke mahasiswa dengan struktur database real
 */
function loadData() {
    global $conn;
    
    $nim = $_POST['nim'] ?? '';
    $jenis = $_POST['jenis'] ?? '';
    $tahun = $_POST['tahun'] ?? '';
    $page = $_POST['page'] ?? 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    // Build query
    $where = ["1=1"];
    $params = [];
    $types = '';
    
    if ($nim) {
        $where[] = "sk.nim LIKE ?";
        $params[] = "%{$nim}%";
        $types .= 's';
    }
    
    if ($jenis) {
        $where[] = "sk.jenis_surat = ?";
        $params[] = $jenis;
        $types .= 's';
    }
    
    if ($tahun) {
        $where[] = "sk.tahun_periode = ?";
        $params[] = $tahun;
        $types .= 'i';
    }
    
    $where_clause = implode(' AND ', $where);
    
    // Count total
    $sql_count = "SELECT COUNT(*) as total FROM surat_keterangan sk WHERE {$where_clause}";
    $stmt = $conn->prepare($sql_count);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    
    // Get data - DISESUAIKAN dengan struktur database real
    $sql = "SELECT 
                sk.*, 
                m.nama as nama_mahasiswa, 
                m.program_studi as nama_prodi,
                m.angkatan,
                a.nama as admin_nama
            FROM surat_keterangan sk
            INNER JOIN mahasiswa m ON sk.nim = m.nim
            LEFT JOIN admin a ON sk.admin_id = a.id
            WHERE {$where_clause}
            ORDER BY sk.created_at DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $types .= 'ii';
    $params[] = $limit;
    $params[] = $offset;
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $html = '';
    $no = $offset + 1;
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $badge_jenis = $row['jenis_surat'] == 'UAS' ? 'badge-primary' : 'badge-info';
            $badge_status = $row['status'] == 'terbit' ? 'badge-success' : 'badge-danger';
            $tanggal = formatTanggalIndonesia($row['tanggal_terbit']);
            
            $html .= "<tr>";
            $html .= "<td>{$no}</td>";
            $html .= "<td><strong>{$row['nomor_surat']}</strong></td>";
            $html .= "<td>{$row['nim']}</td>";
            $html .= "<td>{$row['nama_mahasiswa']}</td>";
            $html .= "<td><span class='badge {$badge_jenis}'>{$row['jenis_surat']}</span></td>";
            $html .= "<td>{$tanggal}</td>";
            $html .= "<td><span class='badge {$badge_status}'>{$row['status']}</span></td>";
            $html .= "<td>";
            $html .= "<button class='btn btn-sm btn-info' onclick='lihatDetail({$row['id']})' title='Lihat Detail'><i class='fas fa-eye'></i></button> ";
            $html .= "<button class='btn btn-sm btn-success' onclick='cetakUlang({$row['id']})' title='Cetak Ulang'><i class='fas fa-print'></i></button> ";
            
            if ($row['status'] == 'terbit') {
                $html .= "<button class='btn btn-sm btn-danger' onclick='batalkanSurat({$row['id']})' title='Batalkan'><i class='fas fa-ban'></i></button>";
            }
            
            $html .= "</td>";
            $html .= "</tr>";
            $no++;
        }
    } else {
        $html = "<tr><td colspan='8' class='text-center'>Tidak ada data</td></tr>";
    }
    
    // Pagination
    $total_pages = ceil($total / $limit);
    $pagination = '';
    
    if ($total_pages > 1) {
        $pagination = '<nav><ul class="pagination justify-content-center">';
        
        for ($i = 1; $i <= $total_pages; $i++) {
            $active = $i == $page ? 'active' : '';
            $pagination .= "<li class='page-item {$active}'><a class='page-link' href='#' onclick='loadData({$i}); return false;'>{$i}</a></li>";
        }
        
        $pagination .= '</ul></nav>';
    }
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'pagination' => $pagination,
        'total' => $total
    ]);
}

/**
 * Cek dan validasi mahasiswa
 */
function cekMahasiswaValidasi() {
    $nim = $_POST['nim'] ?? '';
    $jenis_surat = $_POST['jenis_surat'] ?? '';
    
    if (!$nim || !$jenis_surat) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        return;
    }
    
    // Get data mahasiswa
    $mahasiswa = getDataMahasiswa($nim);
    
    if (!$mahasiswa) {
        echo json_encode(['success' => false, 'message' => 'Mahasiswa tidak ditemukan']);
        return;
    }
    
    // Cek peminjaman aktif
    $peminjaman_aktif = cekPeminjamanAktif($nim);
    
    // Cek tunggakan
    $tunggakan = cekTunggakan($nim);
    
    echo json_encode([
        'success' => true,
        'data' => $mahasiswa,
        'peminjaman_aktif' => $peminjaman_aktif,
        'tunggakan' => $tunggakan
    ]);
}

/**
 * Preview surat sebelum diterbitkan
 */
function previewSurat() {
    global $BULAN_INDONESIA;
    
    $nim = $_POST['nim'] ?? '';
    $jenis_surat = $_POST['jenis_surat'] ?? '';
    
    // Generate nomor surat
    $nomor_surat = generateNomorSurat($jenis_surat);
    
    // Get data mahasiswa
    $mahasiswa = getDataMahasiswa($nim);
    
    $tanggal = formatTanggalIndonesia(date('Y-m-d'));
    
    $keperluan = $jenis_surat == 'UAS' 
        ? 'Syarat Pengambilan Kartu Ujian Akhir Semester (UAS)'
        : 'Syarat Pendaftaran Peserta Penilaian Pembelajaran Akhir (PPA)';
    
    $preview_html = "
    <div style='font-family: Arial, sans-serif; font-size: 12px;'>
        <div style='text-align: center; margin-bottom: 15px;'>
            <div style='font-weight: bold; font-size: 11px;'>KEMENTERIAN AGAMA REPUBLIK INDONESIA</div>
            <div style='font-size: 10px;'>YAYASAN PENDIDIKAN DAN PERSEKOLAHAN KATOLIK</div>
            <div style='font-weight: bold;'>SEKOLAH TINGGI KATOLIK SANTO YAKOBUS MERAUKE</div>
            <div style='font-size: 10px; margin-top: 3px;'>Jalan Missi II Merauke Papua Selatan 99616</div>
            <div style='font-size: 10px;'>Telepon / Faks. (0971) 3330264, Email: humas@stkyakobus.ac.id, Website: www.stkyakobus.ac.id</div>
            <hr style='border-top: 3px solid #000; margin: 5px 0;'>
        </div>
        
        <div style='text-align: center; margin: 15px 0;'>
            <div style='font-weight: bold; text-decoration: underline;'>SURAT KETERANGAN BEBAS PERPUSTAKAAN</div>
            <div style='margin-top: 3px;'>No. {$nomor_surat}</div>
        </div>
        
        <div style='text-align: justify; line-height: 1.6;'>
            <p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p>
            
            <table style='width: 100%; margin: 10px 0;'>
                <tr>
                    <td width='35%'>Nama Mahasiswa</td>
                    <td width='5%'>:</td>
                    <td><strong>{$mahasiswa['nama_mahasiswa']}</strong></td>
                </tr>
                <tr>
                    <td>NIM</td>
                    <td>:</td>
                    <td><strong>{$mahasiswa['nim']}</strong></td>
                </tr>
                <tr>
                    <td>Angkatan</td>
                    <td>:</td>
                    <td><strong>{$mahasiswa['angkatan']}</strong></td>
                </tr>
                <tr>
                    <td>Program Studi</td>
                    <td>:</td>
                    <td><strong>{$mahasiswa['nama_prodi']}</strong></td>
                </tr>
            </table>
            
            <p>yang bersangkutan telah mengembalikan semua peminjaman buku dan menyelesaikan administrasi menyangkut perpustakaan. Untuk itu mahasiswa bersangkutan dinyatakan <strong>Bebas Perpustakaan</strong> sebagai <strong>{$keperluan}</strong>.</p>
            
            <p>Demikian, surat ini dikeluarkan untuk dapat dipergunakan sebagaimana mestinya.</p>
            
            <div style='margin-top: 20px; text-align: right;'>
                <div>Merauke, {$tanggal}</div>
                <div>Kepala Perpustakaan</div>
                <div style='margin-top: 50px;'><strong>Yuliana Mangera, S.S.I</strong></div>
            </div>
        </div>
    </div>
    ";
    
    echo json_encode([
        'success' => true,
        'nomor_surat' => $nomor_surat,
        'preview_html' => $preview_html
    ]);
}

/**
 * Terbitkan surat
 */
function terbitkanSurat() {
    global $conn;
    
    $nim = $_POST['nim'] ?? '';
    $jenis_surat = $_POST['jenis_surat'] ?? '';
    $override_tunggakan = isset($_POST['override_tunggakan']) ? 1 : 0;
    $catatan = $_POST['catatan'] ?? '';
    
    if (!$nim || !$jenis_surat) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        return;
    }
    
    // Validasi ulang
    if (!$override_tunggakan) {
        $peminjaman_aktif = cekPeminjamanAktif($nim);
        $tunggakan = cekTunggakan($nim);
        
        if ($peminjaman_aktif) {
            echo json_encode(['success' => false, 'message' => 'Mahasiswa masih memiliki peminjaman aktif']);
            return;
        }
        
        if ($tunggakan['ada_tunggakan']) {
            echo json_encode(['success' => false, 'message' => 'Mahasiswa memiliki tunggakan denda']);
            return;
        }
    }
    
    // Generate nomor surat
    $nomor_surat = generateNomorSurat($jenis_surat);
    $tanggal_terbit = date('Y-m-d');
    $tahun_periode = date('Y');
    $admin_id = $_SESSION['admin_id'];
    
    // Insert ke database
    $sql = "INSERT INTO surat_keterangan 
            (nomor_surat, nim, jenis_surat, tanggal_terbit, tahun_periode, status, catatan, admin_id, override_tunggakan) 
            VALUES (?, ?, ?, ?, ?, 'terbit', ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisii", $nomor_surat, $nim, $jenis_surat, $tanggal_terbit, $tahun_periode, $catatan, $admin_id, $override_tunggakan);
    
    if ($stmt->execute()) {
        $surat_id = $conn->insert_id;
        
        // Log aktivitas
        logAktivitas('TERBIT_SURAT', "Menerbitkan surat keterangan {$nomor_surat} untuk mahasiswa {$nim}");
        
        // URL untuk generate PDF
        $pdf_url = 'generate_pdf.php?id=' . $surat_id;
        
        echo json_encode([
            'success' => true,
            'message' => 'Surat berhasil diterbitkan',
            'surat_id' => $surat_id,
            'pdf_url' => $pdf_url
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menerbitkan surat: ' . $conn->error]);
    }
}

/**
 * Detail surat
 * DISESUAIKAN: Join dengan struktur database real
 */
function detailSurat() {
    global $conn;
    
    $id = $_POST['id'] ?? 0;
    
    $sql = "SELECT 
                sk.*, 
                m.nama as nama_mahasiswa, 
                m.program_studi as nama_prodi,
                m.angkatan, 
                a.nama as admin_nama
            FROM surat_keterangan sk
            INNER JOIN mahasiswa m ON sk.nim = m.nim
            LEFT JOIN admin a ON sk.admin_id = a.id
            WHERE sk.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $tanggal = formatTanggalIndonesia($row['tanggal_terbit']);
        $badge_jenis = $row['jenis_surat'] == 'UAS' ? 'badge-primary' : 'badge-info';
        $badge_status = $row['status'] == 'terbit' ? 'badge-success' : 'badge-danger';
        
        $html = "
        <table class='table table-bordered'>
            <tr>
                <th width='30%'>Nomor Surat</th>
                <td>{$row['nomor_surat']}</td>
            </tr>
            <tr>
                <th>Jenis Surat</th>
                <td><span class='badge {$badge_jenis}'>{$row['jenis_surat']}</span></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><span class='badge {$badge_status}'>{$row['status']}</span></td>
            </tr>
            <tr>
                <th>Tanggal Terbit</th>
                <td>{$tanggal}</td>
            </tr>
            <tr>
                <th>NIM</th>
                <td>{$row['nim']}</td>
            </tr>
            <tr>
                <th>Nama Mahasiswa</th>
                <td>{$row['nama_mahasiswa']}</td>
            </tr>
            <tr>
                <th>Program Studi</th>
                <td>{$row['nama_prodi']}</td>
            </tr>
            <tr>
                <th>Angkatan</th>
                <td>{$row['angkatan']}</td>
            </tr>
            <tr>
                <th>Override Tunggakan</th>
                <td>" . ($row['override_tunggakan'] ? '<span class="badge badge-warning">Ya</span>' : 'Tidak') . "</td>
            </tr>
        ";
        
        if ($row['catatan']) {
            $html .= "
            <tr>
                <th>Catatan</th>
                <td>{$row['catatan']}</td>
            </tr>
            ";
        }
        
        $html .= "
            <tr>
                <th>Diterbitkan Oleh</th>
                <td>{$row['admin_nama']}</td>
            </tr>
            <tr>
                <th>Waktu Terbit</th>
                <td>" . date('d/m/Y H:i:s', strtotime($row['created_at'])) . "</td>
            </tr>
        </table>
        
        <div class='text-right'>
            <button class='btn btn-primary' onclick='cetakUlang({$id})'>
                <i class='fas fa-print'></i> Cetak Surat
            </button>
        </div>
        ";
        
        echo json_encode(['success' => true, 'html' => $html]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Surat tidak ditemukan']);
    }
}

/**
 * Batalkan surat
 */
function batalkanSurat() {
    global $conn;
    
    $id = $_POST['id'] ?? 0;
    
    $sql = "UPDATE surat_keterangan SET status = 'dibatalkan' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        logAktivitas('BATAL_SURAT', "Membatalkan surat keterangan ID: {$id}");
        echo json_encode(['success' => true, 'message' => 'Surat berhasil dibatalkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal membatalkan surat']);
    }
}
?>