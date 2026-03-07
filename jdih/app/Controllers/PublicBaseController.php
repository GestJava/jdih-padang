<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Services;
use Config\App;

class PublicBaseController extends Controller
{
    protected $session;
    protected $config;
    protected $helpers = [
        'url',
        'form',
        'html',
        'text',
        'date',
        'util',
        'functions',
        'page_history',
        'metadata'
    ]; // Helper umum yang mungkin berguna

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Panggil parent constructor
        parent::initController($request, $response, $logger);

        // Inisialisasi properti
        $this->session = Services::session();
        $this->config = new App();

        // Set default timezone jika belum diatur di tempat lain
        date_default_timezone_set('Asia/Jakarta');

        // Muat semua helper yang diperlukan setelah framework siap
        $this->initializeHelpers();

        // Check jika user belum login untuk protected routes
        if (!$this->session->get('user_id')) {
            // Hanya redirect untuk protected routes
            $protected_routes = ['dashboard', 'admin', 'profile', 'settings'];
            $current_uri = uri_string();

            foreach ($protected_routes as $route) {
                if (strpos($current_uri, $route) !== false) {
                    return redirect()->to('/');
                }
            }
        }
    }

    /**
     * Metode helper untuk memuat view dengan header dan footer standar (jika ada).
     * Anda bisa menyesuaikan ini sesuai struktur tema frontend Anda.
     */
    protected function renderView(string $viewName, array $data = [], string $theme = 'frontend')
    {
        // Tambahkan data global ke semua view jika perlu
        $data['config'] = $this->config;
        $data['request'] = $this->request;
        $data['session'] = $this->session;
        // Anda bisa menambahkan data lain yang umum untuk halaman publik di sini

        // Contoh sederhana, Anda mungkin memiliki header/footer terpisah
        // echo view($theme . '/template/header', $data);
        echo view($theme . '/pages/' . $viewName, $data); // Asumsi view ada di 'Views/frontend/pages/'
        // echo view($theme . '/template/footer', $data);
    }

    protected function initializeHelpers()
    {
        // Load helper untuk sistem navigasi dan metadata
        helper([
            'url',
            'html',
            'form',
            'page_history',
            'metadata',
            'tanggal',
            'util'
        ]);
    }

    // Anda bisa menambahkan metode lain yang berguna untuk controller publik di sini
}
