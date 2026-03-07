/**
 * JDIH Admin Modern - Theme JavaScript
 * Theme management and settings for admin panel
 * 
 * @author AI Assistant
 * @version 1.0
 */

const AdminTheme = {
    // Theme configuration
    themes: {
        light: {
            name: 'Light',
            icon: '☀️',
            colors: {
                primary: '#2563eb',
                secondary: '#64748b',
                success: '#059669',
                warning: '#d97706',
                danger: '#dc2626',
                info: '#0891b2'
            }
        },
        dark: {
            name: 'Dark',
            icon: '🌙',
            colors: {
                primary: '#3b82f6',
                secondary: '#94a3b8',
                success: '#10b981',
                warning: '#f59e0b',
                danger: '#ef4444',
                info: '#06b6d4'
            }
        },
        blue: {
            name: 'Blue',
            icon: '🔵',
            colors: {
                primary: '#1e40af',
                secondary: '#475569',
                success: '#047857',
                warning: '#b45309',
                danger: '#b91c1c',
                info: '#0891b2'
            }
        },
        green: {
            name: 'Green',
            icon: '🟢',
            colors: {
                primary: '#059669',
                secondary: '#64748b',
                success: '#047857',
                warning: '#d97706',
                danger: '#dc2626',
                info: '#0891b2'
            }
        }
    },

    // Initialize theme system
    init() {
        this.initThemeSelector();
        this.initColorCustomizer();
        this.initLayoutSettings();
        this.initAnimationSettings();
        this.initAccessibilitySettings();
        this.loadSavedSettings();
    },

    // Theme selector
    initThemeSelector() {
        const themeSelector = document.getElementById('theme-selector');
        if (!themeSelector) return;

        // Populate theme options
        Object.keys(this.themes).forEach(themeKey => {
            const theme = this.themes[themeKey];
            const option = document.createElement('option');
            option.value = themeKey;
            option.textContent = `${theme.icon} ${theme.name}`;
            themeSelector.appendChild(option);
        });

        // Set current theme
        const currentTheme = localStorage.getItem('admin-theme') || 'light';
        themeSelector.value = currentTheme;

        // Handle theme change
        themeSelector.addEventListener('change', (e) => {
            const selectedTheme = e.target.value;
            this.applyTheme(selectedTheme);
            localStorage.setItem('admin-theme', selectedTheme);
        });
    },

    // Apply theme
    applyTheme(themeKey) {
        const theme = this.themes[themeKey];
        if (!theme) return;

        // Set data attribute
        document.documentElement.setAttribute('data-theme', themeKey);
        document.body.setAttribute('data-theme', themeKey);

        // Update CSS custom properties
        Object.entries(theme.colors).forEach(([key, value]) => {
            document.documentElement.style.setProperty(`--${key}-color`, value);
        });

        // Update theme indicator
        const themeIndicator = document.querySelector('.theme-indicator');
        if (themeIndicator) {
            themeIndicator.textContent = theme.icon;
        }

        // Trigger theme change event
        document.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: themeKey } }));
    },

    // Color customizer
    initColorCustomizer() {
        const colorInputs = document.querySelectorAll('.color-input');
        
        colorInputs.forEach(input => {
            const colorKey = input.dataset.color;
            const preview = document.querySelector(`.color-preview[data-color="${colorKey}"]`);
            
            if (preview) {
                // Set initial color
                const currentColor = getComputedStyle(document.documentElement)
                    .getPropertyValue(`--${colorKey}-color`).trim();
                input.value = currentColor;
                preview.style.backgroundColor = currentColor;

                // Handle color change
                input.addEventListener('input', (e) => {
                    const newColor = e.target.value;
                    preview.style.backgroundColor = newColor;
                    document.documentElement.style.setProperty(`--${colorKey}-color`, newColor);
                    
                    // Save custom colors
                    this.saveCustomColors();
                });
            }
        });
    },

    // Save custom colors
    saveCustomColors() {
        const customColors = {};
        const colorInputs = document.querySelectorAll('.color-input');
        
        colorInputs.forEach(input => {
            const colorKey = input.dataset.color;
            customColors[colorKey] = input.value;
        });

        localStorage.setItem('admin-custom-colors', JSON.stringify(customColors));
    },

    // Load custom colors
    loadCustomColors() {
        const savedColors = localStorage.getItem('admin-custom-colors');
        if (!savedColors) return;

        try {
            const customColors = JSON.parse(savedColors);
            Object.entries(customColors).forEach(([key, value]) => {
                document.documentElement.style.setProperty(`--${key}-color`, value);
                
                // Update inputs
                const input = document.querySelector(`.color-input[data-color="${key}"]`);
                if (input) {
                    input.value = value;
                }
                
                // Update previews
                const preview = document.querySelector(`.color-preview[data-color="${key}"]`);
                if (preview) {
                    preview.style.backgroundColor = value;
                }
            });
        } catch (error) {
            console.error('Failed to load custom colors:', error);
        }
    },

    // Layout settings
    initLayoutSettings() {
        // Sidebar position
        const sidebarPosition = document.getElementById('sidebar-position');
        if (sidebarPosition) {
            const savedPosition = localStorage.getItem('sidebar-position') || 'left';
            sidebarPosition.value = savedPosition;
            
            sidebarPosition.addEventListener('change', (e) => {
                const position = e.target.value;
                this.setSidebarPosition(position);
                localStorage.setItem('sidebar-position', position);
            });
        }

        // Sidebar behavior
        const sidebarBehavior = document.getElementById('sidebar-behavior');
        if (sidebarBehavior) {
            const savedBehavior = localStorage.getItem('sidebar-behavior') || 'fixed';
            sidebarBehavior.value = savedBehavior;
            
            sidebarBehavior.addEventListener('change', (e) => {
                const behavior = e.target.value;
                this.setSidebarBehavior(behavior);
                localStorage.setItem('sidebar-behavior', behavior);
            });
        }

        // Content layout
        const contentLayout = document.getElementById('content-layout');
        if (contentLayout) {
            const savedLayout = localStorage.getItem('content-layout') || 'fluid';
            contentLayout.value = savedLayout;
            
            contentLayout.addEventListener('change', (e) => {
                const layout = e.target.value;
                this.setContentLayout(layout);
                localStorage.setItem('content-layout', layout);
            });
        }
    },

    // Set sidebar position
    setSidebarPosition(position) {
        const sidebar = document.querySelector('.sidebar-nav');
        const mainContent = document.querySelector('.main-content');
        
        if (!sidebar || !mainContent) return;

        // Remove existing position classes
        sidebar.classList.remove('sidebar-left', 'sidebar-right');
        mainContent.classList.remove('content-left', 'content-right');

        // Apply new position
        if (position === 'right') {
            sidebar.classList.add('sidebar-right');
            mainContent.classList.add('content-right');
        } else {
            sidebar.classList.add('sidebar-left');
            mainContent.classList.add('content-left');
        }
    },

    // Set sidebar behavior
    setSidebarBehavior(behavior) {
        const sidebar = document.querySelector('.sidebar-nav');
        if (!sidebar) return;

        // Remove existing behavior classes
        sidebar.classList.remove('sidebar-fixed', 'sidebar-sticky', 'sidebar-floating');

        // Apply new behavior
        sidebar.classList.add(`sidebar-${behavior}`);
    },

    // Set content layout
    setContentLayout(layout) {
        const container = document.querySelector('.admin-container');
        if (!container) return;

        // Remove existing layout classes
        container.classList.remove('layout-fluid', 'layout-boxed', 'layout-compact');

        // Apply new layout
        container.classList.add(`layout-${layout}`);
    },

    // Animation settings
    initAnimationSettings() {
        const animationToggle = document.getElementById('animation-toggle');
        if (animationToggle) {
            const animationsEnabled = localStorage.getItem('admin-animations') !== 'false';
            animationToggle.checked = animationsEnabled;
            
            animationToggle.addEventListener('change', (e) => {
                const enabled = e.target.checked;
                this.setAnimations(enabled);
                localStorage.setItem('admin-animations', enabled);
            });
        }

        // Animation speed
        const animationSpeed = document.getElementById('animation-speed');
        if (animationSpeed) {
            const savedSpeed = localStorage.getItem('admin-animation-speed') || 'normal';
            animationSpeed.value = savedSpeed;
            
            animationSpeed.addEventListener('change', (e) => {
                const speed = e.target.value;
                this.setAnimationSpeed(speed);
                localStorage.setItem('admin-animation-speed', speed);
            });
        }
    },

    // Set animations
    setAnimations(enabled) {
        const root = document.documentElement;
        
        if (enabled) {
            root.style.removeProperty('--animation-disabled');
        } else {
            root.style.setProperty('--animation-disabled', 'none');
        }
    },

    // Set animation speed
    setAnimationSpeed(speed) {
        const speeds = {
            slow: '0.5s',
            normal: '0.3s',
            fast: '0.15s'
        };

        const duration = speeds[speed] || speeds.normal;
        document.documentElement.style.setProperty('--transition-normal', duration);
    },

    // Accessibility settings
    initAccessibilitySettings() {
        // High contrast mode
        const highContrastToggle = document.getElementById('high-contrast-toggle');
        if (highContrastToggle) {
            const highContrast = localStorage.getItem('admin-high-contrast') === 'true';
            highContrastToggle.checked = highContrast;
            
            highContrastToggle.addEventListener('change', (e) => {
                const enabled = e.target.checked;
                this.setHighContrast(enabled);
                localStorage.setItem('admin-high-contrast', enabled);
            });
        }

        // Reduced motion
        const reducedMotionToggle = document.getElementById('reduced-motion-toggle');
        if (reducedMotionToggle) {
            const reducedMotion = localStorage.getItem('admin-reduced-motion') === 'true';
            reducedMotionToggle.checked = reducedMotion;
            
            reducedMotionToggle.addEventListener('change', (e) => {
                const enabled = e.target.checked;
                this.setReducedMotion(enabled);
                localStorage.setItem('admin-reduced-motion', enabled);
            });
        }

        // Font size
        const fontSizeSlider = document.getElementById('font-size-slider');
        if (fontSizeSlider) {
            const savedSize = localStorage.getItem('admin-font-size') || '14';
            fontSizeSlider.value = savedSize;
            
            fontSizeSlider.addEventListener('input', (e) => {
                const size = e.target.value;
                this.setFontSize(size);
                localStorage.setItem('admin-font-size', size);
            });
        }
    },

    // Set high contrast
    setHighContrast(enabled) {
        const root = document.documentElement;
        
        if (enabled) {
            root.classList.add('high-contrast');
        } else {
            root.classList.remove('high-contrast');
        }
    },

    // Set reduced motion
    setReducedMotion(enabled) {
        const root = document.documentElement;
        
        if (enabled) {
            root.style.setProperty('--transition-normal', '0s');
            root.style.setProperty('--transition-fast', '0s');
            root.style.setProperty('--transition-slow', '0s');
        } else {
            root.style.removeProperty('--transition-normal');
            root.style.removeProperty('--transition-fast');
            root.style.removeProperty('--transition-slow');
        }
    },

    // Set font size
    setFontSize(size) {
        document.documentElement.style.setProperty('--base-font-size', `${size}px`);
    },

    // Load saved settings
    loadSavedSettings() {
        // Load custom colors
        this.loadCustomColors();

        // Apply saved theme
        const savedTheme = localStorage.getItem('admin-theme') || 'light';
        this.applyTheme(savedTheme);

        // Apply saved layout settings
        const sidebarPosition = localStorage.getItem('sidebar-position') || 'left';
        this.setSidebarPosition(sidebarPosition);

        const sidebarBehavior = localStorage.getItem('sidebar-behavior') || 'fixed';
        this.setSidebarBehavior(sidebarBehavior);

        const contentLayout = localStorage.getItem('content-layout') || 'fluid';
        this.setContentLayout(contentLayout);

        // Apply saved animation settings
        const animationsEnabled = localStorage.getItem('admin-animations') !== 'false';
        this.setAnimations(animationsEnabled);

        const animationSpeed = localStorage.getItem('admin-animation-speed') || 'normal';
        this.setAnimationSpeed(animationSpeed);

        // Apply saved accessibility settings
        const highContrast = localStorage.getItem('admin-high-contrast') === 'true';
        this.setHighContrast(highContrast);

        const reducedMotion = localStorage.getItem('admin-reduced-motion') === 'true';
        this.setReducedMotion(reducedMotion);

        const fontSize = localStorage.getItem('admin-font-size') || '14';
        this.setFontSize(fontSize);
    },

    // Reset to defaults
    resetToDefaults() {
        // Clear all saved settings
        localStorage.removeItem('admin-theme');
        localStorage.removeItem('admin-custom-colors');
        localStorage.removeItem('sidebar-position');
        localStorage.removeItem('sidebar-behavior');
        localStorage.removeItem('content-layout');
        localStorage.removeItem('admin-animations');
        localStorage.removeItem('admin-animation-speed');
        localStorage.removeItem('admin-high-contrast');
        localStorage.removeItem('admin-reduced-motion');
        localStorage.removeItem('admin-font-size');

        // Reload page to apply defaults
        window.location.reload();
    },

    // Export settings
    exportSettings() {
        const settings = {
            theme: localStorage.getItem('admin-theme') || 'light',
            customColors: localStorage.getItem('admin-custom-colors'),
            sidebarPosition: localStorage.getItem('sidebar-position') || 'left',
            sidebarBehavior: localStorage.getItem('sidebar-behavior') || 'fixed',
            contentLayout: localStorage.getItem('content-layout') || 'fluid',
            animations: localStorage.getItem('admin-animations') !== 'false',
            animationSpeed: localStorage.getItem('admin-animation-speed') || 'normal',
            highContrast: localStorage.getItem('admin-high-contrast') === 'true',
            reducedMotion: localStorage.getItem('admin-reduced-motion') === 'true',
            fontSize: localStorage.getItem('admin-font-size') || '14'
        };

        const blob = new Blob([JSON.stringify(settings, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = 'admin-settings.json';
        a.click();
        
        URL.revokeObjectURL(url);
    },

    // Import settings
    importSettings(file) {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            try {
                const settings = JSON.parse(e.target.result);
                
                // Apply imported settings
                Object.entries(settings).forEach(([key, value]) => {
                    if (value !== null && value !== undefined) {
                        localStorage.setItem(`admin-${key}`, value);
                    }
                });

                // Reload page to apply settings
                window.location.reload();
            } catch (error) {
                AdminModern.showNotification('Gagal mengimpor pengaturan: ' + error.message, 'danger');
            }
        };

        reader.readAsText(file);
    }
};

// Initialize theme when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    AdminTheme.init();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminTheme;
} 