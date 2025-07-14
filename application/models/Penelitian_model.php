<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Penelitian_model extends CI_Model
{

	protected $table = "penelitian";

	public function index($input)
	{
		$this->db->select("
			penelitian.id,
			penelitian.proposal_mahasiswa_id,
			penelitian.judul_penelitian,
			penelitian.bukti,
			penelitian.file_seminar,
			penelitian.sk_tim,
			penelitian.bukti_konsultasi,
			proposal_mahasiswa_v.judul as proposal_mahasiswa_judul,
			proposal_mahasiswa_v.nim,
			proposal_mahasiswa_v.nama_mahasiswa,
			proposal_mahasiswa_v.nama_prodi,
		");

		$this->db->from($this->table);
		$this->db->join('proposal_mahasiswa_v', 'proposal_mahasiswa_v.id = penelitian.proposal_mahasiswa_id', 'left');

		$kondisi = [];
		if ($input['mahasiswa_id']) {
			$kondisi['proposal_mahasiswa_v.mahasiswa_id'] = $input['mahasiswa_id'];
		}

		if ($kondisi) {
			$this->db->where($kondisi);
		}

		$penelitian = $this->db->get()->result_array();

		$hasil = [
			'error' => false,
			'message' => ($penelitian) ? "data berhasil ditemukan" : "data tidak tersedia",
			'data' => $penelitian
		];

		return $hasil;
	}

	public function create($input)
	{
		$data = [
			'proposal_mahasiswa_id' => $input['proposal_mahasiswa_id'],
			'judul_penelitian' => $input['judul_penelitian'],
			'pembimbing_id' => $input['pembimbing_id'],
			'penguji_id' => $input['penguji_id'],
			'bukti' => $input['bukti'],
			'sk_tim' => $input['sk_tim'],
			'file_seminar' => $input['file_seminar'],
			'bukti_konsultasi' => $input['bukti_konsultasi'],
			'persetujuan_pembimbing' => '2',
			'persetujuan_penguji' => '2'
		];

		$validation = $this->app->validate($data);

		if ($validation === true) {
			$bukti_file = explode(';base64,', $data['bukti'])[1];
			$bukti_nama = date('Ymdhis') . '.pdf';
			file_put_contents(FCPATH . 'cdn/vendor/penelitian/' . $bukti_nama, base64_decode($bukti_file));
			$data['bukti'] = $bukti_nama;

			$file_seminar_file = explode(';base64,', $data['file_seminar'])[1];
			$file_seminar_nama = date('Ymdhis') . '.pdf';
			file_put_contents(FCPATH . 'cdn/vendor/penelitian/file_seminar/' . $file_seminar_nama, base64_decode($file_seminar_file));
			$data['file_seminar'] = $file_seminar_nama;

			$sk_tim_file = explode(';base64,', $data['sk_tim'])[1];
			$sk_tim_nama = date('Ymdhis') . '.pdf';
			file_put_contents(FCPATH . 'cdn/vendor/penelitian/sk_tim/' . $sk_tim_nama, base64_decode($sk_tim_file));
			$data['sk_tim'] = $sk_tim_nama;

			$bukti_konsultasi_file = explode(';base64,', $data['bukti_konsultasi'])[1];
			$bukti_konsultasi_nama = date('Ymdhis') . '.pdf';
			file_put_contents(FCPATH . 'cdn/vendor/penelitian/bukti_konsultasi/' . $bukti_konsultasi_nama, base64_decode($bukti_konsultasi_file));
			$data['bukti_konsultasi'] = $bukti_konsultasi_nama;

			$this->db->insert($this->table, $data);
			$data_id = $this->db->insert_id();

			$this->db->insert('hasil_penelitian', [
				'penelitian_id' => $data_id,
				'berita_acara' => '',
				'masukan' => '',
				'status' => "2"
			]);

			$hasil = [
				'error' => false,
				'message' => "data berhasil ditambah"
			];
		} else {
			$hasil = $validation;
		}

		return $hasil;
	}

	public function destroy($id)
	{
		$kondisi = [
			'id' => $id
		];

		$penelitian = $this->db->get_where($this->table, ['id' => $id])->row_array();
		$hasil_penelitian = $this->db->get_where('hasil_penelitian', ['penelitian_id' => $id])->result_array();

		foreach ($hasil_penelitian as $key => $item) {
			if ($item['berita_acara']) {
				unlink(FCPATH . 'cdn/vendor/berita_acara/' . $item['berita_acara']);
			}
			if ($item['masukan']) {
				unlink(FCPATH . 'cdn/vendor/masukan/' . $item['masukan']);
			}
		}

		unlink(FCPATH . 'cdn/vendor/penelitian/' . $penelitian['bukti']);

		if ($penelitian['sk_tim']) {
			unlink(FCPATH . 'cdn/vendor/sk_tim/' . $penelitian['sk_tim']);
		}

		$this->db->delete("hasil_penelitian", ['penelitian_id' => $id]);
		$this->db->delete($this->table, ['id' => $id]);

		$hasil = [
			'error' => false,
			'message' => "data berhasil dihapus"
		];

		return $hasil;
	}

	public function details($id)
	{
		$kondisi = [
			'id' => $id
		];

		$penelitian = $this->db->get_where($this->table, ['id' => $id])->row_array();

		$hasil = [
			'error' => ($penelitian) ? false : true,
			'message' => ($penelitian) ? "data berhasil ditemukan" : "data tidak ditemukan",
			'data' => $penelitian
		];

		if ($hasil['data']) {
			$hasil['data']['penguji'] = $this->db->get_where('dosen', ['id' => $hasil['data']['penguji_id']])->row_array();
			$hasil['data']['pembimbing'] = $this->db->get_where('dosen', ['id' => $hasil['data']['pembimbing_id']])->row_array();
			$hasil['data']['proposal'] = $this->db->get_where('proposal_mahasiswa_v', ['id' => $hasil['data']['proposal_mahasiswa_id']])->row_array();
			$hasil['data']['hasil'] = $this->db->get_where('hasil_penelitian', ['penelitian_id' => $hasil['data']['id']])->row_array();
		}

		return $hasil;
	}
}

/* End of file Penelitian_model.php */
/* Location: ./application/models/Penelitian_model.php */