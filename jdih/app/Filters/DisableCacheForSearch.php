<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\CodeIgniter;

/**
 * Filter untuk disable page cache jika ada parameter filter pencarian
 * 
 * MASALAH YANG DIPERBAIKI:
 * - CodeIgniter cek cache SEBELUM controller method dijalankan
 * - Jika cache ditemukan, controller method TIDAK PERNAH DIEKSEKUSI
 * - Logika skip cache di controller (line 487-505) TIDAK PERNAH DIEKSEKUSI jika cache sudah ada
 * - Akibatnya: Hasil pencarian menampilkan data dari cache (tidak realtime)
 * 
 * SOLUSI:
 * - Filter ini dijalankan SEBELUM cache check di CodeIgniter
 * - Jika ada parameter filter (keyword, jenis, tahun, status, tag), set cacheTTL = 0
 * - Ini memastikan controller method selalu dijalankan untuk pencarian
 * - Hasil pencarian selalu fresh dan realtime
 */
class DisableCacheForSearch implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Cek apakah ini route peraturan
        $uri = $request->getUri();
        $path = $uri->getPath();
        
        // Hanya apply untuk route peraturan
        if (strpos($path, '/peraturan') === false) {
            return;
        }
        
        // Ambil semua parameter GET
        $keyword = $request->getGet('keyword');
        $jenis = $request->getGet('jenis');
        $tahun = $request->getGet('tahun');
        $status = $request->getGet('status');
        $tag = $request->getGet('tag');
        $sort = $request->getGet('sort');
        
        // Cek apakah ada parameter filter pencarian
        $hasFilters = !empty($keyword) 
            || !empty($tag) 
            || !empty($jenis) 
            || !empty($tahun) 
            || !empty($status);
        
        // CRITICAL: Jika ada filter, disable page cache
        // Ini dilakukan SEBELUM CodeIgniter cek cache, sehingga cache tidak akan digunakan
        if ($hasFilters) {
            // Set cacheTTL = 0 untuk mencegah CodeIgniter menggunakan cache
            // Ini akan memastikan controller method selalu dijalankan
            CodeIgniter::cache(0);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Set no-cache headers untuk search dengan filter
        $uri = $request->getUri();
        $path = $uri->getPath();
        
        // Hanya apply untuk route peraturan
        if (strpos($path, '/peraturan') === false) {
            return;
        }
        
        // Ambil semua parameter GET
        $keyword = $request->getGet('keyword');
        $jenis = $request->getGet('jenis');
        $tahun = $request->getGet('tahun');
        $status = $request->getGet('status');
        $tag = $request->getGet('tag');
        
        // Cek apakah ada parameter filter pencarian
        $hasFilters = !empty($keyword) 
            || !empty($tag) 
            || !empty($jenis) 
            || !empty($tahun) 
            || !empty($status);
        
        if ($hasFilters) {
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
            $response->setHeader('X-Accel-Expires', '0'); // Nginx specific
        }
    }
}

