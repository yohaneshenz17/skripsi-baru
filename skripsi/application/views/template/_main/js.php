<?php
// =========================================================
// FILE: application/views/template/_main/js.php (UPDATED)
// =========================================================
?>
<script src="<?= base_url() ?>cdn/vendor/jquery/dist/jquery.min.js"></script>
<script src="<?= base_url() ?>cdn/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url() ?>cdn/vendor/js-cookie/js.cookie.js"></script>
<script src="<?= base_url() ?>cdn/vendor/jquery.scrollbar/jquery.scrollbar.min.js"></script>
<script src="<?= base_url() ?>cdn/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js"></script>
<script src="<?= base_url() ?>cdn/vendor/clipboard/dist/clipboard.min.js"></script>
<script src="<?= base_url() ?>cdn/js/argon.js?v=1.2.0"></script>
<script src="<?= base_url() ?>cdn/plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

<!-- TAMBAHAN BARU: DataTables Core JS -->
<script src="<?= base_url() ?>cdn/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>cdn/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

<!-- TAMBAHAN BARU: DataTables Buttons & Export -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    var base_url = '<?= base_url() ?>';

    // Select 2 
    $('.select2').each(function() {
        $(this).select2({
            theme: 'bootstrap4',
            allowClear: true,
        });
    });

    // Date Time
    $(".dateTime").flatpickr({
        enableTime: true,
        dateFormat: "Y-m-d H:i",
    });

    function call(url, data = null) {
        return $.ajax({
            url: base_url + url,
            method: 'POST',
            data: data
        }).fail(function(err) {
            notif('error : ' + err.statusText, 'warning', true);
        });
    }

    async function notif(message, type, mixin) {
        if (mixin) {
            const Toast = Swal.mixin({
                position: 'top-end',
                toast: true,
                showConfirmButton: false,
                showCloseButton: true,
                timer: 3000
            })
            await Toast.fire({
                title: message,
                icon: type
            })
        } else {
            await Swal.fire({
                title: type[0].toUpperCase() + type.slice(1),
                text: message,
                icon: type
            }).then(s => {
                setTimeout(() => {
                    document.body.style.paddingRight = '0';
                }, 400)
            });
        }
    }

    function read(selector, callback) {
        file = document.querySelector(selector).files[0];
        var reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function() {
            callback({
                file: file.name,
                result: reader.result
            });
        }
        reader.onerror = function(err) {
            notif('file tidak terbaca', 'warning', true);
        }
    }
    
    // TAMBAHAN BARU: Helper function untuk DataTables dengan Export
    function initDataTableWithExport(tableId, options = {}) {
        const defaultOptions = {
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            },
            "pageLength": 25,
            "responsive": true,
            "dom": 'Bfrtip',
            "buttons": [
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel"></i> Export Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Data Export STK St. Yakobus'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fa fa-file-pdf"></i> Export PDF',
                    className: 'btn btn-danger btn-sm',
                    title: 'Data Export STK St. Yakobus'
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> Print',
                    className: 'btn btn-info btn-sm',
                    title: 'Data Export STK St. Yakobus'
                }
            ]
        };
        
        const finalOptions = { ...defaultOptions, ...options };
        return $(tableId).DataTable(finalOptions);
    }
</script>