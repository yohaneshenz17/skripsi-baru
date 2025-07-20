<?php
// =========================================================
// FILE: application/views/template/_main/css.php (UPDATED)
// =========================================================
?>
<?php $app = json_decode(file_get_contents(base_url('cdn/db/app.json'))) ?>
<link rel="icon" href="<?= base_url() ?>cdn/img/icons/<?= ($app->icon) ? $app->icon : 'default.png' ?>" type="image/png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
<link rel="stylesheet" href="<?= base_url() ?>cdn/vendor/nucleo/css/nucleo.css" type="text/css">
<link rel="stylesheet" href="<?= base_url() ?>cdn/vendor/@fortawesome/fontawesome-free/css/all.min.css" type="text/css">
<link rel="stylesheet" href="<?= base_url() ?>cdn/css/argon.css?v=1.2.0" type="text/css">
<link rel="stylesheet" href="<?= base_url() ?>cdn/plugins/sweetalert2/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="<?= base_url() ?>assets/select2/select2-bootstrap4.min.css">

<!-- TAMBAHAN BARU: DataTables CSS -->
<link rel="stylesheet" href="<?= base_url() ?>cdn/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">

<style>
    .card-content {
        min-height: 450px;
    }

    textarea {
        resize: none;
    }

    .foto-fluid {
        height: 100%;
        width: 100%;
    }
    
    /* TAMBAHAN: Style untuk Export Buttons */
    .dt-buttons {
        margin-bottom: 10px;
    }
    
    .dt-button {
        margin-right: 5px;
    }
    
    .dt-button-collection {
        border-radius: 4px;
    }
</style>