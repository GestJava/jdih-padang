<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AgendaModel;
use App\Models\BeritaModel;
use App\Models\FeedbackModel;
use App\Models\WebPeraturanModel;
use App\Models\WebBeritaKategoriModel;
use App\Models\JenisDokumenModel;
use App\Models\StatusDokumenModel;
use App\Models\WebTagModel;
use App\Models\KontakPesanModel;
use App\Models\VisitorStatsModel;
use App\Models\AnnouncementModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * @property \CodeIgniter\HTTP\IncomingRequest $request
 */
class Frontend extends Controller
{
    protected $request;
    protected $webPeraturanModel;
    protected $beritaModel;
    protected $agendaModel;
    protected $feedbackModel;
    protected $kategoriBeritaModel;
    protected $jenisDokumenModel;
    protected $statusDokumenModel;
    protected $webTagModel;
    protected $kontakPesanModel;
    protected $visitorStatsModel;
    protected $announcementModel;
    protected $global_data = [];

    public function __construct()
    {
        $this->request = \Config\Services::request();

        // Load cache helper for optimized caching
        helper('cache');

        // KEAMANAN: Set no-cache headers untuk halaman yang menampilkan data user
        // Ini penting untuk mencegah browser/CDN cache halaman yang mengandung session
        $response = service('response');
        $session = service('session');
        $user = $session->get('user');
        
        // Jika user sudah login, set no-cache headers untuk mencegah cache data session
        if ($user) {
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
            $response->setHeader('X-Accel-Expires', '0'); // Nginx specific
        }

        // Initialize global data with aggressive caching
        $this->initializeGlobalData();

        // Optimized visitor tracking (rate limited)
        $this->trackVisitorOptimized();
    }

    /**
     * Initialize global data with caching
     */
    private function initializeGlobalData(): void
    {
        $this->global_data = cache_get_or_set('frontend_global_data', function () {
            // Get visitor stats with caching
            $visitorStats = cache_get_or_set('visitor_stats_frontend', function () {
                $visitorModel = $this->getModel('VisitorStatsModel');
                return $visitorModel->getVisitorStats();
            }, 'visitor_stats');

            // Get jenis peraturan for Produk Hukum menu (menu = 1)
            $jenisPeraturan = cache_get_or_set('jenis_peraturan_grouped', function () {
                $jenisModel = $this->getModel('JenisDokumenModel');
                $jenisList = $jenisModel->where('menu', 1)->orderBy('urutan', 'ASC')->findAll();
                return $this->groupJenisByKategori($jenisList);
            }, 'jenis_peraturan');

            // Get jenis peraturan for Pembentukan PUU menu (menu = 2)
            $jenisPUU = cache_get_or_set('jenis_puu_grouped', function () {
                $jenisModel = $this->getModel('JenisDokumenModel');
                $jenisList = $jenisModel->where('menu', 2)->orderBy('urutan', 'ASC')->findAll();
                return $this->groupJenisByKategori($jenisList);
            }, 'jenis_puu');

            return [
                'global_jenis_peraturan' => $jenisPeraturan,
                'global_jenis_puu' => $jenisPUU,
                'stat_total'  => $visitorStats['total'],
                'stat_today'  => $visitorStats['today'],
                'stat_week'   => $visitorStats['week'],
                'stat_month'  => $visitorStats['month'],
                'stat_year'   => $visitorStats['year'],
                'stat_online' => $visitorStats['online'],
            ];
        }, 'global_data');
    }

    /**
     * Optimized visitor tracking with rate limiting
     */
    private function trackVisitorOptimized(): void
    {
        $session = session();
        $lastTracked = $session->get('last_visitor_tracked');

        // Only track once per 5 minutes per session
        if (!$lastTracked || (time() - $lastTracked) > 300) {
            try {
                $visitorModel = $this->getModel('VisitorStatsModel');
                $visitorModel->recordVisitor();
                $session->set('last_visitor_tracked', time());

                // Clear relevant caches after tracking
                $cache = \Config\Services::cache();
                $cache->delete('visitor_stats_frontend');
                $cache->delete('frontend_global_data');
            } catch (\Exception $e) {
                // Silent fail - don't break page load for tracking issues
                log_message('error', 'Visitor tracking failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Lazy load models only when needed
     */
    private function getModel(string $modelName)
    {
        $property = lcfirst(str_replace('Model', '', $modelName)) . 'Model';

        if (!isset($this->$property) || $this->$property === null) {
            $className = "App\\Models\\{$modelName}";
            $this->$property = new $className();
        }

        return $this->$property;
    }

    /**
     * Utility: Kelompokkan jenis peraturan berdasarkan kategori_nama
     * @param array $jenisList
     * @return array
     */
    private function groupJenisByKategori($jenisList)
    {
        $grouped = [];
        foreach ($jenisList as $jenis) {
            $kategori = $jenis['kategori_nama'] ?? 'Lainnya';
            $grouped[$kategori][] = $jenis;
        }
        return $grouped;
    }

    public function index()
    {
        helper(['page_history']);

        // Cek apakah ada keyword baru sebelum load cache
        // Ini memastikan keyword baru langsung muncul tanpa harus tunggu cache expire
        $cache = \Config\Services::cache();
        $cacheTimestamp = $cache->get('popular_keywords_timestamp');
        $db = \Config\Database::connect();
        $shouldClearCache = false;
        
        try {
            if ($db->tableExists('search_keywords')) {
                $latestKeyword = $db->query("SELECT MAX(last_searched) as latest FROM search_keywords")->getRow();
                if ($latestKeyword && $latestKeyword->latest) {
                    // Jika ada keyword baru (lebih baru dari cache timestamp), clear cache
                    if (!$cacheTimestamp || $latestKeyword->latest > $cacheTimestamp) {
                        $cache->delete('popular_search_keywords');
                        $cache->delete('homepage_data'); // Clear homepage_data agar popular_tags ter-update
                        $cache->save('popular_keywords_timestamp', $latestKeyword->latest, 86400);
                        $shouldClearCache = true;
                        log_message('info', 'Cleared cache for new keyword: ' . $latestKeyword->latest);
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('debug', 'Error checking for new keywords: ' . $e->getMessage());
        }
        
        // Cache key for homepage data
        $data = cache_get_or_set('homepage_data', function () {
            // Tambahkan halaman ke histori
            add_page_to_history(
                'Beranda',
                base_url('/'),
                'home',
                ['description' => 'Halaman utama JDIH Kota Padang']
            );

            // Get data with lazy loaded models
            $latest_peraturan = $this->getModel('WebPeraturanModel')->getLatestPeraturan(8);
            $berita = $this->getModel('BeritaModel')->getLatestBerita(3);
            $agenda = $this->getModel('AgendaModel')->getUpcomingAgenda(3);

            // Get popular search keywords (bukan tag dari database)
            // Popular tags sekarang menampilkan keyword pencarian yang sering digunakan
            // JANGAN gunakan cache_get_or_set di sini karena sudah di-clear di atas jika ada keyword baru
            try {
                $searchKeywordModel = $this->getModel('SearchKeywordModel');
                $popular_tags = $searchKeywordModel->getPopularKeywords(5, 1, 90); // 5 keywords, min 1 search, last 90 days
                
                // Debug logging
                log_message('info', 'Popular keywords fetched: ' . count($popular_tags) . ' keywords');
                if (!empty($popular_tags)) {
                    log_message('info', 'Popular keywords: ' . json_encode(array_column($popular_tags, 'nama_tag')));
                }
                
                // Jika tidak ada keyword, fallback ke tag database
                if (empty($popular_tags) || !is_array($popular_tags)) {
                    log_message('info', 'No popular keywords found, using WebTagModel fallback');
                    $fallback = $this->getModel('WebTagModel')->getPopularTags(5);
                    $popular_tags = is_array($fallback) ? $fallback : [];
                }
            } catch (\Exception $e) {
                // Fallback ke tag database jika tabel search_keywords belum ada
                log_message('warning', 'SearchKeywordModel not available, using WebTagModel: ' . $e->getMessage());
                $popular_tags = $this->getModel('WebTagModel')->getPopularTags(5);
            }
            
            // Debug logging untuk melihat data yang dikembalikan
            log_message('info', 'Popular tags passed to view: ' . count($popular_tags) . ' items');
            if (!empty($popular_tags)) {
                log_message('info', 'Popular tags: ' . json_encode(array_column($popular_tags, 'nama_tag')));
            }

            // Get all jenis and status with caching
            $all_jenis = cache_get_or_set('all_jenis_dokumen', function () {
                return $this->getModel('JenisDokumenModel')->orderBy('nama_jenis', 'ASC')->findAll();
            }, 'jenis_peraturan');

            $all_status = cache_get_or_set('all_status_dokumen', function () {
                return $this->getModel('StatusDokumenModel')->orderBy('nama_status', 'ASC')->findAll();
            }, 'status_dokumen');

            // Optimized kategori counts with single query and caching
            $kategori_counts = cache_get_or_set('kategori_counts', function () {
                $db = db_connect();
                $query = "SELECT 
                            CASE 
                                WHEN wjp.kategori_slug IN ('produk-hukum-peraturan', 'produk-hukum-non-peraturan') THEN 'produk-hukum'
                                WHEN wjp.kategori_slug = 'monografi-hukum' THEN 'monografi-hukum'
                                WHEN wjp.kategori_slug = 'artikel-hukum' THEN 'artikel-hukum'
                                WHEN wjp.kategori_slug = 'yurisprudensi' THEN 'yurisprudensi'
                                ELSE 'lainnya'
                            END as kategori_group,
                            COUNT(wp.id_peraturan) as total
                          FROM web_jenis_peraturan wjp
                          LEFT JOIN web_peraturan wp ON wjp.id_jenis_peraturan = wp.id_jenis_dokumen
                          WHERE wjp.kategori_slug IS NOT NULL
                          GROUP BY kategori_group";

                $results = $db->query($query)->getResultArray();

                $counts = ['produk-hukum' => 0, 'monografi-hukum' => 0, 'artikel-hukum' => 0, 'yurisprudensi' => 0];
                foreach ($results as $result) {
                    if (isset($counts[$result['kategori_group']])) {
                        $counts[$result['kategori_group']] = (int)$result['total'];
                    }
                }

                return $counts;
            }, 'kategori_counts');

            // Get current notice
            $currentNotice = $this->getModel('AnnouncementModel')
                ->where('status', 'active')
                ->orderBy('updated_at', 'DESC')
                ->first();

            return [
                'title' => 'Beranda - JDIH',
                'currentPage' => 'home',
                'latest_peraturan' => $latest_peraturan,
                'latest_berita' => $berita, // Renamed for component compatibility
                'agenda' => $agenda,
                'kategori_list' => [
                    ['nama' => 'Produk Hukum', 'slug' => 'produk-hukum', 'icon' => 'fa-landmark'],
                    ['nama' => 'Monografi Hukum', 'slug' => 'monografi-hukum', 'icon' => 'fa-book-open'],
                    ['nama' => 'Artikel Hukum', 'slug' => 'artikel-hukum', 'icon' => 'fa-newspaper'],
                    ['nama' => 'Putusan/Yurisprudensi', 'slug' => 'yurisprudensi', 'icon' => 'fa-balance-scale']
                ],
                'kategori_counts' => $kategori_counts,
                'popular_tags' => $popular_tags,
                'all_jenis' => $all_jenis,
                'all_status' => $all_status,
                'maintenance_notice' => $currentNotice,
            ];
        }, 'homepage_data');



        // OPTIMIZED: Use component-based home page
        return view('frontend/pages/home-optimized', array_merge($this->global_data, $data));
    }

    /**
     * Method untuk testing home page yang sudah dioptimasi
     * Akses via: /home-optimized
     */
    public function homeOptimized()
    {
        // Clear cache untuk testing
        $cache = \Config\Services::cache();
        $cache->delete('homepage_data');

        // Get fresh data without cache
        helper(['page_history']);

        // Tambahkan halaman ke histori
        add_page_to_history(
            'Beranda',
            base_url('/'),
            'home',
            ['description' => 'Halaman utama JDIH Kota Padang']
        );

        // Get data with lazy loaded models
        $latest_peraturan = $this->getModel('WebPeraturanModel')->getLatestPeraturan(8);
        $berita = $this->getModel('BeritaModel')->getLatestBerita(3);
        $agenda = $this->getModel('AgendaModel')->getUpcomingAgenda(3);

        // Get popular search keywords (bukan tag dari database)
        try {
            $searchKeywordModel = $this->getModel('SearchKeywordModel');
            $popular_tags = $searchKeywordModel->getPopularKeywords(5, 2, 90); // 5 keywords, min 2 searches, last 90 days
        } catch (\Exception $e) {
            // Fallback ke tag database jika tabel search_keywords belum ada
            log_message('debug', 'SearchKeywordModel not available, using WebTagModel: ' . $e->getMessage());
            $popular_tags = $this->getModel('WebTagModel')->getPopularTags(5);
        }

        // Get all jenis and status
        $all_jenis = $this->getModel('JenisDokumenModel')->orderBy('nama_jenis', 'ASC')->findAll();
        $all_status = $this->getModel('StatusDokumenModel')->orderBy('nama_status', 'ASC')->findAll();

        // Get kategori counts
        $db = db_connect();
        $query = "SELECT 
                    CASE 
                        WHEN wjp.kategori_slug IN ('produk-hukum-peraturan', 'produk-hukum-non-peraturan') THEN 'produk-hukum'
                        WHEN wjp.kategori_slug = 'monografi-hukum' THEN 'monografi-hukum'
                        WHEN wjp.kategori_slug = 'artikel-hukum' THEN 'artikel-hukum'
                        WHEN wjp.kategori_slug = 'yurisprudensi' THEN 'yurisprudensi'
                        ELSE 'lainnya'
                    END as kategori_group,
                    COUNT(wp.id_peraturan) as total
                  FROM web_jenis_peraturan wjp
                  LEFT JOIN web_peraturan wp ON wjp.id_jenis_peraturan = wp.id_jenis_dokumen
                  WHERE wjp.kategori_slug IS NOT NULL
                  GROUP BY kategori_group";

        $results = $db->query($query)->getResultArray();

        $kategori_counts = ['produk-hukum' => 0, 'monografi-hukum' => 0, 'artikel-hukum' => 0, 'yurisprudensi' => 0];
        foreach ($results as $result) {
            if (isset($kategori_counts[$result['kategori_group']])) {
                $kategori_counts[$result['kategori_group']] = (int)$result['total'];
            }
        }

        // Get current notice
        $currentNotice = $this->getModel('AnnouncementModel')
            ->where('status', 'active')
            ->orderBy('updated_at', 'DESC')
            ->first();

        $data = [
            'title' => 'Beranda - JDIH',
            'currentPage' => 'home',
            'latest_peraturan' => $latest_peraturan,
            'latest_berita' => $berita,
            'agenda' => $agenda,
            'kategori_list' => [
                ['nama' => 'Produk Hukum', 'slug' => 'produk-hukum', 'icon' => 'fa-landmark'],
                ['nama' => 'Monografi Hukum', 'slug' => 'monografi-hukum', 'icon' => 'fa-book-open'],
                ['nama' => 'Artikel Hukum', 'slug' => 'artikel-hukum', 'icon' => 'fa-newspaper'],
                ['nama' => 'Putusan/Yurisprudensi', 'slug' => 'yurisprudensi', 'icon' => 'fa-balance-scale']
            ],
            'kategori_counts' => $kategori_counts,
            'popular_tags' => $popular_tags,
            'all_jenis' => $all_jenis,
            'all_status' => $all_status,
            'maintenance_notice' => $currentNotice,
        ];



        // Use component-based home page
        return view('frontend/pages/home-optimized', array_merge($this->global_data, $data));
    }

    public function peraturan($jenis_slug = null)
    {
        // Jika URL adalah /peraturan/search, abaikan 'search' sebagai slug jenis
        if ($jenis_slug === 'search') {
            $jenis_slug = null;
        }

        $request = service('request');

        // KEAMANAN: Sanitize dan validasi semua input dari GET parameter untuk mencegah XSS dan SQL Injection
        $jenis = $jenis_slug ?? $request->getGet('jenis');
        
        // KEAMANAN: Sanitize jenis untuk mencegah XSS
        if (!empty($jenis)) {
            $jenis = trim($jenis);
            // Hanya izinkan alphanumeric, dash, dan underscore (sama seperti di WebPeraturanModel)
            if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $jenis)) {
                $jenis = ''; // Reject jika tidak valid
            }
        }
        
        $keyword = $request->getGet('keyword');
        
        // KEAMANAN: Sanitize keyword untuk mencegah XSS
        if (!empty($keyword)) {
            $keyword = trim($keyword);
            // Hapus karakter berbahaya dan batasi panjang (sama seperti di WebPeraturanModel)
            $keyword = preg_replace('/[^a-zA-Z0-9\s\-\.\/\(\)]/', '', $keyword);
            $keyword = substr($keyword, 0, 100); // Batasi panjang
            // Jika setelah sanitization menjadi kosong atau terlalu pendek, set ke empty
            if (strlen($keyword) < 2) {
                $keyword = '';
            }
        }
        
        $tahun = $request->getGet('tahun');
        
        // KEAMANAN: Sanitize tahun untuk mencegah XSS
        if (!empty($tahun)) {
            $tahun = trim($tahun);
            // Cast ke integer dan validasi range (sama seperti di WebPeraturanModel)
            $tahun = (int)$tahun;
            if ($tahun < 1900 || $tahun > date('Y') + 10) {
                $tahun = ''; // Reject jika tidak valid
            }
        }
        
        $status = $request->getGet('status');
        
        // KEAMANAN: Sanitize status untuk mencegah XSS
        if (!empty($status)) {
            $status = trim($status);
            // Cast ke integer dan validasi (sama seperti di WebPeraturanModel)
            $status = (int)$status;
            if ($status <= 0) {
                $status = ''; // Reject jika tidak valid
            }
        }
        $tag_slug = $request->getGet('tag'); // Ambil parameter tag
        $sort = $request->getGet('sort') ?? 'terbaru';
        
        // Sanitize tag_slug untuk mencegah XSS
        if (!empty($tag_slug)) {
            // Hapus karakter berbahaya dan batasi panjang
            $tag_slug = trim($tag_slug);
            $tag_slug = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $tag_slug);
            $tag_slug = substr($tag_slug, 0, 100); // Batasi panjang
        }
        
        // Sanitize sort untuk mencegah XSS
        if (!empty($sort)) {
            $sort = trim($sort);
            $allowed_sorts = ['terbaru', 'terlama', 'populer', 'abjad'];
            if (!in_array($sort, $allowed_sorts)) {
                $sort = 'terbaru'; // Default jika tidak valid
            }
        }

        // Filter honeypot parameter (jika ada, abaikan)
        $honeypot = $request->getGet('honeypot');
        if (!empty($honeypot)) {
            // Jika honeypot diisi, kemungkinan ini adalah bot, redirect tanpa parameter
            return redirect()->to(base_url('peraturan'));
        }

        // KEAMANAN: Jangan cache halaman jika user sudah login
        $session = service('session');
        $user = $session->get('user');
        
        // Skip cache untuk search dengan parameter apapun (keyword, jenis, tahun, status, tag, sort)
        // atau jika user sudah login
        // CRITICAL: Semua parameter filter harus di-skip cache agar hasil pencarian selalu fresh
        $hasFilters = !empty($keyword) 
            || !empty($tag_slug) 
            || !empty($jenis) 
            || !empty($tahun) 
            || !empty($status);
        
        $skipCache = $hasFilters || !empty($user);
        
        if (!$skipCache) {
            // Enable full page caching (10 menit - untuk list tanpa search) hanya untuk user yang belum login
            $this->cachePage(600);
        } else {
            // Set no-cache headers untuk semua request dengan filter/search atau user yang login
            $response = service('response');
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
        }

        $filters = compact('keyword', 'jenis', 'tahun', 'status', 'sort');
        $filters['tag'] = $tag_slug; // Simpan slug untuk view

        $modelFilters = $filters; // Buat salinan filter untuk model

        // Konversi slug/kata tag menjadi pencarian berdasarkan konten tag
        // User ingin mencari berdasarkan kata/konten tag, bukan hanya slug exact match
        // KEAMANAN: tag_slug sudah di-sanitize di atas, jadi aman untuk digunakan dalam query
        $tagData = null;
        if (!empty($tag_slug)) {
            $webTagModel = $this->getModel('WebTagModel');
            
            // Cari tag yang nama_tag atau slug_tag mengandung kata yang dicari
            // Ini memungkinkan pencarian fleksibel berdasarkan konten tag
            // CodeIgniter's like() method sudah melakukan escaping otomatis untuk mencegah SQL Injection
            $tagData = $webTagModel->groupStart()
                ->like('nama_tag', $tag_slug)
                ->orLike('slug_tag', $tag_slug)
                ->groupEnd()
                ->findAll();

            if (!empty($tagData)) {
                // Jika ditemukan beberapa tag, kirimkan array ID tag untuk pencarian
                $tagIds = array_column($tagData, 'id_tag');
                $modelFilters['tag'] = $tagIds; // Kirimkan array ID tag
                // Simpan tag pertama untuk view (jika diperlukan)
                $tagData = $tagData[0];
            } else {
                // Jika tidak ditemukan tag, pastikan tidak ada hasil
                $modelFilters['tag'] = [0]; // Array dengan 0 untuk memastikan tidak ada hasil
            }
        }

        // Konversi ID jenis menjadi slug jika diperlukan
        if (!empty($filters['jenis']) && is_numeric($filters['jenis'])) {
            $db = db_connect();
            $jenisData = $db->table('web_jenis_peraturan')->where('id_jenis_peraturan', $filters['jenis'])->get()->getRow();
            if ($jenisData) {
                $filters['jenis'] = $jenisData->slug_jenis;
                $modelFilters['jenis'] = $jenisData->slug_jenis;
            }
        }

        $perPage = 10;

        // Process data - skip cache untuk search (keyword, tag) atau user yang login
        $webPeraturanModel = $this->getModel('WebPeraturanModel');
        $viewData = [];
        
        // Log untuk debugging (hanya di development)
        if (ENVIRONMENT === 'development') {
            log_message('debug', 'Search filters: ' . json_encode($modelFilters));
        }
        
        $viewData['peraturan'] = $webPeraturanModel->searchPeraturan($modelFilters, $perPage);
        $viewData['pager'] = $webPeraturanModel->pager;
        $viewData['filters'] = $filters;
        $viewData['jenis_counts'] = $webPeraturanModel->getPeraturanCountByJenis();
        $viewData['tahun_counts'] = $webPeraturanModel->getPeraturanCountByTahun();
        $viewData['list_tahun'] = $webPeraturanModel->getPeraturanCountByTahun();
        $viewData['jenis_peraturan'] = $this->getModel('JenisDokumenModel')->orderBy('nama_jenis', 'ASC')->findAll();
        $viewData['title'] = 'Peraturan - JDIH';
        $viewData['tag'] = $tagData; // Set tag data
        
        // Track search keyword HANYA jika ada hasil pencarian
        // searchPeraturan() menggunakan paginate() yang mengembalikan array langsung
        if (!empty($keyword) && strlen(trim($keyword)) >= 2) {
            try {
                // searchPeraturan() mengembalikan array hasil dari paginate()
                // Array kosong jika tidak ada hasil, array dengan data jika ada hasil
                $hasResults = !empty($viewData['peraturan']) && is_array($viewData['peraturan']) && count($viewData['peraturan']) > 0;
                
                // Log untuk debugging - SELALU log di production untuk troubleshooting
                $resultCount = is_array($viewData['peraturan']) ? count($viewData['peraturan']) : 0;
                log_message('info', 'Tracking search keyword: ' . $keyword . ', hasResults: ' . ($hasResults ? 'YES' : 'NO') . ', results count: ' . $resultCount);
                
                $searchKeywordModel = $this->getModel('SearchKeywordModel');
                $result = $searchKeywordModel->recordSearch($keyword, $hasResults);
                
                if ($result) {
                    log_message('info', 'Successfully recorded search keyword: ' . $keyword . ' (ID: ' . $result . ')');
                } else {
                    log_message('warning', 'Failed to record search keyword: ' . $keyword . ' (hasResults: ' . ($hasResults ? 'true' : 'false') . ', resultCount: ' . $resultCount . ')');
                }
            } catch (\Exception $e) {
                // Log error dengan detail
                log_message('error', 'Failed to record search keyword: ' . $e->getMessage());
                log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            }
        }

        return view('frontend/pages/peraturan', array_merge($this->global_data, $viewData));
    }

    public function detailPeraturan($slug = null)
    {
        if (is_null($slug)) {
            return redirect()->to(base_url('peraturan'));
        }

        // KEAMANAN: Jangan cache halaman jika user sudah login
        $session = service('session');
        $user = $session->get('user');
        
        // Skip cache untuk POST atau query strings (untuk tracking, dll) atau jika user sudah login
        $skipCache = $this->request->getMethod() === 'post' || !empty($this->request->getGet()) || !empty($user);
        
        if (!$skipCache) {
            // Enable full page caching (30 menit - data tidak sering berubah) hanya untuk user yang belum login
            $this->cachePage(1800);
        } else if ($user) {
            // Set no-cache headers untuk user yang sudah login
            $response = service('response');
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
        }

        // Cache data dengan key berdasarkan slug
        $cacheKey = 'peraturan_detail_' . md5($slug);
        $data = cache_get_or_set($cacheKey, function () use ($slug) {
            $webPeraturanModel = model('WebPeraturanModel');
            $detail = $webPeraturanModel->getPeraturanDetailBySlug($slug);

            if (!$detail) {
                throw PageNotFoundException::forPageNotFound();
            }

            // Increment hits (tetap jalan meskipun di-cache)
            $webPeraturanModel->incrementHits($detail['id_peraturan']);

            return [
                'title' => $detail['judul'] . ' - JDIH',
                'peraturan' => $detail,
                'relatedPeraturan' => $webPeraturanModel->getRelasiPeraturan($detail['id_peraturan']),
                'peraturan_populer' => $webPeraturanModel->getLatestPeraturan(5),
                'tag' => $webPeraturanModel->getPeraturanTags($detail['id_peraturan']),
                'lampiran' => $webPeraturanModel->getPeraturanLampiran($detail['id_peraturan']),
                'metadata_labels' => [
                    'tipe_dokumen' => 'Tipe Dokumen',
                    'jenis_dokumen' => 'Jenis Dokumen',
                    'nomor_dokumen' => 'Nomor Dokumen',
                    'tahun_dokumen' => 'Tahun Dokumen',
                    'tanggal_penetapan' => 'Tanggal Penetapan',
                    'tanggal_pengundangan' => 'Tanggal Pengundangan',
                    'tempat_penetapan' => 'Tempat Penetapan',
                    'penandatangan' => 'Penandatangan',
                    'teu' => 'Tajuk Entri Utama (T.E.U)',
                    'bidang_hukum' => 'Bidang Hukum',
                    'sumber' => 'Sumber',
                    'pemrakarsa' => 'Pemrakarsa',
                    'subjek' => 'Subjek',
                    'status' => 'Status',
                ]
            ];
        }, 1800); // Cache 30 menit

        return view('frontend/pages/peraturan-detail', array_merge($this->global_data, $data));
    }

    public function berita($kategori_slug = null)
    {
        $kategoriModel = $this->getModel('WebBeritaKategoriModel');
        $request = service('request');
        $keyword = $request->getGet('q');
        $arsip_filter = $request->getGet('arsip');

        $kategori_id = null;
        $kategori_nama = 'Semua Kategori';

        if ($kategori_slug) {
            $kategori_data = $kategoriModel->where('slug_kategori', $kategori_slug)->first();
            if ($kategori_data) {
                $kategori_id = $kategori_data['id'];
                $kategori_nama = $kategori_data['nama_kategori'];
            } else {
                throw PageNotFoundException::forPageNotFound('Kategori berita tidak ditemukan.');
            }
        }

        $beritaModel = $this->getModel('BeritaModel');
        $beritaQuery = $beritaModel->getBeritaPaginated($kategori_slug);

        if ($keyword) {
            $beritaQuery->like('judul', $keyword);
        }
        if ($arsip_filter && preg_match('/^\d{4}-\d{2}$/', $arsip_filter)) {
            $beritaQuery->where("DATE_FORMAT(tanggal_publish, '%Y-%m') =", $arsip_filter);
        }

        $db = \Config\Database::connect();
        $arsip = $db->query("SELECT DATE_FORMAT(tanggal_publish, '%Y-%m') as bulan, DATE_FORMAT(tanggal_publish, '%M %Y') as label, COUNT(*) as jumlah FROM web_berita WHERE status = 'published' GROUP BY DATE_FORMAT(tanggal_publish, '%Y-%m'), DATE_FORMAT(tanggal_publish, '%M %Y') ORDER BY bulan DESC")->getResultArray();

        $data = [
            'title' => ($kategori_nama !== 'Semua Kategori' ? 'Berita: ' . $kategori_nama : 'Berita Terbaru') . ' - JDIH',
            'currentPage' => 'berita',
            'berita' => $beritaQuery->paginate(9, 'default'),
            'pager' => $beritaModel->pager,
            'kategori' => $kategoriModel->getAllKategori(),
            'berita_populer' => $beritaModel->where('status', 'published')->orderBy('view_count', 'DESC')->limit(5)->find(),
            'arsip_bulan' => $arsip,
            'kategori_nama' => $kategori_nama,
            'current_kategori_id' => $kategori_id,
        ];

        return view('frontend/pages/berita', array_merge($this->global_data, $data));
    }

    public function detailBerita($slug = null)
    {
        // KEAMANAN: Jangan cache halaman jika user sudah login
        $session = service('session');
        $user = $session->get('user');
        $isAdmin = $user && !empty($user['id_user']);
        
        // Skip cache untuk admin, POST/query strings, atau user yang sudah login
        $skipCache = $isAdmin || $this->request->getMethod() === 'post' || !empty($this->request->getGet()) || !empty($user);
        
        if (!$skipCache) {
            // Enable full page caching (30 menit - data tidak sering berubah) hanya untuk user yang belum login
            $this->cachePage(1800);
        } else if ($user) {
            // Set no-cache headers untuk user yang sudah login
            $response = service('response');
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
        }

        // Cache data dengan key berdasarkan slug
        $cacheKey = 'berita_detail_' . md5($slug) . ($isAdmin ? '_admin' : '');
        $data = cache_get_or_set($cacheKey, function () use ($slug, $isAdmin) {
            $beritaModel = model('BeritaModel');

            // Cek apakah admin yang sedang login
            if ($isAdmin) {
                $berita = $beritaModel->getAnyBeritaBySlug($slug);
            } else {
                $berita = $beritaModel->getBeritaBySlug($slug);
            }

            // Jika berita tidak ditemukan untuk slug yang diberikan, tampilkan 404
            if (empty($berita)) {
                throw PageNotFoundException::forPageNotFound('Halaman berita tidak ditemukan.');
            }

            // Hanya increment view count untuk pengunjung biasa pada artikel yang sudah publish
            if (!$isAdmin && $berita['status'] == 'published') {
                $beritaModel->incrementViewCount($berita['id']);
            }

            $kategoriModel = model('WebBeritaKategoriModel');
            $kategori_berita = $kategoriModel->findAll();

            $beritaLainnya = $beritaModel->where('id !=', $berita['id'])
                ->where('status', 'published')
                ->orderBy('tanggal_publish', 'DESC')
                ->limit(5)
                ->find();

            return [
                'title'         => $berita['judul'] . ' - JDIH',
                'currentPage'   => 'berita',
                'berita'        => $berita,
                'kategori_list' => $kategori_berita,
                'beritaLainnya' => $beritaLainnya,
            ];
        }, 1800); // Cache 30 menit

        return view('frontend/pages/berita_detail', array_merge($this->global_data, $data));
    }

    public function agenda()
    {
        $request = service('request');
        $isCalendarView = $request->getGet('view') === 'calendar';
        $filterTahun = $request->getGet('tahun');
        $filterBulan = $request->getGet('bulan');
        
        // KEAMANAN: Jangan cache halaman jika user sudah login
        $session = service('session');
        $user = $session->get('user');
        
        // CRITICAL: Jangan cache halaman agenda karena JavaScript dan filter perlu selalu fresh
        // Page cache CodeIgniter meng-cache seluruh HTML termasuk JavaScript inline,
        // sehingga JavaScript tidak ter-update dan filter/calendar tidak berfungsi
        // Skip cache untuk SEMUA request agenda (termasuk tanpa filter) untuk memastikan JavaScript selalu fresh
        $skipCache = true; // Selalu skip cache untuk agenda page
        
        // Set no-cache headers untuk semua request agenda
        $response = service('response');
        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', '0');
        $response->setHeader('X-Cache-Status', 'DISABLED');
        
        // JANGAN gunakan cachePage() untuk agenda karena akan meng-cache JavaScript inline
        // if (!$skipCache) {
        //     $this->cachePage(300); // DISABLED - menyebabkan JavaScript tidak ter-update
        // }

        $agendaModel = $this->getModel('AgendaModel');
        
        // Log untuk debugging - Hanya di development/staging, tidak di production
        // AMAN: Debug log tidak akan ditulis di production karena threshold = 4 (hanya error)
        if (ENVIRONMENT !== 'production') {
            log_message('debug', 'Agenda page accessed - View: ' . ($isCalendarView ? 'calendar' : 'list') . ', Tahun: ' . ($filterTahun ?? 'none') . ', Bulan: ' . ($filterBulan ?? 'none'));
        }

        try {
            // Build query with filters - Remove status filter if column doesn't exist
            $query = $agendaModel;

            if ($filterTahun) {
                $query = $query->where('YEAR(tanggal_mulai)', $filterTahun);
            }
            if ($filterBulan) {
                $query = $query->where('MONTH(tanggal_mulai)', $filterBulan);
            }

            $agendas = [];
            $data = [];
            $data['events_json'] = '[]'; // Default empty array untuk calendar view

            if ($isCalendarView) {
                // For calendar view, get all events (no pagination)
                try {
                    // Pastikan hanya mengambil agenda yang memiliki tanggal_mulai yang valid
                    $agendas = $query->where('tanggal_mulai IS NOT NULL')
                        ->where('tanggal_mulai !=', '')
                        ->where('tanggal_mulai !=', '0000-00-00')
                        ->orderBy('tanggal_mulai', 'ASC')
                        ->findAll();
                    
                    // Log untuk debugging - Hanya di development/staging
                    if (ENVIRONMENT !== 'production') {
                        log_message('debug', 'Agenda calendar view: Found ' . count($agendas) . ' agendas with valid dates');
                    }

                    // Format events for FullCalendar
                    // FullCalendar membutuhkan format ISO 8601: YYYY-MM-DD atau YYYY-MM-DDTHH:mm:ss
                    $events = [];
                    foreach ($agendas as $agenda) {
                        // Pastikan semua field yang diperlukan ada
                        $startDate = $agenda['tanggal_mulai'] ?? '';
                        
                        // Validasi dan format tanggal mulai
                        if (empty($startDate)) {
                            // Log hanya di development/staging
                            if (ENVIRONMENT !== 'production') {
                                log_message('debug', 'Agenda ID ' . ($agenda['id'] ?? 'unknown') . ' tidak memiliki tanggal_mulai');
                            }
                            continue; // Skip agenda tanpa tanggal
                        }
                        
                        // Pastikan format tanggal adalah YYYY-MM-DD
                        try {
                            $dateObj = new \DateTime($startDate);
                            $startDate = $dateObj->format('Y-m-d');
                        } catch (\Exception $e) {
                            log_message('error', 'Format tanggal_mulai tidak valid untuk agenda ID ' . ($agenda['id'] ?? 'unknown') . ': ' . $startDate);
                            continue; // Skip agenda dengan format tanggal tidak valid
                        }
                        
                        // Format waktu mulai (HH:mm:ss atau HH:mm)
                        $startTime = '';
                        if (!empty($agenda['waktu_mulai'])) {
                            // Pastikan format waktu adalah HH:mm:ss atau HH:mm
                            $waktu_mulai = trim($agenda['waktu_mulai']);
                            // Jika hanya HH:mm, tambahkan :00 untuk detik
                            if (preg_match('/^\d{2}:\d{2}$/', $waktu_mulai)) {
                                $waktu_mulai .= ':00';
                            }
                            // Validasi format waktu
                            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $waktu_mulai)) {
                                $startTime = 'T' . $waktu_mulai;
                            } else {
                                log_message('debug', 'Format waktu_mulai tidak valid untuk agenda ID ' . ($agenda['id'] ?? 'unknown') . ': ' . $waktu_mulai);
                            }
                        }
                        
                        $start = $startDate . $startTime;
                        
                        // Format tanggal dan waktu selesai
                        $end = null;
                        if (!empty($agenda['tanggal_selesai'])) {
                            try {
                                $endDateObj = new \DateTime($agenda['tanggal_selesai']);
                                $endDate = $endDateObj->format('Y-m-d');
                                
                                // Jika tanggal selesai berbeda dengan tanggal mulai, atau jika ada waktu selesai
                                if ($endDate !== $startDate || !empty($agenda['waktu_selesai'])) {
                                    $endTime = '';
                                    if (!empty($agenda['waktu_selesai'])) {
                                        $waktu_selesai = trim($agenda['waktu_selesai']);
                                        // Jika hanya HH:mm, tambahkan :00 untuk detik
                                        if (preg_match('/^\d{2}:\d{2}$/', $waktu_selesai)) {
                                            $waktu_selesai .= ':00';
                                        }
                                        // Validasi format waktu
                                        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $waktu_selesai)) {
                                            $endTime = 'T' . $waktu_selesai;
                                        }
                                    }
                                    $end = $endDate . $endTime;
                                }
                            } catch (\Exception $e) {
                                log_message('error', 'Format tanggal_selesai tidak valid untuk agenda ID ' . ($agenda['id'] ?? 'unknown') . ': ' . ($agenda['tanggal_selesai'] ?? ''));
                            }
                        }
                        
                        $events[] = [
                            'title' => $agenda['judul_agenda'] ?? 'Agenda',
                            'start' => $start,
                            'end' => $end,
                            'url' => base_url('agenda/' . ($agenda['slug'] ?? '')),
                            'extendedProps' => [
                                'location' => $agenda['lokasi'] ?? '',
                                'description' => $agenda['deskripsi_singkat'] ?? ''
                            ]
                        ];
                    }
                    
                    // Log untuk debugging
                    log_message('debug', 'Calendar events formatted: ' . count($events) . ' events dari ' . count($agendas) . ' agenda');

                    $data['events_json'] = json_encode($events, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    
                    // Log untuk debugging - Hanya di development/staging
                    if (ENVIRONMENT !== 'production') {
                        log_message('debug', 'Calendar events JSON generated: ' . count($events) . ' events');
                        if (empty($events)) {
                            log_message('warning', 'No calendar events found - agendas count: ' . count($agendas));
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Error formatting calendar events: ' . $e->getMessage());
                    log_message('error', 'Stack trace: ' . $e->getTraceAsString());
                    $data['events_json'] = '[]';
                }
            } else {
                // For list view, use pagination
                $agendas = $query->orderBy('tanggal_mulai', 'ASC')->paginate(12);
                $data['pager'] = $agendaModel->pager;
            }

            // Get years for filter dropdown - with error handling
            $tahun_list = [];
            try {
                $tahun_list = $agendaModel->select('YEAR(tanggal_mulai) as tahun')
                    ->distinct()
                    ->orderBy('tahun', 'DESC')
                    ->findAll();
            } catch (\Exception $e) {
                log_message('error', 'Error getting years for agenda filter: ' . $e->getMessage());
            }

            $data = array_merge($data, [
                'title' => 'Agenda Kegiatan - JDIH',
                'currentPage' => 'agenda',
                'agendas' => $agendas,
                'isCalendarView' => $isCalendarView,
                'filterTahun' => $filterTahun,
                'filterBulan' => $filterBulan,
                'tahun_list' => $tahun_list,
            ]);
            
            // Pastikan events_json selalu ada untuk calendar view
            if ($isCalendarView && !isset($data['events_json'])) {
                $data['events_json'] = '[]';
            }

            // Log sebelum return view - Hanya di development/staging
            if (ENVIRONMENT !== 'production') {
                log_message('debug', 'Agenda view data prepared - isCalendarView: ' . ($isCalendarView ? 'yes' : 'no') . ', agendas count: ' . count($agendas ?? []));
            }
            
            return view('frontend/pages/agenda', array_merge($this->global_data, $data));
        } catch (\Exception $e) {
            log_message('error', 'Error in agenda method: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            // Return error view with fallback data
            $data = [
                'title' => 'Agenda Kegiatan - JDIH',
                'currentPage' => 'agenda',
                'agendas' => [],
                'isCalendarView' => $isCalendarView ?? false,
                'filterTahun' => $filterTahun ?? null,
                'filterBulan' => $filterBulan ?? null,
                'tahun_list' => [],
                'events_json' => '[]', // Pastikan events_json selalu ada
                'error' => 'Terjadi kesalahan saat memuat agenda. Silakan coba lagi nanti.'
            ];

            return view('frontend/pages/agenda', array_merge($this->global_data, $data));
        }
    }

    public function detailAgenda($slug = null)
    {
        if (is_null($slug)) {
            return redirect()->to(base_url('agenda'));
        }

        // KEAMANAN: Jangan cache halaman jika user sudah login
        $session = service('session');
        $user = $session->get('user');
        
        // Skip cache untuk POST atau query strings atau jika user sudah login
        $skipCache = $this->request->getMethod() === 'post' || !empty($this->request->getGet()) || !empty($user);
        
        if (!$skipCache) {
            // Enable full page caching (15 menit - agenda bisa berubah) hanya untuk user yang belum login
            $this->cachePage(900);
        } else if ($user) {
            // Set no-cache headers untuk user yang sudah login
            $response = service('response');
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
        }

        // Cache data dengan key berdasarkan slug
        $cacheKey = 'agenda_detail_' . md5($slug);
        $data = cache_get_or_set($cacheKey, function () use ($slug) {
            $agendaModel = model('AgendaModel');
            $agenda = $agendaModel->where('slug', $slug)->first();

            if (!$agenda) {
                throw PageNotFoundException::forPageNotFound('Agenda tidak ditemukan.');
            }

            // Format tanggal dan waktu untuk display
            if ($agenda['tanggal_mulai']) {
                try {
                    $tanggal_mulai = new \DateTime($agenda['tanggal_mulai']);
                    $agenda['tanggal_display'] = $tanggal_mulai->format('d F Y');

                    if ($agenda['tanggal_selesai'] && $agenda['tanggal_selesai'] !== $agenda['tanggal_mulai']) {
                        $tanggal_selesai = new \DateTime($agenda['tanggal_selesai']);
                        $agenda['tanggal_display'] .= ' - ' . $tanggal_selesai->format('d F Y');
                    }
                } catch (\Exception $e) {
                    $agenda['tanggal_display'] = 'Tanggal tidak valid';
                }
            } else {
                $agenda['tanggal_display'] = 'Tanggal belum ditentukan';
            }

            // Format waktu
            $waktu_display = '';
            if ($agenda['waktu_mulai']) {
                $waktu_display = substr($agenda['waktu_mulai'], 0, 5);
                if ($agenda['waktu_selesai'] && $agenda['waktu_selesai'] !== '00:00:00') {
                    $waktu_display .= ' - ' . substr($agenda['waktu_selesai'], 0, 5);
                }
                $waktu_display .= ' WIB';
            } else {
                $waktu_display = 'Waktu akan diinformasikan';
            }
            $agenda['waktu_display'] = $waktu_display;

            return [
                'title' => $agenda['judul_agenda'] . ' - JDIH',
                'currentPage' => 'agenda',
                'agenda' => $agenda,
            ];
        }, 900); // Cache 15 menit

        return view('frontend/pages/agenda-detail', array_merge($this->global_data, $data));
    }

    public function statistik()
    {
        // KEAMANAN: Jangan cache halaman jika user sudah login
        // Karena navbar menampilkan data user yang berbeda per session
        $session = service('session');
        $user = $session->get('user');
        
        // Hanya cache jika user belum login (untuk performa)
        if (!$user) {
            // Enable full page caching (10 menit) hanya untuk user yang belum login
            $this->cachePage(600);
        } else {
            // Set no-cache headers untuk user yang sudah login
            $response = service('response');
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
        }
        
        try {
            // Cache statistik data dengan wrapper
            $data = cache_get_or_set('statistik_data', function () {
                // HAPUS: VisitorStatsModel tidak digunakan di halaman statistik
                // Query ke traffic table hanya membuang waktu dan menyebabkan timeout
                $webPeraturanModel = $this->getModel('WebPeraturanModel');
                // HAPUS: BeritaModel dan AgendaModel juga tidak digunakan di view statistik

            // Get peraturan statistics with error handling
            $peraturan_stats = [];
            try {
                $peraturan_stats = $webPeraturanModel->getPeraturanStats();
            } catch (\Exception $e) {
                log_message('error', 'Error getting peraturan stats: ' . $e->getMessage());
                $peraturan_stats = ['total_peraturan' => 0, 'total_hits' => 0, 'total_downloads' => 0, 'by_status' => []];
            }

            // Get data for charts and statistics
            $jenis_counts = [];
            $tahunan_data = [];
            try {
                $jenis_counts = $webPeraturanModel->getPeraturanCountByJenis();
                $tahunan_data = $webPeraturanModel->getPeraturanCountByTahun();
            } catch (\Exception $e) {
                log_message('error', 'Error getting peraturan counts: ' . $e->getMessage());
            }

            $status_counts = $peraturan_stats['by_status'] ?? [];

            // Calculate totals
            $total_dokumen = $peraturan_stats['total_peraturan'] ?? 0;
            $total_hits = $peraturan_stats['total_hits'] ?? 0;
            $total_unduhan = $peraturan_stats['total_downloads'] ?? 0;

            // Find year with most documents
            $tahun_terbanyak = ['tahun' => '-', 'jumlah' => 0];
            if (!empty($tahunan_data)) {
                $max_item = array_reduce($tahunan_data, function ($max, $item) {
                    return ($item['jumlah'] > $max['jumlah']) ? $item : $max;
                }, ['jumlah' => 0]);
                $tahun_terbanyak = $max_item;
            }

            // Get popular documents
            $peraturan_populer = [];
            try {
                $peraturan_populer = $webPeraturanModel->getPopularPeraturan(5);
            } catch (\Exception $e) {
                log_message('error', 'Error getting popular peraturan: ' . $e->getMessage());
            }

            // OPTIMASI: Hapus double caching - model sudah handle cache
            // Get instansi statistics - model sudah di-cache, tidak perlu cache lagi di controller
            $instansi_stats_raw = $webPeraturanModel->getPeraturanCountByInstansi();
            $instansi_stats = [];
            foreach ($instansi_stats_raw as $row) {
                // Skip empty, null, or invalid nama_instansi
                if (
                    !empty($row['nama_instansi']) &&
                    $row['nama_instansi'] !== '' &&
                    $row['nama_instansi'] !== null &&
                    $row['jumlah'] > 0
                ) {
                    $instansi_stats[$row['nama_instansi']] = (int)$row['jumlah'];
                }
            }

            // OPTIMASI: Get jenis peraturan data for current year - model sudah di-cache
            // Hapus double caching di controller, gunakan cache dari model
            $current_year = date('Y');
            $jenis_current_year_raw = $webPeraturanModel->getPeraturanCountByJenisForYear($current_year);
            $jenis_current_year_data = [];

            foreach ($jenis_current_year_raw as $row) {
                if (!empty($row['nama_jenis']) && $row['jumlah'] > 0) {
                    $jenis_current_year_data[$row['nama_jenis']] = (int)$row['jumlah'];
                }
            }

            // OPTIMASI: Cache latest year query untuk menghindari query berulang
            // If no data for current year, try to get data for the most recent year
            if (empty($jenis_current_year_data)) {
                // Cache latest year query (jarang berubah)
                $latest_year = cache_get_or_set('stat_latest_year', function () use ($webPeraturanModel) {
                    return $webPeraturanModel->select('tahun')
                        ->where('is_published', 1)
                        ->where('tahun IS NOT NULL')
                        ->orderBy('tahun', 'DESC')
                        ->first();
                }, 3600); // Cache 1 jam

                if ($latest_year && !empty($latest_year['tahun'])) {
                    $latest_year_value = $latest_year['tahun'];

                    // Model sudah handle cache untuk getPeraturanCountByJenisForYear
                    $jenis_latest_year_raw = $webPeraturanModel->getPeraturanCountByJenisForYear($latest_year_value);
                    foreach ($jenis_latest_year_raw as $row) {
                        if (!empty($row['nama_jenis']) && $row['jumlah'] > 0) {
                            $jenis_current_year_data[$row['nama_jenis']] = (int)$row['jumlah'];
                        }
                    }

                    // Update current year to latest year for display
                    $current_year = $latest_year_value;
                }
            }

            // Cache already handled by cache_get_or_set above

            // HAPUS: Query ke traffic table tidak diperlukan karena tidak digunakan di view
            // Halaman statistik hanya menampilkan data peraturan, bukan data traffic
            // Ini akan meningkatkan performa dan menghindari timeout
            $stats = [
                'peraturan_stats' => $peraturan_stats,
                'popular_peraturan' => $peraturan_populer,
            ];

            // Palet warna
            $color_palette = [
                'rgba(255, 99, 132, 0.7)',   // merah
                'rgba(54, 162, 235, 0.7)',   // biru
                'rgba(255, 206, 86, 0.7)',   // kuning
                'rgba(75, 192, 192, 0.7)',   // hijau
                'rgba(153, 102, 255, 0.7)',  // ungu
                'rgba(255, 159, 64, 0.7)',   // oranye
                'rgba(13, 202, 240, 0.7)',   // biru muda
                'rgba(220, 53, 69, 0.7)',    // merah tua
                'rgba(40, 167, 69, 0.7)',    // hijau tua
                'rgba(255, 87, 34, 0.7)',    // oranye tua
                'rgba(0, 123, 255, 0.7)',    // biru gelap
                'rgba(255, 193, 7, 0.7)',    // kuning terang
            ];

            // Chart Tahunan - dengan fallback jika data kosong
            $chart_colors_tahunan = [];
            if (!empty($tahunan_data) && is_array($tahunan_data)) {
                $i = 0;
                foreach ($tahunan_data as $item) {
                    $chart_colors_tahunan[] = $color_palette[$i % count($color_palette)];
                    $i++;
                }
            }
            
            // Chart Jenis - dengan fallback jika data kosong
            $chart_colors_jenis = [];
            if (!empty($jenis_counts) && is_array($jenis_counts)) {
                $i = 0;
                foreach ($jenis_counts as $item) {
                    $chart_colors_jenis[] = $color_palette[$i % count($color_palette)];
                    $i++;
                }
            }
            
            // Chart Status - dengan fallback jika data kosong
            $chart_colors_status = [];
            if (!empty($status_counts) && is_array($status_counts)) {
                $i = 0;
                // Jika status_counts adalah associative array (key => value)
                if (array_keys($status_counts) !== range(0, count($status_counts) - 1)) {
                    foreach ($status_counts as $status => $count) {
                        $chart_colors_status[] = $color_palette[$i % count($color_palette)];
                        $i++;
                    }
                } else {
                    // Jika status_counts adalah indexed array
                    foreach ($status_counts as $item) {
                        $chart_colors_status[] = $color_palette[$i % count($color_palette)];
                        $i++;
                    }
                }
            }
            
            // Chart Instansi - dengan fallback jika data kosong
            $chart_colors_instansi = [];
            if (!empty($instansi_stats) && is_array($instansi_stats)) {
                $i = 0;
                foreach (array_keys($instansi_stats) as $item) {
                    $chart_colors_instansi[] = $color_palette[$i % count($color_palette)];
                    $i++;
                }
            }

                return [
                    'title' => 'Statistik - JDIH',
                    'currentPage' => 'statistik',
                    'stats' => $stats,
                    // Variables for the view - dengan default values untuk mencegah undefined variable
                    'jenis_counts' => $jenis_counts ?? [],
                    'tahunan_data' => $tahunan_data ?? [],
                    'status_counts' => $status_counts ?? [],
                    'total_dokumen' => $total_dokumen ?? 0,
                    'total_hits' => $total_hits ?? 0,
                    'total_unduhan' => $total_unduhan ?? 0,
                    'tahun_terbanyak' => $tahun_terbanyak ?? ['tahun' => '-', 'jumlah' => 0],
                    'peraturan_populer' => $peraturan_populer ?? [],
                    // New statistics variables
                    'instansi_stats' => $instansi_stats ?? [],
                    'jenis_current_year_data' => $jenis_current_year_data ?? [],
                    'current_year_display' => $current_year ?? date('Y'),
                    'chart_colors_tahunan' => $chart_colors_tahunan ?? [],
                    'chart_colors_jenis' => $chart_colors_jenis ?? [],
                    'chart_colors_status' => $chart_colors_status ?? [],
                    'chart_colors_instansi' => $chart_colors_instansi ?? [],
                ];
            }, 600); // Cache 10 menit
            
            return view('frontend/pages/statistik', array_merge($this->global_data, $data));
        } catch (\Exception $e) {
            log_message('error', 'Error loading statistics: ' . $e->getMessage());

            $data = [
                'title' => 'Statistik - JDIH',
                'currentPage' => 'statistik',
                'error' => 'Terjadi kesalahan saat memuat statistik. Silakan coba lagi nanti.',
            ];

            return view('frontend/pages/statistik_error', array_merge($this->global_data, $data));
        }
    }

    public function tentang()
    {
        // KEAMANAN: Jangan cache halaman jika user sudah login
        // Karena navbar menampilkan data user yang berbeda per session
        $session = service('session');
        $user = $session->get('user');
        
        // Validasi user benar-benar ada dan memiliki data yang valid
        $isUserLoggedIn = !empty($user) && is_array($user) && !empty($user['id_user']) && !empty($user['nama']);
        
        // CRITICAL: Jika user login, pastikan tidak ada cache yang digunakan
        if ($isUserLoggedIn) {
            // Set no-cache headers untuk user yang sudah login
            // Headers ini akan mencegah browser/CDN cache halaman
            $response = service('response');
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
            $response->setHeader('X-Accel-Expires', '0'); // Nginx specific
            $response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
            $response->setHeader('ETag', md5($user['id_user'] . time())); // Unique ETag per user session
            
            // CRITICAL: Jangan panggil cachePage() sama sekali untuk user yang login
            // CodeIgniter cachePage() akan di-skip otomatis jika headers no-cache sudah di-set
        } else {
            // Hanya cache jika user belum login (untuk performa)
            // Enable full page caching (1 jam) hanya untuk user yang belum login
            $this->cachePage(3600);
        }
        
        $data = [
            'title' => 'Tentang - JDIH',
            'currentPage' => 'tentang',
        ];

        return view('frontend/pages/tentang', array_merge($this->global_data, $data));
    }

    public function kebijakanPrivasi()
    {
        // KEAMANAN: Jangan cache halaman jika user sudah login
        $session = service('session');
        $user = $session->get('user');
        
        if (!$user) {
            $this->cachePage(3600);
        } else {
            $response = service('response');
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
        }
        
        $data = [
            'title' => 'Kebijakan Privasi - JDIH',
            'currentPage' => 'kebijakan-privasi',
        ];

        return view('frontend/pages/kebijakan-privasi', array_merge($this->global_data, $data));
    }

    public function syaratKetentuan()
    {
        // KEAMANAN: Jangan cache halaman jika user sudah login
        $session = service('session');
        $user = $session->get('user');
        
        if (!$user) {
            $this->cachePage(3600);
        } else {
            $response = service('response');
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
        }
        
        $data = [
            'title' => 'Syarat & Ketentuan - JDIH',
            'currentPage' => 'syarat-ketentuan',
        ];

        return view('frontend/pages/syarat-ketentuan', array_merge($this->global_data, $data));
    }

    public function panduan()
    {
        // KEAMANAN: Jangan cache halaman jika user sudah login
        $session = service('session');
        $user = $session->get('user');
        
        if (!$user) {
            $this->cachePage(1800);
        } else {
            $response = service('response');
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
        }
        
        $data = [
            'title' => 'Panduan Pengguna - JDIH',
            'currentPage' => 'panduan',
        ];

        return view('frontend/pages/panduan', array_merge($this->global_data, $data));
    }

    public function panduanHarmonisasi()
    {
        // KEAMANAN: Jangan cache halaman jika user sudah login
        $session = service('session');
        $user = $session->get('user');
        
        if (!$user) {
            $this->cachePage(1800);
        } else {
            $response = service('response');
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
        }
        
        $data = [
            'title' => 'Panduan Harmonisasi Peraturan - JDIH',
            'currentPage' => 'panduan-harmonisasi',
        ];

        return view('frontend/pages/panduan_harmonisasi', array_merge($this->global_data, $data));
    }



    public function strukturOrganisasi()
    {
        // KEAMANAN: Jangan cache halaman jika user sudah login
        $session = service('session');
        $user = $session->get('user');
        
        if (!$user) {
            $this->cachePage(3600);
        } else {
            $response = service('response');
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
        }
        
        $data = [
            'title' => 'Struktur Organisasi - JDIH',
            'currentPage' => 'struktur-organisasi',
        ];

        return view('frontend/pages/struktur-organisasi', array_merge($this->global_data, $data));
    }

    public function sop()
    {
        // KEAMANAN: Jangan cache halaman jika user sudah login
        $session = service('session');
        $user = $session->get('user');
        
        if (!$user) {
            $this->cachePage(1800);
        } else {
            $response = service('response');
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
        }
        
        $data = [
            'title' => 'SOP - JDIH',
            'currentPage' => 'sop',
        ];

        return view('frontend/pages/sop', array_merge($this->global_data, $data));
    }

    public function kontak()
    {
        $request = service('request');

        if ($request->getMethod() === 'post') {
            // Rate limiting - max 3 submissions per 10 minutes per IP
            $throttler = service('throttler');
            $ipAddress = $request->getIPAddress();

            if (!$throttler->check($ipAddress . '_kontak', 3, 600)) {
                session()->setFlashdata('error', 'Terlalu banyak percobaan. Silakan coba lagi dalam 10 menit.');
                return redirect()->to(base_url('kontak'));
            }

            // Honeypot validation - if filled, it's likely a bot
            if (!empty($request->getPost('website'))) {
                log_message('warning', 'Bot detected via honeypot from IP: ' . $ipAddress);
                session()->setFlashdata('error', 'Terjadi kesalahan. Silakan coba lagi.');
                return redirect()->to(base_url('kontak'));
            }

            $validation = \Config\Services::validation();

            $validation->setRules([
                'nama' => 'required|min_length[2]|max_length[100]|alpha_space',
                'email' => 'required|valid_email|max_length[100]',
                'telepon' => 'permit_empty|max_length[20]|numeric',
                'subjek' => 'required|max_length[200]',
                'pesan' => 'required|min_length[10]|max_length[1000]',
                'g-recaptcha-response' => 'required|verify_recaptcha'
            ], [
                'nama' => [
                    'required' => 'Nama wajib diisi',
                    'min_length' => 'Nama minimal 2 karakter',
                    'max_length' => 'Nama maksimal 100 karakter',
                    'alpha_space' => 'Nama hanya boleh berisi huruf dan spasi'
                ],
                'email' => [
                    'required' => 'Email wajib diisi',
                    'valid_email' => 'Format email tidak valid',
                    'max_length' => 'Email maksimal 100 karakter'
                ],
                'telepon' => [
                    'max_length' => 'Nomor telepon maksimal 20 karakter',
                    'numeric' => 'Nomor telepon hanya boleh berisi angka'
                ],
                'subjek' => [
                    'required' => 'Subjek wajib diisi',
                    'max_length' => 'Subjek maksimal 200 karakter'
                ],
                'pesan' => [
                    'required' => 'Pesan wajib diisi',
                    'min_length' => 'Pesan minimal 10 karakter',
                    'max_length' => 'Pesan maksimal 1000 karakter'
                ],
                'g-recaptcha-response' => [
                    'required' => 'Silakan verifikasi reCAPTCHA',
                    'verify_recaptcha' => 'Verifikasi reCAPTCHA gagal, silakan coba lagi'
                ]
            ]);

            if ($validation->withRequest($request)->run()) {
                try {
                    $kontakModel = $this->getModel('KontakPesanModel');

                    $data = [
                        'nama' => trim(strip_tags($request->getPost('nama'))),
                        'email' => trim(strtolower($request->getPost('email'))),
                        'telepon' => trim(preg_replace('/[^0-9+\-\s]/', '', $request->getPost('telepon'))),
                        'subjek' => trim(strip_tags($request->getPost('subjek'))),
                        'pesan' => trim(strip_tags($request->getPost('pesan'))),
                        'ip_address' => $request->getIPAddress(),
                        'user_agent' => substr($request->getUserAgent()->getAgentString(), 0, 255),
                        'created_at' => date('Y-m-d H:i:s'),
                    ];

                    $kontakModel->insert($data);

                    // Log successful contact form submission
                    log_message('info', 'Contact form submitted successfully from IP: ' . $ipAddress . ' Email: ' . $data['email']);

                    session()->setFlashdata('success', 'Pesan Anda telah berhasil dikirim. Terima kasih!');
                    return redirect()->to(base_url('kontak'));
                } catch (\Exception $e) {
                    log_message('error', 'Error saving contact message: ' . $e->getMessage());
                    session()->setFlashdata('error', 'Terjadi kesalahan saat mengirim pesan. Silakan coba lagi.');
                }
            }
        }

        $data = [
            'title' => 'Kontak - JDIH',
            'currentPage' => 'kontak',
            'validation' => \Config\Services::validation(),
            'config' => [
                'alamat' => 'Jl. Bagindo Aziz Chan No. 1, Padang, Sumatera Barat',
                'telepon' => '081169112112',
                'email' => 'bagianhukum@padang.go.id',
                'maps' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.6747!2d100.3543!3d-0.9492!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2fd4b942e2b!3d-0.9492!4f13.1!5e0!3m2!1sen!2sid!4v1234567890'
            ]
        ];

        return view('frontend/pages/kontak', array_merge($this->global_data, $data));
    }

    public function submitFeedback()
    {
        $request = service('request');

        if ($request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }

        $feedback = $request->getPost('feedback');

        if (!in_array($feedback, ['puas', 'cukup', 'tidak'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid feedback value']);
        }

        try {
            $feedbackModel = $this->getModel('FeedbackModel');

            $data = [
                'feedback_type' => $feedback,
                'ip_address' => $request->getIPAddress(),
                'user_agent' => $request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $feedbackModel->insert($data);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Feedback berhasil disimpan',
                'csrf_token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saving feedback: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan feedback',
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    public function chatbot()
    {
        $data = [
            'title' => 'Chatbot - JDIH',
            'currentPage' => 'chatbot',
        ];

        return view('frontend/pages/chatbot', array_merge($this->global_data, $data));
    }

    public function getVisitorStats()
    {
        try {
            $visitorModel = $this->getModel('VisitorStatsModel');
            $stats = $visitorModel->getVisitorStats();

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting visitor stats: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error retrieving visitor statistics'
            ]);
        }
    }

    public function dokumenKategori($kategori_slug = null)
    {
        if (!$kategori_slug) {
            throw PageNotFoundException::forPageNotFound('Kategori tidak ditemukan.');
        }

        $jenisDokumenModel = $this->getModel('JenisDokumenModel');

        // Get kategori information
        $kategori_info = $jenisDokumenModel->where('kategori_slug', $kategori_slug)->first();

        if (!$kategori_info) {
            throw PageNotFoundException::forPageNotFound('Kategori tidak ditemukan.');
        }

        // Check if this is a parent category that has sub-categories
        $sub_kategori_list = $jenisDokumenModel->where('parent_kategori_slug', $kategori_slug)->findAll();

        if (!empty($sub_kategori_list)) {
            // This is a parent category, show sub-categories
            $data = [
                'title' => $kategori_info['kategori_nama'] . ' - JDIH',
                'currentPage' => 'dokumen-kategori',
                'kategori_nama' => $kategori_info['kategori_nama'],
                'sub_kategori_list' => $sub_kategori_list,
            ];
        } else {
            // This is a leaf category, show document types
            $jenis_list = $jenisDokumenModel->where('kategori_slug', $kategori_slug)->findAll();

            $data = [
                'title' => $kategori_info['kategori_nama'] . ' - JDIH',
                'currentPage' => 'dokumen-kategori',
                'kategori_nama' => $kategori_info['kategori_nama'],
                'jenis_list' => $jenis_list,
            ];
        }

        return view('frontend/pages/dokumen-kategori', array_merge($this->global_data, $data));
    }

    public function peraturanJenis($jenis_slug = null)
    {
        if (!$jenis_slug) {
            throw PageNotFoundException::forPageNotFound('Jenis peraturan tidak ditemukan.');
        }

        $jenisDokumenModel = $this->getModel('JenisDokumenModel');
        $webPeraturanModel = $this->getModel('WebPeraturanModel');

        // Get jenis information
        $jenis_info = $jenisDokumenModel->where('slug_jenis', $jenis_slug)->first();

        if (!$jenis_info) {
            throw PageNotFoundException::forPageNotFound('Jenis peraturan tidak ditemukan.');
        }

        $request = service('request');
        $filters = [
            'jenis' => $jenis_slug,
            'keyword' => $request->getGet('keyword'),
            'tahun' => $request->getGet('tahun'),
            'status' => $request->getGet('status'),
            'sort' => $request->getGet('sort') ?? 'terbaru'
        ];

        $perPage = 10;
        $peraturan = $webPeraturanModel->searchPeraturan($filters, $perPage);

        // Get statistics for sidebar
        $jenis_peraturan = $jenisDokumenModel->orderBy('nama_jenis', 'ASC')->findAll();
        $jenis_counts = $webPeraturanModel->getPeraturanCountByJenis();
        $tahun_counts = $webPeraturanModel->getPeraturanCountByTahun();

        // Get years for this specific jenis
        $tahun_peraturan = $webPeraturanModel->select('tahun')
            ->join('web_jenis_peraturan', 'web_jenis_peraturan.id_jenis_peraturan = web_peraturan.id_jenis_dokumen')
            ->where('web_jenis_peraturan.slug_jenis', $jenis_slug)
            ->distinct()
            ->orderBy('tahun', 'DESC')
            ->findAll();

        $tahun_peraturan = array_column($tahun_peraturan, 'tahun');

        $data = [
            'title' => $jenis_info['nama_jenis'] . ' - JDIH',
            'currentPage' => 'peraturan-jenis',
            'judulJenis' => $jenis_info['nama_jenis'],
            'peraturan' => $peraturan,
            'pager' => [
                'page' => $request->getGet('page') ?? 1,
                'totalPages' => $webPeraturanModel->pager->getPageCount(),
            ],
            'jenis_peraturan' => $jenis_peraturan,
            'jenis_counts' => $jenis_counts,
            'tahun_peraturan' => $tahun_peraturan,
            'tahun_counts' => $tahun_counts,
            'filters' => $filters,
        ];

        return view('frontend/pages/peraturan-jenis', array_merge($this->global_data, $data));
    }

    /**
     * Simple test method untuk debugging
     * Akses via: /test-home
     */
    public function testHome()
    {
        // Simple test without complex data
        $data = [
            'title' => 'Test Home - JDIH',
            'latest_peraturan' => [],
            'latest_berita' => [],
            'agenda' => [],
            'kategori_list' => [],
            'kategori_counts' => [],
            'popular_tags' => [],
            'all_jenis' => [],
            'all_status' => [],
            'maintenance_notice' => null,
        ];



        return view('frontend/pages/home-optimized', array_merge($this->global_data, $data));
    }

    /**
     * Very simple test method untuk debugging
     * Akses via: /simple-test
     */
    public function simpleTest()
    {

        // Return simple HTML without layout or components
        return '<!DOCTYPE html>
<html>
<head>
    <title>Simple Test</title>
</head>
<body>
    <h1>Simple Test Page</h1>
    <p>If you can see this, the controller is working.</p>
    <p>Time: ' . date('Y-m-d H:i:s') . '</p>
</body>
</html>';
    }

    /**
     * Test method untuk komponen dengan data kosong
     * Akses via: /test-components
     */
    public function testComponents()
    {

        $data = [
            'title' => 'Test Components - JDIH',
            'latest_peraturan' => [], // Empty array
            'latest_berita' => [], // Empty array
            'agenda' => [], // Empty array
            'kategori_list' => [],
            'kategori_counts' => [],
            'popular_tags' => [],
            'all_jenis' => [],
            'all_status' => [],
            'maintenance_notice' => null,
        ];



        return view('frontend/pages/home-optimized', array_merge($this->global_data, $data));
    }

    /**
     * Test method untuk komponen dengan data real dari model
     * Akses via: /test-real-data
     */
    public function testRealData()
    {
        try {
            // Get real data from models
            $latest_peraturan = $this->getModel('WebPeraturanModel')->getLatestPeraturan(8);
            $berita = $this->getModel('BeritaModel')->getLatestBerita(3);
            $agenda = $this->getModel('AgendaModel')->getUpcomingAgenda(3);

            $data = [
                'title' => 'Test Real Data - JDIH',
                'latest_peraturan' => $latest_peraturan,
                'latest_berita' => $berita,
                'agenda' => $agenda,
                'kategori_list' => [],
                'kategori_counts' => [],
                'popular_tags' => [],
                'all_jenis' => [],
                'all_status' => [],
                'maintenance_notice' => null,
            ];

            return view('frontend/pages/home-optimized', array_merge($this->global_data, $data));
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Test method untuk memeriksa model secara langsung
     * Akses via: /test-models
     */
    public function testModels()
    {
        try {
            // Test WebPeraturanModel
            $peraturanModel = $this->getModel('WebPeraturanModel');
            $latest_peraturan = $peraturanModel->getLatestPeraturan(8);

            // Test BeritaModel
            $beritaModel = $this->getModel('BeritaModel');
            $berita = $beritaModel->getLatestBerita(3);

            // Test AgendaModel
            $agendaModel = $this->getModel('AgendaModel');
            $agenda = $agendaModel->getUpcomingAgenda(3);

            // Return simple HTML with results
            $html = '<!DOCTYPE html>
<html>
<head>
    <title>Model Test Results</title>
</head>
<body>
    <h1>Model Test Results</h1>
    <p>WebPeraturanModel count: ' . count($latest_peraturan) . '</p>
    <p>BeritaModel count: ' . count($berita) . '</p>
    <p>AgendaModel count: ' . count($agenda) . '</p>
    <p>Time: ' . date('Y-m-d H:i:s') . '</p>
</body>
</html>';

            return $html;
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Test method untuk homeOptimized tanpa cache
     * Akses via: /test-home-no-cache
     */
    public function testHomeNoCache()
    {
        error_log('=== TEST HOME NO CACHE METHOD CALLED ===');

        try {
            // Clear cache untuk testing
            $cache = \Config\Services::cache();
            $cache->delete('homepage_data');

            // Get fresh data without cache (same as homeOptimized)
            helper(['page_history']);

            // Tambahkan halaman ke histori
            add_page_to_history(
                'Beranda',
                base_url('/'),
                'home',
                ['description' => 'Halaman utama JDIH Kota Padang']
            );

            // Get data with lazy loaded models
            $latest_peraturan = $this->getModel('WebPeraturanModel')->getLatestPeraturan(8);
            $berita = $this->getModel('BeritaModel')->getLatestBerita(3);
            $agenda = $this->getModel('AgendaModel')->getUpcomingAgenda(3);

        // Get popular search keywords (bukan tag dari database)
        try {
            $searchKeywordModel = $this->getModel('SearchKeywordModel');
            $popular_tags = $searchKeywordModel->getPopularKeywords(5, 1, 90); // 5 keywords, min 1 search, last 90 days
            
            // Jika tidak ada keyword, fallback ke tag database
            if (empty($popular_tags)) {
                log_message('debug', 'No popular keywords found in homeOptimized, using WebTagModel fallback');
                $popular_tags = $this->getModel('WebTagModel')->getPopularTags(5);
            }
        } catch (\Exception $e) {
            // Fallback ke tag database jika tabel search_keywords belum ada
            log_message('debug', 'SearchKeywordModel not available, using WebTagModel: ' . $e->getMessage());
            $popular_tags = $this->getModel('WebTagModel')->getPopularTags(5);
        }

            // Get all jenis and status
            $all_jenis = $this->getModel('JenisDokumenModel')->orderBy('nama_jenis', 'ASC')->findAll();
            $all_status = $this->getModel('StatusDokumenModel')->orderBy('nama_status', 'ASC')->findAll();

            // Get kategori counts
            $db = db_connect();
            $query = "SELECT 
                        CASE 
                            WHEN wjp.kategori_slug IN ('produk-hukum-peraturan', 'produk-hukum-non-peraturan') THEN 'produk-hukum'
                            WHEN wjp.kategori_slug = 'monografi-hukum' THEN 'monografi-hukum'
                            WHEN wjp.kategori_slug = 'artikel-hukum' THEN 'artikel-hukum'
                            WHEN wjp.kategori_slug = 'yurisprudensi' THEN 'yurisprudensi'
                            ELSE 'lainnya'
                        END as kategori_group,
                        COUNT(wp.id_peraturan) as total
                      FROM web_jenis_peraturan wjp
                      LEFT JOIN web_peraturan wp ON wjp.id_jenis_peraturan = wp.id_jenis_dokumen
                      WHERE wjp.kategori_slug IS NOT NULL
                      GROUP BY kategori_group";

            $results = $db->query($query)->getResultArray();

            $kategori_counts = ['produk-hukum' => 0, 'monografi-hukum' => 0, 'artikel-hukum' => 0, 'yurisprudensi' => 0];
            foreach ($results as $result) {
                if (isset($kategori_counts[$result['kategori_group']])) {
                    $kategori_counts[$result['kategori_group']] = (int)$result['total'];
                }
            }

            // Get current notice
            $currentNotice = $this->getModel('AnnouncementModel')
                ->where('status', 'active')
                ->orderBy('updated_at', 'DESC')
                ->first();

            $data = [
                'title' => 'Test Home No Cache - JDIH',
                'currentPage' => 'home',
                'latest_peraturan' => $latest_peraturan,
                'latest_berita' => $berita,
                'agenda' => $agenda,
                'kategori_list' => [
                    ['nama' => 'Produk Hukum', 'slug' => 'produk-hukum', 'icon' => 'fa-landmark'],
                    ['nama' => 'Monografi Hukum', 'slug' => 'monografi-hukum', 'icon' => 'fa-book-open'],
                    ['nama' => 'Artikel Hukum', 'slug' => 'artikel-hukum', 'icon' => 'fa-newspaper'],
                    ['nama' => 'Putusan/Yurisprudensi', 'slug' => 'yurisprudensi', 'icon' => 'fa-balance-scale']
                ],
                'kategori_counts' => $kategori_counts,
                'popular_tags' => $popular_tags,
                'all_jenis' => $all_jenis,
                'all_status' => $all_status,
                'maintenance_notice' => $currentNotice,
            ];



            // Use component-based home page
            return view('frontend/pages/home-optimized', array_merge($this->global_data, $data));
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Test method untuk komponen dengan data sample
     * Akses via: /test-sample-data
     */
    public function testSampleData()
    {

        // Create sample data
        $sample_peraturan = [
            [
                'id_peraturan' => 1,
                'judul' => 'Peraturan Daerah Nomor 1 Tahun 2024',
                'nomor' => '1',
                'tahun' => '2024',
                'tgl_penetapan' => '2024-01-15',
                'nama_jenis' => 'Peraturan Daerah',
                'slug' => 'perda-1-2024',
                'file_dokumen' => 'sample.pdf'
            ],
            [
                'id_peraturan' => 2,
                'judul' => 'Peraturan Walikota Nomor 2 Tahun 2024',
                'nomor' => '2',
                'tahun' => '2024',
                'tgl_penetapan' => '2024-01-20',
                'nama_jenis' => 'Peraturan Walikota',
                'slug' => 'perwal-2-2024',
                'file_dokumen' => 'sample.pdf'
            ]
        ];

        $sample_berita = [
            [
                'id' => 1,
                'judul' => 'Berita Sample 1',
                'isi_berita' => 'Ini adalah berita sample untuk testing komponen.',
                'tanggal_publish' => '2024-01-15',
                'nama_penulis' => 'Admin',
                'slug' => 'berita-sample-1',
                'gambar' => 'sample.jpg'
            ],
            [
                'id' => 2,
                'judul' => 'Berita Sample 2',
                'isi_berita' => 'Ini adalah berita sample kedua untuk testing komponen.',
                'tanggal_publish' => '2024-01-20',
                'nama_penulis' => 'Admin',
                'slug' => 'berita-sample-2',
                'gambar' => 'sample.jpg'
            ]
        ];

        $sample_agenda = [
            [
                'id' => 1,
                'judul' => 'Agenda Sample 1',
                'deskripsi' => 'Ini adalah agenda sample untuk testing komponen.',
                'tanggal_mulai' => '2024-02-01',
                'tanggal_selesai' => '2024-02-01',
                'slug' => 'agenda-sample-1'
            ]
        ];

        $data = [
            'title' => 'Test Sample Data - JDIH',
            'latest_peraturan' => $sample_peraturan,
            'latest_berita' => $sample_berita,
            'agenda' => $sample_agenda,
            'kategori_list' => [],
            'kategori_counts' => [],
            'popular_tags' => [],
            'all_jenis' => [],
            'all_status' => [],
            'maintenance_notice' => null,
        ];



        return view('frontend/pages/home-optimized', array_merge($this->global_data, $data));
    }

    /**
     * Test popular keywords - untuk debugging
     * Akses via: /test-popular-keywords
     */
    public function testPopularKeywords()
    {
        try {
            $cache = \Config\Services::cache();
            $db = \Config\Database::connect();
            
            $output = "<h1>Test Popular Keywords</h1>\n";
            
            // 1. Clear Cache
            $output .= "<h2>1. Clear Cache</h2>\n";
            $cacheKeys = ['popular_search_keywords', 'popular_keywords', 'homepage_data'];
            foreach ($cacheKeys as $key) {
                if ($cache->delete($key)) {
                    $output .= "<p style='color:green;'>✓ Cache cleared: $key</p>\n";
                } else {
                    $output .= "<p style='color:blue;'>- Cache not found: $key</p>\n";
                }
            }
            
            // 2. Check Database
            $output .= "<h2>2. Check Database</h2>\n";
            if ($db->tableExists('search_keywords')) {
                $output .= "<p style='color:green;'>✓ Table 'search_keywords' exists</p>\n";
                
                $query = $db->query("SELECT COUNT(*) as total FROM search_keywords");
                $result = $query->getRow();
                $output .= "<p>Total keywords: <strong>{$result->total}</strong></p>\n";
                
                $query = $db->query("SELECT keyword, search_count, last_searched FROM search_keywords ORDER BY search_count DESC, last_searched DESC LIMIT 10");
                $keywords = $query->getResultArray();
                
                if (!empty($keywords)) {
                    $output .= "<h3>Top Keywords:</h3>\n";
                    $output .= "<table border='1' cellpadding='5'><tr><th>Keyword</th><th>Search Count</th><th>Last Searched</th></tr>\n";
                    foreach ($keywords as $kw) {
                        $output .= "<tr><td>{$kw['keyword']}</td><td>{$kw['search_count']}</td><td>{$kw['last_searched']}</td></tr>\n";
                    }
                    $output .= "</table>\n";
                } else {
                    $output .= "<p style='color:orange;'>⚠ No keywords found in database</p>\n";
                }
            } else {
                $output .= "<p style='color:red;'>✗ Table 'search_keywords' does NOT exist!</p>\n";
            }
            
            // 3. Test Model
            $output .= "<h2>3. Test Model</h2>\n";
            $model = new \App\Models\SearchKeywordModel();
            $popular = $model->getPopularKeywords(5, 1, 90);
            
            if (!empty($popular)) {
                $output .= "<p style='color:green;'>✓ Model returned " . count($popular) . " keywords</p>\n";
                $output .= "<pre>" . print_r($popular, true) . "</pre>\n";
            } else {
                $output .= "<p style='color:orange;'>⚠ Model returned empty array</p>\n";
            }
            
            // 4. Test Controller Logic
            $output .= "<h2>4. Test Controller Logic</h2>\n";
            $searchKeywordModel = new \App\Models\SearchKeywordModel();
            $keywords = $searchKeywordModel->getPopularKeywords(5, 1, 90);
            
            if (empty($keywords)) {
                $webTagModel = new \App\Models\WebTagModel();
                $fallback = $webTagModel->getPopularTags(5);
                $output .= "<p style='color:blue;'>Using fallback (WebTagModel): " . count($fallback) . " tags</p>\n";
                $output .= "<pre>" . print_r($fallback, true) . "</pre>\n";
            } else {
                $output .= "<p style='color:green;'>Using SearchKeywordModel: " . count($keywords) . " keywords</p>\n";
                $output .= "<pre>" . print_r($keywords, true) . "</pre>\n";
            }
            
            return $this->response->setBody($output);
            
        } catch (\Exception $e) {
            return $this->response->setBody("<p style='color:red;'>Error: " . $e->getMessage() . "</p><pre>" . $e->getTraceAsString() . "</pre>");
        }
    }
}
