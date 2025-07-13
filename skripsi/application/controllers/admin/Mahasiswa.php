<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mahasiswa extends MY_Controller
{

    public function index()
    {
        return $this->load->view('admin/mahasiswa');
    }

    public function detail($id = null)
    {
        if (empty($id)) {
            return redirect(base_url('admin/dashboard'));
        }
        return $this->load->view('admin/mahasiswa_detail', ['mahasiswa_id' => $id]);
    }

    public function pencarian()
    {
    $x = $this->db->get_where('mahasiswa_v', array('id' => $this->input->post('id')));
    $data['mahasiswa'] = $x->result();

    // TAMBAHKAN DUA BARIS INI
    $data['seminar_proposal'] = 0;
    $data['hasil_penelitian'] = 0;

    foreach ($x->result_array() as $item) {
        $y =  $this->db->get_where('proposal_mahasiswa', ['proposal_mahasiswa.mahasiswa_id' => $item['id']]);
        $data['usulan_proposal'] = $y->num_rows();
        foreach ($y->result_array() as $value) {
            $data['seminar_proposal'] += $this->db->get_where('seminar', ['seminar.proposal_mahasiswa_id' => $value['id']])->num_rows();
            $data['hasil_penelitian'] += $this->db->get_where('penelitian', ['penelitian.proposal_mahasiswa_id' => $value['id']])->num_rows();
        }
        $data['hk3'] = $this->db->get_where('hasil_kegiatan', ['hasil_kegiatan.mahasiswa_id' => $item['id']])->num_rows();
        $data['skripsi'] = $this->db->get_where('skripsi', ['skripsi.mahasiswa_id' => $item['id']])->num_rows();
    }
        if ($this->input->post('level') == 'Dosen') {
            $this->load->view('dosen/pencarian_mahasiswa', $data);
        } else if ($this->input->post('level') == 'Admin') {
            $this->load->view('admin/pencarian_mahasiswa', $data);
        }
    }
}

/* End of file Mahasiswa.php */
