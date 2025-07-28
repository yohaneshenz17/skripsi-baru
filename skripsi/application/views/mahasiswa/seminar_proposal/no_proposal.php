<?php
/**
 * No Proposal View - Seminar Proposal (FIXED VERSION)
 * File: application/views/mahasiswa/seminar_proposal/no_proposal.php
 * 
 * PERBAIKAN: Mengubah pesan dan tombol agar sesuai dengan fase seminar proposal
 * Bukan lagi mengarah ke pengajuan proposal baru (fase 1)
 */
?>

<style>
    /* Empty State Styles */
    .empty-state {
        background: white;
        border-radius: 1rem;
        padding: 3rem 2rem;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 2rem;
    }
    
    .empty-state-icon {
        width: 5rem;
        height: 5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        margin-bottom: 1rem;
        font-size: 1.5rem;
    }
    
    .empty-state p {
        color: #8898aa;
        line-height: 1.6;
        margin-bottom: 2rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    
    /* Info Box */
    .info-box {
        background: #f8f9fe;
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .info-box-icon {
        color: #5e72e4;
        font-size: 1.5rem;
        margin-right: 1rem;
        margin-top: 0.25rem;
    }
    
    .info-box-content {
        flex: 1;
        line-height: 1.6;
        color: #525f7f;
    }
    
    /* Steps Guide */
    .steps-guide {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .step-item {
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        padding: 1.5rem;
        text-align: left;
        position: relative;
        transition: all 0.15s ease;
    }
    
    .step-item:hover {
        border-color: #5e72e4;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }
    
    .step-number {
        background: #5e72e4;
        color: white;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-bottom: 1rem;
        font-size: 0.875rem;
    }
    
    .step-title {
        font-weight: 600;
        color: #32325d;
        margin-bottom: 0.5rem;
    }
    
    .step-description {
        color: #8898aa;
        font-size: 0.875rem;
        line-height: 1.5;
    }
    
    /* Buttons */
    .btn {
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 0.375rem;
        transition: all 0.15s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid transparent;
        cursor: pointer;
    }
    
    .btn-primary {
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
        color: white;
        border-color: #5e72e4;
    }
    
    .btn-primary:hover {
        background: linear-gradient(87deg, #4c63d2 0, #7349d2 100%);
        transform: translateY(-1px);
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        text-decoration: none;
        color: white;
    }
    
    .btn-outline-primary {
        background: transparent;
        color: #5e72e4;
        border-color: #5e72e4;
    }
    
    .btn-outline-primary:hover {
        background: #5e72e4;
        color: white;
        transform: translateY(-1px);
        text-decoration: none;
    }
    
    /* Alert */
    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.375rem;
    }
    
    .alert-warning {
        color: #856404;
        background-color: #fff3cd;
        border-color: #ffeaa7;
    }
    
    .alert-info {
        color: #0c5460;
        background-color: #d1ecf1;
        border-color: #bee5eb;
    }
    
    /* FAQ Section */
    .faq-section {
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
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
        
        .info-box {
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
                Untuk dapat mengajukan <strong>seminar proposal</strong>, Anda perlu memiliki proposal tugas akhir yang sudah 
                <strong>disetujui oleh Kaprodi</strong> dan memiliki <strong>dosen pembimbing yang ditentukan</strong>. 
                Silakan ajukan proposal terlebih dahulu melalui menu Proposal.
            </p>
            
            <!-- PERBAIKAN: Mengarah ke proposal (fase 1), bukan seminar proposal -->
            <a href="<?php echo base_url('mahasiswa/proposal'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Ajukan Proposal Terlebih Dahulu
            </a>
        </div>
        
        <!-- Info Alert -->
        <div class="alert alert-info">
            <div style="display: flex; align-items: flex-start;">
                <i class="fas fa-info-circle" style="margin-right: 0.75rem; margin-top: 0.125rem; color: #0c5460;"></i>
                <div>
                    <strong>Catatan Penting:</strong><br>
                    Halaman ini untuk <strong>Seminar Proposal (Phase 3)</strong>. 
                    Pastikan Anda sudah menyelesaikan <strong>Phase 1 (Pengajuan Proposal)</strong> 
                    dan <strong>Phase 2 (Bimbingan)</strong> terlebih dahulu.
                </div>
            </div>
        </div>
        
        <!-- Steps Guide -->
        <div class="steps-guide">
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-title">Ajukan Proposal</div>
                <div class="step-description">
                    Ajukan usulan proposal tugas akhir Anda melalui menu Proposal. 
                    Proposal akan direview oleh Kaprodi dan dosen pembimbing akan ditentukan.
                </div>
            </div>
            
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-title">Proses Bimbingan</div>
                <div class="step-description">
                    Lakukan bimbingan dengan dosen pembimbing minimal 8 kali pertemuan 
                    dan isi jurnal bimbingan secara rutin.
                </div>
            </div>
            
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-title">Seminar Proposal</div>
                <div class="step-description">
                    Setelah proposal disetujui dan bimbingan mencukupi, 
                    Anda dapat mengajukan seminar proposal di halaman ini.
                </div>
            </div>
        </div>
        
        <!-- Quick Access -->
        <div class="info-box">
            <div style="display: flex; align-items: flex-start;">
                <i class="fas fa-lightbulb info-box-icon"></i>
                <div class="info-box-content">
                    <strong>Akses Cepat Menu Lainnya:</strong><br>
                    Gunakan menu navigasi di sebelah kiri untuk mengakses:
                    <ul style="margin: 0.5rem 0 0 1rem; padding: 0;">
                        <li>📝 <strong>Usulan Proposal</strong> - untuk mengajukan proposal baru</li>
                        <li>📚 <strong>Bimbingan</strong> - untuk jurnal bimbingan</li>
                        <li>👤 <strong>Profil</strong> - untuk mengubah data pribadi</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="faq-section">
            <div class="faq-question" onclick="toggleFAQ(this)">
                <span>Mengapa saya tidak bisa mengajukan seminar proposal?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Untuk mengajukan seminar proposal, Anda harus memenuhi syarat berikut:
                <br>• Proposal sudah disetujui oleh Kaprodi
                <br>• Dosen pembimbing sudah ditentukan
                <br>• Minimal 8 kali bimbingan telah tervalidasi
                <br>• Proposal dalam status "bimbingan" (Phase 2)
            </div>
            
            <div class="faq-question" onclick="toggleFAQ(this)">
                <span>Bagaimana cara mengecek status proposal saya?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Anda dapat mengecek status proposal melalui menu "Usulan Proposal" 
                di sidebar kiri. Di sana akan terlihat status persetujuan Kaprodi 
                dan dosen pembimbing.
            </div>
            
            <div class="faq-question" onclick="toggleFAQ(this)">
                <span>Kapan saya bisa mengajukan seminar proposal?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Anda dapat mengajukan seminar proposal setelah:
                <br>• Proposal disetujui dan dosen pembimbing ditetapkan
                <br>• Melakukan minimal 8 sesi bimbingan yang tervalidasi
                <br>• Mendapat persetujuan dari dosen pembimbing untuk seminar
            </div>
        </div>
        
    </div>
</div>

<script>
function toggleFAQ(element) {
    // Close all other FAQs
    const allQuestions = document.querySelectorAll('.faq-question');
    allQuestions.forEach(q => {
        if (q !== element) {
            q.classList.remove('active');
            q.querySelector('i').classList.remove('fa-chevron-up');
            q.querySelector('i').classList.add('fa-chevron-down');
        }
    });
    
    // Toggle current FAQ
    element.classList.toggle('active');
    const icon = element.querySelector('i');
    if (element.classList.contains('active')) {
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}
</script>