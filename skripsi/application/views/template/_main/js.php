<?php
// =========================================================
// FILE: application/views/template/_main/js.php (ENHANCED)
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

<!-- DataTables Core JS (EXISTING) -->
<script src="<?= base_url() ?>cdn/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>cdn/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

<!-- DataTables Buttons & Export (EXISTING) -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    /* ============================================
       EXISTING VARIABLES & FUNCTIONS (PRESERVED)
    ============================================ */
    var base_url = '<?= base_url() ?>';

    // Select 2 (EXISTING)
    $('.select2').each(function() {
        $(this).select2({
            theme: 'bootstrap4',
            allowClear: true,
        });
    });

    // Date Time (EXISTING)
    $(".dateTime").flatpickr({
        enableTime: true,
        dateFormat: "Y-m-d H:i",
    });

    // ENHANCED: Existing call function dengan error handling yang lebih baik
    function call(url, data = null) {
        return $.ajax({
            url: base_url + url,
            method: data ? 'POST' : 'GET', // FIXED: Ubah ke GET jika tidak ada data
            data: data,
            dataType: 'json' // TAMBAHAN: Eksplisit dataType
        }).fail(function(xhr, status, error) {
            console.error('AJAX Error:', {
                url: base_url + url,
                status: status,
                error: error,
                response: xhr.responseText
            });
            notif('Error: ' + (xhr.responseJSON?.message || error || 'Request failed'), 'error', true);
        });
    }

    // EXISTING: notif function (PRESERVED)
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

    // EXISTING: read function (PRESERVED)
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
    
    // EXISTING: Helper function untuk DataTables dengan Export (PRESERVED)
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

    /* ============================================
       NEW ENHANCED FUNCTIONS (FOR DASHBOARD FIX)
    ============================================ */

    // TAMBAHAN: Utility function untuk format currency
    function formatRupiah(angka, prefix = 'Rp. ') {
        if (!angka) return prefix + '0';
        var number_string = angka.toString().replace(/[^,\d]/g, ''),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? prefix + rupiah : '');
    }

    // TAMBAHAN: Utility function untuk format date yang lebih lengkap
    function formatDate(dateString, format = 'dd/mm/yyyy') {
        if (!dateString) return '-';
        const date = new Date(dateString);
        
        if (isNaN(date.getTime())) return '-';
        
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        
        switch (format) {
            case 'dd/mm/yyyy':
                return `${day}/${month}/${year}`;
            case 'dd-mm-yyyy':
                return `${day}-${month}-${year}`;
            case 'yyyy-mm-dd':
                return `${year}-${month}-${day}`;
            case 'dd/mm/yyyy hh:mm':
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            case 'human':
                return date.toLocaleDateString('id-ID', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
            case 'time-ago':
                return formatTimeAgo(dateString);
            default:
                return `${day}/${month}/${year}`;
        }
    }

    // TAMBAHAN: Format time ago untuk notifikasi
    function formatTimeAgo(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);
        
        if (diffMins < 1) {
            return 'Baru saja';
        } else if (diffMins < 60) {
            return diffMins + ' menit yang lalu';
        } else if (diffHours < 24) {
            return diffHours + ' jam yang lalu';
        } else if (diffDays < 30) {
            return diffDays + ' hari yang lalu';
        } else {
            return formatDate(dateString, 'dd/mm/yyyy');
        }
    }

    // TAMBAHAN: Loading state utility
    function setLoading(selector, isLoading = true, originalText = 'Submit') {
        const element = $(selector);
        if (isLoading) {
            element.prop('disabled', true)
                   .data('original-text', element.html())
                   .html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        } else {
            const original = element.data('original-text') || originalText;
            element.prop('disabled', false).html(original);
        }
    }

    // TAMBAHAN: Show alert dengan SweetAlert yang sudah ada
    function showAlert(type, message, title = '') {
        const alertTitle = title || (type === 'success' ? 'Berhasil!' : 
                                   type === 'error' ? 'Oops...' : 
                                   type === 'warning' ? 'Perhatian!' : 'Info');
        
        Swal.fire({
            icon: type,
            title: alertTitle,
            text: message,
            timer: type === 'success' ? 3000 : null,
            showConfirmButton: type !== 'success',
            timerProgressBar: type === 'success'
        });
    }

    // TAMBAHAN: Confirm dialog yang konsisten dengan existing style
    function confirmAction(message, callback, title = 'Konfirmasi') {
        Swal.fire({
            title: title,
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#5e72e4',
            cancelButtonColor: '#f5365c',
            confirmButtonText: 'Ya, lanjutkan!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
        });
    }

    // TAMBAHAN: Enhanced AJAX setup untuk CSRF dan error handling
    $(document).ready(function() {
        // Initialize tooltips dan popovers
        $('[data-toggle="tooltip"]').tooltip();
        $('[data-toggle="popover"]').popover();
        
        // CSRF Token setup (jika digunakan)
        <?php if (config_item('csrf_protection')): ?>
        var csrfName = '<?= $this->security->get_csrf_token_name() ?>';
        var csrfHash = '<?= $this->security->get_csrf_hash() ?>';
        
        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if (settings.type === 'POST' && csrfName && csrfHash) {
                    settings.data = settings.data || {};
                    if (typeof settings.data === 'string') {
                        settings.data += '&' + csrfName + '=' + csrfHash;
                    } else {
                        settings.data[csrfName] = csrfHash;
                    }
                }
            }
        });
        <?php endif; ?>

        // Global AJAX error handler yang tidak conflict dengan existing
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            // Skip jika sudah ada error handler di call function
            if (settings.skipGlobalError) return;
            
            if (xhr.status === 403) {
                showAlert('error', 'Sesi Anda telah berakhir. Silakan login kembali.', 'Session Expired');
                setTimeout(() => {
                    window.location.href = base_url + 'auth/login';
                }, 2000);
            } else if (xhr.status === 500) {
                console.error('Server Error:', xhr.responseText);
            } else if (xhr.status === 0) {
                console.log('Network error or request aborted');
            }
        });

        // Initialize Argon Dashboard
        if (typeof Argon !== 'undefined') {
            Argon.init();
        }

        // Auto-refresh select2 yang baru ditambah
        setTimeout(() => {
            $('.select2:not(.select2-hidden-accessible)').each(function() {
                $(this).select2({
                    theme: 'bootstrap4',
                    allowClear: true,
                });
            });
        }, 500);

        // Auto-refresh flatpickr yang baru ditambah
        setTimeout(() => {
            $(".dateTime:not(.flatpickr-input)").flatpickr({
                enableTime: true,
                dateFormat: "Y-m-d H:i",
            });
        }, 500);
    });

    // TAMBAHAN: Helper untuk DataTables standar (tanpa export)
    function initDataTable(tableId, options = {}) {
        const defaultOptions = {
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            },
            "pageLength": 25,
            "responsive": true,
            "autoWidth": false,
            "processing": true,
            "order": []
        };
        
        const finalOptions = { ...defaultOptions, ...options };
        return $(tableId).DataTable(finalOptions);
    }

    // TAMBAHAN: Helper untuk reload DataTable
    function reloadDataTable(tableId) {
        const table = $(tableId).DataTable();
        if (table) {
            table.ajax.reload(null, false);
        }
    }

    // TAMBAHAN: Auto-close alerts
    setTimeout(() => {
        $('.alert-dismissible').each(function() {
            const alert = $(this);
            if (!alert.find('.close').length) {
                alert.append('<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>');
            }
        });
    }, 100);

    // TAMBAHAN: Auto-format input number sebagai rupiah
    $(document).on('input', '.format-rupiah', function() {
        const value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatRupiah(value, ''));
    });

    // TAMBAHAN: Auto-uppercase untuk input tertentu
    $(document).on('input', '.uppercase', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // TAMBAHAN: Auto-limit character untuk textarea
    $(document).on('input', 'textarea[maxlength]', function() {
        const maxLength = $(this).attr('maxlength');
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;
        
        let counter = $(this).siblings('.char-counter');
        if (!counter.length) {
            counter = $('<small class="text-muted char-counter"></small>').insertAfter($(this));
        }
        
        counter.text(`${currentLength}/${maxLength} karakter`);
        
        if (remaining < 10) {
            counter.removeClass('text-muted').addClass('text-warning');
        } else {
            counter.removeClass('text-warning').addClass('text-muted');
        }
    });

    // TAMBAHAN: Console log untuk debugging (hanya di development)
    <?php if (ENVIRONMENT === 'development'): ?>
    console.log('STK St. Yakobus - SIM Tugas Akhir - Enhanced JS Loaded');
    console.log('Base URL:', base_url);
    console.log('Available Functions:', {
        call: 'Enhanced AJAX function',
        notif: 'SweetAlert notification',
        read: 'File reader',
        initDataTableWithExport: 'DataTable with export buttons',
        initDataTable: 'Standard DataTable',
        formatDate: 'Date formatter',
        formatRupiah: 'Currency formatter',
        formatTimeAgo: 'Relative time formatter',
        setLoading: 'Button loading state',
        showAlert: 'SweetAlert helper',
        confirmAction: 'Confirmation dialog'
    });
    <?php endif; ?>
</script>