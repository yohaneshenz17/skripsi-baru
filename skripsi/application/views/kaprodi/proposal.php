<?php $this->load->view('template/kaprodi', ['content' => '']); ob_start(); ?>

<div class="row">
    <div class="col">
        <div class="card">
            <!-- Card header -->
            <div class="card-header border-0">
                <h3 class="mb-0">Daftar Proposal untuk Penetapan Pembimbing & Penguji</h3>
            </div>
            
            <?php if($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show mx-3" role="alert">
                <span class="alert-icon"><i class="ni ni-check-bold"></i></span>
                <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            
            <?php if($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
                <span class="alert-icon"><i class="ni ni-fat-remove"></i></span>
                <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            
            <!-- Light table -->
            <div class="table-responsive">
                <table class="table align-items-center table-flush" id="datatable-basic">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Judul Proposal</th>
                            <th>Tanggal Ajuan</th>
                            <th>Status</th>
                            <th>Pembimbing</th>
                            <th>Penguji</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        <?php 
                        $no = 1;
                        foreach($proposals as $p): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $p->nim ?></td>
                            <td><?= $p->nama_mahasiswa ?></td>
                            <td>
                                <span data-toggle="tooltip" data-placement="top" title="<?= $p->judul ?>">
                                    <?= substr($p->judul, 0, 40) ?>...
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($p->id)) ?></td>
                            <td>
                                <?php if($p->dosen_id == null): ?>
                                    <span class="badge badge-warning">Belum Ditetapkan</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Sudah Ditetapkan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                if($p->dosen_id) {
                                    $pembimbing = $this->db->get_where('dosen', ['id' => $p->dosen_id])->row();
                                    echo $pembimbing ? $pembimbing->nama : '-';
                                } else {
                                    echo '<span class="text-muted">Belum ada</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if($p->dosen_penguji_id) {
                                    $penguji1 = $this->db->get_where('dosen', ['id' => $p->dosen_penguji_id])->row();
                                    $penguji2 = $p->dosen_penguji2_id ? $this->db->get_where('dosen', ['id' => $p->dosen_penguji2_id])->row() : null;
                                    
                                    echo '<small>';
                                    echo '1. ' . ($penguji1 ? $penguji1->nama : '-') . '<br>';
                                    echo '2. ' . ($penguji2 ? $penguji2->nama : 'Belum ada');
                                    echo '</small>';
                                } else {
                                    echo '<span class="text-muted">Belum ada</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                        <a class="dropdown-item" href="#" onclick="viewDetail(<?= $p->id ?>)">
                                            <i class="fa fa-eye text-info"></i> Lihat Detail
                                        </a>
                                        <?php if($p->dosen_id == null): ?>
                                        <a class="dropdown-item" href="<?= base_url() ?>kaprodi/penetapan/<?= $p->id ?>">
                                            <i class="fa fa-user-check text-primary"></i> Tetapkan Pembimbing
                                        </a>
                                        <?php else: ?>
                                        <a class="dropdown-item" href="<?= base_url() ?>kaprodi/penetapan/<?= $p->id ?>">
                                            <i class="fa fa-edit text-warning"></i> Edit Penetapan
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Proposal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    $('#datatable-basic').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
        }
    });
    
    $('[data-toggle="tooltip"]').tooltip();
});

function viewDetail(id) {
    $.ajax({
        url: '<?= base_url() ?>api/proposal/detail/' + id,
        type: 'GET',
        success: function(response) {
            $('#detailContent').html(response);
            $('#modalDetail').modal('show');
        },
        error: function() {
            Swal.fire('Error', 'Gagal memuat detail proposal', 'error');
        }
    });
}
</script>
<?php $script = ob_get_clean(); ?>

<?php $this->load->view('template/kaprodi', ['content' => $content, 'script' => $script]); ?>