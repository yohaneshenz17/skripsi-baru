<?php
/**
 * Form Pengajuan Izin Penelitian - Tahap 4 Workflow
 * View untuk mahasiswa mengajukan permohonan izin penelitian
 * 
 * File: application/views/mahasiswa/penelitian/form_ajukan.php
 */
?>

<style>
.form-wizard {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px 15px 0 0;
    padding: 1.5rem;
    margin: -1rem -1rem 0 -1rem;
}

.requirement-check {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    border-left: 4px solid #28a745;
}

.requirement-item {
    display: flex;
    align-items: center;
    padding: 0.5rem 0;
}

.requirement-item.ok {
    color: #155724;
}

.requirement-item.not-ok {
    color: #721c24;
}

.file-upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 2rem;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
}

.file-upload-zone:hover {
    border-color: #007bff;
    background: #e3f2fd;
}

.file-upload-zone.dragover {
    border-color: #28a745;
    background: #d4edda;
}

.form-section {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-section-title {
    color: #495057;
    font-weight: 600;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}

.btn-submit {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    color: white;
    border-radius: 25px;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
    color: white;
}

.btn-back {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    border: none;
    color: white;
    border-radius: 25px;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
    color: white;
}
</style>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Main Form Card -->
<div class="card border-0 shadow-lg">
    <div class="form-wizard">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 font-weight-bold">
                    <i class="fas fa-file-alt mr-2"></i>
                    Ajukan Izin Penelitian
                </h4>
                <p class="mb-0 opacity-75">Tahap 4 - Permohonan Izin Penelitian</p>
            </div>
            <div class="text-right">
                <a href="<?= base_url('mahasiswa/penelitian') ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <!-- Validasi Syarat -->
        <div class="requirement-check">
            <h6 class="font-weight-bold mb-3">
                <i class="fas fa-check-circle text-success mr-2"></i>
                Validasi Syarat Pengajuan
            </h6>
            
            <?php foreach ($eligibility['requirements'] as $key => $req): ?>
                <div class="requirement-item <?= $req['status'] == 'OK' ? 'ok' : 'not-ok' ?>">
                    <i class="fas fa-<?= $req['status'] == 'OK' ? 'check-circle' : 'times-circle' ?> mr-2"></i>
                    <span><?= $req['detail'] ?></span>
                </div>
            <?php endforeach; ?>
            
            <div class="mt-3 p-2 bg-success text-white rounded">
                <i class="fas fa-thumbs-up mr-2"></i>
                <strong><?= $eligibility['message'] ?></strong>
            </div>
        </div>

        <!-- Form Pengajuan -->
        <form id="form_pengajuan" method="POST" action="<?= base_url('mahasiswa/penelitian/ajukan') ?>" enctype="multipart/form-data">
            <input type="hidden" name="proposal_mahasiswa_id" value="<?= $proposal->id ?>">
            <input type="hidden" name="mahasiswa_id" value="<?= $proposal->mahasiswa_id ?>">
            <input type="hidden" name="file_proposal_revisi" value="">

            <!-- Seksi 1: Data Mahasiswa -->
            <div class="form-section">
                <h5 class="form-section-title">
                    <i class="fas fa-user mr-2 text-primary"></i>
                    Data Mahasiswa
                </h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_mahasiswa" class="font-weight-bold">
                                Nama Mahasiswa <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nama_mahasiswa" 
                                   name="nama_mahasiswa" 
                                   value="<?= strtoupper(htmlspecialchars($proposal->nama_mahasiswa)) ?>" 
                                   placeholder="Masukkan nama (HURUF KAPITAL)"
                                   style="text-transform: uppercase;"
                                   required>
                            <small class="form-text text-muted">
                                Nama harus dalam huruf kapital (contoh: ANTONIUS SIGA)
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nim" class="font-weight-bold">
                                NIM <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nim" 
                                   name="nim" 
                                   value="<?= htmlspecialchars($proposal->nim) ?>" 
                                   placeholder="Contoh: 2486208032"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="semester" class="font-weight-bold">
                                Semester <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="semester" name="semester" required>
                                <option value="">Pilih Semester</option>
                                <option value="VII">VII</option>
                                <option value="VIII">VIII</option>
                                <option value="IX">IX</option>
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="program_studi" class="font-weight-bold">
                                Program Studi <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="program_studi" name="program_studi" required>
                                <option value="">Pilih Program Studi</option>
                                <option value="Pendidikan Keagamaan Katolik" <?= ($proposal->nama_prodi == 'Pendidikan Keagamaan Katolik') ? 'selected' : '' ?>>
                                    Pendidikan Keagamaan Katolik
                                </option>
                                <option value="Pendidikan Guru Sekolah Dasar" <?= ($proposal->nama_prodi == 'Pendidikan Guru Sekolah Dasar') ? 'selected' : '' ?>>
                                    Pendidikan Guru Sekolah Dasar
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seksi 2: Data Penelitian -->
            <div class="form-section">
                <h5 class="form-section-title">
                    <i class="fas fa-search mr-2 text-primary"></i>
                    Data Penelitian
                </h5>
                
                <div class="form-group">
                    <label for="judul_skripsi_terbaru" class="font-weight-bold">
                        Judul Skripsi Terbaru <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" 
                              id="judul_skripsi_terbaru" 
                              name="judul_skripsi_terbaru" 
                              rows="3" 
                              placeholder="Masukkan judul skripsi terbaru setelah seminar proposal"
                              required><?= htmlspecialchars($proposal->judul) ?></textarea>
                    <small class="form-text text-muted">
                        Sesuaikan judul dengan hasil revisi dari seminar proposal
                    </small>
                </div>

                <div class="form-group">
                    <label for="tempat_penelitian" class="font-weight-bold">
                        Tempat Penelitian <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="tempat_penelitian" 
                           name="tempat_penelitian" 
                           value="<?= htmlspecialchars($proposal->lokasi_penelitian ?? '') ?>"
                           placeholder="Contoh: SMP Negeri 2 Merauke, Stasi St. Mikael Paroki St. Yosep Bambu Pemali"
                           required>
                    <small class="form-text text-muted">
                        Sebutkan lokasi penelitian (wilayah gerejawi, instansi, wilayah administratif)
                    </small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_mulai_penelitian" class="font-weight-bold">
                                Tanggal Mulai Penelitian <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="tanggal_mulai_penelitian" 
                                   name="tanggal_mulai_penelitian" 
                                   min="<?= date('Y-m-d') ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_selesai_penelitian" class="font-weight-bold">
                                Tanggal Selesai Penelitian <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="tanggal_selesai_penelitian" 
                                   name="tanggal_selesai_penelitian" 
                                   required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="dosen_pembimbing_id" class="font-weight-bold">
                        Dosen Pembimbing <span class="text-danger">*</span>
                    </label>
                    <select class="form-control" id="dosen_pembimbing_id" name="dosen_pembimbing_id" required>
                        <option value="">Pilih Dosen Pembimbing</option>
                        <?php foreach ($dosen_list as $dosen): ?>
                            <option value="<?= $dosen->id ?>" <?= ($dosen->id == $proposal->dosen_pembimbing_id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dosen->nama) ?> - <?= htmlspecialchars($dosen->nip) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Seksi 3: Upload Proposal Revisi -->
            <div class="form-section">
                <h5 class="form-section-title">
                    <i class="fas fa-upload mr-2 text-primary"></i>
                    Upload Proposal Revisi
                </h5>
                
                <div class="file-upload-zone" onclick="document.getElementById('file_proposal_revisi').click()">
                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Klik untuk upload atau drag & drop file</h6>
                    <p class="text-muted mb-0">
                        File proposal yang sudah direvisi sesuai catatan dewan penguji<br>
                        <small>Format: PDF/Word | Maksimal: 2MB | Wajib diisi</small>
                    </p>
                    <input type="file" 
                           id="file_proposal_revisi" 
                           accept=".pdf,.doc,.docx" 
                           style="display: none;" 
                           required>
                </div>
                
                <div id="file_preview" class="mt-3" style="display: none;">
                    <div class="alert alert-info">
                        <i class="fas fa-file-alt mr-2"></i>
                        <span id="file_info"></span>
                        <button type="button" class="btn btn-sm btn-outline-danger float-right" onclick="clearFile()">
                            <i class="fas fa-times"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="text-center">
                <a href="<?= base_url('mahasiswa/penelitian') ?>" class="btn btn-back mr-3">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Ajukan Permohonan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Information Card -->
<div class="card border-left-info mt-4">
    <div class="card-body">
        <h6 class="font-weight-bold text-info mb-3">
            <i class="fas fa-info-circle mr-2"></i>
            Informasi Penting
        </h6>
        <div class="row">
            <div class="col-md-6">
                <h6 class="font-weight-bold">Setelah Pengajuan:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-arrow-right text-primary mr-2"></i>Dosen pembimbing akan mereview permohonan</li>
                    <li><i class="fas fa-arrow-right text-primary mr-2"></i>Jika disetujui, staf akan memproses surat izin</li>
                    <li><i class="fas fa-arrow-right text-primary mr-2"></i>Surat izin dapat didownload setelah selesai</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="font-weight-bold">Tips:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-lightbulb text-warning mr-2"></i>Pastikan semua data sudah benar</li>
                    <li><i class="fas fa-lightbulb text-warning mr-2"></i>Upload proposal hasil revisi terbaru</li>
                    <li><i class="fas fa-lightbulb text-warning mr-2"></i>Koordinasi dengan dosen pembimbing</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// File upload handling
document.getElementById('file_proposal_revisi').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validasi ukuran file (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB');
            e.target.value = '';
            return;
        }
        
        // Validasi tipe file
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!allowedTypes.includes(file.type)) {
            alert('Tipe file tidak diizinkan. Hanya PDF dan Word yang diperbolehkan');
            e.target.value = '';
            return;
        }
        
        // Convert ke base64
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('input[name="file_proposal_revisi"]').value = e.target.result;
            
            // Show preview
            document.getElementById('file_info').textContent = file.name + ' (' + (file.size/1024/1024).toFixed(2) + ' MB)';
            document.getElementById('file_preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Clear file function
function clearFile() {
    document.getElementById('file_proposal_revisi').value = '';
    document.querySelector('input[name="file_proposal_revisi"]').value = '';
    document.getElementById('file_preview').style.display = 'none';
}

// Drag and drop functionality
const uploadZone = document.querySelector('.file-upload-zone');

uploadZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('dragover');
});

uploadZone.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
});

uploadZone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('file_proposal_revisi').files = files;
        document.getElementById('file_proposal_revisi').dispatchEvent(new Event('change'));
    }
});

// Form validation
document.getElementById('form_pengajuan').addEventListener('submit', function(e) {
    // Cek apakah file sudah diupload
    if (!document.querySelector('input[name="file_proposal_revisi"]').value) {
        alert('File proposal revisi wajib diupload');
        e.preventDefault();
        return false;
    }
    
    // Validasi tanggal
    const tanggalMulai = new Date(document.getElementById('tanggal_mulai_penelitian').value);
    const tanggalSelesai = new Date(document.getElementById('tanggal_selesai_penelitian').value);
    
    if (tanggalSelesai <= tanggalMulai) {
        alert('Tanggal selesai harus lebih besar dari tanggal mulai');
        e.preventDefault();
        return false;
    }
    
    // Confirm submission
    if (!confirm('Yakin ingin mengajukan permohonan izin penelitian ini? Pastikan semua data sudah benar.')) {
        e.preventDefault();
        return false;
    }
});

// Auto update tanggal selesai ketika tanggal mulai berubah
document.getElementById('tanggal_mulai_penelitian').addEventListener('change', function() {
    const tanggalMulai = new Date(this.value);
    const tanggalSelesai = new Date(tanggalMulai);
    tanggalSelesai.setMonth(tanggalSelesai.getMonth() + 3); // Default 3 bulan kemudian
    
    document.getElementById('tanggal_selesai_penelitian').min = this.value;
    document.getElementById('tanggal_selesai_penelitian').value = tanggalSelesai.toISOString().split('T')[0];
});
</script>