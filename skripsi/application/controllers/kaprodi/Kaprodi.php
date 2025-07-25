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

    // ============================================
    // PERBAIKAN METHOD review_proposal() DAN _get_review_proposal_content()
    // ============================================
    
    public function review_proposal($proposal_id) {
        $data['title'] = 'Review Detail Proposal';
        
        // Ambil detail proposal dengan semua field termasuk file_draft_proposal
        $this->db->select('
            proposal_mahasiswa.*, 
            mahasiswa.nim, 
            mahasiswa.nama as nama_mahasiswa, 
            mahasiswa.email as email_mahasiswa,
            prodi.nama as nama_prodi,
            d1.nama as nama_pembimbing,
            d2.nama as nama_kaprodi_reviewer
        ');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->join('prodi', 'mahasiswa.prodi_id = prodi.id');
        $this->db->join('dosen d1', 'proposal_mahasiswa.dosen_id = d1.id', 'left');
        $this->db->join('dosen d2', 'proposal_mahasiswa.penetapan_oleh = d2.id', 'left');
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
        
        // Tentukan apakah proposal sudah di-review
        $is_reviewed = ($proposal->status_kaprodi != '0'); // 0 = belum di-review, 1 = disetujui, 2 = ditolak
        $is_approved = ($proposal->status_kaprodi == '1');
        $is_rejected = ($proposal->status_kaprodi == '2');
        
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
                        <!-- Status Alert -->
                        <?php if($is_reviewed): ?>
                        <div class="alert alert-<?= $is_approved ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                            <span class="alert-icon">
                                <i class="fa fa-<?= $is_approved ? 'check-circle' : 'times-circle' ?>"></i>
                            </span>
                            <span class="alert-text">
                                <strong>Proposal Sudah Di-review!</strong><br>
                                Status: <strong><?= $is_approved ? 'DISETUJUI' : 'DITOLAK' ?></strong>
                                <?php if(!empty($proposal->tanggal_review_kaprodi)): ?>
                                pada <?= date('d/m/Y H:i', strtotime($proposal->tanggal_review_kaprodi)) ?>
                                <?php endif; ?>
                                <?php if(!empty($proposal->nama_kaprodi_reviewer)): ?>
                                oleh <?= $proposal->nama_kaprodi_reviewer ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php endif; ?>
    
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
                                    
                                    <!-- Status Current -->
                                    <div class="form-group">
                                        <label class="form-control-label">Status Workflow</label>
                                        <div>
                                            <?php 
                                            switch($proposal->workflow_status) {
                                                case 'proposal': echo '<span class="badge badge-info">Tahap Proposal</span>'; break;
                                                case 'menunggu_pembimbing': echo '<span class="badge badge-warning">Menunggu Pembimbing</span>'; break;
                                                case 'bimbingan': echo '<span class="badge badge-primary">Bimbingan</span>'; break;
                                                case 'ditolak': echo '<span class="badge badge-danger">Ditolak</span>'; break;
                                                default: echo '<span class="badge badge-secondary">Belum Ditentukan</span>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <hr class="my-4">
    
                        <!-- History Review (jika sudah di-review) -->
                        <?php if($is_reviewed): ?>
                        <h5 class="heading-small text-muted mb-4">Riwayat Review</h5>
                        <div class="card border-left-<?= $is_approved ? 'success' : 'danger' ?> shadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 class="text-<?= $is_approved ? 'success' : 'danger' ?> font-weight-bold mb-2">
                                            <i class="fa fa-<?= $is_approved ? 'check-circle' : 'times-circle' ?>"></i>
                                            <?= $is_approved ? 'PROPOSAL DISETUJUI' : 'PROPOSAL DITOLAK' ?>
                                        </h6>
                                        <p class="text-sm text-muted mb-2">
                                            <strong>Tanggal Review:</strong> 
                                            <?= $proposal->tanggal_review_kaprodi ? date('d F Y, H:i', strtotime($proposal->tanggal_review_kaprodi)) : '-' ?>
                                        </p>
                                        <p class="text-sm text-muted mb-2">
                                            <strong>Reviewer:</strong> 
                                            <?= $proposal->nama_kaprodi_reviewer ?? 'Kaprodi' ?>
                                        </p>
                                        <?php if(!empty($proposal->komentar_kaprodi)): ?>
                                        <div class="mt-3">
                                            <label class="text-sm font-weight-bold">Komentar Review:</label>
                                            <div class="alert alert-light">
                                                <?= nl2br(htmlspecialchars($proposal->komentar_kaprodi)) ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?php if($is_approved && !empty($proposal->nama_pembimbing)): ?>
                                        <h6 class="text-success font-weight-bold mb-2">
                                            <i class="fa fa-user-tie"></i> Dosen Pembimbing
                                        </h6>
                                        <div class="alert alert-success">
                                            <strong><?= $proposal->nama_pembimbing ?></strong><br>
                                            <small class="text-muted">
                                                Ditetapkan: <?= $proposal->tanggal_penetapan ? date('d/m/Y', strtotime($proposal->tanggal_penetapan)) : '-' ?>
                                            </small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        <?php endif; ?>
    
                        <!-- Form Review -->
                        <h5 class="heading-small text-muted mb-4">
                            <?= $is_reviewed ? 'Form Review (Terkunci)' : 'Form Review Proposal' ?>
                        </h5>
                        
                        <?php if($is_reviewed): ?>
                        <!-- Form Terkunci -->
                        <div class="alert alert-info">
                            <div class="row align-items-center">
                                <div class="col">
                                    <span class="alert-icon"><i class="fa fa-lock"></i></span>
                                    <span class="alert-text">
                                        <strong>Form Review Terkunci</strong><br>
                                        Proposal ini sudah pernah di-review. 
                                        <?php if($is_rejected): ?>
                                        Jika ingin melakukan review ulang, gunakan tombol di bawah.
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php if($is_rejected): ?>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-warning btn-sm" onclick="unlockReviewForm()">
                                        <i class="fa fa-unlock"></i> Review Ulang
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div id="locked-form">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="form-control-label">Komentar Review</label>
                                        <textarea class="form-control" rows="4" disabled><?= $proposal->komentar_kaprodi ?? 'Tidak ada komentar' ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Dosen Pembimbing</label>
                                        <input type="text" class="form-control" value="<?= $proposal->nama_pembimbing ?? 'Belum ditetapkan' ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <button type="button" class="btn btn-secondary" disabled>
                                    <i class="fa fa-lock"></i> Form Terkunci
                                </button>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Form Aktif -->
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
                        <?php endif; ?>
                        
                        <!-- Form Review Ulang (hidden by default) -->
                        <?php if($is_rejected): ?>
                        <div id="unlock-form" style="display: none;">
                            <div class="alert alert-warning">
                                <strong>Review Ulang Proposal</strong><br>
                                Anda akan melakukan review ulang untuk proposal yang sebelumnya ditolak.
                            </div>
                            
                            <form method="post" action="<?= base_url('kaprodi/proses_review') ?>" id="form-review-ulang">
                                <input type="hidden" name="proposal_id" value="<?= $proposal->id ?>">
                                <input type="hidden" name="review_ulang" value="1">
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="form-control-label">Komentar Review Ulang</label>
                                            <textarea class="form-control" name="komentar_kaprodi" rows="4" placeholder="Berikan komentar untuk review ulang..."></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-control-label">Dosen Pembimbing</label>
                                            <select class="form-control" name="dosen_id" id="dosen_id_ulang">
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
                                    <button type="button" class="btn btn-secondary" onclick="lockReviewForm()">
                                        <i class="fa fa-times"></i> Batal
                                    </button>
                                    <button type="submit" name="aksi" value="tolak" class="btn btn-danger" onclick="return confirm('Yakin ingin menolak proposal ini lagi?')">
                                        <i class="fa fa-times"></i> Tolak Proposal
                                    </button>
                                    <button type="submit" name="aksi" value="setujui" class="btn btn-success" onclick="return validateApprovalUlang()">
                                        <i class="fa fa-check"></i> Setujui & Tetapkan Pembimbing
                                    </button>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>
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
        
        function validateApprovalUlang() {
            var dosenId = $('#dosen_id_ulang').val();
            if (!dosenId) {
                alert('Silakan pilih dosen pembimbing terlebih dahulu!');
                return false;
            }
            return confirm('Yakin ingin menyetujui proposal ini dan menetapkan pembimbing?');
        }
        
        function unlockReviewForm() {
            if (confirm('Yakin ingin melakukan review ulang untuk proposal ini?')) {
                $('#locked-form').hide();
                $('#unlock-form').show();
            }
        }
        
        function lockReviewForm() {
            $('#unlock-form').hide();
            $('#locked-form').show();
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
        
        if ($aksi == 'tolak') {
            $data_update = [
                'status_kaprodi' => '2',
                'komentar_kaprodi' => $komentar,
                'tanggal_review_kaprodi' => date('Y-m-d H:i:s'),
                'workflow_status' => 'proposal_ditolak' // Status baru
            ];
            
            // Tambahkan ke proposal_workflow untuk tracking
            $this->db->insert('proposal_workflow', [
                'proposal_id' => $proposal_id,
                'tahap' => 'review_kaprodi',
                'status' => 'rejected',
                'komentar' => $komentar,
                'diproses_oleh' => $this->session->userdata('id'),
                'tanggal_proses' => date('Y-m-d H:i:s')
            ]);
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

/**
 * METHOD YANG DIPERBAIKI: _kirim_notifikasi_pembimbing()
 * Ganti method yang ada di file Kaprodi.php dengan method ini
 */
private function _kirim_notifikasi_pembimbing($proposal_id, $dosen_id) {
    // Ambil data proposal dan mahasiswa (hanya field yang sudah ada)
    $data = $this->db->select('
            proposal_mahasiswa.*, 
            mahasiswa.nama as nama_mahasiswa, 
            mahasiswa.nim, 
            mahasiswa.email as email_mahasiswa, 
            prodi.nama as nama_prodi
        ')
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
        
        $message = $this->_get_formal_email_template($data, $dosen);
        
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
        $this->email->to($dosen->email);
        $this->email->subject($subject);
        $this->email->message($message);
        
        return $this->email->send();
    }
    
    return false;
}

    /**
     * METHOD BARU: Template email formal yang profesional
     */
    private function _get_formal_email_template($data, $dosen) {
        // Dapatkan nama kaprodi yang menunjuk
        $kaprodi = $this->db->get_where('dosen', ['id' => $this->session->userdata('id')])->row();
        $nama_kaprodi = $kaprodi ? $kaprodi->nama : 'Kaprodi';
        
        $message = "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Penunjukan Dosen Pembimbing</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7fa; }
                .container { max-width: 650px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
                .header p { margin: 10px 0 0 0; font-size: 14px; opacity: 0.9; }
                .content { padding: 40px 30px; }
                .greeting { font-size: 16px; margin-bottom: 20px; color: #333; }
                .intro { font-size: 16px; line-height: 1.6; color: #555; margin-bottom: 25px; }
                .info-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0; }
                .info-title { font-size: 18px; font-weight: 600; color: #2d3748; margin: 0 0 15px 0; display: flex; align-items: center; }
                .info-table { width: 100%; border-collapse: collapse; }
                .info-table td { padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
                .info-table td:first-child { font-weight: 600; color: #4a5568; width: 35%; }
                .info-table td:last-child { color: #2d3748; }
                .proposal-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; padding: 20px; margin: 20px 0; }
                .proposal-title { font-size: 16px; font-weight: 600; margin: 0 0 10px 0; }
                .proposal-text { font-size: 14px; line-height: 1.5; margin: 0; }
                .action-section { background-color: #fff8dc; border: 1px solid #f0e68c; border-radius: 8px; padding: 20px; margin: 25px 0; text-align: center; }
                .action-title { font-size: 16px; font-weight: 600; color: #8b4513; margin: 0 0 10px 0; }
                .action-text { font-size: 14px; color: #8b4513; margin: 0 0 20px 0; }
                .btn-container { text-align: center; margin: 30px 0; }
                .btn { display: inline-block; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; margin: 0 5px; transition: all 0.3s ease; }
                .btn-primary { background-color: #4299e1; color: white; }
                .btn-primary:hover { background-color: #3182ce; }
                .footer { background-color: #2d3748; color: #a0aec0; padding: 25px 30px; text-align: center; }
                .footer-logo { font-size: 18px; font-weight: 600; color: #ffffff; margin: 0 0 10px 0; }
                .footer-text { font-size: 12px; margin: 5px 0; }
                .divider { height: 1px; background-color: #e2e8f0; margin: 25px 0; }
                .badge { display: inline-block; padding: 4px 8px; background-color: #4299e1; color: white; border-radius: 4px; font-size: 12px; font-weight: 600; }
            </style>
        </head>
        <body>
            <div class='container'>
                <!-- Header -->
                <div class='header'>
                    <h1>📋 Penunjukan Dosen Pembimbing</h1>
                    <p>Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
                
                <!-- Content -->
                <div class='content'>
                    <!-- Greeting -->
                    <div class='greeting'>
                        Yth. <strong>{$dosen->nama}</strong>,
                    </div>
                    
                    <!-- Introduction -->
                    <div class='intro'>
                        Dengan hormat, melalui email ini kami mengundang Bapak/Ibu untuk menjadi 
                        <strong>Dosen Pembimbing</strong> tugas akhir mahasiswa kami. Penunjukan ini 
                        dilakukan berdasarkan keahlian dan kompetensi Bapak/Ibu yang sesuai dengan 
                        bidang penelitian mahasiswa.
                    </div>
                    
                    <!-- Student Information -->
                    <div class='info-card'>
                        <div class='info-title'>
                            👨‍🎓 Profil Mahasiswa
                        </div>
                        <table class='info-table'>
                            <tr>
                                <td>Nama Lengkap</td>
                                <td><strong>{$data->nama_mahasiswa}</strong></td>
                            </tr>
                            <tr>
                                <td>Nomor Induk Mahasiswa</td>
                                <td><span class='badge'>{$data->nim}</span></td>
                            </tr>
                            <tr>
                                <td>Program Studi</td>
                                <td>{$data->nama_prodi}</td>
                            </tr>
                            <tr>
                                <td>Email Mahasiswa</td>
                                <td><a href='mailto:{$data->email_mahasiswa}' style='color: #4299e1; text-decoration: none;'>{$data->email_mahasiswa}</a></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Proposal Information -->
                    <div class='proposal-card'>
                        <div class='proposal-title'>📚 Judul Penelitian</div>
                        <div class='proposal-text'>{$data->judul}</div>
                    </div>
                    
                    <!-- Abstract/Summary if available -->";
                    
        if (!empty($data->ringkasan)) {
            $ringkasan_preview = strlen($data->ringkasan) > 300 ? substr($data->ringkasan, 0, 300) . '...' : $data->ringkasan;
            $message .= "
                    <div class='info-card'>
                        <div class='info-title'>📋 Ringkasan Penelitian</div>
                        <p style='margin: 0; line-height: 1.6; color: #4a5568;'>{$ringkasan_preview}</p>
                    </div>";
        }
        
        $message .= "
                    <!-- Action Required -->
                    <div class='action-section'>
                        <div class='action-title'>⏳ Tindakan yang Diperlukan</div>
                        <div class='action-text'>
                            Mohon Bapak/Ibu berkenan untuk memberikan <strong>konfirmasi persetujuan atau penolakan</strong> 
                            terhadap penunjukan ini melalui sistem dalam waktu <strong>maksimal 3 (tiga) hari kerja</strong> 
                            setelah email ini diterima.
                        </div>
                    </div>
                    
                    <!-- Call to Action Button -->
                    <div class='btn-container'>
                        <a href='" . base_url('dosen/usulan_proposal') . "' class='btn btn-primary'>
                            🔐 Login ke Sistem &amp; Berikan Respon
                        </a>
                    </div>
                    
                    <div class='divider'></div>
                    
                    <!-- Additional Information -->
                    <div style='background-color: #f7fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #4299e1;'>
                        <h4 style='margin: 0 0 10px 0; color: #2d3748; font-size: 16px;'>📌 Informasi Tambahan</h4>
                        <ul style='margin: 0; padding-left: 20px; color: #4a5568; line-height: 1.6;'>
                            <li>File proposal lengkap dapat diakses melalui sistem</li>
                            <li>Komunikasi dengan mahasiswa dapat dilakukan melalui sistem atau email langsung</li>
                            <li>Jadwal bimbingan dapat diatur secara fleksibel sesuai kesepakatan</li>
                            <li>Dukungan teknis sistem tersedia melalui admin STK</li>
                        </ul>
                    </div>
                    
                    <!-- Closing -->
                    <div style='margin-top: 30px; color: #4a5568; line-height: 1.6;'>
                        <p>Demikian surat penunjukan ini kami sampaikan. Atas perhatian dan kesediaan 
                        Bapak/Ibu untuk membimbing mahasiswa kami, kami mengucapkan terima kasih.</p>
                        
                        <div style='margin-top: 25px;'>
                            <p style='margin: 0;'><strong>Hormat kami,</strong></p>
                            <p style='margin: 5px 0 0 0;'><strong>{$nama_kaprodi}</strong><br>
                            <span style='color: #718096;'>Ketua Program Studi {$data->nama_prodi}</span></p>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class='footer'>
                    <div class='footer-logo'>🎓 STK Santo Yakobus Merauke</div>
                    <div class='footer-text'>Sistem Informasi Manajemen Tugas Akhir</div>
                    <div class='footer-text'>Email ini dikirim secara otomatis oleh sistem</div>
                    <div class='footer-text' style='margin-top: 10px; font-size: 11px;'>
                        Jl. Missi II, Mandala, Merauke, Papua Selatan 99616<br>
                        📞 (0971) 3330264 | 📧 sipd@stkyakobus.ac.id
                    </div>
                </div>
            </div>
        </body>
        </html>";
        
        return $message;
    }

    /**
     * PERBAIKAN METHOD _kirim_notifikasi_mahasiswa()
     * Ganti method yang ada di Kaprodi.php dengan kode ini
     */
    private function _kirim_notifikasi_mahasiswa($proposal_id, $status) {
        // Ambil data proposal dan mahasiswa dengan informasi lengkap
        $data = $this->db->select('
                proposal_mahasiswa.*, 
                mahasiswa.nama as nama_mahasiswa, 
                mahasiswa.nim,
                mahasiswa.email as email_mahasiswa,
                prodi.nama as nama_prodi
            ')
            ->from('proposal_mahasiswa')
            ->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id')
            ->join('prodi', 'mahasiswa.prodi_id = prodi.id')
            ->where('proposal_mahasiswa.id', $proposal_id)
            ->get()->row();
        
        if ($data) {
            // Setup email configuration
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
                $subject = '[SIM Tugas Akhir] Proposal Disetujui - Menunggu Persetujuan Dosen Pembimbing';
                $message = $this->_get_template_proposal_disetujui_mahasiswa($data);
                
            } else {
                $subject = '[SIM Tugas Akhir] Proposal Memerlukan Perbaikan';
                $message = $this->_get_template_proposal_ditolak_mahasiswa($data);
            }
            
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($data->email_mahasiswa);
            $this->email->subject($subject);
            $this->email->message($message);
            
            return $this->email->send();
        }
        
        return false;
    }
    /**
     * Template email formal untuk mahasiswa ketika proposal disetujui kaprodi
     * TAMBAHKAN method ini di class Kaprodi sebelum kurung kurawal penutup
     */
    private function _get_template_proposal_disetujui_mahasiswa($data) {
        $tanggal_review = !empty($data->tanggal_review_kaprodi) ? 
            date('d F Y, H:i', strtotime($data->tanggal_review_kaprodi)) : 
            date('d F Y, H:i');
        
        $judul_singkat = strlen($data->judul) > 80 ? 
            substr($data->judul, 0, 80) . '...' : 
            $data->judul;
            
        $ringkasan_singkat = !empty($data->ringkasan) ? 
            (strlen($data->ringkasan) > 200 ? substr($data->ringkasan, 0, 200) . '...' : $data->ringkasan) :
            'Tidak ada ringkasan.';
        
        $message = "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Proposal Disetujui</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7fa; }
                .container { max-width: 650px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
                .header p { margin: 10px 0 0 0; font-size: 14px; opacity: 0.9; }
                .content { padding: 40px 30px; }
                .greeting { font-size: 16px; margin-bottom: 20px; color: #333; }
                .success-message { background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
                .success-title { font-size: 20px; font-weight: 600; color: #155724; margin: 0 0 10px 0; }
                .success-text { font-size: 16px; color: #155724; margin: 0; }
                .info-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0; }
                .info-title { font-size: 18px; font-weight: 600; color: #2d3748; margin: 0 0 15px 0; display: flex; align-items: center; }
                .info-table { width: 100%; border-collapse: collapse; }
                .info-table td { padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
                .info-table td:first-child { font-weight: 600; color: #4a5568; width: 35%; }
                .info-table td:last-child { color: #2d3748; }
                .next-steps { background-color: #e3f2fd; border: 1px solid #bbdefb; border-radius: 8px; padding: 20px; margin: 25px 0; }
                .next-steps-title { font-size: 16px; font-weight: 600; color: #0d47a1; margin: 0 0 15px 0; }
                .next-steps ul { margin: 0; padding-left: 20px; color: #1565c0; }
                .next-steps li { margin-bottom: 8px; line-height: 1.5; }
                .warning-box { background-color: #fff8e1; border: 1px solid #ffecb3; border-radius: 8px; padding: 20px; margin: 25px 0; }
                .warning-title { font-size: 16px; font-weight: 600; color: #f57f17; margin: 0 0 10px 0; }
                .warning-text { font-size: 14px; color: #f57f17; margin: 0; }
                .btn-container { text-align: center; margin: 30px 0; }
                .btn { display: inline-block; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; margin: 0 5px; transition: all 0.3s ease; }
                .btn-primary { background-color: #007bff; color: white; }
                .btn-success { background-color: #28a745; color: white; }
                .footer { background-color: #2d3748; color: #a0aec0; padding: 25px 30px; text-align: center; }
                .footer-logo { font-size: 18px; font-weight: 600; color: #ffffff; margin: 0 0 10px 0; }
                .footer-text { font-size: 12px; margin: 5px 0; }
                .divider { height: 1px; background-color: #e2e8f0; margin: 25px 0; }
                .badge { display: inline-block; padding: 4px 8px; background-color: #28a745; color: white; border-radius: 4px; font-size: 12px; font-weight: 600; }
            </style>
        </head>
        <body>
            <div class='container'>
                <!-- Header -->
                <div class='header'>
                    <h1>🎉 Proposal Disetujui!</h1>
                    <p>Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
                
                <!-- Content -->
                <div class='content'>
                    <!-- Greeting -->
                    <div class='greeting'>
                        Yth. <strong>{$data->nama_mahasiswa}</strong>,
                    </div>
                    
                    <!-- Success Message -->
                    <div class='success-message'>
                        <div class='success-title'>
                            ✅ Selamat! Proposal Anda Telah Disetujui
                        </div>
                        <div class='success-text'>
                            Proposal skripsi Anda telah mendapatkan persetujuan dari Ketua Program Studi pada tanggal <strong>{$tanggal_review}</strong>
                        </div>
                    </div>
                    
                    <!-- Proposal Information -->
                    <div class='info-card'>
                        <div class='info-title'>
                            📚 Detail Proposal Anda
                        </div>
                        <table class='info-table'>
                            <tr>
                                <td>Nama Mahasiswa</td>
                                <td><strong>{$data->nama_mahasiswa}</strong></td>
                            </tr>
                            <tr>
                                <td>NIM</td>
                                <td><span class='badge'>{$data->nim}</span></td>
                            </tr>
                            <tr>
                                <td>Program Studi</td>
                                <td>{$data->nama_prodi}</td>
                            </tr>
                            <tr>
                                <td>Judul Proposal</td>
                                <td><strong>{$judul_singkat}</strong></td>
                            </tr>
                            <tr>
                                <td>Tanggal Disetujui</td>
                                <td>{$tanggal_review}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Current Status -->
                    <div class='warning-box'>
                        <div class='warning-title'>⏳ Status Saat Ini</div>
                        <div class='warning-text'>
                            <strong>Menunggu Persetujuan Dosen Pembimbing</strong><br>
                            Kaprodi telah menunjuk seorang dosen pembimbing untuk Anda. 
                            Sistem sedang menunggu konfirmasi persetujuan dari dosen tersebut.
                            <br><br>
                            <em>Catatan: Nama dosen pembimbing akan diinformasikan setelah beliau menyetujui penunjukan ini.</em>
                        </div>
                    </div>
                    
                    <!-- Next Steps -->
                    <div class='next-steps'>
                        <div class='next-steps-title'>📋 Langkah Selanjutnya</div>
                        <ul>
                            <li><strong>Pantau Status:</strong> Cek dashboard SIM Tugas Akhir secara berkala untuk update status terbaru</li>
                            <li><strong>Persiapan Berkas:</strong> Siapkan dokumen pendukung yang mungkin diperlukan untuk tahap berikutnya</li>
                            <li><strong>Komunikasi:</strong> Pastikan email dan nomor telepon Anda selalu aktif untuk menerima notifikasi</li>
                            <li><strong>Bersabar:</strong> Proses persetujuan dosen pembimbing biasanya memerlukan waktu 1-3 hari kerja</li>
                        </ul>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class='btn-container'>
                        <a href='" . base_url('mahasiswa/proposal') . "' class='btn btn-primary'>
                            📊 Lihat Status Proposal
                        </a>
                        <a href='" . base_url('mahasiswa/dashboard') . "' class='btn btn-success'>
                            🏠 Dashboard Mahasiswa
                        </a>
                    </div>
                    
                    <div class='divider'></div>
                    
                    <!-- Important Note -->
                    <div style='background-color: #f7fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #4299e1;'>
                        <h4 style='margin: 0 0 10px 0; color: #2d3748; font-size: 16px;'>📌 Informasi Penting</h4>
                        <ul style='margin: 0; padding-left: 20px; color: #4a5568; line-height: 1.6;'>
                            <li>Anda akan menerima notifikasi email lanjutan ketika dosen pembimbing memberikan respon</li>
                            <li>Jika dosen pembimbing menyetujui, Anda dapat segera memulai proses bimbingan</li>
                            <li>Jika terjadi penolakan, Kaprodi akan menunjuk dosen pembimbing pengganti</li>
                            <li>Untuk pertanyaan urgent, Anda dapat menghubungi Kaprodi melalui sistem</li>
                        </ul>
                    </div>
                    
                    <!-- Closing -->
                    <div style='margin-top: 30px; color: #4a5568; line-height: 1.6;'>
                        <p>Terima kasih atas dedikasi Anda dalam proses pengajuan proposal. 
                        Semoga proses selanjutnya berjalan lancar dan sukses!</p>
                        
                        <div style='margin-top: 25px;'>
                            <p style='margin: 0;'><strong>Salam hormat,</strong></p>
                            <p style='margin: 5px 0 0 0;'><strong>Ketua Program Studi {$data->nama_prodi}</strong><br>
                            <span style='color: #718096;'>STK Santo Yakobus Merauke</span></p>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class='footer'>
                    <div class='footer-logo'>🎓 STK Santo Yakobus Merauke</div>
                    <div class='footer-text'>Sistem Informasi Manajemen Tugas Akhir</div>
                    <div class='footer-text'>Email ini dikirim secara otomatis oleh sistem</div>
                    <div class='footer-text' style='margin-top: 10px; font-size: 11px;'>
                        Jl. Missi II, Mandala, Merauke, Papua Selatan 99616<br>
                        📞 (0971) 3330264 | 📧 sipd@stkyakobus.ac.id
                    </div>
                </div>
            </div>
        </body>
        </html>";
        
        return $message;
    }
    /**
     * Template email formal untuk mahasiswa ketika proposal ditolak kaprodi
     * TAMBAHKAN method ini di class Kaprodi sebelum kurung kurawal penutup
     */
    private function _get_template_proposal_ditolak_mahasiswa($data) {
        $tanggal_review = !empty($data->tanggal_review_kaprodi) ? 
            date('d F Y, H:i', strtotime($data->tanggal_review_kaprodi)) : 
            date('d F Y, H:i');
        
        $judul_singkat = strlen($data->judul) > 80 ? 
            substr($data->judul, 0, 80) . '...' : 
            $data->judul;
            
        $komentar_kaprodi = !empty($data->komentar_kaprodi) ? 
            $data->komentar_kaprodi : 
            'Silakan lakukan perbaikan sesuai panduan penulisan proposal yang berlaku.';
        
        $message = "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Proposal Memerlukan Perbaikan</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7fa; }
                .container { max-width: 650px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
                .header p { margin: 10px 0 0 0; font-size: 14px; opacity: 0.9; }
                .content { padding: 40px 30px; }
                .greeting { font-size: 16px; margin-bottom: 20px; color: #333; }
                .review-message { background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
                .review-title { font-size: 20px; font-weight: 600; color: #721c24; margin: 0 0 10px 0; }
                .review-text { font-size: 16px; color: #721c24; margin: 0; }
                .info-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0; }
                .info-title { font-size: 18px; font-weight: 600; color: #2d3748; margin: 0 0 15px 0; display: flex; align-items: center; }
                .info-table { width: 100%; border-collapse: collapse; }
                .info-table td { padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
                .info-table td:first-child { font-weight: 600; color: #4a5568; width: 35%; }
                .info-table td:last-child { color: #2d3748; }
                .comment-box { background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0; }
                .comment-title { font-size: 16px; font-weight: 600; color: #856404; margin: 0 0 15px 0; }
                .comment-text { font-size: 14px; color: #856404; margin: 0; line-height: 1.6; background-color: white; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; }
                .next-steps { background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin: 25px 0; }
                .next-steps-title { font-size: 16px; font-weight: 600; color: #155724; margin: 0 0 15px 0; }
                .next-steps ul { margin: 0; padding-left: 20px; color: #155724; }
                .next-steps li { margin-bottom: 8px; line-height: 1.5; }
                .btn-container { text-align: center; margin: 30px 0; }
                .btn { display: inline-block; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; margin: 0 5px; transition: all 0.3s ease; }
                .btn-primary { background-color: #007bff; color: white; }
                .btn-warning { background-color: #ffc107; color: #212529; }
                .footer { background-color: #2d3748; color: #a0aec0; padding: 25px 30px; text-align: center; }
                .footer-logo { font-size: 18px; font-weight: 600; color: #ffffff; margin: 0 0 10px 0; }
                .footer-text { font-size: 12px; margin: 5px 0; }
                .divider { height: 1px; background-color: #e2e8f0; margin: 25px 0; }
                .badge { display: inline-block; padding: 4px 8px; background-color: #dc3545; color: white; border-radius: 4px; font-size: 12px; font-weight: 600; }
            </style>
        </head>
        <body>
            <div class='container'>
                <!-- Header -->
                <div class='header'>
                    <h1>📝 Proposal Memerlukan Perbaikan</h1>
                    <p>Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
                
                <!-- Content -->
                <div class='content'>
                    <!-- Greeting -->
                    <div class='greeting'>
                        Yth. <strong>{$data->nama_mahasiswa}</strong>,
                    </div>
                    
                    <!-- Review Message -->
                    <div class='review-message'>
                        <div class='review-title'>
                            📋 Hasil Review Proposal
                        </div>
                        <div class='review-text'>
                            Proposal skripsi Anda telah direview pada tanggal <strong>{$tanggal_review}</strong> dan <strong>memerlukan perbaikan</strong> sebelum dapat disetujui.
                        </div>
                    </div>
                    
                    <!-- Proposal Information -->
                    <div class='info-card'>
                        <div class='info-title'>
                            📚 Detail Proposal Anda
                        </div>
                        <table class='info-table'>
                            <tr>
                                <td>Nama Mahasiswa</td>
                                <td><strong>{$data->nama_mahasiswa}</strong></td>
                            </tr>
                            <tr>
                                <td>NIM</td>
                                <td><span class='badge'>{$data->nim}</span></td>
                            </tr>
                            <tr>
                                <td>Program Studi</td>
                                <td>{$data->nama_prodi}</td>
                            </tr>
                            <tr>
                                <td>Judul Proposal</td>
                                <td><strong>{$judul_singkat}</strong></td>
                            </tr>
                            <tr>
                                <td>Tanggal Review</td>
                                <td>{$tanggal_review}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Comments -->
                    <div class='comment-box'>
                        <div class='comment-title'>💬 Catatan dan Saran Perbaikan</div>
                        <div class='comment-text'>
                            {$komentar_kaprodi}
                        </div>
                    </div>
                    
                    <!-- Next Steps -->
                    <div class='next-steps'>
                        <div class='next-steps-title'>🔧 Langkah Perbaikan</div>
                        <ul>
                            <li><strong>Review Catatan:</strong> Baca dengan teliti semua catatan dan saran perbaikan dari reviewer</li>
                            <li><strong>Konsultasi:</strong> Diskusikan dengan dosen wali atau pembimbing akademik jika diperlukan</li>
                            <li><strong>Perbaiki Dokumen:</strong> Lakukan revisi proposal sesuai dengan masukan yang diberikan</li>
                            <li><strong>Periksa Format:</strong> Pastikan format penulisan sesuai dengan panduan yang berlaku</li>
                            <li><strong>Ajukan Ulang:</strong> Submit proposal yang telah diperbaiki melalui sistem</li>
                        </ul>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class='btn-container'>
                        <a href='" . base_url('mahasiswa/proposal') . "' class='btn btn-primary'>
                            📊 Lihat Detail Review
                        </a>
                        <a href='" . base_url('mahasiswa/proposal/add') . "' class='btn btn-warning'>
                            ✏️ Perbaiki & Ajukan Ulang
                        </a>
                    </div>
                    
                    <div class='divider'></div>
                    
                    <!-- Encouragement -->
                    <div style='background-color: #f7fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #4299e1;'>
                        <h4 style='margin: 0 0 10px 0; color: #2d3748; font-size: 16px;'>💪 Jangan Berkecil Hati!</h4>
                        <p style='margin: 0; color: #4a5568; line-height: 1.6;'>
                            Proses review dan perbaikan adalah bagian normal dari pengembangan proposal yang berkualitas. 
                            Catatan yang diberikan bertujuan untuk membantu Anda menghasilkan proposal yang lebih baik 
                            dan sesuai dengan standar akademik yang berlaku.
                        </p>
                    </div>
                    
                    <!-- Closing -->
                    <div style='margin-top: 30px; color: #4a5568; line-height: 1.6;'>
                        <p>Kami percaya Anda dapat melakukan perbaikan dengan baik. 
                        Jangan ragu untuk berkonsultasi jika memerlukan bantuan lebih lanjut.</p>
                        
                        <div style='margin-top: 25px;'>
                            <p style='margin: 0;'><strong>Semangat dan sukses selalu,</strong></p>
                            <p style='margin: 5px 0 0 0;'><strong>Ketua Program Studi {$data->nama_prodi}</strong><br>
                            <span style='color: #718096;'>STK Santo Yakobus Merauke</span></p>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class='footer'>
                    <div class='footer-logo'>🎓 STK Santo Yakobus Merauke</div>
                    <div class='footer-text'>Sistem Informasi Manajemen Tugas Akhir</div>
                    <div class='footer-text'>Email ini dikirim secara otomatis oleh sistem</div>
                    <div class='footer-text' style='margin-top: 10px; font-size: 11px;'>
                        Jl. Missi II, Mandala, Merauke, Papua Selatan 99616<br>
                        📞 (0971) 3330264 | 📧 sipd@stkyakobus.ac.id
                    </div>
                </div>
            </div>
        </body>
        </html>";
        
        return $message;
    }
    
    // ============================================
    // METHOD MAHASISWA() - DISEDERHANAKAN (hanya tampilkan tombol Detail)
    // ============================================
    public function mahasiswa() {
        $data['title'] = 'Daftar Mahasiswa Prodi';
        
        // Ambil data mahasiswa tanpa JOIN proposal (lebih sederhana)
        $this->db->select('mahasiswa.*, prodi.nama as nama_prodi');
        $this->db->from('mahasiswa');
        $this->db->join('prodi', 'mahasiswa.prodi_id = prodi.id');
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        $this->db->order_by('mahasiswa.nim', 'ASC');
        $data['mahasiswa_list'] = $this->db->get()->result();
        
        $this->load->view('template/kaprodi', [
            'title' => $data['title'],
            'content' => $this->_get_mahasiswa_content($data),
            'script' => $this->_get_mahasiswa_script()
        ]);
    }

    // ============================================
    // METHOD _get_mahasiswa_content() - DISEDERHANAKAN
    // ============================================
    private function _get_mahasiswa_content($data) {
        extract($data);
        
        ob_start();
        ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Daftar Mahasiswa Program Studi</h3>
                        <p class="text-sm mb-0">Data mahasiswa program studi - klik Detail untuk melihat biodata lengkap</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush" id="datatable-mahasiswa">
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
                                        <td>
                                            <span class="badge badge-outline-primary"><?= $mhs->nim ?></span>
                                        </td>
                                        <td>
                                            <div class="media align-items-center">
                                                <div class="media-body">
                                                    <span class="name mb-0 text-sm font-weight-bold"><?= $mhs->nama ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= $mhs->email ?></td>
                                        <td>
                                            <?php if($mhs->status == '1'): ?>
                                            <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                            <span class="badge badge-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- Hanya tombol Detail -->
                                            <a href="<?= base_url('kaprodi/detail_mahasiswa/' . $mhs->id) ?>" 
                                               class="btn btn-sm btn-primary" title="Lihat Detail Mahasiswa"
                                               data-toggle="tooltip">
                                                <i class="fa fa-user"></i> Detail
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
        
        <!-- Info Panel -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card bg-gradient-info">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h3 class="text-white mb-0">Informasi</h3>
                                <p class="text-white mt-2 mb-0">
                                    • <strong>Detail:</strong> Lihat biodata lengkap, foto, dan riwayat workflow mahasiswa<br>
                                    • <strong>Manajemen Proposal:</strong> Gunakan menu "Usulan Proposal" untuk review dan kelola proposal<br>
                                    • Data mahasiswa diurutkan berdasarkan NIM secara ascending
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ============================================
    // METHOD BARU UNTUK SCRIPT JAVASCRIPT
    // ============================================
    private function _get_mahasiswa_script() {
        ob_start();
        ?>
        <script>
        $(document).ready(function() {
            // Initialize DataTables jika library tersedia
            if (typeof $.fn.DataTable !== 'undefined') {
                $('#datatable-mahasiswa').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                    },
                    "order": [[ 1, "asc" ]], // Urutkan berdasarkan NIM
                    "pageLength": 25,
                    "responsive": true,
                    "columnDefs": [
                        { "orderable": false, "targets": 6 } // Kolom aksi tidak bisa diurutkan
                    ]
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
        </script>
        <?php
        return ob_get_clean();
    }

    // ============================================
    // METHOD DETAIL MAHASISWA - DIPERBAIKI LENGKAP
    // ============================================
    public function detail_mahasiswa($mahasiswa_id) {
        $data['title'] = 'Detail Mahasiswa';
        
        // Ambil detail mahasiswa lengkap
        $this->db->select('mahasiswa.*, prodi.nama as nama_prodi, fakultas.nama as nama_fakultas');
        $this->db->from('mahasiswa');
        $this->db->join('prodi', 'mahasiswa.prodi_id = prodi.id');
        $this->db->join('fakultas', 'prodi.fakultas_id = fakultas.id', 'left');
        $this->db->where('mahasiswa.id', $mahasiswa_id);
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        
        $data['mahasiswa'] = $this->db->get()->row();
        
        if(!$data['mahasiswa']) {
            $this->session->set_flashdata('error', 'Mahasiswa tidak ditemukan!');
            redirect('kaprodi/mahasiswa');
        }
        
        // Ambil riwayat proposal mahasiswa
        $this->db->select('*');
        $this->db->from('proposal_mahasiswa');
        $this->db->where('mahasiswa_id', $mahasiswa_id);
        $this->db->order_by('created_at', 'DESC');
        $data['proposals'] = $this->db->get()->result();
        
        // Ambil riwayat jurnal bimbingan jika ada
        $this->db->select('jurnal_bimbingan.*, proposal_mahasiswa.judul');
        $this->db->from('jurnal_bimbingan');
        $this->db->join('proposal_mahasiswa', 'jurnal_bimbingan.proposal_id = proposal_mahasiswa.id');
        $this->db->where('proposal_mahasiswa.mahasiswa_id', $mahasiswa_id);
        $this->db->order_by('jurnal_bimbingan.tanggal_bimbingan', 'DESC');
        $this->db->limit(10); // 10 bimbingan terakhir
        $data['bimbingan'] = $this->db->get()->result();
        
        $this->load->view('template/kaprodi', [
            'title' => $data['title'],
            'content' => $this->_get_detail_mahasiswa_content($data),
            'script' => $this->_get_detail_mahasiswa_script()
        ]);
    }
    
    private function _get_detail_mahasiswa_content($data) {
        extract($data);
        
        ob_start();
        ?>
        <div class="row">
            <div class="col-lg-4">
                <!-- Card Foto dan Data Utama -->
                <div class="card">
                    <div class="card-header text-center">
                        <h3 class="mb-0">Profil Mahasiswa</h3>
                    </div>
                    <div class="card-body text-center">
                        <!-- Foto Mahasiswa -->
                        <div class="mb-3">
                            <?php 
                            $foto_path = '';
                            $has_foto = false;
                            
                            if (!empty($mahasiswa->foto)) {
                                // Cek di path lama
                                $path1 = FCPATH . 'cdn/img/mahasiswa/' . $mahasiswa->foto;
                                // Cek di path baru  
                                $path2 = FCPATH . 'cdn/mahasiswa/foto/' . $mahasiswa->foto;
                                
                                if (file_exists($path1)) {
                                    $foto_path = base_url('cdn/img/mahasiswa/' . $mahasiswa->foto);
                                    $has_foto = true;
                                } elseif (file_exists($path2)) {
                                    $foto_path = base_url('cdn/mahasiswa/foto/' . $mahasiswa->foto);
                                    $has_foto = true;
                                }
                            }
                            ?>
                            
                            <?php if($has_foto): ?>
                                <img src="<?= $foto_path ?>" 
                                     alt="Foto <?= $mahasiswa->nama ?>" 
                                     class="img-fluid rounded-circle"
                                     style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #e9ecef;">
                            <?php else: ?>
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center"
                                     style="width: 150px; height: 150px; border: 3px solid #e9ecef;">
                                    <i class="fa fa-user fa-4x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h4 class="mb-1"><?= $mahasiswa->nama ?></h4>
                        <p class="text-muted mb-0"><?= $mahasiswa->nim ?></p>
                        <p class="text-sm text-muted"><?= $mahasiswa->nama_prodi ?></p>
                        
                        <div class="mt-3">
                            <?php if($mahasiswa->status == '1'): ?>
                            <span class="badge badge-success badge-lg">Mahasiswa Aktif</span>
                            <?php else: ?>
                            <span class="badge badge-secondary badge-lg">Tidak Aktif</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Card Kontak -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="mb-0">Informasi Kontak</h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <i class="fa fa-envelope text-primary"></i>
                                    </div>
                                    <div class="col">
                                        <small class="text-muted">Email</small><br>
                                        <span class="font-weight-bold"><?= $mahasiswa->email ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <i class="fa fa-phone text-primary"></i>
                                    </div>
                                    <div class="col">
                                        <small class="text-muted">Nomor Telepon</small><br>
                                        <span class="font-weight-bold"><?= $mahasiswa->nomor_telepon ?? '-' ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <i class="fa fa-map-marker-alt text-primary"></i>
                                    </div>
                                    <div class="col">
                                        <small class="text-muted">Alamat</small><br>
                                        <span class="font-weight-bold"><?= $mahasiswa->alamat ?? '-' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <!-- Card Biodata Lengkap -->
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">Biodata Lengkap</h3>
                            </div>
                            <div class="col text-right">
                                <a href="<?= base_url('kaprodi/mahasiswa') ?>" class="btn btn-secondary btn-sm">
                                    <i class="ni ni-bold-left"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="heading-small text-muted mb-4">Identitas Pribadi</h6>
                                <div class="pl-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Nama Lengkap</label>
                                        <p class="form-control-static font-weight-bold"><?= $mahasiswa->nama ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-control-label">NIM</label>
                                        <p class="form-control-static font-weight-bold"><?= $mahasiswa->nim ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-control-label">Tempat, Tanggal Lahir</label>
                                        <p class="form-control-static">
                                            <?= ($mahasiswa->tempat_lahir ?? '-') ?>
                                            <?php if(!empty($mahasiswa->tanggal_lahir)): ?>
                                            , <?= date('d F Y', strtotime($mahasiswa->tanggal_lahir)) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-control-label">Jenis Kelamin</label>
                                        <p class="form-control-static">
                                            <?php 
                                            switch($mahasiswa->jenis_kelamin) {
                                                case 'L': echo 'Laki-laki'; break;
                                                case 'P': echo 'Perempuan'; break;
                                                default: echo '-';
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="heading-small text-muted mb-4">Informasi Akademik</h6>
                                <div class="pl-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Program Studi</label>
                                        <p class="form-control-static font-weight-bold"><?= $mahasiswa->nama_prodi ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-control-label">Fakultas</label>
                                        <p class="form-control-static"><?= $mahasiswa->nama_fakultas ?? '-' ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-control-label">Tahun Masuk</label>
                                        <p class="form-control-static"><?= $mahasiswa->tahun_masuk ?? '-' ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-control-label">Status Mahasiswa</label>
                                        <p class="form-control-static">
                                            <?php if($mahasiswa->status == '1'): ?>
                                            <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                            <span class="badge badge-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Card Riwayat Proposal -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="mb-0">Riwayat Proposal & Workflow</h3>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($proposals)): ?>
                            <div class="timeline timeline-one-side">
                                <?php foreach($proposals as $prop): ?>
                                <div class="timeline-block">
                                    <span class="timeline-step 
                                        <?php 
                                        switch($prop->workflow_status) {
                                            case 'selesai': echo 'badge-success'; break;
                                            case 'publikasi': case 'seminar_skripsi': echo 'badge-info'; break;
                                            case 'bimbingan': case 'penelitian': echo 'badge-primary'; break;
                                            case 'ditolak': echo 'badge-danger'; break;
                                            default: echo 'badge-warning';
                                        }
                                        ?>">
                                        <i class="fa fa-file-alt"></i>
                                    </span>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="text-sm font-weight-bold mb-1"><?= substr($prop->judul, 0, 80) ?>...</h6>
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($prop->created_at)) ?></small>
                                        </div>
                                        <p class="text-sm text-muted mb-2"><?= substr($prop->ringkasan, 0, 150) ?>...</p>
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <strong>Status Kaprodi:</strong>
                                                <?php 
                                                switch($prop->status_kaprodi) {
                                                    case '0': echo '<span class="badge badge-warning badge-sm">Menunggu Review</span>'; break;
                                                    case '1': echo '<span class="badge badge-success badge-sm">Disetujui</span>'; break;
                                                    case '2': echo '<span class="badge badge-danger badge-sm">Ditolak</span>'; break;
                                                    default: echo '<span class="badge badge-secondary badge-sm">Belum Ditentukan</span>';
                                                }
                                                ?>
                                            </div>
                                            <div>
                                                <strong>Workflow:</strong>
                                                <?php 
                                                switch($prop->workflow_status) {
                                                    case 'proposal': echo '<span class="badge badge-info badge-sm">Tahap Proposal</span>'; break;
                                                    case 'menunggu_pembimbing': echo '<span class="badge badge-warning badge-sm">Menunggu Pembimbing</span>'; break;
                                                    case 'bimbingan': echo '<span class="badge badge-primary badge-sm">Bimbingan</span>'; break;
                                                    case 'seminar_proposal': echo '<span class="badge badge-info badge-sm">Seminar Proposal</span>'; break;
                                                    case 'penelitian': echo '<span class="badge badge-warning badge-sm">Penelitian</span>'; break;
                                                    case 'seminar_skripsi': echo '<span class="badge badge-success badge-sm">Seminar Skripsi</span>'; break;
                                                    case 'publikasi': echo '<span class="badge badge-purple badge-sm">Publikasi</span>'; break;
                                                    case 'selesai': echo '<span class="badge badge-success badge-sm">Selesai</span>'; break;
                                                    case 'ditolak': echo '<span class="badge badge-danger badge-sm">Ditolak</span>'; break;
                                                    default: echo '<span class="badge badge-secondary badge-sm">Belum Ditentukan</span>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <a href="<?= base_url('kaprodi/review_proposal/' . $prop->id) ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-eye"></i> Lihat Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum Ada Proposal</h5>
                                <p class="text-muted">Mahasiswa ini belum pernah mengajukan proposal tugas akhir</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Card Riwayat Bimbingan -->
                <?php if(!empty($bimbingan)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="mb-0">Riwayat Bimbingan Terbaru</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Pertemuan</th>
                                        <th>Materi Bimbingan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($bimbingan as $bim): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($bim->tanggal_bimbingan)) ?></td>
                                        <td><span class="badge badge-outline-primary"><?= $bim->pertemuan_ke ?></span></td>
                                        <td><?= substr($bim->materi_bimbingan, 0, 50) ?>...</td>
                                        <td>
                                            <?php if($bim->status_validasi == '1'): ?>
                                            <span class="badge badge-success badge-sm">Tervalidasi</span>
                                            <?php else: ?>
                                            <span class="badge badge-warning badge-sm">Belum Validasi</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function _get_detail_mahasiswa_script() {
        ob_start();
        ?>
        <script>
        $(document).ready(function() {
            // Enable tooltips
            if (typeof $().tooltip !== 'undefined') {
                $('[data-toggle="tooltip"]').tooltip();
            }
            
            // Timeline animations
            $('.timeline-block').each(function(i) {
                $(this).delay(i * 100).fadeIn('slow');
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    // ============================================
    // UPDATE METHOD dosen() DAN _get_dosen_content() di Kaprodi.php
    // ============================================
    
        public function dosen() {
            $data['title'] = 'Daftar Seluruh Dosen';
            
            // Tampilkan semua dosen dari semua prodi dengan info prodi dan statistik bimbingan/penguji
            $this->db->select("
                dosen.*, 
                prodi.nama as nama_prodi,
                (SELECT COUNT(*) 
                 FROM proposal_mahasiswa pm2 
                 JOIN mahasiswa m2 ON pm2.mahasiswa_id = m2.id 
                 WHERE pm2.dosen_id = dosen.id 
                 AND pm2.status_kaprodi = '1' 
                 AND pm2.status_pembimbing = '1'
                ) as jumlah_bimbingan,
                (SELECT COUNT(*) 
                 FROM proposal_mahasiswa pm3 
                 JOIN mahasiswa m3 ON pm3.mahasiswa_id = m3.id 
                 WHERE (pm3.dosen_penguji_id = dosen.id OR pm3.dosen_penguji2_id = dosen.id)
                 AND pm3.status_seminar_proposal = '1'
                ) as jumlah_penguji
            ");
            $this->db->from('dosen');
            $this->db->join('prodi', 'dosen.prodi_id = prodi.id', 'left');
            $this->db->where('dosen.level', '2');
            $this->db->order_by('dosen.nama', 'ASC');
            $data['dosen_list'] = $this->db->get()->result();
            
            $this->load->view('template/kaprodi', [
                'title' => $data['title'],
                'content' => $this->_get_dosen_content($data),
                'script' => $this->_get_dosen_script()
            ]);
        }
    
        private function _get_dosen_content($data) {
            // Extract data agar tersedia sebagai variabel lokal
            extract($data);
            
            ob_start();
            ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="mb-0">Daftar Seluruh Dosen</h3>
                                    <p class="text-sm mb-0">Dosen dari semua program studi yang dapat ditunjuk sebagai pembimbing dan penguji</p>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-sm btn-outline-primary" onclick="refreshData()">
                                        <i class="fa fa-sync"></i> Refresh Data
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Statistik Cards -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6">
                                    <div class="card border-0 bg-gradient-info text-white">
                                        <div class="card-body text-center">
                                            <div class="icon icon-shape bg-white icon-shape-sm rounded-circle text-info mb-2">
                                                <i class="fa fa-users"></i>
                                            </div>
                                            <h3 class="text-white mb-0"><?= count($dosen_list) ?></h3>
                                            <p class="text-white-50 mb-0">Total Dosen</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card border-0 bg-gradient-success text-white">
                                        <div class="card-body text-center">
                                            <div class="icon icon-shape bg-white icon-shape-sm rounded-circle text-success mb-2">
                                                <i class="fa fa-chalkboard-teacher"></i>
                                            </div>
                                            <h3 class="text-white mb-0">
                                                <?= count(array_filter($dosen_list, function($d) { return $d->jumlah_bimbingan > 0; })) ?>
                                            </h3>
                                            <p class="text-white-50 mb-0">Dosen Pembimbing Aktif</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card border-0 bg-gradient-warning text-white">
                                        <div class="card-body text-center">
                                            <div class="icon icon-shape bg-white icon-shape-sm rounded-circle text-warning mb-2">
                                                <i class="fa fa-gavel"></i>
                                            </div>
                                            <h3 class="text-white mb-0">
                                                <?= count(array_filter($dosen_list, function($d) { return $d->jumlah_penguji > 0; })) ?>
                                            </h3>
                                            <p class="text-white-50 mb-0">Dosen Penguji Aktif</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card border-0 bg-gradient-primary text-white">
                                        <div class="card-body text-center">
                                            <div class="icon icon-shape bg-white icon-shape-sm rounded-circle text-primary mb-2">
                                                <i class="fa fa-chart-bar"></i>
                                            </div>
                                            <h3 class="text-white mb-0">
                                                <?= array_sum(array_column($dosen_list, 'jumlah_bimbingan')) ?>
                                            </h3>
                                            <p class="text-white-50 mb-0">Total Bimbingan</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table align-items-center table-flush" id="datatable-dosen">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>No</th>
                                            <th>NIP</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Program Studi</th>
                                            <th class="text-center">Jumlah Bimbingan</th>
                                            <th class="text-center">Jumlah Penguji</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach($dosen_list as $dsn): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <span class="badge badge-outline-primary"><?= $dsn->nip ?></span>
                                            </td>
                                            <td>
                                                <div class="media align-items-center">
                                                    <div class="media-body">
                                                        <span class="name mb-0 text-sm font-weight-bold"><?= $dsn->nama ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="mailto:<?= $dsn->email ?>" class="text-decoration-none">
                                                    <?= $dsn->email ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="text-sm font-weight-bold">
                                                    <?= $dsn->nama_prodi ?? 'Belum ditentukan' ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if($dsn->jumlah_bimbingan > 0): ?>
                                                    <span class="badge badge-success badge-lg">
                                                        <i class="fa fa-chalkboard-teacher"></i> <?= $dsn->jumlah_bimbingan ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-light badge-lg">
                                                        <i class="fa fa-minus"></i> 0
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if($dsn->jumlah_penguji > 0): ?>
                                                    <span class="badge badge-warning badge-lg">
                                                        <i class="fa fa-gavel"></i> <?= $dsn->jumlah_penguji ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-light badge-lg">
                                                        <i class="fa fa-minus"></i> 0
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
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
            
            <!-- Info Panel -->
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card bg-gradient-info">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="text-white mb-0">
                                        <i class="fa fa-info-circle"></i> Informasi
                                    </h3>
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <p class="text-white mt-2 mb-0">
                                                <strong><i class="fa fa-chalkboard-teacher"></i> Jumlah Bimbingan:</strong><br>
                                                Mahasiswa yang sudah disetujui Kaprodi dan diterima sebagai bimbingan oleh dosen
                                            </p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="text-white mt-2 mb-0">
                                                <strong><i class="fa fa-gavel"></i> Jumlah Penguji:</strong><br>
                                                Mahasiswa yang ditugaskan sebagai penguji dan sudah disetujui untuk seminar proposal
                                            </p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="text-white mt-2 mb-0">
                                                <strong><i class="fa fa-sync"></i> Data Real-time:</strong><br>
                                                Data diambil secara langsung dari database dan selalu ter-update
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
    
        private function _get_dosen_script() {
            ob_start();
            ?>
            <script>
            $(document).ready(function() {
                console.log('=== DEBUG DataTables Export ===');
                console.log('jQuery version:', $.fn.jquery);
                console.log('DataTables available:', typeof $.fn.DataTable !== 'undefined');
                console.log('DataTables.Buttons available:', typeof $.fn.DataTable.Buttons !== 'undefined');
                console.log('JSZip available:', typeof JSZip !== 'undefined');
                console.log('pdfMake available:', typeof pdfMake !== 'undefined');
                console.log('================================');
                
                // Function untuk init DataTable dengan retry
                function initDataTableWithRetry(attempt = 1) {
                    console.log(`Attempt ${attempt} to initialize DataTable...`);
                    
                    try {
                        if (typeof $.fn.DataTable === 'undefined') {
                            console.error('DataTables not loaded');
                            return;
                        }
                        
                        // Check apakah table element exists
                        if ($('#datatable-dosen').length === 0) {
                            console.error('Table element #datatable-dosen not found');
                            return;
                        }
                        
                        // Destroy existing table if any
                        if ($.fn.DataTable.isDataTable('#datatable-dosen')) {
                            $('#datatable-dosen').DataTable().destroy();
                        }
                        
                        // Base configuration
                        let tableConfig = {
                            "language": {
                                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                            },
                            "order": [[ 1, "asc" ]],
                            "pageLength": 25,
                            "responsive": true,
                            "columnDefs": [
                                { "orderable": false, "targets": 7 },
                                { "className": "text-center", "targets": [5, 6, 7] }
                            ]
                        };
                        
                        // Check if Buttons extension is available
                        if (typeof $.fn.DataTable.Buttons !== 'undefined') {
                            console.log('✅ DataTables Buttons extension available');
                            
                            tableConfig.dom = 'Bfrtip';
                            tableConfig.buttons = [];
                            
                            // Add Excel button if JSZip available
                            if (typeof JSZip !== 'undefined') {
                                console.log('✅ JSZip available - adding Excel export');
                                tableConfig.buttons.push({
                                    extend: 'excel',
                                    text: '<i class="fa fa-file-excel"></i> Export Excel',
                                    className: 'btn btn-success btn-sm mr-2',
                                    title: 'Daftar Dosen STK St. Yakobus',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5, 6]
                                    }
                                });
                            } else {
                                console.warn('❌ JSZip not available - Excel export disabled');
                            }
                            
                            // Add PDF button if pdfMake available
                            if (typeof pdfMake !== 'undefined') {
                                console.log('✅ pdfMake available - adding PDF export');
                                tableConfig.buttons.push({
                                    extend: 'pdf',
                                    text: '<i class="fa fa-file-pdf"></i> Export PDF',
                                    className: 'btn btn-danger btn-sm mr-2',
                                    title: 'Daftar Dosen STK St. Yakobus',
                                    orientation: 'landscape',
                                    pageSize: 'A4',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5, 6]
                                    }
                                });
                            } else {
                                console.warn('❌ pdfMake not available - PDF export disabled');
                            }
                            
                            // Print button - should always work
                            console.log('✅ Adding Print button');
                            tableConfig.buttons.push({
                                extend: 'print',
                                text: '<i class="fa fa-print"></i> Print',
                                className: 'btn btn-info btn-sm mr-2',
                                title: 'Daftar Dosen STK St. Yakobus',
                                exportOptions: {
                                    columns: [0, 1, 2, 3, 4, 5, 6]
                                },
                                customize: function(win) {
                                    $(win.document.body)
                                        .css('font-size', '10pt')
                                        .prepend(
                                            '<div style="text-align:center; margin-bottom:20px;">' +
                                            '<h2>Daftar Seluruh Dosen</h2>' +
                                            '<h3>STK Santo Yakobus Merauke</h3>' +
                                            '<p>Tanggal Cetak: ' + new Date().toLocaleDateString('id-ID') + '</p>' +
                                            '</div>'
                                        );
                                    
                                    $(win.document.body).find('table')
                                        .addClass('compact')
                                        .css('font-size', 'inherit');
                                }
                            });
                            
                            console.log('Total buttons configured:', tableConfig.buttons.length);
                        } else {
                            console.warn('❌ DataTables Buttons extension not available');
                            // Add manual export buttons
                            setTimeout(addManualExportButtons, 500);
                        }
                        
                        // Initialize DataTable
                        const table = $('#datatable-dosen').DataTable(tableConfig);
                        console.log('✅ DataTable initialized successfully');
                        
                        // Debug: Log buttons after initialization
                        setTimeout(() => {
                            const buttonsContainer = $('.dt-buttons');
                            console.log('Buttons container found:', buttonsContainer.length > 0);
                            console.log('Number of button elements:', buttonsContainer.find('button, a').length);
                            
                            if (buttonsContainer.length === 0) {
                                console.warn('No buttons container found - adding manual buttons');
                                addManualExportButtons();
                            }
                        }, 1000);
                        
                    } catch (error) {
                        console.error('DataTable initialization failed:', error);
                        
                        // Retry dengan delay jika belum exceed max attempts
                        if (attempt < 3) {
                            console.log(`Retrying in ${attempt * 1000}ms...`);
                            setTimeout(() => initDataTableWithRetry(attempt + 1), attempt * 1000);
                        } else {
                            console.error('Max retry attempts reached - falling back to basic table');
                            addManualExportButtons();
                        }
                    }
                }
                
                // Function untuk add manual export buttons jika DataTables buttons gagal
                function addManualExportButtons() {
                    console.log('Adding manual export buttons...');
                    
                    // Remove existing manual buttons
                    $('#manual-export-buttons').remove();
                    
                    // Create buttons container
                    const buttonsHtml = `
                        <div id="manual-export-buttons" class="mb-3">
                            <button type="button" class="btn btn-success btn-sm mr-2" onclick="manualExportExcel()">
                                <i class="fa fa-file-excel"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-info btn-sm mr-2" onclick="manualPrint()">
                                <i class="fa fa-print"></i> Print
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm mr-2" onclick="refreshData()">
                                <i class="fa fa-sync"></i> Refresh
                            </button>
                        </div>
                    `;
                    
                    // Insert before table
                    $('#datatable-dosen').before(buttonsHtml);
                    console.log('✅ Manual export buttons added');
                }
                
                // Start initialization
                initDataTableWithRetry();
                
                // Highlight rows dengan beban kerja tinggi
                setTimeout(function() {
                    try {
                        $('#datatable-dosen tbody tr').each(function() {
                            const bimbinganText = $(this).find('td:eq(5) .badge').text().trim();
                            const pengujiText = $(this).find('td:eq(6) .badge').text().trim();
                            
                            const bimbingan = parseInt(bimbinganText) || 0;
                            const penguji = parseInt(pengujiText) || 0;
                            const total = bimbingan + penguji;
                            
                            if (total >= 5) {
                                $(this).addClass('table-warning');
                            } else if (total === 0) {
                                $(this).addClass('table-light');
                            }
                        });
                        console.log('✅ Row highlighting applied');
                    } catch (error) {
                        console.error('Row highlighting failed:', error);
                    }
                }, 2000);
            });
        
            // Manual export functions
            function manualExportExcel() {
                console.log('Manual Excel export triggered');
                try {
                    // Get table data
                    const table = $('#datatable-dosen').DataTable();
                    const data = table.data().toArray();
                    
                    // Create CSV content
                    let csvContent = "data:text/csv;charset=utf-8,";
                    csvContent += "No,NIP,Nama,Email,Program Studi,Jumlah Bimbingan,Jumlah Penguji\n";
                    
                    $('#datatable-dosen tbody tr').each(function(index) {
                        const row = [];
                        $(this).find('td').each(function(i) {
                            if (i < 7) { // Exclude status column
                                let text = $(this).text().trim();
                                // Clean badge text
                                if (i === 5 || i === 6) {
                                    const match = text.match(/\d+/);
                                    text = match ? match[0] : '0';
                                }
                                row.push('"' + text.replace(/"/g, '""') + '"');
                            }
                        });
                        csvContent += row.join(',') + '\n';
                    });
                    
                    // Download CSV
                    const encodedUri = encodeURI(csvContent);
                    const link = document.createElement('a');
                    link.setAttribute('href', encodedUri);
                    link.setAttribute('download', 'daftar_dosen_stk_yakobus.csv');
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    console.log('✅ Manual Excel export completed');
                } catch (error) {
                    console.error('Manual Excel export failed:', error);
                    alert('Export Excel gagal. Silakan coba lagi.');
                }
            }
        
            function manualPrint() {
                console.log('Manual print triggered');
                try {
                    // Create print window
                    const printWindow = window.open('', '_blank');
                    
                    // Get table HTML
                    const tableHtml = $('#datatable-dosen')[0].outerHTML;
                    
                    // Create print content
                    const printContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Daftar Dosen STK St. Yakobus</title>
                            <style>
                                body { font-family: Arial, sans-serif; font-size: 12px; }
                                .header { text-align: center; margin-bottom: 20px; }
                                table { width: 100%; border-collapse: collapse; }
                                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                                th { background-color: #f2f2f2; font-weight: bold; }
                                .badge { background: none; color: black; font-weight: normal; }
                                .btn { display: none; }
                                @media print {
                                    .no-print { display: none; }
                                }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h2>Daftar Seluruh Dosen</h2>
                                <h3>STK Santo Yakobus Merauke</h3>
                                <p>Tanggal Cetak: ${new Date().toLocaleDateString('id-ID')}</p>
                            </div>
                            ${tableHtml}
                        </body>
                        </html>
                    `;
                    
                    printWindow.document.write(printContent);
                    printWindow.document.close();
                    
                    // Wait for content to load, then print
                    setTimeout(() => {
                        printWindow.print();
                        printWindow.close();
                    }, 500);
                    
                    console.log('✅ Manual print completed');
                } catch (error) {
                    console.error('Manual print failed:', error);
                    alert('Print gagal. Silakan coba lagi.');
                }
            }
        
            function refreshData() {
                console.log('Refresh data triggered');
                location.reload();
            }
            </script>
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
    // ============================================
    // TAMBAHAN METHOD UNTUK PENETAPAN ULANG PEMBIMBING
    // Tambahkan ini SEBELUM tanda kurung kurawal penutup class Kaprodi
    // ============================================

    /**
     * Method untuk menampilkan form penetapan ulang pembimbing
     * Khusus untuk proposal yang ditolak dosen pembimbing (status_pembimbing = 2)
     */
    public function penetapan_ulang($proposal_id) {
        $data['title'] = 'Penetapan Ulang Pembimbing';
        
        // Ambil detail proposal yang ditolak dosen
        $this->db->select('
            pm.*, 
            m.nim, 
            m.nama as nama_mahasiswa, 
            m.email as email_mahasiswa,
            m.tempat_lahir,
            m.tanggal_lahir,
            m.jenis_kelamin,
            m.alamat,
            m.nomor_telepon,
            p.nama as nama_prodi,
            d_old.nama as nama_pembimbing_lama,
            d_old.email as email_pembimbing_lama
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d_old', 'pm.dosen_id = d_old.id', 'left'); // Dosen pembimbing lama
        $this->db->where('pm.id', $proposal_id);
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('pm.status_kaprodi', '1'); // Harus sudah disetujui kaprodi
        $this->db->where('pm.status_pembimbing', '2'); // Harus ditolak dosen
        
        $data['proposal'] = $this->db->get()->row();
        
        if (!$data['proposal']) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan atau tidak memerlukan penetapan ulang!');
            redirect('kaprodi/proposal#penetapan-ulang');
        }
        
        // Ambil dosen yang bisa menjadi pembimbing (exclude dosen yang menolak sebelumnya)
        $this->db->where('level', '2'); // Dosen biasa
        $this->db->where('id !=', $data['proposal']->dosen_id); // Exclude dosen yang menolak
        $this->db->where('id !=', $this->session->userdata('id')); // Exclude kaprodi yang login
        $this->db->order_by('nama', 'ASC');
        $data['dosens'] = $this->db->get('dosen')->result();
        
        $this->load->view('kaprodi/penetapan_ulang', $data);
    }

    /**
     * Method untuk memproses penetapan ulang pembimbing
     */
    public function simpan_penetapan_ulang() {
        $proposal_id = $this->input->post('proposal_id');
        $dosen_pembimbing_baru_id = $this->input->post('dosen_pembimbing_baru_id');
        $dosen_penguji1_id = $this->input->post('dosen_penguji1_id');
        $dosen_penguji2_id = $this->input->post('dosen_penguji2_id');
        $alasan_penetapan_ulang = $this->input->post('alasan_penetapan_ulang');
        
        // Validasi input
        if (!$proposal_id || !$dosen_pembimbing_baru_id) {
            $this->session->set_flashdata('error', 'Dosen pembimbing baru harus dipilih!');
            redirect('kaprodi/proposal#penetapan-ulang');
            return;
        }
        
        // Ambil data proposal lengkap dengan data mahasiswa, dosen lama, dll
        $proposal = $this->db->select('
                pm.*, 
                m.nama as nama_mahasiswa, 
                m.nim,
                m.email as email_mahasiswa, 
                p.nama as nama_prodi,
                d_lama.nama as nama_pembimbing_lama,
                d_lama.email as email_pembimbing_lama
            ')
            ->from('proposal_mahasiswa pm')
            ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
            ->join('prodi p', 'm.prodi_id = p.id')
            ->join('dosen d_lama', 'pm.dosen_id = d_lama.id', 'left')
            ->where('pm.id', $proposal_id)
            ->where('m.prodi_id', $this->prodi_id)
            ->where('pm.status_kaprodi', '1')
            ->where('pm.status_pembimbing', '2')
            ->get()->row();
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan atau tidak memerlukan penetapan ulang!');
            redirect('kaprodi/proposal#penetapan-ulang');
            return;
        }
        
        // Ambil data dosen pembimbing baru
        $dosen_baru = $this->db->get_where('dosen', ['id' => $dosen_pembimbing_baru_id])->row();
        if (!$dosen_baru) {
            $this->session->set_flashdata('error', 'Data dosen pembimbing baru tidak ditemukan!');
            redirect('kaprodi/proposal#penetapan-ulang');
            return;
        }
        
        // Simpan data penetapan ulang
        $update_data = [
            'dosen_id' => $dosen_pembimbing_baru_id,
            'status_pembimbing' => '0', // Reset ke menunggu respon dosen baru
            'komentar_pembimbing' => null, // Reset komentar penolakan
            'tanggal_respon_pembimbing' => null, // Reset tanggal respon
            'tanggal_penetapan_ulang' => date('Y-m-d H:i:s'),
            'penetapan_ulang_oleh' => $this->session->userdata('id'),
            'alasan_penetapan_ulang' => $alasan_penetapan_ulang,
            'workflow_status' => 'menunggu_pembimbing' // Set ulang ke menunggu pembimbing
        ];
        
        // Update penguji jika ada perubahan
        if ($dosen_penguji1_id) {
            $update_data['dosen_penguji_id'] = $dosen_penguji1_id;
        }
        if ($dosen_penguji2_id) {
            $update_data['dosen_penguji2_id'] = $dosen_penguji2_id;
        }
        
        // Start transaction untuk memastikan konsistensi data
        $this->db->trans_begin();
        
        try {
            // Update proposal
            $this->db->where('id', $proposal_id);
            $update_result = $this->db->update('proposal_mahasiswa', $update_data);
            
            if (!$update_result) {
                throw new Exception('Gagal mengupdate data proposal');
            }
            
            // Insert log penetapan ulang
            $this->_insert_log_penetapan_ulang($proposal_id, $proposal->dosen_id, $dosen_pembimbing_baru_id, $alasan_penetapan_ulang);
            
            // Commit transaction
            $this->db->trans_commit();
            
            // KIRIM NOTIFIKASI EMAIL DENGAN ERROR HANDLING
            $this->_kirim_notifikasi_penetapan_ulang_safe($proposal, $dosen_baru, $alasan_penetapan_ulang);
            
            $this->session->set_flashdata('success', 'Penetapan ulang pembimbing berhasil disimpan dan notifikasi telah dikirim!');
            
        } catch (Exception $e) {
            // Rollback transaction jika ada error
            $this->db->trans_rollback();
            
            log_message('error', 'Error simpan_penetapan_ulang: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Gagal menyimpan penetapan ulang: ' . $e->getMessage());
        }
        
        redirect('kaprodi/proposal#penetapan-ulang');
    }
    
    /**
     * Method untuk kirim notifikasi penetapan ulang dengan error handling yang aman
     */
    private function _kirim_notifikasi_penetapan_ulang_safe($proposal, $dosen_baru, $alasan) {
        try {
            // Setup email config
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
            
            // 1. Kirim notifikasi ke dosen pembimbing baru
            $this->_kirim_email_dosen_pembimbing_baru_safe($proposal, $dosen_baru, $alasan);
            
            // 2. Kirim notifikasi ke mahasiswa
            $this->_kirim_email_mahasiswa_penetapan_ulang_safe($proposal, $dosen_baru, $alasan);
            
            log_message('info', 'Notifikasi penetapan ulang berhasil dikirim untuk proposal ID: ' . $proposal->id);
            
        } catch (Exception $e) {
            // Log error tapi jangan stop proses
            log_message('error', 'Error kirim notifikasi penetapan ulang: ' . $e->getMessage());
            
            // Set flash message info bahwa email gagal tapi data tetap tersimpan
            $this->session->set_flashdata('info', 'Data berhasil disimpan, namun notifikasi email gagal dikirim. Silakan informasikan secara manual.');
        }
    }
    
    /**
     * Kirim email ke dosen pembimbing baru dengan error handling
     */
    private function _kirim_email_dosen_pembimbing_baru_safe($proposal, $dosen_baru, $alasan) {
        try {
            $subject = 'Penunjukan sebagai Dosen Pembimbing (Penetapan Ulang) - ' . $proposal->nama_mahasiswa;
            
            $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Penunjukan Pembimbing - Penetapan Ulang</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    
                    <!-- Header -->
                    <div style='text-align: center; background-color: #f39c12; color: white; padding: 20px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px;'>
                        <h2 style='margin: 0;'>🔄 Penunjukan Pembimbing (Penetapan Ulang)</h2>
                    </div>
                    
                    <p style='margin: 0 0 20px 0; font-size: 16px;'>
                        Yth. <strong>{$dosen_baru->nama}</strong>,
                    </p>
                    
                    <!-- Informasi Penetapan Ulang -->
                    <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0; font-size: 16px;'>
                            ℹ️ PENETAPAN ULANG PEMBIMBING
                        </h4>
                        <p style='margin: 0; color: #856404;'>
                            Dosen pembimbing sebelumnya (<strong>{$proposal->nama_pembimbing_lama}</strong>) telah menolak penunjukan untuk mahasiswa ini.
                        </p>
                    </div>
                    
                    <p style='margin: 0 0 20px 0; font-size: 16px;'>
                        Anda telah ditunjuk sebagai <strong>Dosen Pembimbing</strong> untuk mahasiswa:
                    </p>
                    
                    <!-- Data Mahasiswa -->
                    <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3 style='color: #495057; margin: 0 0 15px 0; font-size: 18px;'>📚 Data Mahasiswa:</h3>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; width: 30%;'>Nama:</td>
                                <td style='padding: 8px 0;'>{$proposal->nama_mahasiswa}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>NIM:</td>
                                <td style='padding: 8px 0;'>{$proposal->nim}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Program Studi:</td>
                                <td style='padding: 8px 0;'>{$proposal->nama_prodi}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Email:</td>
                                <td style='padding: 8px 0;'>{$proposal->email_mahasiswa}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Judul Proposal -->
                    <div style='background-color: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h4 style='color: #0056b3; margin: 0 0 10px 0; font-size: 16px;'>📖 Judul Proposal:</h4>
                        <p style='margin: 0; color: #0056b3; font-weight: bold;'>{$proposal->judul}</p>
                    </div>
                    
                    <!-- Alasan Penetapan Ulang -->
                    <div style='background-color: #f0f0f0; border: 1px solid #d0d0d0; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h4 style='color: #666; margin: 0 0 10px 0; font-size: 16px;'>📝 Alasan Penetapan Ulang:</h4>
                        <p style='margin: 0; color: #666;'>{$alasan}</p>
                    </div>
                    
                    <!-- Call to Action -->
                    <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0; font-size: 16px;'>⏳ Perlu Tindakan:</h4>
                        <p style='margin: 0; color: #856404;'>
                            Silakan login ke sistem untuk memberikan <strong>persetujuan atau penolakan</strong> 
                            terhadap penunjukan ini dalam waktu <strong>3 hari kerja</strong>.
                        </p>
                    </div>
                    
                    <!-- Buttons -->
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . base_url('dosen/usulan_proposal') . "' 
                           style='background-color: #28a745; color: white; padding: 12px 30px; 
                                  text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;'>
                            ✅ Login & Respon
                        </a>
                    </div>
                    
                    <!-- Footer -->
                    <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6; margin: 20px -20px -20px -20px; border-radius: 0 0 8px 8px;'>
                        <p style='margin: 0; font-size: 12px; color: #6c757d;'>
                            Email ini dikirim secara otomatis oleh<br>
                            <strong>Sistem Informasi Manajemen Tugas Akhir</strong><br>
                            STK Santo Yakobus Merauke
                        </p>
                    </div>
                </div>
            </body>
            </html>";
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($dosen_baru->email);
            $this->email->subject($subject);
            $this->email->message($message);
            
            if (!$this->email->send()) {
                throw new Exception('Gagal mengirim email ke dosen pembimbing baru: ' . $this->email->print_debugger());
            }
            
            log_message('info', 'Email berhasil dikirim ke dosen pembimbing baru: ' . $dosen_baru->email);
            
        } catch (Exception $e) {
            log_message('error', 'Error kirim email dosen pembimbing baru: ' . $e->getMessage());
            throw $e; // Re-throw untuk ditangani di level atas
        }
    }
    
    /**
     * Kirim email ke mahasiswa tentang penetapan ulang
     */
    private function _kirim_email_mahasiswa_penetapan_ulang_safe($proposal, $dosen_baru, $alasan) {
        try {
            $subject = 'Penetapan Ulang Dosen Pembimbing - ' . $proposal->nama_mahasiswa;
            
            $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Penetapan Ulang Dosen Pembimbing</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    
                    <!-- Header -->
                    <div style='text-align: center; background-color: #17a2b8; color: white; padding: 20px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px;'>
                        <h2 style='margin: 0;'>🔄 Penetapan Ulang Dosen Pembimbing</h2>
                    </div>
                    
                    <p style='margin: 0 0 20px 0; font-size: 16px;'>
                        Yth. <strong>{$proposal->nama_mahasiswa}</strong>,
                    </p>
                    
                    <p style='margin: 0 0 20px 0; font-size: 16px;'>
                        Terkait proposal Anda dengan judul <strong>{$proposal->judul}</strong>, terdapat perubahan dosen pembimbing:
                    </p>
                    
                    <!-- Info Dosen Lama -->
                    <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h4 style='color: #721c24; margin: 0 0 10px 0; font-size: 16px;'>
                            ❌ Dosen Pembimbing Sebelumnya:
                        </h4>
                        <p style='margin: 0; color: #721c24;'>
                            <strong>{$proposal->nama_pembimbing_lama}</strong><br>
                            <small>Menolak penunjukan sebagai pembimbing</small>
                        </p>
                    </div>
                    
                    <!-- Info Dosen Baru -->
                    <div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h4 style='color: #155724; margin: 0 0 10px 0; font-size: 16px;'>
                            👨‍🏫 Dosen Pembimbing Baru:
                        </h4>
                        <p style='margin: 0; color: #155724;'>
                            <strong>{$dosen_baru->nama}</strong><br>
                            <small>Menunggu konfirmasi persetujuan</small>
                        </p>
                    </div>
                    
                    <!-- Alasan -->
                    <div style='background-color: #f0f0f0; border: 1px solid #d0d0d0; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h4 style='color: #666; margin: 0 0 10px 0; font-size: 16px;'>📝 Alasan Penetapan Ulang:</h4>
                        <p style='margin: 0; color: #666;'>{$alasan}</p>
                    </div>
                    
                    <!-- Informasi Selanjutnya -->
                    <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0; font-size: 16px;'>ℹ️ Informasi:</h4>
                        <p style='margin: 0; color: #856404;'>
                            Mohon bersabar menunggu konfirmasi dari dosen pembimbing baru. 
                            Anda akan mendapat notifikasi lanjutan setelah dosen memberikan respon.
                        </p>
                    </div>
                    
                    <!-- Button -->
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . base_url('mahasiswa/proposal') . "' 
                           style='background-color: #007bff; color: white; padding: 12px 25px; 
                                  text-decoration: none; border-radius: 5px; display: inline-block;'>
                           📋 Cek Status Proposal
                        </a>
                    </div>
                    
                    <!-- Footer -->
                    <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6; margin: 20px -20px -20px -20px; border-radius: 0 0 8px 8px;'>
                        <p style='margin: 0; font-size: 12px; color: #6c757d;'>
                            Email ini dikirim secara otomatis oleh<br>
                            <strong>Sistem Informasi Manajemen Tugas Akhir</strong><br>
                            STK Santo Yakobus Merauke
                        </p>
                        <p style='margin: 10px 0 0 0; font-size: 12px; color: #6c757d;'>
                            Terima kasih atas pengertiannya.<br><strong>Kaprodi</strong>
                        </p>
                    </div>
                </div>
            </body>
            </html>";
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($proposal->email_mahasiswa);
            $this->email->subject($subject);
            $this->email->message($message);
            
            if (!$this->email->send()) {
                throw new Exception('Gagal mengirim email ke mahasiswa: ' . $this->email->print_debugger());
            }
            
            log_message('info', 'Email berhasil dikirim ke mahasiswa: ' . $proposal->email_mahasiswa);
            
        } catch (Exception $e) {
            log_message('error', 'Error kirim email mahasiswa: ' . $e->getMessage());
            throw $e; // Re-throw untuk ditangani di level atas
        }
    }
    
    /**
     * Method untuk insert log penetapan ulang - DIPERBAIKI
     */
    private function _insert_log_penetapan_ulang($proposal_id, $dosen_lama_id, $dosen_baru_id, $alasan) {
        try {
            // Cek apakah tabel proposal_workflow exists
            if (!$this->db->table_exists('proposal_workflow')) {
                log_message('info', 'Tabel proposal_workflow tidak ada, skip insert log');
                return true;
            }
            
            $log_data = [
                'proposal_id' => $proposal_id,
                'tahap' => 'penetapan_ulang',
                'status' => 'approved',
                'komentar' => "Penetapan ulang pembimbing dari dosen ID {$dosen_lama_id} ke dosen ID {$dosen_baru_id}. Alasan: {$alasan}",
                'diproses_oleh' => $this->session->userdata('id'),
                'tanggal_proses' => date('Y-m-d H:i:s')
            ];
            
            // Cek field yang ada di tabel
            $fields = $this->db->list_fields('proposal_workflow');
            $insert_data = [];
            
            foreach ($log_data as $key => $value) {
                if (in_array($key, $fields)) {
                    $insert_data[$key] = $value;
                }
            }
            
            if (!empty($insert_data)) {
                $this->db->insert('proposal_workflow', $insert_data);
                log_message('info', 'Log penetapan ulang berhasil disimpan untuk proposal ID: ' . $proposal_id);
            }
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Error insert log penetapan ulang: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Method untuk get data proposal yang ditolak dosen (untuk AJAX jika diperlukan)
     */
    public function get_proposals_ditolak_dosen() {
        $this->db->select('
            pm.*, 
            m.nim, 
            m.nama as nama_mahasiswa, 
            m.email as email_mahasiswa,
            d.nama as nama_pembimbing_lama
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('pm.status_kaprodi', '1'); // Sudah disetujui kaprodi
        $this->db->where('pm.status_pembimbing', '2'); // Ditolak dosen
        $this->db->order_by('pm.tanggal_respon_pembimbing', 'DESC');
        
        $proposals = $this->db->get()->result();
        
        header('Content-Type: application/json');
        echo json_encode($proposals);
    }
}