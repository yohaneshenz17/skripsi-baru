/**
 * File: cdn/js/staf-photo-helper.js
 * Helper JavaScript untuk menangani update foto profil staf
 * PERBAIKAN: Mengatasi masalah foto tidak update di header dan sidebar
 * 
 * Usage: Include di template staf.php setelah jQuery
 * <script src="<?= base_url() ?>cdn/js/staf-photo-helper.js"></script>
 */

(function($) {
    'use strict';
    
    // Pastikan jQuery tersedia
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is required for staf-photo-helper.js');
        return;
    }
    
    // Pastikan base_url tersedia (biasanya didefinisikan di template)
    if (typeof base_url === 'undefined') {
        console.warn('base_url not defined, using default');
        window.base_url = '/skripsi/';
    }
    
    /**
     * Namespace untuk Staf Photo Helper
     */
    window.StafPhotoHelper = {
        
        /**
         * Update foto profil staf di seluruh halaman
         * @param {string} fotoName - Nama file foto baru atau kosong untuk default
         * @param {boolean} forceUpdate - Paksa update meskipun foto sama
         */
        updatePhoto: function(fotoName, forceUpdate = false) {
            console.log('StafPhotoHelper.updatePhoto called:', fotoName);
            
            const timestamp = new Date().getTime();
            let fotoUrl;
            
            // Tentukan URL foto berdasarkan nama file
            if (!fotoName || fotoName === '' || fotoName === 'default.png') {
                fotoUrl = base_url + 'assets/img/theme/default-avatar.png';
            } else {
                fotoUrl = base_url + 'cdn/img/staf/' + fotoName;
            }
            
            // Tambahkan timestamp untuk cache busting
            const finalUrl = fotoUrl + '?v=' + timestamp;
            
            console.log('Updating staf photos to:', finalUrl);
            
            // Update foto di berbagai lokasi
            this.updateHeaderPhoto(finalUrl);
            this.updateSidebarPhoto(finalUrl);
            this.updateProfilePagePhoto(finalUrl);
            this.updateAllStafPhotos(finalUrl);
            
            // Trigger event untuk komponen lain
            $(document).trigger('stafPhotoUpdated', [fotoName, finalUrl]);
            
            console.log('All staf profile photos updated successfully');
            return true;
        },
        
        /**
         * Update foto di header/navbar
         */
        updateHeaderPhoto: function(url) {
            const selectors = [
                '#header-staf-photo',
                '.staf-header-avatar',
                '.navbar .avatar img',
                '.navbar .dropdown .media img'
            ];
            
            this._updatePhotosBySelectors(selectors, url, 'header');
        },
        
        /**
         * Update foto di sidebar
         */
        updateSidebarPhoto: function(url) {
            const selectors = [
                '#sidebar-staf-photo',
                '.staf-sidebar-avatar',
                '.sidebar-profile img',
                '.profile-card-compact img'
            ];
            
            this._updatePhotosBySelectors(selectors, url, 'sidebar');
        },
        
        /**
         * Update foto di halaman profil
         */
        updateProfilePagePhoto: function(url) {
            // Update preview image dengan background-image
            const $preview = $('#imagePreview');
            if ($preview.length > 0) {
                $preview.css('background-image', 'url(' + url + ')');
                console.log('Profile page preview updated');
            }
            
            // Update profile card images
            $('.profile-card img, .profile-header img').each(function() {
                this._updateSinglePhoto($(this), url);
            }.bind(this));
        },
        
        /**
         * Update semua instance foto staf lainnya
         */
        updateAllStafPhotos: function(url) {
            // Update berdasarkan class staf-profile-photo
            $('.staf-profile-photo').each(function() {
                this._updateSinglePhoto($(this), url);
            }.bind(this));
            
            // Update berdasarkan src pattern untuk foto staf
            $('img[src*="cdn/img/staf/"]').each(function() {
                const $img = $(this);
                const currentSrc = $img.attr('src');
                if (currentSrc && currentSrc.includes('cdn/img/staf/')) {
                    this._updateSinglePhoto($img, url);
                }
            }.bind(this));
            
            // Update background images yang menggunakan foto staf
            $('[style*="cdn/img/staf/"]').each(function() {
                const $element = $(this);
                const currentStyle = $element.attr('style');
                if (currentStyle && currentStyle.includes('cdn/img/staf/')) {
                    $element.css('background-image', 'url(' + url + ')');
                    console.log('Background image updated');
                }
            });
        },
        
        /**
         * Helper method untuk update foto berdasarkan selector
         */
        _updatePhotosBySelectors: function(selectors, url, location) {
            selectors.forEach(selector => {
                const $elements = $(selector);
                if ($elements.length > 0) {
                    $elements.each(function() {
                        this._updateSinglePhoto($(this), url);
                    }.bind(this));
                    console.log(`${location} photos updated for selector: ${selector}`);
                }
            }.bind(this));
        },
        
        /**
         * Helper method untuk update single photo element dengan loading state
         */
        _updateSinglePhoto: function($element, url) {
            if (!$element || $element.length === 0) return;
            
            // Tambahkan loading state
            $element.addClass('loading-photo');
            
            // Test load gambar terlebih dahulu untuk memastikan valid
            const testImg = new Image();
            testImg.onload = function() {
                $element.attr('src', url);
                $element.removeClass('loading-photo');
            };
            testImg.onerror = function() {
                console.warn('Failed to load photo:', url);
                $element.removeClass('loading-photo');
                // Fallback ke default avatar jika gagal
                $element.attr('src', base_url + 'assets/img/theme/default-avatar.png');
            };
            testImg.src = url;
        },
        
        /**
         * Refresh foto dari server/session
         */
        refreshFromServer: function() {
            // Implementasi untuk refresh foto dari server jika diperlukan
            console.log('Refreshing photo from server...');
            
            // Bisa ditambahkan AJAX call ke endpoint untuk mendapatkan foto terbaru
            // Untuk saat ini, gunakan detection dari flashdata di template
            return this;
        },
        
        /**
         * Initialize helper
         */
        init: function() {
            console.log('StafPhotoHelper initialized');
            
            // Event listeners
            $(document).on('stafPhotoUpdated', function(event, fotoName, url) {
                console.log('Staf photo update event received:', fotoName);
            });
            
            // Periodic check untuk sinkronisasi (opsional)
            if (window.location.pathname.includes('/staf/')) {
                setInterval(function() {
                    // Auto-sync setiap 60 detik jika diperlukan
                    // this.refreshFromServer();
                }.bind(this), 60000);
            }
            
            return this;
        }
    };
    
    /**
     * Shorthand function untuk kemudahan penggunaan
     */
    window.updateStafPhoto = function(fotoName, forceUpdate) {
        return StafPhotoHelper.updatePhoto(fotoName, forceUpdate);
    };
    
    /**
     * Backward compatibility untuk function yang sudah ada di template
     */
    if (typeof window.updateStafProfilePhoto === 'undefined') {
        window.updateStafProfilePhoto = function(fotoName) {
            return StafPhotoHelper.updatePhoto(fotoName);
        };
    }
    
    /**
     * Auto-initialize ketika DOM ready
     */
    $(document).ready(function() {
        // Hanya initialize untuk halaman staf
        if (window.location.pathname.includes('/staf/')) {
            StafPhotoHelper.init();
            
            // Auto-detect flashdata dari template jika ada
            // Template akan call updateStafProfilePhoto() jika ada foto_updated flashdata
            console.log('StafPhotoHelper ready for staf pages');
        }
    });
    
})(jQuery);

/**
 * Fallback jika jQuery belum loaded
 */
if (typeof jQuery === 'undefined') {
    // Simple fallback tanpa jQuery
    window.updateStafProfilePhoto = function(fotoName) {
        console.log('Using fallback updateStafProfilePhoto (no jQuery)');
        
        const timestamp = new Date().getTime();
        let fotoUrl;
        
        if (!fotoName || fotoName === '') {
            fotoUrl = (typeof base_url !== 'undefined' ? base_url : '/') + 'assets/img/theme/default-avatar.png';
        } else {
            fotoUrl = (typeof base_url !== 'undefined' ? base_url : '/') + 'cdn/img/staf/' + fotoName;
        }
        
        const finalUrl = fotoUrl + '?v=' + timestamp;
        
        // Simple update tanpa jQuery
        const headerPhoto = document.getElementById('header-staf-photo');
        const sidebarPhoto = document.getElementById('sidebar-staf-photo');
        
        if (headerPhoto) headerPhoto.src = finalUrl;
        if (sidebarPhoto) sidebarPhoto.src = finalUrl;
        
        // Update semua img dengan class staf-profile-photo
        const stafPhotos = document.getElementsByClassName('staf-profile-photo');
        for (let i = 0; i < stafPhotos.length; i++) {
            stafPhotos[i].src = finalUrl;
        }
        
        console.log('Fallback photo update completed');
    };
}

/**
 * Export untuk module system jika diperlukan
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = StafPhotoHelper;
}