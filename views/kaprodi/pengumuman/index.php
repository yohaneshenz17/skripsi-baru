<?php
ob_start();
?>

<div class="row">
    <div class="col-lg-12">
        <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <?php endif; ?>
        <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Kelola Pengumuman Tahapan Skripsi</h3>
                    </div>
                    <div class="col text-right">
                        <a href="<?= base_url() ?>kaprodi/pengumuman/tambah" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Tambah Pengumuman
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 8%">No</th>
                                <th style="width: 25%">Tahapan</th>
                                <th style="width: 20%">Tanggal Deadline</th>
                                <th style="width: 25%">Keterangan</th>
                                <th style="width: 10%">Status</th>
                                <th style="width: 12%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($pengumuman)): ?>
                                <?php foreach($pengumuman as $p): ?>
                                <tr>
                                    <td><strong><?= $p->no ?></strong></td>
                                    <td><?= $p->tahapan ?></td>
                                    <td>
                                        <?php
                                        $deadline_date = new DateTime($p->tanggal_deadline);
                                        $today = new DateTime();
                                        $interval = $today->diff($deadline_date);
                                        
                                        if ($today > $deadline_date) {
                                            $status_class = 'text-danger';
                                            $status_text = 'Telah lewat';
                                        } elseif ($interval->days <= 7) {
                                            $status_class = 'text-warning';
                                            $status_text = $interval->days . ' hari lagi';
                                        } else {
                                            $status_class = 'text-success';
                                            $status_text = $interval->days . ' hari lagi';
                                        }
                                        ?>
                                        <span class="<?= $status_class ?>"><?= date('d F Y', strtotime($p->tanggal_deadline)) ?></span>
                                        <br><small class="<?= $status_class ?>"><?= $status_text ?></small>
                                    </td>
                                    <td><?= $p->keterangan ? $p->keterangan : '-' ?></td>
                                    <td>
                                        <?php if($p->aktif == '1'): ?>
                                            <span class="badge badge-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Non-aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= base_url() ?>kaprodi/pengumuman/edit/<?= $p->id ?>" class="btn btn-sm btn-info" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url() ?>kaprodi/pengumuman/toggle_status/<?= $p->id ?>" class="btn btn-sm btn-warning" title="Toggle Status">
                                                <i class="fa fa-eye<?= $p->aktif == '1' ? '-slash' : '' ?>"></i>
                                            </a>
                                            <a href="<?= base_url() ?>kaprodi/pengumuman/hapus/<?= $p->id ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus pengumuman ini?')">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada pengumuman tahapan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 

$this->load->view('template/kaprodi', [
    'title' => $title,
    'content' => $content
]); 
?>