<?php $this->app->extend('template/admin') ?>

<?php $this->app->setVar('title', 'Website') ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Website Edit</div>
    </div>
    <div class="card-body">
        <form action="update-home-template" enctype="multipart/form-data" id="form_update" method="POST">
            <input type="hidden" name="id" value="<?= $id; ?>">
            <h3>Head</h3>
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Page Title : </label>
                        <input name="page_title" type="text" class="form-control" value="<?= $page_title; ?>">
                    </div>
                </div>
            </div>

            <h3>Carousel / Slider</h3>
            <div class="alert alert-info">Rekomendasi ukuran background panjang x lebar = 1500 x 1000 (px)</div>
            <div class="row mb-3">
                <div class="col-md-4 col-xs-12">
                    <label>Background Image 1: </label>
                    <input type="hidden" name="def_carousel_bg1" value="<?= $carousel_bg1; ?>">
                    <input class=form-control type="file" name="carousel_bg1">
                </div>
                <div class="col-md-4 col-xs-12">
                    <label>Background Image 2: </label>
                    <input type="hidden" name="def_carousel_bg2" value="<?= $carousel_bg2; ?>">
                    <input class=form-control type="file" name="carousel_bg2">
                </div>
                <div class="col-md-4 col-xs-12">
                    <label>Background Image 3: </label>
                    <input type="hidden" name="def_carousel_bg3" value="<?= $carousel_bg3; ?>">
                    <input class=form-control type="file" name="carousel_bg3">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Sub-Title 1"</label>
                        <input type="text" name="carousel_subtitle1" class="form-control" value="<?= $carousel_subtitle1; ?>">
                    </div>
                    <div class="form-group">
                        <label>Title 1:</label>
                        <input type="text" name="carousel_title1" class="form-control" value="<?= $carousel_title1; ?>">
                    </div>
                    <div class="form-group">
                        <label>Description 1:</label>
                        <textarea name="carousel_description1" rows="8" class="form-control"><?= $carousel_description1; ?></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Sub-Title 2"</label>
                        <input type="text" name="carousel_subtitle2" class="form-control" value="<?= $carousel_subtitle2; ?>">
                    </div>
                    <div class="form-group">
                        <label>Title 2:</label>
                        <input type="text" name="carousel_title2" class="form-control" value="<?= $carousel_title2; ?>">
                    </div>
                    <div class="form-group">
                        <label>Description 2:</label>
                        <textarea name="carousel_description2" rows="8" class="form-control"><?= $carousel_description2; ?></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Sub-Title 3"</label>
                        <input type="text" name="carousel_subtitle3" class="form-control" value="<?= $carousel_subtitle3; ?>">
                    </div>
                    <div class="form-group">
                        <label>Title 3:</label>
                        <input type="text" name="carousel_title3" class="form-control" value="<?= $carousel_title3; ?>">
                    </div>
                    <div class="form-group">
                        <label>Description 3:</label>
                        <textarea name="carousel_description3" rows="8" class="form-control"><?= $carousel_description3; ?></textarea>
                    </div>
                </div>
            </div>

            <h3>Tentang Kami</h3>
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Sub-Title : </label>
                        <input name="tentang_kami_subtitle" type="text" class="form-control" value="<?= $tentang_kami_subtitle; ?>">
                    </div>
                    <div class="form-group">
                        <label>Isi : </label>
                        <textarea rows="10" class="form-control" name="tentang_kami_isi"><?= $tentang_kami_isi; ?></textarea>
                    </div>
                </div>
            </div>

            <h3>Kontak</h3>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Sub-Title : </label>
                        <input name="kontak_subtitle" type="text" class="form-control" value="<?= $kontak_subtitle; ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Deskripsi Akun Media Sosial : </label>
                        <textarea name="social_description" class="form-control" rows="5"><?= $social_description; ?></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <div class="form-group">
                        <label>Link Facebook : </label>
                        <input name="link_fb" type="text" class="form-control" value="<?= $link_fb; ?>">
                    </div>
                </div>
                <div class="col-md-6 col-xs-12">
                    <div class="form-group">
                        <label>Link Twitter : </label>
                        <input name="link_twitter" type="text" class="form-control" value="<?= $link_twitter; ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-xs-12">
                    <div class="form-group">
                        <label>Alamat : </label>
                        <input name="alamat" type="text" class="form-control" value="<?= $alamat; ?>">
                    </div>
                </div>
                <div class="col-md-4 col-xs-12">
                    <div class="form-group">
                        <label>Phone : </label>
                        <input name="phone" type="text" class="form-control" value="<?= $phone; ?>">
                    </div>
                </div>
                <div class="col-md-4 col-xs-12">
                    <div class="form-group">
                        <label>E-Mail : </label>
                        <input name="email" type="text" class="form-control" value="<?= $email; ?>">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-act"><i class="fa fa-save"></i> Simpan Perubahan</button>
        </form>
    </div>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<?php $this->app->endSection('script') ?>
<?php $this->app->init() ?>
<script>
    err_update = '<?= $this->session->flashdata('err_update'); ?>'
    succ_update = '<?= $this->session->flashdata('succ_update'); ?>'

    if (err_update == 'true') {
        notif('Gagal mengubah, error tidak diketahui', 'error', true);
    }

    if (succ_update == 'true') {
        notif('Data berhasil disimpan', 'success', true);
    }


    $("#form_update").submit(function() {
        $(".btn-act").html('<i class="fa fa-spinner fa-spin"></i> Loading...').attr('disabled', true)
    })
</script>