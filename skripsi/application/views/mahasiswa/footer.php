</div> <!-- End container -->

    <!-- Footer -->
    <footer class="bg-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        &copy; <?= date('Y') ?> STK Santo Yakobus Merauke. 
                        Sistem Informasi Manajemen Tugas Akhir.
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0 text-muted">
                        <i class="fas fa-code"></i> Dikembangkan oleh SIPD dengan 
                        <i class="fas fa-heart text-danger"></i>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Global base URL
        var base_url = '<?= base_url() ?>';
        
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Confirm delete actions
        $('.btn-delete').on('click', function(e) {
            e.preventDefault();
            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                window.location.href = $(this).attr('href');
            }
        });
        
        // Form validation helper
        $('form').on('submit', function() {
            var submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        });
    </script>
</body>
</html>