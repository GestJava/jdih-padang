document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('accessibility-toggle');
    const menu = document.getElementById('accessibility-menu');
    const body = document.body;

    // Check if elements exist
    if (!toggleButton || !menu) {
        console.error('Accessibility widget elements not found.');
        return;
    }

    const increaseTextButton = document.getElementById('increase-text');
    const decreaseTextButton = document.getElementById('decrease-text');
    const highContrastButton = document.getElementById('high-contrast');
    const highlightLinksButton = document.getElementById('highlight-links');
    const resetButton = document.getElementById('reset-accessibility');

    const FONT_SIZE_KEY = 'accessibility_font_size';
    const CONTRAST_KEY = 'accessibility_contrast';
    const LINKS_KEY = 'accessibility_links';
    const BASE_FONT_SIZE = 16; // Assuming base font size is 16px

    // Function to apply settings from localStorage
    function applySavedSettings() {
        // Font Size
        const savedFontSize = localStorage.getItem(FONT_SIZE_KEY);
        if (savedFontSize) {
            body.style.fontSize = savedFontSize + 'px';
        }

        // High Contrast
        if (localStorage.getItem(CONTRAST_KEY) === 'true') {
            body.classList.add('high-contrast');
        }

        // Highlight Links
        if (localStorage.getItem(LINKS_KEY) === 'true') {
            body.classList.add('links-highlighted');
        }
    }

    // Function to get current font size
    function getCurrentFontSize() {
        const currentSize = parseFloat(window.getComputedStyle(body, null).getPropertyValue('font-size'));
        return currentSize;
    }

    // Toggle menu visibility
    toggleButton.addEventListener('click', function() {
        menu.classList.toggle('active');
    });

    // Increase text size
    increaseTextButton.addEventListener('click', function() {
        let currentSize = getCurrentFontSize();
        if (currentSize < 24) { // Max size limit
            let newSize = currentSize + 1;
            body.style.fontSize = newSize + 'px';
            localStorage.setItem(FONT_SIZE_KEY, newSize);
        }
    });

    // Decrease text size
    decreaseTextButton.addEventListener('click', function() {
        let currentSize = getCurrentFontSize();
        if (currentSize > 12) { // Min size limit
            let newSize = currentSize - 1;
            body.style.fontSize = newSize + 'px';
            localStorage.setItem(FONT_SIZE_KEY, newSize);
        }
    });

    // Toggle high contrast
    highContrastButton.addEventListener('click', function() {
        const isContrast = body.classList.toggle('high-contrast');
        localStorage.setItem(CONTRAST_KEY, isContrast);
    });

    // Toggle highlight links
    highlightLinksButton.addEventListener('click', function() {
        const areLinksHighlighted = body.classList.toggle('links-highlighted');
        localStorage.setItem(LINKS_KEY, areLinksHighlighted);
    });

    // Reset all settings
    resetButton.addEventListener('click', function() {
        // Reset styles
        body.style.fontSize = BASE_FONT_SIZE + 'px';
        body.classList.remove('high-contrast', 'links-highlighted');

        // Clear localStorage
        localStorage.removeItem(FONT_SIZE_KEY);
        localStorage.removeItem(CONTRAST_KEY);
        localStorage.removeItem(LINKS_KEY);
    });

    // Apply settings on page load
    applySavedSettings();

    // === Text to Speech ===
    (function(){
        const btnSpeak = document.getElementById('speak-page');
        const btnStop  = document.getElementById('stop-speech');
        if(!btnSpeak || !btnStop) return;

        function getPageText(){
            const main = document.querySelector('main');
            return (main ? main.innerText : document.body.innerText).replace(/\s+/g,' ').trim();
        }

        btnSpeak.addEventListener('click', ()=>{
            if(!('speechSynthesis' in window)){
                alert('Browser Anda tidak mendukung Text-to-Speech.');
                return;
            }
            if(window.speechSynthesis.speaking){
                // jika sedang membaca, abaikan
                return;
            }
            const utter = new SpeechSynthesisUtterance(getPageText());
            utter.lang = 'id-ID';
            window.speechSynthesis.speak(utter);
        });

        btnStop.addEventListener('click', ()=>{
            if('speechSynthesis' in window){
                window.speechSynthesis.cancel();
            }
        });
    })();
});
