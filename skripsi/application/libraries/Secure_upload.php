<?php
/**
 * ==========================================
 * COMPLETE SECURE UPLOAD LIBRARY - ENHANCED VERSION
 * File: application/libraries/Secure_upload.php
 * ==========================================
 * 
 * 🔧 FIXED ISSUES:
 * - File size limit: 5MB → 1MB (sesuai workflow requirement)
 * - Consistent virus scan dengan multiple engines
 * - Enhanced error handling dengan retry mechanism
 * - Comprehensive security validation
 * 
 * 🔒 SECURITY FEATURES:
 * - Multi-engine virus scanning (ClamAV + pattern detection)
 * - File signature validation (prevent spoofing)
 * - MIME type validation (finfo-based)
 * - Content integrity analysis
 * - Secure directory protection
 * - Atomic file operations
 * 
 * @package     SIM_TA
 * @subpackage  Libraries
 * @category    Security
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.0 (Enhanced)
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Secure_upload {
    
    private $CI;
    
    // 🔧 FIXED: File size limit - 1MB sesuai workflow requirement
    private $max_file_size = 1048576; // 1MB (bukan 5MB)
    
    // Allowed MIME types untuk dokumen proposal
    private $allowed_mime_types = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    // Allowed file extensions
    private $allowed_extensions = ['pdf', 'doc', 'docx'];
    
    // Configuration
    private $config = [
        'enable_virus_scan' => true,
        'enable_content_validation' => true,
        'enable_retry' => true,
        'max_retries' => 3,
        'log_activities' => true
    ];
    
    public function __construct($config = []) {
        $this->CI =& get_instance();
        $this->CI->load->library('upload');
        
        // Merge custom config
        $this->config = array_merge($this->config, $config);
        
        log_message('info', 'Enhanced Secure_upload Library Initialized');
    }
    
    // =================================================================
    // 🔧 MAIN UPLOAD METHODS (Enhanced)
    // =================================================================
    
    /**
     * 🔧 ENHANCED: Main secure upload method dengan retry mechanism
     * 
     * @param string $field_name - Form field name
     * @param string $upload_path - Upload directory path
     * @param string $file_prefix - Filename prefix (default: 'proposal_')
     * @param int $retry_count - Number of retry attempts (default: 3)
     * @return array - Upload result with status and details
     */
    public function secure_upload($field_name, $upload_path, $file_prefix = 'proposal_', $retry_count = null) {
        $retry_count = $retry_count ?? $this->config['max_retries'];
        $last_error = '';
        $debug_info = [];
        
        for ($attempt = 1; $attempt <= $retry_count; $attempt++) {
            try {
                if ($this->config['log_activities']) {
                    log_message('info', "Secure upload attempt {$attempt}/{$retry_count} for field: {$field_name}");
                }
                
                // Execute single upload attempt
                $result = $this->_execute_single_upload($field_name, $upload_path, $file_prefix);
                
                if ($result['status']) {
                    // SUCCESS!
                    $result['attempts'] = $attempt;
                    $result['total_attempts'] = $retry_count;
                    
                    if ($this->config['log_activities']) {
                        log_message('info', "Secure upload SUCCESS on attempt {$attempt}: {$result['filename']}");
                    }
                    
                    return $result;
                } else {
                    throw new Exception($result['error']);
                }
                
            } catch (Exception $e) {
                $last_error = $e->getMessage();
                $debug_info[] = [
                    'attempt' => $attempt,
                    'error' => $last_error,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                
                log_message('error', "Secure upload attempt {$attempt} failed: {$last_error}");
                
                // Clean up any partial files
                if (isset($result['partial_file_path']) && file_exists($result['partial_file_path'])) {
                    unlink($result['partial_file_path']);
                }
                
                // If not the last attempt, wait before retry
                if ($attempt < $retry_count) {
                    $wait_time = $attempt * 500000; // Progressive backoff: 0.5s, 1s, 1.5s...
                    usleep($wait_time);
                    continue;
                }
            }
        }
        
        // All attempts failed
        log_message('error', "Secure upload FAILED after {$retry_count} attempts. Last error: {$last_error}");
        
        return [
            'status' => false,
            'error' => $last_error,
            'attempts' => $retry_count,
            'retry_exhausted' => true,
            'debug_info' => $debug_info,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Execute single upload attempt (internal method)
     */
    private function _execute_single_upload($field_name, $upload_path, $file_prefix) {
        try {
            // Step 1: Basic file validation
            $basic_validation = $this->_validate_file_basic($field_name);
            if (!$basic_validation['status']) {
                return $basic_validation;
            }
            
            $file = $_FILES[$field_name];
            
            // Step 2: Size validation (1MB max)
            $size_validation = $this->_validate_file_size($file);
            if (!$size_validation['status']) {
                return $size_validation;
            }
            
            // Step 3: MIME type validation (enhanced)
            $mime_validation = $this->_validate_mime_type_enhanced($file);
            if (!$mime_validation['status']) {
                return $mime_validation;
            }
            
            // Step 4: Extension validation
            $extension_validation = $this->_validate_file_extension($file);
            if (!$extension_validation['status']) {
                return $extension_validation;
            }
            
            // Step 5: Comprehensive virus scan
            if ($this->config['enable_virus_scan']) {
                $virus_scan = $this->_comprehensive_virus_scan($file['tmp_name']);
                if (!$virus_scan['clean']) {
                    return [
                        'status' => false,
                        'error' => "Security scan failed: {$virus_scan['message']}",
                        'error_code' => 'VIRUS_SCAN_FAILED',
                        'scan_details' => $virus_scan
                    ];
                }
            }
            
            // Step 6: Content validation
            if ($this->config['enable_content_validation']) {
                $content_validation = $this->_validate_document_content($file['tmp_name'], $extension_validation['extension']);
                if (!$content_validation['status']) {
                    return $content_validation;
                }
            }
            
            // Step 7: Generate secure filename
            $secure_filename = $this->_generate_secure_filename($file_prefix, $extension_validation['extension']);
            
            // Step 8: Prepare secure directory
            $directory_result = $this->_secure_upload_directory($upload_path);
            if (!$directory_result['success']) {
                return [
                    'status' => false,
                    'error' => "Directory preparation failed: {$directory_result['message']}",
                    'error_code' => 'DIRECTORY_ERROR'
                ];
            }
            
            // Step 9: Atomic file move
            $final_path = $upload_path . $secure_filename;
            $move_result = $this->_atomic_file_move($file['tmp_name'], $final_path);
            
            if (!$move_result['success']) {
                return [
                    'status' => false,
                    'error' => "File move failed: {$move_result['message']}",
                    'error_code' => 'FILE_MOVE_FAILED'
                ];
            }
            
            // Step 10: Set secure permissions
            chmod($final_path, 0644);
            
            // Step 11: Final integrity check
            if (!file_exists($final_path) || filesize($final_path) !== $file['size']) {
                return [
                    'status' => false,
                    'error' => 'File integrity check failed after upload',
                    'error_code' => 'INTEGRITY_CHECK_FAILED'
                ];
            }
            
            // Step 12: Log successful upload
            if ($this->config['log_activities']) {
                log_message('info', "Secure upload completed: {$secure_filename} by user " . 
                           ($this->CI->session->userdata('id') ?? 'unknown'));
            }
            
            // Return success result
            return [
                'status' => true,
                'filename' => $secure_filename,
                'path' => $final_path,
                'size' => $file['size'],
                'original_name' => $file['name'],
                'upload_timestamp' => date('Y-m-d H:i:s'),
                'security_checks' => [
                    'virus_scan' => $this->config['enable_virus_scan'] ? 'completed' : 'disabled',
                    'content_validation' => $this->config['enable_content_validation'] ? 'completed' : 'disabled',
                    'mime_validation' => $mime_validation,
                    'size_check' => 'passed',
                    'extension_check' => 'passed'
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'status' => false,
                'error' => 'Upload system error: ' . $e->getMessage(),
                'error_code' => 'SYSTEM_ERROR'
            ];
        }
    }
    
    // =================================================================
    // 🔧 VALIDATION METHODS (Enhanced)
    // =================================================================
    
    /**
     * Basic file validation
     */
    private function _validate_file_basic($field_name) {
        if (!isset($_FILES[$field_name])) {
            return [
                'status' => false,
                'error' => 'File tidak ditemukan dalam form data',
                'error_code' => 'FILE_NOT_FOUND'
            ];
        }
        
        $file = $_FILES[$field_name];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'File melebihi ukuran maksimal server (upload_max_filesize)',
                UPLOAD_ERR_FORM_SIZE => 'File melebihi ukuran maksimal form',
                UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
                UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP'
            ];
            
            return [
                'status' => false,
                'error' => $error_messages[$file['error']] ?? "Error upload tidak dikenal (code: {$file['error']})",
                'error_code' => 'UPLOAD_ERROR_' . $file['error']
            ];
        }
        
        return ['status' => true];
    }
    
    /**
     * 🔧 FIXED: File size validation (1MB max)
     */
    private function _validate_file_size($file) {
        if ($file['size'] > $this->max_file_size) {
            $max_mb = round($this->max_file_size / 1048576, 1);
            $file_mb = round($file['size'] / 1048576, 1);
            
            return [
                'status' => false,
                'error' => "Ukuran file terlalu besar ({$file_mb}MB). Maksimal {$max_mb}MB diperbolehkan",
                'error_code' => 'FILE_SIZE_EXCEEDED',
                'details' => [
                    'file_size' => $file['size'],
                    'max_allowed' => $this->max_file_size,
                    'file_size_mb' => $file_mb,
                    'max_allowed_mb' => $max_mb
                ]
            ];
        }
        
        if ($file['size'] < 100) {
            return [
                'status' => false,
                'error' => 'File terlalu kecil untuk menjadi dokumen yang valid',
                'error_code' => 'FILE_TOO_SMALL'
            ];
        }
        
        return ['status' => true];
    }
    
    /**
     * Enhanced MIME type validation
     */
    private function _validate_mime_type_enhanced($file) {
        // Check reported MIME type first
        if (!in_array($file['type'], $this->allowed_mime_types)) {
            return [
                'status' => false,
                'error' => "Tipe file tidak diperbolehkan: {$file['type']}. Hanya PDF, DOC, DOCX yang diizinkan",
                'error_code' => 'INVALID_REPORTED_MIME'
            ];
        }
        
        // Check actual MIME type using finfo (more reliable)
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $actual_mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($actual_mime, $this->allowed_mime_types)) {
                return [
                    'status' => false,
                    'error' => "Tipe file tidak valid. File terdeteksi sebagai: {$actual_mime} (dilaporkan: {$file['type']})",
                    'error_code' => 'INVALID_ACTUAL_MIME',
                    'details' => [
                        'reported_mime' => $file['type'],
                        'detected_mime' => $actual_mime,
                        'allowed_mimes' => $this->allowed_mime_types
                    ]
                ];
            }
            
            return [
                'status' => true,
                'reported_mime' => $file['type'],
                'actual_mime' => $actual_mime
            ];
        }
        
        // Fallback jika finfo tidak tersedia
        return [
            'status' => true,
            'reported_mime' => $file['type'],
            'note' => 'finfo not available, using basic validation'
        ];
    }
    
    /**
     * File extension validation
     */
    private function _validate_file_extension($file) {
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $this->allowed_extensions)) {
            return [
                'status' => false,
                'error' => 'Ekstensi file tidak diperbolehkan. Hanya ' . implode(', ', $this->allowed_extensions) . ' yang diizinkan',
                'error_code' => 'INVALID_EXTENSION',
                'details' => [
                    'detected_extension' => $file_extension,
                    'allowed_extensions' => $this->allowed_extensions
                ]
            ];
        }
        
        return [
            'status' => true,
            'extension' => $file_extension
        ];
    }
    
    // =================================================================
    // 🦠 COMPREHENSIVE VIRUS SCAN METHODS
    // =================================================================
    
    /**
     * 🦠 Comprehensive virus scan dengan multiple detection engines
     */
    private function _comprehensive_virus_scan($file_path) {
        try {
            $scan_results = [];
            
            // 1. ClamAV scan (if available)
            $scan_results['clamav'] = $this->_improved_clamav_scan($file_path);
            
            // 2. Malware pattern detection
            $scan_results['pattern'] = $this->_malware_pattern_scan($file_path);
            
            // 3. File signature validation
            $scan_results['signature'] = $this->_validate_file_signature($file_path);
            
            // 4. Content analysis
            $scan_results['content'] = $this->_analyze_file_content($file_path);
            
            // Evaluate all scan results
            $failed_scans = [];
            foreach ($scan_results as $scan_type => $result) {
                if (!$result['clean']) {
                    $failed_scans[] = "{$scan_type}: {$result['message']}";
                }
            }
            
            if (!empty($failed_scans)) {
                return [
                    'clean' => false,
                    'message' => 'File tidak lulus security scan: ' . implode('; ', $failed_scans),
                    'details' => $scan_results
                ];
            }
            
            return [
                'clean' => true,
                'message' => 'File lulus semua security scan',
                'details' => $scan_results
            ];
            
        } catch (Exception $e) {
            return [
                'clean' => false,
                'message' => 'Error during security scan: ' . $e->getMessage(),
                'details' => ['error' => $e->getMessage()]
            ];
        }
    }
    
    /**
     * Improved ClamAV scan dengan timeout dan error handling
     */
    private function _improved_clamav_scan($file_path) {
        if (!function_exists('exec')) {
            return [
                'clean' => true,
                'message' => 'exec() disabled, ClamAV scan skipped',
                'skipped' => true
            ];
        }
        
        // Check if ClamAV is installed
        $output = [];
        $return_code = 0;
        exec('which clamscan 2>/dev/null', $output, $return_code);
        
        if ($return_code !== 0) {
            return [
                'clean' => true,
                'message' => 'ClamAV not installed, scan skipped',
                'skipped' => true
            ];
        }
        
        // Execute ClamAV scan with timeout (30 seconds)
        $output = [];
        $return_code = 0;
        $escaped_path = escapeshellarg($file_path);
        exec("timeout 30 clamscan --no-summary --infected {$escaped_path} 2>/dev/null", $output, $return_code);
        
        switch ($return_code) {
            case 0:
                return ['clean' => true, 'message' => 'ClamAV: File bersih'];
            case 1:
                return ['clean' => false, 'message' => 'ClamAV: Virus terdeteksi - ' . implode(' ', $output)];
            case 124: // timeout
                return ['clean' => false, 'message' => 'ClamAV: Scan timeout (file mencurigakan atau terlalu besar)'];
            default:
                return ['clean' => false, 'message' => "ClamAV: Scan error (code: {$return_code})"]; 
        }
    }
    
    /**
     * Malware pattern detection
     */
    private function _malware_pattern_scan($file_path) {
        try {
            // Read first 8KB for pattern analysis
            $content = file_get_contents($file_path, false, null, 0, 8192);
            
            if ($content === false) {
                return ['clean' => false, 'message' => 'Cannot read file for pattern scan'];
            }
            
            // Suspicious patterns untuk dokumen
            $malware_patterns = [
                // PHP code injection
                '<?php', '<?=', 
                
                // JavaScript injection
                '<script', 'javascript:', 'eval(', 'unescape(',
                
                // System functions
                'exec(', 'system(', 'shell_exec', 'passthru', 'proc_open',
                
                // File operations
                'file_get_contents', 'file_put_contents', 'fopen', 'fwrite',
                
                // Network functions
                'curl_exec', 'fsockopen', 'gzuncompress', 'base64_decode',
                
                // Obfuscation indicators
                'str_rot13', 'gzinflate', 'assert(', 'create_function',
                
                // Suspicious hex patterns
                '\x', 'chr(', 'ord('
            ];
            
            $detected_patterns = [];
            foreach ($malware_patterns as $pattern) {
                if (stripos($content, $pattern) !== false) {
                    $detected_patterns[] = $pattern;
                }
            }
            
            if (!empty($detected_patterns)) {
                return [
                    'clean' => false,
                    'message' => 'Pola mencurigakan terdeteksi: ' . implode(', ', $detected_patterns)
                ];
            }
            
            return ['clean' => true, 'message' => 'Tidak ada pola malware terdeteksi'];
            
        } catch (Exception $e) {
            return ['clean' => false, 'message' => 'Pattern scan error: ' . $e->getMessage()];
        }
    }
    
    /**
     * File signature validation (prevent file type spoofing)
     */
    private function _validate_file_signature($file_path) {
        try {
            $handle = fopen($file_path, 'rb');
            if (!$handle) {
                return ['clean' => false, 'message' => 'Cannot read file for signature validation'];
            }
            
            $header = fread($handle, 32); // Read more bytes for better detection
            fclose($handle);
            
            $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
            
            // Expected file signatures
            $signatures = [
                'pdf' => ['%PDF-'],
                'doc' => ["\xD0\xCF\x11\xE0", "\x50\x4B\x03\x04"], // OLE or ZIP
                'docx' => ["\x50\x4B\x03\x04"] // ZIP signature
            ];
            
            if (isset($signatures[$extension])) {
                $valid_signature = false;
                foreach ($signatures[$extension] as $signature) {
                    if (strpos($header, $signature) === 0) {
                        $valid_signature = true;
                        break;
                    }
                }
                
                if (!$valid_signature) {
                    return [
                        'clean' => false,
                        'message' => "Invalid file signature untuk .{$extension} file (kemungkinan file type spoofing)"
                    ];
                }
            }
            
            return ['clean' => true, 'message' => 'File signature valid'];
            
        } catch (Exception $e) {
            return ['clean' => false, 'message' => 'Signature validation error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Content analysis untuk document integrity
     */
    private function _analyze_file_content($file_path) {
        try {
            $file_size = filesize($file_path);
            $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
            
            // Size validation
            if ($file_size < 100) {
                return ['clean' => false, 'message' => 'File terlalu kecil untuk menjadi dokumen valid'];
            }
            
            if ($file_size > $this->max_file_size) {
                return ['clean' => false, 'message' => 'File melebihi ukuran maksimal yang diperbolehkan'];
            }
            
            // Read sample content
            $sample = file_get_contents($file_path, false, null, 0, 1024);
            if (empty($sample)) {
                return ['clean' => false, 'message' => 'File tampaknya kosong atau rusak'];
            }
            
            // Check for null bytes (often indicates binary injection)
            if (strpos($sample, "\0") !== false && $extension !== 'pdf') {
                return ['clean' => false, 'message' => 'Null bytes mencurigakan terdeteksi'];
            }
            
            return ['clean' => true, 'message' => 'Content analysis passed'];
            
        } catch (Exception $e) {
            return ['clean' => false, 'message' => 'Content analysis error: ' . $e->getMessage()];
        }
    }
    
    // =================================================================
    // 🔧 LEGACY METHODS (Backward Compatibility)
    // =================================================================
    
    /**
     * Legacy method untuk backward compatibility
     * Redirects ke enhanced virus scan
     */
    private function _is_infected($file_path) {
        $scan_result = $this->_comprehensive_virus_scan($file_path);
        return !$scan_result['clean'];
    }
    
    /**
     * Legacy document content validation
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
    
    /**
     * Enhanced document content validation (internal)
     */
    private function _validate_document_content($file_path, $file_type) {
        switch ($file_type) {
            case 'pdf':
                return $this->_validate_pdf_enhanced($file_path);
            case 'doc':
            case 'docx':
                return $this->_validate_word_enhanced($file_path);
            default:
                return ['status' => true, 'message' => 'No specific validation for this file type'];
        }
    }
    
    /**
     * Enhanced PDF validation
     */
    private function _validate_pdf_enhanced($file_path) {
        try {
            // Check PDF header
            $handle = fopen($file_path, 'rb');
            if (!$handle) {
                return ['status' => false, 'error' => 'Cannot read PDF file'];
            }
            
            $header = fread($handle, 8);
            fclose($handle);
            
            if (strpos($header, '%PDF-') !== 0) {
                return ['status' => false, 'error' => 'File PDF tidak valid atau rusak'];
            }
            
            // Additional PDF validation bisa ditambah di sini
            return ['status' => true, 'message' => 'PDF validation passed'];
            
        } catch (Exception $e) {
            return ['status' => false, 'error' => 'PDF validation error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Enhanced Word document validation
     */
    private function _validate_word_enhanced($file_path) {
        try {
            $file_size = filesize($file_path);
            
            // Word documents should have reasonable minimum size
            if ($file_size < 1024) { // Less than 1KB suspicious
                return ['status' => false, 'error' => 'Dokumen Word terlalu kecil atau corrupt'];
            }
            
            // Additional Word validation bisa ditambah di sini
            return ['status' => true, 'message' => 'Word document validation passed'];
            
        } catch (Exception $e) {
            return ['status' => false, 'error' => 'Word validation error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Legacy PDF validation (backward compatibility)
     */
    private function _validate_pdf($file_path) {
        $result = $this->_validate_pdf_enhanced($file_path);
        return $result;
    }
    
    /**
     * Legacy Word validation (backward compatibility)
     */
    private function _validate_word($file_path) {
        $result = $this->_validate_word_enhanced($file_path);
        return $result;
    }
    
    // =================================================================
    // 🔧 UTILITY METHODS (Enhanced)
    // =================================================================
    
    /**
     * Generate secure filename dengan enhanced randomness
     */
    private function _generate_secure_filename($prefix, $extension) {
        $timestamp = date('YmdHis');
        $random = bin2hex(random_bytes(8)); // 16 hex characters
        $user_id = $this->CI->session->userdata('id') ?? 'guest';
        $microseconds = substr(microtime(), 2, 6); // Add microseconds for uniqueness
        
        return $prefix . $timestamp . '_' . $user_id . '_' . $random . '_' . $microseconds . '.' . $extension;
    }
    
    /**
     * Enhanced secure directory preparation
     */
    private function _secure_upload_directory($path) {
        try {
            // Ensure path ends with slash
            if (substr($path, -1) !== '/') {
                $path .= '/';
            }
            
            // Create directory if not exists
            if (!is_dir($path)) {
                if (!mkdir($path, 0755, true)) {
                    return ['success' => false, 'message' => 'Gagal membuat direktori upload'];
                }
            }
            
            // Check if directory is writable
            if (!is_writable($path)) {
                return ['success' => false, 'message' => 'Direktori upload tidak dapat ditulis'];
            }
            
            // Create/update .htaccess for security
            $htaccess_file = $path . '.htaccess';
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "deny from all\n";
            $htaccess_content .= "<Files ~ \"\\.(pdf|doc|docx)$\">\n";
            $htaccess_content .= "    Order allow,deny\n";
            $htaccess_content .= "    Allow from all\n";
            $htaccess_content .= "</Files>\n";
            $htaccess_content .= "# Generated by Secure_upload library on " . date('Y-m-d H:i:s');
            
            if (!file_put_contents($htaccess_file, $htaccess_content)) {
                log_message('warning', 'Failed to create .htaccess in upload directory: ' . $path);
            }
            
            // Create/update index.php for directory protection
            $index_file = $path . 'index.php';
            $index_content = "<?php\n";
            $index_content .= "// Generated by Secure_upload library on " . date('Y-m-d H:i:s') . "\n";
            $index_content .= "header('HTTP/1.0 403 Forbidden');\n";
            $index_content .= "exit('Access denied');\n";
            
            if (!file_put_contents($index_file, $index_content)) {
                log_message('warning', 'Failed to create index.php in upload directory: ' . $path);
            }
            
            return ['success' => true, 'message' => 'Direktori berhasil disiapkan dengan keamanan'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Directory preparation error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Atomic file move dengan comprehensive error handling
     */
    private function _atomic_file_move($source, $destination) {
        try {
            // Validate source file exists
            if (!file_exists($source)) {
                return ['success' => false, 'message' => 'Source file tidak ditemukan'];
            }
            
            // Validate source is readable
            if (!is_readable($source)) {
                return ['success' => false, 'message' => 'Source file tidak dapat dibaca'];
            }
            
            // Validate destination directory
            $dest_dir = dirname($destination);
            if (!is_dir($dest_dir)) {
                return ['success' => false, 'message' => 'Destination directory tidak ada'];
            }
            
            if (!is_writable($dest_dir)) {
                return ['success' => false, 'message' => 'Destination directory tidak dapat ditulis'];
            }
            
            // Check if destination file already exists
            if (file_exists($destination)) {
                return ['success' => false, 'message' => 'Destination file sudah ada'];
            }
            
            // Perform atomic move
            if (move_uploaded_file($source, $destination)) {
                return ['success' => true, 'message' => 'File berhasil dipindahkan'];
            } else {
                return ['success' => false, 'message' => 'move_uploaded_file() gagal'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Move operation error: ' . $e->getMessage()];
        }
    }
    
    // =================================================================
    // 🔧 CONFIGURATION METHODS
    // =================================================================
    
    /**
     * Set maximum file size
     */
    public function set_max_file_size($size_in_bytes) {
        $this->max_file_size = (int) $size_in_bytes;
        return $this;
    }
    
    /**
     * Set allowed MIME types
     */
    public function set_allowed_mime_types($mime_types) {
        $this->allowed_mime_types = (array) $mime_types;
        return $this;
    }
    
    /**
     * Set allowed extensions
     */
    public function set_allowed_extensions($extensions) {
        $this->allowed_extensions = (array) $extensions;
        return $this;
    }
    
    /**
     * Update configuration
     */
    public function set_config($config) {
        $this->config = array_merge($this->config, $config);
        return $this;
    }
    
    /**
     * Get current configuration
     */
    public function get_config() {
        return [
            'max_file_size' => $this->max_file_size,
            'max_file_size_mb' => round($this->max_file_size / 1048576, 1),
            'allowed_mime_types' => $this->allowed_mime_types,
            'allowed_extensions' => $this->allowed_extensions,
            'config' => $this->config
        ];
    }
    
    // =================================================================
    // 🔧 UTILITY / HELPER METHODS
    // =================================================================
    
    /**
     * Get upload error message in human-readable format
     */
    public function get_upload_error_message($error_code) {
        $error_messages = [
            UPLOAD_ERR_OK => 'Upload berhasil',
            UPLOAD_ERR_INI_SIZE => 'File melebihi upload_max_filesize di php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File melebihi MAX_FILE_SIZE form directive',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh PHP extension'
        ];
        
        return $error_messages[$error_code] ?? "Unknown upload error (code: {$error_code})";
    }
    
    /**
     * Check if file is safe for download/display
     */
    public function is_safe_file($file_path) {
        if (!file_exists($file_path)) {
            return false;
        }
        
        $scan_result = $this->_comprehensive_virus_scan($file_path);
        return $scan_result['clean'];
    }
    
    /**
     * Get file information dengan security check
     */
    public function get_file_info($file_path) {
        if (!file_exists($file_path)) {
            return null;
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        
        return [
            'filename' => basename($file_path),
            'size' => filesize($file_path),
            'size_mb' => round(filesize($file_path) / 1048576, 2),
            'mime_type' => $mime_type,
            'extension' => strtolower(pathinfo($file_path, PATHINFO_EXTENSION)),
            'created' => date('Y-m-d H:i:s', filectime($file_path)),
            'modified' => date('Y-m-d H:i:s', filemtime($file_path)),
            'is_safe' => $this->is_safe_file($file_path)
        ];
    }
}