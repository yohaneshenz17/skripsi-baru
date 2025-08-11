/**
 * Script untuk halaman input penilaian seminar proposal staf
 * Menghandle auto-calculate nilai, validasi, dan form submission
 */
const SeminarProposalPenilaian = {
    
    init: function() {
        this.initAutoCalculate();
        this.initFormValidation();
        this.initFormSubmission();
        this.initTooltips();
        this.initRealTimeValidation();
        this.calculateInitialValues();
    },
    
    /**
     * Initialize auto-calculate functionality
     */
    initAutoCalculate: function() {
        // Bind change event to all nilai input
        $(document).on('input change', '.nilai-input', () => {
            this.calculateAllValues();
        });
        
        // Bind change event to rekomendasi
        $(document).on('change', '#rekomendasi', () => {
            this.handleRekomendasi();
        });
    },
    
    /**
     * Calculate all values (komponen dan nilai akhir)
     */
    calculateAllValues: function() {
        this.calculateKomponen1();
        this.calculateKomponen2();
        this.calculateKomponen3();
        this.calculateNilaiAkhir();
    },
    
    /**
     * Calculate Komponen 1: Substansi dan Metode Penelitian
     */
    calculateKomponen1: function() {
        const nilai1_1 = parseFloat($('#nilai_substansi_1_1').val()) || 0;
        const nilai1_2 = parseFloat($('#nilai_substansi_1_2').val()) || 0;
        const nilai1_3 = parseFloat($('#nilai_substansi_1_3').val()) || 0;
        const nilai1_4 = parseFloat($('#nilai_substansi_1_4').val()) || 0;
        const nilai1_5 = parseFloat($('#nilai_substansi_1_5').val()) || 0;
        
        const rataRata = (nilai1_1 + nilai1_2 + nilai1_3 + nilai1_4 + nilai1_5) / 5;
        
        $('#rata_rata_substansi').text(rataRata.toFixed(1));
        $('#nilai_komponen_1').text(rataRata.toFixed(1));
        
        return rataRata;
    },
    
    /**
     * Calculate Komponen 2: Presentasi dan Teknik Penyajian
     */
    calculateKomponen2: function() {
        const nilai2_1 = parseFloat($('#nilai_presentasi_2_1').val()) || 0;
        const nilai2_2 = parseFloat($('#nilai_presentasi_2_2').val()) || 0;
        const nilai2_3 = parseFloat($('#nilai_presentasi_2_3').val()) || 0;
        
        const rataRata = (nilai2_1 + nilai2_2 + nilai2_3) / 3;
        
        $('#rata_rata_presentasi').text(rataRata.toFixed(1));
        $('#nilai_komponen_2').text(rataRata.toFixed(1));
        
        return rataRata;
    },
    
    /**
     * Calculate Komponen 3: Penguasaan Materi dan Diskusi
     */
    calculateKomponen3: function() {
        const nilai3_1 = parseFloat($('#nilai_diskusi_3_1').val()) || 0;
        const nilai3_2 = parseFloat($('#nilai_diskusi_3_2').val()) || 0;
        const nilai3_3 = parseFloat($('#nilai_diskusi_3_3').val()) || 0;
        
        const rataRata = (nilai3_1 + nilai3_2 + nilai3_3) / 3;
        
        $('#rata_rata_diskusi').text(rataRata.toFixed(1));
        $('#nilai_komponen_3').text(rataRata.toFixed(1));
        
        return rataRata;
    },
    
    /**
     * Calculate Nilai Akhir berdasarkan bobot
     */
    calculateNilaiAkhir: function() {
        const komponen1 = this.calculateKomponen1();
        const komponen2 = this.calculateKomponen2();
        const komponen3 = this.calculateKomponen3();
        
        // Bobot: Komponen 1 (50%), Komponen 2 (20%), Komponen 3 (30%)
        const nilaiAkhir = (komponen1 * 0.5) + (komponen2 * 0.2) + (komponen3 * 0.3);
        
        $('#nilai_akhir').text(nilaiAkhir.toFixed(1));
        
        // Update color based on score
        this.updateNilaiAkhirColor(nilaiAkhir);
        
        return nilaiAkhir;
    },
    
    /**
     * Update color of nilai akhir based on score
     */
    updateNilaiAkhirColor: function(nilai) {
        const $badge = $('#nilai_akhir');
        $badge.removeClass('badge-success badge-warning badge-danger badge-secondary');
        
        if (nilai >= 85) {
            $badge.addClass('badge-success');
        } else if (nilai >= 70) {
            $badge.addClass('badge-warning');
        } else if (nilai >= 55) {
            $badge.addClass('badge-danger');
        } else {
            $badge.addClass('badge-secondary');
        }
    },
    
    /**
     * Handle rekomendasi selection
     */
    handleRekomendasi: function() {
        const rekomendasi = $('#rekomendasi').val();
        const $catatanGroup = $('#catatan_saran').closest('.form-group');
        
        if (rekomendasi === 'diterima_revisi_minor' || 
            rekomendasi === 'diterima_revisi_mayor' || 
            rekomendasi === 'ditolak_mengulang') {
            
            $catatanGroup.find('label').html('Catatan/Saran Revisi <span class="text-danger">*</span>');
            $('#catatan_saran').prop('required', true);
            
            if (!$('#catatan_saran').val()) {
                $('#catatan_saran').focus();
            }
        } else {
            $catatanGroup.find('label').text('Catatan/Saran Revisi');
            $('#catatan_saran').prop('required', false);
        }
    },
    
    /**
     * Initialize form validation
     */
    initFormValidation: function() {
        // Custom validation rules
        $.validator.addMethod("range", function(value, element, param) {
            return this.optional(element) || (value >= param[0] && value <= param[1]);
        }, "Nilai harus antara {0} dan {1}.");
        
        // jQuery validation setup
        $('#penilaianForm').validate({
            rules: {
                nilai_substansi_1_1: { required: true, range: [1, 100] },
                nilai_substansi_1_2: { required: true, range: [1, 100] },
                nilai_substansi_1_3: { required: true, range: [1, 100] },
                nilai_substansi_1_4: { required: true, range: [1, 100] },
                nilai_substansi_1_5: { required: true, range: [1, 100] },
                nilai_presentasi_2_1: { required: true, range: [1, 100] },
                nilai_presentasi_2_2: { required: true, range: [1, 100] },
                nilai_presentasi_2_3: { required: true, range: [1, 100] },
                nilai_diskusi_3_1: { required: true, range: [1, 100] },
                nilai_diskusi_3_2: { required: true, range: [1, 100] },
                nilai_diskusi_3_3: { required: true, range: [1, 100] },
                rekomendasi: { required: true }
            },
            messages: {
                nilai_substansi_1_1: "Masukkan nilai antara 1-100",
                nilai_substansi_1_2: "Masukkan nilai antara 1-100",
                nilai_substansi_1_3: "Masukkan nilai antara 1-100",
                nilai_substansi_1_4: "Masukkan nilai antara 1-100",
                nilai_substansi_1_5: "Masukkan nilai antara 1-100",
                nilai_presentasi_2_1: "Masukkan nilai antara 1-100",
                nilai_presentasi_2_2: "Masukkan nilai antara 1-100",
                nilai_presentasi_2_3: "Masukkan nilai antara 1-100",
                nilai_diskusi_3_1: "Masukkan nilai antara 1-100",
                nilai_diskusi_3_2: "Masukkan nilai antara 1-100",
                nilai_diskusi_3_3: "Masukkan nilai antara 1-100",
                rekomendasi: "Pilih rekomendasi penguji"
            },
            errorClass: "text-danger",
            errorElement: "small",
            highlight: function(element) {
                $(element).addClass("is-invalid");
            },
            unhighlight: function(element) {
                $(element).removeClass("is-invalid");
            }
        });
    },
    
    /**
     * Initialize real-time validation
     */
    initRealTimeValidation: function() {
        // Real-time validation for nilai input
        $(document).on('blur', '.nilai-input', function() {
            const value = parseFloat($(this).val());
            const $input = $(this);
            
            if (isNaN(value) || value < 1 || value > 100) {
                $input.addClass('is-invalid');
                $input.next('.invalid-feedback').remove();
                $input.after('<div class="invalid-feedback">Nilai harus antara 1-100</div>');
            } else {
                $input.removeClass('is-invalid');
                $input.next('.invalid-feedback').remove();
            }
        });
        
        // Prevent invalid characters
        $(document).on('keypress', '.nilai-input', function(e) {
            // Allow: backspace, delete, tab, escape, enter
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                // Allow: Ctrl+A, Command+A
                (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    },
    
    /**
     * Initialize form submission
     */
    initFormSubmission: function() {
        $('#penilaianForm').on('submit', (e) => {
            if (!$('#penilaianForm').valid()) {
                e.preventDefault();
                this.showAlert('Mohon periksa kembali input Anda!', 'error');
                return false;
            }
            
            // Show loading state
            const $submitBtn = $('#submitBtn');
            const originalText = $submitBtn.html();
            
            $submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');
            $submitBtn.prop('disabled', true);
            
            // Restore button if form submission fails
            setTimeout(() => {
                $submitBtn.html(originalText);
                $submitBtn.prop('disabled', false);
            }, 10000);
        });
    },
    
    /**
     * Calculate initial values if editing existing penilaian
     */
    calculateInitialValues: function() {
        // Check if there are existing values
        if ($('#nilai_substansi_1_1').val()) {
            this.calculateAllValues();
        }
    },
    
    /**
     * Initialize tooltips
     */
    initTooltips: function() {
        $('[data-toggle="tooltip"]').tooltip();
    },
    
    /**
     * Show alert notification
     */
    showAlert: function(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        };
        
        const alertHtml = `
            <div class="alert ${alertClass[type]} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        // Insert alert at top of form
        $('#penilaianForm').prepend(alertHtml);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
};
