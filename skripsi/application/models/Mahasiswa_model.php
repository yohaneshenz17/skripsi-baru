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

    // PERBAIKAN: Update method update2() di Mahasiswa_model.php
    // Tambahkan update session setelah foto berhasil disimpan
    
    public function update2($input, $id)
    {
        // Data yang akan diupdate
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
                'message' => "Data tidak ditemukan"
            ];
        } else {
            $validate = $this->app->validate($data);
    
            if ($validate === true) {
                // Cek duplikasi NIM
                $cek = $this->db->get_where($this->table, [
                    'mahasiswa.id <>' => $id, 
                    'mahasiswa.nim' => $data['nim']
                ])->num_rows();
                
                if ($cek > 0) {
                    $hasil = [
                        'error' => true,
                        'message' => "NIM sudah digunakan"
                    ];
                } else {
                    $data['status'] = 1;
                    $foto_baru = null;
                    
                    // PERBAIKAN: Handle foto upload dengan error handling yang lebih baik
                    if (!empty($input['foto'])) {
                        try {
                            // Pastikan folder ada
                            $upload_path = FCPATH . 'cdn/img/mahasiswa/';
                            if (!is_dir($upload_path)) {
                                mkdir($upload_path, 0755, true);
                            }
                            
                            // Decode base64
                            $foto_parts = explode(';base64,', $input['foto']);
                            if (count($foto_parts) == 2) {
                                $foto_data = base64_decode($foto_parts[1]);
                                $foto_nama = date('Ymdhis') . '_' . $id . '.png';
                                
                                // Simpan file
                                if (file_put_contents($upload_path . $foto_nama, $foto_data)) {
                                    $data['foto'] = $foto_nama;
                                    $foto_baru = $foto_nama;
                                    
                                    log_message('info', 'Foto mahasiswa berhasil disimpan: ' . $foto_nama);
                                    
                                    // Hapus foto lama jika ada dan bukan default
                                    $mahasiswa_lama = $this->db->get_where($this->table, $kondisi)->row_array();
                                    if ($mahasiswa_lama && !empty($mahasiswa_lama['foto']) && $mahasiswa_lama['foto'] !== 'default.png') {
                                        $foto_lama_path = $upload_path . $mahasiswa_lama['foto'];
                                        if (file_exists($foto_lama_path)) {
                                            unlink($foto_lama_path);
                                            log_message('info', 'Foto lama dihapus: ' . $mahasiswa_lama['foto']);
                                        }
                                    }
                                } else {
                                    log_message('error', 'Gagal menyimpan file foto mahasiswa');
                                    throw new Exception('Gagal menyimpan file foto');
                                }
                            } else {
                                throw new Exception('Format foto tidak valid');
                            }
                            
                        } catch (Exception $e) {
                            log_message('error', 'Error upload foto mahasiswa: ' . $e->getMessage());
                            // Continue tanpa update foto, tapi beri peringatan
                            $hasil = [
                                'error' => true,
                                'message' => "Data profil berhasil disimpan, tetapi foto gagal diupload: " . $e->getMessage()
                            ];
                            return $hasil;
                        }
                    }
    
                    // Update database
                    try {
                        $this->db->update($this->table, $data, $kondisi);
                        
                        if ($this->db->affected_rows() > 0 || !$this->db->error()['code']) {
                            
                            // PERBAIKAN: Update session dengan data terbaru
                            $CI =& get_instance();
                            
                            // Update session nama jika berubah
                            if ($data['nama'] != $CI->session->userdata('nama')) {
                                $CI->session->set_userdata('nama', $data['nama']);
                                log_message('info', 'Session nama updated to: ' . $data['nama']);
                            }
                            
                            // Update session foto jika ada foto baru
                            if ($foto_baru) {
                                $CI->session->set_userdata('foto', $foto_baru);
                                log_message('info', 'Session foto updated to: ' . $foto_baru);
                            }
                            
                            // PERBAIKAN: Return response dengan informasi foto yang jelas
                            $hasil = [
                                'error' => false,
                                'message' => "Profil berhasil diperbarui",
                                'foto_updated' => !empty($foto_baru),
                                'new_foto_name' => $foto_baru,
                                'affected_rows' => $this->db->affected_rows()
                            ];
                            
                            log_message('info', 'Mahasiswa profile updated successfully: ' . $id);
                            
                        } else {
                            log_message('error', 'Database update failed for mahasiswa: ' . $id . ' - ' . print_r($this->db->error(), true));
                            $hasil = [
                                'error' => true,
                                'message' => "Gagal menyimpan data ke database"
                            ];
                        }
                        
                    } catch (Exception $e) {
                        log_message('error', 'Error update mahasiswa database: ' . $e->getMessage());
                        $hasil = [
                            'error' => true,
                            'message' => "Gagal menyimpan data: " . $e->getMessage()
                        ];
                    }
                }
            } else {
                $hasil = $validate;
            }
        }
    
        return $hasil;
    }
    
    // PERBAIKAN: Method detail dengan better error handling
    public function detail($id)
    {
        try {
            $mahasiswa = $this->db->get_where($this->table, ['id' => $id])->row_array();
            
            if ($mahasiswa) {
                $hasil = [
                    'error' => false,
                    'message' => "Data berhasil ditemukan",
                    'data' => $mahasiswa
                ];
                
                // Load data proposal dengan error handling
                try {
                    $hasil['data']['proposal'] = $this->db->get_where('proposal_mahasiswa', [
                        'proposal_mahasiswa.mahasiswa_id' => $hasil['data']['id']
                    ])->result_array();
                } catch (Exception $e) {
                    log_message('error', 'Error loading proposal data: ' . $e->getMessage());
                    $hasil['data']['proposal'] = [];
                }
                
                // Load data prodi dengan error handling
                try {
                    $prodi = $this->db->get_where('prodi', ['prodi.id' => $hasil['data']['prodi_id']])->row_array();
                    if ($prodi) {
                        $prodi['fakultas'] = $this->db->get_where('fakultas', ['fakultas.id' => $prodi['fakultas_id']])->row_array();
                    }
                    $hasil['data']['prodi'] = $prodi;
                } catch (Exception $e) {
                    log_message('error', 'Error loading prodi data: ' . $e->getMessage());
                    $hasil['data']['prodi'] = null;
                }
                
            } else {
                $hasil = [
                    'error' => true,
                    'message' => "Data tidak ditemukan"
                ];
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error detail mahasiswa: ' . $e->getMessage());
            $hasil = [
                'error' => true,
                'message' => "Terjadi kesalahan sistem: " . $e->getMessage()
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
