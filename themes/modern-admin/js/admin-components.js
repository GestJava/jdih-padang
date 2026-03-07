/**
 * JDIH Admin Modern - Components JavaScript
 * Component-specific functionality for admin panel
 * 
 * @author AI Assistant
 * @version 1.0
 */

const AdminComponents = {
    // Initialize all components
    init() {
        this.initStatsCards();
        this.initActivityFeed();
        this.initDataTables();
        this.initFileUpload();
        this.initRichTextEditor();
        this.initDatePickers();
        this.initSelect2();
        this.initColorPickers();
        this.initImageCropper();
        this.initCharts();
    },

    // Stats cards with animations
    initStatsCards() {
        const statCards = document.querySelectorAll('.stat-card');
        
        statCards.forEach(card => {
            const valueEl = card.querySelector('.stat-value');
            if (valueEl) {
                const finalValue = parseInt(valueEl.textContent.replace(/,/g, ''));
                this.animateNumber(valueEl, 0, finalValue, 1000);
            }
        });
    },

    animateNumber(element, start, end, duration) {
        const startTime = performance.now();
        const difference = end - start;

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const current = Math.floor(start + (difference * progress));
            element.textContent = AdminModern.formatNumber(current);

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        requestAnimationFrame(animate);
    },

    // Activity feed with real-time updates
    initActivityFeed() {
        const activityList = document.querySelector('.activity-list');
        if (!activityList) return;

        // Auto-refresh activity feed every 30 seconds
        setInterval(() => {
            this.refreshActivityFeed();
        }, 30000);
    },

    async refreshActivityFeed() {
        try {
            const response = await AdminModern.ajax(`${AdminModern.config.baseUrl}admin-modern/activity-feed`);
            const activityList = document.querySelector('.activity-list');
            
            if (activityList && response.html) {
                activityList.innerHTML = response.html;
            }
        } catch (error) {
            console.error('Failed to refresh activity feed:', error);
        }
    },

    // Enhanced DataTables
    initDataTables() {
        if (typeof $.fn.DataTable === 'undefined') return;

        $('.table-modern').each(function() {
            const table = $(this);
            const options = {
                responsive: true,
                language: {
                    url: `${AdminModern.config.baseUrl}vendors/datatables/Indonesian.json`
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                pageLength: 25,
                order: [[0, 'desc']],
                columnDefs: [
                    {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }
                ]
            };

            // Add export buttons if specified
            if (table.data('export')) {
                options.dom = '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                             '<"row"<"col-sm-12"B>>' +
                             '<"row"<"col-sm-12"tr>>' +
                             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>';
                
                options.buttons = [
                    {
                        extend: 'copy',
                        text: '<i class="bi bi-clipboard"></i> Salin',
                        className: 'btn btn-outline-secondary btn-sm'
                    },
                    {
                        extend: 'csv',
                        text: '<i class="bi bi-file-earmark-text"></i> CSV',
                        className: 'btn btn-outline-secondary btn-sm'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
                        className: 'btn btn-outline-secondary btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        className: 'btn btn-outline-secondary btn-sm'
                    }
                ];
            }

            table.DataTable(options);
        });
    },

    // File upload with drag & drop
    initFileUpload() {
        const uploadAreas = document.querySelectorAll('.file-upload-area');
        
        uploadAreas.forEach(area => {
            const input = area.querySelector('input[type="file"]');
            const preview = area.querySelector('.file-preview');
            const dropText = area.querySelector('.drop-text');

            if (!input) return;

            // Drag and drop events
            area.addEventListener('dragover', (e) => {
                e.preventDefault();
                area.classList.add('dragover');
            });

            area.addEventListener('dragleave', () => {
                area.classList.remove('dragover');
            });

            area.addEventListener('drop', (e) => {
                e.preventDefault();
                area.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    input.files = files;
                    this.handleFileSelect(input, preview, dropText);
                }
            });

            // File input change
            input.addEventListener('change', () => {
                this.handleFileSelect(input, preview, dropText);
            });
        });
    },

    handleFileSelect(input, preview, dropText) {
        const files = input.files;
        
        if (files.length === 0) return;

        const file = files[0];
        
        // Show file info
        if (dropText) {
            dropText.innerHTML = `
                <i class="bi bi-file-earmark"></i>
                <div class="file-info">
                    <div class="file-name">${file.name}</div>
                    <div class="file-size">${this.formatFileSize(file.size)}</div>
                </div>
            `;
        }

        // Show preview for images
        if (file.type.startsWith('image/') && preview) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="file-preview-img">`;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    },

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    // Rich text editor (TinyMCE)
    initRichTextEditor() {
        if (typeof tinymce === 'undefined') return;

        const editors = document.querySelectorAll('.rich-text-editor');
        
        editors.forEach(editor => {
            tinymce.init({
                selector: `#${editor.id}`,
                height: 400,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | formatselect | bold italic backcolor | \
                         alignleft aligncenter alignright alignjustify | \
                         bullist numlist outdent indent | removeformat | help',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; font-size: 14px; }',
                language: 'id',
                language_url: `${AdminModern.config.baseUrl}vendors/tinymce/langs/id.js`
            });
        });
    },

    // Date pickers
    initDatePickers() {
        if (typeof flatpickr === 'undefined') return;

        const dateInputs = document.querySelectorAll('.date-picker');
        
        dateInputs.forEach(input => {
            flatpickr(input, {
                dateFormat: 'd/m/Y',
                locale: 'id',
                allowInput: true,
                clickOpens: true
            });
        });

        const datetimeInputs = document.querySelectorAll('.datetime-picker');
        
        datetimeInputs.forEach(input => {
            flatpickr(input, {
                dateFormat: 'd/m/Y H:i',
                enableTime: true,
                time_24hr: true,
                locale: 'id',
                allowInput: true,
                clickOpens: true
            });
        });
    },

    // Select2 dropdowns
    initSelect2() {
        if (typeof $.fn.select2 === 'undefined') return;

        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            language: 'id'
        });

        // AJAX select2
        $('.select2-ajax').each(function() {
            const $select = $(this);
            const url = $select.data('url');
            
            if (url) {
                $select.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    language: 'id',
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                search: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            
                            return {
                                results: data.items,
                                pagination: {
                                    more: data.pagination && data.pagination.more
                                }
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 2,
                    placeholder: $select.data('placeholder') || 'Pilih...'
                });
            }
        });
    },

    // Color pickers
    initColorPickers() {
        if (typeof $.fn.spectrum === 'undefined') return;

        $('.color-picker').spectrum({
            type: 'component',
            showInput: true,
            showInitial: true,
            showPalette: true,
            showSelectionPalette: true,
            maxPaletteSize: 10,
            preferredFormat: 'hex',
            palette: [
                ['#2563eb', '#1d4ed8', '#1e40af'],
                ['#059669', '#047857', '#065f46'],
                ['#d97706', '#b45309', '#92400e'],
                ['#dc2626', '#b91c1c', '#991b1b'],
                ['#64748b', '#475569', '#334155']
            ]
        });
    },

    // Image cropper
    initImageCropper() {
        if (typeof Cropper === 'undefined') return;

        const cropImages = document.querySelectorAll('.crop-image');
        
        cropImages.forEach(img => {
            const cropper = new Cropper(img, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false
            });

            // Add crop button functionality
            const cropBtn = img.parentNode.querySelector('.crop-btn');
            if (cropBtn) {
                cropBtn.addEventListener('click', () => {
                    const canvas = cropper.getCroppedCanvas();
                    const croppedImage = canvas.toDataURL('image/jpeg');
                    
                    // Update preview
                    const preview = img.parentNode.querySelector('.cropped-preview');
                    if (preview) {
                        preview.src = croppedImage;
                        preview.style.display = 'block';
                    }
                    
                    // Update hidden input
                    const input = img.parentNode.querySelector('input[type="hidden"]');
                    if (input) {
                        input.value = croppedImage;
                    }
                });
            }
        });
    },

    // Enhanced charts
    initCharts() {
        if (typeof ApexCharts === 'undefined') return;

        // Real-time charts
        const realtimeCharts = document.querySelectorAll('.realtime-chart');
        
        realtimeCharts.forEach(chartEl => {
            const chart = new ApexCharts(chartEl, {
                chart: {
                    type: 'line',
                    height: 200,
                    animations: {
                        enabled: true,
                        easing: 'linear',
                        dynamicAnimation: {
                            speed: 1000
                        }
                    },
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Data',
                    data: []
                }],
                xaxis: {
                    type: 'datetime'
                },
                yaxis: {
                    min: 0
                },
                colors: ['#2563eb'],
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
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

            chart.render();

            // Add real-time data
            setInterval(() => {
                const newData = {
                    x: new Date().getTime(),
                    y: Math.floor(Math.random() * 100)
                };

                chart.appendData([{
                    data: [newData]
                }]);
            }, 2000);
        });
    },

    // Utility functions for components
    showConfirmDialog(message, callback) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed && callback) {
                    callback();
                }
            });
        } else {
            if (confirm(message) && callback) {
                callback();
            }
        }
    },

    showSuccessDialog(message, callback) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Berhasil!',
                text: message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                if (callback) callback();
            });
        } else {
            alert(message);
            if (callback) callback();
        }
    },

    showErrorDialog(message, callback) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: message,
                icon: 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                if (callback) callback();
            });
        } else {
            alert('Error: ' + message);
            if (callback) callback();
        }
    }
};

// Initialize components when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    AdminComponents.init();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminComponents;
} 