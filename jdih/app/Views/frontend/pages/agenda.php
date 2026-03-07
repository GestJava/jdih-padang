<?= $this->extend('layouts/frontend') ?>

<?= $this->section('styles') ?>
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<!-- CSS untuk FullCalendar dan agenda list telah dipindahkan ke jdih-custom.css -->
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- FullCalendar dengan cache busting untuk memastikan library ter-load fresh -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js?v=<?= time() ?>'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js?v=<?= time() ?>'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($isCalendarView) && $isCalendarView): ?>
            var calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                // Pastikan events_json selalu ter-set, default ke array kosong
                var calendarEvents = <?= isset($events_json) ? $events_json : '[]' ?>;
                
                // Debug: Log events untuk troubleshooting
                console.log('Calendar events loaded:', calendarEvents);
                console.log('Calendar events count:', calendarEvents ? calendarEvents.length : 0);
                
                // Debug: Log jika events kosong
                if (!calendarEvents || calendarEvents.length === 0) {
                    console.warn('Calendar events is empty or not loaded. Check server logs for agenda data.');
                }

                // Cek apakah FullCalendar tersedia
                if (typeof FullCalendar === 'undefined') {
                    console.error('FullCalendar library tidak ter-load. Pastikan CDN tersedia.');
                    calendarEl.innerHTML = '<div class="alert alert-danger">Error: FullCalendar library tidak ter-load. Silakan refresh halaman.</div>';
                    return;
                }
                
                try {
                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        locale: 'id',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,listWeek'
                        },
                        events: calendarEvents,
                        eventTimeFormat: {
                            hour: '2-digit',
                            minute: '2-digit',
                            meridiem: false,
                            hour12: false
                        },
                        eventClick: function(info) {
                            info.jsEvent.preventDefault();
                            if (info.event.url) {
                                window.location.href = info.event.url; // Arahkan ke URL detail agenda
                            }
                        },
                        eventMouseEnter: function(info) {
                            let tooltipText = info.event.title;
                            if (info.event.extendedProps.location && info.event.extendedProps.location !== '-') {
                                tooltipText += '\\nLokasi: ' + info.event.extendedProps.location;
                            }
                            // Anda bisa menambahkan deskripsi singkat jika ada di extendedProps
                            // if (info.event.extendedProps.description) {
                            //     tooltipText += '\\nDeskripsi: ' + info.event.extendedProps.description;
                            // }
                            info.el.title = tooltipText; // Tooltip sederhana bawaan browser
                        },
                        // Menambahkan parameter untuk filter bulan dan tahun saat navigasi kalender
                        datesSet: function(dateInfo) {
                            // Ambil tahun dan bulan dari tampilan kalender saat ini
                            let currentYear = dateInfo.view.currentStart.getFullYear();
                            let currentMonth = String(dateInfo.view.currentStart.getMonth() + 1).padStart(2, '0');

                            // Update URL filter jika tombol navigasi kalender ditekan
                            // Ini akan berguna jika Anda ingin filter di URL tetap sinkron dengan tampilan kalender
                            // Namun, untuk saat ini, filter utama dikontrol oleh form.
                            // Jika ingin filter form mengupdate kalender, form harus submit dan reload halaman.
                            // Jika ingin navigasi kalender mengupdate form, perlu JS tambahan.
                        }
                    });
                    calendar.render();
                    console.log('Calendar rendered successfully with', calendarEvents.length, 'events');
                } catch (error) {
                    console.error('Error initializing FullCalendar:', error);
                    calendarEl.innerHTML = '<div class="alert alert-danger">Error: Gagal memuat kalender. ' + error.message + '</div>';
                }
            } else {
                console.error('Calendar element (#calendar) tidak ditemukan di halaman');
            }
        <?php endif; ?>
    });
    
    // Pastikan form filter berfungsi dengan benar
    // Tambahkan event listener untuk form submission
    document.addEventListener('DOMContentLoaded', function() {
        var filterForm = document.getElementById('agendaFilterForm');
        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                // Form akan submit normal, tidak perlu preventDefault
                // Pastikan view parameter tetap ada jika sedang di calendar view
                var viewInput = filterForm.querySelector('input[name="view"]');
                var urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('view') === 'calendar' && !viewInput) {
                    var hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'view';
                    hiddenInput.value = 'calendar';
                    filterForm.appendChild(hiddenInput);
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?= $this->include('frontend/components/hero', [
    'title' => 'Agenda Kegiatan',
    'subtitle' => 'Ikuti berbagai kegiatan dan acara yang diselenggarakan',
    'icon' => 'fa-calendar-alt',
    'badge' => 'Agenda Kegiatan'
]) ?>

<section class="py-5">
    <div class="container">
        <?= $this->include('frontend/components/breadcrumb', [
            'items' => [
                ['label' => 'Agenda', 'url' => '']
            ]
        ]) ?>

        <div class="row mb-4 filter-form">
            <div class="col-md-8">
                <form action="<?= base_url('agenda') ?>" method="get" class="row g-2 align-items-center" id="agendaFilterForm">
                    <?php if (isset($isCalendarView) && $isCalendarView): ?>
                        <input type="hidden" name="view" value="calendar">
                    <?php endif; ?>
                    <div class="col-md-4">
                        <select name="bulan" class="form-select form-select-sm">
                            <option value="">Semua Bulan</option>
                            <?php
                            $bulan_options = [
                                '01' => 'Januari',
                                '02' => 'Februari',
                                '03' => 'Maret',
                                '04' => 'April',
                                '05' => 'Mei',
                                '06' => 'Juni',
                                '07' => 'Juli',
                                '08' => 'Agustus',
                                '09' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember'
                            ];
                            foreach ($bulan_options as $key => $value) {
                                $selected = (isset($filterBulan) && $filterBulan == $key) ? 'selected' : '';
                                echo "<option value=\"$key\" $selected>$value</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="tahun" class="form-select form-select-sm">
                            <option value="">Semua Tahun</option>
                            <?php if (!empty($tahun_list)): ?>
                                <?php foreach ($tahun_list as $tahun_item): ?>
                                    <?php $selected = (isset($filterTahun) && $filterTahun == $tahun_item['tahun']) ? 'selected' : ''; ?>
                                    <option value="<?= esc($tahun_item['tahun']) ?>" <?= $selected ?>><?= esc($tahun_item['tahun']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0 view-toggle-buttons">
                <div class="btn-group" role="group">
                    <?php
                    $filterParams = [];
                    if (!empty($filterTahun)) $filterParams['tahun'] = $filterTahun;
                    if (!empty($filterBulan)) $filterParams['bulan'] = $filterBulan;
                    $queryString = http_build_query($filterParams);
                    ?>
                    <a href="<?= base_url('agenda') . ($queryString ? '?' . $queryString : '') ?>"
                        class="btn btn-sm <?= (!$isCalendarView) ? 'btn-primary active' : 'btn-outline-primary' ?>">
                        <i class="fas fa-list me-1"></i> Daftar
                    </a>
                    <a href="<?= base_url('agenda?view=calendar') . ($queryString ? '&' . $queryString : '') ?>"
                        class="btn btn-sm <?= ($isCalendarView) ? 'btn-primary active' : 'btn-outline-primary' ?>">
                        <i class="fas fa-calendar me-1"></i> Kalender
                    </a>
                </div>
            </div>
        </div>

        <div class="agenda-wrapper mt-4">
            <?php if (isset($isCalendarView) && $isCalendarView): ?>
                <div id="calendar">
                    <!-- Kalender akan dirender di sini oleh JavaScript -->
                </div>
                <?php if (empty($agendas) && (isset($filterBulan) || isset($filterTahun))): ?>
                    <div class="alert alert-info text-center mt-3" role="alert">
                        Tidak ada agenda kegiatan untuk filter yang dipilih pada tampilan kalender.
                    </div>
                <?php elseif (empty($agendas)): ?>
                    <div class="alert alert-info text-center mt-3" role="alert">
                        Saat ini belum ada agenda kegiatan yang tersedia untuk ditampilkan di kalender.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- List View (Default) -->
                <div class="row">
                    <?php if (!empty($agendas) && is_array($agendas)): ?>
                        <?php foreach ($agendas as $item): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card shadow-sm agenda-card h-100">
                                    <div class="agenda-date bg-primary text-white text-center p-2 position-relative" style="padding-top:2.2rem;">
                                        <?php
                                        try {
                                            $tanggal_mulai_obj = new DateTime($item['tanggal_mulai']);
                                            $formatterBulan = new IntlDateFormatter('id_ID', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'MMMM');
                                            $bulan_nama = ucfirst($formatterBulan->format($tanggal_mulai_obj));
                                            $tahun_item = $tanggal_mulai_obj->format('Y');
                                            $hari_item = $tanggal_mulai_obj->format('d');
                                            $is_akan_datang = $tanggal_mulai_obj > new DateTime('today');
                                        } catch (Exception $e) {
                                            $hari_item = '-';
                                            $bulan_nama = 'Invalid date';
                                            $tahun_item = '';
                                            $is_akan_datang = false;
                                        }
                                        ?>
                                        <?php if ($is_akan_datang): ?>
                                            <span class="badge badge-status position-absolute top-0 start-50 translate-middle-x mt-2" style="z-index:2; background:#22c55e; color:#fff; font-weight:600; font-size:0.85rem; border-radius:8px; padding:4px 12px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                                                Akan Datang
                                            </span>
                                        <?php endif; ?>
                                        <div class="agenda-day" style="font-size:2rem; font-weight:bold;"><?= esc($hari_item) ?></div>
                                        <div class="agenda-month"><?= esc($bulan_nama) ?> <?= esc($tahun_item) ?></div>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title agenda-title"><?= esc(mb_convert_case($item['judul_agenda'], MB_CASE_TITLE, "UTF-8")) ?></h5>
                                        <p class="card-text agenda-desc flex-grow-1">
                                            <?= esc(strip_tags(substr($item['deskripsi_singkat'] ?? '', 0, 100))) . (strlen($item['deskripsi_singkat'] ?? '') > 100 ? '...' : '') ?>
                                        </p>
                                        <div class="agenda-meta mt-2">
                                            <?php if (!empty($item['lokasi'])): ?>
                                                <div class="meta-item mb-1 text-muted small"><i class="fas fa-map-marker-alt me-1"></i> <?= esc(mb_convert_case($item['lokasi'], MB_CASE_TITLE, "UTF-8")) ?></div>
                                            <?php endif; ?>
                                            <?php
                                            $waktu_display = '';
                                            if (!empty($item['waktu_mulai'])) {
                                                $waktu_display = substr($item['waktu_mulai'], 0, 5);
                                                if (!empty($item['waktu_selesai'])) {
                                                    $waktu_display .= ' - ' . substr($item['waktu_selesai'], 0, 5);
                                                }
                                                $waktu_display .= ' WIB';
                                            }
                                            ?>
                                            <?php if (!empty($waktu_display)): ?>
                                                <div class="meta-item mb-1 text-muted small"><i class="fas fa-clock me-1"></i> <?= esc($waktu_display) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['target_peserta'])): ?>
                                                <div class="meta-item mb-1 text-muted small"><i class="fas fa-user me-1"></i> Peserta: <?= esc(mb_convert_case($item['target_peserta'], MB_CASE_TITLE, "UTF-8")) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?= base_url('agenda/' . esc($item['slug'])) ?>" class="btn btn-sm btn-outline-primary mt-3 align-self-start">Detail Acara</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center" role="alert">
                                <?php if (!empty($filterBulan) || !empty($filterTahun)): ?>
                                    Tidak ada agenda kegiatan yang cocok dengan kriteria filter Anda.
                                <?php else: ?>
                                    Saat ini belum ada agenda kegiatan yang tersedia.
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
                    <div class="row mt-4">
                        <div class="col-12 d-flex justify-content-center">
                            <?= $pager->links('agenda', 'default_full') ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>