/**
 * Harmonisasi Admin JavaScript Functions
 * Centralized functions for all harmonisasi admin pages
 */

// DataTable initialization with dynamic row numbering
function initHarmonisasiDataTable(tableId, options = {}) {
    if (!$.fn.DataTable || !$(tableId).length) {
        console.warn('DataTable not available or table not found:', tableId);
        return null;
    }

    // Destroy existing instance if any (with error handling)
    try {
        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().destroy();
        }
    } catch (e) {
        console.warn('Error destroying existing DataTable:', e);
        // Remove any existing DataTable classes and data
        $(tableId).removeClass('dataTable').removeData();
    }

    // Default options
    var defaultOptions = {
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        order: [[4, 'desc']], // Sort by date column (index 4)
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1; // auto rownum
                }
            },
            {
                targets: 5, // status column (id status)
                className: 'text-center',
                render: function (data, type, row) {
                    var cls = window.HarmonisasiAdmin.getStatusColorClass ? window.HarmonisasiAdmin.getStatusColorClass(parseInt(data)) : 'bg-secondary';
                    var txt = '';
                    switch (parseInt(data)) {
                        case 1: txt = 'Draft'; break;
                        case 2: txt = 'Diajukan'; break;
                        case 3: txt = 'Verifikasi'; break;
                        case 4: txt = 'Validasi'; break;
                        case 5: txt = 'Revisi'; break;
                        case 6: txt = 'Finalisasi'; break;
                        case 7: txt = 'Paraf OPD'; break;
                        case 8: txt = 'Paraf Kabag'; break;
                        case 9: txt = 'Paraf Asisten'; break;
                        case 10: txt = 'Revisi ke Finalisasi'; break;
                        case 11: txt = 'Paraf Sekda'; break;
                        case 12: txt = 'Paraf Wawako'; break;
                        case 13: txt = 'TTE Walikota'; break;
                        case 14: txt = 'Selesai'; break;
                        case 15: txt = 'Ditolak'; break;
                        default: txt = 'Status';
                    }
                    return '<span class="badge ' + cls + '">' + txt + '</span>';
                }
            },
            {
                targets: 6, // actions
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    var id = data;
                    var statusId = row[5]; // Status ID dari kolom status (index 5)
                    var baseUrl = (window.JDIH_CONFIG ? window.JDIH_CONFIG.base_url : '/');
                    var detailUrl = baseUrl + 'harmonisasi/show/' + id;

                    var actions = '<a href="' + detailUrl + '" class="btn btn-outline-info btn-sm" title="Detail"><i class="fas fa-eye"></i></a>';

                    // Tambahkan tombol revisi jika status = 5 (Revisi)
                    if (statusId == 5) {
                        var revisiUrl = baseUrl + 'harmonisasi/showRevisiForm/' + id;
                        actions += ' <a href="' + revisiUrl + '" class="btn btn-outline-warning btn-sm" title="Revisi"><i class="fas fa-edit"></i></a>';
                    }

                    return actions.replace(/\\"/g, '"');
                }
            }
        ],
        pageLength: 25,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy me-1"></i>Copy',
                className: 'btn btn-secondary btn-sm'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel me-1"></i>Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf me-1"></i>PDF',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print me-1"></i>Print',
                className: 'btn btn-info btn-sm'
            }
        ],
        drawCallback: function (settings) {
            // Update row numbers after sorting/filtering/pagination
            var api = this.api();
            var pageInfo = api.page.info();

            api.rows({ page: 'current' }).nodes().each(function (cell, i) {
                if (cell.cells && cell.cells[0]) {
                    cell.cells[0].innerHTML = pageInfo.start + i + 1;
                }
            });
        }
    };

    // Merge custom options with defaults
    var finalOptions = $.extend(true, {}, defaultOptions, options);

    // Initialize DataTable
    var table = $(tableId).DataTable(finalOptions);

    console.log('DataTable initialized for:', tableId);
    return table;
}

// Enhanced DataTable for harmonisasi main page (with custom buttons)
function initHarmonisasiMainDataTable(tableId, options = {}) {
    if (!$.fn.DataTable || !$(tableId).length) {
        console.warn('DataTable not available or table not found:', tableId);
        return null;
    }

    // Destroy existing instance if any (with error handling)
    try {
        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().destroy();
        }
    } catch (e) {
        console.warn('Error destroying existing DataTable:', e);
        // Remove any existing DataTable classes and data
        $(tableId).removeClass('dataTable').removeData();
    }

    // Default options for main harmonisasi page
    var defaultOptions = {
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        order: [[4, 'desc']], // Sort by date column
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1; // auto rownum
                }
            },
            {
                targets: 5, // status column (id status)
                className: 'text-center',
                render: function (data, type, row) {
                    var cls = window.HarmonisasiAdmin.getStatusColorClass ? window.HarmonisasiAdmin.getStatusColorClass(parseInt(data)) : 'bg-secondary';
                    var txt = '';
                    switch (parseInt(data)) {
                        case 1: txt = 'Draft'; break;
                        case 2: txt = 'Diajukan'; break;
                        case 3: txt = 'Verifikasi'; break;
                        case 4: txt = 'Validasi'; break;
                        case 5: txt = 'Revisi'; break;
                        case 6: txt = 'Finalisasi'; break;
                        case 7: txt = 'Paraf OPD'; break;
                        case 8: txt = 'Paraf Kabag'; break;
                        case 9: txt = 'Paraf Asisten'; break;
                        case 10: txt = 'Revisi ke Finalisasi'; break;
                        case 11: txt = 'Paraf Sekda'; break;
                        case 12: txt = 'Paraf Wawako'; break;
                        case 13: txt = 'TTE Walikota'; break;
                        case 14: txt = 'Selesai'; break;
                        case 15: txt = 'Ditolak'; break;
                        default: txt = 'Status';
                    }
                    return '<span class="badge ' + cls + '">' + txt + '</span>';
                }
            },
            {
                targets: 6, // actions
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    var id = data;
                    var statusId = row[5]; // Status ID dari kolom status (index 5)
                    var baseUrl = (window.JDIH_CONFIG ? window.JDIH_CONFIG.base_url : '/');
                    var detailUrl = baseUrl + 'harmonisasi/show/' + id;

                    var actions = '<a href="' + detailUrl + '" class="btn btn-outline-info btn-sm" title="Detail"><i class="fas fa-eye"></i></a>';

                    // Tambahkan tombol revisi jika status = 5 (Revisi)
                    if (statusId == 5) {
                        var revisiUrl = baseUrl + 'harmonisasi/showRevisiForm/' + id;
                        actions += ' <a href="' + revisiUrl + '" class="btn btn-outline-warning btn-sm" title="Revisi"><i class="fas fa-edit"></i></a>';
                    }

                    return actions.replace(/\\"/g, '"');
                }
            }
        ],
        pageLength: 10,            // tampilkan 10 baris per halaman
        deferRender: true,         // render baris saat diperlukan saja
        dom: 'lfrtip', // simple dom: length + filter + table + info + paging
        buttons: []
    };

    // Merge custom options with defaults
    var finalOptions = $.extend(true, {}, defaultOptions, options);

    // Initialize DataTable
    var table = null;
    try {
        table = $(tableId).DataTable(finalOptions);
        console.log('Main DataTable initialized for:', tableId);
    } catch (e) {
        console.warn('DataTable initialization failed, retrying without Buttons extension. Error:', e);

        // Remove Buttons and dom option then retry
        var fallbackOptions = $.extend(true, {}, finalOptions);
        fallbackOptions.dom = 'lfrtip'; // simple dom: length + filter + table + info + paging
        fallbackOptions.buttons = [];

        try {
            table = $(tableId).DataTable(fallbackOptions);
            console.log('Main DataTable initialized WITHOUT buttons for:', tableId);
        } catch (e2) {
            console.error('Fallback DataTable initialization still failed:', e2);
        }
    }

    return table;
}

// Confirmation dialog for submit actions
function confirmSubmit(message = 'Apakah Anda yakin ingin melanjutkan?') {
    return confirm(message);
}

// Enhanced confirmation for harmonisasi submit
function confirmHarmonisasiSubmit() {
    return confirm('Apakah Anda yakin ingin mengajukan draft ini? Proses ini tidak dapat dibatalkan.');
}

// Utility function to format dates
function formatDate(dateString) {
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
}

// Utility function to get status color class
function getStatusColorClass(statusId) {
    var statusColors = {
        1: 'bg-secondary text-white',      // Draft
        2: 'bg-warning text-dark',         // Diajukan ke Kabag
        3: 'bg-info text-white',           // Ditugaskan ke Verifikator
        4: 'bg-danger text-white',         // Proses Validasi
        5: 'bg-primary text-white',        // Revisi
        6: 'bg-danger text-white',         // Proses Finalisasi
        7: 'bg-warning text-dark',         // Menunggu Paraf OPD
        8: 'bg-warning text-dark',         // Menunggu Paraf Kabag
        9: 'bg-warning text-dark',         // Menunggu Paraf Asisten
        10: 'bg-warning text-dark',        // Revisi ke Finalisasi
        11: 'bg-info text-white',         // Menunggu Paraf/TTE Sekda
        12: 'bg-info text-white',         // Menunggu Paraf Wawako
        13: 'bg-info text-white',         // Menunggu TTE Walikota
        14: 'bg-success text-white',       // SELESAI
        15: 'bg-danger text-white'         // Ditolak
    };

    return statusColors[statusId] || 'bg-secondary text-white';
}

// Auto-initialize DataTables when document is ready
$(document).ready(function () {
    // Skip automatic initialization if complete script is present
    if (window.JDIHModules && window.JDIHModules.Harmonisasi) {
        console.log('📋 harmonisasi-complete.js detected, HarmonisasiAdmin will skip DataTable auto-init');
        return;
    }

    // Skip automatic initialization on Data Peraturan page to avoid conflict
    if ($('body').hasClass('data-peraturan-page')) {
        console.log('📋 Data Peraturan page detected, HarmonisasiAdmin will skip DataTable auto-init');
        return;
    }

    // Skip automatic initialization on Penugasan page to avoid duplicate buttons
    if (window.location.pathname.indexOf('/penugasan') !== -1) {
        console.log('📋 Penugasan page detected, HarmonisasiAdmin will skip DataTable auto-init');
        return;
    }

    // Skip automatic initialization on Verifikasi page to avoid duplicate buttons
    if (window.location.pathname.indexOf('/verifikasi') !== -1) {
        console.log('📋 Verifikasi page detected, HarmonisasiAdmin will skip DataTable auto-init');
        return;
    }

    // Skip automatic initialization on Validasi, Finalisasi, and Paraf pages
    var skipPages = ['/validasi', '/finalisasi', '/paraf'];
    for (var i = 0; i < skipPages.length; i++) {
        if (window.location.pathname.indexOf(skipPages[i]) !== -1) {
            console.log('📋 ' + skipPages[i] + ' page detected, HarmonisasiAdmin will skip DataTable auto-init');
            return;
        }
    }

    /* ---------------------------------------------------------------------
     * MAIN HARMONISASI DASHBOARD TABLE (id = harmonisasi-table)
     * ------------------------------------------------------------------- */
    if ($('#harmonisasi-table').length) {
        // Prevent double-initialization
        if (!$.fn.DataTable.isDataTable('#harmonisasi-table')) {
            var csrfName = window.JDIH_CONFIG ? window.JDIH_CONFIG.csrf_name : 'csrf_token';
            var csrfHash = window.JDIH_CONFIG ? window.JDIH_CONFIG.csrf_token : '';
            var sspOptions = {
                processing: true,
                serverSide: true,
                ajax: {
                    url: (window.JDIH_CONFIG ? window.JDIH_CONFIG.base_url : '/') + 'ajax/harmonisasi',
                    type: 'POST',
                    xhrFields: {
                        withCredentials: true // Kirim cookies dengan request
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // Identifikasi sebagai AJAX request
                    },
                    data: function (d) {
                        d[csrfName] = csrfHash; // tambahkan CSRF token
                    },
                    error: function (xhr, error, thrown) {
                        console.error('DataTables AJAX Error:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            error: error,
                            thrown: thrown,
                            url: (window.JDIH_CONFIG ? window.JDIH_CONFIG.base_url : '/') + 'ajax/harmonisasi'
                        });

                        // Show user-friendly error message
                        var errorMsg = 'Terjadi kesalahan saat memuat data. ';
                        if (xhr.status === 404) {
                            errorMsg += 'Endpoint tidak ditemukan.';
                        } else if (xhr.status === 403) {
                            errorMsg += 'Akses ditolak.';
                        } else if (xhr.status === 500) {
                            errorMsg += 'Kesalahan server.';
                        } else if (xhr.status === 0) {
                            errorMsg += 'Tidak dapat terhubung ke server.';
                        } else {
                            errorMsg += 'Status: ' + xhr.status + ' - ' + xhr.statusText;
                        }

                        alert(errorMsg + '\n\nSilakan cek console untuk detail lebih lanjut.');
                    }
                }
            };

            initHarmonisasiMainDataTable('#harmonisasi-table', sspOptions);
        } else {
            console.log('DataTable already initialized for #harmonisasi-table');
        }

        // Update CSRF token setelah setiap ajax draw
        $('#harmonisasi-table').on('xhr.dt', function (e, settings, json) {
            if (json && json.csrf_token) {
                csrfHash = json.csrf_token;
                if (window.JDIH_CONFIG) { window.JDIH_CONFIG.csrf_token = csrfHash; }
            }
        });
    }

    /* ---------------------------------------------------------------------
     * GENERIC TABLE (id = data-tables)
     * ------------------------------------------------------------------- */
    if ($('#data-tables').length) {
        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('#data-tables')) {
            console.log('DataTable already initialized for #data-tables');
            return;
        }

        // Check if it's the main harmonisasi page (has different buttons)
        var isMainPage = $('#data-tables').closest('.harmonisasi-module').length > 0;

        if (isMainPage) {
            initHarmonisasiMainDataTable('#data-tables');
        } else {
            initHarmonisasiDataTable('#data-tables');
        }
    }

    // Add loading states to buttons
    $('.btn').on('click', function () {
        var $btn = $(this);
        if (!$btn.hasClass('btn-loading')) {
            $btn.addClass('btn-loading');
            setTimeout(function () {
                $btn.removeClass('btn-loading');
            }, 2000);
        }
    });
});

// Export functions for use in other scripts
window.HarmonisasiAdmin = {
    initDataTable: initHarmonisasiDataTable,
    initMainDataTable: initHarmonisasiMainDataTable,
    confirmSubmit: confirmSubmit,
    confirmHarmonisasiSubmit: confirmHarmonisasiSubmit,
    formatDate: formatDate,
    getStatusColorClass: getStatusColorClass
}; 