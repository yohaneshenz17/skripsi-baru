<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Seminar_model
 * Model untuk mengelola data seminar proposal, termasuk CRUD dan penanganan file.
 *
 * @property CI_DB_query_builder $db
 * @property App $app
 */
class Seminar_model extends CI_Model
{
    protected $table = "seminar";
    protected $table_view = "proposal_mahasiswa_v"; // View untuk data proposal lengkap

    /**
     * Mengambil semua data seminar untuk ditampilkan di DataTables.
     *
     * @param array $input Filter data, contoh: ['mahasiswa_id' => 1]
     * @return array Hasil yang siap di-encode ke JSON.
     */
    public function index($input)
    {
        $this->db->select('
            seminar.id, seminar.proposal_mahasiswa_id, seminar.tanggal, seminar.jam,
            seminar.tempat, seminar.file_proposal, seminar.bukti_konsultasi,
            seminar.persetujuan, seminar.sk_tim,
            v.judul as proposal_mahasiswa_judul, v.nama_mahasiswa, v.nim, v.nama_prodi,
            hs.status as hasil_seminar_status
        ');
        $this->db->from($this->table . ' as seminar');
        $this->db->join('hasil_seminar as hs', 'hs.seminar_id = seminar.id', 'left');
        $this->db->join($this->table_view . ' as v', 'v.id = seminar.proposal_mahasiswa_id', 'left');

        // Filter berdasarkan mahasiswa jika ada
        if (!empty($input['mahasiswa_id'])) {
            $this->db->where('v.mahasiswa_id', $input['mahasiswa_id']);
        }

        $seminar = $this->db->order_by('seminar.id', 'DESC')->get()->result_array();

        // Selalu kembalikan struktur yang konsisten, bahkan saat data kosong
        return [
            'error'   => false,
            'message' => $seminar ? "Data berhasil ditemukan." : "Data tidak tersedia.",
            'data'    => $seminar,
        ];
    }

    /**
     * Menyimpan data seminar baru.
     * PERBAIKAN: Membuat nama file unik untuk setiap upload.
     *
     * @param array $input Data dari form.
     * @return array Hasil operasi.
     */
    public function create($input)
    {
        $data = [
            'proposal_mahasiswa_id' => $input['proposal_mahasiswa_id'] ?? null,
            'tanggal'               => $input['tanggal'] ?? null,
            'jam'                   => $input['jam'] ?? null,
            'tempat'                => $input['tempat'] ?? null,
            'file_proposal'         => $input['file_proposal'] ?? null,
            'sk_tim'                => $input['sk_tim'] ?? null,
            'persetujuan'           => $input['persetujuan'] ?? null,
            'bukti_konsultasi'      => $input['bukti_konsultasi'] ?? null,
        ];

        // Validasi sesuai pola repositori
        $validation = $this->app->validate($data);
        if ($validation !== true) {
            return $validation; // Kembalikan error validasi
        }

        try {
            // Helper function untuk menyimpan file base64
            $save_base64_file = function ($base64_string, $folder, $prefix) {
                if (empty($base64_string) || !str_contains($base64_string, ';base64,')) return null;
                $file_data = explode(';base64,', $base64_string)[1];
                $file_name = $prefix . '_' . uniqid() . '.pdf';
                $path = FCPATH . 'cdn/vendor/' . $folder . '/' . $file_name;
                file_put_contents($path, base64_decode($file_data));
                return $file_name;
            };

            // Simpan setiap file dengan nama unik
            $data['file_proposal']    = $save_base64_file($data['file_proposal'], 'file_proposal', 'proposal');
            $data['sk_tim']           = $save_base64_file($data['sk_tim'], 'sk_tim', 'sktim');
            $data['bukti_konsultasi'] = $save_base64_file($data['bukti_konsultasi'], 'bukti_konsultasi', 'konsultasi');
            $data['persetujuan']      = $save_base64_file($data['persetujuan'], 'persetujuan', 'persetujuan');

            $this->db->insert($this->table, $data);
            $data_id = $this->db->insert_id();

            // Masukkan record ke hasil_seminar
            $this->db->insert("hasil_seminar", [
                'seminar_id' => $data_id,
                'status'     => '3' // Status default 'Proses'
            ]);

            return ['error' => false, 'message' => "Data seminar berhasil ditambahkan."];

        } catch (Exception $e) {
            return ['error' => true, 'message' => 'Gagal menyimpan file: ' . $e->getMessage()];
        }
    }

    /**
     * Menghapus data seminar beserta file-file terkait.
     *
     * @param int $id ID Seminar
     * @return array Hasil operasi.
     */
    public function destroy($id)
    {
        $seminar = $this->db->get_where($this->table, ['id' => $id])->row_array();
        if (!$seminar) {
            return ['error' => true, 'message' => "Data seminar tidak ditemukan."];
        }

        // Helper untuk menghapus file dengan aman
        $delete_file = function($path, $filename) {
            if ($filename && file_exists($path . $filename)) {
                unlink($path . $filename);
            }
        };

        // Hapus semua file terkait dari server
        $delete_file(FCPATH . 'cdn/vendor/file_proposal/', $seminar['file_proposal']);
        $delete_file(FCPATH . 'cdn/vendor/sk_tim/', $seminar['sk_tim']);
        $delete_file(FCPATH . 'cdn/vendor/bukti_konsultasi/', $seminar['bukti_konsultasi']);
        $delete_file(FCPATH . 'cdn/vendor/persetujuan/', $seminar['persetujuan']);

        // Hapus file dari hasil_seminar jika ada
        $hasil_seminar = $this->db->get_where('hasil_seminar', ['seminar_id' => $id])->row_array();
        if ($hasil_seminar) {
            $delete_file(FCPATH . 'cdn/vendor/berita_acara/', $hasil_seminar['berita_acara']);
            $delete_file(FCPATH . 'cdn/vendor/masukan/', $hasil_seminar['masukan']);
        }

        // Hapus record dari database
        $this->db->delete("hasil_seminar", ['seminar_id' => $id]);
        $this->db->delete($this->table, ['id' => $id]);

        return ['error' => false, 'message' => "Data seminar berhasil dihapus."];
    }

    // Metode 'details' tidak ada di file asli, jadi tidak disertakan.
    // Jika diperlukan, bisa ditambahkan dengan pola yang sama.
}