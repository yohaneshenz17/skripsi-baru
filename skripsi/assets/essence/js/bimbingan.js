/*!
 * Bimbingan Enhancement JavaScript - ESSENCE STRUCTURE
 * STK Santo Yakobus Merauke
 * Version: 2.2.0 - Fixed Function Conflicts
 * Path: assets/essence/js/bimbingan.js
 */

(function($) {
    'use strict';
    
    /**
     * Bimbingan Manager Class
     * Handles all bimbingan-related enhancements WITHOUT conflicting with content page functions
     */
    class BimbinganManager {
        constructor() {
            // Configuration
            this.baseUrl = window.location.origin + '/skripsi/';
            this.currentEditJurnalId = null;
            this.isProcessing = false;
            this.debug = true; // Enable debug untuk troubleshooting
            
            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.init());
            } else {
                this.init();
            }
        }
        
        /**
         * Initialize all enhancements
         */
        init() {
            this.log('Initializing Bimbingan Manager (Essence Structure)...');
            
            try {
                this.bindEvents();
                this.enhanceUI();
                this.initializeTooltips();
                this.setupQuickValidation();
                this.checkExistingFunctions();
                
                this.log('Bimbingan Manager initialized successfully');
            } catch (error) {
                console.error('Error initializing Bimbingan Manager:', error);
            }
        }
        
        /**
         * Check existing functions to avoid conflicts
         */
        checkExistingFunctions() {
            const existingFunctions = [
                'exportJurnal',
                'tambahJurnalBimbingan', 
                'validasiJurnal',
                'editJurnal',
                'deleteJurnal'
            ];
            
            this.log('Checking existing functions:');
            existingFunctions.forEach(funcName => {
                if (typeof window[funcName] === 'function') {
                    this.log(`- ${funcName}: EXISTS (content page function)`);
                } else {
                    this.log(`- ${funcName}: NOT FOUND (will be provided by manager)`);
                }
            });
        }
        
        /**
         * Bind all event listeners
         */
        bindEvents() {
            // Button click events
            $(document).on('click', '[data-action]', (e) => {
                e.preventDefault();
                this.handleActionClick(e);
            });
            
            // Form submission events - hanya handle jika tidak ada handler lain
            if (!$('#formJurnal').data('hasHandler')) {
                $(document).on('submit', '#formJurnal', (e) => {
                    if (this.currentEditJurnalId) {
                        e.preventDefault();
                        this.handleEditJurnal(e.target);
                    }
                });
                $('#formJurnal').data('hasHandler', true);
            }
            
            // Modal events
            $('#modalJurnal').on('show.bs.modal', () => this.handleModalShow());
            $('#modalJurnal').on('hidden.bs.modal', () => this.handleModalHide());
            
            // Table enhancements
            this.enhanceTables();
        }
        
        /**
         * Handle action button clicks
         */
        handleActionClick(e) {
            const $button = $(e.currentTarget);
            const action = $button.data('action');
            
            switch (action) {
                case 'edit-jurnal':
                    // Check if content page has this function
                    if (typeof window.editJurnal === 'function') {
                        window.editJurnal($button.data('jurnal-id'));
                    } else {
                        this.editJurnal($button.data('jurnal-id'));
                    }
                    break;
                case 'delete-jurnal':
                    // Check if content page has this function
                    if (typeof window.deleteJurnal === 'function') {
                        window.deleteJurnal($button.data('jurnal-id'));
                    } else {
                        this.deleteJurnal($button.data('jurnal-id'));
                    }
                    break;
                case 'quick-validasi':
                    this.quickValidasi($button.data('jurnal-id'), $button.data('status'));
                    break;
                case 'export-pdf':
                    // Check if content page has exportJurnal function
                    if (typeof window.exportJurnal === 'function') {
                        this.log('Using content page exportJurnal function');
                        window.exportJurnal();
                    } else {
                        this.log('Using manager exportPDF function');
                        this.exportPDF($button.data('proposal-id'));
                    }
                    break;
                default:
                    this.log('Unknown action:', action);
            }
        }
        
        /**
         * Edit jurnal bimbingan - fallback jika tidak ada di content page
         */
        async editJurnal(jurnalId) {
            if (this.isProcessing || !jurnalId) return;
            
            try {
                this.showLoading('Memuat data jurnal...');
                this.isProcessing = true;
                
                const response = await this.apiCall(`dosen/bimbingan/get_jurnal/${jurnalId}`);
                
                if (response.error) {
                    throw new Error(response.message);
                }
                
                this.populateEditForm(response.data);
                this.currentEditJurnalId = jurnalId;
                
                $('#modalJurnal').modal('show');
                
            } catch (error) {
                this.showToast('Error: ' + error.message, 'error');
                this.log('Edit jurnal error:', error);
            } finally {
                this.hideLoading();
                this.isProcessing = false;
            }
        }
        
        /**
         * Handle edit jurnal form submission
         */
        async handleEditJurnal(form) {
            if (this.isProcessing) return;
            
            try {
                this.isProcessing = true;
                const $submitBtn = $(form).find('[type="submit"]');
                this.setButtonLoading($submitBtn, true);
                
                const formData = new FormData(form);
                formData.append('jurnal_id', this.currentEditJurnalId);
                
                const response = await this.apiCall('dosen/bimbingan/edit_jurnal', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.error) {
                    throw new Error(response.message);
                }
                
                this.showToast('Jurnal berhasil diupdate!', 'success');
                $('#modalJurnal').modal('hide');
                
                // Reload page to update data
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
                
            } catch (error) {
                this.showToast('Error: ' + error.message, 'error');
                this.log('Handle edit jurnal error:', error);
            } finally {
                this.isProcessing = false;
                const $submitBtn = $(form).find('[type="submit"]');
                this.setButtonLoading($submitBtn, false);
            }
        }
        
        /**
         * Delete jurnal bimbingan - fallback jika tidak ada di content page
         */
        async deleteJurnal(jurnalId) {
            if (this.isProcessing || !jurnalId) return;
            
            const confirmed = await this.showConfirm(
                'Hapus Jurnal Bimbingan',
                'Apakah Anda yakin ingin menghapus jurnal bimbingan ini?\n\nData yang dihapus tidak dapat dikembalikan!',
                'danger'
            );
            
            if (!confirmed) return;
            
            try {
                this.isProcessing = true;
                this.showLoading('Menghapus jurnal...');
                
                const formData = new FormData();
                formData.append('jurnal_id', jurnalId);
                
                const response = await this.apiCall('dosen/bimbingan/delete_jurnal', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.error) {
                    throw new Error(response.message);
                }
                
                this.showToast('Jurnal berhasil dihapus!', 'success');
                
                // Reload page to update data
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
                
            } catch (error) {
                this.showToast('Error: ' + error.message, 'error');
                this.log('Delete jurnal error:', error);
            } finally {
                this.isProcessing = false;
                this.hideLoading();
            }
        }
        
        /**
         * Quick validation setup
         */
        setupQuickValidation() {
            // Only setup if not already handled by content page
            if (!$('#quickValidasiSubmit').data('hasHandler')) {
                $('#quickValidasiSubmit').off('click').on('click', async () => {
                    const modal = $('#modalQuickValidasi');
                    const jurnalId = modal.data('jurnal-id');
                    const status = modal.data('status');
                    const catatan = $('#quickCatatanDosen').val().trim();
                    
                    if (status == 2 && catatan.length < 5) {
                        this.showToast('Catatan wajib diisi untuk revisi (minimal 5 karakter)!', 'warning');
                        $('#quickCatatanDosen').focus();
                        return;
                    }
                    
                    try {
                        this.isProcessing = true;
                        const $submitBtn = $('#quickValidasiSubmit');
                        this.setButtonLoading($submitBtn, true);
                        
                        const formData = new FormData();
                        formData.append('jurnal_id', jurnalId);
                        formData.append('status_validasi', status);
                        formData.append('catatan_dosen', catatan);
                        
                        const response = await this.apiCall('dosen/bimbingan/quick_validasi', {
                            method: 'POST',
                            body: formData
                        });
                        
                        if (response.error) {
                            throw new Error(response.message);
                        }
                        
                        this.showToast('Success: ' + response.message, 'success');
                        
                        // Reload page after short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                        
                    } catch (error) {
                        this.showToast('Error: ' + error.message, 'error');
                        this.log('Quick validasi error:', error);
                    } finally {
                        this.isProcessing = false;
                        const $submitBtn = $('#quickValidasiSubmit');
                        this.setButtonLoading($submitBtn, false);
                        modal.modal('hide');
                    }
                });
                $('#quickValidasiSubmit').data('hasHandler', true);
            }
        }
        
        /**
         * Quick validasi modal setup
         */
        quickValidasi(jurnalId, status) {
            try {
                const modal = $('#modalQuickValidasi');
                const infoDiv = modal.find('#quickValidasiInfo');
                const submitBtn = modal.find('#quickValidasiSubmit');
                
                if (status == 1) {
                    infoDiv.removeClass().addClass('alert alert-success');
                    infoDiv.html('<i class="fa fa-check"></i> <strong>Validasi Jurnal</strong><br>Jurnal akan ditandai sebagai tervalidasi.');
                    submitBtn.removeClass().addClass('btn btn-success').text('Validasi Jurnal');
                } else {
                    infoDiv.removeClass().addClass('alert alert-warning');
                    infoDiv.html('<i class="fa fa-edit"></i> <strong>Minta Revisi</strong><br>Jurnal akan dikembalikan untuk diperbaiki. <strong>Catatan wajib diisi.</strong>');
                    submitBtn.removeClass().addClass('btn btn-warning').text('Minta Revisi');
                }
                
                // Store data for submission
                modal.data('jurnal-id', jurnalId);
                modal.data('status', status);
                modal.find('#quickCatatanDosen').val('');
                
                modal.modal('show');
                
            } catch (error) {
                this.showToast('Error: ' + error.message, 'error');
                this.log('Quick validasi setup error:', error);
            }
        }
        
        /**
         * Export PDF - fallback function
         */
        exportPDF(proposalId = null) {
            try {
                // Method 1: Gunakan parameter yang diberikan
                if (proposalId) {
                    this.log('Using provided proposal ID:', proposalId);
                    this.performExport(proposalId);
                    return;
                }
                
                // Method 2: Ambil dari URL current page
                const currentUrl = window.location.href;
                const urlParts = currentUrl.split('/');
                const detailIndex = urlParts.indexOf('detail_mahasiswa');
                
                if (detailIndex !== -1 && urlParts[detailIndex + 1]) {
                    proposalId = urlParts[detailIndex + 1];
                    this.log('Found proposal ID in URL:', proposalId);
                    this.performExport(proposalId);
                    return;
                }
                
                // Method 3: Ambil dari data attribute
                const proposalElement = document.querySelector('[data-proposal-id]');
                if (proposalElement) {
                    proposalId = proposalElement.dataset.proposalId;
                    this.log('Found proposal ID in data attribute:', proposalId);
                    this.performExport(proposalId);
                    return;
                }
                
                // Jika semua method gagal
                this.showToast('ID proposal tidak ditemukan! Pastikan Anda berada di halaman detail mahasiswa.', 'error');
                console.error('Export PDF Error: Proposal ID not found. Current URL:', currentUrl);
                
            } catch (error) {
                this.showToast('Error saat export PDF: ' + error.message, 'error');
                this.log('Export PDF error:', error);
            }
        }
        
        /**
         * Perform actual PDF export
         */
        performExport(proposalId) {
            if (!proposalId) {
                this.showToast('ID proposal tidak valid!', 'error');
                return;
            }
            
            try {
                const url = this.baseUrl + `dosen/bimbingan/export_jurnal/${proposalId}`;
                this.log('Opening export URL:', url);
                
                // Show loading toast
                this.showToast('Memproses export PDF...', 'info', 3000);
                
                // Open in new tab
                const newWindow = window.open(url, '_blank');
                
                // Check if popup blocked
                if (!newWindow || newWindow.closed || typeof newWindow.closed == 'undefined') {
                    this.showToast('Popup diblokir! Silakan allow popup untuk download PDF.', 'warning');
                    // Fallback: try direct navigation
                    window.location.href = url;
                }
                
            } catch (error) {
                this.showToast('Error saat membuka PDF: ' + error.message, 'error');
                this.log('Perform export error:', error);
            }
        }
        
        /**
         * Populate edit form with data
         */
        populateEditForm(data) {
            try {
                $('#modalJurnalTitle').text('Edit Jurnal Bimbingan');
                $('#submitJurnalBtn').text('Update Jurnal');
                
                // Populate form fields
                $('#input_pertemuan_ke').val(data.pertemuan_ke);
                $('#input_tanggal_bimbingan').val(data.tanggal_bimbingan);
                $('#input_materi_bimbingan').val(data.materi_bimbingan);
                $('#input_catatan_dosen').val(data.catatan_dosen || '');
                $('#input_tindak_lanjut').val(data.tindak_lanjut || '');
                
                this.log('Form populated with data:', data);
            } catch (error) {
                this.log('Error populating form:', error);
            }
        }
        
        /**
         * Show loading overlay
         */
        showLoading(message = 'Loading...') {
            let $overlay = $('#loadingOverlay');
            if ($overlay.length === 0) {
                $overlay = $(`
                    <div id="loadingOverlay" class="loading-overlay">
                        <div class="loading-content">
                            <div class="loading-spinner"></div>
                            <div id="loadingMessage">${message}</div>
                        </div>
                    </div>
                `);
                $('body').append($overlay);
            } else {
                $('#loadingMessage').text(message);
                $overlay.show();
            }
        }
        
        /**
         * Hide loading overlay
         */
        hideLoading() {
            $('#loadingOverlay').fadeOut(200);
        }
        
        /**
         * Set button loading state
         */
        setButtonLoading($button, loading) {
            if (!$button || $button.length === 0) return;
            
            if (loading) {
                $button.prop('disabled', true);
                $button.data('original-text', $button.html());
                $button.html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $button.addClass('btn-loading');
            } else {
                $button.prop('disabled', false);
                $button.html($button.data('original-text') || $button.html());
                $button.removeClass('btn-loading');
            }
        }
        
        /**
         * Show toast notification
         */
        showToast(message, type = 'info', duration = 5000) {
            try {
                // Create toast container if not exists
                let $container = $('#toastContainer');
                if ($container.length === 0) {
                    $container = $('<div id="toastContainer" class="toast-container"></div>');
                    $('body').append($container);
                }
                
                // Create toast
                const toastId = 'toast-' + Date.now();
                const iconClass = {
                    success: 'fa-check-circle text-success',
                    error: 'fa-exclamation-circle text-danger',
                    warning: 'fa-exclamation-triangle text-warning',
                    info: 'fa-info-circle text-info'
                }[type] || 'fa-info-circle text-info';
                
                const $toast = $(`
                    <div id="${toastId}" class="toast toast-custom toast-${type} show" role="alert">
                        <div class="toast-header">
                            <i class="fa ${iconClass} mr-2"></i>
                            <strong class="mr-auto">Sistem Bimbingan</strong>
                            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="toast-body">${message}</div>
                    </div>
                `);
                
                $container.append($toast);
                
                // Auto-hide
                setTimeout(() => {
                    $toast.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, duration);
                
            } catch (error) {
                // Fallback to alert
                alert(message);
                this.log('Toast error:', error);
            }
        }
        
        /**
         * Show confirmation dialog
         */
        async showConfirm(title, message, type = 'warning') {
            return new Promise((resolve) => {
                // Use SweetAlert if available
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: title,
                        text: message,
                        icon: type,
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: type === 'danger' ? '#dc3545' : '#007bff',
                        reverseButtons: true
                    }).then((result) => {
                        resolve(result.isConfirmed);
                    });
                } else {
                    // Fallback to native confirm
                    resolve(confirm(message));
                }
            });
        }
        
        /**
         * API call helper
         */
        async apiCall(endpoint, options = {}) {
            const url = this.baseUrl + endpoint;
            
            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        ...options.headers
                    },
                    ...options
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                return data;
                
            } catch (error) {
                this.log('API call failed:', {
                    url: url,
                    error: error.message,
                    options: options
                });
                throw error;
            }
        }
        
        /**
         * Enhance UI elements
         */
        enhanceUI() {
            // Add enhanced classes to tables
            $('.table').addClass('table-jurnal-enhanced');
            
            // Add enhanced classes to cards
            $('.card').addClass('card-enhanced');
            
            // Add enhanced classes to dropdowns
            $('.dropdown-menu').addClass('dropdown-menu-enhanced');
            
            // Enhance progress bars
            $('.progress').addClass('progress-enhanced');
            
            // Enhance modals
            $('.modal').addClass('modal-enhanced');
        }
        
        /**
         * Enhance tables
         */
        enhanceTables() {
            // Initialize DataTables if available and not already initialized
            if (typeof $.fn.DataTable !== 'undefined') {
                $('.table-jurnal').each(function() {
                    if (!$.fn.DataTable.isDataTable(this)) {
                        $(this).DataTable({
                            "language": {
                                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                            },
                            "order": [[ 0, "desc" ]],
                            "pageLength": 25,
                            "responsive": true,
                            "dom": 'frtip'
                        });
                    }
                });
            }
        }
        
        /**
         * Initialize tooltips
         */
        initializeTooltips() {
            if (typeof $().tooltip === 'function') {
                $('[data-toggle="tooltip"]').tooltip();
            }
        }
        
        /**
         * Handle modal show
         */
        handleModalShow() {
            // Reset form if not in edit mode
            if (!this.currentEditJurnalId) {
                $('#modalJurnalTitle').text('Tambah Jurnal Bimbingan');
                $('#submitJurnalBtn').text('Simpan Jurnal');
            }
        }
        
        /**
         * Handle modal hide
         */
        handleModalHide() {
            this.currentEditJurnalId = null;
            if ($('#formJurnal').length > 0) {
                $('#formJurnal')[0].reset();
            }
        }
        
        /**
         * Debug logging
         */
        log(...args) {
            if (this.debug) {
                console.log('[BimbinganManager]', ...args);
            }
        }
    }
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        // Create global instance
        window.bimbinganManager = new BimbinganManager();
        
        // PERBAIKAN: Hanya provide fallback functions jika belum ada
        // Ini memungkinkan content page functions tetap diutamakan
        
        if (typeof window.editJurnal !== 'function') {
            window.editJurnal = function(jurnalId) {
                window.bimbinganManager.editJurnal(jurnalId);
            };
        }
        
        if (typeof window.deleteJurnal !== 'function') {
            window.deleteJurnal = function(jurnalId) {
                window.bimbinganManager.deleteJurnal(jurnalId);
            };
        }
        
        if (typeof window.quickValidasi !== 'function') {
            window.quickValidasi = function(jurnalId, status) {
                window.bimbinganManager.quickValidasi(jurnalId, status);
            };
        }
        
        // UNTUK EXPORT: Hanya provide fallback jika benar-benar tidak ada
        // Content page punya priority
        if (typeof window.exportJurnal !== 'function') {
            window.exportJurnal = function(proposalId = null) {
                window.bimbinganManager.exportPDF(proposalId);
            };
        }
        
        // Log final function availability
        console.log('=== Final Function Status ===');
        console.log('exportJurnal:', typeof window.exportJurnal, window.exportJurnal.toString().substring(0, 50) + '...');
        console.log('editJurnal:', typeof window.editJurnal);
        console.log('deleteJurnal:', typeof window.deleteJurnal);
        console.log('quickValidasi:', typeof window.quickValidasi);
    });
    
})(jQuery);