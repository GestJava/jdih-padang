<?php

namespace App\Models;

use CodeIgniter\Model;

class VisitorStatsModel extends Model
{
    protected $table = 'traffic';
    protected $allowedFields = [
        'ip',
        'browser',
        'os',
        'platform',
        'country',
        'city',
        'date',
        'hits',
        'online'
    ];

    private $cacheExpiry = 300; // 5 menit cache
    private $longCacheExpiry = 3600; // 1 jam untuk data yang jarang berubah

    /**
     * Merekam kunjungan pengunjung dengan optimasi
     */
    public function recordVisitor()
    {
        // Cek rate limiting untuk mengurangi beban database
        $session = session();
        $lastRecord = $session->get('last_visitor_record');

        // Hanya record setiap 2 menit per session untuk mengurangi beban
        if ($lastRecord && (time() - $lastRecord) < 120) {
            return;
        }

        $ip = $this->getClientIP();
        $date = date('Y-m-d');
        $now = time();

        try {
            // Gunakan single query dengan ON DUPLICATE KEY UPDATE (MySQL specific)
            $db = \Config\Database::connect();

            // Optimized query dengan prepared statement
            $sql = "INSERT INTO traffic (ip, browser, os, platform, country, city, date, hits, online) 
                    VALUES (?, ?, ?, ?, '', '', ?, 1, ?) 
                    ON DUPLICATE KEY UPDATE 
                    hits = hits + 1, 
                    online = VALUES(online),
                    browser = VALUES(browser)";

            $browser = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255); // Limit browser length
            $os = $this->getOS($browser);
            $platform = $this->getPlatform($browser);

            $db->query($sql, [$ip, $browser, $os, $platform, $date, (string)$now]);

            // Update session timestamp
            $session->set('last_visitor_record', time());

            // Clear cache setelah record baru dengan error handling
            try {
                $cache = \Config\Services::cache();
                $cache->delete('visitor_stats');
                $cache->delete('visitor_stats_frontend');
            } catch (\Exception $e) {
                // Log error tapi jangan stop execution
                log_message('warning', 'Failed to clear visitor cache: ' . $e->getMessage());
            }

            // Update summary table setiap 10 menit sekali untuk mengurangi beban
            try {
                $lastSummaryUpdate = $cache->get('last_summary_update');
                if (!$lastSummaryUpdate || (time() - $lastSummaryUpdate) > 600) {
                    $this->updateTodaySummary();
                    $cache->save('last_summary_update', time(), 3600);
                }
            } catch (\Exception $e) {
                // Log error tapi jangan stop execution
                log_message('warning', 'Failed to update summary: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            // Log error tapi jangan stop eksekusi
            log_message('error', 'Visitor tracking error: ' . $e->getMessage());
        }
    }

    /**
     * Get cached statistics atau query database jika cache expired
     */
    public function getVisitorStats()
    {
        try {
            $cache = \Config\Services::cache();
            $cacheKey = 'visitor_stats';

            $stats = $cache->get($cacheKey);

            if ($stats === null) {
                // Coba ambil dari cache jangka panjang dulu
                $longCacheKey = 'visitor_stats_long';
                $baseStats = $cache->get($longCacheKey);

                if ($baseStats === null) {
                    // Single optimized query untuk semua statistik
                    $stats = $this->getSingleQueryStats();

                    // Cache base stats untuk 1 jam (data yang jarang berubah)
                    $baseStats = [
                        'total' => $stats['total'],
                        'month' => $stats['month'],
                        'year' => $stats['year']
                    ];
                    $cache->save($longCacheKey, $baseStats, $this->longCacheExpiry);
                } else {
                    // Ambil data real-time hanya untuk today, week, online
                    $realtimeStats = $this->getRealtimeStats();
                    $stats = array_merge($baseStats, $realtimeStats);
                }

                // Cache hasil akhir untuk 5 menit
                $cache->save($cacheKey, $stats, $this->cacheExpiry);
            }

            return $stats;
        } catch (\Exception $e) {
            // Fallback jika cache atau database error
            log_message('error', 'Visitor stats error: ' . $e->getMessage());

            return [
                'total' => 0,
                'today' => 0,
                'week' => 0,
                'month' => 0,
                'year' => 0,
                'online' => 0
            ];
        }
    }

    /**
     * Get only real-time statistics (today, week, online) - OPTIMIZED VERSION
     */
    private function getRealtimeStats()
    {
        $db = \Config\Database::connect();

        // OPTIMASI: Gunakan traffic_summary untuk today dan week (jauh lebih cepat)
        // Query online dioptimasi dengan limit dan menggunakan date filter untuk mengurangi scan
        $threshold = time() - 300; // 5 menit dalam timestamp
        $today = date('Y-m-d');
        
        $sql = "SELECT 
                    (SELECT COALESCE(SUM(total_hits), 0) FROM traffic_summary WHERE date = CURDATE()) as today,
                    (SELECT COALESCE(SUM(total_hits), 0) FROM traffic_summary WHERE YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)) as week,
                    (SELECT COUNT(DISTINCT ip) FROM traffic WHERE date >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND CAST(online AS UNSIGNED) >= ?) as online";

        $result = $db->query($sql, [$threshold])->getRow();

        return [
            'today' => (int)($result->today ?? 0),
            'week' => (int)($result->week ?? 0),
            'online' => (int)($result->online ?? 0)
        ];
    }

    /**
     * Single query untuk mendapatkan semua statistik sekaligus - ULTRA FAST VERSION
     * OPTIMASI: Gunakan traffic_summary untuk semua statistik kecuali online
     */
    private function getSingleQueryStats()
    {
        $db = \Config\Database::connect();

        // OPTIMASI: Gunakan traffic_summary untuk semua statistik (jauh lebih cepat dari 1 juta rows)
        // Query online dioptimasi dengan menambahkan filter date untuk menggunakan index date
        $threshold = time() - 300; // 5 menit dalam timestamp
        
        $sql = "SELECT 
                    (SELECT COALESCE(SUM(total_hits), 0) FROM traffic_summary) as total,
                    (SELECT COALESCE(SUM(total_hits), 0) FROM traffic_summary WHERE date = CURDATE()) as today,
                    (SELECT COALESCE(SUM(total_hits), 0) FROM traffic_summary WHERE YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)) as week,
                    (SELECT COALESCE(SUM(total_hits), 0) FROM traffic_summary WHERE YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())) as month,
                    (SELECT COALESCE(SUM(total_hits), 0) FROM traffic_summary WHERE YEAR(date) = YEAR(CURDATE())) as year,
                    (SELECT COUNT(DISTINCT ip) FROM traffic WHERE date >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND CAST(online AS UNSIGNED) >= ?) as online";

        $result = $db->query($sql, [$threshold])->getRow();

        return [
            'total' => (int)($result->total ?? 0),
            'today' => (int)($result->today ?? 0),
            'week' => (int)($result->week ?? 0),
            'month' => (int)($result->month ?? 0),
            'year' => (int)($result->year ?? 0),
            'online' => (int)($result->online ?? 0)
        ];
    }

    /**
     * Update summary table untuk hari ini
     */
    public function updateTodaySummary()
    {
        try {
            $db = \Config\Database::connect();
            $today = date('Y-m-d');

            $sql = "INSERT INTO traffic_summary (date, total_hits, total_visitors) 
                    SELECT ?, SUM(hits), COUNT(DISTINCT ip) FROM traffic WHERE date = ?
                    ON DUPLICATE KEY UPDATE 
                    total_hits = VALUES(total_hits), 
                    total_visitors = VALUES(total_visitors)";

            $db->query($sql, [$today, $today]);

            // Clear cache setelah update
            $cache = \Config\Services::cache();
            $cache->delete('visitor_stats');
            $cache->delete('visitor_stats_long');
        } catch (\Exception $e) {
            log_message('error', 'Summary update error: ' . $e->getMessage());
        }
    }

    /**
     * Clear stats cache
     */
    private function clearStatsCache()
    {
        $cache = \Config\Services::cache();
        $cache->delete('visitor_stats');
    }

    /**
     * Mendapatkan IP address client yang sebenarnya
     */
    private function getClientIP()
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Deteksi Operating System dari User Agent (optimized)
     */
    private function getOS($userAgent)
    {
        if (empty($userAgent)) return 'Unknown';

        $osArray = [
            'Windows 11' => '/Windows NT 10\.0/',
            'Windows 10' => '/Windows NT 10\.0/',
            'Mac OS X' => '/Mac OS X/',
            'Linux' => '/Linux/',
            'Android' => '/Android/',
            'iOS' => '/iPhone|iPad|iPod/',
        ];

        foreach ($osArray as $os => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $os;
            }
        }

        return 'Unknown';
    }

    /**
     * Deteksi Platform/Device dari User Agent (optimized)
     */
    private function getPlatform($userAgent)
    {
        if (empty($userAgent)) return 'Desktop';

        if (preg_match('/mobile|android|iphone|phone/i', $userAgent)) {
            return 'Mobile';
        } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'Tablet';
        }
        return 'Desktop';
    }

    // Optimized queries dengan index
    public function getTotalVisitors()
    {
        return $this->selectSum('hits')->get()->getRow()->hits ?? 0;
    }

    public function getTodayVisitors()
    {
        return $this->selectSum('hits')
            ->where('date', date('Y-m-d'))
            ->get()->getRow()->hits ?? 0;
    }

    public function getMonthVisitors()
    {
        return $this->selectSum('hits')
            ->where('YEAR(date)', date('Y'))
            ->where('MONTH(date)', date('m'))
            ->get()->getRow()->hits ?? 0;
    }

    public function getYearVisitors()
    {
        return $this->selectSum('hits')
            ->where('YEAR(date)', date('Y'))
            ->get()->getRow()->hits ?? 0;
    }

    public function getWeekVisitors()
    {
        return $this->selectSum('hits')
            ->where("YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)", null, false)
            ->get()->getRow()->hits ?? 0;
    }

    public function getOnlineVisitors()
    {
        $threshold = time() - 900; // 15 menit
        return $this->where('online >=', $threshold)->countAllResults();
    }

    /**
     * Data pengunjung untuk grafik dengan cache
     */
    public function getVisitorChart($days = 7)
    {
        $cache = \Config\Services::cache();
        $cacheKey = "visitor_chart_{$days}";

        $data = $cache->get($cacheKey);

        if ($data === null) {
            $data = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $hits = $this->selectSum('hits')
                    ->where('date', $date)
                    ->get()->getRow()->hits ?? 0;

                $data[] = [
                    'date' => $date,
                    'hits' => $hits,
                    'label' => date('d M', strtotime($date))
                ];
            }

            // Cache untuk 1 jam
            $cache->save($cacheKey, $data, 3600);
        }

        return $data;
    }

    /**
     * Top pengunjung dengan cache
     */
    public function getTopVisitors($limit = 10)
    {
        $cache = \Config\Services::cache();
        $cacheKey = "top_visitors_{$limit}";

        $data = $cache->get($cacheKey);

        if ($data === null) {
            $data = $this->select('ip, SUM(hits) as total_hits, MAX(date) as last_visit')
                ->groupBy('ip')
                ->orderBy('total_hits', 'DESC')
                ->limit($limit)
                ->get()->getResultArray();

            // Cache untuk 1 jam
            $cache->save($cacheKey, $data, 3600);
        }

        return $data;
    }

    /**
     * Cleanup data lama (jalankan via cron job)
     */
    public function cleanupOldData()
    {
        $oneYearAgo = date('Y-m-d', strtotime('-1 year'));
        return $this->where('date <', $oneYearAgo)->delete();
    }

    /**
     * Get monthly statistics for the last N months
     * 
     * @param int $months Number of months to get data for
     * @return array
     */
    public function getMonthlyStats($months = 12)
    {
        $cache = \Config\Services::cache();
        $cacheKey = "monthly_stats_{$months}";

        $data = $cache->get($cacheKey);

        if ($data === null) {
            $data = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $date = date('Y-m-01', strtotime("-{$i} months"));
                $year = date('Y', strtotime($date));
                $month = date('m', strtotime($date));

                $hits = $this->selectSum('hits')
                    ->where('YEAR(date)', $year)
                    ->where('MONTH(date)', $month)
                    ->get()->getRow()->hits ?? 0;

                $visitors = $this->select('COUNT(DISTINCT ip) as unique_visitors')
                    ->where('YEAR(date)', $year)
                    ->where('MONTH(date)', $month)
                    ->get()->getRow()->unique_visitors ?? 0;

                $data[] = [
                    'year' => $year,
                    'month' => $month,
                    'hits' => $hits,
                    'visitors' => $visitors,
                    'label' => date('M Y', strtotime($date))
                ];
            }

            // Cache untuk 2 jam
            $cache->save($cacheKey, $data, 7200);
        }

        return $data;
    }

    /**
     * Get yearly statistics for the last N years
     * 
     * @param int $years Number of years to get data for
     * @return array
     */
    public function getYearlyStats($years = 5)
    {
        $cache = \Config\Services::cache();
        $cacheKey = "yearly_stats_{$years}";

        $data = $cache->get($cacheKey);

        if ($data === null) {
            $data = [];
            for ($i = $years - 1; $i >= 0; $i--) {
                $year = date('Y', strtotime("-{$i} years"));

                $hits = $this->selectSum('hits')
                    ->where('YEAR(date)', $year)
                    ->get()->getRow()->hits ?? 0;

                $visitors = $this->select('COUNT(DISTINCT ip) as unique_visitors')
                    ->where('YEAR(date)', $year)
                    ->get()->getRow()->unique_visitors ?? 0;

                $data[] = [
                    'year' => $year,
                    'hits' => $hits,
                    'visitors' => $visitors,
                    'label' => $year
                ];
            }

            // Cache untuk 4 jam
            $cache->save($cacheKey, $data, 14400);
        }

        return $data;
    }
}
