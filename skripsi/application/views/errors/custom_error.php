<?php
/**
 * Custom Error View untuk SIM-TA
 * 
 * View ini digunakan untuk menangani error pada modul yang mengalami masalah
 * tanpa harus redirect ke halaman lain atau crash sistem
 * 
 * File: application/views/errors/custom_error.php
 */

$error_message = isset($error_message) ? $error_message : 'Terjadi kesalahan sistem.';
$technical_error = isset($technical_error) ? $technical_error : null;
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-body p-5 text-center">
                    <!-- Error Icon -->
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle fa-4x text-warning"></i>
                    </div>
                    
                    <!-- Error Title -->
                    <h2 class="h4 text-gray-800 mb-3">Terjadi Kesalahan Sistem</h2>
                    
                    <!-- Error Message -->
                    <p class="text-gray-600 mb-4">
                        <?= htmlspecialchars($error_message) ?>
                    </p>
                    
                    <!-- Technical Error (Development Only) -->
                    <?php if($technical_error && ENVIRONMENT === 'development'): ?>
                        <div class="alert alert-danger text-left">
                            <h6 class="alert-heading">
                                <i class="fas fa-code mr-2"></i>Technical Error (Development Mode):
                            </h6>
                            <small class="font-monospace">
                                <?= htmlspecialchars($technical_error) ?>
                            </small>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Suggested Actions -->
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <div class="card bg-light border-0">
                                <div class="card-body p-3">
                                    <h6 class="card-title">
                                        <i class="fas fa-redo-alt text-primary mr-2"></i>Coba Lagi
                                    </h6>
                                    <p class="card-text text-sm">
                                        Refresh halaman atau tunggu beberapa saat dan coba akses kembali.
                                    </p>
                                    <button onclick="location.reload()" class="btn btn-primary btn-sm">
                                        <i class="fas fa-sync-alt mr-1"></i>Refresh Halaman
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6 mb-3">
                            <div class="card bg-light border-0">
                                <div class="card-body p-3">
                                    <h6 class="card-title">
                                        <i class="fas fa-home text-success mr-2"></i>Kembali ke Dashboard
                                    </h6>
                                    <p class="card-text text-sm">
                                        Kembali ke halaman utama dan akses menu lain yang tersedia.
                                    </p>
                                    <a href="<?= base_url('kaprodi/dashboard') ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-home mr-1"></i>Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Alternative Menu -->
                    <div class="mt-4">
                        <h6 class="text-gray-700 mb-3">Atau akses menu lain:</h6>
                        <div class="btn-group flex-wrap" role="group">
                            <a href="<?= base_url('kaprodi/proposal') ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-file-alt mr-1"></i>Usulan Proposal
                            </a>
                            <a href="<?= base_url('kaprodi/seminar_proposal') ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-presentation mr-1"></i>Seminar Proposal
                            </a>
                            <a href="<?= base_url('kaprodi/publikasi') ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-upload mr-1"></i>Publikasi
                            </a>
                            <a href="<?= base_url('kaprodi/mahasiswa') ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-users mr-1"></i>Daftar Mahasiswa
                            </a>
                        </div>
                    </div>
                    
                    <!-- Contact Support -->
                    <div class="mt-4 pt-3 border-top">
                        <p class="text-muted text-sm mb-0">
                            <i class="fas fa-question-circle mr-1"></i>
                            Jika masalah terus berlanjut, silakan hubungi 
                            <strong>Unit SIPD STK Santo Yakobus</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto refresh setelah 30 detik
setTimeout(function() {
    if(confirm('Halaman akan di-refresh otomatis. Lanjutkan?')) {
        location.reload();
    }
}, 30000);

// Log error untuk monitoring (jika ada konsol)
<?php if($technical_error): ?>
console.error('SIM-TA Error:', <?= json_encode($technical_error) ?>);
<?php endif; ?>
</script>