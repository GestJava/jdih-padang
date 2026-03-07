<?= $this->extend('layouts/frontend') ?>

<?php
// Data preparation for charts
$tahunan_labels = [];
$tahunan_values = [];
if (!empty($tahunan_data) && is_array($tahunan_data)) {
    // Sort by year ascending for the chart
    usort($tahunan_data, function ($a, $b) {
        return ($a['tahun'] ?? 0) <=> ($b['tahun'] ?? 0);
    });
    foreach ($tahunan_data as $item) {
        if (isset($item['tahun']) && isset($item['jumlah'])) {
            $tahunan_labels[] = $item['tahun'];
            $tahunan_values[] = $item['jumlah'];
        }
    }
}
$jenis_labels = [];
$jenis_values = [];
if (!empty($jenis_counts)) {
    usort($jenis_counts, function ($a, $b) {
        return ($b['jumlah'] ?? 0) <=> ($a['jumlah'] ?? 0);
    });
    foreach ($jenis_counts as $item) {
        $jenis_labels[] = $item['nama_jenis'] ?? 'Lainnya';
        $jenis_values[] = $item['jumlah'] ?? 0;
    }
}
$status_labels = [];
$status_values = [];
if (!empty($status_counts)) {
    foreach ($status_counts as $status => $count) {
        $status_labels[] = $status;
        $status_values[] = $count;
    }
}
?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="jdih-hero">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="hero-badge mb-3">
                    <span><i class="fas fa-chart-bar icon-sm me-1"></i> Statistik</span>
                </div>
                <h1 class="hero-title">Statistik Produk Hukum</h1>
                <p class="hero-subtitle">Data dan analisis produk hukum Kota Padang</p>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-decoration-none">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Statistik</li>
        </ol>
    </nav>

    <style>
        .card-statistik {
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        @media (min-width: 768px) {
            .card-statistik {
                min-height: 200px;
            }
        }
    </style>

    <!-- Ringkasan Statistik -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card card-statistik shadow-sm border-0 text-center h-100">
                <div class="card-body p-4">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-file-alt text-white fa-2x"></i>
                    </div>
                    <h3 class="h4 mb-1"><?= number_format($total_dokumen) ?></h3>
                    <p class="text-muted mb-0">Total Dokumen</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-statistik shadow-sm border-0 text-center h-100">
                <div class="card-body p-4">
                    <div class="bg-success rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-eye text-white fa-2x"></i>
                    </div>
                    <h3 class="h4 mb-1"><?= number_format($total_hits) ?></h3>
                    <p class="text-muted mb-0">Total Dilihat</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-statistik shadow-sm border-0 text-center h-100">
                <div class="card-body p-4">
                    <div class="bg-info rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-calendar-alt text-white fa-2x"></i>
                    </div>
                    <h3 class="h4 mb-1"><?= $tahun_terbanyak['tahun'] ?? '-' ?></h3>
                    <p class="text-muted mb-0">Tahun Terbanyak</p>
                    <small class="text-muted"><?= number_format($tahun_terbanyak['jumlah'] ?? 0) ?> dokumen</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-statistik shadow-sm border-0 text-center h-100">
                <div class="card-body p-4">
                    <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-download text-white fa-2x"></i>
                    </div>
                    <h3 class="h4 mb-1"><?= number_format($total_unduhan) ?></h3>
                    <p class="text-muted mb-0">Total Unduhan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Tahunan -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h2 class="h4 mb-0 text-primary"><i class="fas fa-chart-bar me-2"></i>Statistik Produk Hukum Per Tahun</h2>
        </div>
        <div class="card-body p-4">
            <div class="chart-container" style="position: relative; height:300px;">
                <canvas id="chartTahunan" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Statistik Jenis Peraturan -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h2 class="h4 mb-0 text-primary"><i class="fas fa-chart-pie me-2"></i>Statistik Berdasarkan Jenis</h2>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-12">
                    <div class="chart-container" style="position: relative;">
                        <canvas id="chartJenis" width="400" height="400"></canvas>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Jenis Peraturan</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-center">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($jenis_counts)) {
                                    foreach ($jenis_counts as $item) {
                                        $persentase = $total_dokumen > 0 ? round(($item['jumlah'] / $total_dokumen) * 100, 1) : 0;
                                ?>
                                        <tr>
                                            <td class="align-middle"><?= esc($item['nama_jenis'] ?? 'Lainnya') ?></td>
                                            <td class="text-center align-middle">
                                                <div class="fw-bold fs-5"><?= $item['jumlah'] ?? 0 ?></div>
                                                <small class="text-muted">dokumen</small>
                                            </td>
                                            <td class="text-center align-middle fw-bold fs-5 text-primary">
                                                <?= $persentase ?>%
                                            </td>
                                        </tr>
                                    <?php
                                    } // end foreach
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="3" class="text-center p-4">Data statistik tidak tersedia.</td>
                                    </tr>
                                <?php
                                } // end if
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Status Peraturan -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h2 class="h4 mb-0 text-primary"><i class="fas fa-chart-pie me-2"></i>Statistik Berdasarkan Status</h2>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-5">
                    <div class="chart-container" style="position: relative; height:250px;">
                        <canvas id="chartStatus" width="200" height="200"></canvas>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Status</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-center">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($status_counts)) {
                                    $total_status = array_sum($status_counts);
                                    foreach ($status_counts as $status => $count) {
                                        $persentase = $total_status > 0 ? round(($count / $total_status) * 100, 1) : 0;
                                ?>
                                        <tr>
                                            <td><?= $status ?></td>
                                            <td class="text-center"><?= $count ?> <span class="text-muted">dokumen</span></td>
                                            <td class="text-center"><?= $persentase ?>%</td>
                                        </tr>
                                    <?php
                                    } // end foreach
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Data statistik tidak tersedia.</td>
                                    </tr>
                                <?php
                                } // end if
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Instansi (Baru) -->
    <?php if (!empty($instansi_stats)): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h2 class="h4 mb-0 text-primary"><i class="fas fa-building me-2"></i>Statistik Berdasarkan Instansi</h2>
            </div>
            <div class="card-body p-4">
                <div class="chart-container" style="position: relative; height:300px;">
                    <canvas id="chartInstansi" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Statistik Peraturan Tahun <?= $current_year_display ?? date('Y') ?> Berdasarkan Jenis -->
    <?php if (!empty($jenis_current_year_data)): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h2 class="h4 mb-0 text-primary"><i class="fas fa-chart-bar me-2"></i>Peraturan Tahun <?= $current_year_display ?? date('Y') ?> Berdasarkan Jenis</h2>
            </div>
            <div class="card-body p-4">
                <div class="chart-container" style="position: relative; height:300px;">
                    <canvas id="chartJenisTahun<?= $current_year_display ?? date('Y') ?>" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Dokumen Populer -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h2 class="h4 mb-0 text-primary"><i class="fas fa-fire me-2"></i>Dokumen Populer</h2>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <?php if (!empty($peraturan_populer) && is_array($peraturan_populer)) : ?>
                    <?php foreach ($peraturan_populer as $item) : ?>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-file-alt text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">
                                        <a href="<?= base_url('peraturan/' . $item['slug_peraturan']) ?>" class="text-decoration-none">
                                            <?= esc($item['judul']) ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-eye me-1"></i>
                                        Dilihat <?= number_format($item['hits']) ?> kali
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="col-12">
                        <p class="text-center text-muted">Data tidak tersedia.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js dari CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

<!-- Script untuk inisialisasi chart -->
<script>
    // Ubah inisialisasi chart menjadi lazy loading untuk semua chart
    document.addEventListener('DOMContentLoaded', function() {
        // Definisi warna untuk chart (tetap sama)
        const colors = {
            primary: {
                fill: 'rgba(13, 110, 253, 0.7)',
                stroke: 'rgba(13, 110, 253, 1)'
            },
            success: {
                fill: 'rgba(25, 135, 84, 0.7)',
                stroke: 'rgba(25, 135, 84, 1)'
            },
            warning: {
                fill: 'rgba(255, 193, 7, 0.7)',
                stroke: 'rgba(255, 193, 7, 1)'
            },
            danger: {
                fill: 'rgba(220, 53, 69, 0.7)',
                stroke: 'rgba(220, 53, 69, 1)'
            },
            info: {
                fill: 'rgba(13, 202, 240, 0.7)',
                stroke: 'rgba(13, 202, 240, 1)'
            },
            secondary: {
                fill: 'rgba(108, 117, 125, 0.7)',
                stroke: 'rgba(108, 117, 125, 1)'
            },
            purple: {
                fill: 'rgba(111, 66, 193, 0.7)',
                stroke: 'rgba(111, 66, 193, 1)'
            }
        };

        // Fungsi untuk lazy loading chart
        function initCharts() {
            // Chart Tahunan dengan data dinamis
            const ctxTahunan = document.getElementById('chartTahunan').getContext('2d');
            const chartTahunan = new Chart(ctxTahunan, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($tahunan_labels) ?>,
                    datasets: [{
                        label: 'Jumlah Dokumen',
                        data: <?= json_encode($tahunan_values) ?>,
                        backgroundColor: <?= json_encode($chart_colors_tahunan) ?>,
                        borderColor: <?= json_encode($chart_colors_tahunan) ?>,
                        borderWidth: 1,
                        borderRadius: 4,
                        barThickness: 20,
                        maxBarThickness: 30
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    weight: 'bold'
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' dokumen';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Fungsi untuk lazy loading chart jenis dan status
        function initSecondaryCharts() {
            // Chart Jenis Peraturan dengan data dinamis
            const ctxJenis = document.getElementById('chartJenis').getContext('2d');
            const chartJenis = new Chart(ctxJenis, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($jenis_labels) ?>,
                    datasets: [{
                        label: 'Jumlah',
                        data: <?= json_encode($jenis_values) ?>,
                        backgroundColor: <?= json_encode($chart_colors_jenis) ?>,
                        borderColor: <?= json_encode($chart_colors_jenis) ?>,
                        borderWidth: 2,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 13,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    let total = context.chart.getDatasetMeta(0).total;
                                    let percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Chart Status Peraturan dengan data dinamis
            const ctxStatus = document.getElementById('chartStatus').getContext('2d');
            const chartStatus = new Chart(ctxStatus, {
                type: 'pie',
                data: {
                    labels: <?= json_encode($status_labels) ?>,
                    datasets: [{
                        label: 'Jumlah',
                        data: <?= json_encode($status_values) ?>,
                        backgroundColor: <?= json_encode($chart_colors_status) ?>,
                        borderColor: <?= json_encode($chart_colors_status) ?>,
                        borderWidth: 2,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false // Legenda sudah ditampilkan di samping dalam bentuk daftar
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    let total = context.chart.getDatasetMeta(0).total;
                                    let percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Inisialisasi chart utama segera
        initCharts();

        // Inisialisasi chart sekunder dengan lazy loading
        let secondaryChartsInitialized = false;

        // Fungsi untuk memeriksa apakah elemen dalam viewport
        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.bottom >= 0
            );
        }

        // Fungsi untuk memeriksa dan menginisialisasi chart sekunder jika terlihat
        function checkAndInitSecondaryCharts() {
            if (!secondaryChartsInitialized) {
                const chartJenisElement = document.getElementById('chartJenis');
                if (chartJenisElement && isElementInViewport(chartJenisElement)) {
                    initSecondaryCharts();
                    secondaryChartsInitialized = true;
                    // Hapus event listener setelah chart diinisialisasi
                    window.removeEventListener('scroll', checkAndInitSecondaryCharts);
                }
            }
        }

        // Tambahkan event listener untuk scroll
        window.addEventListener('scroll', checkAndInitSecondaryCharts);

        // Periksa juga saat halaman pertama kali dimuat
        checkAndInitSecondaryCharts();

        // Chart Instansi
        <?php if (!empty($instansi_stats)): ?>
            const ctxInstansi = document.getElementById('chartInstansi');
            if (ctxInstansi) {
                const chartInstansi = new Chart(ctxInstansi.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode(array_keys($instansi_stats ?? [])) ?>,
                        datasets: [{
                            label: 'Jumlah Peraturan',
                            data: <?= json_encode(array_values($instansi_stats ?? [])) ?>,
                            backgroundColor: <?= json_encode($chart_colors_instansi) ?>,
                            borderColor: <?= json_encode($chart_colors_instansi) ?>,
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        <?php endif; ?>

        // Chart Jenis Peraturan Tahun Dinamis
        <?php if (!empty($jenis_current_year_data)): ?>
            const ctxJenisCurrentYear = document.getElementById('chartJenisTahun<?= $current_year_display ?? date('Y') ?>');
            if (ctxJenisCurrentYear) {
                const chartJenisCurrentYear = new Chart(ctxJenisCurrentYear.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode(array_keys($jenis_current_year_data ?? [])) ?>,
                        datasets: [{
                            label: 'Jumlah Peraturan',
                            data: <?= json_encode(array_values($jenis_current_year_data ?? [])) ?>,
                            backgroundColor: <?= json_encode($chart_colors_jenis) ?>,
                            borderColor: <?= json_encode($chart_colors_jenis) ?>,
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>