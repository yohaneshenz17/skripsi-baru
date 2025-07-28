<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bimbingan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Cek login dan level dosen
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
    }

    public function index() {
        $data['title'] = 'Bimbingan Mahasiswa';
        $dosen_id = $this->session->userdata('id');
        
        // Ambil mahasiswa dengan statistik jurnal bimbingan - IMPROVED QUERY
        $this->db->select('
            pm.id as proposal_id,
            pm.judul,
            pm.jenis_penelitian,
            pm.lokasi_penelitian,
            pm.workflow_status,
            pm.created_at as tanggal_proposal,
            pm.tanggal_penetapan,
            m.id as mahasiswa_id,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            p.nama as nama_prodi,
            COUNT(jb.id) as total_bimbingan,
            SUM(CASE WHEN jb.status_validasi = "1" THEN 1 ELSE 0 END) as jurnal_tervalidasi,
            SUM(CASE WHEN jb.status_validasi = "0" THEN 1 ELSE 0 END) as jurnal_pending,
            SUM(CASE WHEN jb.status_validasi = "2" THEN 1 ELSE 0 END) as jurnal_revisi,
            MAX(jb.tanggal_bimbingan) as bimbingan_terakhir,
            MAX(jb.created_at) as jurnal_terakhir_dibuat
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('jurnal_bimbingan jb', 'pm.id = jb.proposal_id', 'left');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('pm.status_kaprodi', '1');
        $this->db->where('pm.status_pembimbing', '1'); 
        $this->db->group_by('pm.id, m.nim, m.nama, m.email, p.nama, pm.judul, pm.jenis_penelitian, pm.lokasi_penelitian, pm.workflow_status, pm.created_at, m.nomor_telepon');
        $this->db->order_by('jurnal_pending', 'DESC');
        $this->db->order_by('jurnal_terakhir_dibuat', 'DESC');
        
        $mahasiswa_list = $this->db->get()->result();
        
        // Ambil jurnal pending untuk overview  
        $this->db->select('
            jb.*,
            pm.judul as judul_proposal,
            m.nim,
            m.nama as nama_mahasiswa
        ');
        $this->db->from('jurnal_bimbingan jb');
        $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('jb.status_validasi', '0');
        $this->db->order_by('jb.created_at', 'DESC');
        $this->db->limit(10);
        
        $data['jurnal_pending_list'] = $this->db->get()->result();
        $data['mahasiswa_bimbingan'] = $mahasiswa_list;
        
        // Statistik untuk dashboard
        $data['total_mahasiswa'] = count($mahasiswa_list);
        $data['total_jurnal_pending'] = 0;
        $data['total_jurnal_tervalidasi'] = 0;
        
        foreach($mahasiswa_list as $mhs) {
            $data['total_jurnal_pending'] += (int)$mhs->jurnal_pending;
            $data['total_jurnal_tervalidasi'] += (int)$mhs->jurnal_tervalidasi;
        }
        
        $this->load->view('dosen/bimbingan', $data);
    }

    public function detail_mahasiswa($proposal_id) {
        $data['title'] = 'Detail Bimbingan Mahasiswa';
        $dosen_id = $this->session->userdata('id');
        
        // Ambil detail mahasiswa dan proposal
        $this->db->select('
            pm.*,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            m.alamat,
            m.foto,
            p.nama as nama_prodi,
            pm.id as proposal_id
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('pm.id', $proposal_id);
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('pm.status_pembimbing', '1');
        
        $mahasiswa = $this->db->get()->row();
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/bimbingan');
            return;
        }
        
        $data['mahasiswa'] = $mahasiswa;
        
        // Ambil jurnal bimbingan
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->order_by('pertemuan_ke', 'ASC');
        
        $jurnal_list = $this->db->get()->result();
        $data['jurnal_bimbingan'] = $jurnal_list;
        
        // Hitung statistik
        $data['total_bimbingan'] = count($jurnal_list);
        $data['bimbingan_tervalidasi'] = count(array_filter($jurnal_list, function($j) { return $j->status_validasi == '1'; }));
        $data['bimbingan_pending'] = count(array_filter($jurnal_list, function($j) { return $j->status_validasi == '0'; }));
        $data['bimbingan_revisi'] = count(array_filter($jurnal_list, function($j) { return $j->status_validasi == '2'; }));
        
        $this->load->view('dosen/bimbingan_detail', $data);
    }

    /**
     * ✅ FIXED: Quick Validasi Jurnal (AJAX) - WITH EMAIL NOTIFICATION
     */
    public function quick_validasi() {
        // Set header JSON
        header('Content-Type: application/json');
        
        // Validasi request method
        if ($this->input->method() !== 'post') {
            echo json_encode(['error' => true, 'message' => 'Method tidak diizinkan']);
            return;
        }
        
        $dosen_id = $this->session->userdata('id');
        $jurnal_id = $this->input->post('jurnal_id');
        $status = $this->input->post('status_validasi'); // 1=validasi, 2=revisi
        $catatan = $this->input->post('catatan_dosen', true); // XSS filtering
        
        try {
            // Validasi input
            if (empty($jurnal_id) || !in_array($status, ['1', '2'])) {
                echo json_encode(['error' => true, 'message' => 'Data tidak valid! Pastikan semua field terisi dengan benar.']);
                return;
            }
            
            // Cek jurnal milik dosen ini dengan query yang lebih aman
            $this->db->select('jb.*, pm.dosen_id, m.nama as nama_mahasiswa, m.nim, m.email as email_mahasiswa, pm.judul');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('jb.id', (int)$jurnal_id);
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('pm.status_pembimbing', '1'); // Pastikan dosen adalah pembimbing aktif
            $jurnal = $this->db->get()->row();
            
            if (!$jurnal) {
                echo json_encode(['error' => true, 'message' => 'Jurnal tidak ditemukan atau Anda tidak memiliki akses!']);
                return;
            }
            
            // Cek apakah jurnal sudah divalidasi sebelumnya
            if ($jurnal->status_validasi == '1') {
                echo json_encode(['error' => true, 'message' => 'Jurnal sudah divalidasi sebelumnya!']);
                return;
            }
            
            // Update status dengan transaction untuk keamanan
            $this->db->trans_start();
            
            $update_data = [
                'status_validasi' => $status,
                'validasi_oleh' => $dosen_id,
                'tanggal_validasi' => date('Y-m-d H:i:s'),
                'catatan_dosen' => $catatan,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $jurnal_id);
            $this->db->update('jurnal_bimbingan', $update_data);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                echo json_encode(['error' => true, 'message' => 'Gagal memperbarui status jurnal! Silakan coba lagi.']);
                return;
            }
            
            // ✅ NEW: Kirim email notifikasi ke mahasiswa
            $this->_send_validation_notification($jurnal, $status, $catatan);
            
            // Response sukses dengan detail
            $status_text = ($status == '1') ? 'divalidasi' : 'dikembalikan untuk revisi';
            $message = "Jurnal bimbingan {$jurnal->nama_mahasiswa} ({$jurnal->nim}) berhasil {$status_text}! Email notifikasi telah dikirim.";
            
            echo json_encode([
                'error' => false, 
                'message' => $message,
                'status' => $status,
                'jurnal_id' => $jurnal_id
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Error in quick_validasi: ' . $e->getMessage());
            echo json_encode(['error' => true, 'message' => 'Terjadi kesalahan sistem! Silakan hubungi administrator.']);
        }
    }

    /**
     * ✅ FIXED: Validasi Jurnal dengan Form - WITH EMAIL NOTIFICATION
     */
    public function validasi_jurnal() {
        header('Content-Type: application/json');
        
        if ($this->input->method() !== 'post') {
            echo json_encode(['error' => true, 'message' => 'Method tidak diizinkan']);
            return;
        }
        
        $dosen_id = $this->session->userdata('id');
        $jurnal_id = $this->input->post('jurnal_id');
        $status = $this->input->post('status');
        $catatan = $this->input->post('catatan_dosen', true);
        
        try {
            // Validasi input yang lebih ketat
            if (empty($jurnal_id) || !is_numeric($jurnal_id) || !in_array($status, ['1', '2'])) {
                echo json_encode(['error' => true, 'message' => 'Data tidak valid! Pastikan semua field terisi dengan benar.']);
                return;
            }
            
            // Jika status revisi, catatan harus ada
            if ($status == '2' && empty(trim($catatan))) {
                echo json_encode(['error' => true, 'message' => 'Catatan dosen wajib diisi untuk jurnal yang perlu revisi!']);
                return;
            }
            
            // Cek jurnal milik dosen ini
            $this->db->select('jb.*, pm.dosen_id, m.nama as nama_mahasiswa, m.nim, m.email as email_mahasiswa, pm.judul');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('jb.id', (int)$jurnal_id);
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('pm.status_pembimbing', '1');
            $jurnal = $this->db->get()->row();
            
            if (!$jurnal) {
                echo json_encode(['error' => true, 'message' => 'Jurnal tidak ditemukan atau Anda tidak memiliki akses!']);
                return;
            }
            
            // Cek apakah jurnal sudah divalidasi
            if ($jurnal->status_validasi == '1') {
                echo json_encode(['error' => true, 'message' => 'Jurnal sudah divalidasi sebelumnya dan tidak dapat diubah!']);
                return;
            }
            
            // Update dengan transaction
            $this->db->trans_start();
            
            $update_data = [
                'status_validasi' => $status,
                'catatan_dosen' => $catatan,
                'validasi_oleh' => $dosen_id,
                'tanggal_validasi' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $jurnal_id);
            $this->db->update('jurnal_bimbingan', $update_data);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                echo json_encode(['error' => true, 'message' => 'Gagal memperbarui jurnal! Silakan coba lagi.']);
                return;
            }
            
            // ✅ NEW: Kirim email notifikasi ke mahasiswa
            $this->_send_validation_notification($jurnal, $status, $catatan);
            
            $status_text = ($status == '1') ? 'divalidasi' : 'dikembalikan untuk revisi';
            $message = "Jurnal bimbingan {$jurnal->nama_mahasiswa} ({$jurnal->nim}) berhasil {$status_text}! Email notifikasi telah dikirim.";
            
            echo json_encode([
                'error' => false, 
                'message' => $message,
                'status' => $status,
                'jurnal_id' => $jurnal_id
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Error in validasi_jurnal: ' . $e->getMessage());
            echo json_encode(['error' => true, 'message' => 'Terjadi kesalahan sistem! Silakan hubungi administrator.']);
        }
    }

    /**
     * ✅ FIXED: Get Jurnal Data (AJAX)
     */
    public function get_jurnal() {
        header('Content-Type: application/json');
        
        if (!$this->input->is_ajax_request()) {
            echo json_encode(['error' => true, 'message' => 'Invalid request']);
            return;
        }
        
        $jurnal_id = $this->input->post('jurnal_id') ?: $this->uri->segment(4);
        $dosen_id = $this->session->userdata('id');
        
        try {
            if (empty($jurnal_id) || !is_numeric($jurnal_id)) {
                echo json_encode(['error' => true, 'message' => 'ID jurnal tidak valid!']);
                return;
            }
            
            // Get jurnal data dengan validasi akses
            $this->db->select('jb.*, pm.dosen_id, m.nama as nama_mahasiswa, m.nim');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('jb.id', (int)$jurnal_id);
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('pm.status_pembimbing', '1');
            $jurnal = $this->db->get()->row();
            
            if (!$jurnal) {
                echo json_encode(['error' => true, 'message' => 'Jurnal tidak ditemukan atau Anda tidak memiliki akses!']);
                return;
            }
            
            echo json_encode([
                'error' => false,
                'data' => $jurnal
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Error in get_jurnal: ' . $e->getMessage());
            echo json_encode(['error' => true, 'message' => 'Terjadi kesalahan saat mengambil data jurnal!']);
        }
    }

    /**
     * ✅ FIXED: Hapus Jurnal Bimbingan - DAPAT HAPUS SEMUA STATUS
     */
    public function delete_jurnal() {
        header('Content-Type: application/json');
        
        if ($this->input->method() !== 'post') {
            echo json_encode(['error' => true, 'message' => 'Method tidak diizinkan']);
            return;
        }
        
        $jurnal_id = $this->input->post('jurnal_id');
        $dosen_id = $this->session->userdata('id');
        
        try {
            if (empty($jurnal_id) || !is_numeric($jurnal_id)) {
                echo json_encode(['error' => true, 'message' => 'ID jurnal tidak valid!']);
                return;
            }
            
            // Validasi jurnal dengan akses yang ketat
            $this->db->select('jb.*, pm.dosen_id, m.nama as nama_mahasiswa, m.nim');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('jb.id', (int)$jurnal_id);
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('pm.status_pembimbing', '1');
            $jurnal = $this->db->get()->row();
            
            if (!$jurnal) {
                echo json_encode(['error' => true, 'message' => 'Jurnal tidak ditemukan atau Anda tidak memiliki akses!']);
                return;
            }
            
            // ✅ PERUBAHAN: Hapus pengecekan status validasi - dosen bisa hapus semua jurnal
            // Dosen pembimbing memiliki hak penuh untuk mengelola jurnal bimbingan
            
            // Hapus dengan transaction
            $this->db->trans_start();
            $this->db->where('id', $jurnal_id);
            $this->db->delete('jurnal_bimbingan');
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                echo json_encode(['error' => true, 'message' => 'Gagal menghapus jurnal! Silakan coba lagi.']);
                return;
            }
            
            $status_desc = '';
            if ($jurnal->status_validasi == '1') {
                $status_desc = ' (sudah divalidasi)';
            } elseif ($jurnal->status_validasi == '2') {
                $status_desc = ' (perlu revisi)';
            } else {
                $status_desc = ' (pending)';
            }
            
            echo json_encode([
                'error' => false, 
                'message' => "Jurnal bimbingan {$jurnal->nama_mahasiswa} ({$jurnal->nim}) pertemuan ke-{$jurnal->pertemuan_ke}{$status_desc} berhasil dihapus!"
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Error in delete_jurnal: ' . $e->getMessage());
            echo json_encode(['error' => true, 'message' => 'Terjadi kesalahan sistem! Silakan hubungi administrator.']);
        }
    }

    /**
     * ✅ FIXED: Tambah Jurnal Bimbingan
     */
    public function tambah_jurnal() {
        header('Content-Type: application/json');
        
        if ($this->input->method() !== 'post') {
            echo json_encode(['error' => true, 'message' => 'Method tidak diizinkan']);
            return;
        }
        
        $dosen_id = $this->session->userdata('id');
        $proposal_id = $this->input->post('proposal_id');
        $pertemuan_ke = $this->input->post('pertemuan_ke');
        $tanggal_bimbingan = $this->input->post('tanggal_bimbingan');
        $materi_bimbingan = $this->input->post('materi_bimbingan', true);
        $tindak_lanjut = $this->input->post('tindak_lanjut', true);
        $catatan_dosen = $this->input->post('catatan_dosen', true);
        
        try {
            // Validasi input wajib
            if (empty($proposal_id) || empty($tanggal_bimbingan) || empty($materi_bimbingan)) {
                echo json_encode(['error' => true, 'message' => 'Data wajib tidak lengkap! Pastikan proposal, tanggal, dan materi bimbingan terisi.']);
                return;
            }
            
            if (empty($pertemuan_ke) || !is_numeric($pertemuan_ke) || $pertemuan_ke < 1) {
                echo json_encode(['error' => true, 'message' => 'Pertemuan ke- harus berupa angka positif!']);
                return;
            }
            
            // Validasi tanggal
            if (!strtotime($tanggal_bimbingan)) {
                echo json_encode(['error' => true, 'message' => 'Format tanggal tidak valid!']);
                return;
            }
            
            // Validasi proposal milik dosen ini
            $this->db->select('pm.*, m.nama as nama_mahasiswa, m.nim');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('pm.id', (int)$proposal_id);
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('pm.status_pembimbing', '1');
            $proposal = $this->db->get()->row();
            
            if (!$proposal) {
                echo json_encode(['error' => true, 'message' => 'Proposal tidak ditemukan atau Anda tidak memiliki akses!']);
                return;
            }
            
            // Cek duplikasi pertemuan
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('pertemuan_ke', $pertemuan_ke);
            $existing = $this->db->get('jurnal_bimbingan')->row();
            
            if ($existing) {
                echo json_encode(['error' => true, 'message' => "Pertemuan ke-{$pertemuan_ke} sudah ada! Silakan gunakan nomor pertemuan yang lain."]);
                return;
            }
            
            // Insert jurnal dengan transaction
            $this->db->trans_start();
            
            $data_jurnal = [
                'proposal_id' => $proposal_id,
                'pertemuan_ke' => $pertemuan_ke,
                'tanggal_bimbingan' => $tanggal_bimbingan,
                'materi_bimbingan' => $materi_bimbingan,
                'tindak_lanjut' => $tindak_lanjut,
                'catatan_dosen' => $catatan_dosen,
                'status_validasi' => '1', // Langsung tervalidasi karena dibuat dosen
                'validasi_oleh' => $dosen_id,
                'tanggal_validasi' => date('Y-m-d H:i:s'),
                'created_by' => 'dosen',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('jurnal_bimbingan', $data_jurnal);
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                echo json_encode(['error' => true, 'message' => 'Gagal menambahkan jurnal bimbingan! Silakan coba lagi.']);
                return;
            }
            
            echo json_encode([
                'error' => false, 
                'message' => "Jurnal bimbingan pertemuan ke-{$pertemuan_ke} untuk {$proposal->nama_mahasiswa} ({$proposal->nim}) berhasil ditambahkan dan langsung tervalidasi!"
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Error in tambah_jurnal: ' . $e->getMessage());
            echo json_encode(['error' => true, 'message' => 'Terjadi kesalahan sistem! Silakan hubungi administrator.']);
        }
    }

    /**
     * ✅ FIXED: Edit Jurnal Bimbingan
     */
    public function edit_jurnal($jurnal_id = null) {
        header('Content-Type: application/json');
        
        $jurnal_id = $jurnal_id ?: $this->input->post('jurnal_id');
        $dosen_id = $this->session->userdata('id');
        
        try {
            if (empty($jurnal_id) || !is_numeric($jurnal_id)) {
                echo json_encode(['error' => true, 'message' => 'ID jurnal tidak valid!']);
                return;
            }
            
            // Get jurnal data dengan validasi akses
            $this->db->select('jb.*, pm.dosen_id, m.nama as nama_mahasiswa, m.nim');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('jb.id', (int)$jurnal_id);
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('pm.status_pembimbing', '1');
            $jurnal = $this->db->get()->row();
            
            if (!$jurnal) {
                echo json_encode(['error' => true, 'message' => 'Jurnal tidak ditemukan atau Anda tidak memiliki akses!']);
                return;
            }
            
            if ($this->input->method() === 'post') {
                // Process update
                $tanggal_bimbingan = $this->input->post('tanggal_bimbingan');
                $materi_bimbingan = $this->input->post('materi_bimbingan', true);
                $tindak_lanjut = $this->input->post('tindak_lanjut', true);
                $catatan_dosen = $this->input->post('catatan_dosen', true);
                
                // Validasi input wajib
                if (empty($tanggal_bimbingan) || empty($materi_bimbingan)) {
                    echo json_encode(['error' => true, 'message' => 'Tanggal dan materi bimbingan wajib diisi!']);
                    return;
                }
                
                // Validasi tanggal
                if (!strtotime($tanggal_bimbingan)) {
                    echo json_encode(['error' => true, 'message' => 'Format tanggal tidak valid!']);
                    return;
                }
                
                $this->db->trans_start();
                
                $update_data = [
                    'tanggal_bimbingan' => $tanggal_bimbingan,
                    'materi_bimbingan' => $materi_bimbingan,
                    'tindak_lanjut' => $tindak_lanjut,
                    'catatan_dosen' => $catatan_dosen,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->where('id', $jurnal_id);
                $this->db->update('jurnal_bimbingan', $update_data);
                
                $this->db->trans_complete();
                
                if ($this->db->trans_status() === FALSE) {
                    echo json_encode(['error' => true, 'message' => 'Gagal memperbarui jurnal bimbingan! Silakan coba lagi.']);
                    return;
                }
                
                echo json_encode([
                    'error' => false, 
                    'message' => "Jurnal bimbingan {$jurnal->nama_mahasiswa} ({$jurnal->nim}) berhasil diperbarui!"
                ]);
            } else {
                // Return jurnal data for editing (GET request)
                echo json_encode(['error' => false, 'data' => $jurnal]);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error in edit_jurnal: ' . $e->getMessage());
            echo json_encode(['error' => true, 'message' => 'Terjadi kesalahan sistem! Silakan hubungi administrator.']);
        }
    }

    /**
     * ✅ IMPROVED: Export Jurnal ke PDF
     */
    public function export_jurnal($proposal_id) {
        $dosen_id = $this->session->userdata('id');
        
        if (!is_numeric($proposal_id)) {
            $this->session->set_flashdata('error', 'ID proposal tidak valid!');
            redirect('dosen/bimbingan');
            return;
        }
        
        try {
            // Query proposal dengan data lengkap
            $proposal = $this->_get_proposal_data($proposal_id);
            if (!$proposal) {
                $this->session->set_flashdata('error', 'Data proposal tidak ditemukan!');
                redirect('dosen/bimbingan');
                return;
            }
            
            // Validasi dosen bimbingan
            if ($proposal->dosen_id != $dosen_id) {
                $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk data ini!');
                redirect('dosen/bimbingan');
                return;
            }
            
            // Jika kaprodi tidak ada dari JOIN, ambil manual
            if (empty($proposal->nama_kaprodi) && !empty($proposal->prodi_id)) {
                $kaprodi_data = $this->_get_kaprodi_by_prodi($proposal->prodi_id);
                if ($kaprodi_data) {
                    $proposal->nama_kaprodi = $kaprodi_data->nama_kaprodi;
                    $proposal->nip_kaprodi = $kaprodi_data->nip_kaprodi;
                    $proposal->email_kaprodi = $kaprodi_data->email_kaprodi;
                }
            }
            
            // Query jurnal dengan validator
            $this->db->select('
                jb.*,
                d.nama as nama_dosen_validator,
                d.nip as nip_dosen_validator
            ');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('dosen d', 'jb.validasi_oleh = d.id', 'left');
            $this->db->where('jb.proposal_id', $proposal_id);
            $this->db->order_by('jb.pertemuan_ke', 'ASC');
            $jurnal_bimbingan = $this->db->get()->result();
            
            if (empty($jurnal_bimbingan)) {
                $this->session->set_flashdata('error', 'Tidak ada data jurnal bimbingan untuk di-export!');
                redirect('dosen/bimbingan/detail_mahasiswa/' . $proposal_id);
                return;
            }
            
            // Prepare data untuk template
            $data = [
                'proposal' => $proposal,
                'jurnal_bimbingan' => $jurnal_bimbingan,
                'generated_by' => $this->session->userdata('nama'),
                'generated_at' => date('d F Y H:i:s')
            ];
            
            // Generate HTML untuk PDF
            $html = $this->load->view('dosen/pdf/jurnal_bimbingan_clean', $data, true);
            $filename = 'Jurnal_Bimbingan_' . str_replace([' ', ',', '.'], '_', $proposal->nama_mahasiswa) . '_' . date('Y-m-d') . '.html';
            
            // Output HTML yang clean untuk browser print
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: inline; filename="' . str_replace('.html', '.pdf', $filename) . '"');
            
            echo $this->_generate_pdf_html($html, $proposal->nama_mahasiswa);
            exit;
            
        } catch (Exception $e) {
            log_message('error', 'Error in dosen export_jurnal: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat export jurnal PDF: ' . $e->getMessage());
            redirect('dosen/bimbingan');
        }
    }

    /**
     * ✅ IMPROVED: Export All Jurnal ke Excel - FORMAT EXCEL YANG RAPI
     */
    public function export_all_excel() {
        $dosen_id = $this->session->userdata('id');
        
        try {
            // Ambil semua data mahasiswa bimbingan dosen ini
            $mahasiswa_data = $this->_get_all_bimbingan_data($dosen_id);
            
            if (empty($mahasiswa_data)) {
                $this->session->set_flashdata('error', 'Tidak ada data mahasiswa bimbingan untuk di-export!');
                redirect('dosen/bimbingan');
                return;
            }
            
            // Coba export Excel format terbaik yang tersedia
            if ($this->_export_xlsx_phpspreadsheet($mahasiswa_data)) {
                return; // Berhasil dengan PhpSpreadsheet
            } elseif ($this->_export_xlsx_simple($mahasiswa_data)) {
                return; // Berhasil dengan Excel XML
            } else {
                // Fallback ke CSV jika semua gagal
                $this->_export_to_csv($mahasiswa_data);
                return;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error in dosen export_all_excel: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat export data Excel: ' . $e->getMessage());
            redirect('dosen/bimbingan');
        }
    }

    // ========================================
    // PRIVATE HELPER METHODS
    // ========================================

    /**
     * ✅ NEW: Send Email Notification untuk Validasi/Revisi Jurnal
     */
    private function _send_validation_notification($jurnal, $status, $catatan) {
        try {
            // Load email library
            $this->load->library('email');
            
            // Konfigurasi email
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
            
            $dosen_nama = $this->session->userdata('nama');
            
            if ($status == '1') {
                // Jurnal divalidasi
                $subject = "✅ Jurnal Bimbingan Divalidasi - Pertemuan ke-{$jurnal->pertemuan_ke}";
                $status_badge = "background-color: #28a745; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;";
                $status_text = "DIVALIDASI";
                $status_desc = "Jurnal bimbingan Anda telah divalidasi dan disetujui oleh dosen pembimbing.";
                $action_text = "Lanjutkan ke pertemuan berikutnya";
                $action_color = "#28a745";
            } else {
                // Jurnal perlu revisi
                $subject = "⚠️ Jurnal Bimbingan Perlu Revisi - Pertemuan ke-{$jurnal->pertemuan_ke}";
                $status_badge = "background-color: #ffc107; color: #212529; padding: 5px 10px; border-radius: 15px; font-size: 12px;";
                $status_text = "PERLU REVISI";
                $status_desc = "Dosen pembimbing meminta revisi pada jurnal bimbingan Anda.";
                $action_text = "Revisi Jurnal Sekarang";
                $action_color = "#ffc107";
            }
            
            $message = "<!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>{$subject}</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
                    <!-- Header -->
                    <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px 20px; text-align: center;'>
                        <h1 style='margin: 0; font-size: 24px; font-weight: bold;'>📚 Notifikasi Jurnal Bimbingan</h1>
                        <p style='margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;'>STK Santo Yakobus Merauke</p>
                    </div>
                    
                    <!-- Content -->
                    <div style='padding: 30px 20px;'>
                        <div style='text-align: center; margin-bottom: 25px;'>
                            <span style='{$status_badge}'>{$status_text}</span>
                        </div>
                        
                        <div style='background-color: #f8f9fa; border-left: 4px solid {$action_color}; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0;'>
                            <h3 style='margin: 0 0 10px 0; color: #2c3e50; font-size: 18px;'>Halo, {$jurnal->nama_mahasiswa}!</h3>
                            <p style='margin: 0; font-size: 14px; color: #555;'>{$status_desc}</p>
                        </div>
                        
                        <div style='background-color: #ffffff; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 20px 0;'>
                            <h4 style='margin: 0 0 15px 0; color: #495057; border-bottom: 2px solid #e9ecef; padding-bottom: 8px;'>📋 Detail Jurnal Bimbingan</h4>
                            <table style='width: 100%; border-collapse: collapse;'>
                                <tr>
                                    <td style='padding: 8px 0; font-weight: bold; width: 30%;'>Pertemuan ke-:</td>
                                    <td style='padding: 8px 0;'>{$jurnal->pertemuan_ke}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; font-weight: bold;'>Tanggal:</td>
                                    <td style='padding: 8px 0;'>" . date('d F Y', strtotime($jurnal->tanggal_bimbingan)) . "</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; font-weight: bold;'>Judul Proposal:</td>
                                    <td style='padding: 8px 0;'>{$jurnal->judul}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; font-weight: bold;'>Dosen Pembimbing:</td>
                                    <td style='padding: 8px 0;'>{$dosen_nama}</td>
                                </tr>";
                                
            if (!empty($catatan)) {
                $message .= "<tr>
                                    <td style='padding: 8px 0; font-weight: bold; vertical-align: top;'>Catatan Dosen:</td>
                                    <td style='padding: 8px 0; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 10px;'>" . nl2br(htmlspecialchars($catatan)) . "</td>
                                </tr>";
            }
            
            $message .= "    </table>
                        </div>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='" . base_url('mahasiswa/bimbingan') . "' 
                               style='background-color: {$action_color}; color: white; padding: 12px 25px; text-decoration: none; border-radius: 25px; display: inline-block; font-weight: bold;'>
                               📝 {$action_text}
                            </a>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6;'>
                        <p style='margin: 0; font-size: 12px; color: #6c757d;'>
                            Email ini dikirim secara otomatis oleh<br>
                            <strong>Sistem Informasi Manajemen Tugas Akhir</strong><br>
                            STK Santo Yakobus Merauke
                        </p>
                        <p style='margin: 10px 0 0 0; font-size: 11px; color: #adb5bd;'>
                            Diterima pada: " . date('d F Y H:i:s') . " WIT
                        </p>
                    </div>
                </div>
            </body>
            </html>";
            
            // Send email
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($jurnal->email_mahasiswa);
            $this->email->subject($subject);
            $this->email->message($message);
            
            if ($this->email->send()) {
                log_message('info', "Email notifikasi berhasil dikirim ke mahasiswa: {$jurnal->email_mahasiswa}");
            } else {
                log_message('error', "Gagal mengirim email notifikasi: " . $this->email->print_debugger());
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error sending validation notification: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NEW: Export Excel XLSX menggunakan PhpSpreadsheet
     */
    private function _export_xlsx_phpspreadsheet($mahasiswa_data) {
        try {
            // Cek apakah PhpSpreadsheet tersedia
            if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                // Coba load manual jika ada di vendor
                if (file_exists(FCPATH . 'vendor/autoload.php')) {
                    require_once FCPATH . 'vendor/autoload.php';
                } else {
                    return false; // Library tidak tersedia
                }
            }
            
            if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                return false; // Library tetap tidak tersedia
            }
            
            // Create new spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Jurnal Bimbingan');
            
            // Set header styling
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ];
            
            // Title
            $sheet->setCellValue('A1', 'JURNAL BIMBINGAN MAHASISWA - ' . strtoupper($this->session->userdata('nama')));
            $sheet->mergeCells('A1:M1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Subtitle
            $sheet->setCellValue('A2', 'STK Santo Yakobus Merauke');
            $sheet->mergeCells('A2:M2');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Generated info
            $sheet->setCellValue('A3', 'Digenerate oleh: ' . $this->session->userdata('nama') . ' | Tanggal: ' . date('d F Y H:i:s'));
            $sheet->mergeCells('A3:M3');
            $sheet->getStyle('A3')->applyFromArray([
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Headers
            $headers = [
                'A5' => 'No',
                'B5' => 'NIM',
                'C5' => 'Nama Mahasiswa',
                'D5' => 'Program Studi',
                'E5' => 'Judul Proposal',
                'F5' => 'Total Bimbingan',
                'G5' => 'Tervalidasi',
                'H5' => 'Pending',
                'I5' => 'Revisi',
                'J5' => 'Progress %',
                'K5' => 'Status Workflow',
                'L5' => 'Tanggal Pengajuan',
                'M5' => 'Email Mahasiswa'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Apply header style
            $sheet->getStyle('A5:M5')->applyFromArray($headerStyle);
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(12);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(35);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('G')->setWidth(12);
            $sheet->getColumnDimension('H')->setWidth(10);
            $sheet->getColumnDimension('I')->setWidth(10);
            $sheet->getColumnDimension('J')->setWidth(12);
            $sheet->getColumnDimension('K')->setWidth(18);
            $sheet->getColumnDimension('L')->setWidth(15);
            $sheet->getColumnDimension('M')->setWidth(25);
            
            // Data rows
            $row = 6;
            foreach ($mahasiswa_data as $index => $mhs) {
                $progress_persen = $mhs->total_bimbingan > 0 ? min(($mhs->total_bimbingan / 16) * 100, 100) : 0;
                
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $mhs->nim);
                $sheet->setCellValue('C' . $row, $mhs->nama_mahasiswa);
                $sheet->setCellValue('D' . $row, $mhs->nama_prodi);
                $sheet->setCellValue('E' . $row, $mhs->judul);
                $sheet->setCellValue('F' . $row, $mhs->total_bimbingan);
                $sheet->setCellValue('G' . $row, $mhs->jurnal_tervalidasi);
                $sheet->setCellValue('H' . $row, $mhs->jurnal_pending);
                $sheet->setCellValue('I' . $row, $mhs->jurnal_revisi);
                $sheet->setCellValue('J' . $row, round($progress_persen, 1) . '%');
                $sheet->setCellValue('K' . $row, ucfirst(str_replace('_', ' ', $mhs->workflow_status)));
                $sheet->setCellValue('L' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($mhs->tanggal_pengajuan)));
                $sheet->setCellValue('M' . $row, $mhs->email_mahasiswa);
                
                // Format date column
                $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                
                $row++;
            }
            
            // Apply borders to all data
            $sheet->getStyle('A5:M' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ]);
            
            // Summary section
            $row += 2;
            $sheet->setCellValue('A' . $row, 'RINGKASAN BIMBINGAN');
            $sheet->mergeCells('A' . $row . ':M' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($headerStyle);
            
            $row++;
            $total_mahasiswa = count($mahasiswa_data);
            $total_jurnal = array_sum(array_column($mahasiswa_data, 'total_bimbingan'));
            $total_tervalidasi = array_sum(array_column($mahasiswa_data, 'jurnal_tervalidasi'));
            $total_pending = array_sum(array_column($mahasiswa_data, 'jurnal_pending'));
            
            $sheet->setCellValue('A' . $row, 'Total Mahasiswa Bimbingan:');
            $sheet->setCellValue('B' . $row, $total_mahasiswa);
            $row++;
            $sheet->setCellValue('A' . $row, 'Total Jurnal Bimbingan:');
            $sheet->setCellValue('B' . $row, $total_jurnal);
            $row++;
            $sheet->setCellValue('A' . $row, 'Total Jurnal Tervalidasi:');
            $sheet->setCellValue('B' . $row, $total_tervalidasi);
            $row++;
            $sheet->setCellValue('A' . $row, 'Total Jurnal Pending:');
            $sheet->setCellValue('B' . $row, $total_pending);
            
            // Set filename and download
            $filename = 'Jurnal_Bimbingan_' . str_replace([' ', '.'], '_', $this->session->userdata('nama')) . '_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            // Headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            
            exit;
            
        } catch (Exception $e) {
            log_message('error', 'Error in dosen PhpSpreadsheet export: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ NEW: Export Excel menggunakan Excel XML format (fallback)
     */
    private function _export_xlsx_simple($mahasiswa_data) {
        try {
            $filename = 'Jurnal_Bimbingan_' . str_replace([' ', '.'], '_', $this->session->userdata('nama')) . '_' . date('Y-m-d_H-i-s') . '.xls';
            
            // Headers for Excel
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Start output
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
            echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
            echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
            
            // Styles
            echo '<Styles>' . "\n";
            echo '<Style ss:ID="HeaderStyle">' . "\n";
            echo '<Font ss:Bold="1" ss:Color="#FFFFFF"/>' . "\n";
            echo '<Interior ss:Color="#4472C4" ss:Pattern="Solid"/>' . "\n";
            echo '<Borders>' . "\n";
            echo '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '</Borders>' . "\n";
            echo '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
            echo '</Style>' . "\n";
            
            echo '<Style ss:ID="DataStyle">' . "\n";
            echo '<Borders>' . "\n";
            echo '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '</Borders>' . "\n";
            echo '</Style>' . "\n";
            echo '</Styles>' . "\n";
            
            // Worksheet
            echo '<Worksheet ss:Name="Jurnal Bimbingan">' . "\n";
            echo '<Table>' . "\n";
            
            // Title row
            echo '<Row>' . "\n";
            echo '<Cell ss:MergeAcross="12" ss:StyleID="HeaderStyle">' . "\n";
            echo '<Data ss:Type="String">JURNAL BIMBINGAN - ' . htmlspecialchars(strtoupper($this->session->userdata('nama'))) . '</Data>' . "\n";
            echo '</Cell>' . "\n";
            echo '</Row>' . "\n";
            
            // Empty row
            echo '<Row></Row>' . "\n";
            
            // Headers
            echo '<Row>' . "\n";
            $headers = ['No', 'NIM', 'Nama Mahasiswa', 'Program Studi', 'Judul Proposal', 
                       'Total Bimbingan', 'Tervalidasi', 'Pending', 'Revisi', 'Progress %',
                       'Status Workflow', 'Tanggal Pengajuan', 'Email Mahasiswa'];
            
            foreach ($headers as $header) {
                echo '<Cell ss:StyleID="HeaderStyle">' . "\n";
                echo '<Data ss:Type="String">' . htmlspecialchars($header) . '</Data>' . "\n";
                echo '</Cell>' . "\n";
            }
            echo '</Row>' . "\n";
            
            // Data rows
            foreach ($mahasiswa_data as $index => $mhs) {
                $progress_persen = $mhs->total_bimbingan > 0 ? min(($mhs->total_bimbingan / 16) * 100, 100) : 0;
                
                echo '<Row>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="Number">' . ($index + 1) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->nim) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->nama_mahasiswa) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->nama_prodi) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->judul) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="Number">' . $mhs->total_bimbingan . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="Number">' . $mhs->jurnal_tervalidasi . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="Number">' . $mhs->jurnal_pending . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="Number">' . $mhs->jurnal_revisi . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . round($progress_persen, 1) . '%</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $mhs->workflow_status))) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . date('d/m/Y', strtotime($mhs->tanggal_pengajuan)) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->email_mahasiswa) . '</Data></Cell>' . "\n";
                echo '</Row>' . "\n";
            }
            
            echo '</Table>' . "\n";
            echo '</Worksheet>' . "\n";
            echo '</Workbook>' . "\n";
            
            exit;
            
        } catch (Exception $e) {
            log_message('error', 'Error in dosen Excel XML export: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate HTML wrapper untuk PDF
     */
    private function _generate_pdf_html($content, $nama_mahasiswa) {
        return '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . htmlspecialchars('Jurnal Bimbingan - ' . $nama_mahasiswa) . '</title>
            <style>
                @media print { 
                    @page { 
                        size: A4 landscape; 
                        margin: 12mm 8mm; 
                    }
                    body { margin: 0; }
                    .no-print { display: none !important; }
                }
                body { 
                    font-family: "Times New Roman", Times, serif; 
                    margin: 0;
                    padding: 10px;
                }
                .print-info {
                    background: #e8f4fd;
                    border: 1px solid #2c5aa0;
                    padding: 10px;
                    margin-bottom: 15px;
                    text-align: center;
                    font-size: 12px;
                    color: #2c5aa0;
                }
                .print-btn {
                    background: #2c5aa0;
                    color: white;
                    border: none;
                    padding: 8px 15px;
                    cursor: pointer;
                    border-radius: 4px;
                    margin: 0 5px;
                    font-size: 11px;
                }
                .print-btn:hover {
                    background: #1e3f73;
                }
            </style>
        </head>
        <body>
            <div class="print-info no-print">
                📄 <strong>Jurnal Bimbingan - ' . htmlspecialchars($nama_mahasiswa) . '</strong><br>
                Klik tombol di bawah untuk mencetak atau simpan sebagai PDF. Pastikan pilih orientasi <strong>Landscape</strong> di pengaturan print.
                <br><br>
                <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
                <button class="print-btn" onclick="window.close()">❌ Tutup</button>
            </div>
            
            ' . $content . '
            
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.addEventListener("keydown", function(event) {
                        if (event.ctrlKey && event.key === "p") {
                            event.preventDefault();
                            window.print();
                        }
                    });
                });
            </script>
        </body>
        </html>';
    }

    /**
     * ✅ Fallback export ke CSV yang kompatibel dengan Excel
     */
    private function _export_to_csv($mahasiswa_data) {
        $filename = 'Jurnal_Bimbingan_' . str_replace([' ', '.'], '_', $this->session->userdata('nama')) . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM untuk Excel
        
        // Header
        fputcsv($output, [
            'No', 'NIM', 'Nama Mahasiswa', 'Program Studi', 'Judul Proposal',
            'Total Bimbingan', 'Tervalidasi', 'Pending', 'Revisi', 'Progress %',
            'Status Workflow', 'Tanggal Pengajuan', 'Email Mahasiswa'
        ]);
        
        // Data rows
        foreach ($mahasiswa_data as $index => $mhs) {
            $progress_persen = $mhs->total_bimbingan > 0 ? min(($mhs->total_bimbingan / 16) * 100, 100) : 0;
            
            fputcsv($output, [
                $index + 1,
                $mhs->nim,
                $mhs->nama_mahasiswa,
                $mhs->nama_prodi,
                $mhs->judul,
                $mhs->total_bimbingan,
                $mhs->jurnal_tervalidasi,
                $mhs->jurnal_pending,
                $mhs->jurnal_revisi,
                round($progress_persen, 1) . '%',
                ucfirst(str_replace('_', ' ', $mhs->workflow_status)),
                date('d/m/Y', strtotime($mhs->tanggal_pengajuan)),
                $mhs->email_mahasiswa
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Get all bimbingan data untuk export
     */
    private function _get_all_bimbingan_data($dosen_id) {
        $this->db->select('
            pm.id as proposal_id,
            pm.judul,
            pm.workflow_status,
            pm.created_at as tanggal_pengajuan,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            p.nama as nama_prodi,
            COUNT(jb.id) as total_bimbingan,
            SUM(CASE WHEN jb.status_validasi = "1" THEN 1 ELSE 0 END) as jurnal_tervalidasi,
            SUM(CASE WHEN jb.status_validasi = "0" THEN 1 ELSE 0 END) as jurnal_pending,
            SUM(CASE WHEN jb.status_validasi = "2" THEN 1 ELSE 0 END) as jurnal_revisi
        ');
        
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'inner');
        $this->db->join('prodi p', 'm.prodi_id = p.id', 'inner');
        $this->db->join('jurnal_bimbingan jb', 'pm.id = jb.proposal_id', 'left');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('pm.status_pembimbing', '1');
        $this->db->group_by('pm.id, m.nim, m.nama, m.email, p.nama, pm.judul, pm.workflow_status, pm.created_at');
        $this->db->order_by('pm.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get proposal data lengkap dengan data kaprodi
     */
    private function _get_proposal_data($proposal_id) {
        try {
            $this->db->select('
                pm.*,
                m.nim,
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                m.prodi_id,
                p.nama as nama_prodi,
                d.nama as nama_pembimbing,
                d.nip as nip_pembimbing,
                d.email as email_pembimbing,
                kaprodi.nama as nama_kaprodi,
                kaprodi.nip as nip_kaprodi,
                kaprodi.email as email_kaprodi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('dosen kaprodi', 'p.dosen_id = kaprodi.id', 'left');
            $this->db->where('pm.id', $proposal_id);
            
            $query = $this->db->get();
            
            if ($query && $query->num_rows() > 0) {
                return $query->row();
            }
            
            return null;
        } catch (Exception $e) {
            log_message('error', 'Error getting proposal data: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get kaprodi data by prodi ID
     */
    private function _get_kaprodi_by_prodi($prodi_id) {
        try {
            $this->db->select('
                d.nama as nama_kaprodi,
                d.nip as nip_kaprodi,
                d.email as email_kaprodi
            ');
            $this->db->from('prodi p');
            $this->db->join('dosen d', 'p.dosen_id = d.id');
            $this->db->where('p.id', $prodi_id);
            $this->db->where('d.level', '4'); // Level 4 = Kaprodi
            
            $query = $this->db->get();
            
            if ($query && $query->num_rows() > 0) {
                return $query->row();
            }
            
            // Fallback: cari kaprodi dari level di tabel dosen
            $this->db->select('
                d.nama as nama_kaprodi,
                d.nip as nip_kaprodi,
                d.email as email_kaprodi
            ');
            $this->db->from('dosen d');
            $this->db->where('d.level', '4');
            $this->db->limit(1);
            
            $query = $this->db->get();
            return $query ? $query->row() : null;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting kaprodi data: ' . $e->getMessage());
            return null;
        }
    }
}