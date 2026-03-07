<?php

/**
 * JDIH Cache Helper
 * 
 * Provides optimized caching functions for the JDIH application
 * with automatic cache invalidation and performance monitoring
 */

if (!function_exists('get_cache_duration')) {
    /**
     * Get cache duration for specific data type
     * 
     * @param string $type
     * @return int Duration in seconds
     */
    function get_cache_duration(string $type): int
    {
        $config = config('Cache');
        return $config->jdihCacheDurations[$type] ?? $config->ttl;
    }
}

if (!function_exists('cache_get_or_set')) {
    /**
     * Get from cache or set if not exists
     * 
     * @param string $key Cache key
     * @param callable $callback Function to get data if cache miss
     * @param string $type Data type for duration
     * @return mixed
     */
    function cache_get_or_set(string $key, callable $callback, string $type = 'default')
    {
        $cache = \Config\Services::cache();
        $data = $cache->get($key);

        if ($data === null) {
            $data = $callback();
            if ($data !== null) {
                $duration = get_cache_duration($type);
                $cache->save($key, $data, $duration);
            }
        }

        return $data;
    }
}

if (!function_exists('cache_remember')) {
    /**
     * Cache remember pattern with automatic key generation
     * 
     * @param string $prefix Key prefix
     * @param array $params Parameters for key generation
     * @param callable $callback Function to get data
     * @param string $type Data type for duration
     * @return mixed
     */
    function cache_remember(string $prefix, array $params, callable $callback, string $type = 'default')
    {
        $key = $prefix . '_' . md5(serialize($params));
        return cache_get_or_set($key, $callback, $type);
    }
}

if (!function_exists('cache_tags_invalidate')) {
    /**
     * Invalidate cache by tags
     * 
     * @param string $tag Tag name
     * @return void
     */
    function cache_tags_invalidate(string $tag): void
    {
        $config = config('Cache');
        $cache = \Config\Services::cache();

        if (isset($config->cacheTags[$tag])) {
            foreach ($config->cacheTags[$tag] as $cacheKey) {
                $cache->delete($cacheKey);

                // Also delete pattern-based keys
                if (function_exists('cache_delete_pattern')) {
                    cache_delete_pattern($cacheKey . '_*');
                }
            }
        }
    }
}

if (!function_exists('cache_delete_pattern')) {
    /**
     * Delete cache keys by pattern (file cache only)
     * 
     * @param string $pattern Pattern with wildcards
     * @return void
     */
    function cache_delete_pattern(string $pattern): void
    {
        $cache = \Config\Services::cache();
        $config = config('Cache');

        // Only works with file cache
        if ($config->handler !== 'file') {
            return;
        }

        $cacheDir = $config->file['storePath'];
        $prefix = $config->prefix;

        // Convert pattern to regex
        $regex = '/^' . preg_quote($prefix . $pattern, '/') . '$/';
        $regex = str_replace('\*', '.*', $regex);

        $files = glob($cacheDir . '*');
        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match($regex, $filename)) {
                unlink($file);
            }
        }
    }
}

if (!function_exists('cache_warm_up')) {
    /**
     * Warm up cache for critical data
     * 
     * @return void
     */
    function cache_warm_up(): void
    {
        try {
            // Warm up critical caches
            $cache = \Config\Services::cache();

            // 1. Jenis Peraturan
            cache_get_or_set('jenis_peraturan_grouped', function () {
                $jenisDokumenModel = new \App\Models\JenisDokumenModel();
                $jenisList = $jenisDokumenModel->orderBy('urutan', 'ASC')->findAll();

                $grouped = [];
                foreach ($jenisList as $jenis) {
                    $kategori = $jenis['kategori_nama'] ?? 'Lainnya';
                    $grouped[$kategori][] = $jenis;
                }
                return $grouped;
            }, 'jenis_peraturan');

            // 2. Visitor Stats
            cache_get_or_set('visitor_stats_frontend', function () {
                $visitorModel = new \App\Models\VisitorStatsModel();
                return $visitorModel->getVisitorStats();
            }, 'visitor_stats');

            // 3. Kategori Counts
            cache_get_or_set('kategori_counts', function () {
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

            // 4. Popular Tags
            cache_get_or_set('popular_tags', function () {
                $webTagModel = new \App\Models\WebTagModel();
                return $webTagModel->getPopularTags(5);
            }, 'popular_tags');

            // 5. All Jenis Dokumen
            cache_get_or_set('all_jenis_dokumen', function () {
                $jenisDokumenModel = new \App\Models\JenisDokumenModel();
                return $jenisDokumenModel->orderBy('nama_jenis', 'ASC')->findAll();
            }, 'jenis_peraturan');

            // 6. All Status Dokumen
            cache_get_or_set('all_status_dokumen', function () {
                $statusDokumenModel = new \App\Models\StatusDokumenModel();
                return $statusDokumenModel->orderBy('nama_status', 'ASC')->findAll();
            }, 'status_dokumen');

            log_message('info', 'Cache warm-up completed successfully');
        } catch (\Exception $e) {
            log_message('error', 'Cache warm-up failed: ' . $e->getMessage());
        }
    }
}

if (!function_exists('cache_stats')) {
    /**
     * Get cache statistics
     * 
     * @return array
     */
    function cache_stats(): array
    {
        $cache = \Config\Services::cache();
        $config = config('Cache');

        $stats = [
            'handler' => $config->handler,
            'total_keys' => 0,
            'total_size' => 0,
            'hit_rate' => 0
        ];

        // Only detailed stats for file cache
        if ($config->handler === 'file') {
            $cacheDir = $config->file['storePath'];
            $files = glob($cacheDir . '*');

            $stats['total_keys'] = count($files);

            foreach ($files as $file) {
                if (is_file($file)) {
                    $stats['total_size'] += filesize($file);
                }
            }

            $stats['total_size_mb'] = round($stats['total_size'] / 1024 / 1024, 2);
        }

        return $stats;
    }
}

if (!function_exists('cache_optimize')) {
    /**
     * Optimize cache by removing expired entries
     * 
     * @return array Results
     */
    function cache_optimize(): array
    {
        $config = config('Cache');
        $results = [
            'removed_count' => 0,
            'size_freed' => 0,
            'error' => null
        ];

        try {
            if ($config->handler === 'file') {
                $cacheDir = $config->file['storePath'];
                $files = glob($cacheDir . '*');
                $now = time();

                foreach ($files as $file) {
                    if (is_file($file)) {
                        $content = file_get_contents($file);
                        if ($content !== false) {
                            $data = unserialize($content);

                            // Check if expired (basic check)
                            if (isset($data['ttl']) && $data['ttl'] < $now) {
                                $size = filesize($file);
                                if (unlink($file)) {
                                    $results['removed_count']++;
                                    $results['size_freed'] += $size;
                                }
                            }
                        }
                    }
                }

                $results['size_freed_mb'] = round($results['size_freed'] / 1024 / 1024, 2);
            }
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }

        return $results;
    }
}

if (!function_exists('cache_clear_all')) {
    /**
     * Clear all application cache
     * 
     * @return bool
     */
    function cache_clear_all(): bool
    {
        try {
            $cache = \Config\Services::cache();
            return $cache->clean();
        } catch (\Exception $e) {
            log_message('error', 'Failed to clear all cache: ' . $e->getMessage());
            return false;
        }
    }
}
