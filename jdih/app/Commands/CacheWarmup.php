<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Cache Warm-up Command
 * 
 * Preloads critical cache data for optimal performance
 * Run this after deployment or data updates
 */
class CacheWarmup extends BaseCommand
{
    protected $group       = 'JDIH';
    protected $name        = 'cache:warmup';
    protected $description = 'Warm up critical cache data for optimal performance';

    protected $usage = 'cache:warmup [options]';
    protected $arguments = [];
    protected $options = [
        '--force' => 'Force refresh all caches even if they exist',
        '--stats' => 'Show cache statistics after warm-up'
    ];

    public function run(array $params)
    {
        helper('cache');

        $force = CLI::getOption('force');
        $showStats = CLI::getOption('stats');

        CLI::write('🚀 Starting cache warm-up...', 'green');

        $startTime = microtime(true);
        $warmedCaches = [];

        try {
            cache_warm_up();
            CLI::write('✅ Cache warm-up completed successfully!', 'green');
        } catch (\Exception $e) {
            CLI::error('❌ Cache warm-up failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Warm up jenis peraturan cache
     */
    private function warmupJenisPeraturan(bool $force = false): void
    {
        $cacheKey = 'jenis_peraturan_grouped';
        $cache = \Config\Services::cache();

        if ($force) {
            $cache->delete($cacheKey);
        }

        cache_get_or_set($cacheKey, function () {
            $jenisDokumenModel = new \App\Models\JenisDokumenModel();
            $jenisList = $jenisDokumenModel->orderBy('urutan', 'ASC')->findAll();

            $grouped = [];
            foreach ($jenisList as $jenis) {
                $kategori = $jenis['kategori_nama'] ?? 'Lainnya';
                $grouped[$kategori][] = $jenis;
            }
            return $grouped;
        }, 'jenis_peraturan');

        CLI::write('   ✓ Jenis peraturan cache warmed', 'green');
    }

    /**
     * Warm up visitor stats cache
     */
    private function warmupVisitorStats(bool $force = false): void
    {
        $cacheKey = 'visitor_stats_frontend';
        $cache = \Config\Services::cache();

        if ($force) {
            $cache->delete($cacheKey);
        }

        cache_get_or_set($cacheKey, function () {
            $visitorModel = new \App\Models\VisitorStatsModel();
            return $visitorModel->getVisitorStats();
        }, 'visitor_stats');

        CLI::write('   ✓ Visitor stats cache warmed', 'green');
    }

    /**
     * Warm up global data cache
     */
    private function warmupGlobalData(bool $force = false): void
    {
        $cacheKey = 'frontend_global_data';
        $cache = \Config\Services::cache();

        if ($force) {
            $cache->delete($cacheKey);
        }

        // This will use the cached jenis_peraturan and visitor_stats
        cache_get_or_set($cacheKey, function () {
            $visitorStats = cache_get_or_set('visitor_stats_frontend', function () {
                $visitorModel = new \App\Models\VisitorStatsModel();
                return $visitorModel->getVisitorStats();
            }, 'visitor_stats');

            $jenisPeraturan = cache_get_or_set('jenis_peraturan_grouped', function () {
                $jenisDokumenModel = new \App\Models\JenisDokumenModel();
                $jenisList = $jenisDokumenModel->orderBy('urutan', 'ASC')->findAll();

                $grouped = [];
                foreach ($jenisList as $jenis) {
                    $kategori = $jenis['kategori_nama'] ?? 'Lainnya';
                    $grouped[$kategori][] = $jenis;
                }
                return $grouped;
            }, 'jenis_peraturan');

            return [
                'global_jenis_peraturan' => $jenisPeraturan,
                'stat_total'  => $visitorStats['total'],
                'stat_today'  => $visitorStats['today'],
                'stat_week'   => $visitorStats['week'],
                'stat_month'  => $visitorStats['month'],
                'stat_year'   => $visitorStats['year'],
                'stat_online' => $visitorStats['online'],
            ];
        }, 'global_data');

        CLI::write('   ✓ Global data cache warmed', 'green');
    }

    /**
     * Warm up homepage data cache
     */
    private function warmupHomepageData(bool $force = false): void
    {
        $cacheKey = 'homepage_data';
        $cache = \Config\Services::cache();

        if ($force) {
            $cache->delete($cacheKey);
        }

        cache_get_or_set($cacheKey, function () {
            $webPeraturanModel = new \App\Models\WebPeraturanModel();
            $beritaModel = new \App\Models\BeritaModel();
            $agendaModel = new \App\Models\AgendaModel();
            $announcementModel = new \App\Models\AnnouncementModel();

            $latest_peraturan = $webPeraturanModel->getLatestPeraturan(8);
            $berita = $beritaModel->getLatestBerita(3);
            $agenda = $agendaModel->getUpcomingAgenda(3);

            $popular_tags = cache_get_or_set('popular_tags', function () {
                $webTagModel = new \App\Models\WebTagModel();
                return $webTagModel->getPopularTags(5);
            }, 'popular_tags');

            $all_jenis = cache_get_or_set('all_jenis_dokumen', function () {
                $jenisDokumenModel = new \App\Models\JenisDokumenModel();
                return $jenisDokumenModel->orderBy('nama_jenis', 'ASC')->findAll();
            }, 'jenis_peraturan');

            $all_status = cache_get_or_set('all_status_dokumen', function () {
                $statusDokumenModel = new \App\Models\StatusDokumenModel();
                return $statusDokumenModel->orderBy('nama_status', 'ASC')->findAll();
            }, 'status_dokumen');

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

            $currentNotice = $announcementModel
                ->where('status', 'active')
                ->orderBy('updated_at', 'DESC')
                ->first();

            return [
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
        }, 'homepage_data');

        CLI::write('   ✓ Homepage data cache warmed', 'green');
    }

    /**
     * Warm up kategori counts cache
     */
    private function warmupKategoriCounts(bool $force = false): void
    {
        $cacheKey = 'kategori_counts';
        $cache = \Config\Services::cache();

        if ($force) {
            $cache->delete($cacheKey);
        }

        cache_get_or_set($cacheKey, function () {
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

        CLI::write('   ✓ Kategori counts cache warmed', 'green');
    }

    /**
     * Warm up popular tags cache
     */
    private function warmupPopularTags(bool $force = false): void
    {
        $cacheKey = 'popular_tags';
        $cache = \Config\Services::cache();

        if ($force) {
            $cache->delete($cacheKey);
        }

        cache_get_or_set($cacheKey, function () {
            $webTagModel = new \App\Models\WebTagModel();
            return $webTagModel->getPopularTags(5);
        }, 'popular_tags');

        CLI::write('   ✓ Popular tags cache warmed', 'green');
    }

    /**
     * Warm up master data caches
     */
    private function warmupMasterData(bool $force = false): void
    {
        $cache = \Config\Services::cache();

        // All Jenis Dokumen
        $jenisKey = 'all_jenis_dokumen';
        if ($force) {
            $cache->delete($jenisKey);
        }

        cache_get_or_set($jenisKey, function () {
            $jenisDokumenModel = new \App\Models\JenisDokumenModel();
            return $jenisDokumenModel->orderBy('nama_jenis', 'ASC')->findAll();
        }, 'jenis_peraturan');

        // All Status Dokumen
        $statusKey = 'all_status_dokumen';
        if ($force) {
            $cache->delete($statusKey);
        }

        cache_get_or_set($statusKey, function () {
            $statusDokumenModel = new \App\Models\StatusDokumenModel();
            return $statusDokumenModel->orderBy('nama_status', 'ASC')->findAll();
        }, 'status_dokumen');

        CLI::write('   ✓ Master data cache warmed', 'green');
    }

    /**
     * Show cache statistics
     */
    private function showCacheStats(): void
    {
        CLI::write('');
        CLI::write('📊 Cache Statistics:', 'yellow');

        $stats = cache_stats();

        CLI::write("   Handler: {$stats['handler']}", 'blue');
        CLI::write("   Total Keys: {$stats['total_keys']}", 'blue');

        if (isset($stats['total_size_mb'])) {
            CLI::write("   Total Size: {$stats['total_size_mb']} MB", 'blue');
        }
    }
}
