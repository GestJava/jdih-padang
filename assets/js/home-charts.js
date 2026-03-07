// Script untuk membuat chart di halaman home JDIH
document.addEventListener('DOMContentLoaded', function() {
    // Chart untuk Dokumen per Kategori
    if(document.getElementById('docsPerCategory')) {
        const docsPerCategoryCtx = document.getElementById('docsPerCategory').getContext('2d');
        const docsPerCategory = new Chart(docsPerCategoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Undang-Undang', 'Peraturan Pemerintah', 'Peraturan Presiden', 'Peraturan Daerah', 'Lainnya'],
                datasets: [{
                    data: [157, 243, 189, 276, 100],
                    backgroundColor: [
                        '#4e73df',
                        '#1cc88a',
                        '#36b9cc',
                        '#f6c23e',
                        '#e74a3b'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        bodyFont: {
                            size: 12
                        },
                        titleFont: {
                            size: 13
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
    }
    
    // Chart untuk Trend Pencarian

    // Pie Chart untuk distribusi dokumen per kategori (Statistik Dokumen)
    if(document.getElementById('kategoriChart')) {
      const kategoriChartCtx = document.getElementById('kategoriChart').getContext('2d');
      const kategoriChart = new Chart(kategoriChartCtx, {
        type: 'pie',
        data: {
          labels: ['Perda', 'Perbup', 'Keputusan', 'Instruksi', 'Surat Edaran', 'MOU'],
          datasets: [{
            data: [120, 180, 90, 60, 45, 30], // Ganti sesuai data real
            backgroundColor: [
              '#0d6efd', '#198754', '#fd7e14', '#6f42c1', '#20c997', '#ffc107'
            ],
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: true,
              position: 'bottom',
              labels: {
                font: { size: 13 },
                color: '#6f42c1',
                padding: 18,
                boxWidth: 18
              }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  let label = context.label || '';
                  let value = context.parsed || 0;
                  return label + ': ' + value + ' dokumen';
                }
              }
            }
          }
        }
      });
    }
    if(document.getElementById('searchTrend')) {
        const searchTrendCtx = document.getElementById('searchTrend').getContext('2d');
        const searchTrend = new Chart(searchTrendCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                datasets: [{
                    label: 'Pencarian',
                    data: [1458, 1985, 2365, 2842, 3156, 3743],
                    backgroundColor: 'rgba(54, 185, 204, 0.1)',
                    borderColor: '#36b9cc',
                    borderWidth: 2,
                    pointBackgroundColor: '#36b9cc',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10
                    }
                }
            }
        });
    }
    
    // Animasi untuk elemen Timeline
    const timelineItems = document.querySelectorAll('.timeline-item');
    if(timelineItems.length > 0) {
        timelineItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            item.style.transition = 'all 0.5s ease';
            
            setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, 200 + (index * 200));
        });
    }
    
    // Interaksi YouTube play button
    const playButtons = document.querySelectorAll('.play-button');
    if(playButtons.length > 0) {
        playButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Dalam implementasi nyata, ini akan mengarah ke video YouTube yang sebenarnya
                alert('Video akan diputar di YouTube');
            });
        });
    }
});
