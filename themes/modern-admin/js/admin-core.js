/**
 * JDIH Admin Modern - Core JavaScript
 * Modern admin panel functionality for JDIH Kota Padang
 * 
 * @author AI Assistant
 * @version 1.0
 */

const AdminModern = {
    // Configuration
    config: {
        baseUrl: window.adminConfig?.baseUrl || '',
        csrfToken: window.adminConfig?.csrfToken || '',
        currentUser: window.adminConfig?.currentUser || {},
        theme: window.adminConfig?.theme || 'light'
    },

    // Initialize admin panel
    init() {
        this.initTheme();
        this.initSidebar();
        this.initSearch();
        this.initNotifications();
        this.initModals();
        this.initLoading();
        this.initCharts();
        this.initTables();
        this.initForms();
        this.initTooltips();
        this.initDropdowns();
        
        console.log('JDIH Admin Modern initialized');
    },

    // Theme management
    initTheme() {
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                this.toggleTheme();
            });
        }

        // Apply saved theme
        this.applyTheme(this.config.theme);
    },

    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        this.applyTheme(newTheme);
        localStorage.setItem('admin-theme', newTheme);
    },

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        document.body.setAttribute('data-theme', theme);
        
        // Update theme indicator
        const themeIndicator = document.querySelector('.theme-indicator');
        if (themeIndicator) {
            themeIndicator.textContent = theme === 'dark' ? '🌙' : '☀️';
        }
    },

    // Sidebar management
    initSidebar() {
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('.sidebar-nav');
        const mainContent = document.querySelector('.main-content');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent?.classList.toggle('expanded');
                
                // Save state
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebar-collapsed', isCollapsed);
            });
        }

        // Restore sidebar state
        const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        if (isCollapsed && sidebar) {
            sidebar.classList.add('collapsed');
            mainContent?.classList.add('expanded');
        }

        // Mobile sidebar
        const mobileToggle = document.querySelector('.mobile-sidebar-toggle');
        if (mobileToggle && sidebar) {
            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 1024) {
                if (!sidebar?.contains(e.target) && !mobileToggle?.contains(e.target)) {
                    sidebar?.classList.remove('show');
                }
            }
        });
    },

    // Search functionality
    initSearch() {
        const searchInput = document.getElementById('global-search');
        const searchResults = document.getElementById('search-results');
        let searchTimeout;

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    this.hideSearchResults();
                    return;
                }

                searchTimeout = setTimeout(() => {
                    this.performSearch(query);
                }, 300);
            });

            // Close search results when clicking outside
            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !searchResults?.contains(e.target)) {
                    this.hideSearchResults();
                }
            });
        }
    },

    async performSearch(query) {
        try {
            const response = await fetch(`${this.config.baseUrl}admin-modern/api-search?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const results = await response.json();
                this.displaySearchResults(results);
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    },

    displaySearchResults(results) {
        const searchResults = document.getElementById('search-results');
        if (!searchResults) return;

        if (results.length === 0) {
            searchResults.innerHTML = '<div class="search-no-results">Tidak ada hasil ditemukan</div>';
        } else {
            const html = results.map(result => `
                <a href="${result.url}" class="search-result-item">
                    <div class="search-result-icon">
                        <i class="${result.icon}"></i>
                    </div>
                    <div class="search-result-content">
                        <div class="search-result-title">${result.title}</div>
                        <div class="search-result-description">${result.description}</div>
                    </div>
                    <div class="search-result-type">${result.type}</div>
                </a>
            `).join('');
            
            searchResults.innerHTML = html;
        }

        searchResults.classList.add('show');
    },

    hideSearchResults() {
        const searchResults = document.getElementById('search-results');
        if (searchResults) {
            searchResults.classList.remove('show');
        }
    },

    // Notification system
    initNotifications() {
        // Create notification container if it doesn't exist
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'notification-container';
            document.body.appendChild(container);
        }
    },

    showNotification(message, type = 'info', duration = 5000) {
        const container = document.getElementById('notification-container');
        if (!container) return;

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        
        const icon = this.getNotificationIcon(type);
        
        notification.innerHTML = `
            <div class="notification-header">
                <div class="notification-title">
                    <i class="${icon}"></i>
                    ${this.getNotificationTitle(type)}
                </div>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="notification-message">${message}</div>
        `;

        container.appendChild(notification);

        // Show animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, duration);
        }
    },

    getNotificationIcon(type) {
        const icons = {
            success: 'bi bi-check-circle',
            warning: 'bi bi-exclamation-triangle',
            danger: 'bi bi-x-circle',
            info: 'bi bi-info-circle'
        };
        return icons[type] || icons.info;
    },

    getNotificationTitle(type) {
        const titles = {
            success: 'Berhasil',
            warning: 'Peringatan',
            danger: 'Error',
            info: 'Informasi'
        };
        return titles[type] || titles.info;
    },

    // Modal system
    initModals() {
        // Close modals when clicking overlay
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                this.closeModal(e.target);
            }
        });

        // Close modals with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal-overlay.show');
                if (openModal) {
                    this.closeModal(openModal);
                }
            }
        });
    },

    showModal(modalId, options = {}) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Set modal content if provided
        if (options.content) {
            const modalBody = modal.querySelector('.modal-body');
            if (modalBody) {
                modalBody.innerHTML = options.content;
            }
        }

        // Set modal title if provided
        if (options.title) {
            const modalTitle = modal.querySelector('.modal-title');
            if (modalTitle) {
                modalTitle.textContent = options.title;
            }
        }

        modal.classList.add('show');
        document.body.style.overflow = 'hidden';

        // Focus first input
        setTimeout(() => {
            const firstInput = modal.querySelector('input, select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        }, 100);
    },

    closeModal(modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    },

    // Loading system
    initLoading() {
        // Create loading overlay if it doesn't exist
        if (!document.getElementById('loading-overlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="loading-content">
                    <div class="loading-spinner"></div>
                    <p>Memuat...</p>
                </div>
            `;
            document.body.appendChild(overlay);
        }
    },

    showLoading(message = 'Memuat...') {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            const messageEl = overlay.querySelector('p');
            if (messageEl) {
                messageEl.textContent = message;
            }
            overlay.classList.add('show');
        }
    },

    hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.remove('show');
        }
    },

    // Charts initialization
    initCharts() {
        // Initialize ApexCharts if available
        if (typeof ApexCharts !== 'undefined') {
            this.initDashboardCharts();
        }
    },

    initDashboardCharts() {
        // Document per month chart
        const docChartEl = document.getElementById('documents-chart');
        if (docChartEl) {
            const docChart = new ApexCharts(docChartEl, {
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Dokumen',
                    data: [30, 40, 35, 50, 49, 60, 70, 91, 125, 80, 90, 100]
                }],
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                },
                colors: ['#2563eb'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.2,
                        stops: [0, 90, 100]
                    }
                }
            });
            docChart.render();
        }

        // Document types chart
        const typeChartEl = document.getElementById('types-chart');
        if (typeChartEl) {
            const typeChart = new ApexCharts(typeChartEl, {
                chart: {
                    type: 'donut',
                    height: 300
                },
                series: [44, 55, 13, 33],
                labels: ['Perda', 'Perwal', 'SK Walikota', 'Lainnya'],
                colors: ['#2563eb', '#059669', '#d97706', '#64748b']
            });
            typeChart.render();
        }
    },

    // Table functionality
    initTables() {
        // Initialize DataTables if available
        if (typeof $.fn.DataTable !== 'undefined') {
            $('.table-modern').each(function() {
                $(this).DataTable({
                    responsive: true,
                    language: {
                        url: `${AdminModern.config.baseUrl}vendors/datatables/Indonesian.json`
                    }
                });
            });
        }
    },

    // Form functionality
    initForms() {
        // Auto-submit forms with data-auto-submit attribute
        document.querySelectorAll('form[data-auto-submit]').forEach(form => {
            form.addEventListener('change', () => {
                form.submit();
            });
        });

        // Form validation
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });
    },

    validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'Field ini wajib diisi');
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });

        return isValid;
    },

    showFieldError(field, message) {
        this.clearFieldError(field);
        
        field.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    },

    clearFieldError(field) {
        field.classList.remove('is-invalid');
        
        const errorDiv = field.parentNode.querySelector('.form-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    },

    // Tooltip initialization
    initTooltips() {
        // Initialize Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    },

    // Dropdown initialization
    initDropdowns() {
        // Initialize Bootstrap dropdowns if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
            const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
        }
    },

    // Utility functions
    formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    },

    formatDate(date, format = 'DD/MM/YYYY') {
        if (!date) return '';
        
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        
        return format.replace('DD', day).replace('MM', month).replace('YYYY', year);
    },

    formatDateTime(date) {
        if (!date) return '';
        
        const d = new Date(date);
        return d.toLocaleString('id-ID');
    },

    // AJAX helper
    async ajax(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        };

        const config = { ...defaultOptions, ...options };

        try {
            this.showLoading();
            const response = await fetch(url, config);
            this.hideLoading();

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            } else {
                return await response.text();
            }
        } catch (error) {
            this.hideLoading();
            this.showNotification('Terjadi kesalahan: ' + error.message, 'danger');
            throw error;
        }
    },

    // File upload helper
    async uploadFile(file, url, onProgress = null) {
        const formData = new FormData();
        formData.append('file', file);

        const xhr = new XMLHttpRequest();

        return new Promise((resolve, reject) => {
            xhr.upload.addEventListener('progress', (e) => {
                if (onProgress && e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    onProgress(percentComplete);
                }
            });

            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        resolve(response);
                    } catch (e) {
                        resolve(xhr.responseText);
                    }
                } else {
                    reject(new Error(`Upload failed: ${xhr.status}`));
                }
            });

            xhr.addEventListener('error', () => {
                reject(new Error('Upload failed'));
            });

            xhr.open('POST', url);
            xhr.send(formData);
        });
    }
};

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminModern;
} 