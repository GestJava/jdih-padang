/**
 * JDIH Dashboard JavaScript
 * Handles dashboard charts and interactions
 */

$(document).ready(function() {
	// Initialize dashboard components
	initDashboardCharts();
	initDashboardInteractions();
});

/**
 * Initialize all dashboard charts
 */
function initDashboardCharts() {
	
	// Destroy existing charts if they exist
	destroyExistingCharts();
	
	// Get theme colors
	const themeColors = getThemeColors();
	const colors = getColorPalette('modern', 10);
	
	// Dokumen per bulan chart
	initDokumenChart(themeColors, colors);
	
	// Dokumen by type chart
	initDokumenTypeChart(themeColors, colors);
	

	
	// Harmonisasi per bulan chart
	initHarmonisasiBulanChart(themeColors, colors);
	
	// Harmonisasi jenis chart
	initHarmonisasiJenisChart(themeColors, colors);
	
	// Harmonisasi instansi chart
	initHarmonisasiInstansiChart(themeColors, colors);
	
	// Harmonisasi verifikator chart
	initHarmonisasiVerifikatorChart(themeColors, colors);
	
	// Dokumen jenis chart
	initDokumenJenisChart(themeColors, colors);
	
	// Dokumen top jenis chart
	initDokumenTopJenisChart(themeColors, colors);
	
}

/**
 * Destroy existing charts to prevent conflicts
 */
function destroyExistingCharts() {
	// Destroy dokumen chart
	const dokumenChart = Chart.getChart('dokumen-chart');
	if (dokumenChart) {
		dokumenChart.destroy();
	}
	
	// Destroy type chart
	const typeChart = Chart.getChart('dokumen-type-chart');
	if (typeChart) {
		typeChart.destroy();
	}
	

	
	// Destroy harmonisasi bulan chart
	const harmonisasiBulanChart = Chart.getChart('harmonisasi-bulan-chart');
	if (harmonisasiBulanChart) {
		harmonisasiBulanChart.destroy();
	}
	
	// Destroy harmonisasi jenis chart
	const harmonisasiJenisChart = Chart.getChart('harmonisasi-jenis-chart');
	if (harmonisasiJenisChart) {
		harmonisasiJenisChart.destroy();
	}
	
	// Destroy harmonisasi instansi chart
	const harmonisasiInstansiChart = Chart.getChart('harmonisasi-instansi-chart');
	if (harmonisasiInstansiChart) {
		harmonisasiInstansiChart.destroy();
	}
	
	// Destroy harmonisasi verifikator chart
	const harmonisasiVerifikatorChart = Chart.getChart('harmonisasi-verifikator-chart');
	if (harmonisasiVerifikatorChart) {
		harmonisasiVerifikatorChart.destroy();
	}
	
	// Destroy dokumen jenis chart
	const dokumenJenisChart = Chart.getChart('dokumen-jenis-chart');
	if (dokumenJenisChart) {
		dokumenJenisChart.destroy();
	}
	
	// Destroy dokumen top jenis chart
	const dokumenTopJenisChart = Chart.getChart('dokumen-top-jenis-chart');
	if (dokumenTopJenisChart) {
		dokumenTopJenisChart.destroy();
	}
}

/**
 * Initialize dokumen per bulan chart
 */
function initDokumenChart(themeColors, colors) {
	const ctx = document.getElementById('dokumen-chart');
	if (!ctx) {
		console.warn('Dokumen chart canvas not found');
		return;
	}
	
	try {
		// Get data from PHP variables (if available)
		const dokumenData = window.dokumenData || [];
		const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Oct', 'Nov', 'Des'];
		
		new Chart(ctx, {
			type: 'bar',
			data: {
				labels: monthNames,
				datasets: [{
					label: 'Dokumen Dipublikasikan',
					data: dokumenData,
					backgroundColor: colors[0] + '80', // Add transparency
					borderColor: colors[0],
					borderWidth: 2,
					borderRadius: 6,
					tension: 0.1
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false
					},
					tooltip: {
						callbacks: {
							label: function(context) {
								return 'Dokumen: ' + context.parsed.y;
							}
						}
					}
				},
				scales: {
					y: {
						beginAtZero: true,
						grid: {
							color: themeColors.grid
						},
						ticks: {
							color: themeColors.font
						}
					},
					x: {
						grid: {
							display: false
						},
						ticks: {
							color: themeColors.font
						}
					}
				}
			}
		});
	} catch (error) {
		console.error('Error initializing dokumen chart:', error);
	}
}

/**
 * Initialize dokumen by type chart
 */
function initDokumenTypeChart(themeColors, colors) {
	const ctx = document.getElementById('dokumen-type-chart');
	if (!ctx) {
		console.warn('Dokumen type chart canvas not found');
		return;
	}
	
	try {
		// Get data from PHP variables (if available)
		const typeData = window.typeData || [];
		const typeLabels = window.typeLabels || [];
		
		new Chart(ctx, {
			type: 'doughnut',
			data: {
				labels: typeLabels,
				datasets: [{
					data: typeData,
					backgroundColor: colors.slice(0, typeData.length),
					borderWidth: 2,
					borderColor: themeColors.border
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						position: 'bottom',
						labels: {
							padding: 15,
							usePointStyle: true,
							color: themeColors.font,
							font: {
								size: 11
							}
						}
					},
					tooltip: {
						callbacks: {
							label: function(context) {
								const total = context.dataset.data.reduce((a, b) => a + b, 0);
								const percentage = ((context.parsed / total) * 100).toFixed(1);
								return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
							}
						}
					}
				}
			}
		});
	} catch (error) {
		console.error('Error initializing dokumen type chart:', error);
	}
}



/**
 * Initialize harmonisasi per bulan chart
 */
function initHarmonisasiBulanChart(themeColors, colors) {
	const ctx = document.getElementById('harmonisasi-bulan-chart');
	if (!ctx) {
		console.warn('Harmonisasi bulan chart canvas not found');
		return;
	}
	
	try {
		// Get data from PHP variables
		const harmBulanData = window.harmBulanData || [];
		const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Oct', 'Nov', 'Des'];
		
		new Chart(ctx, {
			type: 'line',
			data: {
				labels: monthNames,
				datasets: [{
					label: 'Ajuan Harmonisasi',
					data: harmBulanData,
					backgroundColor: colors[1] + '20',
					borderColor: colors[1],
					borderWidth: 3,
					tension: 0.4,
					fill: true,
					pointBackgroundColor: colors[1],
					pointBorderColor: '#fff',
					pointBorderWidth: 2,
					pointRadius: 6
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false
					},
					tooltip: {
						callbacks: {
							label: function(context) {
								return 'Ajuan: ' + context.parsed.y;
							}
						}
					}
				},
				scales: {
					y: {
						beginAtZero: true,
						grid: {
							color: themeColors.grid
						},
						ticks: {
							color: themeColors.font
						}
					},
					x: {
						grid: {
							display: false
						},
						ticks: {
							color: themeColors.font
						}
					}
				}
			}
		});
	} catch (error) {
		console.error('Error initializing harmonisasi bulan chart:', error);
	}
}

/**
 * Initialize harmonisasi jenis chart
 */
function initHarmonisasiJenisChart(themeColors, colors) {
	const ctx = document.getElementById('harmonisasi-jenis-chart');
	if (!ctx) {
		console.warn('Harmonisasi jenis chart canvas not found');
		return;
	}
	
	try {
		// Get data from PHP variables
		const harmJenisData = window.harmJenisData || [];
		const harmJenisLabels = window.harmJenisLabels || [];
		
		new Chart(ctx, {
			type: 'doughnut',
			data: {
				labels: harmJenisLabels,
				datasets: [{
					data: harmJenisData,
					backgroundColor: colors.slice(0, harmJenisData.length),
					borderWidth: 2,
					borderColor: themeColors.border
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						position: 'bottom',
						labels: {
							padding: 15,
							usePointStyle: true,
							color: themeColors.font,
							font: {
								size: 11
							}
						}
					},
					tooltip: {
						callbacks: {
							label: function(context) {
								const total = context.dataset.data.reduce((a, b) => a + b, 0);
								const percentage = ((context.parsed / total) * 100).toFixed(1);
								return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
							}
						}
					}
				}
			}
		});
	} catch (error) {
		console.error('Error initializing harmonisasi jenis chart:', error);
	}
}

/**
 * Initialize harmonisasi instansi chart
 */
function initHarmonisasiInstansiChart(themeColors, colors) {
	const ctx = document.getElementById('harmonisasi-instansi-chart');
	if (!ctx) {
		console.warn('Harmonisasi instansi chart canvas not found');
		return;
	}
	
	try {
		// Get data from PHP variables
		const harmInstansiData = window.harmInstansiData || [];
		const harmInstansiLabels = window.harmInstansiLabels || [];
		
		new Chart(ctx, {
			type: 'bar',
			data: {
				labels: harmInstansiLabels,
				datasets: [{
					label: 'Jumlah Ajuan',
					data: harmInstansiData,
					backgroundColor: colors[2] + '80',
					borderColor: colors[2],
					borderWidth: 2,
					borderRadius: 6
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				indexAxis: 'y',
				plugins: {
					legend: {
						display: false
					},
					tooltip: {
						callbacks: {
							label: function(context) {
								return 'Ajuan: ' + context.parsed.x;
							}
						}
					}
				},
				scales: {
					x: {
						beginAtZero: true,
						grid: {
							color: themeColors.grid
						},
						ticks: {
							color: themeColors.font
						}
					},
					y: {
						grid: {
							display: false
						},
						ticks: {
							color: themeColors.font
						}
					}
				}
			}
		});
	} catch (error) {
		console.error('Error initializing harmonisasi instansi chart:', error);
	}
}

/**
 * Initialize harmonisasi verifikator chart
 */
function initHarmonisasiVerifikatorChart(themeColors, colors) {
	const ctx = document.getElementById('harmonisasi-verifikator-chart');
	if (!ctx) {
		console.warn('Harmonisasi verifikator chart canvas not found');
		return;
	}
	
	try {
		// Get data from PHP variables
		const harmVerifikatorData = window.harmVerifikatorData || [];
		const harmVerifikatorLabels = window.harmVerifikatorLabels || [];
		
		new Chart(ctx, {
			type: 'bar',
			data: {
				labels: harmVerifikatorLabels,
				datasets: [{
					label: 'Jumlah Ajuan',
					data: harmVerifikatorData,
					backgroundColor: colors[3] + '80',
					borderColor: colors[3],
					borderWidth: 2,
					borderRadius: 6
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				indexAxis: 'y',
				plugins: {
					legend: {
						display: false
					},
					tooltip: {
						callbacks: {
							label: function(context) {
								return 'Ajuan: ' + context.parsed.x;
							}
						}
					}
				},
				scales: {
					x: {
						beginAtZero: true,
						grid: {
							color: themeColors.grid
						},
						ticks: {
							color: themeColors.font
						}
					},
					y: {
						grid: {
							display: false
						},
						ticks: {
							color: themeColors.font
						}
					}
				}
			}
		});
	} catch (error) {
		console.error('Error initializing harmonisasi verifikator chart:', error);
	}
}

/**
 * Initialize dokumen jenis chart
 */
function initDokumenJenisChart(themeColors, colors) {
	const ctx = document.getElementById('dokumen-jenis-chart');
	if (!ctx) {
		console.warn('Dokumen jenis chart canvas not found');
		return;
	}
	
	try {
		// Get data from PHP variables (dari tabel web_peraturan)
		const dokumenPeraturanData = window.dokumenPeraturanData || [];
		const dokumenPeraturanLabels = window.dokumenPeraturanLabels || [];
		
		new Chart(ctx, {
			type: 'bar',
			data: {
				labels: dokumenPeraturanLabels,
				datasets: [{
					label: 'Jumlah Dokumen',
					data: dokumenPeraturanData,
					backgroundColor: colors[4] + '80',
					borderColor: colors[4],
					borderWidth: 2,
					borderRadius: 6
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false
					},
					tooltip: {
						callbacks: {
							label: function(context) {
								return 'Dokumen: ' + context.parsed.y;
							}
						}
					}
				},
				scales: {
					y: {
						beginAtZero: true,
						grid: {
							color: themeColors.grid
						},
						ticks: {
							color: themeColors.font
						}
					},
					x: {
						grid: {
							display: false
						},
						ticks: {
							color: themeColors.font,
							maxRotation: 45
						}
					}
				}
			}
		});
	} catch (error) {
		console.error('Error initializing dokumen jenis chart:', error);
	}
}

/**
 * Initialize dokumen top jenis chart
 */
function initDokumenTopJenisChart(themeColors, colors) {
	const ctx = document.getElementById('dokumen-top-jenis-chart');
	if (!ctx) {
		console.warn('Dokumen top jenis chart canvas not found');
		return;
	}
	
	try {
		// Get data from PHP variables (dari tabel web_peraturan, top 5 only)
		const dokumenPeraturanData = window.dokumenPeraturanData || [];
		const dokumenPeraturanLabels = window.dokumenPeraturanLabels || [];
		
		// Take only top 5
		const top5Data = dokumenPeraturanData.slice(0, 5);
		const top5Labels = dokumenPeraturanLabels.slice(0, 5);
		
		new Chart(ctx, {
			type: 'doughnut',
			data: {
				labels: top5Labels,
				datasets: [{
					data: top5Data,
					backgroundColor: colors.slice(0, top5Data.length),
					borderWidth: 2,
					borderColor: themeColors.border
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						position: 'bottom',
						labels: {
							padding: 15,
							usePointStyle: true,
							color: themeColors.font,
							font: {
								size: 11
							}
						}
					},
					tooltip: {
						callbacks: {
							label: function(context) {
								const total = context.dataset.data.reduce((a, b) => a + b, 0);
								const percentage = ((context.parsed / total) * 100).toFixed(1);
								return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
							}
						}
					}
				}
			}
		});
	} catch (error) {
		console.error('Error initializing dokumen top jenis chart:', error);
	}
}

/**
 * Initialize dashboard interactions
 */
function initDashboardInteractions() {
	
	// Year selector functionality
	$('.year-selector').on('change', function() {
		const year = $(this).val();
		updateDashboardData(year);
	});
	
	// Refresh button functionality
	$('.refresh-dashboard').on('click', function() {
		location.reload();
	});
	
	// Auto refresh every 5 minutes (optional)
	setInterval(function() {
		// Uncomment to enable auto refresh
		// updateDashboardData();
	}, 300000);
	
}

/**
 * Update dashboard data via AJAX
 */
function updateDashboardData(year) {
	year = year || new Date().getFullYear();
	
	$.ajax({
		url: base_url + 'dashboard/ajaxGetDokumenPerBulan',
		type: 'GET',
		data: { tahun: year },
		success: function(response) {
			// Update charts with new data
			updateCharts(response);
		},
		error: function(xhr, status, error) {
			console.error('Error updating dashboard data:', error);
		}
	});
}

/**
 * Update charts with new data
 */
function updateCharts(data) {
	// Implementation for updating charts with new data
	console.log('Updating charts with new data:', data);
}

/**
 * Utility function to format numbers
 */
function formatNumber(num) {
	return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}