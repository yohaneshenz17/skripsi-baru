/**
 * Script untuk halaman detail seminar proposal staf - FIXED VERSION
 * Menghandle countdown timer dan interaktivitas detail
 * 
 * FIXED:
 * - Error handling yang lebih robust
 * - Safe element checking
 * - Fallback untuk missing elements
 * - Compatible dengan controller yang sudah diperbaiki
 */
const SeminarProposalDetail = {
    
    countdownInterval: null,
    
    /**
     * Initialize semua functionality
     */
    init: function() {
        // Pastikan DOM sudah ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.initializeComponents();
            });
        } else {
            this.initializeComponents();
        }
    },
    
    /**
     * Initialize semua komponen setelah DOM ready
     */
    initializeComponents: function() {
        try {
            this.initCountdownTimer();
            this.initQuickActions();
            this.initTooltips();
            this.initModals();
            this.initDownloadHandlers();
            this.initEmailHandlers();
            
            console.log('SeminarProposalDetail initialized successfully');
        } catch (error) {
            console.error('Error initializing SeminarProposalDetail:', error);
        }
    },
    
    /**
     * Initialize countdown timer untuk jadwal seminar
     */
    initCountdownTimer: function() {
        try {
            const $countdown = $('#countdown');
            if ($countdown.length > 0) {
                const targetDate = $countdown.data('target');
                if (targetDate && targetDate.trim() !== '') {
                    this.startCountdown(targetDate);
                } else {
                    console.warn('Countdown target date not found or empty');
                }
            } else {
                console.log('Countdown element not found - seminar may not be scheduled yet');
            }
        } catch (error) {
            console.error('Error initializing countdown timer:', error);
        }
    },
    
    /**
     * Start countdown timer dengan error handling
     */
    startCountdown: function(targetDate) {
        try {
            const target = new Date(targetDate).getTime();
            
            // Validasi target date
            if (isNaN(target)) {
                console.error('Invalid target date for countdown:', targetDate);
                this.showCountdownError();
                return;
            }
            
            // Clear existing interval
            if (this.countdownInterval) {
                clearInterval(this.countdownInterval);
            }
            
            this.countdownInterval = setInterval(() => {
                try {
                    const now = new Date().getTime();
                    const distance = target - now;
                    
                    if (distance < 0) {
                        clearInterval(this.countdownInterval);
                        this.showCountdownFinished();
                        return;
                    }
                    
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    
                    // Update display dengan safe element checking
                    this.updateCountdownDisplay(days, hours, minutes);
                    
                    // Change color based on time remaining
                    this.updateCountdownColor(days);
                    
                } catch (error) {
                    console.error('Error in countdown interval:', error);
                    clearInterval(this.countdownInterval);
                    this.showCountdownError();
                }
            }, 60000); // Update every minute
            
            // Initial update
            setTimeout(() => {
                const now = new Date().getTime();
                const distance = target - now;
                
                if (distance > 0) {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    
                    this.updateCountdownDisplay(days, hours, minutes);
                    this.updateCountdownColor(days);
                }
            }, 100);
            
        } catch (error) {
            console.error('Error starting countdown:', error);
            this.showCountdownError();
        }
    },
    
    /**
     * Update countdown display dengan safe checking
     */
    updateCountdownDisplay: function(days, hours, minutes) {
        try {
            const $days = $('#days');
            const $hours = $('#hours');
            const $minutes = $('#minutes');
            
            if ($days.length > 0) $days.text(days);
            if ($hours.length > 0) $hours.text(hours);
            if ($minutes.length > 0) $minutes.text(minutes);
            
        } catch (error) {
            console.error('Error updating countdown display:', error);
        }
    },
    
    /**
     * Update countdown color berdasarkan waktu tersisa
     */
    updateCountdownColor: function(days) {
        try {
            const $countdown = $('#countdown');
            if ($countdown.length > 0) {
                $countdown.removeClass('text-primary text-warning text-danger');
                
                if (days < 1) {
                    $countdown.addClass('text-danger');
                } else if (days < 3) {
                    $countdown.addClass('text-warning');
                } else {
                    $countdown.addClass('text-primary');
                }
            }
        } catch (error) {
            console.error('Error updating countdown color:', error);
        }
    },
    
    /**
     * Show countdown finished state
     */
    showCountdownFinished: function() {
        try {
            const $countdown = $('#countdown');
            if ($countdown.length > 0) {
                $countdown.html('<span class="badge badge-secondary">Telah Selesai</span>');
            }
        } catch (error) {
            console.error('Error showing countdown finished:', error);
        }
    },
    
    /**
     * Show countdown error state
     */
    showCountdownError: function() {
        try {
            const $countdown = $('#countdown');
            if ($countdown.length > 0) {
                $countdown.html('<span class="badge badge-secondary">Error loading countdown</span>');
            }
        } catch (error) {
            console.error('Error showing countdown error:', error);
        }
    },
    
    /**
     * Initialize quick actions
     */
    initQuickActions: function() {
        try {
            // Scroll to top button (jika ada)
            this.initScrollToTop();
            
            // Copy functionality untuk berbagai elemen
            this.initCopyFunctionality();
            
        } catch (error) {
            console.error('Error initializing quick actions:', error);
        }
    },
    
    /**
     * Initialize email handlers
     */
    initEmailHandlers: function() {
        try {
            // Handle email clicks dengan copy functionality
            $(document).on('click', 'a[href^="mailto:"]', function(e) {
                const email = $(this).attr('href').replace('mailto:', '');
                
                // Try to copy email to clipboard
                if (navigator.clipboard && email !== 'Tidak tersedia') {
                    e.preventDefault();
                    navigator.clipboard.writeText(email).then(() => {
                        SeminarProposalDetail.showToast('Email berhasil disalin: ' + email, 'success');
                    }).catch(() => {
                        // Fallback: biarkan mailto handler default
                        window.location.href = $(this).attr('href');
                    });
                }
            });
            
        } catch (error) {
            console.error('Error initializing email handlers:', error);
        }
    },
    
    /**
     * Initialize copy functionality
     */
    initCopyFunctionality: function() {
        try {
            // Copy buttons (jika ada)
            $(document).on('click', '[data-copy]', function(e) {
                e.preventDefault();
                const textToCopy = $(this).data('copy') || $(this).text();
                
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(textToCopy).then(() => {
                        SeminarProposalDetail.showToast('Teks berhasil disalin!', 'success');
                    }).catch(() => {
                        SeminarProposalDetail.showToast('Gagal menyalin teks', 'error');
                    });
                }
            });
            
        } catch (error) {
            console.error('Error initializing copy functionality:', error);
        }
    },
    
    /**
     * Initialize download handlers
     */
    initDownloadHandlers: function() {
        try {
            // Download progress indication
            $(document).on('click', '.list-group-item[href*="download"], a[href*="download"]', function() {
                const $item = $(this);
                const $icon = $item.find('.fa-external-link-alt, .fas.fa-download');
                
                if ($icon.length > 0) {
                    const originalClass = $icon.attr('class');
                    $icon.removeClass().addClass('fas fa-spinner fa-spin');
                    
                    // Reset after 3 seconds
                    setTimeout(() => {
                        $icon.removeClass().addClass(originalClass);
                    }, 3000);
                }
            });
            
            // Handle dropdown download items
            $(document).on('click', '.dropdown-item[href*="download"]', function() {
                const $item = $(this);
                const text = $item.text().trim();
                
                SeminarProposalDetail.showToast('Memproses download: ' + text, 'info');
            });
            
        } catch (error) {
            console.error('Error initializing download handlers:', error);
        }
    },
    
    /**
     * Initialize scroll to top functionality
     */
    initScrollToTop: function() {
        try {
            // Create scroll to top button if it doesn't exist
            if ($('#scrollToTop').length === 0) {
                $('body').append(`
                    <button id="scrollToTop" class="btn btn-primary" style="
                        position: fixed;
                        bottom: 20px;
                        right: 20px;
                        display: none;
                        z-index: 1000;
                        border-radius: 50%;
                        width: 50px;
                        height: 50px;
                    ">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                `);
            }
            
            // Show/hide scroll to top button
            $(window).scroll(function() {
                try {
                    if ($(this).scrollTop() > 300) {
                        $('#scrollToTop').fadeIn();
                    } else {
                        $('#scrollToTop').fadeOut();
                    }
                } catch (error) {
                    console.error('Error in scroll handler:', error);
                }
            });
            
            // Scroll to top click handler
            $(document).on('click', '#scrollToTop', function() {
                $('html, body').animate({scrollTop: 0}, 'slow');
            });
            
        } catch (error) {
            console.error('Error initializing scroll to top:', error);
        }
    },
    
    /**
     * Initialize tooltips dengan fallback
     */
    initTooltips: function() {
        try {
            if (typeof $.fn.tooltip === 'function') {
                $('[data-toggle="tooltip"]').tooltip({
                    container: 'body',
                    trigger: 'hover'
                });
            } else {
                console.warn('Bootstrap tooltip not available');
            }
        } catch (error) {
            console.error('Error initializing tooltips:', error);
        }
    },
    
    /**
     * Initialize modals (if any)
     */
    initModals: function() {
        try {
            // Handle modal functionality here if needed
            // Untuk sementara hanya log
            console.log('Modal handlers initialized (none configured yet)');
        } catch (error) {
            console.error('Error initializing modals:', error);
        }
    },
    
    /**
     * Show toast notification dengan fallback
     */
    showToast: function(message, type = 'info') {
        try {
            // Using toastr if available
            if (typeof toastr !== 'undefined') {
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "timeOut": "3000"
                };
                toastr[type](message);
            } else if (typeof Swal !== 'undefined') {
                // Fallback to SweetAlert if available
                Swal.fire({
                    icon: type === 'success' ? 'success' : type === 'error' ? 'error' : 'info',
                    title: message,
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                // Final fallback to alert
                console.log(`${type.toUpperCase()}: ${message}`);
                if (type === 'error') {
                    alert(message);
                }
            }
        } catch (error) {
            console.error('Error showing toast:', error);
            // Ultimate fallback
            console.log(`TOAST ${type.toUpperCase()}: ${message}`);
        }
    },
    
    /**
     * Helper: Check if element exists
     */
    elementExists: function(selector) {
        try {
            return $(selector).length > 0;
        } catch (error) {
            console.error('Error checking element existence:', error);
            return false;
        }
    },
    
    /**
     * Helper: Safe element operation
     */
    safeElementOperation: function(selector, operation) {
        try {
            const $element = $(selector);
            if ($element.length > 0) {
                return operation($element);
            } else {
                console.warn(`Element not found: ${selector}`);
                return null;
            }
        } catch (error) {
            console.error(`Error in safe element operation for ${selector}:`, error);
            return null;
        }
    },
    
    /**
     * Cleanup when leaving page
     */
    destroy: function() {
        try {
            if (this.countdownInterval) {
                clearInterval(this.countdownInterval);
                this.countdownInterval = null;
            }
            
            // Remove event handlers yang mungkin masih aktif
            $(document).off('click', '[data-copy]');
            $(document).off('click', 'a[href^="mailto:"]');
            $(document).off('click', '.list-group-item[href*="download"]');
            $(document).off('click', '.dropdown-item[href*="download"]');
            $(document).off('click', '#scrollToTop');
            
            console.log('SeminarProposalDetail destroyed successfully');
        } catch (error) {
            console.error('Error destroying SeminarProposalDetail:', error);
        }
    },
    
    /**
     * Public method untuk refresh data jika diperlukan
     */
    refresh: function() {
        try {
            console.log('Refreshing SeminarProposalDetail...');
            this.destroy();
            this.init();
        } catch (error) {
            console.error('Error refreshing SeminarProposalDetail:', error);
        }
    }
};

// ✅ FIXED: Auto-initialize dengan error handling
try {
    // Initialize when script loads
    SeminarProposalDetail.init();
    
    // Also make it available globally for manual init if needed
    window.SeminarProposalDetail = SeminarProposalDetail;
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        SeminarProposalDetail.destroy();
    });
    
} catch (error) {
    console.error('Error during SeminarProposalDetail auto-initialization:', error);
}