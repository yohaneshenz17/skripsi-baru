<?php
ob_start();
?>

<div class="row">
    <div class="col-xl-8 order-xl-1">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h3 class="mb-0">Profil Saya</h3>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h6 class="heading-small text-muted mb-4">Informasi Pengguna</h6>
                <div class="pl-lg-4">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="form-control-label">Nama Lengkap</label>
                                <input type="text" class="form-control" readonly value="<?= htmlspecialchars($user->nama) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-control-label">NIDN</label>
                                <input type="text" class="form-control" readonly value="<?= htmlspecialchars($user->nip) ?>">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-control-label">Email</label>
                                <input type="email" class="form-control" readonly value="<?= htmlspecialchars($user->email) ?>">
                            </div>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-control-label">Nomor Telepon</label>
                                <input type="text" class="form-control" readonly value="<?= htmlspecialchars($user->nomor_telepon) ?>">
                            </div>
                        </div>
                         <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-control-label">Level</label>
                                <input type="text" class="form-control" readonly value="Ketua Program Studi">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 

$this->load->view('template/kaprodi', [
    'title' => 'Profil Saya',
    'content' => $content,
    'script' => '' // Tidak ada skrip khusus
]); 
?>