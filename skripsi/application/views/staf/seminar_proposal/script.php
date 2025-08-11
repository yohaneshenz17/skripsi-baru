/**
 * Script untuk halaman index seminar proposal staf
 * Menghandle DataTable, filtering, dan interaktivitas
 */
const SeminarProposalIndex = {
    
    init: function() {
        this.initDataTable();
        this.initDropdownSubmenu();
        this.initTooltips();
        this.initFilterHandling();
        this.initQuickActions();
    },
    
    /**
     * Initialize DataTable untuk list seminar proposal
     */
    initDataTable: function() {
        if ($('#seminarTable').length > 0) {
            $('#seminarTable').DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "pageLength": 10,
                "order": [[5, "asc"]], // Sort by jadwal (kolom 5)
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "zeroRecords": "Data tidak ditemukan",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                    "infoFiltered": "(difilter dari _MAX_ total data)",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
                "columnDefs": [
                    {
                        "targets": [7], // Kolom Action
                        "orderable": false,
                        "searchable": false
                    }
                ]
            });
        }
    },
    
    /**
     * Initialize dropdown submenu functionality
     */
    initDropdownSubmenu: function() {
        // Handle dropdown submenu for form penilaian
        $(document).on('click', '.dropdown-submenu a.dropdown-toggle', function(e) {
            if (!$(this).next().hasClass('show')) {
                $(this).parents('.dropdown-menu').first().find('.show').removeClass('show');
            }
            var $subMenu = $(this).next('.dropdown-menu');
            $subMenu.toggleClass('show');
            
            $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
                $('.dropdown-submenu .show').removeClass('show');
            });
            
            return false;
        });
    },
    
    /**
     * Initialize tooltips
     */
    initTooltips: function() {
        $('[data-toggle="tooltip"]').tooltip();
    },
    
    /**
     * Handle filter form functionality
     */
    initFilterHandling: function() {
        // Auto-submit form when select changes
        $('#filter_prodi, #filter_bulan').on('change', function() {
            // Optional: auto-submit filter
            // $(this).closest('form').submit();
        });
        
        // Enter key search
        $('#search').on('keypress', function(e) {
            if (e.which === 13) {
                $(this).closest('form').submit();
            }
        });
    },
    
    /**
     * Handle quick actions
     */
    initQuickActions: function() {
        // Confirmation for delete actions (if any)
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            const nama = $(this).data('nama');
            
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus data seminar ${nama}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
        
        // Loading state for download buttons
        $(document).on('click', 'a[href*="download"], a[href*="cetak"]', function() {
            const $btn = $(this);
            const originalText = $btn.html();
            
            $btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Generating...');
            $btn.prop('disabled', true);
            
            // Reset button after 3 seconds
            setTimeout(() => {
                $btn.html(originalText);
                $btn.prop('disabled', false);
            }, 3000);
        });
    }
};
