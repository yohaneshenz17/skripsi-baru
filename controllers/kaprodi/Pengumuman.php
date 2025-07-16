<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pengumuman extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');

        // Cek apakah user sudah login sebagai kaprodi
        if ($this->session->userdata('level') != '4') {
            redirect('auth/login');
        }
    }

    public function index()
    {
        $data['title'] = 'Pengumuman Tahapan Skripsi';
        
        // Ambil data pengumuman
        $this->db->order_by('no', 'ASC');
        $data['pengumuman'] = $this->db->get('pengumuman_tahapan')->result();

        $this->load->view('kaprodi/pengumuman/index', $data);
    }

    public function tambah()
    {
        $data['title'] = 'Tambah Pengumuman Tahapan';
        $this->load->view('kaprodi/pengumuman/tambah', $data);
    }

    public function simpan()
    {
        if ($this->input->post()) {
            $data = [
                'no' => $this->input->post('no'),
                'tahapan' => $this->input->post('tahapan'),
                'tanggal_deadline' => $this->input->post('tanggal_deadline'),
                'keterangan' => $this->input->post('keterangan'),
                'aktif' => $this->input->post('aktif') ? '1' : '0'
            ];

            if ($this->db->insert('pengumuman_tahapan', $data)) {
                $this->session->set_flashdata('success', 'Pengumuman berhasil ditambahkan!');
            } else {
                $this->session->set_flashdata('error', 'Gagal menambahkan pengumuman!');
            }

            redirect('kaprodi/pengumuman');
        }
    }

    public function edit($id)
    {
        $data['title'] = 'Edit Pengumuman Tahapan';
        $data['pengumuman'] = $this->db->get_where('pengumuman_tahapan', ['id' => $id])->row();

        if (!$data['pengumuman']) {
            $this->session->set_flashdata('error', 'Data pengumuman tidak ditemukan!');
            redirect('kaprodi/pengumuman');
        }

        $this->load->view('kaprodi/pengumuman/edit', $data);
    }

    public function update($id)
    {
        if ($this->input->post()) {
            $data = [
                'no' => $this->input->post('no'),
                'tahapan' => $this->input->post('tahapan'),
                'tanggal_deadline' => $this->input->post('tanggal_deadline'),
                'keterangan' => $this->input->post('keterangan'),
                'aktif' => $this->input->post('aktif') ? '1' : '0'
            ];

            if ($this->db->update('pengumuman_tahapan', $data, ['id' => $id])) {
                $this->session->set_flashdata('success', 'Pengumuman berhasil diperbarui!');
            } else {
                $this->session->set_flashdata('error', 'Gagal memperbarui pengumuman!');
            }

            redirect('kaprodi/pengumuman');
        }
    }

    public function hapus($id)
    {
        if ($this->db->delete('pengumuman_tahapan', ['id' => $id])) {
            $this->session->set_flashdata('success', 'Pengumuman berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus pengumuman!');
        }

        redirect('kaprodi/pengumuman');
    }

    public function toggle_status($id)
    {
        $pengumuman = $this->db->get_where('pengumuman_tahapan', ['id' => $id])->row();
        
        if ($pengumuman) {
            $new_status = ($pengumuman->aktif == '1') ? '0' : '1';
            $this->db->update('pengumuman_tahapan', ['aktif' => $new_status], ['id' => $id]);
            
            $status_text = ($new_status == '1') ? 'diaktifkan' : 'dinonaktifkan';
            $this->session->set_flashdata('success', 'Pengumuman berhasil ' . $status_text . '!');
        } else {
            $this->session->set_flashdata('error', 'Data pengumuman tidak ditemukan!');
        }

        redirect('kaprodi/pengumuman');
    }
}