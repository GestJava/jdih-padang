/**
 * JDIH Asset Manager
 * Mengelola loading dan inisialisasi assets secara optimal
 */

(function(window, $) {
    'use strict';
    
    // Asset Manager Configuration
    const AssetManager = {
        config: window.JDIH_CONFIG || {},
        loaded: {},
        initialized: {},
        
        // Initialize asset manager
        init: function() {
            this.hideLoadingOverlay();
            this.initCoreFeatures();
            this.initPageSpecific();
            this.bindEvents();
        },
        
        // Hide loading overlay when page is ready
        hideLoadingOverlay: function() {
            $(document).ready(function() {
                $('body').addClass('page-ready');
                setTimeout(function() {
                    $('.loading-overlay').fadeOut(300);
                }, 500);
            });
        },
        
        // Initialize core features
        initCoreFeatures: function() {
            $(document).ready(function() {
                console.log('🎯 JDIH Asset Manager: Initializing core features...');
                
                // Initialize overlay scrollbars
                if ($.fn.overlayScrollbars) {
                    $('.sidebar, .overlayscollbar').overlayScrollbars({
                        scrollbars: {
                            autoHide: 'leave',
                            autoHideDelay: 100
                        }
                    });
                    console.log('✅ OverlayScrollbars: Initialized');
                } else {
                    console.warn('⚠️ OverlayScrollbars: Library not available');
                }
                
                // Initialize datepicker
                if ($.fn.datepicker) {
                    $('.date-picker').datepicker({
                        format: "dd-mm-yyyy",
                        weekStart: 1,
                        language: "id",
                        autoclose: true
                    });
                    console.log('✅ Datepicker: Initialized');
                } else {
                    console.warn('⚠️ Datepicker: Library not available');
                }
                
                // Initialize select2
                if ($.fn.select2) {
                    $('select[multiple][name="tags[]"]').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        tags: true,
                        placeholder: 'Ketik untuk mencari tags atau buat tag baru...',
                        allowClear: true,
                        minimumInputLength: 1,
                        ajax: {
                            url: base_url + 'api/searchTags',
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    q: params.term,
                                    page: params.page || 1
                                };
                            },
                            processResults: function(data, params) {
                                params.page = params.page || 1;
                                return {
                                    results: data.results,
                                    pagination: { more: data.pagination && data.pagination.more }
                                };
                            },
                            cache: true
                        },
                        createTag: function(params) {
                            var term = $.trim(params.term);
                            if (term === '') return null;
                            return {
                                id: term,
                                text: term,
                                newTag: true
                            };
                        }
                    }).on('select2:select', function(e) {
                        var data = e.params.data;
                        if (data.newTag) {
                            var $select = $(this);
                            // Kirim AJAX untuk membuat tag baru
                            $.ajax({
                                url: base_url + 'api/createTag',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    nama_tag: data.text,
                                    [window.JDIH_CONFIG.csrf_name]: window.JDIH_CONFIG.csrf_token
                                },
                                success: function(response) {
                                    if (response.status === 'success') {
                                        // Ganti value option dengan id_tag dari database
                                        var newOption = new Option(data.text, response.id_tag, true, true);
                                        $select.append(newOption).trigger('change');
                                    } else {
                                        // Hapus option jika gagal
                                        $select.find('option[value="' + data.id + '"]').remove();
                                        $select.trigger('change');
                                        if (response.message) alert(response.message);
                                    }
                                },
                                error: function() {
                                    $select.find('option[value="' + data.id + '"]').remove();
                                    $select.trigger('change');
                                    alert('Gagal menambah tag baru.');
                                }
                            });
                        }
                    });
                    console.log('✅ Select2: Initialized (AJAX dynamic tags)');
                } else {
                    console.warn('⚠️ Select2: Library not available');
                }
                
                // Initialize tooltips
                if ($.fn.tooltip) {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                    console.log('✅ Bootstrap Tooltips: Initialized');
                }
                
                // Initialize popovers
                if ($.fn.popover) {
                    $('[data-bs-toggle="popover"]').popover();
                    console.log('✅ Bootstrap Popovers: Initialized');
                }
                
                console.log('🎯 JDIH Asset Manager: Core features initialization complete');
            });
        },
        
        // Initialize page-specific features
        initPageSpecific: function() {
            const currentUrl = this.config.current_url;
            
            console.log('🎯 JDIH Asset Manager: Initializing page-specific features...');
            console.log('📍 Current URL:', currentUrl);
            
            // Check if we're on harmonisasi pages
            if (currentUrl.includes('harmonisasi') || currentUrl.includes('penugasan') || 
                currentUrl.includes('verifikasi') || currentUrl.includes('validasi') || 
                currentUrl.includes('finalisasi')) {
                console.log('📋 Page Type: Harmonisasi Module');
                this.initHarmonisasiFeatures();
            }
            
            // Check if we're on data-peraturan page
            if (currentUrl.includes('data-peraturan')) {
                console.log('📋 Page Type: Data Peraturan');
                this.initDataPeraturanFeatures();
            }
            
            // Check if we're on dashboard
            if (currentUrl.includes('dashboard')) {
                console.log('📋 Page Type: Dashboard');
                this.initDashboardFeatures();
            }
            
            console.log('🎯 JDIH Asset Manager: Page-specific features initialization complete');
        },
        
        // Initialize harmonisasi features
        initHarmonisasiFeatures: function() {
            $(document).ready(function() {
                console.log('📊 Harmonisasi Module: Checking DataTable...');
                
                // Skip DataTable initialization for data-peraturan pages
                if ($('body').hasClass('data-peraturan-page')) {
                    console.log('📋 Data Peraturan page detected, skipping harmonisasi DataTable initialization');
                    return;
                }
                
                // Skip DataTable initialization for harmonisasi-table (server-side rendered table)
                if ($('#harmonisasi-table').length) {
                    console.log('📋 Harmonisasi table detected (server-side rendered), skipping DataTable initialization');
                    return;
                }
                
                // Initialize DataTable for harmonisasi pages (only for data-tables ID)
                if ($('#data-tables').length && $.fn.DataTable) {
                    console.log('📋 Found DataTable element: #data-tables');
                    
                    // Check if DataTable is already initialized
                    if ($.fn.DataTable.isDataTable('#data-tables')) {
                        console.log('✅ DataTable already initialized by harmonisasi-admin.js');
                        return;
                    }
                    
                    // Check if it's the main harmonisasi page
                    var isMainPage = $('#data-tables').closest('.harmonisasi-module').length > 0;
                    
                    if (isMainPage) {
                        console.log('📋 Initializing main harmonisasi DataTable...');
                        window.HarmonisasiAdmin?.initMainDataTable('#data-tables');
                    } else {
                        console.log('📋 Initializing standard harmonisasi DataTable...');
                        window.HarmonisasiAdmin?.initDataTable('#data-tables');
                    }
                } else {
                    console.log('ℹ️ No DataTable element found or DataTable library not available');
                }
            });
        },
        
        // Initialize data peraturan features
        initDataPeraturanFeatures: function() {
            $(document).ready(function() {
                // DataTable initialization for data-peraturan is handled in the view
                // This is just for additional features
                console.log('Data Peraturan page initialized');
            });
        },
        
        // Initialize dashboard features
        initDashboardFeatures: function() {
            const self = this; // Store reference to AssetManager
            $(document).ready(function() {
                // Initialize charts if Chart.js is available with performance optimization
                if (typeof Chart !== 'undefined') {
                    // Use requestAnimationFrame for better performance
                    requestAnimationFrame(() => {
                        // Dashboard charts initialization
                        self.debounceChartResize();
                        console.log('Dashboard charts initialized');
                    });
                }
            });
        },
        
        // Debounce chart resize for better performance
        debounceChartResize: function() {
            let resizeTimeout;
            const idleCallback = window.requestIdleCallback || function(cb) {
                return setTimeout(cb, 1);
            };
            
            $(window).off('resize.charts').on('resize.charts', () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    idleCallback(() => {
                        if (typeof Chart !== 'undefined') {
                            Chart.instances.forEach(chart => {
                                if (chart && typeof chart.resize === 'function') {
                                    chart.resize();
                                }
                            });
                        }
                    });
                }, 250);
            });
        },
        
        // Bind global events
        bindEvents: function() {
            $(document).ready(function() {
                // Mobile menu toggle
                // $('#mobile-menu-btn').on('click', function(e) {
                //     e.preventDefault();
                //     $('body').toggleClass('mobile-menu-show');
                //     if ($('body').hasClass('mobile-menu-show')) {
                //         Cookies.set('jwd_adm_mobile', '1');
                //     } else {
                //         Cookies.set('jwd_adm_mobile', '0');
                //     }
                // });
                
                // Mobile right menu toggle
                $('#mobile-menu-btn-right').on('click', function(e) {
                    e.preventDefault();
                    $('header').toggleClass('mobile-right-menu-show');
                });
                
                // Sidebar guide with passive listeners
                $('.sidebar-guide').on('mouseenter', function() {
                    $('body').addClass('show-sidebar');
                });
                
                $('.sidebar').on('mouseleave', function() {
                    $('body').removeClass('show-sidebar');
                });
                
                // Add passive event listeners for scroll events
                $(window).on('scroll', { passive: true }, function() {
                    // Passive scroll handler
                });
                
                // Add passive event listeners for touch events
                $(document).on('touchstart touchmove touchend', { passive: true }, function() {
                    // Passive touch handler
                });
                
                // Sidebar guide click handler
                $('.sidebar-guide').on('click', function() {
                    $('body').toggleClass('sidebar-hidden');
                    if ($('body').hasClass('sidebar-hidden')) {
                        Cookies.set('jwd_adm_sidebar', 'hidden');
                    } else {
                        Cookies.set('jwd_adm_sidebar', 'visible');
                    }
                });
                
                // Theme switcher
                $('body').on('click', '.nav-theme-option button', function() {
                    const $this = $(this);
                    const $ul = $this.parents('ul').eq(0);
                    const $icon = $this.children('.bi:not(.check)').clone().removeClass('me-2');
                    const $link = $ul.prev().empty();
                    const themeValue = $this.attr('data-theme-value');
                    
                    $link.append($icon);
                    $ul.find('button').removeClass('active');
                    $this.addClass('active');
                    
                    let themeColor = '';
                    const themeCurrent = Cookies.get('jwd_adm_theme');
                    
                    if (themeValue === 'system') {
                        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                            themeColor = 'dark';
                        } else {
                            themeColor = 'light';
                        }
                        Cookies.set('jwd_adm_theme_system', 'true');
                    } else {
                        themeColor = themeValue;
                        Cookies.set('jwd_adm_theme_system', 'false');
                    }
                    
                    $('html').attr('data-bs-theme', themeColor);
                    Cookies.set('jwd_adm_theme', themeColor);
                });
                
                // Delete confirmation
                $('table').on('click', '[data-action="delete-data"]', function(e) {
                    e.preventDefault();
                    const $this = $(this);
                    const $form = $this.parents('form:eq(0)');
                    
                    if (typeof bootbox !== 'undefined') {
                        bootbox.confirm({
                            message: $this.attr('data-delete-title'),
                            callback: function(confirmed) {
                                if (confirmed) {
                                    $form.submit();
                                }
                            },
                            centerVertical: true
                        });
                    } else {
                        if (confirm($this.attr('data-delete-title'))) {
                            $form.submit();
                        }
                    }
                });
                
                // Number only inputs
                $('.number-only').on('keyup', function() {
                    this.value = this.value.replace(/\D/i, '');
                });
                
                // Loading states for buttons with optimized timing
                $('.btn').on('click', function() {
                    const $btn = $(this);
                    if (!$btn.hasClass('btn-loading')) {
                        requestAnimationFrame(function() {
                            $btn.addClass('btn-loading');
                        });
                        
                        // Use requestIdleCallback if available, otherwise setTimeout
                        const idleCallback = window.requestIdleCallback || function(cb) {
                            return setTimeout(cb, 1);
                        };
                        
                        idleCallback(function() {
                            setTimeout(function() {
                                requestAnimationFrame(function() {
                                    $btn.removeClass('btn-loading');
                                });
                            }, 2000);
                        });
                    }
                });
            });
        },
        
        // Utility functions
        utils: {
            // Format date
            formatDate: function(dateString) {
                if (!dateString) return 'N/A';
                
                try {
                    const date = new Date(dateString);
                    if (isNaN(date.getTime())) return 'N/A';
                    
                    return date.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch (e) {
                    return 'N/A';
                }
            },
            
            // Get status color class
            getStatusColorClass: function(statusId) {
                const statusColors = {
                    1: 'bg-secondary text-white',
                    2: 'bg-warning text-dark',
                    3: 'bg-info text-white',
                    4: 'bg-danger text-white',
                    5: 'bg-primary text-white',
                    6: 'bg-danger text-white',
                    7: 'bg-success text-white',
                    8: 'bg-dark text-white',
                    9: 'bg-dark text-white',
                    10: 'bg-dark text-white',
                    11: 'bg-dark text-white',
                    12: 'bg-dark text-white',
                    13: 'bg-success text-white',
                    14: 'bg-secondary text-white',
                    15: 'bg-danger text-white'
                };
                
                return statusColors[statusId] || 'bg-secondary text-white';
            },
            
            // Show notification
            showNotification: function(message, type = 'info') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: type,
                        title: type.charAt(0).toUpperCase() + type.slice(1),
                        text: message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    alert(message);
                }
            },
            
            // AJAX request helper
            ajaxRequest: function(url, data, options = {}) {
                const defaultOptions = {
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                };
                
                const finalOptions = $.extend(true, defaultOptions, options);
                
                // Add CSRF token if available
                if (window.JDIH_CONFIG && window.JDIH_CONFIG.csrf_token) {
                    finalOptions.data = finalOptions.data || {};
                    finalOptions.data[window.JDIH_CONFIG.csrf_name] = window.JDIH_CONFIG.csrf_token;
                }
                
                return $.ajax(url, finalOptions);
            }
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        AssetManager.init();
    });
    
    // Expose to global scope
    window.JDIHAssetManager = AssetManager;
    
})(window, jQuery); 