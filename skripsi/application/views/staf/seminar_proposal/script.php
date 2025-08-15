<!-- =====================================================
     SCRIPT: Staf Seminar Proposal - Enhanced DataTables
     File: application/views/staf/seminar_proposal/script.php
     ===================================================== -->

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#seminarTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
        },
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        "order": [[5, "desc"]], // Order by tanggal seminar descending
        "columnDefs": [
            { "orderable": false, "targets": [7] }, // Disable ordering on action column
            { "searchable": false, "targets": [0, 7] } // Disable search on No and Action columns
        ],
        "responsive": true,
        "autoWidth": false,
        "processing": true,
        "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
               '<"row"<"col-sm-12"tr>>' +
               '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        "drawCallback": function(settings) {
            // Reinitialize tooltips after table redraw
            $('[title]').tooltip();
        }
    });

    // Custom search filters
    $('#filter_prodi').on('change', function() {
        var prodi = this.value;
        table.column(4).search(prodi).draw();
    });

    $('#filter_status').on('change', function() {
        var status = this.value;
        table.column(6).search(status).draw();
    });

    $('#search_mahasiswa').on('keyup', function() {
        var searchTerm = this.value;
        // Search in both NIM (column 1) and Nama (column 2)
        table.columns([1, 2]).search(searchTerm).draw();
    });

    // Initialize tooltips
    $('[title]').tooltip();

    // Confirm delete actions (if any)
    $('.btn-danger').on('click', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus data ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });

    // Auto refresh data setiap 5 menit
    setInterval(function() {
        // Refresh statistics cards
        refreshStatistics();
    }, 300000); // 5 minutes

    // Export functions
    $('#exportExcel').on('click', function() {
        showExportProgress();
        window.location.href = '<?= base_url("staf/seminar_proposal/export_excel") ?>';
    });

    $('#exportPDF').on('click', function() {
        showExportProgress();
        window.location.href = '<?= base_url("staf/seminar_proposal/export_pdf") ?>';
    });

    // Quick actions
    $('.quick-approve').on('click', function(e) {
        e.preventDefault();
        var seminarId = $(this).data('id');
        var mahasiswaName = $(this).data('mahasiswa');
        
        Swal.fire({
            title: 'Quick Approve',
            html: `Setujui penilaian untuk <strong>${mahasiswaName}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url("staf/seminar_proposal/quick_approve") ?>',
                    type: 'POST',
                    data: {
                        seminar_id: seminarId,
                        <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Berhasil!', response.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    });

    // Print individual documents
    $('.print-doc').on('click', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var docType = $(this).data('type');
        
        // Open in new window for printing
        var printWindow = window.open(url, '_blank', 'width=800,height=600');
        printWindow.focus();
    });

    // Bulk operations
    $('#bulkAction').on('change', function() {
        var action = this.value;
        var checkedIds = [];
        
        $('.select-item:checked').each(function() {
            checkedIds.push($(this).val());
        });

        if (action && checkedIds.length > 0) {
            handleBulkAction(action, checkedIds);
        }
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.select-item').prop('checked', this.checked);
    });

    // Individual checkbox change
    $(document).on('change', '.select-item', function() {
        var totalItems = $('.select-item').length;
        var checkedItems = $('.select-item:checked').length;
        
        $('#selectAll').prop('indeterminate', checkedItems > 0 && checkedItems < totalItems);
        $('#selectAll').prop('checked', checkedItems === totalItems);
    });
});

// Refresh statistics function
function refreshStatistics() {
    $.ajax({
        url: '<?= base_url("staf/seminar_proposal/ajax_stats") ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                // Update statistics cards
                updateStatCard('seminar_hari_ini', data.stats.seminar_hari_ini);
                updateStatCard('seminar_minggu_ini', data.stats.seminar_minggu_ini);
                updateStatCard('belum_dinilai', data.stats.belum_dinilai);
                updateStatCard('sudah_dinilai', data.stats.sudah_dinilai);
            }
        },
        error: function() {
            console.log('Failed to refresh statistics');
        }
    });
}

// Update individual stat card
function updateStatCard(cardId, newValue) {
    var card = $(`[data-stat="${cardId}"]`);
    if (card.length) {
        card.find('.h5').countTo({
            from: parseInt(card.find('.h5').text()) || 0,
            to: newValue,
            speed: 1000,
            refreshInterval: 50
        });
    }
}

// Show export progress
function showExportProgress() {
    Swal.fire({
        title: 'Mengekspor Data...',
        text: 'Silakan tunggu, file sedang diproses.',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    // Hide loading after 3 seconds
    setTimeout(() => {
        Swal.close();
    }, 3000);
}

// Handle bulk actions
function handleBulkAction(action, ids) {
    var actionText = '';
    var confirmText = '';
    
    switch(action) {
        case 'approve_all':
            actionText = 'Setujui Semua';
            confirmText = `Setujui ${ids.length} seminar proposal yang dipilih?`;
            break;
        case 'export_selected':
            actionText = 'Export Terpilih';
            confirmText = `Export ${ids.length} seminar proposal yang dipilih?`;
            break;
        case 'print_selected':
            actionText = 'Print Terpilih';
            confirmText = `Print dokumen untuk ${ids.length} seminar proposal yang dipilih?`;
            break;
        default:
            return;
    }
    
    Swal.fire({
        title: actionText,
        text: confirmText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Proses',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url("staf/seminar_proposal/bulk_action") ?>',
                type: 'POST',
                data: {
                    action: action,
                    ids: ids,
                    <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        
                        if (action === 'export_selected' && response.download_url) {
                            window.location.href = response.download_url;
                        } else {
                            setTimeout(() => location.reload(), 1500);
                        }
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
                }
            });
        }
    });
}

// Add some custom CSS for better UX
$('<style>')
    .prop('type', 'text/css')
    .html(`
        .dataTables_wrapper .dataTables_length select {
            padding: 4px 8px;
            font-size: 12px;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 4px;
            border: 1px solid #d1d3e2;
            padding: 4px 8px;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .btn-group.show .dropdown-toggle {
            box-shadow: none;
        }
        
        .badge {
            font-size: 0.75rem;
        }
        
        .avatar-title {
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .btn-group {
                display: block;
                width: 100%;
            }
            
            .btn-group .btn {
                width: 100%;
                margin-bottom: 2px;
            }
        }
    `)
    .appendTo('head');

// Console log for debugging
console.log('Staf Seminar Proposal scripts loaded successfully');
</script>

<!-- Include SweetAlert2 if not already included -->
<?php if (!isset($swal_included)): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php endif; ?>

<!-- Include CountTo plugin for animated numbers -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-countto/1.2.0/jquery.countTo.min.js"></script>