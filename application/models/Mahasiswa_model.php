<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mahasiswa_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Email_model', 'emailm');
    }


    protected $table = "mahasiswa";

    public function get($input)
    {
        $this->db->select("*");
        $mahasiswa = $this->db->get($this->table)->result_array();

        $hasil['error'] = false;
        $hasil['message'] = ($mahasiswa) ? "data berhasil ditemukan" : "data tidak tersedia";
        $hasil['data'] = $mahasiswa;

    foreach ($mahasiswa as $key => $item) {
        $prodi = $this->db->get_where('prodi', ['prodi.id' => $item['prodi_id']])->row_array();
        if ($prodi) {
            $prodi['fakultas'] = $this->db->get_where('fakultas', ['fakultas.id' => $prodi['fakultas_id']])->row_array();
        }
        $hasil['data'][$key]['prodi'] = $prodi;
    
        // Inisialisasi variabel dengan nilai 0
        $hasil['data'][$key]['seminar_proposal'] = 0;
        $hasil['data'][$key]['hasil_penelitian'] = 0;
    
        $x =  $this->db->get_where('proposal_mahasiswa', ['proposal_mahasiswa.mahasiswa_id' => $item['id']]);
        $hasil['data'][$key]['usulan_proposal'] = $x->num_rows();
    
        foreach ($x->result_array() as $k => $value) {
            $hasil['data'][$key]['seminar_proposal'] += $this->db->get_where('seminar', ['seminar.proposal_mahasiswa_id' => $value['id']])->num_rows();
            $hasil['data'][$key]['hasil_penelitian'] += $this->db->get_where('penelitian', ['penelitian.proposal_mahasiswa_id' => $value['id']])->num_rows();
        }
    
        $hasil['data'][$key]['hk3'] = $this->db->get_where('hasil_kegiatan', ['hasil_kegiatan.mahasiswa_id' => $item['id']])->num_rows();
        $hasil['data'][$key]['skripsi'] = $this->db->get_where('skripsi', ['skripsi.mahasiswa_id' => $item['id']])->num_rows();
    }

        return $hasil;
    }

    public function create($input)
    {
    // 1. Validasi konfirmasi password
    if ($input['password'] != $input['password_konfirmasi']) {
        return ['error' => true, 'message' => 'Konfirmasi password tidak cocok'];
    }

    // 2. Siapkan data untuk dimasukkan ke database
    $data = [
        'nim' => $input['nim'],
        'nama' => $input['nama'],
        'prodi_id' => $input['prodi_id'],
        'jenis_kelamin' => $input['jenis_kelamin'],
        'tempat_lahir' => $input['tempat_lahir'],
        'tanggal_lahir' => $input['tanggal_lahir'],
        'email' => $input['email'],
        'alamat' => $input['alamat'],
        'nomor_telepon' => $input['nomor_telepon'],
        'nomor_telepon_orang_dekat' => $input['nomor_telepon_orang_dekat'],
        'ipk' => $input['ipk'],
        'password' => $input['password'] ? password_hash($input['password'], PASSWORD_DEFAULT) : '',
        'status' => '1'  // <-- PERUBAHAN: Langsung set status menjadi aktif
    ];

    $validate = $this->app->validate($data);
    
        if ($validate === true) {
        $cek = $this->db->get_where($this->table, ['mahasiswa.nim' => $data['nim']])->num_rows();
        if ($cek > 0) {
            $hasil = [
                'error' => true,
                'message' => "NIM sudah digunakan"
            ];
        } else {
            if ($input['foto']) {
                $foto = explode(';base64,', $input['foto'])[1];
                $foto_nama = date('Ymdhis') . '.png';
                file_put_contents(FCPATH . 'cdn/img/mahasiswa/' . $foto_nama, base64_decode($foto));
                $data['foto'] = $foto_nama;
            }

            if ($this->db->insert($this->table, $data)) {

                // <-- PERUBAHAN: Isi email notifikasi diubah
                $isi_email = '
                <p>Halo ' . $data['nama'] . ',</p>
                <p>Pendaftaran Anda di Sistem Informasi Manajemen Tugas Akhir STK St. Yakobus Merauke telah berhasil. Akun Anda sudah aktif dan bisa langsung digunakan untuk login.</p>
                <p>Gunakan kredensial berikut untuk login:</p>
                <ul>
                    <li><b>Username/NIM:</b> ' . $data['nim'] . '</li>
                    <li><b>Password:</b> ' . $input['password'] . '</li>
                </ul>
                <p>Terima kasih.</p>
                ';

                $data_id = $this->db->insert_id();

                $hasil = [
                    'error' => false,
                    'message' => "Registrasi berhasil! Akun Anda sudah aktif.",
                    'email_message' => $this->emailm->send('Registrasi SIM Tugas Akhir Berhasil', $data['email'], $isi_email),
                    'data_id' => $data_id
                ];
            }
        }
    } else {
        $hasil = $validate;
    }

    return $hasil;
    }

    public function update($input, $id)
    {
        $data = [
            'nim' => $input['nim'],
            'nama' => $input['nama'],
            'prodi_id' => $input['prodi_id'],
            'jenis_kelamin' => $input['jenis_kelamin'],
            'tempat_lahir' => $input['tempat_lahir'],
            'tanggal_lahir' => $input['tanggal_lahir'],
            'email' => $input['email'],
            'alamat' => $input['alamat'],
            'nomor_telepon' => $input['nomor_telepon'],
            'nomor_telepon_orang_dekat' => $input['nomor_telepon_orang_dekat'],
            'ipk' => $input['ipk']
        ];

        $kondisi = ['mahasiswa.id' => $id];

        $this->db->where($kondisi);
        $cek = $this->db->get($this->table)->num_rows();

        if ($cek <= 0) {
            $hasil = [
                'error' => true,
                'message' => "data tidak ditemukan"
            ];
        } else {
            $validate = $this->app->validate($data);

            if ($validate === true) {
                $cek = $this->db->get_where($this->table, ['mahasiswa.id <>' => $id, 'mahasiswa.nim' => $data['nim']])->num_rows();
                if ($cek > 0) {
                    $hasil = [
                        'error' => true,
                        'message' => "nim sudah digunakan"
                    ];
                } else {
                    $data['status'] = $input['status'];
                    if ($input['foto']) {
                        $foto = explode(';base64,', $input['foto'])[1];
                        $foto_nama = date('Ymdhis') . '.png';
                        file_put_contents(FCPATH . 'cdn/img/mahasiswa/' . $foto_nama, base64_decode($foto));
                        $data['foto'] = $foto_nama;

                        $foto = $this->db->get_where($this->table, $kondisi)->row_array()['foto'];
                        if ($foto) {
                            unlink(FCPATH . 'cdn/img/mahasiswa/' . $foto);
                        }
                    }

                    if ($input['def_status'] != $input['status']) {
                        if ($input['def_status'] == 0) {
                            $isi_email = '
                        <p>Akun anda telah diaktifkan oleh admin kami.</p>
                        ';
                            $this->emailm->send('Akun Diaktifkan', $data['email'], $isi_email);
                        } else {
                            $isi_email = '
                        <p>Akun anda telah dinonaktifkan oleh admin kami.</p>
                        ';
                            $this->emailm->send('Akun Dinonaktifkan', $data['email'], $isi_email);
                        }
                    }

                    $this->db->update($this->table, $data, $kondisi);
                    $hasil = [
                        'error' => false,
                        'message' => "data berhasil diedit"
                    ];
                }
            } else {
                $hasil = $validate;
            }
        }

        return $hasil;
    }

    public function update2($input, $id)
    {
        $data = [
            'nim' => $input['nim'],
            'nama' => $input['nama'],
            'prodi_id' => $input['prodi_id'],
            'jenis_kelamin' => $input['jenis_kelamin'],
            'tempat_lahir' => $input['tempat_lahir'],
            'tanggal_lahir' => $input['tanggal_lahir'],
            'email' => $input['email'],
            'alamat_orang_tua' => $input['alamat_orang_tua'],
            'nomor_telepon_orang_tua' => $input['nomor_telepon_orang_tua'],
            'alamat' => $input['alamat'],
            'nomor_telepon' => $input['nomor_telepon'],
            'nomor_telepon_orang_dekat' => $input['nomor_telepon_orang_dekat'],
            'ipk' => $input['ipk']
        ];

        $kondisi = ['mahasiswa.id' => $id];

        $this->db->where($kondisi);
        $cek = $this->db->get($this->table)->num_rows();

        if ($cek <= 0) {
            $hasil = [
                'error' => true,
                'message' => "data tidak ditemukan"
            ];
        } else {
            $validate = $this->app->validate($data);

            if ($validate === true) {
                $cek = $this->db->get_where($this->table, ['mahasiswa.id <>' => $id, 'mahasiswa.nim' => $data['nim']])->num_rows();
                if ($cek > 0) {
                    $hasil = [
                        'error' => true,
                        'message' => "nim sudah digunakan"
                    ];
                } else {
                    $data['status'] = 1;
                    if ($input['foto']) {
                        $foto = explode(';base64,', $input['foto'])[1];
                        $foto_nama = date('Ymdhis') . '.png';
                        file_put_contents(FCPATH . 'cdn/img/mahasiswa/' . $foto_nama, base64_decode($foto));
                        $data['foto'] = $foto_nama;

                        $foto = $this->db->get_where($this->table, $kondisi)->row_array()['foto'];
                        if ($foto) {
                            unlink(FCPATH . 'cdn/img/mahasiswa/' . $foto);
                        }
                    }

                    if ($input['def_status'] != $input['status']) {
                        if ($input['def_status'] == 0) {
                            $isi_email = '
                        <p>Akun anda telah diaktifkan oleh admin kami.</p>
                        ';
                            $this->emailm->send('Akun Diaktifkan', $data['email'], $isi_email);
                        } else {
                            $isi_email = '
                        <p>Akun anda telah dinonaktifkan oleh admin kami.</p>
                        ';
                            $this->emailm->send('Akun Dinonaktifkan', $data['email'], $isi_email);
                        }
                    }

                    $this->db->update($this->table, $data, $kondisi);
                    $hasil = [
                        'error' => false,
                        'message' => "data berhasil diedit"
                    ];
                }
            } else {
                $hasil = $validate;
            }
        }

        return $hasil;
    }

    public function destroy($id)
    {
        $kondisi = ['mahasiswa.id' => $id];

        $this->db->where($kondisi);
        $cek = $this->db->get($this->table)->num_rows();

        if ($cek <= 0) {
            $hasil = [
                'error' => true,
                'message' => "data tidak ditemukan"
            ];
        } else {
            $this->db->where($kondisi);
            $this->db->delete($this->table);
            $hasil = [
                'error' => false,
                'message' => "data berhasil dihapus"
            ];
        }

        return $hasil;
    }

    public function detail($id)
    {
        $mahasiswa = $this->db->get_where($this->table, ['id' => $id])->row_array();
        if ($mahasiswa) {
            $hasil = [
                'error' => false,
                'message' => "data berhasil ditemukan",
                'data' => $mahasiswa
            ];
            $hasil['data']['proposal'] = $this->db->get_where('proposal_mahasiswa', ['proposal_mahasiswa.mahasiswa_id' => $hasil['data']['id']])->result_array();
            
            $prodi = $this->db->get_where('prodi', ['prodi.id' => $hasil['data']['prodi_id']])->row_array();
            $prodi['fakultas'] = $this->db->get_where('fakultas', ['fakultas.id' => $prodi['fakultas_id']])->row_array();
            $hasil['data']['prodi'] = $prodi;
        } else {
            $hasil = [
                'error' => true,
                'message' => "data tidak ditemukan"
            ];
        }

        return $hasil;
    }

    public function search($input)
    {
        $mahasiswa = $this->db->get_where($this->table, $input)->row_array();

        $hasil['error'] = false;
        $hasil['message'] = ($mahasiswa) ? "data berhasil ditemukan" : "data tidak ditemukan";
        $hasil['data'] = $mahasiswa;

        return $hasil;
    }

    public function dataperprodi()
    {
        $this->db->select("
            count(mahasiswa.id) as mahasiswa_total,
            prodi.nama as prodi_nama
        ");
        $this->db->group_by('prodi.id');
        $this->db->from($this->table);
        $this->db->join('prodi', 'prodi.id = mahasiswa.prodi_id', 'left');

        $mahasiswa_per_prodi = $this->db->get()->result_array();

        $hasil = [
            'error' => false,
            'message' => $mahasiswa_per_prodi ? "data berhasil ditemukan" : "data tidak tersedia",
            'data' => $mahasiswa_per_prodi
        ];

        return $hasil;
    }

    public function verifikasi($input, $id)
    {
        $mahasiswa = $this->db->get_where('mahasiswa', ['id' => $id])->row_array();

        if ($mahasiswa) {
            $validation = $this->app->validate(['password' => $input['password']]);

            if ($mahasiswa["status"] == "0") {
                $hasil = [
                    'error' => true,
                    'message' => "akun belum diverifikasi"
                ];
                return $hasil;
            }

            if ($validation === true) {
                if (password_verify($input['password'], $mahasiswa['password'])) {
                    $hasil = [
                        'error' => false,
                        'message' => "berhasil login",
                        'data' => $mahasiswa
                    ];
                } else {
                    $hasil = [
                        'error' => true,
                        'message' => "password salah"
                    ];
                }
            } else {
                $ahsil = $validation;
            }
        } else {
            $hasil = [
                'error' => true,
                'message' => "mahasiswa tidak ditemukan"
            ];
        }

        return $hasil;
    }
}

/* End of file Mahasiswa_model.php */
