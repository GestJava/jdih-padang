/**
 * Fungsi untuk memverifikasi status sertifikat TTE
 */
function verifyTteCertificate() {
    const resultDiv = document.getElementById('tteVerificationResult');
    const btnProceed = document.getElementById('btnProceedTte');
    const tteNik = document.getElementById('tte_nik')?.value;
    const modal = $('#tteVerificationModal');
    
    // Validasi NIK
    if (!tteNik || !/^\d{16}$/.test(tteNik)) {
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>NIK Tidak Valid</h5>
                <p class="mb-0">NIK harus terdiri dari 16 digit angka.</p>
            </div>`;
        return false;
    }
    
    // Tampilkan loading
    resultDiv.innerHTML = `
        <div class="text-center my-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Memeriksa sertifikat...</span>
            </div>
            <p class="mt-3 mb-0">Sedang memeriksa status sertifikat TTE...</p>
        </div>`;
    
    // Sembunyikan tombol lanjutkan
    if (btnProceed) {
        btnProceed.style.display = 'none';
    }
    
    // Tampilkan modal
    modal.modal('show');
    
    // Kirim request ke endpoint verifikasi dengan NIK
    const formData = new FormData();
    formData.append('nik', tteNik);
    
    const baseUrl = window.location.origin + '/jdih_backup';
    fetch(baseUrl + '/test-tte/check-certificate', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (data.certificate_status === 'ACTIVE') {
                // Sertifikat aktif
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle me-2"></i> Sertifikat TTE Aktif</h5>
                        <p class="mb-0">${data.message || 'Sertifikat TTE Anda aktif dan dapat digunakan.'}</p>
                    </div>`;
                btnProceed.style.display = 'inline-block';
            } else {
                // Sertifikat bermasalah
                let errorMessage = 'Status sertifikat tidak valid.';
                if (data.certificate_status === 'ISSUE') {
                    errorMessage = 'Sertifikat TTE Anda bermasalah (ISSUE). Silakan perbarui sertifikat Anda di BSrE.';
                } else if (data.certificate_status === 'EXPIRED') {
                    errorMessage = 'Sertifikat TTE Anda telah kadaluarsa. Silakan perbarui sertifikat Anda di BSrE.';
                } else if (data.certificate_status === 'REVOKED') {
                    errorMessage = 'Sertifikat TTE Anda telah dicabut. Silakan hubungi administrator BSrE.';
                }
                
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i> Sertifikat Bermasalah</h5>
                        <p class="mb-2">${errorMessage}</p>
                        <p class="mb-0">
                            <a href="https://bsre.bssn.go.id" target="_blank" class="alert-link">
                                Kunjungi BSrE untuk memperbarui sertifikat
                            </a>
                        </p>
                    </div>`;
            }
        } else {
            // Error dari server
            resultDiv.innerHTML = `
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-circle me-2"></i> Gagal Memeriksa Sertifikat</h5>
                    <p class="mb-0">${data.message || 'Terjadi kesalahan saat memeriksa status sertifikat. Silakan coba lagi.'}</p>
                </div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <h5><i class="fas fa-times-circle me-2"></i> Kesalahan Jaringan</h5>
                <p class="mb-0">Tidak dapat terhubung ke server TTE. Pastikan koneksi internet Anda stabil dan coba lagi.</p>
            </div>`;
    });
}

// Fungsi untuk memeriksa status sertifikat (dipanggil dari luar)
window.verifyTteCertificate = verifyTteCertificate;
