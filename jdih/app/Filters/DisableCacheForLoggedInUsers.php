<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\CodeIgniter;

/**
 * Filter untuk disable page cache jika user sudah login
 * 
 * MASALAH YANG DIPERBAIKI:
 * - CodeIgniter cachePage() menggunakan cache key yang sama untuk semua user (hanya berdasarkan URI)
 * - Ketika guest user mengakses halaman, HTML di-cache dengan navbar "Admin Panel"
 * - Ketika logged-in user mengakses, CodeIgniter cek cache SEBELUM controller method dijalankan
 * - Jika cache ditemukan, langsung return cached HTML tanpa menjalankan controller
 * - Akibatnya: Logged-in user melihat navbar "Admin Panel" dari cache guest user
 * 
 * SOLUSI:
 * - Filter ini dijalankan SEBELUM cache check di CodeIgniter
 * - Jika user sudah login, set cacheTTL = 0 untuk mencegah cache digunakan
 * - Ini memastikan controller method selalu dijalankan untuk logged-in users
 */
class DisableCacheForLoggedInUsers implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Cek apakah user sudah login
        $session = service('session');
        $user = $session->get('user');
        
        // Validasi user benar-benar ada dan memiliki data yang valid
        $isUserLoggedIn = !empty($user) && is_array($user) && !empty($user['id_user']) && !empty($user['nama']);
        
        // CRITICAL: Jika user login, disable page cache
        // Ini dilakukan SEBELUM CodeIgniter cek cache, sehingga cache tidak akan digunakan
        if ($isUserLoggedIn) {
            // Set cacheTTL = 0 untuk mencegah CodeIgniter menggunakan cache
            // Ini akan memastikan controller method selalu dijalankan
            CodeIgniter::cache(0);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Set no-cache headers untuk logged-in users
        $session = service('session');
        $user = $session->get('user');
        
        $isUserLoggedIn = !empty($user) && is_array($user) && !empty($user['id_user']) && !empty($user['nama']);
        
        if ($isUserLoggedIn) {
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('X-Cache-Status', 'DISABLED');
            $response->setHeader('X-Accel-Expires', '0'); // Nginx specific
        }
    }
}

