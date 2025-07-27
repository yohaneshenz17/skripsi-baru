<?php
/**
 * Controller Bimbingan Mahasiswa - Versi Sederhana
 * File: application/controllers/mahasiswa/Bimbingan.php
 * Kompatibel dengan template sederhana dan views yang telah dibuat
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Bimbingan extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Load minimal libraries
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
        
        // Simple authentication check
        if (!$this->session->userdata('id') || $this->session->userdata('level') != '3') {
            redirect('auth/login');
        }
        
        // Set error reporting untuk development
        if (ENVIRONMENT === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
    }

    /**
     * Halaman utama bimbingan
     */
    public function index()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get proposal mahasiswa
        $proposal = $this->_get_proposal($mahasiswa_id);
        
        $data = [
            'proposal' => $proposal
        ];
        
        // Jika proposal ada dan disetujui, ambil data bimbingan
        if ($proposal && $proposal->status == '1') {
            $data = array_merge($data, [
                'dosen_pembimbing' => $this->_get_dosen_pembimbing($proposal->dosen_id),
                'jurnal_bimbingan' => $this->_get_jurnal_bimbingan($proposal->id),
                'total_bimbingan' => $this->_count_total_bimbingan($proposal->id),
                'total_bimbingan_valid' => $this->_count_bimbingan_by_status($proposal->id, '1'),
                'total_bimbingan_pending' => $this->_count_bimbingan_by_status($proposal->id, '0'),
                'total_bimbingan_revisi' => $this->_count_bimbingan_by_status($proposal->id, '2'),
            ]);
        }
        
        $this->load->view('mahasiswa/bimbingan', $data);
    }

    /**
     * Tambah jurnal bimbingan
     */
    public function tambah_jurnal()
    {
        // Validasi request method
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('mahasiswa/bimbingan');
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get proposal
        $proposal = $this->_get_proposal($mahasiswa_id);
        if (!$proposal || $proposal->status != '1') {
            $this->session->set_flashdata('error', 'Proposal belum disetujui atau tidak ditemukan');
            redirect('mahasiswa/bimbingan');
        }
        
        // Validasi input
        $pertemuan_ke = (int) $this->input->post('pertemuan_ke');
        $tanggal_bimbingan = $this->input->post('tanggal_bimbingan');
        $materi_bimbingan = trim($this->input->post('materi_bimbingan'));
        $catatan_mahasiswa = trim($this->input->post('catatan_mahasiswa'));
        $tindak_lanjut = trim($this->input->post('tindak_lanjut'));
        
        // Validasi required fields
        if (!$pertemuan_ke || !$tanggal_bimbingan || !$materi_bimbingan) {
            $this->session->set_flashdata('error', 'Mohon lengkapi semua field yang wajib diisi');
            redirect('mahasiswa/bimbingan');
        }
        
        // Validasi tanggal
        if ($tanggal_bimbingan > date('Y-m-d')) {
            $this->session->set_flashdata('error', 'Tanggal bimbingan tidak boleh lebih dari hari ini');
            redirect('mahasiswa/bimbingan');
        }
        
        // Cek apakah jurnal dengan pertemuan_ke sudah ada
        $existing = $this->db->get_where('jurnal_bimbingan', [
            'proposal_id' => $proposal->id,
            'pertemuan_ke' => $pertemuan_ke
        ])->row();
        
        $data_jurnal = [
            'proposal_id' => $proposal->id,
            'pertemuan_ke' => $pertemuan_ke,
            'tanggal_bimbingan' => $tanggal_bimbingan,
            'materi_bimbingan' => $materi_bimbingan,
            'catatan_mahasiswa' => $catatan_mahasiswa,
            'tindak_lanjut' => $tindak_lanjut,
            'status_validasi' => '0',
            'created_by' => 'mahasiswa',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            if ($existing) {
                // Update existing jurnal jika masih pending
                if ($existing->status_validasi == '0') {
                    $this->db->where('id', $existing->id);
                    $this->db->update('jurnal_bimbingan', $data_jurnal);
                    $this->session->set_flashdata('success', 'Jurnal bimbingan pertemuan ke-' . $pertemuan_ke . ' berhasil diperbarui');
                } else {
                    $this->session->set_flashdata('error', 'Jurnal pertemuan ke-' . $pertemuan_ke . ' sudah divalidasi dan tidak dapat diubah');
                }
            } else {
                // Insert new jurnal
                $data_jurnal['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('jurnal_bimbingan', $data_jurnal);
                $this->session->set_flashdata('success', 'Jurnal bimbingan berhasil ditambahkan');
                
                // Optional: Send notification to dosen
                $this->_send_notification_to_dosen($proposal->dosen_id, $mahasiswa_id, $pertemuan_ke);
            }
        } catch (Exception $e) {
            log_message('error', 'Error tambah jurnal bimbingan: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menyimpan jurnal bimbingan');
        }
        
        redirect('mahasiswa/bimbingan');
    }

    /**
     * Edit jurnal bimbingan
     */
    public function edit_jurnal($jurnal_id)
    {
        if (!$jurnal_id) {
            redirect('mahasiswa/bimbingan');
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get jurnal dengan validasi ownership
        $jurnal = $this->_get_jurnal_with_validation($jurnal_id, $mahasiswa_id);
        
        if (!$jurnal) {
            $this->session->set_flashdata('error', 'Jurnal tidak ditemukan atau Anda tidak memiliki akses');
            redirect('mahasiswa/bimbingan');
        }
        
        // Jika POST request (form submitted)
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            return $this->_process_edit_jurnal($jurnal);
        }
        
        // Tampilkan form edit
        $data = [
            'jurnal' => $jurnal
        ];
        
        $this->load->view('mahasiswa/bimbingan_edit', $data);
    }

    /**
     * Hapus jurnal bimbingan
     */
    public function hapus_jurnal($jurnal_id)
    {
        if (!$jurnal_id) {
            redirect('mahasiswa/bimbingan');
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get jurnal dengan validasi
        $jurnal = $this->_get_jurnal_with_validation($jurnal_id, $mahasiswa_id);
        
        if (!$jurnal) {
            $this->session->set_flashdata('error', 'Jurnal tidak ditemukan atau Anda tidak memiliki akses');
            redirect('mahasiswa/bimbingan');
        }
        
        if ($jurnal->status_validasi != '0') {
            $this->session->set_flashdata('error', 'Jurnal yang sudah divalidasi tidak dapat dihapus');
            redirect('mahasiswa/bimbingan');
        }
        
        try {
            $this->db->where('id', $jurnal_id);
            $this->db->delete('jurnal_bimbingan');
            
            $this->session->set_flashdata('success', 'Jurnal bimbingan berhasil dihapus');
        } catch (Exception $e) {
            log_message('error', 'Error hapus jurnal: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menghapus jurnal');
        }
        
        redirect('mahasiswa/bimbingan');
    }

    /**
     * Detail jurnal bimbingan
     */
    public function detail_jurnal($jurnal_id)
    {
        if (!$jurnal_id) {
            redirect('mahasiswa/bimbingan');
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get jurnal dengan validasi
        $jurnal = $this->_get_jurnal_with_validation($jurnal_id, $mahasiswa_id);
        
        if (!$jurnal) {
            $this->session->set_flashdata('error', 'Jurnal tidak ditemukan atau Anda tidak memiliki akses');
            redirect('mahasiswa/bimbingan');
        }
        
        $data = [
            'jurnal' => $jurnal
        ];
        
        $this->load->view('mahasiswa/bimbingan_detail', $data);
    }

    // ========================================
    // PRIVATE HELPER METHODS
    // ========================================

    /**
     * Get proposal mahasiswa
     */
    private function _get_proposal($mahasiswa_id)
    {
        $query = "SELECT pm.*, p.nama as nama_prodi 
                  FROM proposal_mahasiswa pm 
                  JOIN mahasiswa m ON pm.mahasiswa_id = m.id 
                  JOIN prodi p ON m.prodi_id = p.id 
                  WHERE pm.mahasiswa_id = ?
                  ORDER BY pm.id DESC LIMIT 1";
        
        return $this->db->query($query, [$mahasiswa_id])->row();
    }

    /**
     * Get dosen pembimbing
     */
    private function _get_dosen_pembimbing($dosen_id)
    {
        if (!$dosen_id) return null;
        
        return $this->db->select('id, nama, email, nomor_telepon')
                       ->where('id', $dosen_id)
                       ->get('dosen')
                       ->row_array();
    }

    /**
     * Get jurnal bimbingan
     */
    private function _get_jurnal_bimbingan($proposal_id)
    {
        return $this->db->where('proposal_id', $proposal_id)
                       ->order_by('pertemuan_ke', 'ASC')
                       ->get('jurnal_bimbingan')
                       ->result();
    }

    /**
     * Count total bimbingan
     */
    private function _count_total_bimbingan($proposal_id)
    {
        return $this->db->where('proposal_id', $proposal_id)
                       ->count_all_results('jurnal_bimbingan');
    }

    /**
     * Count bimbingan by status
     */
    private function _count_bimbingan_by_status($proposal_id, $status)
    {
        return $this->db->where(['proposal_id' => $proposal_id, 'status_validasi' => $status])
                       ->count_all_results('jurnal_bimbingan');
    }

    /**
     * Get jurnal dengan validasi ownership
     */
    private function _get_jurnal_with_validation($jurnal_id, $mahasiswa_id)
    {
        $query = "SELECT jb.*, pm.mahasiswa_id 
                  FROM jurnal_bimbingan jb 
                  JOIN proposal_mahasiswa pm ON jb.proposal_id = pm.id 
                  WHERE jb.id = ? AND pm.mahasiswa_id = ?";
        
        return $this->db->query($query, [$jurnal_id, $mahasiswa_id])->row();
    }

    /**
     * Process edit jurnal
     */
    private function _process_edit_jurnal($jurnal)
    {
        // Cek apakah jurnal masih bisa diedit
        if ($jurnal->status_validasi != '0') {
            $this->session->set_flashdata('error', 'Jurnal yang sudah divalidasi tidak dapat diedit');
            redirect('mahasiswa/bimbingan');
        }
        
        // Validasi input
        $tanggal_bimbingan = $this->input->post('tanggal_bimbingan');
        $materi_bimbingan = trim($this->input->post('materi_bimbingan'));
        $catatan_mahasiswa = trim($this->input->post('catatan_mahasiswa'));
        $tindak_lanjut = trim($this->input->post('tindak_lanjut'));
        
        if (!$tanggal_bimbingan || !$materi_bimbingan) {
            $this->session->set_flashdata('error', 'Mohon lengkapi semua field yang wajib diisi');
            redirect('mahasiswa/bimbingan/edit_jurnal/' . $jurnal->id);
        }
        
        if ($tanggal_bimbingan > date('Y-m-d')) {
            $this->session->set_flashdata('error', 'Tanggal bimbingan tidak boleh lebih dari hari ini');
            redirect('mahasiswa/bimbingan/edit_jurnal/' . $jurnal->id);
        }
        
        $data_update = [
            'tanggal_bimbingan' => $tanggal_bimbingan,
            'materi_bimbingan' => $materi_bimbingan,
            'catatan_mahasiswa' => $catatan_mahasiswa,
            'tindak_lanjut' => $tindak_lanjut,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->db->where('id', $jurnal->id);
            $this->db->update('jurnal_bimbingan', $data_update);
            
            $this->session->set_flashdata('success', 'Jurnal bimbingan berhasil diperbarui');
            redirect('mahasiswa/bimbingan');
        } catch (Exception $e) {
            log_message('error', 'Error update jurnal: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memperbarui jurnal');
            redirect('mahasiswa/bimbingan/edit_jurnal/' . $jurnal->id);
        }
    }

    /**
     * Send notification to dosen (optional)
     */
    private function _send_notification_to_dosen($dosen_id, $mahasiswa_id, $pertemuan_ke)
    {
        // Implementation untuk kirim notifikasi ke dosen
        // Bisa via email, database notification, dll
        // Untuk saat ini skip dulu atau implementasi sederhana
        
        try {
            // Simple database notification
            $this->db->insert('notifikasi', [
                'user_id' => $dosen_id,
                'user_type' => 'dosen',
                'judul' => 'Jurnal Bimbingan Baru',
                'pesan' => 'Mahasiswa telah menambahkan jurnal bimbingan pertemuan ke-' . $pertemuan_ke,
                'tipe' => 'bimbingan',
                'referensi_id' => $mahasiswa_id,
                'status' => 'unread',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            // Silent fail untuk notifikasi
            log_message('debug', 'Gagal kirim notifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Export jurnal bimbingan (bonus feature)
     */
    public function export_jurnal()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        $proposal = $this->_get_proposal($mahasiswa_id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan');
            redirect('mahasiswa/bimbingan');
        }
        
        $jurnal_list = $this->_get_jurnal_bimbingan($proposal->id);
        
        // Simple CSV export
        $filename = 'jurnal_bimbingan_' . $this->session->userdata('username') . '_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV Header
        fputcsv($output, [
            'No', 'Pertemuan Ke', 'Tanggal', 'Materi Bimbingan', 
            'Tindak Lanjut', 'Status', 'Tanggal Validasi'
        ]);
        
        // CSV Data
        foreach ($jurnal_list as $index => $jurnal) {
            $status = '';
            switch ($jurnal->status_validasi) {
                case '0': $status = 'Pending'; break;
                case '1': $status = 'Tervalidasi'; break;
                case '2': $status = 'Perlu Revisi'; break;
            }
            
            fputcsv($output, [
                $index + 1,
                $jurnal->pertemuan_ke,
                date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)),
                $jurnal->materi_bimbingan,
                $jurnal->tindak_lanjut,
                $status,
                $jurnal->tanggal_validasi ? date('d/m/Y', strtotime($jurnal->tanggal_validasi)) : '-'
            ]);
        }
        
        fclose($output);
    }
}