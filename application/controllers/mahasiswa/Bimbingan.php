<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bimbingan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');

        // Cek apakah user sudah login sebagai mahasiswa
        if ($this->session->userdata('level') != '3') {
            redirect('auth/login');
        }
    }

    public function index()
    {
        $data['title'] = 'Bimbingan Skripsi';
        $mahasiswa_id = $this->session->userdata('id');

        // Ambil data konsultasi/bimbingan mahasiswa
        $this->db->select('k.*, d.nama as nama_dosen, pm.judul');
        $this->db->from('konsultasi k');
        $this->db->join('proposal_mahasiswa pm', 'k.proposal_mahasiswa_id = pm.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        $this->db->order_by('k.tanggal', 'DESC');
        $data['bimbingan'] = $this->db->get()->result();

        // Ambil data proposal dan dosen pembimbing
        $this->db->select('pm.id as proposal_id, pm.judul, d.id as dosen_id, d.nama as nama_dosen');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('dosen d', 'pm.dosen_id = d.id');
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        $this->db->where('pm.status', '1'); // Status disetujui
        $data['proposal'] = $this->db->get()->row();

        $this->load->view('mahasiswa/bimbingan', $data);
    }

    public function ajukan()
    {
        if ($this->input->post()) {
            $mahasiswa_id = $this->session->userdata('id');
            
            // Cek apakah mahasiswa sudah memiliki proposal yang disetujui
            $proposal = $this->db->get_where('proposal_mahasiswa', [
                'mahasiswa_id' => $mahasiswa_id,
                'status' => '1',
                'dosen_id !=' => NULL
            ])->row();

            if (!$proposal) {
                $this->session->set_flashdata('error', 'Anda belum memiliki proposal yang disetujui dengan dosen pembimbing.');
                redirect('mahasiswa/bimbingan');
                return;
            }

            // Pastikan folder upload ada
            if (!is_dir('./cdn/konsultasi/')) {
                mkdir('./cdn/konsultasi/', 0755, true);
            }

            // Upload file bukti konsultasi
            $config['upload_path'] = './cdn/konsultasi/';
            $config['allowed_types'] = 'pdf|doc|docx';
            $config['max_size'] = 2048; // 2MB
            $config['encrypt_name'] = TRUE;

            $this->load->library('upload', $config);
            
            if (!$this->upload->do_upload('bukti')) {
                $error = $this->upload->display_errors('', '');
                $this->session->set_flashdata('error', 'Gagal upload file bukti: ' . $error);
                redirect('mahasiswa/bimbingan');
                return;
            }

            $upload_data = $this->upload->data();

            $data_to_save = [
                'proposal_mahasiswa_id' => $proposal->id,
                'tanggal' => date('Y-m-d'),
                'jam' => date('H:i:s'),
                'isi' => $this->input->post('isi_konsultasi'),
                'bukti' => $upload_data['file_name'],
                'persetujuan_pembimbing' => '0',
                'persetujuan_kaprodi' => '0'
            ];

            if ($this->db->insert('konsultasi', $data_to_save)) {
                $this->session->set_flashdata('success', 'Permintaan konsultasi berhasil diajukan!');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengajukan permintaan konsultasi!');
            }
            
            redirect('mahasiswa/bimbingan');
        } else {
            // Jika tidak ada POST data, redirect ke index
            redirect('mahasiswa/bimbingan');
        }
    }

    public function detail($id)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Ambil detail konsultasi
        $this->db->select('k.*, d.nama as nama_dosen, pm.judul');
        $this->db->from('konsultasi k');
        $this->db->join('proposal_mahasiswa pm', 'k.proposal_mahasiswa_id = pm.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id');
        $this->db->where('k.id', $id);
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        $detail = $this->db->get()->row();
        
        if (!$detail) {
            echo '<div class="alert alert-danger">Data konsultasi tidak ditemukan</div>';
            return;
        }
        
        // Output detail dalam format HTML
        ?>
        <div class="row">
            <div class="col-md-6">
                <strong>Tanggal:</strong> <?= date('d/m/Y', strtotime($detail->tanggal)) ?><br>
                <strong>Jam:</strong> <?= date('H:i', strtotime($detail->jam)) ?><br>
                <strong>Dosen Pembimbing:</strong> <?= $detail->nama_dosen ?><br>
                <strong>Judul Proposal:</strong> <?= $detail->judul ?>
            </div>
            <div class="col-md-6">
                <strong>Status Pembimbing:</strong> 
                <?php if($detail->persetujuan_pembimbing == '1'): ?>
                    <span class="badge badge-success">Disetujui</span>
                <?php else: ?>
                    <span class="badge badge-warning">Menunggu</span>
                <?php endif; ?><br>
                <strong>Status Kaprodi:</strong> 
                <?php if($detail->persetujuan_kaprodi == '1'): ?>
                    <span class="badge badge-success">Disetujui</span>
                <?php else: ?>
                    <span class="badge badge-warning">Menunggu</span>
                <?php endif; ?>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <strong>Isi Konsultasi:</strong>
                <div class="alert alert-light"><?= nl2br($detail->isi) ?></div>
            </div>
        </div>
        <?php if($detail->bukti): ?>
        <div class="row">
            <div class="col-md-12">
                <strong>File Bukti:</strong><br>
                <a href="<?= base_url('cdn/konsultasi/' . $detail->bukti) ?>" target="_blank" class="btn btn-sm btn-success">
                    <i class="fa fa-download"></i> Download File
                </a>
            </div>
        </div>
        <?php endif; ?>
        <?php if($detail->komentar_pembimbing): ?>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <strong>Komentar Pembimbing:</strong>
                <div class="alert alert-info"><?= nl2br($detail->komentar_pembimbing) ?></div>
            </div>
        </div>
        <?php endif; ?>
        <?php if($detail->komentar_kaprodi): ?>
        <div class="row">
            <div class="col-md-12">
                <strong>Komentar Kaprodi:</strong>
                <div class="alert alert-secondary"><?= nl2br($detail->komentar_kaprodi) ?></div>
            </div>
        </div>
        <?php endif; ?>
        <?php
    }
}