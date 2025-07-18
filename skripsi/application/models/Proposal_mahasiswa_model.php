<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Proposal_mahasiswa_model
 *
 * Model ini bertanggung jawab untuk mengelola data proposal mahasiswa,
 * termasuk operasi CRUD (Create, Read, Update, Delete) dan proses
 * persetujuan atau penolakan proposal.
 *
 * @property CI_DB_query_builder $db
 * @property CI_Loader $load
 * @property Email_model $emailm
 * @property App $app // Asumsi dari pemanggilan $this->app->validate()
 */
class Proposal_mahasiswa_model extends CI_Model
{
    protected $table = "proposal_mahasiswa";
    protected $table_view = "proposal_mahasiswa_v"; // View untuk join data mahasiswa

    public function __construct()
    {
        parent::__construct();
        // Memuat model email seperti pada skrip asli
        $this->load->model('Email_model', 'emailm');
    }

    /**
     * Mengambil data proposal mahasiswa.
     * Menggunakan JOIN untuk efisiensi query agar tidak membebani database.
     *
     * @param array $input Filter data, bisa berisi 'dosen_id', 'status', atau 'mahasiswa_id'.
     * @return array Hasil data proposal.
     */
    public function get($input)
    {
        $this->db->select(
            $this->table . '.*, ' .
            'p1.nama as nama_pembimbing, ' .
            'p2.nama as nama_pembimbing2'
        );
        $this->db->from($this->table);
        $this->db->join('dosen as p1', 'p1.id = ' . $this->table . '.dosen_id', 'left');
        $this->db->join('dosen as p2', 'p2.id = ' . $this->table . '.dosen2_id', 'left');

        // Menerapkan filter jika ada
        if (!empty($input['dosen_id'])) {
            $this->db->where($this->table . '.dosen_id', $input['dosen_id']);
        }
        if (isset($input['status']) && $input['status'] !== '') {
            $this->db->where($this->table . '.status', $input['status']);
        }
        if (!empty($input['mahasiswa_id'])) {
            $this->db->where($this->table . '.mahasiswa_id', $input['mahasiswa_id']);
        }

        $query_result = $this->db->order_by($this->table . '.id', 'DESC')->get()->result_array();

        $hasil['error'] = false;
        $hasil['message'] = $query_result ? "Data berhasil ditemukan" : "Data tidak tersedia";
        $hasil['data'] = [];

        foreach ($query_result as $item) {
            // Format status untuk tampilan
            if ($item['status'] == '1') {
                $item['nama_status'] = '<span class="badge badge-success">Disetujui</span>';
            } elseif ($item['status'] == '0' && $item['status'] !== null) {
                $item['nama_status'] = '<span class="badge badge-danger">Ditolak</span>';
            } else {
                $item['nama_status'] = '<span class="badge badge-warning">Proses</span>';
            }

            // Format tanggal deadline
            $item['deadline_format'] = $item['deadline'] ? date('d-m-Y H:i', strtotime($item['deadline'])) . " WIB" : "-";
            
            // Menyiapkan data pembimbing untuk konsistensi struktur
            $item['pembimbing'] = ['nama' => $item['nama_pembimbing'] ?? 'N/A'];
            $item['pembimbing2'] = ['nama' => $item['nama_pembimbing2'] ?? 'N/A'];
            
            $hasil['data'][] = $item;
        }

        return $hasil;
    }

    /**
     * Menyimpan data proposal baru.
     *
     * @param array $input Data dari form.
     * @return array Status operasi.
     */
    public function create($input)
    {
        $data = [
            'mahasiswa_id' => $input['mahasiswa_id'] ?? null,
            'judul' => $input['judul'] ?? null,
            'ringkasan' => $input['ringkasan'] ?? null,
            'dosen_id' => $input['dosen_id'] ?? null,
            'dosen2_id' => $input['dosen2_id'] ?? null,
            'dosen_penguji_id' => $input['dosen_penguji_id'] ?? null,
        ];

        // Pola validasi sesuai repositori
        $validate = $this->app->validate($data);

        if ($validate === true) {
            $this->db->insert($this->table, $data);
            $hasil = [
                'error' => false,
                'message' => "Data proposal berhasil diajukan.",
                'data_id' => $this->db->insert_id()
            ];
        } else {
            $hasil = $validate; // Mengembalikan pesan error dari validasi
        }

        return $hasil;
    }

    /**
     * Memperbarui data proposal yang ada.
     *
     * @param array $input Data baru.
     * @param int $id ID proposal.
     * @return array Status operasi.
     */
    public function update($input, $id)
    {
        // Cek dulu apakah data ada
        if ($this->db->get_where($this->table, ['id' => $id])->num_rows() == 0) {
            return ['error' => true, 'message' => "Data tidak ditemukan"];
        }

        $data = [
            'mahasiswa_id' => $input['mahasiswa_id'] ?? null,
            'judul' => $input['judul'] ?? null,
            'ringkasan' => $input['ringkasan'] ?? null,
            'dosen_id' => $input['dosen_id'] ?? null,
            'dosen2_id' => $input['dosen2_id'] ?? null,
            'dosen_penguji_id' => $input['dosen_penguji_id'] ?? null
        ];

        $validate = $this->app->validate($data);

        if ($validate === true) {
            $this->db->update($this->table, $data, ['id' => $id]);
            $hasil = ['error' => false, 'message' => "Data berhasil diperbarui"];
        } else {
            $hasil = $validate;
        }

        return $hasil;
    }

    /**
     * Menghapus data proposal.
     *
     * @param int $id ID proposal.
     * @return array Status operasi.
     */
    public function destroy($id)
    {
        if ($this->db->get_where($this->table, ['id' => $id])->num_rows() > 0) {
            $this->db->delete($this->table, ['id' => $id]);
            return ['error' => false, 'message' => "Data berhasil dihapus"];
        }
        return ['error' => true, 'message' => "Data tidak ditemukan"];
    }

    /**
     * Mengubah status proposal menjadi 'Disetujui'.
     *
     * @param int $id ID proposal.
     * @param string $deadline Tanggal deadline.
     * @return array Status operasi.
     */
    public function agree($id, $deadline)
    {
        // Mengambil data lengkap proposal termasuk email dari view
        $proposal = $this->db->get_where($this->table_view, ['id' => $id])->row();

        if ($proposal) {
            $this->db->update($this->table, ['status' => '1', 'deadline' => $deadline], ['id' => $id]);
            
            $isi_email = '<p>Selamat, usulan proposal Anda telah <b>DISETUJUI</b>. Silakan lanjutkan ke tahap berikutnya.</p>';
            $this->emailm->send('Status Usulan Proposal: Disetujui', $proposal->email, $isi_email);

            return ['error' => false, 'message' => "Proposal berhasil disetujui"];
        }
        
        return ['error' => true, 'message' => "Data proposal tidak ditemukan"];
    }

    /**
     * Mengubah status proposal menjadi 'Ditolak' (Tidak Disetujui).
     *
     * @param int $id ID proposal.
     * @return array Status operasi.
     */
    public function disagree($id)
    {
        $proposal = $this->db->get_where($this->table_view, ['id' => $id])->row();

        if ($proposal) {
            $this->db->update($this->table, ['status' => '0', 'deadline' => null], ['id' => $id]);

            $isi_email = '<p>Mohon maaf, usulan proposal Anda saat ini <b>TIDAK DISETUJUI</b>. Silakan perbaiki usulan Anda dan ajukan kembali.</p>';
            $this->emailm->send('Status Usulan Proposal: Tidak Disetujui', $proposal->email, $isi_email);

            return ['error' => false, 'message' => "Proposal telah ditolak"];
        }

        return ['error' => true, 'message' => "Data proposal tidak ditemukan"];
    }
}
/* End of file Proposal_mahasiswa_model.php */