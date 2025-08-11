/**
 * Script untuk halaman detail seminar proposal staf
 * Menghandle countdown timer dan interaktivitas detail
 */
const SeminarProposalDetail = {
    
    countdownInterval: null,
    
    init: function() {
        this.initCountdownTimer();
        this.initQuickActions();
        this.initTooltips();
        this.initModals();
    },
    
    /**
     * Initialize countdown timer untuk jadwal seminar
     */
    initCountdownTimer: function() {
        const $countdown = $('#countdown');
        if ($countdown.length > 0) {
            const targetDate = $countdown.data('target');
            if (targetDate) {
                this.startCountdown(targetDate);
            }
        }
    },
    
    /**
     * Start countdown timer
     */
    startCountdown: function(targetDate) {
        const target = new Date(targetDate).getTime();
        
        this.countdownInterval = setInterval(() => {
            const now = new Date().getTime();
            const distance = target - now;
            
            if (distance < 0) {
                clearInterval(this.countdownInterval);
                $('#countdown').html('<span class="badge badge-secondary">Telah Selesai</span>');
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            $('#days').text(days);
            $('#hours').text(hours);
            $('#minutes').text(minutes);
            
            // Optional: show seconds
            // $('#seconds').text(seconds);
            
            // Change color based on time remaining
            if (days < 1) {
                $('#countdown').removeClass('text-primary').addClass('text-danger');
            } else if (days < 3) {
                $('#countdown').removeClass('text-primary').addClass('text-warning');
            }
        }, 60000); // Update every minute
    },
    
    /**
     * Initialize quick actions
     */
    initQuickActions: function() {
        // Copy email functionality
        $(document).on('click', '[href^="mailto:"]', function(e) {
            e.preventDefault();
            const email = $(this).attr('href').replace('mailto:', '');
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(email).then(() => {
                    this.showToast('Email berhasil disalin!', 'success');
                });
            }
        });
        
        // Download progress indication
        $(document).on('click', '.list-group-item[href*="download"]', function() {
            const $item = $(this);
            const $icon = $item.find('.fa-external-link-alt');
            
            $icon.removeClass('fa-external-link-alt').addClass('fa-spinner fa-spin');
            
            setTimeout(() => {
                $icon.removeClass('fa-spinner fa-spin').addClass('fa-external-link-alt');
            }, 2000);
        });
    },
    
    /**
     * Initialize tooltips
     */
    initTooltips: function() {
        $('[data-toggle="tooltip"]').tooltip();
    },
    
    /**
     * Initialize modals (if any)
     */
    initModals: function() {
        // Handle any modal functionality here
    },
    
    /**
     * Show toast notification
     */
    showToast: function(message, type = 'info') {
        // Using toastr if available, otherwise fallback to alert
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            alert(message);
        }
    },
    
    /**
     * Cleanup when leaving page
     */
    destroy: function() {
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
        }
    }
};