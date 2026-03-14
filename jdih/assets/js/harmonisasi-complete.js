/**
 * Harmonisasi Complete JavaScript - All-in-One Solution
 * Complete harmonisasi module functionality
 * Prevents conflicts with header.php and other modules
 * 
 * IMPORTANT: This is the main harmonisasi module file
 * If you see errors, clear your browser cache!
 */

(function($) {
    'use strict';

    // ============================================================
    // AGGRESSIVE CACHE CLEANUP AND CONFLICT PREVENTION
    // ============================================================
    
    // Force HTTP protocol for localhost to prevent CORS issues
    if (window.location.hostname === 'localhost' && window.location.protocol === 'https:') {
        console.warn('HTTPS detected on localhost, redirecting to HTTP to prevent CORS issues');
        window.location.href = 'http://' + window.location.host + window.location.pathname + window.location.search;
        return;
    }
    
    // Remove any existing references to old files
    if (window.HarmonisasiAdmin && typeof window.HarmonisasiAdmin === 'object') {
        console.log('HarmonisasiAdmin namespace already exists, cleaning up...');
    }
    
    // Clear any DataTable instances that might conflict
    if ($.fn.DataTable) {
        try {
            if ($.fn.DataTable.isDataTable('#harmonisasi-table')) {
                $('#harmonisasi-table').DataTable().destroy();
            }
            if ($.fn.DataTable.isDataTable('#hasil-table')) {
                $('#hasil-table').DataTable().destroy();
            }
            if ($.fn.DataTable.isDataTable('#data-tables')) {
                $('#data-tables').DataTable().destroy();
            }
        } catch (e) {
            console.log('DataTable cleanup completed');
        }
    }

    // ============================================================
    // HARMONISASI MODULE NAMESPACE
    // ============================================================
    window.JDIHModules = window.JDIHModules || {};
    JDIHModules.Harmonisasi = {
        
        // Configuration
        config: {
            baseUrl: (function() {
                // Check if JDIH_CONFIG exists (prioritas utama)
                if (window.JDIH_CONFIG && window.JDIH_CONFIG.base_url) {
                    var baseUrl = window.JDIH_CONFIG.base_url;
                    // Ensure trailing slash
                    if (!baseUrl.endsWith('/')) {
                        baseUrl += '/';
                    }
                    return baseUrl;
                }
                
                // Fallback: Deteksi path dari window.location
                var protocol = window.location.protocol;
                var host = window.location.host;
                var pathname = window.location.pathname;
                
                // Deteksi base path dari pathname
                var basePath = '/';
                if (pathname.includes('/jdih/')) {
                    basePath = '/jdih/';
                } else if (pathname.includes('/webjdih/')) {
                    basePath = '/webjdih/';
                } else if (pathname.startsWith('/harmonisasi')) {
                    // Jika pathname dimulai dengan /harmonisasi, ambil path sebelum itu
                    var parts = pathname.split('/');
                    if (parts.length > 1 && parts[1] !== 'harmonisasi') {
                        basePath = '/' + parts[1] + '/';
                    }
                }
                
                // Force HTTP for localhost to prevent protocol inconsistency
                if (host.includes('localhost') || host.includes('.test')) {
                    protocol = 'http:';
                }
                
                return protocol + '//' + host + basePath;
            })(),
            csrfToken: window.JDIH_CONFIG ? window.JDIH_CONFIG.csrf_token : (window.csrf_token_value || ''),
            csrfName: window.JDIH_CONFIG ? window.JDIH_CONFIG.csrf_name : (window.csrf_token_name || 'csrf_app_token')
        },

        // Initialize module
        init: function() {
            console.log('JDIH Harmonisasi Module Initializing...');
            
            // Debug: Check current protocol and base URL
            console.log('Current Protocol:', window.location.protocol);
            console.log('Current URL:', window.location.href);
            console.log('Base URL from config:', this.config.baseUrl);
            
            // Fix protocol mismatch if detected
            if (this.config.baseUrl.indexOf('https://') === 0 && window.location.protocol === 'http:') {
                console.warn('Protocol mismatch detected - fixing base URL');
                this.config.baseUrl = this.config.baseUrl.replace(/^https:\/\//, 'http://');
            }
            
            this.initDataTables();
            this.initBulkActions();
            this.initAjaxHandlers();
            this.initFormValidation();
            this.initUIEnhancements();
            this.initGlobalUtilities();
            
            console.log('JDIH Harmonisasi Module Initialized Successfully');
        },

        // ============================================================
        // DATATABLE INITIALIZATION
        // ============================================================
        initDataTables: function() {
            // Initialize DataTable for harmonisasi table
            if ($('#harmonisasi-table').length && $.fn.DataTable) {
                this.initHarmonisasiDataTable();
            }

            // Initialize DataTable for hasil table
            if ($('#hasil-table').length && $.fn.DataTable) {
                this.initHasilDataTable();
            }

            // Skip DataTable initialization for data-tables (handled by robust-datatable-init.js)
            if ($('#data-tables').length && $.fn.DataTable) {
                // Skip initialization
            }
        },

        initHarmonisasiDataTable: function() {
            if ($.fn.DataTable.isDataTable('#harmonisasi-table')) {
                $('#harmonisasi-table').DataTable().destroy();
            }

            $('#harmonisasi-table').DataTable({
                responsive: true,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                order: [[4, 'desc']],
                columnDefs: [
                    {
                        targets: 0,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        targets: 5,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return JDIHModules.Harmonisasi.getStatusBadge(parseInt(data));
                        }
                    },
                    {
                        targets: 6,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return JDIHModules.Harmonisasi.getActionButtons(data, row[5]);
                        }
                    }
                ],
                pageLength: 25,
                dom: 'lBfrtip',
                buttons: [
                    { extend: 'copy', text: '<i class="fas fa-copy me-1"></i>Copy', className: 'btn btn-secondary btn-sm' },
                    { extend: 'excel', text: '<i class="fas fa-file-excel me-1"></i>Excel', className: 'btn btn-success btn-sm' },
                    { extend: 'pdf', text: '<i class="fas fa-file-pdf me-1"></i>PDF', className: 'btn btn-danger btn-sm' },
                    { extend: 'print', text: '<i class="fas fa-print me-1"></i>Print', className: 'btn btn-info btn-sm' }
                ]
            });
        },

        initHasilDataTable: function() {
            if ($.fn.DataTable.isDataTable('#hasil-table')) {
                $('#hasil-table').DataTable().destroy();
            }

            $('#hasil-table').DataTable({
                responsive: true,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                order: [[4, 'desc']],
                pageLength: 25,
                dom: 'lBfrtip',
                buttons: [
                    { extend: 'copy', text: '<i class="fas fa-copy me-1"></i>Copy', className: 'btn btn-secondary btn-sm' },
                    { extend: 'excel', text: '<i class="fas fa-file-excel me-1"></i>Excel', className: 'btn btn-success btn-sm' },
                    { extend: 'pdf', text: '<i class="fas fa-file-pdf me-1"></i>PDF', className: 'btn btn-danger btn-sm' }
                ]
            });
        },

        initPenugasanDataTable: function() {
            if ($.fn.DataTable.isDataTable('#data-tables')) {
                $('#data-tables').DataTable().destroy();
            }

            $('#data-tables').DataTable({
                responsive: true,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                order: [[4, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [6] },
                    { className: 'text-center', targets: [0, 5, 6] }
                ],
                pageLength: 25,
                dom: 'lBfrtip',
                buttons: [
                    { extend: 'copy', text: '<i class="fas fa-copy me-1"></i>Copy', className: 'btn btn-secondary btn-sm' },
                    { extend: 'excel', text: '<i class="fas fa-file-excel me-1"></i>Excel', className: 'btn btn-success btn-sm' },
                    { extend: 'pdf', text: '<i class="fas fa-file-pdf me-1"></i>PDF', className: 'btn btn-danger btn-sm' },
                    { extend: 'print', text: '<i class="fas fa-print me-1"></i>Print', className: 'btn btn-info btn-sm' }
                ],
                drawCallback: function(settings) {
                    var api = this.api();
                    var pageInfo = api.page.info();
                    api.rows({ page: 'current' }).nodes().each(function(cell, i) {
                        if (cell.cells && cell.cells[0]) {
                            cell.cells[0].innerHTML = pageInfo.start + i + 1;
                        }
                    });
                }
            });
        },

        // ============================================================
        // GLOBAL UTILITIES
        // ============================================================
        initGlobalUtilities: function() {
            this.autoInitDataTables();
        },

        autoInitDataTables: function() {
            // Skip pages that don't need auto-init
            var skipPages = ['data-peraturan-page', '/penugasan', '/verifikasi', '/validasi', '/finalisasi', '/paraf'];
            for (var i = 0; i < skipPages.length; i++) {
                if ($('body').hasClass(skipPages[i]) || window.location.pathname.indexOf(skipPages[i]) !== -1) {
                    console.log('📋 ' + skipPages[i] + ' page detected, skipping DataTable auto-init');
                    return;
                }
            }

            // Initialize harmonisasi-table with server-side processing
            if ($('#harmonisasi-table').length && !$.fn.DataTable.isDataTable('#harmonisasi-table')) {
                console.log('🔧 Initializing harmonisasi-table DataTable...');
                console.log('📍 Base URL:', this.config.baseUrl);
                console.log('🔑 CSRF Token:', this.config.csrfToken ? 'Available' : 'Missing');
                console.log('🔑 CSRF Name:', this.config.csrfName);
                
                var ajaxUrl = this.config.baseUrl + 'harmonisasi/ajax';
                console.log('🌐 AJAX URL:', ajaxUrl);
                
                var sspOptions = {
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: ajaxUrl,
                        type: 'POST',
                        dataType: 'json',
                        crossDomain: false,
                        xhrFields: {
                            withCredentials: true
                        },
                        beforeSend: function(xhr, settings) {
                            // Ensure we're using the same protocol
                            if (window.location.protocol === 'http:' && settings.url.indexOf('https://') === 0) {
                                settings.url = settings.url.replace(/^https:\/\//, 'http://');
                                console.log('⚠️ Protocol mismatch fixed, new URL:', settings.url);
                            }
                            console.log('📤 Sending AJAX request to:', settings.url);
                        },
                        data: function (d) {
                            // Add CSRF token only if enabled
                            if (JDIHModules.Harmonisasi.config.csrfToken) {
                                d[JDIHModules.Harmonisasi.config.csrfName] = JDIHModules.Harmonisasi.config.csrfToken;
                            }
                            
                            // Add custom filters
                            d.custom_filters = {
                                status: $('#statusFilter').val(),
                                jenis: $('#jenisFilter').val(),
                                start_date: $('#startDate').val(),
                                end_date: $('#endDate').val()
                            };
                            
                            return d;
                        },
                        error: function(xhr, error, thrown) {
                            console.error('❌ DataTable AJAX Error:', error, thrown);
                            console.error('📊 Response:', xhr.responseText);
                            console.error('📊 Status:', xhr.status);
                            console.error('📊 Status Text:', xhr.statusText);
                            console.error('📊 Ready State:', xhr.readyState);
                            
                            // Show user-friendly error message
                            if (xhr.status === 403) {
                                alert('Akses ditolak. Silakan refresh halaman dan coba lagi.');
                            } else if (xhr.status === 401) {
                                alert('Sesi Anda telah berakhir. Silakan login kembali.');
                                window.location.href = JDIHModules.Harmonisasi.config.baseUrl + 'login';
                            } else if (xhr.status === 0) {
                                alert('Tidak dapat terhubung ke server. Periksa koneksi internet Anda.');
                            } else {
                                alert('Terjadi kesalahan saat memuat data. Silakan refresh halaman.');
                            }
                        }
                    }
                };
                
                try {
                    this.initHarmonisasiMainDataTable('#harmonisasi-table', sspOptions);
                    console.log('✅ harmonisasi-table DataTable initialized successfully');
                } catch (e) {
                    console.error('❌ Error initializing harmonisasi-table:', e);
                }
            } else {
                if ($('#harmonisasi-table').length) {
                    console.log('ℹ️ harmonisasi-table already initialized');
                } else {
                    console.log('ℹ️ harmonisasi-table not found on this page');
                }
            }

            // Initialize hasil-table with server-side processing
            if ($('#hasil-table').length && !$.fn.DataTable.isDataTable('#hasil-table')) {
                var hasilOptions = {
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: this.config.baseUrl + 'harmonisasi/hasil/ajax',
                        type: 'POST',
                        dataType: 'json',
                        crossDomain: false,
                        xhrFields: {
                            withCredentials: true
                        },
                        beforeSend: function(xhr) {
                            // Ensure we're using the same protocol
                            if (window.location.protocol === 'http:' && this.url.indexOf('https://') === 0) {
                                this.url = this.url.replace(/^https:\/\//, 'http://');
                            }
                        },
                        data: function (d) {
                            // Add CSRF token only if enabled
                            if (JDIHModules.Harmonisasi.config.csrfToken) {
                                d[JDIHModules.Harmonisasi.config.csrfName] = JDIHModules.Harmonisasi.config.csrfToken;
                            }

                            // Add custom filters
                            d.custom_filters = {
                                status: $('#statusFilter').val(),
                                jenis: $('#jenisFilter').val(),
                                start_date: $('#startDate').val(),
                                end_date: $('#endDate').val()
                            };

                            return d;
                        },
                    }
                };
                this.initHarmonisasiMainDataTable('#hasil-table', hasilOptions);
            }

            // Skip data-tables initialization for data-peraturan page
            if ($('body').hasClass('data-peraturan-page')) {
                return;
            }
            
            // Initialize data-tables for harmonisasi pages only
            if ($('#data-tables').length && !$.fn.DataTable.isDataTable('#data-tables')) {
                var isMainPage = $('#data-tables').closest('.harmonisasi-module').length > 0;
                if (isMainPage) {
                    this.initHarmonisasiMainDataTable('#data-tables');
                } else {
                    this.initHarmonisasiDataTable('#data-tables');
                }
            }
        },

        initHarmonisasiMainDataTable: function(tableId, options = {}) {
            if (!$.fn.DataTable || !$(tableId).length) return null;

            try {
                if ($.fn.DataTable.isDataTable(tableId)) {
                    $(tableId).DataTable().destroy();
                }
            } catch (e) {
                console.warn('Error destroying existing DataTable:', e);
                $(tableId).removeClass('dataTable').removeData();
            }

            var defaultOptions = {
                responsive: true,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                order: [[4, 'desc']],
                columnDefs: [
                    {
                        targets: 0,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        targets: 5,
                        className: 'text-center',
                        render: function(data, type, row) {
                            var cls = JDIHModules.Harmonisasi.getStatusColorClass(parseInt(data));
                            var txt = '';
                            switch (parseInt(data)) {
                                case 1: txt = 'Draft'; break;
                                case 2: txt = 'Diajukan ke Kabag'; break;
                                case 3: txt = 'Ditugaskan ke Verifikator'; break;
                                case 4: txt = 'Proses Validasi'; break;
                                case 5: txt = 'Revisi'; break;
                                case 6: txt = 'Proses Finalisasi'; break;
                                case 7: txt = 'Menunggu Paraf OPD'; break;
                                case 8: txt = 'Menunggu Paraf Kabag'; break;
                                case 9: txt = 'Menunggu Paraf Asisten'; break;
                                case 11: txt = 'Menunggu Paraf/TTE Sekda'; break;
                                case 12: txt = 'Menunggu Paraf Wawako'; break;
                                case 13: txt = 'Menunggu TTE Walikota'; break;
                                case 14: txt = 'Selesai'; break;
                                case 15: txt = 'Ditolak'; break;
                                default: txt = 'Status';
                            }
                            return '<span class="badge ' + cls + '">' + txt + '</span>';
                        }
                    },
                    {
                        targets: 6,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            var id = data;
                            var statusId = row[5];
                            var baseUrl = JDIHModules.Harmonisasi.config.baseUrl;
                            var detailUrl = baseUrl + 'harmonisasi/show/' + id;
                            
                            var actions = '<a href="' + detailUrl + '" class="btn btn-outline-info btn-sm" title="Detail"><i class="fas fa-eye"></i></a>';
                            
                            if (statusId == 5) {
                                var revisiUrl = baseUrl + 'harmonisasi/showRevisiForm/' + id;
                                actions += ' <a href="' + revisiUrl + '" class="btn btn-outline-warning btn-sm" title="Revisi"><i class="fas fa-edit"></i></a>';
                            }
                            
                            return actions.replace(/\\"/g,'"');
                        }
                    }
                ],
                pageLength: 10,
                deferRender: true,
                dom: 'lfrtip', // Added 'f' to enable search box
                buttons: []
            };

            var finalOptions = $.extend(true, {}, defaultOptions, options);
            
            // Add custom filtering for server-side processing
            if (options.serverSide) {
                finalOptions.ajax = {
                    url: options.ajax.url,
                    type: 'POST',
                    data: function (d) {
                        // Add CSRF token only if enabled
                        if (JDIHModules.Harmonisasi.config.csrfToken) {
                            d[JDIHModules.Harmonisasi.config.csrfName] = JDIHModules.Harmonisasi.config.csrfToken;
                        }
                        
                        // Add custom filters
                        var statusFilter = $('#statusFilter').val();
                        var jenisFilter = $('#jenisFilter').val();
                        var startDate = $('#startDate').val();
                        var endDate = $('#endDate').val();
                        
                        if (statusFilter) {
                            d.custom_filters = d.custom_filters || {};
                            d.custom_filters.status = statusFilter;
                        }
                        
                        if (jenisFilter) {
                            d.custom_filters = d.custom_filters || {};
                            d.custom_filters.jenis = jenisFilter;
                        }
                        
                        if (startDate) {
                            d.custom_filters = d.custom_filters || {};
                            d.custom_filters.start_date = startDate;
                        }
                        
                        if (endDate) {
                            d.custom_filters = d.custom_filters || {};
                            d.custom_filters.end_date = endDate;
                        }
                        
                        return d;
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTables AJAX Error:', error, thrown);
                        console.error('Response:', xhr.responseText);
                        console.error('Status:', xhr.status);
                        console.error('ReadyState:', xhr.readyState);
                        
                        // Enhanced CORS error handling
                        if (xhr.status === 0 || xhr.readyState === 0) {
                            console.error('CORS or network error detected');
                            
                            // Check if it's a protocol mismatch
                            if (window.location.protocol === 'http:' && xhr.responseText && xhr.responseText.indexOf('https://') !== -1) {
                                console.error('Protocol mismatch detected - HTTPS redirect on HTTP page');
                                alert('Terjadi kesalahan protokol. Halaman ini menggunakan HTTP, tetapi server mencoba redirect ke HTTPS. Silakan refresh halaman.');
                            } else {
                                // Try to reload the page with proper protocol
                                console.log('Attempting to fix protocol mismatch...');
                                var currentUrl = window.location.href;
                                if (currentUrl.indexOf('https://') === 0) {
                                    window.location.href = currentUrl.replace(/^https:\/\//, 'http://');
                                } else {
                                    alert('Terjadi kesalahan koneksi. Silakan refresh halaman dan coba lagi.');
                                }
                            }
                        }
                        
                        // Handle specific HTTP status codes
                        if (xhr.status === 403) {
                            alert('Akses ditolak. Anda tidak memiliki permission untuk melihat data ini.');
                        } else if (xhr.status === 401) {
                            alert('Sesi tidak valid. Silakan login kembali.');
                        } else if (xhr.status === 500) {
                            alert('Terjadi kesalahan server. Silakan coba lagi nanti.');
                        }
                        
                        // Additional protocol consistency check
                        if (xhr.status === 301 || xhr.status === 302) {
                            console.error('Redirect detected - checking protocol consistency');
                            var redirectUrl = xhr.getResponseHeader('Location');
                            if (redirectUrl && redirectUrl.indexOf('https://localhost') === 0) {
                                console.error('HTTPS redirect detected - forcing HTTP');
                                // Force HTTP redirect
                                window.location.href = redirectUrl.replace(/^https:\/\//, 'http://');
                                return;
                            }
                        }
                    }
                };
            }

            var dataTable = $(tableId).DataTable(finalOptions);
            
            // Store DataTable instance for external access
            window.harmonisasiTable = dataTable;
            
            // Initialize filter functionality
            this.initFilterFunctionality(dataTable);
            
            // Add error recovery mechanism
            dataTable.on('error.dt', function(e, settings, techNote, message) {
                console.error('DataTable error:', message);
                // Try to reload data after 3 seconds
                setTimeout(function() {
                    if (dataTable) {
                        dataTable.ajax.reload(null, false);
                    }
                }, 3000);
            });
            
            return dataTable;
        },

        // ============================================================
        // FILTER FUNCTIONALITY
        // ============================================================
        initFilterFunctionality: function(dataTable) {
            var self = this;
            
            // Load jenis peraturan options
            this.loadJenisPeraturanOptions();
            
            // Apply filter button
            $('#applyFilter').off('click').on('click', function() {
                self.applyFilters(dataTable);
            });
            
            // Reset filter button
            $('#resetFilter').off('click').on('click', function() {
                self.resetFilters(dataTable);
            });
            
            // Quick filter buttons
            $('.quick-filter').off('click').on('click', function() {
                var status = $(this).data('status');
                
                // Remove active class from all buttons
                $('.quick-filter').removeClass('active');
                
                // Add active class to clicked button
                $(this).addClass('active');
                
                // Set status filter value
                $('#statusFilter').val(status);
                
                // Apply filter
                self.applyFilters(dataTable);
            });
            
            // Export filtered data
            $('#exportFiltered').off('click').on('click', function() {
                self.exportFilteredData(dataTable);
            });
            
            // Collapse toggle functionality
            $('[data-bs-toggle="collapse"]').off('click').on('click', function() {
                var isExpanded = $(this).attr('aria-expanded') === 'true';
                $(this).find('.collapse-text').text(isExpanded ? 'Tampilkan' : 'Sembunyikan');
            });
        },

        loadJenisPeraturanOptions: function() {
            var self = this;
            var data = {};
            
            // Add CSRF token only if enabled
            if (this.config.csrfToken) {
                data[this.config.csrfName] = this.config.csrfToken;
            }
            
            $.ajax({
                url: this.config.baseUrl + 'harmonisasi/get_jenis_peraturan',
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success && response.data) {
                        var jenisFilter = $('#jenisFilter');
                        jenisFilter.find('option:not(:first)').remove();
                        
                        response.data.forEach(function(jenis) {
                            jenisFilter.append('<option value="' + jenis.id + '">' + jenis.nama_jenis + '</option>');
                        });
                    }
                },
                error: function() {
                    console.warn('Failed to load jenis peraturan options');
                }
            });
        },

        applyFilters: function(dataTable) {
            // Show loading state
            this.showLoadingState();
            
            // Redraw DataTable to apply filters
            if (dataTable && typeof dataTable.draw === 'function') {
                dataTable.draw();
            }
        },

        resetFilters: function(dataTable) {
            // Reset all filter inputs
            $('#statusFilter').val('');
            $('#jenisFilter').val('');
            $('#startDate').val('');
            $('#endDate').val('');
            
            // Remove active class from quick filter buttons
            $('.quick-filter').removeClass('active');
            
            // Redraw DataTable
            if (dataTable && typeof dataTable.draw === 'function') {
                dataTable.draw();
            }
        },

        exportFilteredData: function(dataTable) {
            // Get current filter values
            var filters = {
                status: $('#statusFilter').val(),
                jenis: $('#jenisFilter').val(),
                start_date: $('#startDate').val(),
                end_date: $('#endDate').val()
            };
            
            // Create export URL with filters
            var exportUrl = this.config.baseUrl + 'harmonisasi/export';
            var params = new URLSearchParams();
            
            Object.keys(filters).forEach(function(key) {
                if (filters[key]) {
                    params.append(key, filters[key]);
                }
            });
            
            if (params.toString()) {
                exportUrl += '?' + params.toString();
            }
            
            // Trigger download
            window.open(exportUrl, '_blank');
        },

        showLoadingState: function() {
            var tableContainer = $('.table-responsive');
            if (tableContainer.length) {
                var overlay = $('<div class="loading-overlay"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
                tableContainer.css('position', 'relative').append(overlay);
                
                // Remove overlay after 1 second
                setTimeout(function() {
                    overlay.remove();
                }, 1000);
            }
        },

        initHarmonisasiDataTable: function(tableId, options = {}) {
            if (!$.fn.DataTable || !$(tableId).length) return null;

            try {
                if ($.fn.DataTable.isDataTable(tableId)) {
                    $(tableId).DataTable().destroy();
                }
            } catch (e) {
                console.warn('Error destroying existing DataTable:', e);
                $(tableId).removeClass('dataTable').removeData();
            }

            var defaultOptions = {
                responsive: true,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                order: [[4, 'desc']],
                columnDefs: [
                    {
                        targets: 0,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        targets: 5,
                        className: 'text-center',
                        render: function(data, type, row) {
                            var cls = JDIHModules.Harmonisasi.getStatusColorClass(parseInt(data));
                            var txt = '';
                            switch (parseInt(data)) {
                                case 1: txt = 'Draft'; break;
                                case 2: txt = 'Diajukan ke Kabag'; break;
                                case 3: txt = 'Ditugaskan ke Verifikator'; break;
                                case 4: txt = 'Proses Validasi'; break;
                                case 5: txt = 'Revisi'; break;
                                case 6: txt = 'Proses Finalisasi'; break;
                                case 7: txt = 'Menunggu Paraf OPD'; break;
                                case 8: txt = 'Menunggu Paraf Kabag'; break;
                                case 9: txt = 'Menunggu Paraf Asisten'; break;
                                case 11: txt = 'Menunggu Paraf/TTE Sekda'; break;
                                case 12: txt = 'Menunggu Paraf Wawako'; break;
                                case 13: txt = 'Menunggu TTE Walikota'; break;
                                case 14: txt = 'Selesai'; break;
                                case 15: txt = 'Ditolak'; break;
                                default: txt = 'Status';
                            }
                            return '<span class="badge ' + cls + '">' + txt + '</span>';
                        }
                    },
                    {
                        targets: 6,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            var id = data;
                            var statusId = row[5];
                            var baseUrl = JDIHModules.Harmonisasi.config.baseUrl;
                            var detailUrl = baseUrl + 'harmonisasi/show/' + id;
                            
                            var actions = '<a href="' + detailUrl + '" class="btn btn-outline-info btn-sm" title="Detail"><i class="fas fa-eye"></i></a>';
                            
                            if (statusId == 5) {
                                var revisiUrl = baseUrl + 'harmonisasi/showRevisiForm/' + id;
                                actions += ' <a href="' + revisiUrl + '" class="btn btn-outline-warning btn-sm" title="Revisi"><i class="fas fa-edit"></i></a>';
                            }
                            
                            return actions.replace(/\\"/g,'"');
                        }
                    }
                ],
                pageLength: 25,
                dom: 'lBfrtip',
                buttons: [
                    { extend: 'copy', text: '<i class="fas fa-copy me-1"></i>Copy', className: 'btn btn-secondary btn-sm' },
                    { extend: 'excel', text: '<i class="fas fa-file-excel me-1"></i>Excel', className: 'btn btn-success btn-sm' },
                    { extend: 'pdf', text: '<i class="fas fa-file-pdf me-1"></i>PDF', className: 'btn btn-danger btn-sm' },
                    { extend: 'print', text: '<i class="fas fa-print me-1"></i>Print', className: 'btn btn-info btn-sm' }
                ],
                drawCallback: function(settings) {
                    var api = this.api();
                    var pageInfo = api.page.info();
                    api.rows({ page: 'current' }).nodes().each(function(cell, i) {
                        if (cell.cells && cell.cells[0]) {
                            cell.cells[0].innerHTML = pageInfo.start + i + 1;
                        }
                    });
                }
            };

            var finalOptions = $.extend(true, {}, defaultOptions, options);
            var table = $(tableId).DataTable(finalOptions);
            console.log('DataTable initialized for:', tableId);
            return table;
        },

        // ============================================================
        // BULK ACTIONS
        // ============================================================
        initBulkActions: function() {
            $('#bulk-submit-btn').on('click', function(e) {
                e.preventDefault();
                JDIHModules.Harmonisasi.handleBulkSubmit();
            });

            $('#select-all-drafts').on('change', function() {
                $('.draft-checkbox').prop('checked', this.checked);
                JDIHModules.Harmonisasi.updateBulkButtonState();
            });

            $(document).on('change', '.draft-checkbox', function() {
                JDIHModules.Harmonisasi.updateBulkButtonState();
            });
        },

        handleBulkSubmit: function() {
            var selectedDrafts = $('.draft-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedDrafts.length === 0) {
                this.showAlert('Pilih draft yang akan diajukan', 'warning');
                return;
            }

            if (!confirm('Apakah Anda yakin ingin mengajukan ' + selectedDrafts.length + ' draft?')) {
                return;
            }

            this.submitBulkDrafts(selectedDrafts);
        },

        submitBulkDrafts: function(draftIds) {
            var data = {
                draft_ids: draftIds
            };
            
            // Add CSRF token only if enabled
            if (this.config.csrfToken) {
                data[this.config.csrfName] = this.config.csrfToken;
            }
            
            $.ajax({
                url: this.config.baseUrl + 'harmonisasi/bulk-submit-drafts',
                type: 'POST',
                data: data,
                dataType: 'json',
                beforeSend: function() {
                    JDIHModules.Harmonisasi.showLoading();
                },
                success: function(response) {
                    if (response.success) {
                        JDIHModules.Harmonisasi.showAlert(response.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        JDIHModules.Harmonisasi.showAlert(response.error || 'Terjadi kesalahan', 'error');
                    }
                },
                error: function(xhr) {
                    JDIHModules.Harmonisasi.handleAjaxError(xhr);
                },
                complete: function() {
                    JDIHModules.Harmonisasi.hideLoading();
                }
            });
        },

        updateBulkButtonState: function() {
            var checkedCount = $('.draft-checkbox:checked').length;
            var totalCount = $('.draft-checkbox').length;
            
            $('#select-all-drafts').prop('indeterminate', checkedCount > 0 && checkedCount < totalCount);
            $('#bulk-submit-btn').prop('disabled', checkedCount === 0);
            $('#bulk-submit-btn').text('Ajukan Draft (' + checkedCount + ')');
        },

        // ============================================================
        // AJAX HANDLERS
        // ============================================================
        initAjaxHandlers: function() {
            $(document).ajaxError(function(event, xhr, settings) {
                JDIHModules.Harmonisasi.handleAjaxError(xhr);
            });

            $(document).ajaxSuccess(function(event, xhr, settings) {
                JDIHModules.Harmonisasi.handleAjaxSuccess(xhr);
            });
        },

        handleAjaxError: function(xhr) {
            var message = 'Terjadi kesalahan sistem';
            
            // Handle CORS and network errors
            if (xhr.status === 0 || xhr.readyState === 0) {
                message = 'Kesalahan koneksi. Pastikan Anda menggunakan protokol yang sama (HTTP/HTTPS).';
                console.error('CORS or network error detected');
            } else if (xhr.status === 403) {
                message = 'Akses ditolak. Silakan login kembali.';
            } else if (xhr.status === 404) {
                message = 'Data tidak ditemukan';
            } else if (xhr.status === 500) {
                message = 'Kesalahan server internal';
            } else if (xhr.status === 302 || xhr.status === 301) {
                message = 'Terjadi redirect yang tidak diizinkan. Silakan refresh halaman.';
            }

            this.showAlert(message, 'error');
        },

        handleAjaxSuccess: function(xhr) {
            if (xhr.responseJSON && xhr.responseJSON.csrf_token) {
                this.config.csrfToken = xhr.responseJSON.csrf_token;
            }
        },

        // ============================================================
        // FORM VALIDATION
        // ============================================================
        initFormValidation: function() {
            $('#draft_peraturan').on('change', function(e) {
                JDIHModules.Harmonisasi.validateFileUpload(e.target);
            });

            $('#judul_peraturan').on('input', function() {
                JDIHModules.Harmonisasi.validateTitle(this);
            });

            $('#harmonisasi-form').on('submit', function(e) {
                if (!JDIHModules.Harmonisasi.validateForm(this)) {
                    e.preventDefault();
                }
            });
        },

        validateFileUpload: function(input) {
            var file = input.files[0];
            var maxSize = 25 * 1024 * 1024; // 25MB
            var allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

            if (file) {
                if (file.size > maxSize) {
                    this.showAlert('Ukuran file terlalu besar. Maksimal 25MB.', 'error');
                    input.value = '';
                    return false;
                }

                if (!allowedTypes.includes(file.type)) {
                    this.showAlert('Format file tidak didukung. Gunakan PDF, DOC, atau DOCX.', 'error');
                    input.value = '';
                    return false;
                }

                this.showFilePreview(file);
            }
        },

        validateTitle: function(input) {
            var length = input.value.length;
            var minLength = 10;

            if (length > 0 && length < minLength) {
                $(input).addClass('is-invalid').removeClass('is-valid');
            } else if (length >= minLength) {
                $(input).removeClass('is-invalid').addClass('is-valid');
            }
        },

        validateForm: function(form) {
            var isValid = true;
            var requiredFields = $(form).find('[required]');

            requiredFields.each(function() {
                if (!this.value.trim()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            return isValid;
        },

        // ============================================================
        // UI ENHANCEMENTS
        // ============================================================
        initUIEnhancements: function() {
            $('[data-bs-toggle="tooltip"]').tooltip();

            $('.btn').on('click', function() {
                if (!$(this).hasClass('no-loading')) {
                    $(this).addClass('loading');
                }
            });

            $('.alert').delay(5000).fadeOut();
        },

        // ============================================================
        // UTILITY FUNCTIONS
        // ============================================================
        getStatusBadge: function(statusId) {
            var statusColors = {
                1: 'secondary', 2: 'warning', 3: 'info', 4: 'primary',
                5: 'warning', 6: 'info', 7: 'primary', 8: 'primary',
                9: 'primary', 11: 'primary', 12: 'primary', 13: 'primary',
                14: 'success', 15: 'danger'
            };

            var statusTexts = {
                1: 'Draft', 2: 'Diajukan ke Kabag', 3: 'Ditugaskan ke Verifikator', 4: 'Proses Validasi',
                5: 'Revisi', 6: 'Proses Finalisasi', 7: 'Menunggu Paraf OPD', 8: 'Menunggu Paraf Kabag',
                9: 'Menunggu Paraf Asisten', 11: 'Menunggu Paraf/TTE Sekda', 12: 'Menunggu Paraf Wawako',
                13: 'Menunggu TTE Walikota', 14: 'Selesai', 15: 'Ditolak'
            };

            var color = statusColors[statusId] || 'secondary';
            var text = statusTexts[statusId] || 'Status';

            return '<span class="badge-pill-premium badge-soft-' + color + '" data-bs-toggle="tooltip" title="' + text + '">' + text + '</span>';
        },

        getActionButtons: function(id, statusId) {
            var baseUrl = this.config.baseUrl;
            var buttons = '<div class="d-flex justify-content-center gap-2">';
            
            buttons += '<a href="' + baseUrl + 'harmonisasi/show/' + id + '" class="action-btn-pill" title="Lihat Detail"><i class="fas fa-eye fa-sm"></i></a>';

            if (statusId == 5) {
                buttons += '<a href="' + baseUrl + 'harmonisasi/showRevisiForm/' + id + '" class="action-btn-pill text-warning" title="Revisi Berkas"><i class="fas fa-edit fa-sm"></i></a>';
            }

            buttons += '</div>';
            return buttons;
        },

        showAlert: function(message, type) {
            var alertClass = type === 'error' ? 'alert-danger' : 
                           type === 'success' ? 'alert-success' : 
                           type === 'warning' ? 'alert-warning' : 'alert-info';

            var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                           '<i class="fas fa-' + (type === 'error' ? 'exclamation-triangle' : 'check-circle') + ' me-2"></i>' +
                           message +
                           '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                           '</div>';

            $('.harmonisasi-module').prepend(alertHtml);
        },

        showLoading: function() {
            $('.harmonisasi-module').addClass('loading');
        },

        hideLoading: function() {
            $('.harmonisasi-module').removeClass('loading');
        },

        showFilePreview: function(file) {
            var fileName = file.name;
            var fileSize = this.formatFileSize(file.size);

            $('#file-name').text(fileName);
            $('#file-size').text(fileSize);
            $('#file-preview').removeClass('d-none');
        },

        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        // ============================================================
        // GLOBAL UTILITY FUNCTIONS
        // ============================================================
        confirmSubmit: function(message = 'Apakah Anda yakin ingin melanjutkan?') {
            return confirm(message);
        },

        confirmHarmonisasiSubmit: function() {
            return confirm('Apakah Anda yakin ingin mengajukan draft ini? Proses ini tidak dapat dibatalkan.');
        },

        formatDate: function(dateString) {
            if (!dateString) return 'N/A';
            
            try {
                var date = new Date(dateString);
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

        getStatusColorClass: function(statusId) {
            var statusColors = {
                1: 'secondary', 2: 'warning', 3: 'info', 4: 'primary',
                5: 'warning', 6: 'info', 7: 'primary', 8: 'primary',
                9: 'primary', 11: 'primary', 12: 'primary', 13: 'primary',
                14: 'success', 15: 'danger'
            };
            
            return statusColors[statusId] || 'secondary';
        }
    };

    // ============================================================
    // EXPORT FUNCTIONS FOR BACKWARD COMPATIBILITY
    // ============================================================
    window.HarmonisasiAdmin = {
        initDataTable: function(tableId, options) {
            return JDIHModules.Harmonisasi.initHarmonisasiDataTable(tableId, options);
        },
        initMainDataTable: function(tableId, options) {
            return JDIHModules.Harmonisasi.initHarmonisasiMainDataTable(tableId, options);
        },
        confirmSubmit: function(message) {
            return JDIHModules.Harmonisasi.confirmSubmit(message);
        },
        confirmHarmonisasiSubmit: function() {
            return JDIHModules.Harmonisasi.confirmHarmonisasiSubmit();
        },
        formatDate: function(dateString) {
            return JDIHModules.Harmonisasi.formatDate(dateString);
        },
        getStatusColorClass: function(statusId) {
            return JDIHModules.Harmonisasi.getStatusColorClass(statusId);
        }
    };

    // ============================================================
    // INITIALIZATION WHEN DOCUMENT IS READY
    // ============================================================
    $(document).ready(function() {
        $('body').addClass('harmonisasi-module');
        JDIHModules.Harmonisasi.init();

        // Table Row Click Handler
        $(document).on('click', '#harmonisasi-table tbody tr', function(e) {
            if ($(e.target).closest('.action-btn-pill, .badge-pill-premium, button, a').length) return;
            
            var rowData = $('#harmonisasi-table').DataTable().row(this).data();
            if (rowData && rowData[6]) {
                var baseUrl = JDIHModules.Harmonisasi.config.baseUrl;
                window.location.href = baseUrl + 'harmonisasi/show/' + rowData[6];
            }
        });

        $('.btn').on('click', function() {
            var $btn = $(this);
            if (!$btn.hasClass('btn-loading')) {
                $btn.addClass('btn-loading');
                setTimeout(function() {
                    $btn.removeClass('btn-loading');
                }, 2000);
            }
        });
    });

})(jQuery);
