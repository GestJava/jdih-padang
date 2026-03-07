<?php

namespace App\Controllers;

use App\Models\WebTagModel;
use App\Models\VisitorStatsModel;

class Home extends BaseController
{
    protected $webPeraturanModel;
    protected $webTagModel;
    protected $visitorStatsModel;

    public function __construct()
    {
        $this->webPeraturanModel = new \App\Models\WebPeraturanModel();
        $this->webTagModel = new \App\Models\WebTagModel();
        $this->visitorStatsModel = new VisitorStatsModel();
    }

    public function index()
    {
        $this->visitorStatsModel->recordVisitor();

        // CACHING IMPLEMENTATION (10 Minutes)
        // -----------------------------------
        
        // 1. Cache Jenis Peraturan
        if (! $jenis_peraturan = cache('home_jenis_peraturan')) {
            $jenis_peraturan = $this->webPeraturanModel->getJenisPeraturan();
            cache()->save('home_jenis_peraturan', $jenis_peraturan, 600);
        }

        // 2. Cache Latest Peraturan
        if (! $latest_peraturan = cache('home_latest_peraturan')) {
            $latest_peraturan = $this->webPeraturanModel->getLatestPeraturan(8);
            cache()->save('home_latest_peraturan', $latest_peraturan, 600);
        }

        // 3. Cache Popular Tags
        if (! $popular_tags = cache('home_popular_tags')) {
            $popular_tags = $this->webTagModel->getPopularTags(5);
            cache()->save('home_popular_tags', $popular_tags, 600);
        }
        
        // 4. Cache Statistics (Heavy Aggregation Queries)
        if (! $stats = cache('home_visitor_stats')) {
            $stats = [
                'total'  => $this->visitorStatsModel->getTotalVisitors(),
                'today'  => $this->visitorStatsModel->getTodayVisitors(),
                'week'   => $this->visitorStatsModel->getWeekVisitors(),
                'month'  => $this->visitorStatsModel->getMonthVisitors(),
                'year'   => $this->visitorStatsModel->getYearVisitors(),
                'online' => $this->visitorStatsModel->getOnlineVisitors(),
            ];
            cache()->save('home_visitor_stats', $stats, 600);
        }

        $data = [
            'title' => 'JDIH - Jaringan Dokumentasi dan Informasi Hukum',
            'jenis_peraturan' => $jenis_peraturan,
            'latest_peraturan' => $latest_peraturan,
            'popular_tags' => $popular_tags,
            'stat_total'  => $stats['total'],
            'stat_today'  => $stats['today'],
            'stat_week'   => $stats['week'],
            'stat_month'  => $stats['month'],
            'stat_year'   => $stats['year'],
            'stat_online' => $stats['online'],
        ];

        return view('frontend/pages/home-optimized', $data);
    }

    public function recordVisitor()
    {
        $this->visitorStatsModel->recordVisitor();
        return $this->response->setJSON(['status' => 'success']);
    }
}
