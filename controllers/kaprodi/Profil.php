<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Profil extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('upload');
        $this->load->helper('url');
        $this->load->helper('file');

        // Cek login
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }

        // Cek level kaprodi
        if ($this->session->userdata('level') != '4') {
            show_error('Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.', 403);
        }
    }

    public function index()
    {
        $data['title'] = 'Profil Saya';
        $dosen_id = $this->session->userdata('id');

        // Ambil data dosen/kaprodi
        $data['user'] = $this->db->get_where('dosen', ['id' => $dosen_id])->row();

        if (!$data['user']) {
            show_error('Data dosen tidak ditemukan.', 404);
        }

        $this->load->view('kaprodi/profil', $data);
    }

    public function update()
    {
        if ($this->input->post()) {
            $dosen_id = $this->session->userdata('id');
            
            // Validasi input
            $nama = $this->input->post('nama');
            $email = $this->input->post('email');
            $nomor_telepon = $this->input->post('nomor_telepon');
            $nip = $this->input->post('nip');

            // Validasi email unique (kecuali untuk dosen yang sama)
            $this->db->where('email', $email);
            $this->db->where('id !=', $dosen_id);
            $email_exists = $this->db->get('dosen')->num_rows();

            if ($email_exists > 0) {
                $this->session->set_flashdata('error', 'Email sudah digunakan oleh dosen lain.');
                redirect('kaprodi/profil');
                return;
            }

            // Validasi NIP unique (kecuali untuk dosen yang sama)
            $this->db->where('nip', $nip);
            $this->db->where('id !=', $dosen_id);
            $nip_exists = $this->db->get('dosen')->num_rows();

            if ($nip_exists > 0) {
                $this->session->set_flashdata('error', 'NIP sudah digunakan oleh dosen lain.');
                redirect('kaprodi/profil');
                return;
            }

            // Data yang akan diupdate
            $data_update = [
                'nama' => $nama,
                'email' => $email,
                'nomor_telepon' => $nomor_telepon,
                'nip' => $nip
            ];

            // Handle upload foto
            if (!empty($_FILES['foto']['name'])) {
                $upload_result = $this->_upload_foto();
                if ($upload_result['status']) {
                    $data_update['foto'] = $upload_result['filename'];
                    
                    // Hapus foto lama jika ada
                    $old_dosen = $this->db->get_where('dosen', ['id' => $dosen_id])->row();
                    if ($old_dosen && !empty($old_dosen->foto) && $old_dosen->foto != 'default.png') {
                        $old_file = './cdn/img/dosen/' . $old_dosen->foto;
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                } else {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('kaprodi/profil');
                    return;
                }
            }

            // Update data
            $this->db->where('id', $dosen_id);
            if ($this->db->update('dosen', $data_update)) {
                // Update session data
                $this->session->set_userdata([
                    'nama' => $nama,
                    'email' => $email
                ]);

                $this->session->set_flashdata('success', 'Profil berhasil diperbarui!');
            } else {
                $this->session->set_flashdata('error', 'Gagal memperbarui profil!');
            }

            redirect('kaprodi/profil');
        } else {
            redirect('kaprodi/profil');
        }
    }

    private function _upload_foto()
    {
        // Buat folder jika belum ada
        if (!is_dir('./cdn/img/dosen/')) {
            mkdir('./cdn/img/dosen/', 0755, true);
        }

        // Konfigurasi upload
        $config['upload_path'] = './cdn/img/dosen/';
        $config['allowed_types'] = 'jpg|jpeg';
        $config['max_size'] = 500; // 500KB
        $config['encrypt_name'] = TRUE;
        $config['remove_spaces'] = TRUE;

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('foto')) {
            return [
                'status' => false,
                'message' => 'Gagal upload foto: ' . $this->upload->display_errors('', '')
            ];
        }

        $upload_data = $this->upload->data();
        
        // Resize foto jika terlalu besar
        $this->load->library('image_lib');
        $config_resize = [
            'image_library' => 'gd2',
            'source_image' => $upload_data['full_path'],
            'maintain_ratio' => TRUE,
            'width' => 300,
            'height' => 300
        ];

        $this->image_lib->initialize($config_resize);
        $this->image_lib->resize();

        return [
            'status' => true,
            'filename' => $upload_data['file_name'],
            'message' => 'Foto berhasil diupload'
        ];
    }

    public function hapus_foto()
    {
        $dosen_id = $this->session->userdata('id');
        
        // Ambil data dosen
        $dosen = $this->db->get_where('dosen', ['id' => $dosen_id])->row();
        
        if ($dosen && !empty($dosen->foto) && $dosen->foto != 'default.png') {
            // Hapus file foto
            $foto_path = './cdn/img/dosen/' . $dosen->foto;
            if (file_exists($foto_path)) {
                unlink($foto_path);
            }
            
            // Update database
            $this->db->where('id', $dosen_id);
            $this->db->update('dosen', ['foto' => '']);
            
            $this->session->set_flashdata('success', 'Foto profil berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Foto tidak ditemukan!');
        }
        
        redirect('kaprodi/profil');
    }
}