<?php
/**
 * No Proposal - Seminar Proposal
 * File: application/views/mahasiswa/seminar_proposal/no_proposal.php
 * 
 * Halaman yang ditampilkan ketika mahasiswa belum memiliki proposal aktif
 * Menggunakan template mahasiswa_simple.php dengan CSS inline
 */
?>

<style>
    /* Empty State Styles */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 0 2rem 0 rgba(136, 152, 170, 0.15);
        margin-bottom: 1.5rem;
    }
    
    .empty-state-icon {
        width: 6rem;
        height: 6rem;
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: white;
        font-size: 2.5rem;
    }
    
    .empty-state h4 {
        color: #32325d;
        font-weight: 600;
        margin-bottom: 0.75rem;
        font-size: 1.25rem;
    }
    
    .empty-state p {
        color: #8898aa;
        margin-bottom: 1.5rem;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.6;
    }
    
    /* Card Styles */
    .card {
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 0 2rem 0 rgba(136, 152, 170, 0.15);
        border: none;
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 1.5rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 0.375rem;
        border: 1px solid transparent;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s ease;
        gap: 0.5rem;
    }
    
    .btn-primary {
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
        color: white;
        border-color: #5e72e4;
        font-size: 1rem;
        padding: 0.875rem 2rem;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        color: white;
        text-decoration: none;
    }
    
    .btn-outline-primary {
        background: transparent;
        color: #5e72e4;
        border-color: #5e72e4;
    }
    
    .btn-outline-primary:hover {
        background: #5e72e4;
        color: white;
        text-decoration: none;
    }
    
    .btn-outline-secondary {
        background: transparent;
        color: #6c757d;
        border-color: #6c757d;
    }
    
    .btn-outline-secondary:hover {
        background: #6c757d;
        color: white;
        text-decoration: none;
    }
    
    /* Steps Guide */
    .steps-guide {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .step-item {
        background: white;
        border-radius: 0.375rem;
        padding: 1.5rem;
        text-align: center;
        border: 2px solid #e3e6f0;
        transition: all 0.15s ease;
        position: relative;
    }
    
    .step-item:hover {
        border-color: #5e72e4;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(94, 114, 228, 0.15);
    }
    
    .step-number {
        width: 2.5rem;
        height: 2.5rem;
        background: #5e72e4;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin: 0 auto 1rem;
        font-size: 1.125rem;
    }
    
    .step-title {
        color: #32325d;
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 1rem;
    }
    
    .step-description {
        color: #8898aa;
        font-size: 0.875rem;
        line-height: 1.5;
    }
    
    /* Info Box */
    .info-box {
        background: rgba(17, 205, 239, 0.1);
        border: 1px solid rgba(17, 205, 239, 0.2);
        border-radius: 0.375rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }
    
    .info-box-icon {
        color: #11cdef;
        font-size: 1.25rem;
        margin-right: 0.75rem;
    }
    
    .info-box-content {
        color: #0c5460;
    }
    
    /* FAQ Section */
    .faq-item {
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 0.375rem;
        margin-bottom: 0.75rem;
        overflow: hidden;
    }
    
    .faq-question {
        padding: 1rem;
        background: #f8f9fe;
        border-bottom: 1px solid #e3e6f0;
        font-weight: 600;
        color: #32325d;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .faq-answer {
        padding: 1rem;
        color: #8898aa;
        line-height: 1.6;
        display: none;
    }
    
    .faq-question.active + .faq-answer {
        display: block;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .empty-state {
            padding: 2rem 1rem;
        }
        
        .empty-state-icon {
            width: 4rem;
            height: 4rem;
            font-size: 2rem;
        }
        
        .steps-guide {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .step-item {
            padding: 1rem;
        }
        
        .card-body {
            padding: 1rem;
        }
    }
</style>

<div style="margin-top: -3rem; position: relative; z-index: 10;">
    <div style="max-width: 1000px; margin: 0 auto; padding: 0 1.5rem;">
        
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            
            <h4>Belum Ada Proposal Aktif</h4>
            <p>
                Untuk dapat mengajukan seminar proposal, Anda perlu memiliki proposal tugas akhir yang sudah disetujui 
                dan memiliki dosen pembimbing. Silakan ajukan proposal terlebih dahulu melalui menu Proposal.
            </p>
            
            <a href="<?php echo base_url('mahasiswa/proposal'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Ajukan Proposal Sekarang
            </a>
        </div>
        
        <!-- Info Box -->
        <div class="info-box">
            <div style="display: flex; align-items: flex-start;">
                <i class="fas fa-lightbulb info-box-icon"></i>
                <div class="info-box-content">
                    <strong>Tahukah Anda?</strong><br>
                    Setelah proposal Anda disetujui dan dosen pembimbing ditentukan, 
                    Anda dapat mengajukan seminar proposal setelah melakukan minimal beberapa sesi bimbingan yang tervalidasi.
                </div>
            </div>
        </div>
        
        <!-- Steps Guide -->
        <div class="card">
            <div class="card-header">
                <h5 style="margin: 0; font-weight: 600; color: #32325d;">
                    <i class="fas fa-route" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                    Langkah-langkah Menuju Seminar Proposal
                </h5>
            </div>
            <div class="card-body">
                <div class="steps-guide">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-title">Ajukan Proposal</div>
                        <div class="step-description">
                            Buat dan ajukan proposal tugas akhir melalui menu Proposal. 
                            Pastikan proposal sesuai dengan bidang keahlian dan minat Anda.
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-title">Penetapan Pembimbing</div>
                        <div class="step-description">
                            Kaprodi akan menentukan dosen pembimbing yang sesuai dengan topik proposal Anda. 
                            Dosen pembimbing akan memvalidasi untuk menjadi pembimbing.
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-title">Bimbingan Proposal</div>
                        <div class="step-description">
                            Lakukan bimbingan secara rutin dengan dosen pembimbing. 
                            Catat setiap sesi bimbingan di jurnal bimbingan untuk validasi.
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">4</div>
                        <div class="step-title">Ajukan Seminar</div>
                        <div class="step-description">
                            Setelah syarat jurnal bimbingan terpenuhi, Anda dapat mengajukan seminar proposal 
                            melalui halaman ini.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                    <i class="fas fa-bolt" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                    Aksi yang Dapat Dilakukan
                </h6>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="<?php echo base_url('mahasiswa/proposal'); ?>" class="btn btn-primary">
                        <i class="fas fa-file-alt"></i>
                        Kelola Proposal
                    </a>
                    
                    <a href="<?php echo base_url('mahasiswa/dashboard'); ?>" class="btn btn-outline-primary">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard Utama
                    </a>
                    
                    <a href="<?php echo base_url('mahasiswa/kontak'); ?>" class="btn btn-outline-primary">
                        <i class="fas fa-comments"></i>
                        Hubungi Kaprodi
                    </a>
                    
                    <a href="<?php echo base_url('mahasiswa/bantuan'); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-question-circle"></i>
                        Bantuan & Panduan
                    </a>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="card">
            <div class="card-header">
                <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                    <i class="fas fa-question-circle" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                    Pertanyaan yang Sering Diajukan
                </h6>
            </div>
            <div class="card-body">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        Berapa lama proses persetujuan proposal?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Proses persetujuan proposal biasanya memakan waktu 7-14 hari kerja setelah pengajuan, 
                        tergantung pada kelengkapan dokumen dan ketersediaan dosen pembimbing yang sesuai.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        Bagaimana jika proposal saya ditolak?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Jika proposal ditolak, Anda akan mendapat feedback dari Kaprodi. 
                        Perbaiki proposal sesuai saran yang diberikan dan ajukan kembali.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        Apakah bisa mengubah topik proposal setelah disetujui?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Perubahan topik yang signifikan setelah persetujuan memerlukan pengajuan ulang proposal. 
                        Untuk perubahan minor, konsultasikan dengan dosen pembimbing dan Kaprodi.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        Berapa minimal jurnal bimbingan untuk seminar proposal?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Minimal jurnal bimbingan yang harus tervalidasi adalah 5 sesi, namun hal ini dapat berbeda 
                        tergantung kebijakan program studi. Konsultasikan dengan dosen pembimbing Anda.
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Info -->
        <div class="card">
            <div class="card-body">
                <div style="text-align: center; color: #8898aa;">
                    <p style="margin-bottom: 0.5rem;">
                        <strong>Butuh bantuan lebih lanjut?</strong>
                    </p>
                    <p style="margin: 0;">
                        Hubungi admin akademik atau gunakan fitur 
                        <a href="<?php echo base_url('mahasiswa/kontak'); ?>" style="color: #5e72e4;">Kontak</a> 
                        untuk berkomunikasi dengan Kaprodi dan dosen.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

function toggleFaq(element) {
    const isActive = element.classList.contains('active');
    
    // Close all FAQ items
    document.querySelectorAll('.faq-question').forEach(item => {
        item.classList.remove('active');
        item.querySelector('i').style.transform = 'rotate(0deg)';
    });
    
    // Open clicked item if it wasn't active
    if (!isActive) {
        element.classList.add('active');
        element.querySelector('i').style.transform = 'rotate(180deg)';
    }
}
</script>