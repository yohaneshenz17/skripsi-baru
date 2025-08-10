<?php
/**
 * JavaScript untuk Daftar Penguji
 * 
 * File: application/views/dosen/daftar_penguji/script.php
 */
?>

<script>
$(document).ready(function() {
    
    // Initialize DataTables untuk tabel proposal jika ada data
    if ($('#table-proposal tbody tr').length > 0) {
        $('#table-proposal').DataTable({
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            },
            pageLength: 10,
            order: [[3, 'desc']], // Sort by jadwal seminar descending
            columnDefs: [
                {
                    targets: [0, 6], // No dan Aksi
                    orderable: false
                },
                {
                    targets: [3], // Jadwal Seminar
                    type: 'date'
                }
            ],
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip',
            drawCallback: function() {
                // Re-initialize tooltips after table draw
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    }

    // Initialize DataTables untuk tabel skripsi jika ada data
    if ($('#table-skripsi tbody tr').length > 0) {
        $('#table-skripsi').DataTable({
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            },
            pageLength: 10,
            order: [[3, 'desc']], // Sort by jadwal seminar descending
            columnDefs: [
                {
                    targets: [0, 6], // No dan Aksi
                    orderable: false
                },
                {
                    targets: [3], // Jadwal Seminar
                    type: 'date'
                }
            ],
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip',
            drawCallback: function() {
                // Re-initialize tooltips after table draw
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    }

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Tab switch handling
    $('#pengujTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // Redraw DataTables when tab is shown
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        
        // Update URL hash without triggering scroll
        var hash = $(e.target).attr('href');
        if (hash) {
            history.replaceState(null, null, hash);
        }
    });

    // Load tab based on URL hash
    if (window.location.hash) {
        var hash = window.location.hash;
        $('#pengujTabs a[href="' + hash + '"]').tab('show');
    }

    // Filter functionality
    $('.filter-status').on('change', function() {
        var table = $(this).closest('.tab-pane').find('table').DataTable();
        var status = $(this).val();
        
        if (status === '') {
            table.column(5).search('').draw(); // Status column
        } else {
            table.column(5).search(status).draw();
        }
    });

    // Export functionality
    $('.btn-export').on('click', function(e) {
        e.preventDefault();
        
        var url = $(this).attr('href');
        var type = $(this).data('type');
        
        // Show loading
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.html('<i class="fa fa-spinner fa-spin"></i> Generating...');
        $btn.prop('disabled', true);
        
        // Open in new window
        window.open(url, '_blank');
        
        // Restore button after 3 seconds
        setTimeout(function() {
            $btn.html(originalText);
            $btn.prop('disabled', false);
        }, 3000);
    });

    // Quick filter buttons
    $('.quick-filter').on('click', function(e) {
        e.preventDefault();
        
        var filter = $(this).data('filter');
        var tabId = $(this).data('tab');
        var table = $(tabId + ' table').DataTable();
        
        // Remove active class from siblings
        $(this).siblings('.quick-filter').removeClass('btn-primary').addClass('btn-outline-primary');
        
        // Add active class to clicked button
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
        
        // Apply filter
        if (filter === 'all') {
            table.search('').columns().search('').draw();
        } else if (filter === 'belum-dinilai') {
            table.column(5).search('Belum Dinilai').draw();
        } else if (filter === 'sudah-dinilai') {
            table.column(5).search('Sudah Dinilai').draw();
        } else if (filter === 'menunggu') {
            table.column(5).search('Terjadwal|Proses', true, false).draw();
        }
    });

    // Auto refresh statistics every 5 minutes
    setInterval(function() {
        $.ajax({
            url: '<?= base_url("dosen/daftar_penguji/ajax_stats") ?>',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    // Update statistics cards
                    $('.card-stats').each(function() {
                        var type = $(this).data('type');
                        if (data.stats[type] !== undefined) {
                            $(this).find('.h2').text(data.stats[type]);
                        }
                    });
                }
            },
            error: function() {
                console.log('Failed to update statistics');
            }
        });
    }, 300000); // 5 minutes

    // Print detail functionality
    $('.btn-print-detail').on('click', function(e) {
        e.preventDefault();
        
        var seminarId = $(this).data('id');
        var type = $(this).data('type');
        
        // Open print-friendly version
        var printUrl = '<?= base_url("dosen/daftar_penguji/print_detail/") ?>' + type + '/' + seminarId;
        var printWindow = window.open(printUrl, 'print', 'width=800,height=600');
        
        // Auto print when loaded
        printWindow.onload = function() {
            printWindow.print();
        };
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        var searchTerm = $(this).val();
        var activeTab = $('.tab-pane.active');
        var table = activeTab.find('table').DataTable();
        
        table.search(searchTerm).draw();
    });

    // Advanced search modal
    $('#advancedSearchModal').on('show.bs.modal', function() {
        // Initialize date pickers if available
        if ($.fn.datepicker) {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
        }
    });

    // Apply advanced search
    $('#applyAdvancedSearch').on('click', function() {
        var activeTab = $('.tab-pane.active');
        var table = activeTab.find('table').DataTable();
        
        // Get search criteria
        var mahasiswa = $('#searchMahasiswa').val();
        var status = $('#searchStatus').val();
        var dateFrom = $('#searchDateFrom').val();
        var dateTo = $('#searchDateTo').val();
        
        // Build search regex
        var searchParts = [];
        if (mahasiswa) searchParts.push(mahasiswa);
        if (status) searchParts.push(status);
        
        var searchTerm = searchParts.join('|');
        table.search(searchTerm, true, false).draw();
        
        // Close modal
        $('#advancedSearchModal').modal('hide');
        
        // Show clear filter button if any filter is applied
        if (searchTerm || dateFrom || dateTo) {
            $('#clearFilters').show();
        }
    });

    // Clear all filters
    $('#clearFilters').on('click', function() {
        var activeTab = $('.tab-pane.active');
        var table = activeTab.find('table').DataTable();
        
        // Clear table search
        table.search('').columns().search('').draw();
        
        // Clear form inputs
        $('#advancedSearchForm')[0].reset();
        $('#searchInput').val('');
        
        // Hide clear button
        $(this).hide();
        
        // Reset quick filter buttons
        $('.quick-filter').removeClass('btn-primary').addClass('btn-outline-primary');
        $('.quick-filter[data-filter="all"]').removeClass('btn-outline-primary').addClass('btn-primary');
    });

    // Bulk actions
    $('.select-all').on('change', function() {
        var isChecked = $(this).is(':checked');
        var table = $(this).closest('.tab-pane').find('table');
        
        table.find('.select-item').prop('checked', isChecked);
        updateBulkActions();
    });

    $(document).on('change', '.select-item', function() {
        updateBulkActions();
    });

    function updateBulkActions() {
        var selectedItems = $('.select-item:checked').length;
        
        if (selectedItems > 0) {
            $('#bulkActions').show();
            $('#selectedCount').text(selectedItems);
        } else {
            $('#bulkActions').hide();
        }
    }

    // Bulk export
    $('#bulkExport').on('click', function() {
        var selectedIds = [];
        $('.select-item:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        if (selectedIds.length > 0) {
            var activeTab = $('.tab-pane.active').attr('id');
            var type = activeTab.includes('proposal') ? 'proposal' : 'skripsi';
            
            var exportUrl = '<?= base_url("dosen/daftar_penguji/export_bulk") ?>?type=' + type + '&ids=' + selectedIds.join(',');
            window.open(exportUrl, '_blank');
        }
    });

    // Initialize perfect scrollbar for sidebar if available
    if (typeof PerfectScrollbar !== 'undefined') {
        var sidebar = document.querySelector('.card-body');
        if (sidebar) {
            new PerfectScrollbar(sidebar);
        }
    }

    // Responsive table handling
    $(window).on('resize', function() {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Prevent form double submission
    $('form').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true);
    });

    // Initialize any additional plugins
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    // Console log for debugging
    console.log('Daftar Penguji scripts initialized successfully');
});

// Helper functions
function refreshTable(type) {
    var tableId = type === 'proposal' ? '#table-proposal' : '#table-skripsi';
    var table = $(tableId).DataTable();
    
    if (table) {
        table.ajax.reload(null, false);
    }
}

function showNotification(message, type = 'info') {
    var alertClass = 'alert-' + type;
    var iconClass = type === 'success' ? 'ni-check-bold' : 'ni-support-16';
    
    var alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="ni ${iconClass}"></i></span>
            <span class="alert-text">${message}</span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    $('.container-fluid').prepend(alert);
    
    // Auto hide after 5 seconds
    setTimeout(function() {
        $('.alert').first().fadeOut('slow');
    }, 5000);
}

function formatDate(dateString) {
    if (!dateString) return '-';
    
    var date = new Date(dateString);
    var options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    
    return date.toLocaleDateString('id-ID', options);
}

function truncateText(text, maxLength = 50) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}
</script>