<?php

/**
 *	App Name	: JDIH Kota Padang	
 *	Author		: Agus Salim
 *	Website		: https://jdih.padang.go.id
 *	Year		: 2025
 */

namespace App\Controllers;

use App\Models\DashboardModel;

class Dashboard extends BaseController
{
	public function __construct()
	{
		parent::__construct();
		$this->model = new DashboardModel;
		$this->addJs(base_url('vendors/chartjs/chart.js'));
		$this->addStyle(base_url('vendors/material-icons/css.css'));
		$this->addStyle(base_url('vendors/fontawesome/css/all.min.css'));

		$this->addJs(base_url('vendors/datatables/extensions/Buttons/js/dataTables.buttons.min.js'));
		$this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.bootstrap5.min.js'));
		$this->addJs(base_url('vendors/datatables/extensions/JSZip/jszip.min.js'));
		$this->addJs(base_url('vendors/datatables/extensions/pdfmake/pdfmake.min.js'));
		$this->addJs(base_url('vendors/datatables/extensions/pdfmake/vfs_fonts.js'));
		$this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.html5.min.js'));
		$this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.print.min.js'));
		$this->addStyle(base_url('vendors/datatables/extensions/Buttons/css/buttons.bootstrap5.min.css'));

		$this->addStyle(base_url('themes/modern/css/dashboard.css'));

		// Load chart-utils.js first, then dashboard.js
		$this->addJs(base_url('themes/modern/js/chart-utils.js'));
		$this->addJs(base_url('themes/modern/js/dashboard.js'));
	}

	public function index()
	{
		// KEAMANAN: Set no-cache headers untuk mencegah browser/CDN cache halaman dashboard
		// Ini penting untuk memastikan session tidak ter-cache dan user selalu melihat halaman terbaru
		$response = service('response');
		$response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
		$response->setHeader('Pragma', 'no-cache');
		$response->setHeader('Expires', '0');

		$result = $this->model->getListTahun();
		$list_tahun = [];
		foreach ($result as $val) {
			$list_tahun[$val['tahun']] = $val['tahun'];
		}

		// Default ke tahun hari ini, atau ambil dari parameter GET
		$tahun_sekarang = date('Y');
		$tahun = $this->request->getGet('tahun') ?? $tahun_sekarang;
		
		// Validasi tahun: jika tidak ada di list, gunakan tahun sekarang
		if (!in_array($tahun, $list_tahun) && !empty($list_tahun)) {
			$tahun = $tahun_sekarang;
		} elseif (empty($list_tahun)) {
			$tahun = $tahun_sekarang;
		}

		                // ============================================================
                // CACHING IMPLEMENTATION (OPTIMIZATION)
                // Cache key unique per tahun to avoid conflict
                // ============================================================
                $cacheKey = 'dashboard_stats_' . $tahun;
                $cachedData = cache()->get($cacheKey);

                if ($cachedData) {
                    // Jika ada cache, gunakan data dari cache
                    $this->data = array_merge($this->data, $cachedData);
                    // Tambahkan indikator bahwa data diambil dari cache (untuk debug jika perlu)
                    // log_message('debug', 'Dashboard stats loaded from cache: ' . $cacheKey);
                } else {
                    // Jika tidak ada cache, jalankan query berat
                    
                    // Get JDIH relevant statistics - GLOBAL DATA untuk semua role
                    $statsData['total_dokumen'] = $this->model->getTotalDokumen($tahun);
                    $statsData['total_harmonisasi'] = $this->model->getTotalHarmonisasi($tahun);
                    $statsData['total_user'] = $this->model->getTotalUser($tahun);
                    $statsData['dokumen_bulan_ini'] = $this->model->getDokumenBulanIni();

                    // Get chart data - GLOBAL DATA untuk semua role
                    $statsData['dokumen_per_bulan'] = $this->model->getDokumenPerBulan($tahun);
                    $statsData['dokumen_by_type'] = $this->model->getDokumenByType($tahun);
                    $statsData['harmonisasi_status'] = $this->model->getHarmonisasiStatus($tahun);

                    // Get recent documents - GLOBAL DATA untuk semua role
                    $dokumen_terbaru = $this->model->getDokumenTerbaru(10);
                    $statsData['dokumen_terbaru'] = $dokumen_terbaru;

                    $statsData['message']['status'] = 'ok';
                    if (empty($statsData['dokumen_per_bulan']) && empty($statsData['dokumen_terbaru'])) {
                            $statsData['message']['status'] = 'error';
                            $statsData['message']['message'] = 'Data tidak ditemukan';
                    }

                    // Statistik harmonisasi - GLOBAL DATA untuk semua role
                    // Semua role melihat data global, tidak terbatas berdasarkan instansi
                    $statsData['harm_total_ajuan'] = $this->model->getTotalAjuanHarmonisasi($tahun);
                    $statsData['harm_selesai'] = $this->model->getHarmonisasiSelesai($tahun); // NEW: Helper method
                    // $statsData['harm_per_status'] used for graph, keep it
                    $statsData['harm_per_status'] = $this->model->getHarmonisasiPerStatus($tahun);
                    $statsData['harm_per_bulan'] = $this->model->getHarmonisasiPerBulan($tahun);

                    // Statistik Legalisasi - GLOBAL DATA (NEW)
                    $statsData['leg_total_ajuan'] = $this->model->getTotalAjuanLegalisasi($tahun);
                    $statsData['leg_selesai'] = $this->model->getLegalisasiSelesai($tahun);
                    
                    // Simpan ke cache selama 1 jam (3600 detik)
                    cache()->save($cacheKey, $statsData, 3600);
                    
                    // Merge ke this->data
                    $this->data = array_merge($this->data, $statsData);
                }

                $this->data['list_tahun'] = $list_tahun;
                $this->data['tahun'] = $tahun;

		$this->view('dashboard.php', $this->data);
	}

	public function ajaxGetDokumenPerBulan()
	{
		$tahun = $_GET['tahun'] ?? date('Y');
		$result = $this->model->getDokumenPerBulan($tahun);

		echo json_encode($result);
	}

	public function ajaxGetDokumenByType()
	{
		$tahun = $_GET['tahun'] ?? date('Y');
		$result = $this->model->getDokumenByType($tahun);

		$total = [];
		$jenis = [];
		foreach ($result as $val) {
			$total[] = $val['jumlah'];
			$jenis[] = $val['jenis_peraturan'];
		}

		echo json_encode(['total' => $total, 'jenis' => $jenis, 'data' => $result]);
	}

	public function ajaxGetHarmonisasiStatus()
	{
		$tahun = $_GET['tahun'] ?? date('Y');
		$result = $this->model->getHarmonisasiStatus($tahun);

		$total = [];
		$status = [];
		foreach ($result as $val) {
			$total[] = $val['jumlah'];
			$status[] = $val['nama_status'];
		}

		echo json_encode(['total' => $total, 'status' => $status, 'data' => $result]);
	}

	public function ajaxGetVisitorStats()
	{
		$tahun = $_GET['tahun'] ?? date('Y');
		$result = $this->model->getVisitorStats($tahun);

		echo json_encode($result);
	}
}
