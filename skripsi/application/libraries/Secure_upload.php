<?php
// ==========================================
// FILE: application/libraries/Secure_upload.php
// ==========================================

defined('BASEPATH') OR exit('No direct script access allowed');

class Secure_upload {
    
    private $CI;
    private $allowed_mime_types = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    private $max_file_size = 5242880; // 5MB
    
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('upload');
    }
    
    /**
     * Secure file upload dengan validasi lengkap
     */
    public function secure_upload($field_name, $upload_path, $file_prefix = 'proposal_') {
        
        // 1. Validasi file exists
        if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
            return ['status' => false, 'error' => 'File tidak valid atau gagal diupload'];
        }
        
        $file = $_FILES[$field_name];
        
        // 2. Validasi ukuran file
        if ($file['size'] > $this->max_file_size) {
            return ['status' => false, 'error' => 'Ukuran file terlalu besar. Maksimal 5MB'];
        }
        
        // 3. Validasi MIME type (bukan hanya ekstensi)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $this->allowed_mime_types)) {
            return ['status' => false, 'error' => 'Tipe file tidak diizinkan. Hanya PDF, DOC, DOCX'];
        }
        
        // 4. Validasi ekstensi file
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'doc', 'docx'];
        
        if (!in_array($file_ext, $allowed_extensions)) {
            return ['status' => false, 'error' => 'Ekstensi file tidak diizinkan'];
        }
        
        // 5. Scan virus (jika ClamAV tersedia)
        if ($this->_is_infected($file['tmp_name'])) {
            return ['status' => false, 'error' => 'File terdeteksi mengandung virus'];
        }
        
        // 6. Generate secure filename
        $secure_filename = $this->_generate_secure_filename($file_prefix, $file_ext);
        
        // 7. Ensure upload directory exists dan secure
        $this->_secure_upload_directory($upload_path);
        
        // 8. Move file dengan validasi final
        $final_path = $upload_path . $secure_filename;
        
        if (move_uploaded_file($file['tmp_name'], $final_path)) {
            
            // 9. Set file permissions
            chmod($final_path, 0644);
            
            // 10. Log upload activity
            log_message('info', "Secure file upload: {$secure_filename} by user " . 
                       ($this->CI->session->userdata('id') ?? 'unknown'));
            
            return [
                'status' => true, 
                'filename' => $secure_filename,
                'path' => $final_path,
                'size' => $file['size'],
                'original_name' => $file['name']
            ];
            
        } else {
            return ['status' => false, 'error' => 'Gagal menyimpan file ke server'];
        }
    }
    
    /**
     * Generate secure filename
     */
    private function _generate_secure_filename($prefix, $extension) {
        $timestamp = date('YmdHis');
        $random = bin2hex(random_bytes(8));
        $user_id = $this->CI->session->userdata('id') ?? 'guest';
        
        return $prefix . $timestamp . '_' . $user_id . '_' . $random . '.' . $extension;
    }
    
    /**
     * Secure upload directory
     */
    private function _secure_upload_directory($path) {
        // Create directory if not exists
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        // Create .htaccess untuk prevent direct access
        $htaccess_file = $path . '.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "deny from all\n";
            $htaccess_content .= "<Files ~ \"\\.(pdf|doc|docx)$\">\n";
            $htaccess_content .= "    Order allow,deny\n";
            $htaccess_content .= "    Allow from all\n";
            $htaccess_content .= "</Files>";
            
            file_put_contents($htaccess_file, $htaccess_content);
        }
        
        // Create index.php untuk prevent directory listing
        $index_file = $path . 'index.php';
        if (!file_exists($index_file)) {
            file_put_contents($index_file, "<?php\nheader('HTTP/1.0 403 Forbidden');\nexit('Access denied');");
        }
    }
    
    /**
     * Simple virus scan (jika ClamAV available)
     */
    private function _is_infected($file_path) {
        // Check if ClamAV is available
        if (!function_exists('exec')) {
            return false; // Skip virus scan jika exec disabled
        }
        
        $output = [];
        $return_code = 0;
        
        // Try ClamAV scan
        exec("clamscan --no-summary --infected {$file_path} 2>/dev/null", $output, $return_code);
        
        // Return code 1 means infected
        return ($return_code === 1);
    }
    
    /**
     * Validate file content untuk dokumen
     */
    public function validate_document_content($file_path, $file_type) {
        try {
            switch ($file_type) {
                case 'pdf':
                    return $this->_validate_pdf($file_path);
                case 'doc':
                case 'docx':
                    return $this->_validate_word($file_path);
                default:
                    return ['status' => true]; // Pass jika tidak ada validator
            }
        } catch (Exception $e) {
            log_message('error', 'File validation error: ' . $e->getMessage());
            return ['status' => false, 'error' => 'Error validating file content'];
        }
    }
    
    private function _validate_pdf($file_path) {
        // Basic PDF validation - check PDF header
        $handle = fopen($file_path, 'r');
        $header = fread($handle, 8);
        fclose($handle);
        
        if (strpos($header, '%PDF-') !== 0) {
            return ['status' => false, 'error' => 'File PDF tidak valid'];
        }
        
        return ['status' => true];
    }
    
    private function _validate_word($file_path) {
        // Basic Word document validation
        $file_size = filesize($file_path);
        
        // Word documents should have minimum size
        if ($file_size < 1024) { // Less than 1KB suspicious
            return ['status' => false, 'error' => 'Dokumen Word terlalu kecil atau corrupt'];
        }
        
        return ['status' => true];
    }
}