<?php

/**
 * Agenda Cards Component
 * Usage: <?= $this->include('frontend/components/agenda-cards', ['agenda' => $agenda, 'title' => 'Agenda Kegiatan']) ?>
 */

$agenda = $agenda ?? [];
$title = $title ?? 'Agenda Kegiatan';
$subtitle = $subtitle ?? 'Informasi agenda kegiatan terkini dan yang akan datang.';
$sectionClass = $sectionClass ?? 'jdih-section jdih-agenda-terbaru my-5 bg-light';
$showViewAll = $showViewAll ?? true;
$viewAllUrl = $viewAllUrl ?? 'agenda';
$viewAllText = $viewAllText ?? 'Lihat Semua Agenda';
$maxDisplay = $maxDisplay ?? 6;
?>

<!-- Agenda Kegiatan -->
<section class="<?= esc($sectionClass) ?>">
    <div class="container">
        <div class="section-heading text-center mb-5" data-aos="fade-up">
            <h2><?= esc($title) ?></h2>
            <?php if (!empty($subtitle)): ?>
                <p><?= esc($subtitle) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($showViewAll): ?>
            <div class="d-flex justify-content-end mb-3">
                <a href="<?= base_url($viewAllUrl) ?>" class="btn btn-outline-primary btn-sm">
                    <?= esc($viewAllText) ?> &raquo;
                </a>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (!empty($agenda) && is_array($agenda)): ?>
                <?php
                $displayedCount = 0;
                foreach ($agenda as $index => $item):
                    if ($displayedCount >= $maxDisplay) break;

                    // Prepare agenda data
                    $agendaUrl = 'agenda/' . esc($item['slug'] ?? $item['id'], 'url');
                    $agendaTitle = esc($item['judul_agenda'] ?? 'Agenda Tanpa Judul');
                    $agendaLokasi = esc($item['lokasi'] ?? '-');

                    // Format tanggal dan waktu
                    try {
                        $tanggal_mulai_obj = new DateTime($item['tanggal_mulai'] ?? date('Y-m-d'));
                        $formatterBulan = new IntlDateFormatter('id_ID', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Asia/Jakarta', null, 'MMMM');
                        $bulan_nama = mb_substr(ucfirst($formatterBulan->format($tanggal_mulai_obj)), 0, 3);
                        $hari_item = $tanggal_mulai_obj->format('d');
                        $tahun_item = $tanggal_mulai_obj->format('Y');
                        $tanggal_formatted = format_tanggal_indo($item['tanggal_mulai']);
                    } catch (Exception $e) {
                        $hari_item = '-';
                        $bulan_nama = 'ERR';
                        $tahun_item = date('Y');
                        $tanggal_formatted = '-';
                    }

                    // Format waktu
                    $waktu_display = '';
                    if (!empty($item['waktu_mulai'])) {
                        $waktu_display = substr($item['waktu_mulai'], 0, 5);
                        if (!empty($item['waktu_selesai']) && $item['waktu_selesai'] != '00:00:00') {
                            $waktu_display .= ' - ' . substr($item['waktu_selesai'], 0, 5);
                        } else {
                            $waktu_display .= ' - Selesai';
                        }
                    } else {
                        $waktu_display = 'Waktu akan diinformasikan';
                    }

                    // Determine status berdasarkan tanggal
                    $currentDate = new DateTime();
                    $agendaDate = new DateTime($item['tanggal_mulai'] ?? date('Y-m-d'));
                    $status = '';
                    $statusClass = '';

                    if ($agendaDate < $currentDate) {
                        $status = 'Selesai';
                        $statusClass = 'bg-secondary';
                    } elseif ($agendaDate->format('Y-m-d') === $currentDate->format('Y-m-d')) {
                        $status = 'Hari Ini';
                        $statusClass = 'bg-warning';
                    } else {
                        $status = 'Akan Datang';
                        $statusClass = 'bg-success';
                    }

                    $displayedCount++;
                ?>
                    <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                        <div class="card shadow-sm agenda-card w-100">
                            <div class="card-body d-flex align-items-start">
                                <!-- Calendar Date -->
                                <div class="agenda-date bg-primary text-white text-center me-3 p-3 rounded d-flex flex-column justify-content-center position-relative" style="min-width: 80px;">
                                    <div class="fw-bold" style="font-size: 2rem; line-height: 1;"><?= esc($hari_item) ?></div>
                                    <div class="text-uppercase" style="font-size: 0.9rem; margin-top: -5px;"><?= esc($bulan_nama) ?></div>
                                    <div class="small" style="font-size: 0.8rem; margin-top: -3px;"><?= esc($tahun_item) ?></div>

                                    <!-- Status Badge -->
                                    <div class="position-absolute top-0 start-100 translate-middle">
                                        <span class="badge <?= esc($statusClass, 'attr') ?> px-2 py-1 small"><?= esc($status) ?></span>
                                    </div>
                                </div>

                                <!-- Agenda Info -->
                                <div class="agenda-info flex-grow-1">
                                    <h5 class="card-title mb-2" style="font-size: 1.1rem; line-height: 1.3;">
                                        <a href="<?= base_url($agendaUrl) ?>" class="text-decoration-none stretched-link text-dark agenda-title-link">
                                            <?= $agendaTitle ?>
                                        </a>
                                    </h5>

                                    <div class="agenda-details">
                                        <div class="text-muted small mb-2">
                                            <i class="fas fa-calendar-alt fa-fw me-2 text-secondary"></i>
                                            <?= esc($tanggal_formatted) ?>
                                        </div>

                                        <div class="text-muted small mb-2">
                                            <i class="fas fa-clock fa-fw me-2 text-secondary"></i>
                                            <?= esc($waktu_display) ?>
                                        </div>

                                        <div class="text-muted small mb-2">
                                            <i class="fas fa-map-marker-alt fa-fw me-2 text-secondary"></i>
                                            <?= esc($agendaLokasi) ?>
                                        </div>

                                        <?php if (!empty($item['deskripsi'])): ?>
                                            <p class="small text-muted mb-0 mt-2" style="line-height: 1.4;">
                                                <?= esc(mb_substr(strip_tags($item['deskripsi']), 0, 100)) ?>...
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="card-footer bg-transparent border-top-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i>
                                        <?= esc($item['jumlah_peserta'] ?? 'Tidak terbatas') ?>
                                    </small>
                                    <a href="<?= base_url($agendaUrl) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-info-circle me-1"></i> Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-light border text-center py-5" role="alert">
                        <i class="fas fa-calendar-check fa-3x text-primary mb-3"></i>
                        <h5 class="alert-heading">Belum Ada Agenda</h5>
                        <p class="mb-0 text-muted">Saat ini belum ada agenda kegiatan yang dijadwalkan. Silakan periksa kembali nanti.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($showViewAll && !empty($agenda)): ?>
            <div class="text-center mt-5">
                <a href="<?= base_url($viewAllUrl) ?>"
                    class="btn btn-primary">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Lihat Semua Agenda
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Enhanced Styles -->
<style>
    .agenda-card {
        transition: all 0.3s ease;
        border: none;
        overflow: hidden;
    }

    .agenda-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important;
    }

    .agenda-date {
        border-radius: 0.75rem !important;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        transition: all 0.3s ease;
    }

    .agenda-card:hover .agenda-date {
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
        transform: scale(1.05);
    }

    .agenda-title-link {
        font-weight: 600;
        transition: color 0.2s ease;
    }

    .agenda-title-link:hover {
        color: #0d6efd !important;
    }

    .agenda-details i {
        color: #6c757d !important;
    }

    .agenda-info {
        position: relative;
        z-index: 1;
    }

    .card-footer {
        background: rgba(248, 249, 250, 0.8) !important;
    }

    /* Status badges */
    .badge.bg-warning {
        background: #ffc107 !important;
        color: #000 !important;
    }

    .badge.bg-success {
        background: #198754 !important;
    }

    .badge.bg-secondary {
        background: #6c757d !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .agenda-card .card-body {
            flex-direction: column;
            text-align: center;
        }

        .agenda-date {
            margin-right: 0 !important;
            margin-bottom: 1rem;
            align-self: center;
        }

        .agenda-info {
            text-align: left;
        }
    }

    /* Loading animation */
    .agenda-card.loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .agenda-card.loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
        animation: loading-shimmer 1.5s infinite;
    }

    @keyframes loading-shimmer {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }

    /* Accessibility improvements */
    .agenda-card:focus-within {
        outline: 2px solid #0d6efd;
        outline-offset: 3px;
    }
</style>

<!-- Enhanced JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const agendaCards = document.querySelectorAll('.agenda-card');

        agendaCards.forEach(card => {
            // Add loading state on navigation
            const links = card.querySelectorAll('a[href*="agenda/"]');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    card.classList.add('loading');
                });
            });

            // Keyboard navigation
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    const titleLink = this.querySelector('.agenda-title-link');
                    if (titleLink) {
                        e.preventDefault();
                        titleLink.click();
                    }
                }
            });

            // Add accessibility attributes
            card.setAttribute('tabindex', '0');
            card.setAttribute('role', 'article');

            // Add enhanced date formatting
            const dateElement = card.querySelector('.agenda-date');
            if (dateElement) {
                const today = new Date();
                const agendaDate = new Date(card.dataset.agendaDate);

                if (agendaDate.toDateString() === today.toDateString()) {
                    dateElement.classList.add('today');
                    dateElement.style.background = 'linear-gradient(45deg, #ffc107, #ff8c00)';
                }
            }
        });

        // Add countdown for upcoming events
        const upcomingEvents = document.querySelectorAll('.agenda-card [data-agenda-date]');
        upcomingEvents.forEach(event => {
            const eventDate = new Date(event.dataset.agendaDate);
            const now = new Date();
            const diff = eventDate - now;

            if (diff > 0) {
                const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
                if (days <= 7) {
                    const countdownEl = document.createElement('small');
                    countdownEl.className = 'text-warning fw-bold';
                    countdownEl.innerHTML = `<i class="fas fa-clock me-1"></i>${days} hari lagi`;
                    event.appendChild(countdownEl);
                }
            }
        });
    });
</script>