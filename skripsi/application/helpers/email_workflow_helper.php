<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Email Helper untuk Workflow SIM Tugas Akhir
 * File: application/helpers/email_workflow_helper.php
 */

if (!function_exists('kirim_email_notifikasi')) {
    function kirim_email_notifikasi($CI, $jenis, $data) {
        // Setup email configuration
        $config = [
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_user' => 'stkyakobus@gmail.com',
            'smtp_pass' => 'yonroxhraathnaug',
            'charset' => 'utf-8',
            'newline' => "\r\n",
            'mailtype' => 'html',
            'smtp_crypto' => 'tls',
            'smtp_timeout' => 30
        ];
        
        $CI->email->initialize($config);
        
        switch ($jenis) {
            case 'proposal_disetujui_kaprodi':
                return kirim_email_proposal_disetujui_kaprodi($CI, $data);
                break;
                
            case 'proposal_ditolak_kaprodi':
                return kirim_email_proposal_ditolak_kaprodi($CI, $data);
                break;
                
            case 'pembimbing_ditunjuk':
                return kirim_email_pembimbing_ditunjuk($CI, $data);
                break;
                
            case 'pembimbing_menyetujui':
                return kirim_email_pembimbing_menyetujui($CI, $data);
                break;
                
            case 'pembimbing_menolak':
                return kirim_email_pembimbing_menolak($CI, $data);
                break;
                
            default:
                return false;
        }
    }
}

if (!function_exists('kirim_email_proposal_disetujui_kaprodi')) {
    function kirim_email_proposal_disetujui_kaprodi($CI, $data) {
        $subject = '[SIM Tugas Akhir] Proposal Disetujui - Menunggu Persetujuan Pembimbing';
        
        $message = template_email_header();
        $message .= "
        <tr>
            <td style='padding: 40px 30px;'>
                <h2 style='color: #28a745; margin: 0 0 20px 0; font-size: 24px;'>
                    ✅ Proposal Disetujui
                </h2>
                
                <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                    Yth. <strong>{$data['nama_mahasiswa']}</strong>,
                </p>
                
                <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                    Selamat! Proposal skripsi Anda telah <strong>DISETUJUI</strong> oleh Ketua Program Studi.
                </p>
                
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #495057; margin: 0 0 15px 0; font-size: 18px;'>Detail Proposal:</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold; width: 30%;'>ID Proposal:</td>
                            <td style='padding: 8px 0;'>#{$data['proposal_id']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>NIM:</td>
                            <td style='padding: 8px 0;'>{$data['nim']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Judul:</td>
                            <td style='padding: 8px 0;'>{$data['judul']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Dosen Pembimbing:</td>
                            <td style='padding: 8px 0;'>{$data['nama_pembimbing']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Tanggal Disetujui:</td>
                            <td style='padding: 8px 0;'>{$data['tanggal_disetujui']}</td>
                        </tr>
                    </table>
                </div>
                
                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h4 style='color: #856404; margin: 0 0 10px 0; font-size: 16px;'>
                        🔄 Status Saat Ini: Menunggu Persetujuan Dosen Pembimbing
                    </h4>
                    <p style='margin: 0; color: #856404; font-size: 14px;'>
                        Dosen pembimbing telah ditetapkan dan akan segera memberikan persetujuan untuk memulai proses bimbingan.
                    </p>
                </div>
                
                " . ($data['komentar_kaprodi'] ? "
                <div style='background-color: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h4 style='color: #0056b3; margin: 0 0 10px 0; font-size: 16px;'>💬 Catatan dari Kaprodi:</h4>
                    <p style='margin: 0; color: #0056b3; font-style: italic;'>{$data['komentar_kaprodi']}</p>
                </div>
                " : "") . "
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . base_url('mahasiswa/proposal') . "' 
                       style='background-color: #007bff; color: white; padding: 12px 30px; 
                              text-decoration: none; border-radius: 5px; font-weight: bold;'>
                        📱 Pantau Status di Sistem
                    </a>
                </div>
                
                <p style='margin: 20px 0 0 0; font-size: 14px; color: #6c757d;'>
                    Terima kasih atas kesabaran Anda. Kami akan segera menginformasikan perkembangan selanjutnya.
                </p>
            </td>
        </tr>";
        $message .= template_email_footer();
        
        $CI->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
        $CI->email->to($data['email_mahasiswa']);
        $CI->email->subject($subject);
        $CI->email->message($message);
        
        return $CI->email->send();
    }
}

if (!function_exists('kirim_email_proposal_ditolak_kaprodi')) {
    function kirim_email_proposal_ditolak_kaprodi($CI, $data) {
        $subject = '[SIM Tugas Akhir] Proposal Memerlukan Perbaikan';
        
        $message = template_email_header();
        $message .= "
        <tr>
            <td style='padding: 40px 30px;'>
                <h2 style='color: #dc3545; margin: 0 0 20px 0; font-size: 24px;'>
                    📝 Proposal Memerlukan Perbaikan
                </h2>
                
                <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                    Yth. <strong>{$data['nama_mahasiswa']}</strong>,
                </p>
                
                <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                    Proposal skripsi Anda perlu dilakukan perbaikan sebelum dapat disetujui.
                </p>
                
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #495057; margin: 0 0 15px 0; font-size: 18px;'>Detail Proposal:</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold; width: 30%;'>ID Proposal:</td>
                            <td style='padding: 8px 0;'>#{$data['proposal_id']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>NIM:</td>
                            <td style='padding: 8px 0;'>{$data['nim']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Judul:</td>
                            <td style='padding: 8px 0;'>{$data['judul']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Tanggal Review:</td>
                            <td style='padding: 8px 0;'>{$data['tanggal_review']}</td>
                        </tr>
                    </table>
                </div>
                
                <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h4 style='color: #721c24; margin: 0 0 10px 0; font-size: 16px;'>💬 Komentar dan Saran Perbaikan:</h4>
                    <p style='margin: 0; color: #721c24;'>{$data['komentar_kaprodi']}</p>
                </div>
                
                <div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h4 style='color: #155724; margin: 0 0 10px 0; font-size: 16px;'>📋 Langkah Selanjutnya:</h4>
                    <ol style='color: #155724; margin: 0; padding-left: 20px;'>
                        <li>Perbaiki proposal sesuai komentar yang diberikan</li>
                        <li>Konsultasikan perbaikan dengan dosen pembimbing (jika ada)</li>
                        <li>Upload proposal yang sudah diperbaiki ke sistem</li>
                        <li>Ajukan kembali untuk review</li>
                    </ol>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . base_url('mahasiswa/proposal') . "' 
                       style='background-color: #007bff; color: white; padding: 12px 30px; 
                              text-decoration: none; border-radius: 5px; font-weight: bold;'>
                        📝 Ajukan Proposal Kembali
                    </a>
                </div>
                
                <p style='margin: 20px 0 0 0; font-size: 14px; color: #6c757d;'>
                    Jangan berkecil hati! Proses perbaikan ini adalah bagian normal dari pengembangan proposal yang baik.
                </p>
            </td>
        </tr>";
        $message .= template_email_footer();
        
        $CI->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
        $CI->email->to($data['email_mahasiswa']);
        $CI->email->subject($subject);
        $CI->email->message($message);
        
        return $CI->email->send();
    }
}

if (!function_exists('kirim_email_pembimbing_ditunjuk')) {
    function kirim_email_pembimbing_ditunjuk($CI, $data) {
        $subject = '[SIM Tugas Akhir] Penunjukan sebagai Dosen Pembimbing';
        
        $message = template_email_header();
        $message .= "
        <tr>
            <td style='padding: 40px 30px;'>
                <h2 style='color: #007bff; margin: 0 0 20px 0; font-size: 24px;'>
                    👨‍🏫 Penunjukan Dosen Pembimbing
                </h2>
                
                <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                    Yth. <strong>{$data['nama_dosen']}</strong>,
                </p>
                
                <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                    Anda telah ditunjuk sebagai <strong>Dosen Pembimbing</strong> untuk mahasiswa berikut:
                </p>
                
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #495057; margin: 0 0 15px 0; font-size: 18px;'>Data Mahasiswa:</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold; width: 30%;'>Nama:</td>
                            <td style='padding: 8px 0;'>{$data['nama_mahasiswa']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>NIM:</td>
                            <td style='padding: 8px 0;'>{$data['nim']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Program Studi:</td>
                            <td style='padding: 8px 0;'>{$data['nama_prodi']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Email:</td>
                            <td style='padding: 8px 0;'>{$data['email_mahasiswa']}</td>
                        </tr>
                    </table>
                </div>
                
                <div style='background-color: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h4 style='color: #0056b3; margin: 0 0 10px 0; font-size: 16px;'>📚 Judul Proposal:</h4>
                    <p style='margin: 0; color: #0056b3; font-weight: bold;'>{$data['judul']}</p>
                </div>
                
                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h4 style='color: #856404; margin: 0 0 10px 0; font-size: 16px;'>⏳ Perlu Tindakan:</h4>
                    <p style='margin: 0; color: #856404;'>
                        Silakan login ke sistem untuk memberikan <strong>persetujuan atau penolakan</strong> 
                        terhadap penunjukan ini dalam waktu <strong>3 hari kerja</strong>.
                    </p>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . base_url('dosen/bimbingan') . "' 
                       style='background-color: #28a745; color: white; padding: 12px 30px; 
                              text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;'>
                        ✅ Setujui Pembimbingan
                    </a>
                    <a href='" . base_url('dosen/bimbingan') . "' 
                       style='background-color: #dc3545; color: white; padding: 12px 30px; 
                              text-decoration: none; border-radius: 5px; font-weight: bold;'>
                        ❌ Tolak Pembimbingan
                    </a>
                </div>
                
                <p style='margin: 20px 0 0 0; font-size: 14px; color: #6c757d;'>
                    Terima kasih atas kesediaan Bapak/Ibu untuk membimbing mahasiswa kami.
                </p>
            </td>
        </tr>";
        $message .= template_email_footer();
        
        $CI->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
        $CI->email->to($data['email_dosen']);
        $CI->email->subject($subject);
        $CI->email->message($message);
        
        return $CI->email->send();
    }
}

if (!function_exists('template_email_header')) {
    function template_email_header() {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>SIM Tugas Akhir STK St. Yakobus</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;'>
            <table style='width: 100%; background-color: #f4f4f4; margin: 0; padding: 20px 0;'>
                <tr>
                    <td style='text-align: center;'>
                        <table style='width: 600px; background-color: white; margin: 0 auto; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                            <tr>
                                <td style='background-color: #007bff; padding: 20px; text-align: center;'>
                                    <h1 style='color: white; margin: 0; font-size: 24px;'>
                                        🎓 SIM Tugas Akhir
                                    </h1>
                                    <p style='color: white; margin: 5px 0 0 0; font-size: 14px;'>
                                        STK Santo Yakobus Merauke
                                    </p>
                                </td>
                            </tr>";
    }
}

if (!function_exists('template_email_footer')) {
    function template_email_footer() {
        return "
                            <tr>
                                <td style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6;'>
                                    <p style='margin: 0 0 10px 0; font-size: 14px; color: #6c757d;'>
                                        <strong>STK Santo Yakobus Merauke</strong><br>
                                        Jl. Missi 2, Mandala, Merauke, Papua Selatan<br>
                                        Telp: (0971) 3330264 | Email: sipd@stkyakobus.ac.id
                                    </p>
                                    <p style='margin: 0; font-size: 12px; color: #adb5bd;'>
                                        Email ini dikirim otomatis oleh sistem. Mohon tidak membalas email ini.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
    }
}

/* End of file email_workflow_helper.php */
/* Location: ./application/helpers/email_workflow_helper.php */