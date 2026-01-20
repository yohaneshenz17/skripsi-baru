<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// PERBAIKAN: Gunakan config.php modul, bukan langsung database.php
// config.php ini sudah include database.php DAN mendefinisikan SK_PDF_PATH
require_once 'config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'search_mahasiswa':
        searchMahasiswa();
        break;
    case 'load_data':
        loadData();
        break;
    case 'cek_mahasiswa':
        cekMahasiswa();
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
    case 'hapus_data': 
        hapusData();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function searchMahasiswa() {
    global $conn;
    
    $term = $_POST['term'] ?? '';
    
    if (strlen($term) < 3) {
        echo json_encode([]);
        return;
    }
    
    $search = "%{$term}%";
    
    $query = "SELECT nim, nama, program_studi, angkatan 
              FROM mahasiswa 
              WHERE nim LIKE ? OR nama LIKE ?
              ORDER BY nama ASC
              LIMIT 20";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    
    $stmt->bind_result($nim, $nama, $program_studi, $angkatan);
    
    $data = [];
    while ($stmt->fetch()) {
        $data[] = [
            'label' => $nama . ' (' . $nim . ') - ' . $program_studi,
            'value' => $nama,
            'nim' => $nim,
            'nama' => $nama,
            'program_studi' => $program_studi,
            'angkatan' => $angkatan
        ];
    }
    
    $stmt->close();
    echo json_encode($data);
}

function loadData() {
    global $conn;
    
    $nim = $_POST['nim'] ?? '';
    $jenis = $_POST['jenis'] ?? '';
    $tahun = $_POST['tahun'] ?? '';
    
    $page = intval($_POST['page'] ?? 1);
    $limit = 30;
    $offset = ($page - 1) * $limit;
    
    $sort_by = $_POST['sort_by'] ?? 'created_at';
    $order = strtoupper($_POST['order'] ?? 'DESC');
    
    $allowed_columns = [
        'nomor_surat' => 'sk.nomor_surat',
        'nim'         => 'sk.nim',
        'nama'        => 'm.nama',
        'jenis'       => 'sk.jenis_surat',
        'tanggal'     => 'sk.tanggal_terbit',
        'status'      => 'sk.status',
        'created_at'  => 'sk.created_at'
    ];
    
    $order_clause = "sk.created_at DESC"; 
    
    if (array_key_exists($sort_by, $allowed_columns)) {
        $order = ($order === 'ASC') ? 'ASC' : 'DESC';
        $order_clause = $allowed_columns[$sort_by] . " " . $order;
    }
    
    $where = ["1=1"];
    
    if ($nim) {
        $where[] = "sk.nim LIKE '%" . $conn->real_escape_string($nim) . "%'";
    }
    if ($jenis) {
        $where[] = "sk.jenis_surat = '" . $conn->real_escape_string($jenis) . "'";
    }
    if ($tahun) {
        $where[] = "sk.tahun_periode = " . intval($tahun);
    }
    
    $where_clause = implode(' AND ', $where);
    
    $sql_count = "SELECT COUNT(*) as total FROM surat_keterangan sk WHERE {$where_clause}";
    $result = $conn->query($sql_count);
    $total = $result->fetch_assoc()['total'];
    
    $sql = "SELECT sk.*, m.nama, m.program_studi
            FROM surat_keterangan sk
            INNER JOIN mahasiswa m ON sk.nim = m.nim
            WHERE {$where_clause}
            ORDER BY {$order_clause}
            LIMIT {$limit} OFFSET {$offset}";
    
    $result = $conn->query($sql);
    
    $html = '';
    $no = $offset + 1;
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $badge_jenis = $row['jenis_surat'] == 'UAS' ? 'bg-primary' : 'bg-info';
            $badge_status = $row['status'] == 'terbit' ? 'bg-success' : 'bg-danger';
            
            $html .= "<tr>";
            
            if ($row['status'] == 'terbit') {
                $html .= "<td><input type='checkbox' class='form-check-input chk-surat' value='{$row['id']}'></td>";
            } else {
                $html .= "<td></td>";
            }

            $html .= "<td>{$no}</td>";
            $html .= "<td><strong>{$row['nomor_surat']}</strong></td>";
            $html .= "<td>{$row['nim']}</td>";
            $html .= "<td>{$row['nama']}</td>";
            $html .= "<td><span class='badge {$badge_jenis}'>{$row['jenis_surat']}</span></td>";
            $html .= "<td>" . date('d/m/Y', strtotime($row['tanggal_terbit'])) . "</td>";
            $html .= "<td><span class='badge {$badge_status}'>{$row['status']}</span></td>";
            $html .= "<td>";
            $html .= "<div class='btn-group'>";
            $html .= "<button class='btn btn-sm btn-info' title='Lihat' onclick='lihatDetail({$row['id']})'><i class='bi bi-eye'></i></button> ";
            $html .= "<button class='btn btn-sm btn-success' title='Cetak' onclick='cetakUlang({$row['id']})'><i class='bi bi-printer'></i></button> ";
            
            if ($row['status'] == 'terbit') {
                $html .= "<button class='btn btn-sm btn-warning' title='Batalkan' onclick='batalkanSurat({$row['id']})'><i class='bi bi-x-circle'></i></button> ";
            }
            
            $html .= "<button class='btn btn-sm btn-danger' title='Hapus Permanen' onclick='hapusSatu({$row['id']})'><i class='bi bi-trash'></i></button>";
            
            $html .= "</div>";
            $html .= "</td>";
            $html .= "</tr>";
            $no++;
        }
    } else {
        $html = "<tr><td colspan='9' class='text-center'>Tidak ada data</td></tr>";
    }
    
    $total_pages = ceil($total / $limit);
    $pagination = '';
    
    if ($total_pages > 1) {
        $pagination = '<nav><ul class="pagination justify-content-center">';
        
        $prev_disabled = ($page == 1) ? 'disabled' : '';
        $pagination .= "<li class='page-item {$prev_disabled}'><a class='page-link' href='#' onclick='loadData(" . ($page - 1) . "); return false;'>&laquo;</a></li>";
        
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);
        
        if ($start_page > 1) {
             $pagination .= "<li class='page-item'><a class='page-link' href='#' onclick='loadData(1); return false;'>1</a></li>";
             if ($start_page > 2) $pagination .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        }

        for ($i = $start_page; $i <= $end_page; $i++) {
            $active = $i == $page ? 'active' : '';
            $pagination .= "<li class='page-item {$active}'><a class='page-link' href='#' onclick='loadData({$i}); return false;'>{$i}</a></li>";
        }

        if ($end_page < $total_pages) {
             if ($end_page < $total_pages - 1) $pagination .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
             $pagination .= "<li class='page-item'><a class='page-link' href='#' onclick='loadData({$total_pages}); return false;'>{$total_pages}</a></li>";
        }
        
        $next_disabled = ($page == $total_pages) ? 'disabled' : '';
        $pagination .= "<li class='page-item {$next_disabled}'><a class='page-link' href='#' onclick='loadData(" . ($page + 1) . "); return false;'>&raquo;</a></li>";
        
        $pagination .= '</ul></nav>';
    }
    
    echo json_encode(['success' => true, 'html' => $html, 'pagination' => $pagination]);
}

function cekMahasiswa() {
    global $conn;
    
    $nim = $_POST['nim'] ?? '';
    
    $query = "SELECT nim, nama, program_studi, angkatan FROM mahasiswa WHERE nim = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $stmt->bind_result($m_nim, $m_nama, $m_prodi, $m_angkatan);
    
    if (!$stmt->fetch()) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Mahasiswa tidak ditemukan']);
        return;
    }
    
    $mahasiswa = [
        'nim' => $m_nim,
        'nama' => $m_nama,
        'program_studi' => $m_prodi,
        'angkatan' => $m_angkatan
    ];
    $stmt->close();
    
    $query = "SELECT COUNT(*) as total FROM peminjaman p
              INNER JOIN mahasiswa m ON p.peminjam_id = m.id
              WHERE m.nim = ? AND p.jenis_peminjam = 'mahasiswa' 
              AND p.status IN ('dipinjam', 'diperpanjang')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $stmt->bind_result($total_pinjam);
    $stmt->fetch();
    $peminjaman_aktif = $total_pinjam > 0;
    $stmt->close();
    
    $query = "SELECT COUNT(*) as total, SUM(pg.sisa_denda) as total_denda
              FROM pengembalian pg
              INNER JOIN peminjaman pm ON pg.peminjaman_id = pm.id
              INNER JOIN mahasiswa m ON pm.peminjam_id = m.id
              WHERE m.nim = ? AND pg.sisa_denda > 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $stmt->bind_result($total_tunggakan, $total_denda);
    $stmt->fetch();
    $stmt->close();
    
    $tunggakan = [
        'ada_tunggakan' => $total_tunggakan > 0,
        'jumlah' => $total_tunggakan ?? 0,
        'total_denda' => $total_denda ?? 0
    ];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'nama_mahasiswa' => $mahasiswa['nama'],
            'nim' => $mahasiswa['nim'],
            'nama_prodi' => $mahasiswa['program_studi'],
            'angkatan' => $mahasiswa['angkatan']
        ],
        'peminjaman_aktif' => $peminjaman_aktif,
        'tunggakan' => $tunggakan
    ]);
}

function previewSurat() {
    global $conn;
    
    $nim = $_POST['nim'] ?? '';
    $jenis_surat = $_POST['jenis_surat'] ?? '';
    
    $tahun = date('Y');
    $bulan = date('n');
    $bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    
    $query = "SELECT MAX(CAST(SUBSTRING_INDEX(nomor_surat, '/', 1) AS UNSIGNED)) as last_number 
              FROM surat_keterangan WHERE tahun_periode = ? AND status = 'terbit'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $tahun);
    $stmt->execute();
    $stmt->bind_result($last_number);
    $stmt->fetch();
    $stmt->close();
    
    $next_number = str_pad(($last_number ?? 0) + 1, 3, '0', STR_PAD_LEFT);
    $nomor_surat = "{$next_number}/SKP-PERP/STK/{$bulan_romawi[$bulan]}/{$tahun}";
    
    $query = "SELECT nim, nama, program_studi, angkatan FROM mahasiswa WHERE nim = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $stmt->bind_result($mhs_nim, $mhs_nama, $mhs_prodi, $mhs_angkatan);
    $stmt->fetch();
    $stmt->close();
    
    $keperluan = $jenis_surat == 'UAS' 
        ? 'Syarat Pengambilan Kartu Ujian Akhir Semester (UAS)'
        : 'Syarat Pendaftaran Peserta Penilaian Pembelajaran Akhir (PPA)';
    
    $bulan_indo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tanggal = date('d') . ' ' . $bulan_indo[date('n')] . ' ' . date('Y');
    
    $preview = "<div style='font-family: Arial; font-size: 12px;'>
        <div style='text-align: center; margin-bottom: 15px;'>
            <div style='font-weight: bold;'>KEMENTERIAN AGAMA REPUBLIK INDONESIA</div>
            <div style='font-size: 10px;'>YAYASAN PENDIDIKAN DAN PERSEKOLAHAN KATOLIK</div>
            <div style='font-weight: bold;'>SEKOLAH TINGGI KATOLIK SANTO YAKOBUS MERAUKE</div>
            <div style='font-size: 10px;'>Jalan Missi II Merauke Papua Selatan 99616</div>
            <hr style='border-top: 3px solid #000;'>
        </div>
        <div style='text-align: center; margin: 15px 0;'>
            <div style='font-weight: bold; text-decoration: underline;'>SURAT KETERANGAN BEBAS PERPUSTAKAAN</div>
            <div>No. {$nomor_surat}</div>
        </div>
        <div style='line-height: 1.6;'>
            <p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p>
            <table style='width: 100%; margin: 10px 0;'>
                <tr><td width='35%'>Nama Mahasiswa</td><td>: <strong>{$mhs_nama}</strong></td></tr>
                <tr><td>NIM</td><td>: <strong>{$mhs_nim}</strong></td></tr>
                <tr><td>Angkatan</td><td>: <strong>{$mhs_angkatan}</strong></td></tr>
                <tr><td>Program Studi</td><td>: <strong>{$mhs_prodi}</strong></td></tr>
            </table>
            <p>telah menyelesaikan administrasi perpustakaan dan dinyatakan <strong>Bebas Perpustakaan</strong> sebagai <strong>{$keperluan}</strong>.</p>
            <div style='margin-top: 30px; text-align: right;'>
                <div>Merauke, {$tanggal}</div>
                <div>Kepala Perpustakaan</div>
                <div style='margin-top: 60px;'><strong>Yuliana Mangera, S.S.I</strong></div>
            </div>
        </div>
    </div>";
    
    echo json_encode(['success' => true, 'nomor_surat' => $nomor_surat, 'preview_html' => $preview]);
}

function terbitkanSurat() {
    global $conn;
    
    $nim = $_POST['nim'] ?? '';
    $jenis_surat = $_POST['jenis_surat'] ?? '';
    $override = isset($_POST['override_tunggakan']) ? 1 : 0;
    $catatan = $_POST['catatan'] ?? '';
    
    $tahun = date('Y');
    $bulan = date('n');
    $bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    
    $query = "SELECT MAX(CAST(SUBSTRING_INDEX(nomor_surat, '/', 1) AS UNSIGNED)) as last_number 
              FROM surat_keterangan WHERE tahun_periode = ? AND status = 'terbit'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $tahun);
    $stmt->execute();
    $stmt->bind_result($last_number);
    $stmt->fetch();
    $stmt->close();
    
    $next_number = str_pad(($last_number ?? 0) + 1, 3, '0', STR_PAD_LEFT);
    $nomor_surat = "{$next_number}/SKP-PERP/STK/{$bulan_romawi[$bulan]}/{$tahun}";
    
    $tanggal_terbit = date('Y-m-d');
    $admin_id = $_SESSION['admin_id'];
    
    $query = "INSERT INTO surat_keterangan 
              (nomor_surat, nim, jenis_surat, tanggal_terbit, tahun_periode, status, catatan, admin_id, override_tunggakan) 
              VALUES (?, ?, ?, ?, ?, 'terbit', ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssisii", $nomor_surat, $nim, $jenis_surat, $tanggal_terbit, $tahun, $catatan, $admin_id, $override);
    
    if ($stmt->execute()) {
        $surat_id = $conn->insert_id;
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Surat berhasil diterbitkan', 'pdf_url' => 'generate_pdf.php?id=' . $surat_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal: ' . $conn->error]);
    }
}

function detailSurat() {
    global $conn;
    
    $id = intval($_POST['id'] ?? 0);
    
    $query = "SELECT sk.*, m.nama, m.program_studi, m.angkatan
              FROM surat_keterangan sk
              INNER JOIN mahasiswa m ON sk.nim = m.nim
              WHERE sk.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($id, $nomor, $nim, $jenis, $tgl, $tahun, $status, $catatan, $admin_id, $file_pdf, $override, $created, $updated, $nama, $prodi, $angkatan);
    
    if ($stmt->fetch()) {
        $badge_jenis = $jenis == 'UAS' ? 'bg-primary' : 'bg-info';
        $badge_status = $status == 'terbit' ? 'bg-success' : 'bg-danger';
        
        $html = "<table class='table table-bordered'>
            <tr><th width='30%'>Nomor Surat</th><td>{$nomor}</td></tr>
            <tr><th>Jenis</th><td><span class='badge {$badge_jenis}'>{$jenis}</span></td></tr>
            <tr><th>Status</th><td><span class='badge {$badge_status}'>{$status}</span></td></tr>
            <tr><th>NIM</th><td>{$nim}</td></tr>
            <tr><th>Nama</th><td>{$nama}</td></tr>
            <tr><th>Program Studi</th><td>{$prodi}</td></tr>
            <tr><th>Tanggal Terbit</th><td>" . date('d/m/Y', strtotime($tgl)) . "</td></tr>
        </table>
        <div class='text-end'>
            <button class='btn btn-primary' onclick='cetakUlang({$id})'><i class='bi bi-printer'></i> Cetak</button>
        </div>";
        
        $stmt->close();
        echo json_encode(['success' => true, 'html' => $html]);
    } else {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
    }
}

function batalkanSurat() {
    global $conn;
    
    $id = intval($_POST['id'] ?? 0);
    
    $query = "UPDATE surat_keterangan SET status = 'dibatalkan' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Surat berhasil dibatalkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal: ' . $conn->error]);
    }
}

function hapusData() {
    global $conn;
    
    $ids_raw = $_POST['ids'] ?? '';
    
    if (empty($ids_raw)) {
        echo json_encode(['success' => false, 'message' => 'Tidak ada data yang dipilih']);
        return;
    }
    
    // Sanitasi input menjadi array integer
    $ids_arr = explode(',', $ids_raw);
    $ids_clean = array_map('intval', $ids_arr);
    $ids_string = implode(',', $ids_clean);
    
    if (empty($ids_string)) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        return;
    }

    // A. HAPUS FILE PDF FISIK (Penting untuk hemat storage)
    // PERBAIKAN: Pastikan SK_PDF_PATH tersedia. Jika require_once 'config.php' sudah ada di atas, ini aman.
    if (!defined('SK_PDF_PATH')) {
         // Fallback jika terjadi error constant, meski seharusnya tidak terjadi jika header benar
         define('SK_PDF_PATH', dirname(__FILE__) . '/pdf_generated/');
    }

    $sql_file = "SELECT file_pdf FROM surat_keterangan WHERE id IN ($ids_string)";
    $res_file = $conn->query($sql_file);
    $files_deleted = 0;
    
    if ($res_file) {
        while ($row = $res_file->fetch_assoc()) {
            if (!empty($row['file_pdf'])) {
                $path = SK_PDF_PATH . $row['file_pdf'];
                if (file_exists($path)) {
                    @unlink($path); 
                    $files_deleted++;
                }
            }
        }
    }
    
    // B. HAPUS DATA DARI DATABASE
    $sql = "DELETE FROM surat_keterangan WHERE id IN ($ids_string)";
    
    if ($conn->query($sql)) {
        $count = $conn->affected_rows;
        echo json_encode([
            'success' => true, 
            'message' => "Berhasil menghapus $count data surat dan $files_deleted file PDF."
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal DB: ' . $conn->error]);
    }
}
?>