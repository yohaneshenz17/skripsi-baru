<?php
// ============================================
// FILE: application/controllers/kaprodi/Kaprodi.php (DIPERBAIKI LENGKAP)
// ============================================

defined('BASEPATH') OR exit('No direct script access allowed');

class Kaprodi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper('url');
        
        // Cek login dan level
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '4') {
            redirect('auth/login');
        }
        
        // Get prodi_id kaprodi
        $this->prodi_id = $this->session->userdata('prodi_id');
        if (!$this->prodi_id) {
            // Coba ambil dari database jika tidak ada di session
            $kaprodi = $this->db->get_where('prodi', ['dosen_id' => $this->session->userdata('id')])->row();
            if ($kaprodi) {
                $this->session->set_userdata('prodi_id', $kaprodi->id);
                $this->prodi_id = $kaprodi->id;
            }
        }
    }

    public function index() {
        redirect('kaprodi/dashboard');
    }

    // ============================================
    // METHOD PROPOSAL() - DIPERBAIKI
    // ============================================
    public function proposal() {
        $data['title'] = 'Review Proposal Mahasiswa';
        
        // Ambil semua proposal dari mahasiswa prodi ini
        $this->db->select('
            proposal_mahasiswa.*, 
            mahasiswa.nim, 
            mahasiswa.nama as nama_mahasiswa,
            mahasiswa.email as email_mahasiswa,
            d1.nama as nama_pembimbing
        ');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->join('dosen d1', 'proposal_mahasiswa.dosen_id = d1.id', 'left');
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        $this->db->order_by('proposal_mahasiswa.id', 'DESC');
        
        $data['proposals'] = $this->db->get()->result();
        
        // PERBAIKAN: Pastikan $proposals tidak null
        if (!$data['proposals']) {
            $data['proposals'] = array(); // Atur sebagai array kosong jika null
        }
    
        // PERBAIKAN: Langsung load view tanpa wrapper yang rumit
        $this->load->view('kaprodi/proposal', $data);
    }

    // ============================================
    // METHOD _get_proposal_content - DIPERBAIKI
    // ============================================
    private function _get_proposal_content_fixed($data) {
        // PERBAIKAN: Extract data agar tersedia sebagai variabel lokal
        extract($data);
        
        ob_start();
        include(APPPATH . 'views/kaprodi/proposal.php');
        return ob_get_clean();
    }

    // ============================================
    // METHOD ALTERNATIF YANG LEBIH AMAN
    // ============================================
    private function _get_proposal_content_safe($data) {
        // PERBAIKAN: Gunakan CodeIgniter load view dengan return TRUE
        return $this->load->view('kaprodi/proposal', $data, TRUE);
    }

    private function _get_proposal_script() {
        ob_start();
        ?>
        <script>
        $(document).ready(function() {
            // Initialize DataTables untuk setiap tab jika library tersedia
            if (typeof $.fn.DataTable !== 'undefined') {
                $('#table-menunggu').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                    },
                    "order": [[ 4, "desc" ]]
                });
                
                $('#table-disetujui').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                    },
                    "order": [[ 6, "desc" ]]
                });
                
                $('#table-ditolak').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                    },
                    "order": [[ 5, "desc" ]]
                });
                
                $('#table-menunggu-pembimbing').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                    },
                    "order": [[ 6, "desc" ]]
                });
                
                $('#table-riwayat').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                    },
                    "order": [[ 6, "desc" ]]
                });
            }
            
            // Enable tooltips jika library tersedia
            if (typeof $().tooltip !== 'undefined') {
                $('[data-toggle="tooltip"]').tooltip();
            }
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });

        // Function untuk menampilkan tab Riwayat Review
        function showRiwayatTab() {
            $('#tabs-icons-text-5-tab').tab('show');
            $('html, body').animate({
                scrollTop: $('#tabs-icons-text').offset().top - 100
            }, 500);
        }
        </script>
        <?php
        return ob_get_clean();
    }

    public function review_proposal($proposal_id) {
        $data['title'] = 'Review Detail Proposal';
        
        // Ambil detail proposal dengan semua field termasuk file_draft_proposal
        $this->db->select('
            proposal_mahasiswa.*, 
            mahasiswa.nim, 
            mahasiswa.nama as nama_mahasiswa, 
            mahasiswa.email as email_mahasiswa,
            prodi.nama as nama_prodi
        ');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->join('prodi', 'mahasiswa.prodi_id = prodi.id');
        $this->db->where('proposal_mahasiswa.id', $proposal_id);
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        
        $data['proposal'] = $this->db->get()->row();
        
        if(!$data['proposal']) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan!');
            redirect('kaprodi/proposal');
        }
        
        // Ambil SEMUA dosen dari SEMUA prodi sebagai calon pembimbing
        $this->db->select('dosen.*, prodi.nama as nama_prodi');
        $this->db->from('dosen');
        $this->db->join('prodi', 'dosen.prodi_id = prodi.id', 'left');
        $this->db->where('dosen.level', '2');
        $this->db->where('dosen.id !=', $this->session->userdata('id')); // Exclude kaprodi yang sedang login
        $this->db->order_by('dosen.nama', 'ASC');
        $data['dosens'] = $this->db->get()->result();
        
        $this->load->view('template/kaprodi', [
            'title' => $data['title'],
            'content' => $this->_get_review_proposal_content($data),
            'script' => $this->_get_review_proposal_script()
        ]);
    }

    private function _get_review_proposal_content($data) {
        // PERBAIKAN: Extract data agar tersedia sebagai variabel lokal
        extract($data);
        
        ob_start();
        ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">Review Proposal Mahasiswa</h3>
                                <p class="text-sm mb-0">Detail proposal dan form review</p>
                            </div>
                            <div class="col text-right">
                                <a href="<?= base_url('kaprodi/proposal') ?>" class="btn btn-secondary btn-sm">
                                    <i class="ni ni-bold-left"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Detail Mahasiswa -->
                            <div class="col-md-6">
                                <h5 class="heading-small text-muted mb-4">Data Mahasiswa</h5>
                                <div class="pl-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">NIM</label>
                                        <p class="form-control-static font-weight-bold"><?= $proposal->nim ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-control-label">Nama Mahasiswa</label>
                                        <p class="form-control-static font-weight-bold"><?= $proposal->nama_mahasiswa ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-control-label">Email</label>
                                        <p class="form-control-static"><?= $proposal->email_mahasiswa ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-control-label">Program Studi</label>
                                        <p class="form-control-static"><?= $proposal->nama_prodi ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Detail Proposal -->
                            <div class="col-md-6">
                                <h5 class="heading-small text-muted mb-4">Detail Proposal</h5>
                                <div class="pl-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Judul Proposal</label>
                                        <p class="form-control-static font-weight-bold"><?= $proposal->judul ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-control-label">Tanggal Pengajuan</label>
                                        <p class="form-control-static"><?= date('d/m/Y H:i', strtotime($proposal->created_at)) ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-control-label">File Proposal</label>
                                        <div>
                                            <?php if(!empty($proposal->file_draft_proposal)): ?>
                                            <a href="<?= base_url('kaprodi/download_proposal/' . $proposal->id) ?>" class="btn btn-primary btn-sm">
                                                <i class="fa fa-download"></i> Download
                                            </a>
                                            <a href="<?= base_url('kaprodi/view_proposal/' . $proposal->id) ?>" class="btn btn-info btn-sm" target="_blank">
                                                <i class="fa fa-eye"></i> Lihat
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted">File tidak tersedia</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Form Review -->
                        <h5 class="heading-small text-muted mb-4">Form Review Proposal</h5>
                        <form method="post" action="<?= base_url('kaprodi/proses_review') ?>" id="form-review">
                            <input type="hidden" name="proposal_id" value="<?= $proposal->id ?>">
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="form-control-label">Komentar Review</label>
                                        <textarea class="form-control" name="komentar_kaprodi" rows="4" placeholder="Berikan komentar review untuk proposal ini..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Dosen Pembimbing</label>
                                        <select class="form-control" name="dosen_id" id="dosen_id">
                                            <option value="">-- Pilih Dosen Pembimbing --</option>
                                            <?php foreach($dosens as $dosen): ?>
                                            <option value="<?= $dosen->id ?>"><?= $dosen->nama ?> (<?= $dosen->nama_prodi ?? 'Prodi tidak ditemukan' ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Wajib dipilih jika proposal disetujui</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <button type="submit" name="aksi" value="tolak" class="btn btn-danger" onclick="return confirm('Yakin ingin menolak proposal ini?')">
                                    <i class="fa fa-times"></i> Tolak Proposal
                                </button>
                                <button type="submit" name="aksi" value="setujui" class="btn btn-success" onclick="return validateApproval()">
                                    <i class="fa fa-check"></i> Setujui & Tetapkan Pembimbing
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function _get_review_proposal_script() {
        ob_start();
        ?>
        <script>
        function validateApproval() {
            var dosenId = $('#dosen_id').val();
            if (!dosenId) {
                alert('Silakan pilih dosen pembimbing terlebih dahulu!');
                return false;
            }
            return confirm('Yakin ingin menyetujui proposal ini dan menetapkan pembimbing?');
        }
        </script>
        <?php
        return ob_get_clean();
    }

    // Method untuk download file proposal
    public function download_proposal($proposal_id) {
        // Validasi proposal
        $this->db->select('proposal_mahasiswa.*, mahasiswa.nama as nama_mahasiswa');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->where('proposal_mahasiswa.id', $proposal_id);
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        
        $proposal = $this->db->get()->row();
        
        if (!$proposal) {
            show_404();
        }
        
        if (empty($proposal->file_draft_proposal)) {
            $this->session->set_flashdata('error', 'File proposal tidak tersedia!');
            redirect('kaprodi/review_proposal/' . $proposal_id);
        }
        
        $file_path = FCPATH . 'cdn/proposals/' . $proposal->file_draft_proposal;
        
        // Cek apakah file ada
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File proposal tidak ditemukan di server!');
            redirect('kaprodi/review_proposal/' . $proposal_id);
        }
        
        // Download file
        $this->load->helper('download');
        $file_name = 'Proposal_' . str_replace(' ', '_', $proposal->nama_mahasiswa) . '_' . date('Y-m-d') . '.' . pathinfo($proposal->file_draft_proposal, PATHINFO_EXTENSION);
        
        force_download($file_name, file_get_contents($file_path));
    }
    
    // Method untuk view file proposal di browser
    public function view_proposal($proposal_id) {
        // Validasi proposal
        $this->db->select('proposal_mahasiswa.*, mahasiswa.nama as nama_mahasiswa');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->where('proposal_mahasiswa.id', $proposal_id);
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        
        $proposal = $this->db->get()->row();
        
        if (!$proposal) {
            show_404();
        }
        
        if (empty($proposal->file_draft_proposal)) {
            echo '<div style="padding: 20px; font-family: Arial;"><h3>File tidak tersedia</h3><p>File proposal tidak ada atau belum diupload.</p></div>';
            return;
        }
        
        $file_path = FCPATH . 'cdn/proposals/' . $proposal->file_draft_proposal;
        
        // Cek apakah file ada
        if (!file_exists($file_path)) {
            echo '<div style="padding: 20px; font-family: Arial;"><h3>File tidak ditemukan</h3><p>File proposal tidak ditemukan di server.</p></div>';
            return;
        }
        
        // Set header untuk menampilkan file
        $file_info = pathinfo($file_path);
        $extension = strtolower($file_info['extension']);
        
        switch($extension) {
            case 'pdf':
                header('Content-Type: application/pdf');
                break;
            case 'doc':
                header('Content-Type: application/msword');
                break;
            case 'docx':
                header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                break;
            default:
                header('Content-Type: application/octet-stream');
        }
        
        header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));
        
        readfile($file_path);
    }

    public function proses_review() {
        $proposal_id = $this->input->post('proposal_id');
        $aksi = $this->input->post('aksi'); // 'setujui' atau 'tolak'
        $komentar = $this->input->post('komentar_kaprodi');
        $dosen_id = $this->input->post('dosen_id'); // hanya jika disetujui
        
        // Validasi input
        if (empty($proposal_id) || empty($aksi)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('kaprodi/proposal');
        }
        
        // Validasi proposal
        $proposal = $this->db->select('proposal_mahasiswa.*, mahasiswa.nama as nama_mahasiswa, mahasiswa.email as email_mahasiswa')
                            ->from('proposal_mahasiswa')
                            ->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id')
                            ->where('proposal_mahasiswa.id', $proposal_id)
                            ->where('mahasiswa.prodi_id', $this->prodi_id)
                            ->get()->row();
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan!');
            redirect('kaprodi/proposal');
        }
        
        if ($aksi == 'setujui') {
            if (!$dosen_id) {
                $this->session->set_flashdata('error', 'Dosen pembimbing harus dipilih!');
                redirect('kaprodi/review_proposal/' . $proposal_id);
            }
            
            // Update proposal - disetujui kaprodi dan tetapkan pembimbing
            $data_update = [
                'status_kaprodi' => '1',
                'komentar_kaprodi' => $komentar,
                'tanggal_review_kaprodi' => date('Y-m-d H:i:s'),
                'dosen_id' => $dosen_id,
                'penetapan_oleh' => $this->session->userdata('id'),
                'tanggal_penetapan' => date('Y-m-d H:i:s'),
                'status_pembimbing' => '0', // Menunggu persetujuan pembimbing
                'workflow_status' => 'menunggu_pembimbing'
            ];
            
            $result = $this->db->where('id', $proposal_id)->update('proposal_mahasiswa', $data_update);
            
            if ($result) {
                // Kirim notifikasi ke dosen pembimbing
                $this->_kirim_notifikasi_pembimbing($proposal_id, $dosen_id);
                
                // Kirim notifikasi ke mahasiswa
                $this->_kirim_notifikasi_mahasiswa($proposal_id, 'disetujui');
                
                $this->session->set_flashdata('success', 'Proposal berhasil disetujui dan dosen pembimbing telah ditetapkan!');
            } else {
                $this->session->set_flashdata('error', 'Gagal menyimpan review proposal!');
            }
            
        } else if ($aksi == 'tolak') {
            // Update proposal - ditolak kaprodi
            $data_update = [
                'status_kaprodi' => '2',
                'komentar_kaprodi' => $komentar,
                'tanggal_review_kaprodi' => date('Y-m-d H:i:s'),
                'workflow_status' => 'ditolak'
            ];
            
            $result = $this->db->where('id', $proposal_id)->update('proposal_mahasiswa', $data_update);
            
            if ($result) {
                // Kirim notifikasi ke mahasiswa
                $this->_kirim_notifikasi_mahasiswa($proposal_id, 'ditolak');
                
                $this->session->set_flashdata('success', 'Proposal telah ditolak dan mahasiswa telah diberi tahu.');
            } else {
                $this->session->set_flashdata('error', 'Gagal menyimpan review proposal!');
            }
        }
        
        redirect('kaprodi/proposal');
    }

    private function _kirim_notifikasi_pembimbing($proposal_id, $dosen_id) {
        // Ambil data proposal dan mahasiswa
        $data = $this->db->select('proposal_mahasiswa.*, mahasiswa.nama as nama_mahasiswa, mahasiswa.nim, mahasiswa.email as email_mahasiswa, prodi.nama as nama_prodi')
                        ->from('proposal_mahasiswa')
                        ->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id')
                        ->join('prodi', 'mahasiswa.prodi_id = prodi.id')
                        ->where('proposal_mahasiswa.id', $proposal_id)
                        ->get()->row();
        
        $dosen = $this->db->get_where('dosen', ['id' => $dosen_id])->row();
        
        if ($data && $dosen) {
            // Setup email
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'html',
                'smtp_crypto' => 'tls'
            ];
            
            $this->email->initialize($config);
            
            $subject = 'Penunjukan sebagai Dosen Pembimbing - ' . $data->nama_mahasiswa;
            
            $message = "
            <h3>Penunjukan sebagai Dosen Pembimbing</h3>
            <p>Yth. {$dosen->nama},</p>
            <p>Anda telah ditunjuk sebagai <strong>Dosen Pembimbing</strong> untuk mahasiswa:</p>
            <ul>
                <li>Nama: {$data->nama_mahasiswa}</li>
                <li>NIM: {$data->nim}</li>
                <li>Prodi: {$data->nama_prodi}</li>
                <li>Judul: {$data->judul}</li>
            </ul>
            <p>Silakan login ke sistem untuk memberikan persetujuan: <a href='" . base_url('dosen/usulan_proposal') . "'>Login Sistem</a></p>
            <p>Terima kasih atas kesediaannya.</p>
            ";
            
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($dosen->email);
            $this->email->subject($subject);
            $this->email->message($message);
            
            $this->email->send();
        }
    }

    private function _kirim_notifikasi_mahasiswa($proposal_id, $status) {
        // Ambil data proposal dan mahasiswa
        $data = $this->db->select('proposal_mahasiswa.*, mahasiswa.nama as nama_mahasiswa, mahasiswa.email as email_mahasiswa')
                        ->from('proposal_mahasiswa')
                        ->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id')
                        ->where('proposal_mahasiswa.id', $proposal_id)
                        ->get()->row();
        
        if ($data) {
            // Setup email
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'html',
                'smtp_crypto' => 'tls'
            ];
            
            $this->email->initialize($config);
            
            if ($status == 'disetujui') {
                $subject = 'Proposal Disetujui - Menunggu Persetujuan Dosen Pembimbing';
                $message = "
                <h3>Proposal Disetujui</h3>
                <p>Yth. {$data->nama_mahasiswa},</p>
                <p>Proposal Anda dengan judul <strong>{$data->judul}</strong> telah <strong>DISETUJUI</strong> oleh Kaprodi.</p>
                <p>Status saat ini: <strong>Menunggu persetujuan dosen pembimbing</strong></p>
                <p>Silakan pantau perkembangan di sistem: <a href='" . base_url('mahasiswa/proposal') . "'>Login Sistem</a></p>
                ";
            } else {
                $subject = 'Proposal Ditolak - Perlu Perbaikan';
                $message = "
                <h3>Proposal Ditolak</h3>
                <p>Yth. {$data->nama_mahasiswa},</p>
                <p>Proposal Anda dengan judul <strong>{$data->judul}</strong> belum dapat disetujui.</p>
                <p>Komentar: {$data->komentar_kaprodi}</p>
                <p>Silakan lakukan perbaikan dan ajukan kembali.</p>
                ";
            }
            
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($data->email_mahasiswa);
            $this->email->subject($subject);
            $this->email->message($message);
            
            $this->email->send();
        }
    }

    public function mahasiswa() {
        $data['title'] = 'Daftar Mahasiswa Prodi';
        
        $this->db->from('mahasiswa');
        $this->db->where('prodi_id', $this->prodi_id);
        $this->db->order_by('nim', 'ASC');
        $data['mahasiswa_list'] = $this->db->get()->result();
        
        $this->load->view('template/kaprodi', [
            'title' => $data['title'],
            'content' => $this->_get_mahasiswa_content($data),
            'script' => ''
        ]);
    }

    private function _get_mahasiswa_content($data) {
        // PERBAIKAN: Extract data agar tersedia sebagai variabel lokal
        extract($data);
        
        ob_start();
        ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Daftar Mahasiswa Program Studi</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush datatable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach($mahasiswa_list as $mhs): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $mhs->nim ?></td>
                                        <td><?= $mhs->nama ?></td>
                                        <td><?= $mhs->email ?></td>
                                        <td>
                                            <?php if($mhs->status == '1'): ?>
                                            <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                            <span class="badge badge-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-info">
                                                <i class="fa fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function dosen() {
        $data['title'] = 'Daftar Seluruh Dosen';
        
        // Tampilkan semua dosen dari semua prodi dengan info prodi
        $this->db->select('dosen.*, prodi.nama as nama_prodi');
        $this->db->from('dosen');
        $this->db->join('prodi', 'dosen.prodi_id = prodi.id', 'left');
        $this->db->where('dosen.level', '2');
        $this->db->order_by('dosen.nama', 'ASC');
        $data['dosen_list'] = $this->db->get()->result();
        
        $this->load->view('template/kaprodi', [
            'title' => $data['title'],
            'content' => $this->_get_dosen_content($data),
            'script' => ''
        ]);
    }

    private function _get_dosen_content($data) {
        // PERBAIKAN: Extract data agar tersedia sebagai variabel lokal
        extract($data);
        
        ob_start();
        ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Daftar Seluruh Dosen</h3>
                        <p class="text-sm mb-0">Dosen dari semua program studi yang dapat ditunjuk sebagai pembimbing</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush datatable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th>NIP</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Program Studi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach($dosen_list as $dsn): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $dsn->nip ?></td>
                                        <td><?= $dsn->nama ?></td>
                                        <td><?= $dsn->email ?></td>
                                        <td><?= $dsn->nama_prodi ?? 'Belum ditentukan' ?></td>
                                        <td>
                                            <span class="badge badge-success">Aktif</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function laporan() {
        $data['title'] = 'Rekapitulasi Laporan';

        // Data proposal berdasarkan status
        $this->db->select('proposal_mahasiswa.*, mahasiswa.nim, mahasiswa.nama as nama_mahasiswa');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        $this->db->order_by('proposal_mahasiswa.id', 'DESC');
        $data['all_proposals'] = $this->db->get()->result();
        
        $this->load->view('template/kaprodi', [
            'title' => $data['title'],
            'content' => $this->_get_laporan_content($data),
            'script' => ''
        ]);
    }

    private function _get_laporan_content($data) {
        // PERBAIKAN: Extract data agar tersedia sebagai variabel lokal
        extract($data);
        
        ob_start();
        ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Rekapitulasi Laporan Tugas Akhir</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush datatable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Judul</th>
                                        <th>Status Kaprodi</th>
                                        <th>Status Pembimbing</th>
                                        <th>Workflow</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach($all_proposals as $prop): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $prop->nim ?></td>
                                        <td><?= $prop->nama_mahasiswa ?></td>
                                        <td><?= substr($prop->judul, 0, 50) ?>...</td>
                                        <td>
                                            <?php 
                                            switch($prop->status_kaprodi) {
                                                case '0': echo '<span class="badge badge-warning">Menunggu Review</span>'; break;
                                                case '1': echo '<span class="badge badge-success">Disetujui</span>'; break;
                                                case '2': echo '<span class="badge badge-danger">Ditolak</span>'; break;
                                                default: echo '<span class="badge badge-secondary">Belum Ditentukan</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if(isset($prop->status_pembimbing)):
                                                switch($prop->status_pembimbing) {
                                                    case '0': echo '<span class="badge badge-warning">Menunggu Persetujuan</span>'; break;
                                                    case '1': echo '<span class="badge badge-success">Disetujui</span>'; break;
                                                    case '2': echo '<span class="badge badge-danger">Ditolak</span>'; break;
                                                    default: echo '<span class="badge badge-secondary">Belum Ditentukan</span>';
                                                }
                                            else:
                                                echo '<span class="badge badge-secondary">Belum Ditentukan</span>';
                                            endif;
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if(isset($prop->workflow_status)):
                                                switch($prop->workflow_status) {
                                                    case 'proposal': echo '<span class="badge badge-info">Tahap Proposal</span>'; break;
                                                    case 'menunggu_pembimbing': echo '<span class="badge badge-warning">Menunggu Pembimbing</span>'; break;
                                                    case 'bimbingan': echo '<span class="badge badge-primary">Bimbingan</span>'; break;
                                                    case 'seminar_proposal': echo '<span class="badge badge-info">Seminar Proposal</span>'; break;
                                                    case 'penelitian': echo '<span class="badge badge-warning">Penelitian</span>'; break;
                                                    case 'seminar_skripsi': echo '<span class="badge badge-success">Seminar Skripsi</span>'; break;
                                                    case 'publikasi': echo '<span class="badge badge-purple">Publikasi</span>'; break;
                                                    case 'selesai': echo '<span class="badge badge-success">Selesai</span>'; break;
                                                    case 'ditolak': echo '<span class="badge badge-danger">Ditolak</span>'; break;
                                                    default: echo '<span class="badge badge-secondary">Belum Ditentukan</span>';
                                                }
                                            else:
                                                echo '<span class="badge badge-secondary">Belum Ditentukan</span>';
                                            endif;
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}