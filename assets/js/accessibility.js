/**
 * Accessibility Toolkit JS - JDIH Kota Padang
 * Handles logic for High Contrast, Font Size, and Monochrome modes.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if we are on a page with PDF Reader (detail peraturan)
    const hasPdfReader = document.getElementById('btn-read-pdf') !== null;

    // Create UI Elements
    const a11yHtml = `
    <div class="a11y-widget">
        <button class="a11y-toggle" id="a11yToggle" title="Fitur Aksesibilitas">
            <i class="fas fa-universal-access"></i>
        </button>
        <div class="a11y-panel" id="a11yPanel">
            <h5>Fitur Aksesibilitas</h5>
            
            <div class="a11y-item">
                <span>Ukuran Teks</span>
                <div class="a11y-btn-group">
                    <button class="a11y-btn" data-fz="normal" title="Normal">A</button>
                    <button class="a11y-btn" data-fz="large" title="Besar">A+</button>
                    <button class="a11y-btn" data-fz="xlarge" title="Sangat Besar">A++</button>
                </div>
            </div>

            <div class="a11y-item">
                <span>Kontras Tinggi</span>
                <button class="a11y-btn" id="toggleContrast" title="Toggle Kontras">
                    <i class="fas fa-adjust"></i>
                </button>
            </div>

            <div class="a11y-item">
                <span>Mode Monokrom</span>
                <button class="a11y-btn" id="toggleMonochrome" title="Toggle Monokrom">
                    <i class="fas fa-palette"></i>
                </button>
            </div>

            <div class="a11y-item">
                <span>Sorot Link</span>
                <button class="a11y-btn" id="toggleLinks" title="Sorot Link">
                    <i class="fas fa-link"></i>
                </button>
            </div>

            <div class="a11y-item">
                <span>Font Mudah Baca</span>
                <button class="a11y-btn" id="toggleFont" title="Ganti Font">
                    <i class="fas fa-font"></i>
                </button>
            </div>

            ${hasPdfReader ? `
            <div class="a11y-item">
                <span>Caption Visual</span>
                <button class="a11y-btn" id="toggleCaptions" title="Tampilkan Teks Bacaan">
                    <i class="fas fa-closed-captioning"></i>
                </button>
            </div>

            <div class="a11y-item">
                <span>Bahasa Isyarat</span>
                <button class="a11y-btn" id="toggleSL" title="Tampilkan Isyarat">
                    <i class="fas fa-hands-helping"></i>
                </button>
            </div>
            ` : ''}

            <hr style="margin: 5px 0;">
            <button class="btn btn-sm btn-outline-danger w-100" id="resetA11y">Reset Pengaturan</button>
        </div>
    </div>

    ${hasPdfReader ? `
    <div id="a11yCaptionBox" class="a11y-caption-container">
        <div class="a11y-caption-label">Visual Caption</div>
        <div id="a11yCaptionText">...</div>
    </div>

    <div id="a11ySLBox" class="a11y-sl-container">
        <div class="bg-primary text-white p-2 d-flex justify-content-between">
            <span>Bahasa Isyarat (BISINDO)</span>
            <i class="fas fa-times cursor-pointer" onclick="document.getElementById('toggleSL').click()"></i>
        </div>
        <div class="a11y-sl-video">
            <div class="text-center">
                <i class="fas fa-video-slash mb-2 d-block fa-2x"></i>
                Maaf, video isyarat untuk dokumen ini belum tersedia di database JDIH.
            </div>
        </div>
    </div>
    ` : ''}
    `;

    document.body.insertAdjacentHTML('beforeend', a11yHtml);

    // Elements
    const toggle = document.getElementById('a11yToggle');
    const panel = document.getElementById('a11yPanel');
    const body = document.body;

    // Load saved settings
    const settings = JSON.parse(localStorage.getItem('jdih_a11y_settings')) || {
        fontSize: 'normal',
        contrast: false,
        monochrome: false,
        highlightLinks: false,
        readableFont: false,
        visualCaptions: false,
        signLanguage: false
    };

    function applySettings() {
        // Font Size
        body.classList.remove('a11y-font-size-large', 'a11y-font-size-xlarge');
        if (settings.fontSize === 'large') body.classList.add('a11y-font-size-large');
        if (settings.fontSize === 'xlarge') body.classList.add('a11y-font-size-xlarge');

        // Contrast
        body.classList.toggle('a11y-high-contrast', settings.contrast);
        document.getElementById('toggleContrast').classList.toggle('active', settings.contrast);

        // Monochrome
        body.classList.toggle('a11y-monochrome', settings.monochrome);
        document.getElementById('toggleMonochrome').classList.toggle('active', settings.monochrome);

        // Links
        body.classList.toggle('a11y-highlight-links', settings.highlightLinks);
        document.getElementById('toggleLinks').classList.toggle('active', settings.highlightLinks);

        // Font
        body.classList.toggle('a11y-readable-font', settings.readableFont);
        document.getElementById('toggleFont').classList.toggle('active', settings.readableFont);

        // Captions
        const captionBox = document.getElementById('a11yCaptionBox');
        const toggleCaptions = document.getElementById('toggleCaptions');
        if (captionBox) captionBox.classList.toggle('active', settings.visualCaptions);
        if (toggleCaptions) toggleCaptions.classList.toggle('active', settings.visualCaptions);

        // Sign Language
        const slBox = document.getElementById('a11ySLBox');
        const toggleSL = document.getElementById('toggleSL');
        if (slBox) slBox.classList.toggle('active', settings.signLanguage);
        if (toggleSL) toggleSL.classList.toggle('active', settings.signLanguage);

        // Update Font Buttons active state
        document.querySelectorAll('[data-fz]').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-fz') === settings.fontSize);
        });

        localStorage.setItem('jdih_a11y_settings', JSON.stringify(settings));
    }

    // Initial apply
    applySettings();

    // Toggle Panel
    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        panel.classList.toggle('active');
    });

    document.addEventListener('click', (e) => {
        if (!panel.contains(e.target) && !toggle.contains(e.target)) {
            panel.classList.remove('active');
        }
    });

    // Font Size Handlers
    document.querySelectorAll('[data-fz]').forEach(btn => {
        btn.addEventListener('click', () => {
            settings.fontSize = btn.getAttribute('data-fz');
            applySettings();
        });
    });

    // Toggle Handlers
    const contrastBtn = document.getElementById('toggleContrast');
    if (contrastBtn) contrastBtn.onclick = () => {
        settings.contrast = !settings.contrast;
        applySettings();
    };

    const monochromeBtn = document.getElementById('toggleMonochrome');
    if (monochromeBtn) monochromeBtn.onclick = () => {
        settings.monochrome = !settings.monochrome;
        applySettings();
    };

    const linksBtn = document.getElementById('toggleLinks');
    if (linksBtn) linksBtn.onclick = () => {
        settings.highlightLinks = !settings.highlightLinks;
        applySettings();
    };

    const fontBtn = document.getElementById('toggleFont');
    if (fontBtn) fontBtn.onclick = () => {
        settings.readableFont = !settings.readableFont;
        applySettings();
    };

    const captionsBtn = document.getElementById('toggleCaptions');
    if (captionsBtn) captionsBtn.onclick = () => {
        settings.visualCaptions = !settings.visualCaptions;
        applySettings();
        // Global signal for other scripts
        window.dispatchEvent(new CustomEvent('a11y_captions_changed', { detail: settings.visualCaptions }));
    };

    const slBtn = document.getElementById('toggleSL');
    if (slBtn) slBtn.onclick = () => {
        settings.signLanguage = !settings.signLanguage;
        applySettings();
    };

    // Reset
    document.getElementById('resetA11y').onclick = () => {
        settings.fontSize = 'normal';
        settings.contrast = false;
        settings.monochrome = false;
        settings.highlightLinks = false;
        settings.readableFont = false;
        settings.visualCaptions = false;
        settings.signLanguage = false;
        applySettings();
    };
});
