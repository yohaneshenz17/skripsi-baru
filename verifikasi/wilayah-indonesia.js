// wilayah-indonesia.js
// Script untuk cascading dropdown wilayah Indonesia

class WilayahIndonesia {
    constructor() {
        this.baseUrl = './api/wilayah.php';
        this.cache = new Map();
        this.init();
    }
    
    async init() {
        // Load provinsi saat pertama kali
        await this.loadProvinces();
        this.bindEvents();
    }
    
    // Ambil data dari API dengan caching
    async fetchData(type, id = '') {
        const cacheKey = `${type}_${id}`;
        
        if (this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey);
        }
        
        try {
            const url = id ? 
                `${this.baseUrl}?type=${type}&id=${id}` : 
                `${this.baseUrl}?type=${type}`;
                
            const response = await fetch(url);
            const result = await response.json();
            
            if (result.success) {
                this.cache.set(cacheKey, result.data);
                return result.data;
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error(`Error fetching ${type}:`, error);
            this.showError(`Gagal memuat data ${type}: ${error.message}`);
            return [];
        }
    }
    
    // Load semua provinsi
    async loadProvinces() {
        const provinsiSelect = document.getElementById('provinsi');
        if (!provinsiSelect) return;
        
        this.showLoading(provinsiSelect);
        
        try {
            const provinces = await this.fetchData('provinces');
            
            // Clear existing options
            provinsiSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
            
            // Add provinces
            provinces.forEach(province => {
                const option = document.createElement('option');
                option.value = province.id;
                option.textContent = province.name;
                option.dataset.name = province.name;
                provinsiSelect.appendChild(option);
            });
            
        } catch (error) {
            this.showError('Gagal memuat data provinsi');
        }
    }
    
    // Load kabupaten berdasarkan provinsi
    async loadRegencies(provinceId) {
        const kabupatenSelect = document.getElementById('kabupaten_kota');
        if (!kabupatenSelect || !provinceId) return;
        
        this.showLoading(kabupatenSelect);
        this.resetSelect('kecamatan');
        this.resetSelect('desa');
        
        try {
            const regencies = await this.fetchData('regencies', provinceId);
            
            kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
            
            regencies.forEach(regency => {
                const option = document.createElement('option');
                option.value = regency.id;
                option.textContent = regency.name;
                option.dataset.name = regency.name;
                kabupatenSelect.appendChild(option);
            });
            
        } catch (error) {
            this.showError('Gagal memuat data kabupaten/kota');
        }
    }
    
    // Load kecamatan berdasarkan kabupaten
    async loadDistricts(regencyId) {
        const kecamatanSelect = document.getElementById('kecamatan');
        if (!kecamatanSelect || !regencyId) return;
        
        this.showLoading(kecamatanSelect);
        this.resetSelect('desa');
        
        try {
            const districts = await this.fetchData('districts', regencyId);
            
            kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            
            districts.forEach(district => {
                const option = document.createElement('option');
                option.value = district.id;
                option.textContent = district.name;
                option.dataset.name = district.name;
                kecamatanSelect.appendChild(option);
            });
            
        } catch (error) {
            this.showError('Gagal memuat data kecamatan');
        }
    }
    
    // Load kelurahan berdasarkan kecamatan
    async loadVillages(districtId) {
        const desaSelect = document.getElementById('desa');
        if (!desaSelect || !districtId) return;
        
        this.showLoading(desaSelect);
        
        try {
            const villages = await this.fetchData('villages', districtId);
            
            desaSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
            
            villages.forEach(village => {
                const option = document.createElement('option');
                option.value = village.id;
                option.textContent = village.name;
                option.dataset.name = village.name;
                desaSelect.appendChild(option);
            });
            
        } catch (error) {
            this.showError('Gagal memuat data desa/kelurahan');
        }
    }
    
    // Reset dropdown
    resetSelect(selectId) {
        const select = document.getElementById(selectId);
        if (select) {
            select.innerHTML = `<option value="">Pilih ${this.getSelectLabel(selectId)}</option>`;
        }
    }
    
    // Get label for select
    getSelectLabel(selectId) {
        const labels = {
            'provinsi': 'Provinsi',
            'kabupaten_kota': 'Kabupaten/Kota',
            'kecamatan': 'Kecamatan',
            'desa': 'Desa/Kelurahan'
        };
        return labels[selectId] || '';
    }
    
    // Show loading state
    showLoading(select) {
        select.innerHTML = '<option value="">Memuat...</option>';
        select.disabled = true;
        setTimeout(() => {
            select.disabled = false;
        }, 500);
    }
    
    // Show error message
    showError(message) {
        console.error(message);
        // Bisa ditambahkan notifikasi UI di sini
    }
    
    // Bind event handlers
    bindEvents() {
        // Provinsi change
        const provinsiSelect = document.getElementById('provinsi');
        if (provinsiSelect) {
            provinsiSelect.addEventListener('change', async (e) => {
                const provinceId = e.target.value;
                const provinceName = e.target.selectedOptions[0]?.dataset.name || '';
                
                // Update form data untuk React component
                if (window.handleInputChange && typeof window.handleInputChange === 'function') {
                    window.handleInputChange('provinsi', provinceName);
                }
                
                if (provinceId) {
                    await this.loadRegencies(provinceId);
                } else {
                    this.resetSelect('kabupaten_kota');
                    this.resetSelect('kecamatan');
                    this.resetSelect('desa');
                }
            });
        }
        
        // Kabupaten change
        const kabupatenSelect = document.getElementById('kabupaten_kota');
        if (kabupatenSelect) {
            kabupatenSelect.addEventListener('change', async (e) => {
                const regencyId = e.target.value;
                const regencyName = e.target.selectedOptions[0]?.dataset.name || '';
                
                if (window.handleInputChange && typeof window.handleInputChange === 'function') {
                    window.handleInputChange('kabupaten_kota', regencyName);
                }
                
                if (regencyId) {
                    await this.loadDistricts(regencyId);
                } else {
                    this.resetSelect('kecamatan');
                    this.resetSelect('desa');
                }
            });
        }
        
        // Kecamatan change
        const kecamatanSelect = document.getElementById('kecamatan');
        if (kecamatanSelect) {
            kecamatanSelect.addEventListener('change', async (e) => {
                const districtId = e.target.value;
                const districtName = e.target.selectedOptions[0]?.dataset.name || '';
                
                if (window.handleInputChange && typeof window.handleInputChange === 'function') {
                    window.handleInputChange('kecamatan', districtName);
                }
                
                if (districtId) {
                    await this.loadVillages(districtId);
                } else {
                    this.resetSelect('desa');
                }
            });
        }
        
        // Desa change
        const desaSelect = document.getElementById('desa');
        if (desaSelect) {
            desaSelect.addEventListener('change', (e) => {
                const villageName = e.target.selectedOptions[0]?.dataset.name || '';
                
                if (window.handleInputChange && typeof window.handleInputChange === 'function') {
                    window.handleInputChange('desa', villageName);
                }
            });
        }
    }
    
    // Method untuk set nilai programmatically (untuk data existing)
    async setWilayah(provinsi, kabupaten, kecamatan, desa) {
        // Tunggu provinsi dimuat
        await this.loadProvinces();
        
        // Set provinsi
        const provinsiSelect = document.getElementById('provinsi');
        if (provinsiSelect && provinsi) {
            // Cari berdasarkan nama provinsi
            const provinsiOption = Array.from(provinsiSelect.options)
                .find(option => option.dataset.name === provinsi);
            
            if (provinsiOption) {
                provinsiSelect.value = provinsiOption.value;
                await this.loadRegencies(provinsiOption.value);
                
                // Set kabupaten
                if (kabupaten) {
                    const kabupatenSelect = document.getElementById('kabupaten_kota');
                    const kabupatenOption = Array.from(kabupatenSelect.options)
                        .find(option => option.dataset.name === kabupaten);
                    
                    if (kabupatenOption) {
                        kabupatenSelect.value = kabupatenOption.value;
                        await this.loadDistricts(kabupatenOption.value);
                        
                        // Set kecamatan
                        if (kecamatan) {
                            const kecamatanSelect = document.getElementById('kecamatan');
                            const kecamatanOption = Array.from(kecamatanSelect.options)
                                .find(option => option.dataset.name === kecamatan);
                            
                            if (kecamatanOption) {
                                kecamatanSelect.value = kecamatanOption.value;
                                await this.loadVillages(kecamatanOption.value);
                                
                                // Set desa
                                if (desa) {
                                    const desaSelect = document.getElementById('desa');
                                    const desaOption = Array.from(desaSelect.options)
                                        .find(option => option.dataset.name === desa);
                                    
                                    if (desaOption) {
                                        desaSelect.value = desaOption.value;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

// Initialize setelah DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.wilayahIndonesia = new WilayahIndonesia();
});

// Export untuk dapat digunakan dari luar
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WilayahIndonesia;
}