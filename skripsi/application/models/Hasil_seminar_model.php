<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Hasil_seminar_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Email_model', 'emailm');
	}

	protected $table = "hasil_seminar";

	public function edit($input, $seminar_id)
	{
		$data = [
			'status' => $input['status']
		];

		$validation = $this->app->validate($data);

		if ($validation === true) {
			if ($input['berita_acara']) {
				$berita_acara_file = explode(';base64,', $input['berita_acara'])[1];
				$berita_acara_nama = date('Ymdhis') . '.pdf';
				file_put_contents(FCPATH . 'cdn/vendor/berita_acara/' . $berita_acara_nama, base64_decode($berita_acara_file));
				$data['berita_acara'] = $berita_acara_nama;
			}

			if ($input['masukan']) {
				$masukan_file = explode(';base64,', $input['masukan'])[1];
				$masukan_nama = date('Ymdhis') . '.pdf';
				file_put_contents(FCPATH . 'cdn/vendor/masukan/' . $masukan_nama, base64_decode($masukan_file));
				$data['masukan'] = $masukan_nama;
			}

			if ($this->db->update($this->table, $data, ['seminar_id' => $seminar_id])) {
				if ($input['status'] != $input['def_status']) {
					$email = $input['email'];
					$isi_email = '';
					$subjek_email = '';

					if ($input['status'] == 1) {
						$isi_email = '
						<p>Seminar proposal anda telah disetujui (Sempurna)</p>
						';
						$subjek_email = 'Seminar Proposal Dilanjutkan (Sempurna)';
					} elseif ($input['status'] == 2) {
						$isi_email = '
						<p>Lanjutkan perbaikan seminar proposal anda</p>
						';
						$subjek_email = 'Seminar Proposal Dilanjutkan (Perbaikan)';
					} elseif ($input['status'] == 3) {
						$isi_email = '
						<p>Seminar proposal anda ditolak</p>
						';
						$subjek_email = 'Seminar Proposal Ditolak';
					}
					$this->emailm->send($subjek_email, $email, $isi_email);
				}

				$hasil = [
					'error' => false,
					'message' => "data berhasil diedit"
				];
			}
		} else {
			$hasil = [
				'error' => true,
				'message' => "data tidak ditemukan"
			];
		}

		return $hasil;
	}
}

/* End of file Hasil_seminar_model.php */
/* Location: ./application/models/Hasil_seminar_model.php */