/**
 * PDF Viewer JavaScript
 * Fungsi untuk menangani tampilan dan interaksi PDF viewer
 */

class PDFViewer {
    constructor() {
        this.container = document.getElementById('pdfContainer');
        this.viewer = document.getElementById('pdfViewer');
        this.loading = document.querySelector('.pdf-loading');
        this.isFullscreen = false;
        
        this.init();
    }

    init() {
        if (!this.viewer) {
            return;
        }

        // Event listeners
        this.viewer.addEventListener('load', () => this.onPDFLoad());
        this.viewer.addEventListener('error', () => this.onPDFError());
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => this.handleKeyboard(e));
        
        // Fullscreen change event
        document.addEventListener('fullscreenchange', () => this.onFullscreenChange());
        
        // Timeout fallback - if PDF doesn't load within 10 seconds, show error
        setTimeout(() => {
            if (this.loading && this.loading.style.display !== 'none') {
                this.onPDFError();
            }
        }, 10000);
    }

    onPDFLoad() {
        if (this.loading) {
            this.loading.style.display = 'none';
        }
        if (this.viewer) {
            this.viewer.style.display = 'block';
            this.viewer.classList.add('loaded');
        }
        
        // Track PDF view
        this.trackPDFView();
    }

    onPDFError() {
        if (this.loading) {
            this.loading.innerHTML = this.getErrorHTML();
        }
    }

    getErrorHTML() {
        return `
            <div class="text-center py-5">
                <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                <h5>Gagal memuat dokumen PDF</h5>
                <p class="text-muted">Dokumen mungkin tidak tersedia atau terjadi kesalahan.</p>
                <div class="mt-3">
                    <a href="${this.getDownloadURL()}" class="btn btn-primary me-2">
                        <i class="fas fa-download me-1"></i> Download Dokumen
                    </a>
                    <button onclick="location.reload()" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i> Coba Lagi
                    </button>
                </div>
            </div>
        `;
    }

    getDownloadURL() {
        // Extract ID from current URL or use data attribute
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get('id') || this.viewer?.dataset?.peraturanId || '';
        return `/peraturan/download/${id}`;
    }

    toggleFullscreen() {
        if (this.isFullscreen) {
            this.exitFullscreen();
        } else {
            this.enterFullscreen();
        }
    }

    enterFullscreen() {
        if (!this.container) return;

        this.container.classList.add('fullscreen');
        if (this.viewer) {
            this.viewer.style.height = 'calc(100vh - 60px)';
        }
        document.body.style.overflow = 'hidden';
        this.isFullscreen = true;

        // Update button text
        const fullscreenBtn = document.querySelector('[onclick="toggleFullscreen()"]');
        if (fullscreenBtn) {
            fullscreenBtn.innerHTML = '<i class="fas fa-compress me-1"></i> Exit Fullscreen';
        }
    }

    exitFullscreen() {
        if (!this.container) return;

        this.container.classList.remove('fullscreen');
        if (this.viewer) {
            this.viewer.style.height = '600px';
        }
        document.body.style.overflow = '';
        this.isFullscreen = false;

        // Update button text
        const fullscreenBtn = document.querySelector('[onclick="toggleFullscreen()"]');
        if (fullscreenBtn) {
            fullscreenBtn.innerHTML = '<i class="fas fa-expand me-1"></i> Fullscreen';
        }
    }

    onFullscreenChange() {
        if (!document.fullscreenElement && this.isFullscreen) {
            this.exitFullscreen();
        }
    }

    handleKeyboard(e) {
        // Escape key to exit fullscreen
        if (e.key === 'Escape' && this.isFullscreen) {
            this.exitFullscreen();
        }
        
        // F11 key to toggle fullscreen
        if (e.key === 'F11') {
            e.preventDefault();
            this.toggleFullscreen();
        }
    }

    trackPDFView() {
        // Track PDF view for analytics
        try {
            const peraturanId = this.viewer?.dataset?.peraturanId;
            if (peraturanId) {
                // Send analytics data
                fetch('/api/analytics/pdf-view', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        peraturan_id: peraturanId,
                        action: 'pdf_view',
                        timestamp: new Date().toISOString()
                    })
                }).catch(console.error);
            }
        } catch (error) {
            console.error('Error tracking PDF view:', error);
        }
    }

    // Utility methods
    getPDFURL() {
        return this.viewer?.src || '';
    }

    reload() {
        if (this.viewer) {
            this.viewer.src = this.viewer.src;
        }
    }
}

// Global functions for backward compatibility
function toggleFullscreen() {
    if (window.pdfViewer) {
        window.pdfViewer.toggleFullscreen();
    }
}

// Initialize PDF viewer when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize PDF viewer
    if (document.getElementById('pdfViewer')) {
        window.pdfViewer = new PDFViewer();
    }

    // Add smooth scrolling to PDF viewer
    const pdfViewer = document.getElementById('pdfViewer');
    if (pdfViewer) {
        pdfViewer.addEventListener('load', function() {
            // Add loading animation
            this.style.opacity = '0';
            setTimeout(() => {
                this.style.opacity = '1';
            }, 100);
        });
    }

    // Add print functionality
    const printBtn = document.querySelector('[data-action="print-pdf"]');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            const pdfViewer = document.getElementById('pdfViewer');
            if (pdfViewer) {
                const printWindow = window.open(pdfViewer.src, '_blank');
                if (printWindow) {
                    printWindow.onload = function() {
                        printWindow.print();
                    };
                }
            }
        });
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PDFViewer;
} 