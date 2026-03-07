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
 * OPTIMIZED Frontend Controller
 * Performance improvements:
 * - Lazy loading models
 * - Aggressive caching
 * - Optimized queries
 * - Reduced memory usage
 */
class OptimizedFrontend extends Controller
{
    protected $request;
    protected $global_data = [];

    // Lazy loaded models
    private $models = [];
    private $cache;

    // Cache keys
    private const CACHE_GLOBAL_DATA = 'frontend_global_data';
    private const CACHE_VISITOR_STATS = 'visitor_stats_frontend';
    private const CACHE_JENIS_PERATURAN = 'jenis_peraturan_grouped';
    private const CACHE_JENIS_PUU = 'jenis_puu_grouped';

    // Cache durations (in seconds)
    private const CACHE_GLOBAL_DURATION = 3600; // 1 hour
    private const CACHE_STATS_DURATION = 300;   // 5 minutes
    private const CACHE_JENIS_DURATION = 7200;  // 2 hours

    public function __construct()
    {
        $this->request = \Config\Services::request();
        $this->cache = \Config\Services::cache();

        // Initialize global data with caching
        $this->initializeGlobalData();

        // Optimized visitor tracking (async/background)
        $this->trackVisitorOptimized();
    }

    /**
     * Lazy load models only when needed
     */
    private function getModel(string $modelName)
    {
        if (!isset($this->models[$modelName])) {
            $className = "App\\Models\\{$modelName}";
            $this->models[$modelName] = new $className();
        }
        return $this->models[$modelName];
    }

    /**
     * Initialize global data with aggressive caching
     */
    private function initializeGlobalData(): void
    {
        $this->global_data = $this->cache->get(self::CACHE_GLOBAL_DATA);

        if ($this->global_data === null) {
            // Get visitor stats with caching
            $visitorStats = $this->getVisitorStatsOptimized();

            // Get jenis peraturan with caching
            $jenisPeraturan = $this->getJenisPeraturanOptimized();

            // Get jenis pembentukan PUU with caching
            $jenisPUU = $this->getJenisPUUOptimized();

            $this->global_data = [
                'global_jenis_peraturan' => $jenisPeraturan,
                'global_jenis_puu' => $jenisPUU,
                'stat_total'  => $visitorStats['total'],
                'stat_today'  => $visitorStats['today'],
                'stat_week'   => $visitorStats['week'],
                'stat_month'  => $visitorStats['month'],
                'stat_year'   => $visitorStats['year'],
                'stat_online' => $visitorStats['online'],
            ];

            // Cache global data
            $this->cache->save(self::CACHE_GLOBAL_DATA, $this->global_data, self::CACHE_GLOBAL_DURATION);
        }
    }

    /**
     * Optimized visitor stats with multi-level caching
     */
    private function getVisitorStatsOptimized(): array
    {
        $stats = $this->cache->get(self::CACHE_VISITOR_STATS);

        if ($stats === null) {
            $visitorModel = $this->getModel('VisitorStatsModel');
            $stats = $visitorModel->getVisitorStats();

            // Cache with shorter duration for real-time data
            $this->cache->save(self::CACHE_VISITOR_STATS, $stats, self::CACHE_STATS_DURATION);
        }

        return $stats;
    }

    /**
     * Optimized jenis peraturan with long-term caching
     */
    private function getJenisPeraturanOptimized(): array
    {
        $jenisPeraturan = $this->cache->get(self::CACHE_JENIS_PERATURAN);

        if ($jenisPeraturan === null) {
            $jenisDokumenModel = $this->getModel('JenisDokumenModel');
            $jenisList = $jenisDokumenModel->where('menu', 1)->orderBy('urutan', 'ASC')->findAll();
            $jenisPeraturan = $this->groupJenisByKategori($jenisList);

            // Cache with long duration since this data rarely changes
            $this->cache->save(self::CACHE_JENIS_PERATURAN, $jenisPeraturan, self::CACHE_JENIS_DURATION);
        }

        return $jenisPeraturan;
    }

    /**
     * Optimized jenis pembentukan PUU with caching (menu = 2)
     */
    private function getJenisPUUOptimized(): array
    {
        $jenisPUU = $this->cache->get(self::CACHE_JENIS_PUU);

        if ($jenisPUU === null) {
            $jenisDokumenModel = $this->getModel('JenisDokumenModel');
            $jenisList = $jenisDokumenModel->where('menu', 2)->orderBy('urutan', 'ASC')->findAll();
            $jenisPUU = $this->groupJenisByKategori($jenisList);

            // Cache with long duration since this data rarely changes
            $this->cache->save(self::CACHE_JENIS_PUU, $jenisPUU, self::CACHE_JENIS_DURATION);
        }

        return $jenisPUU;
    }

    /**
     * Optimized visitor tracking
     */
    private function trackVisitorOptimized(): void
    {
        // Use session to prevent multiple tracking per session
        $session = session();
        $lastTracked = $session->get('last_visitor_tracked');

        // Only track once per 5 minutes per session
        if (!$lastTracked || (time() - $lastTracked) > 300) {
            // Track visitor in background (non-blocking)
            try {
                $visitorModel = $this->getModel('VisitorStatsModel');
                $visitorModel->recordVisitor();
                $session->set('last_visitor_tracked', time());

                // Clear short-term cache after tracking
                $this->cache->delete(self::CACHE_VISITOR_STATS);
            } catch (\Exception $e) {
                // Silent fail - don't break page load for tracking issues
                log_message('error', 'Visitor tracking failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Utility: Group jenis by kategori (same as original)
     */
    private function groupJenisByKategori($jenisList): array
    {
        $grouped = [];
        foreach ($jenisList as $jenis) {
            $kategori = $jenis['kategori_nama'] ?? 'Lainnya';
            $grouped[$kategori][] = $jenis;
        }
        return $grouped;
    }

    /**
     * OPTIMIZED: Index method with aggressive caching
     */
    public function index()
    {
        helper(['page_history']);

        // Cache key for homepage data
        $cacheKey = 'homepage_data';
        $data = $this->cache->get($cacheKey);

        if ($data === null) {
            // Add page to history
            add_page_to_history(
                'Beranda',
                base_url('/'),
                'home',
                ['description' => 'Halaman utama JDIH Kota Padang']
            );

            // Get data with optimized queries
            $webPeraturanModel = $this->getModel('WebPeraturanModel');
            $beritaModel = $this->getModel('BeritaModel');
            $agendaModel = $this->getModel('AgendaModel');

            // Use single optimized queries
            $latest_peraturan = $webPeraturanModel->getLatestPeraturan(8);
            $berita = $beritaModel->getLatestBerita(3);
            $agenda = $agendaModel->getUpcomingAgenda(3);

            // Optimized category counts with single query
            $kategori_counts = $this->getKategoriCountsOptimized();

            // Get popular tags with caching
            $popular_tags = $this->getPopularTagsOptimized();

            // Get all jenis and status with caching
            $all_jenis = $this->getAllJenisOptimized();
            $all_status = $this->getAllStatusOptimized();

            // Get current notice
            $announcementModel = $this->getModel('AnnouncementModel');
            $currentNotice = $announcementModel
                ->where('status', 'active')
                ->orderBy('updated_at', 'DESC')
                ->first();

            $data = [
                'title' => 'Beranda - JDIH',
                'currentPage' => 'home',
                'latest_peraturan' => $latest_peraturan,
                'berita' => $berita,
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

            // Cache homepage data for 10 minutes
            $this->cache->save($cacheKey, $data, 600);
        }

        return view('frontend/pages/home', array_merge($this->global_data, $data));
    }

    /**
     * Optimized kategori counts with single query
     */
    private function getKategoriCountsOptimized(): array
    {
        $cacheKey = 'kategori_counts';
        $counts = $this->cache->get($cacheKey);

        if ($counts === null) {
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

            // Cache for 1 hour
            $this->cache->save($cacheKey, $counts, 3600);
        }

        return $counts;
    }

    /**
     * Optimized popular tags
     */
    private function getPopularTagsOptimized(): array
    {
        $cacheKey = 'popular_tags';
        $tags = $this->cache->get($cacheKey);

        if ($tags === null) {
            $webTagModel = $this->getModel('WebTagModel');
            $tags = $webTagModel->getPopularTags(5);

            // Cache for 30 minutes
            $this->cache->save($cacheKey, $tags, 1800);
        }

        return $tags;
    }

    /**
     * Optimized all jenis
     */
    private function getAllJenisOptimized(): array
    {
        $cacheKey = 'all_jenis_dokumen';
        $jenis = $this->cache->get($cacheKey);

        if ($jenis === null) {
            $jenisDokumenModel = $this->getModel('JenisDokumenModel');
            $jenis = $jenisDokumenModel->orderBy('nama_jenis', 'ASC')->findAll();

            // Cache for 2 hours
            $this->cache->save($cacheKey, $jenis, 7200);
        }

        return $jenis;
    }

    /**
     * Optimized all status
     */
    private function getAllStatusOptimized(): array
    {
        $cacheKey = 'all_status_dokumen';
        $status = $this->cache->get($cacheKey);

        if ($status === null) {
            $statusDokumenModel = $this->getModel('StatusDokumenModel');
            $status = $statusDokumenModel->orderBy('nama_status', 'ASC')->findAll();

            // Cache for 2 hours
            $this->cache->save($cacheKey, $status, 7200);
        }

        return $status;
    }

    /**
     * OPTIMIZED: Peraturan method with better caching
     */
    public function peraturan($jenis_slug = null)
    {
        // Skip 'search' as jenis slug
        if ($jenis_slug === 'search') {
            $jenis_slug = null;
        }

        $request = service('request');

        // Get filters
        $filters = [
            'jenis' => $jenis_slug ?? $request->getGet('jenis'),
            'keyword' => $request->getGet('keyword'),
            'tahun' => $request->getGet('tahun'),
            'status' => $request->getGet('status'),
            'tag' => $request->getGet('tag'),
            'sort' => $request->getGet('sort') ?? 'terbaru'
        ];

        // Create cache key based on filters
        $cacheKey = 'peraturan_' . md5(serialize($filters) . ($request->getGet('page') ?? 1));
        $viewData = $this->cache->get($cacheKey);

        if ($viewData === null) {
            $webPeraturanModel = $this->getModel('WebPeraturanModel');

            // Process tag filter
            $modelFilters = $filters;
            $viewData['tag'] = null;

            if (!empty($filters['tag'])) {
                $webTagModel = $this->getModel('WebTagModel');
                $tagData = $webTagModel->where('slug_tag', $filters['tag'])->first();

                if ($tagData) {
                    $modelFilters['tag'] = $tagData['id_tag'];
                    $viewData['tag'] = $tagData;
                } else {
                    $modelFilters['tag'] = 0;
                }
            }

            // Convert jenis ID to slug if needed
            if (!empty($filters['jenis']) && is_numeric($filters['jenis'])) {
                $db = db_connect();
                $jenisData = $db->table('web_jenis_peraturan')
                    ->where('id_jenis_peraturan', $filters['jenis'])
                    ->get()->getRow();
                if ($jenisData) {
                    $filters['jenis'] = $jenisData->slug_jenis;
                    $modelFilters['jenis'] = $jenisData->slug_jenis;
                }
            }

            $perPage = 10;
            $viewData['peraturan'] = $webPeraturanModel->searchPeraturan($modelFilters, $perPage);
            $viewData['pager'] = $webPeraturanModel->pager;
            $viewData['filters'] = $filters;

            // Get counts with caching
            $viewData['jenis_counts'] = $this->getJenisCountsOptimized();
            $viewData['tahun_counts'] = $this->getTahunCountsOptimized();
            $viewData['jenis_peraturan'] = $this->getAllJenisOptimized();
            $viewData['title'] = 'Peraturan - JDIH';

            // Cache for 5 minutes (shorter due to pagination)
            $this->cache->save($cacheKey, $viewData, 300);
        }

        return view('frontend/pages/peraturan', array_merge($this->global_data, $viewData));
    }

    /**
     * Optimized jenis counts
     */
    private function getJenisCountsOptimized(): array
    {
        $cacheKey = 'jenis_counts';
        $counts = $this->cache->get($cacheKey);

        if ($counts === null) {
            $webPeraturanModel = $this->getModel('WebPeraturanModel');
            $counts = $webPeraturanModel->getPeraturanCountByJenis();

            // Cache for 30 minutes
            $this->cache->save($cacheKey, $counts, 1800);
        }

        return $counts;
    }

    /**
     * Optimized tahun counts
     */
    private function getTahunCountsOptimized(): array
    {
        $cacheKey = 'tahun_counts';
        $counts = $this->cache->get($cacheKey);

        if ($counts === null) {
            $webPeraturanModel = $this->getModel('WebPeraturanModel');
            $counts = $webPeraturanModel->getPeraturanCountByTahun();

            // Cache for 1 hour
            $this->cache->save($cacheKey, $counts, 3600);
        }

        return $counts;
    }

    /**
     * Clear all frontend caches (call this when data is updated)
     */
    public function clearAllCaches(): void
    {
        $cacheKeys = [
            self::CACHE_GLOBAL_DATA,
            self::CACHE_VISITOR_STATS,
            self::CACHE_JENIS_PERATURAN,
            'homepage_data',
            'kategori_counts',
            'popular_tags',
            'all_jenis_dokumen',
            'all_status_dokumen',
            'jenis_counts',
            'tahun_counts'
        ];

        foreach ($cacheKeys as $key) {
            $this->cache->delete($key);
        }
    }

    // ... (other methods can be optimized similarly)
    // For brevity, I'll continue with the remaining methods in the next part
}
