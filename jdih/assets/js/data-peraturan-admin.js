/**
 * Data Peraturan Admin JavaScript
 * Handles DataTables initialization and AJAX functionality for data-peraturan module
 * 
 * @author Agus Salim
 * @year 2025
 * @version 1.1 - Added cache busting and enhanced error handling
 */

(function($) {
    'use strict';

    // Data Peraturan Admin namespace
    window.DataPeraturanAdmin = {
        
        // Initialize DataTable for data-peraturan
        initDataTable: function(tableSelector) {
            console.log('📋 Data Peraturan: Initializing DataTable for', tableSelector);
            console.log('🕐 Cache busting timestamp:', new Date().toISOString());
            
            // Check if DataTable library is available
            if (typeof $.fn.DataTable === 'undefined') {
                console.error('❌ DataTables library not loaded');
                this.showError('DataTables library tidak tersedia. Silakan refresh halaman.');
                return;
            }
            
            // Check if table exists
            if ($(tableSelector).length === 0) {
                console.warn('⚠️ Table element not found:', tableSelector);
                return;
            }
            
            // Destroy existing DataTable if any - with better error handling
            if ($.fn.DataTable.isDataTable(tableSelector)) {
                try {
                    $(tableSelector).DataTable().destroy(true); // destroy and remove all event listeners
                    // Kosongkan tbody agar data lama hilang, pertahankan thead untuk mencegah error parentNode
                    $(tableSelector).find('tbody').empty();
                } catch (e) {
                    console.warn('⚠️ Error destroying existing DataTable:', e.message);
                }
            }
            
            // Register custom sorting type for file exists column
            // Untuk server-side processing, kita perlu menggunakan approach yang berbeda
            // Gunakan data attribute di cell untuk sorting
            $.fn.dataTable.ext.order['file-exists-pre'] = function(data) {
                // Extract data-file-exists value from HTML
                try {
                    if (typeof data === 'string') {
                        // Cari data-file-exists dengan regex yang lebih robust
                        var match = data.match(/data-file-exists\s*=\s*["'](\d)["']/i);
                        if (match && match[1]) {
                            return parseInt(match[1]); // 1 = ada, 0 = hilang
                        }
                        // Fallback: check for specific text patterns
                        if (data.indexOf('File hilang') !== -1 || data.indexOf('Tidak ada file') !== -1) {
                            return 0; // File hilang
                        }
                        if (data.indexOf('btn-success') !== -1 || data.indexOf('Lihat') !== -1) {
                            return 1; // File ada (ada tombol hijau atau teks "Lihat")
                        }
                    }
                    return 0; // Default: file hilang
                } catch (e) {
                    console.warn('Error in file-exists sorting:', e, data);
                    return 0;
                }
            };
            
            // Alternative: Use jQuery to extract data attribute after rendering
            $.fn.dataTable.ext.order['file-exists-asc'] = function(settings, col) {
                return this.api().column(col, {order: 'index'}).nodes().map(function(td, i) {
                    var $td = $(td);
                    var fileExists = $td.find('[data-file-exists]').attr('data-file-exists') || 
                                    $td.attr('data-file-exists') || '0';
                    return parseInt(fileExists) || 0;
                });
            };
            
            $.fn.dataTable.ext.order['file-exists-desc'] = function(settings, col) {
                return this.api().column(col, {order: 'index'}).nodes().map(function(td, i) {
                    var $td = $(td);
                    var fileExists = $td.find('[data-file-exists]').attr('data-file-exists') || 
                                    $td.attr('data-file-exists') || '0';
                    return -(parseInt(fileExists) || 0); // Negative for descending
                });
            };
            
            // Initialize DataTable with server-side processing
            var dataTable = $(tableSelector).DataTable({
                "processing": true,
                "serverSide": true,
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "ajax": {
                    "url": base_url + 'data-peraturan/ajax_list',
                    "type": "POST",
                    "data": function(d) {
                        // Add CSRF token only if available and CSRF is enabled
                        if (typeof csrf_token_name !== 'undefined' && typeof csrf_token_value !== 'undefined' && csrf_token_name && csrf_token_value) {
                            d[csrf_token_name] = csrf_token_value;
                            console.log('🔑 CSRF Token added:', csrf_token_name, '=', csrf_token_value);
                        } else {
                            console.log('⚠️ CSRF Token not available, request may be blocked');
                        }
                        console.log('📤 DataTables AJAX Request:', d);
                    },
                    "error": function(xhr, error, code) {
                        console.error('❌ DataTables AJAX Error:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText
                        });
                        
                        // Show user-friendly error message
                        var errorMessage = 'Terjadi kesalahan saat memuat data';
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.error) {
                                errorMessage = response.error;
                            } else if (response.data && response.data.length > 0) {
                                // If we have data but there's still an error, it might be a parsing issue
                                console.warn('⚠️ Data received but error occurred during processing');
                                return; // Don't show error if we have data
                            }
                        } catch (e) {
                            console.warn('⚠️ Could not parse response as JSON:', e.message);
                            // Use default message if parsing fails
                        }
                        
                        // Use new showError method
                        DataPeraturanAdmin.showError(errorMessage);
                    },
                    "dataSrc": function(json) {
                        // Update CSRF token jika ada di response
                        if (json.csrf_token_name && json.csrf_token_value) {
                            window.csrf_token_name = json.csrf_token_name;
                            window.csrf_token_value = json.csrf_token_value;
                            console.log('🔑 CSRF token updated from AJAX:', window.csrf_token_name, window.csrf_token_value);
                        }
                        console.log('✅ DataTables AJAX Success:', {
                            draw: json.draw,
                            recordsTotal: json.recordsTotal,
                            recordsFiltered: json.recordsFiltered,
                            dataLength: json.data ? json.data.length : 0
                        });
                        // Return the data array for DataTables to render
                        return json.data;
                    }
                },
                "order": [[3, "desc"]], // Sort by year descending
                "columnDefs": [
                    {
                        "targets": [0, 8], // No, Aksi columns
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "targets": [1, 2, 3, 4, 5, 6], // Sortable columns
                        "orderable": true,
                        "searchable": true
                    },
                    {
                        "targets": [7], // File column - Enable sorting berdasarkan file ada/hilang
                        "orderable": true,
                        "searchable": false,
                        "orderDataType": "file-exists", // Custom sorting type
                        "type": "html", // Type HTML untuk parsing
                        "render": function(data, type, row, meta) {
                            // Untuk display, return HTML asli
                            if (type === 'display') {
                                return data;
                            }
                            // Untuk sorting, extract nilai
                            if (type === 'sort' || type === 'type') {
                                try {
                                    if (typeof data === 'string') {
                                        var match = data.match(/data-file-exists\s*=\s*["'](\d)["']/i);
                                        if (match && match[1]) {
                                            return parseInt(match[1]);
                                        }
                                        if (data.indexOf('File hilang') !== -1 || data.indexOf('Tidak ada file') !== -1) {
                                            return 0;
                                        }
                                        if (data.indexOf('btn-success') !== -1) {
                                            return 1;
                                        }
                                    }
                                    return 0;
                                } catch (e) {
                                    return 0;
                                }
                            }
                            return data;
                        }
                    },
                    {
                        "targets": [8], // Aksi column (HTML)
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row, meta) {
                            return data;
                        }
                    }
                ],
                "language": {
                    "processing": "Memuat data...",
                    "loadingRecords": "Memuat...",
                    "emptyTable": "Tidak ada data yang tersedia",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                    "infoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ entri",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
                "drawCallback": function(settings) {
                    // Dynamic row numbering
                    var api = this.api();
                    var start = api.page.info().start;
                    
                    api.column(0, { page: 'current' }).nodes().each(function(cell, i) {
                        if (cell) {
                            cell.innerHTML = start + i + 1;
                        }
                    });
                },
                "initComplete": function(settings, json) {
                    console.log('✅ Data Peraturan DataTable initialized successfully');
                    console.log('📊 Paging info:', {
                        pageLength: settings._iDisplayLength,
                        totalRecords: json.recordsTotal,
                        filteredRecords: json.recordsFiltered
                    });
                    
                    // Add export buttons if DataTables Buttons extension is available
                    if (typeof this.api().buttons === 'function') {
                        var buttons = this.api().buttons().container();
                        if (buttons.length) {
                            buttons.appendTo($(tableSelector + '_wrapper .col-md-6:eq(0)'));
                        }
                    }
                    
                    // Sorting untuk kolom File sekarang dilakukan di server-side
                    // Tidak perlu custom handler karena server sudah handle sorting
                    console.log('✅ File column sorting handled by server-side');
                }
            });
            
            // Store reference for potential future use
            window.dataPeraturanTable = dataTable;
            
            return dataTable;
        },
        
        // Initialize all data-peraturan features
        init: function() {
            console.log('🚀 Data Peraturan Admin: Initializing...');
            
            // Wait a bit to ensure DOM is fully ready and other scripts have loaded
            setTimeout(function() {
                // Initialize DataTable if element exists
                if ($('#data-tables').length) {
                    console.log('📋 Found data-tables element, initializing DataTable...');
                    DataPeraturanAdmin.initDataTable('#data-tables');
                    
                    // Auto-refresh DataTable if coming from delete action
                    var urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.get('refresh') === '1') {
                        console.log('🔄 Auto-refreshing DataTable after delete action...');
                        setTimeout(function() {
                            if (window.dataPeraturanTable) {
                                window.dataPeraturanTable.ajax.reload();
                            }
                        }, 500);
                    }
                } else {
                    console.warn('⚠️ data-tables element not found');
                }
                
                // Initialize other features
                DataPeraturanAdmin.initEventHandlers();
                DataPeraturanAdmin.initFormValidation();
            }, 100);
        },
        
        // Initialize event handlers
        initEventHandlers: function() {
            // Delete confirmation for data-action buttons
            $(document).on('click', '[data-action="delete-data"]', function(e) {
                e.preventDefault();
                var $this = $(this);
                var deleteTitle = $this.attr('data-delete-title') || 'Apakah Anda yakin ingin menghapus data ini?';
                var dataId = $this.attr('data-id');
                
                if (typeof bootbox !== 'undefined') {
                    bootbox.confirm({
                        message: deleteTitle,
                        callback: function(confirmed) {
                            if (confirmed) {
                                // Use AJAX request instead of form submit
                                DataPeraturanAdmin.deletePeraturan(dataId);
                            }
                        },
                        centerVertical: true
                    });
                } else {
                    if (confirm(deleteTitle)) {
                        // Use AJAX request instead of form submit
                        DataPeraturanAdmin.deletePeraturan(dataId);
                    }
                }
            });
            
            // Delete confirmation for direct form submit buttons (for compatibility)
            $(document).on('click', 'form[action*="delete"] button[type="submit"]', function(e) {
                var $form = $(this).closest('form');
                var deleteTitle = $(this).attr('data-delete-title') || 'Apakah Anda yakin ingin menghapus data ini?';
                
                if (typeof bootbox !== 'undefined') {
                    e.preventDefault();
                    bootbox.confirm({
                        message: deleteTitle,
                        callback: function(confirmed) {
                            if (confirmed) {
                                $form.submit();
                            }
                        },
                        centerVertical: true
                    });
                }
                // If no bootbox, let the native confirm() handle it
            });
            
            // Reload table button
            $(document).on('click', '[data-action="reload-table"]', function(e) {
                e.preventDefault();
                if (window.dataPeraturanTable) {
                    window.dataPeraturanTable.ajax.reload();
                }
            });
        },
        
        // Initialize form validation
        initFormValidation: function() {
            // Number only inputs
            $('.number-only').on('keyup', function() {
                this.value = this.value.replace(/\D/g, '');
            });
            
            // Year validation
            $('input[name="tahun"]').on('blur', function() {
                var year = parseInt($(this).val());
                var currentYear = new Date().getFullYear();
                
                if (year < 1900 || year > currentYear + 1) {
                    $(this).addClass('is-invalid');
                    if (!$(this).next('.invalid-feedback').length) {
                        $(this).after('<div class="invalid-feedback">Tahun harus antara 1900 dan ' + (currentYear + 1) + '</div>');
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).next('.invalid-feedback').remove();
                }
            });
        },
        
        // Utility functions
        utils: {
            // Format date
            formatDate: function(dateString) {
                if (!dateString) return 'N/A';
                
                try {
                    var date = new Date(dateString);
                    if (isNaN(date.getTime())) return 'N/A';
                    
                    return date.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                } catch (e) {
                    return 'N/A';
                }
            },
            
            // Show notification
            showNotification: function(message, type) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: type || 'info',
                        title: type === 'success' ? 'Berhasil' : type === 'error' ? 'Error' : 'Info',
                        text: message,
                        timer: type === 'success' ? 3000 : null,
                        showConfirmButton: type !== 'success'
                    });
                } else {
                    alert(message);
                }
            },
            
            // Show error
            showError: function(message) {
                console.error('❌ Data Peraturan Error:', message);
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message,
                        confirmButtonText: 'OK',
                        allowOutsideClick: false
                    });
                } else {
                    alert('Error: ' + message);
                }
            },
            
            // Show success message to user
            showSuccess: function(message) {
                console.log('✅ Data Peraturan Success:', message);
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    alert('Berhasil: ' + message);
                }
            }
        },
        
        // Show error message to user
        showError: function(message) {
            console.error('❌ Data Peraturan Error:', message);
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
            } else {
                alert('Error: ' + message);
            }
        },
        
        // Show success message to user
        showSuccess: function(message) {
            console.log('✅ Data Peraturan Success:', message);
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: message,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                alert('Berhasil: ' + message);
            }
        },
        
        // Delete peraturan via AJAX
        deletePeraturan: function(dataId) {
            console.log('🗑️ Deleting peraturan with ID:', dataId);
            
            // Show loading indicator
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Menghapus data...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
            
            // Prepare AJAX request
            var requestData = {
                id: dataId
            };
            
            // Add CSRF token if available
            if (typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_token_value !== 'undefined') {
                requestData[window.csrf_token_name] = window.csrf_token_value;
            }
            
            // Send AJAX request
            $.ajax({
                url: base_url + 'data_peraturan/delete_ajax',
                type: 'POST',
                data: requestData,
                dataType: 'json',
                beforeSend: function(xhr) {
                    if (window.csrf_token_value) {
                        xhr.setRequestHeader('X-CSRF-TOKEN', window.csrf_token_value);
                    }
                },
                success: function(response) {
                    console.log('✅ Delete response:', response);
                    console.log('📊 Response type:', typeof response);
                    console.log('📊 Response keys:', Object.keys(response));
                    
                    if (response.success) {
                        // Update CSRF token if provided
                        if (response.csrf_token_name && response.csrf_token_value) {
                            window.csrf_token_name = response.csrf_token_name;
                            window.csrf_token_value = response.csrf_token_value;
                            console.log('🔑 CSRF token updated after delete:', window.csrf_token_name, window.csrf_token_value);
                        }
                        
                        // Paksa tutup semua modal Bootbox jika ada
                        if (typeof bootbox !== 'undefined') {
                            bootbox.hideAll();
                        }
                        // Show success message
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            alert('Berhasil: ' + response.message);
                        }
                        
                        // Paksa reload DataTable dengan cara paling kompatibel
                        setTimeout(function() {
                            if (window.dataPeraturanTable && typeof window.dataPeraturanTable.ajax !== 'undefined') {
                                window.dataPeraturanTable.ajax.reload(null, false);
                                console.log('✅ DataTable reloaded via window.dataPeraturanTable');
                            } else if ($.fn.DataTable.isDataTable('#data-tables')) {
                                $('#data-tables').DataTable().ajax.reload(null, false);
                                console.log('✅ DataTable reloaded via jQuery selector');
                            } else {
                                location.reload();
                                console.warn('⚠️ DataTable instance not found, fallback to full reload');
                            }
                        }, 500);
                    } else {
                        // Show error message
                        console.error('❌ Server returned error:', response.message);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('Error: ' + response.message);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Delete AJAX error:', error);
                    console.error('Response:', xhr.responseText);
                    
                    var errorMessage = 'Terjadi kesalahan saat menghapus data';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.warn('Could not parse error response');
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('Error: ' + errorMessage);
                    }
                }
            });
        }
    };
    
    // Auto-initialize when DOM is ready
    $(document).ready(function() {
        // Only initialize if we're on a data-peraturan page and haven't initialized yet
        if ($('#data-tables').length && $('body').hasClass('data-peraturan-page')) {
            // Prevent multiple initializations
            if (window.dataPeraturanInitialized) {
                console.log('📋 Data Peraturan already initialized, skipping...');
                return;
            }
            
            console.log('📋 Data Peraturan page detected, starting initialization...');
            window.dataPeraturanInitialized = true;
            DataPeraturanAdmin.init();
        }
    });
    
})(jQuery); 