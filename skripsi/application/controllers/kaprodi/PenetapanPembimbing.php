<?php
/**
 * PERBAIKAN EMAIL FORMAL - PENUNJUKAN DOSEN PEMBIMBING
 * 
 * File: application/controllers/kaprodi/PenetapanPembimbing.php
 * 
 * Ganti method _kirim_email_dosen_pembimbing() dengan kode di bawah ini
 * untuk format email yang lebih formal dan konsisten
 */

private function _kirim_email_dosen_pembimbing($proposal, $dosen) {
    if (!$dosen) return false;
    
    // Validasi data sebelum kirim email
    if (!$this->_validate_email_data($proposal, $dosen)) {
        log_message('error', 'Email validation failed for proposal ID: ' . $proposal->id);
        return false;
    }
    
    $config = [
        'protocol' => 'smtp',
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_user' => 'stkyakobus@gmail.com',
        'smtp_pass' => 'yonroxhraathnaug',
        'charset' => 'utf-8',
        'newline' => "\r\n",
        'mailtype' => 'html',
        'smtp_crypto' => 'tls'
    ];
    
    $this->email->initialize($config);
    
    $subject = 'Penunjukan sebagai Dosen Pembimbing - ' . $proposal->nama_mahasiswa;
    
    // Ambil nama kaprodi dari session
    $kaprodi_nama = $this->session->userdata('nama');
    
    // Ambil nama prodi (pastikan tersedia)
    $nama_prodi = isset($proposal->nama_prodi) ? $proposal->nama_prodi : 'Program Studi';
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Penunjukan sebagai Dosen Pembimbing</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
            
            <!-- Header -->
            <div style='text-align: center; background-color: #28a745; color: white; padding: 20px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px;'>
                <h2 style='margin: 0;'>🎓 Penunjukan sebagai Dosen Pembimbing</h2>
            </div>
            
            <p style='margin: 0 0 20px 0; font-size: 16px;'>
                Yth. <strong>{$dosen->nama}</strong>,<br>
                S.Pd., M.Pd.
            </p>
            
            <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                Dengan hormat, melalui email ini kami sampaikan bahwa Bapak/Ibu telah <strong>ditunjuk sebagai Dosen Pembimbing</strong> 
                untuk mahasiswa dalam proses penyusunan Tugas Akhir (Skripsi).
            </p>
            
            <!-- Data Mahasiswa -->
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #495057; margin: 0 0 15px 0; font-size: 18px;'>📚 Data Mahasiswa:</h3>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; width: 30%; border-bottom: 1px solid #dee2e6;'>Nama:</td>
                        <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6;'>{$proposal->nama_mahasiswa}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; border-bottom: 1px solid #dee2e6;'>NIM:</td>
                        <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6;'>{$proposal->nim}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; border-bottom: 1px solid #dee2e6;'>Program Studi:</td>
                        <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6;'>{$nama_prodi}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold;'>Email Mahasiswa:</td>
                        <td style='padding: 8px 0;'>{$proposal->email_mahasiswa}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Judul Proposal -->
            <div style='background-color: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                <h4 style='color: #0056b3; margin: 0 0 10px 0; font-size: 16px;'>📖 Judul Proposal:</h4>
                <p style='margin: 0; color: #0056b3; font-weight: bold; font-style: italic;'>\"{$proposal->judul}\"</p>
            </div>
            
            <!-- Informasi Tugas -->
            <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                <h4 style='color: #856404; margin: 0 0 10px 0; font-size: 16px;'>📋 Tugas Sebagai Pembimbing:</h4>
                <ul style='color: #856404; margin: 0; padding-left: 20px;'>
                    <li>Membimbing mahasiswa dalam penyusunan proposal dan skripsi</li>
                    <li>Memberikan arahan akademik dan metodologi penelitian</li>
                    <li>Memvalidasi jurnal bimbingan melalui sistem</li>
                    <li>Memberikan rekomendasi untuk seminar proposal dan akhir</li>
                </ul>
            </div>
            
            <!-- Call to Action -->
            <div style='text-align: center; margin: 30px 0;'>
                <p style='margin: 0 0 15px 0; font-size: 16px; color: #dc3545; font-weight: bold;'>
                    ⚠️ Silakan memberikan persetujuan melalui sistem:
                </p>
                <a href='" . base_url('dosen/usulan_proposal') . "' 
                   style='background-color: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>
                   🔗 Login Sistem & Berikan Persetujuan
                </a>
            </div>
            
            <!-- Workflow Info -->
            <div style='background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                <h4 style='color: #0c5460; margin: 0 0 10px 0; font-size: 16px;'>🔄 Langkah Selanjutnya:</h4>
                <ol style='color: #0c5460; margin: 0; padding-left: 20px;'>
                    <li><strong>Login ke sistem</strong> menggunakan link di atas</li>
                    <li><strong>Tinjau proposal</strong> dan data mahasiswa</li>
                    <li><strong>Berikan persetujuan</strong> atau penolakan dengan alasan</li>
                    <li>Jika disetujui, <strong>mulai proses bimbingan</strong> dengan mahasiswa</li>
                </ol>
            </div>
            
            <!-- Contact Info -->
            <div style='background-color: #f1f3f4; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                <h4 style='color: #495057; margin: 0 0 10px 0; font-size: 16px;'>📞 Informasi Kontak:</h4>
                <p style='margin: 0; color: #495057;'>
                    <strong>Mahasiswa:</strong> {$proposal->email_mahasiswa}<br>
                    <strong>Kaprodi:</strong> {$kaprodi_nama}<br>
                    <strong>Program Studi:</strong> {$nama_prodi}
                </p>
            </div>
            
            <!-- Penutup -->
            <p style='margin: 20px 0; font-size: 16px; line-height: 1.5;'>
                Demikian pemberitahuan ini kami sampaikan. Atas perhatian dan kesediaan Bapak/Ibu untuk membimbing mahasiswa, 
                kami ucapkan terima kasih.
            </p>
            
            <p style='margin: 20px 0 30px 0; font-size: 16px;'>
                Hormat kami,<br>
                <strong>{$kaprodi_nama}</strong><br>
                Ketua Program Studi {$nama_prodi}<br>
                STK Santo Yakobus Merauke
            </p>
            
            <!-- Footer -->
            <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6; margin: 20px -20px -20px -20px; border-radius: 0 0 8px 8px;'>
                <p style='margin: 0; font-size: 12px; color: #6c757d;'>
                    Email ini dikirim secara otomatis oleh<br>
                    <strong>Sistem Informasi Manajemen Tugas Akhir</strong><br>
                    STK Santo Yakobus Merauke<br>
                    <em>Mohon tidak membalas email ini</em>
                </p>
            </div>
        </div>
    </body>
    </html>";
    
    try {
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
        $this->email->to($dosen->email);
        $this->email->subject($subject);
        $this->email->message($message);
        
        $result = $this->email->send();
        
        if ($result) {
            log_message('info', 'Email berhasil dikirim ke dosen: ' . $dosen->email . ' untuk proposal ID: ' . $proposal->id);
        } else {
            log_message('error', 'Email gagal dikirim ke dosen: ' . $dosen->email . ' - ' . $this->email->print_debugger());
        }
        
        return $result;
        
    } catch (Exception $e) {
        log_message('error', 'Exception saat kirim email ke dosen: ' . $e->getMessage());
        return false;
    }
}

/**
 * PERBAIKI JUGA METHOD _kirim_notifikasi UNTUK MEMASTIKAN DATA LENGKAP
 * Ganti method _kirim_notifikasi() di PenetapanPembimbing.php dengan ini:
 */
private function _kirim_notifikasi($proposal_id, $dosen_pembimbing_id, $dosen_penguji1_id, $dosen_penguji2_id) {
    // Ambil data proposal dan mahasiswa DENGAN JOIN KE PRODI
    $proposal = $this->db->select('
            pm.*, 
            m.nama as nama_mahasiswa, 
            m.nim, 
            m.email as email_mahasiswa,
            p.nama as nama_prodi
        ')
        ->from('proposal_mahasiswa pm')
        ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
        ->join('prodi p', 'm.prodi_id = p.id') // PASTIKAN JOIN KE PRODI
        ->where('pm.id', $proposal_id)
        ->get()->row();
    
    if (!$proposal) {
        log_message('error', 'Proposal tidak ditemukan untuk ID: ' . $proposal_id);
        return false;
    }
    
    // Ambil data dosen
    $dosen_pembimbing = $this->db->get_where('dosen', ['id' => $dosen_pembimbing_id])->row();
    $dosen_penguji1 = $this->db->get_where('dosen', ['id' => $dosen_penguji1_id])->row();
    $dosen_penguji2 = $this->db->get_where('dosen', ['id' => $dosen_penguji2_id])->row();
    
    try {
        // Kirim notifikasi ke mahasiswa
        $this->_kirim_email_mahasiswa($proposal, $dosen_pembimbing, $dosen_penguji1, $dosen_penguji2);
        
        // Kirim notifikasi ke dosen pembimbing - MENGGUNAKAN METHOD YANG SUDAH DIPERBAIKI
        $result_pembimbing = $this->_kirim_email_dosen_pembimbing($proposal, $dosen_pembimbing);
        
        // Kirim notifikasi ke dosen penguji
        $this->_kirim_email_dosen_penguji($proposal, $dosen_penguji1, 'Penguji 1');
        $this->_kirim_email_dosen_penguji($proposal, $dosen_penguji2, 'Penguji 2');
        
        log_message('info', 'Notifikasi penetapan pembimbing berhasil dikirim untuk proposal ID: ' . $proposal_id);
        return true;
        
    } catch (Exception $e) {
        log_message('error', 'Error saat kirim notifikasi: ' . $e->getMessage());
        return false;
    }
}

/**
 * TAMBAHKAN METHOD ERROR HANDLING UNTUK EMAIL INI JUGA DI CONTROLLER YANG SAMA
 */
private function _handle_email_error($error_message, $proposal_id = null, $dosen_email = null) {
    $context = '';
    if ($proposal_id) $context .= "Proposal ID: $proposal_id ";
    if ($dosen_email) $context .= "Dosen Email: $dosen_email ";
    
    log_message('error', "Email Error [$context]: $error_message");
    
    // Bisa ditambahkan notifikasi ke admin atau system monitor di sini
    // contoh: kirim email ke admin tentang kegagalan sistem
}
private function _validate_email_data($proposal, $dosen) {
    $required_fields = [
        'proposal' => ['nama_mahasiswa', 'nim', 'judul', 'email_mahasiswa'],
        'dosen' => ['nama', 'email']
    ];
    
    foreach ($required_fields['proposal'] as $field) {
        if (empty($proposal->$field)) {
            log_message('error', "Email validation failed: Missing proposal field '$field'");
            return false;
        }
    }
    
    foreach ($required_fields['dosen'] as $field) {
        if (empty($dosen->$field)) {
            log_message('error', "Email validation failed: Missing dosen field '$field'");
            return false;
        }
    }
    
    return true;
}

/**
 * BONUS: Method untuk validasi data sebelum kirim email
 * Tambahkan method ini di controller yang sama
 */
private function _validate_email_data($proposal, $dosen) {
    $required_fields = [
        'proposal' => ['nama_mahasiswa', 'nim', 'judul', 'email_mahasiswa', 'nama_prodi'],
        'dosen' => ['nama', 'email']
    ];
    
    foreach ($required_fields['proposal'] as $field) {
        if (empty($proposal->$field)) {
            log_message('error', "Email validation failed: Missing proposal field '$field'");
            return false;
        }
    }
    
    foreach ($required_fields['dosen'] as $field) {
        if (empty($dosen->$field)) {
            log_message('error', "Email validation failed: Missing dosen field '$field'");
            return false;
        }
    }
    
    return true;
}

/**
 * ENHANCED: Method kirim email dengan error handling
 * Ganti pemanggilan _kirim_email_dosen_pembimbing() dengan method ini
 */
private function _kirim_email_dosen_pembimbing_safe($proposal, $dosen) {
    if (!$this->_validate_email_data($proposal, $dosen)) {
        log_message('error', 'Email not sent: Invalid data');
        return false;
    }
    
    try {
        return $this->_kirim_email_dosen_pembimbing($proposal, $dosen);
    } catch (Exception $e) {
        log_message('error', 'Email sending failed: ' . $e->getMessage());
        return false;
    }
}