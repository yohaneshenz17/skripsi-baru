/**
 * =====================================================
 * JAVASCRIPT UTILITIES UNTUK KAPRODI PUBLIKASI
 * File: assets/js/kaprodi-publikasi.js
 * =====================================================
 */

/**
 * Main Kaprodi Publikasi Class
 */
class KaprodiPublikasi {
    constructor() {
        this.init();
    }

    init() {
        this.initDataTables();
        this.initTooltips();
        this.initModals();
        this.initFormValidation();
        this.initAutoRefresh();
        this.initProgressTracking();
        this.initNotifications();
    }

    /**
     * Initialize DataTables with custom configurations
     */
    initDataTables() {
        if (typeof $.fn.DataTable !== 'undefined') {
            // Main publikasi table
            $('#datatable-publikasi').DataTable({
                "order": [[ 6, "desc" ]], // Sort by date
                "pageLength": 25,
                "responsive": true,
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ publikasi",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir", 
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    },
                    "emptyTable": "Tidak ada data publikasi",
                    "zeroRecords": "Tidak ditemukan data yang sesuai"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [7] }, // Actions column
                    { "className": "text-center", "targets": [0, 4, 5] }
                ],
                "drawCallback": function() {
                    // Reinitialize tooltips after table redraw
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });

            // Laporan mahasiswa table
            $('#table-laporan-mahasiswa').DataTable({
                "order": [[ 5, "desc" ]],
                "pageLength": 25,
                "responsive": true,
                "language": {
                    "search": "Cari mahasiswa:",
                    "lengthMenu": "Tampilkan _MENU_ mahasiswa per halaman",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ mahasiswa"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [0] }
                ]
            });
        }
    }

    /**
     * Initialize tooltips
     */
    initTooltips() {
        if (typeof $().tooltip !== 'undefined') {
            $('[data-toggle="tooltip"]').tooltip({
                trigger: 'hover',
                placement: 'top'
            });
        }
    }

    /**
     * Initialize modals and confirmations
     */
    initModals() {
        // Confirmation dialogs
        $('.btn-danger, .btn-warning[data-confirm]').on('click', function(e) {
            const message = $(this).data('confirm') || 'Yakin ingin melanjutkan?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });

        // Override confirmation
        $('#override-form').on('submit', function(e) {
            const action = $('#override-action').val();
            const comment = $('textarea[name="komentar_kaprodi"]').val();
            
            if (!action || comment.length < 20) {
                e.preventDefault();
                alert('Harap lengkapi form dengan benar.');
                return false;
            }

            const confirmMsg = `Yakin ingin melakukan override "${action}"?\n\nTindakan ini akan dicatat dalam sistem.`;
            if (!confirm(confirmMsg)) {
                e.preventDefault();
                return false;
            }

            // Show loading state
            $(this).find('button[type="submit"]')
                   .prop('disabled', true)
                   .html('<i class="fa fa-spinner fa-spin"></i> Memproses...');
        });
    }

    /**
     * Initialize form validation
     */
    initFormValidation() {
        // Status filter
        $('#filter-status').on('change', function() {
            const status = $(this).val();
            const table = $('#datatable-publikasi').DataTable();
            
            if (status === '') {
                table.column(4).search('').draw();
            } else {
                table.column(4).search(status).draw();
            }
        });

        // Character counter for textareas
        $('textarea[maxlength]').on('input', function() {
            const current = $(this).val().length;
            const max = $(this).attr('maxlength');
            const min = $(this).attr('minlength') || 0;
            
            let counter = $(this).siblings('.char-counter');
            if (counter.length === 0) {
                counter = $('<small class="char-counter text-muted"></small>');
                $(this).after(counter);
            }
            
            let counterText = `${current}/${max} karakter`;
            let counterClass = 'text-muted';
            
            if (current < min) {
                counterText += ` (minimal ${min})`;
                counterClass = 'text-danger';
            } else if (current > max * 0.9) {
                counterClass = 'text-warning';
            }
            
            counter.removeClass('text-muted text-danger text-warning')
                   .addClass(counterClass)
                   .text(counterText);
        });
    }

    /**
     * Initialize auto refresh for real-time updates
     */
    initAutoRefresh() {
        // Auto refresh every 5 minutes for dashboard
        if (window.location.pathname.includes('/kaprodi/publikasi') && 
            !window.location.pathname.includes('/detail/') &&
            !window.location.pathname.includes('/tracking/')) {
            
            setInterval(() => {
                this.refreshStats();
            }, 300000); // 5 minutes
        }

        // Auto refresh every 30 seconds for tracking page
        if (window.location.pathname.includes('/tracking/')) {
            setInterval(() => {
                this.refreshTrackingStatus();
            }, 30000); // 30 seconds
        }
    }

    /**
     * Refresh dashboard statistics
     */
    refreshStats() {
        $.ajax({
            url: base_url + 'kaprodi/publikasi/get_stats',
            method: 'GET',
            dataType: 'json',
            success: (data) => {
                if (data.success) {
                    this.updateStatsCards(data.stats);
                }
            },
            error: () => {
                console.log('Failed to refresh stats');
            }
        });
    }

    /**
     * Update statistics cards
     */
    updateStatsCards(stats) {
        Object.keys(stats).forEach(key => {
            const card = $(`.card-stats[data-stat="${key}"]`);
            if (card.length) {
                card.find('.h2').text(stats[key]);
                card.addClass('updated').removeClass('updated', 2000);
            }
        });
    }

    /**
     * Refresh tracking status
     */
    refreshTrackingStatus() {
        const publikasiId = this.getPublikasiIdFromUrl();
        if (!publikasiId) return;

        $.ajax({
            url: base_url + `kaprodi/publikasi/get_status/${publikasiId}`,
            method: 'GET',
            dataType: 'json',
            success: (data) => {
                if (data.success && data.status_changed) {
                    this.updateTrackingProgress(data.publikasi);
                    this.showStatusUpdateNotification(data.publikasi);
                }
            },
            error: () => {
                console.log('Failed to refresh tracking status');
            }
        });
    }

    /**
     * Initialize progress tracking
     */
    initProgressTracking() {
        // Animate progress bars on page load
        $('.progress-bar').each(function() {
            const width = $(this).attr('aria-valuenow') + '%';
            $(this).animate({ width: width }, 1000);
        });

        // Workflow step animations
        $('.workflow-steps .step-item.current').addClass('pulse-animation');
    }

    /**
     * Update tracking progress
     */
    updateTrackingProgress(publikasi) {
        // Update status badge
        const statusBadge = $('.status-badge');
        statusBadge.removeClass()
                   .addClass(`badge ${this.getStatusBadgeClass(publikasi.status)}`)
                   .text(this.getStatusText(publikasi.status));

        // Update progress bar
        const progressBar = $('.progress-bar');
        const newWidth = this.calculateProgress(publikasi.status);
        progressBar.animate({ width: newWidth + '%' }, 500);

        // Update step indicators
        this.updateStepIndicators(publikasi.status);
    }

    /**
     * Initialize notifications
     */
    initNotifications() {
        // Auto-hide alerts after 5 seconds
        $('.alert:not(.alert-permanent)').delay(5000).fadeOut();

        // Show success notifications for completed actions
        if (window.location.hash === '#success') {
            this.showNotification('success', 'Tindakan berhasil dilakukan!');
        }
    }

    /**
     * Show notification
     */
    showNotification(type, message) {
        const alertClass = `alert-${type}`;
        const icon = type === 'success' ? 'fa-check' : 'fa-exclamation-triangle';
        
        const alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show notification-alert" role="alert">
                <i class="fa ${icon} mr-2"></i>${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `);

        $('body').prepend(alert);
        setTimeout(() => alert.fadeOut(), 5000);
    }

    /**
     * Show status update notification
     */
    showStatusUpdateNotification(publikasi) {
        const message = `Status publikasi ${publikasi.nama_mahasiswa} telah diupdate ke: ${this.getStatusText(publikasi.status)}`;
        this.showNotification('info', message);
    }

    /**
     * Utility functions
     */
    getPublikasiIdFromUrl() {
        const pathArray = window.location.pathname.split('/');
        const index = pathArray.indexOf('tracking') + 1;
        return pathArray[index] || null;
    }

    getStatusBadgeClass(status) {
        const badges = {
            'draft': 'badge-secondary',
            'submitted': 'badge-info',
            'review_pembimbing': 'badge-warning',
            'approved_pembimbing': 'badge-primary',
            'review_staf': 'badge-primary',
            'completed': 'badge-success',
            'rejected': 'badge-danger'
        };
        return badges[status] || 'badge-secondary';
    }

    getStatusText(status) {
        const texts = {
            'draft': 'Draft',
            'submitted': 'Diajukan',
            'review_pembimbing': 'Review Dosen',
            'approved_pembimbing': 'Approved Dosen',
            'review_staf': 'Review Staf',
            'completed': 'Selesai',
            'rejected': 'Ditolak'
        };
        return texts[status] || 'Unknown';
    }

    calculateProgress(status) {
        const progress = {
            'draft': 20,
            'submitted': 40,
            'review_pembimbing': 50,
            'approved_pembimbing': 70,
            'review_staf': 80,
            'completed': 100,
            'rejected': 30
        };
        return progress[status] || 10;
    }

    updateStepIndicators(status) {
        const stepMap = {
            'draft': 2,
            'submitted': 3,
            'review_pembimbing': 5,
            'approved_pembimbing': 6,
            'review_staf': 7,
            'completed': 9,
            'rejected': 6
        };

        const currentStep = stepMap[status] || 1;
        
        $('.workflow-steps .step-item').each(function(index) {
            const stepNumber = index + 1;
            $(this).removeClass('completed current');
            
            if (stepNumber < currentStep) {
                $(this).addClass('completed');
            } else if (stepNumber === currentStep) {
                $(this).addClass('current');
            }
        });
    }
}

/**
 * Export utilities
 */
const ExportUtilities = {
    /**
     * Export table to CSV
     */
    exportTableToCSV(tableId, filename = 'laporan.csv') {
        const table = document.getElementById(tableId);
        const rows = Array.from(table.querySelectorAll('tr'));
        
        const csvContent = rows.map(row => {
            const cols = Array.from(row.querySelectorAll('td, th'));
            return cols.map(col => `"${col.textContent.trim()}"`).join(',');
        }).join('\n');

        this.downloadCSV(csvContent, filename);
    },

    /**
     * Download CSV content
     */
    downloadCSV(content, filename) {
        const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    },

    /**
     * Print specific element
     */
    printElement(elementId) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print</title>
                    <link rel="stylesheet" href="${base_url}assets/css/bootstrap.min.css">
                    <link rel="stylesheet" href="${base_url}assets/css/kaprodi-publikasi.css">
                </head>
                <body onload="window.print();window.close();">
                    ${element.outerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
    }
};

/**
 * Initialize when document is ready
 */
$(document).ready(function() {
    // Initialize main class
    window.kaprodiPublikasi = new KaprodiPublikasi();
    
    // Expose export utilities globally
    window.ExportUtilities = ExportUtilities;
    
    // Set base URL for AJAX calls
    if (typeof base_url === 'undefined') {
        window.base_url = $('meta[name="base_url"]').attr('content') || '/';
    }
});

/**
 * Additional utility functions
 */

// Format numbers with thousand separators
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Get time ago format
function timeAgo(date) {
    const now = new Date();
    const past = new Date(date);
    const diff = now - past;
    
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (days > 0) return `${days} hari yang lalu`;
    if (hours > 0) return `${hours} jam yang lalu`;
    if (minutes > 0) return `${minutes} menit yang lalu`;
    return 'Baru saja';
}

// Debounce function for search inputs
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}