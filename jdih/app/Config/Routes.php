<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Router\RouteCollection;
use Config\Services;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
|--------------------------------------------------------------------------
| Router Setup
|--------------------------------------------------------------------------
*/
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Frontend');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(true);
$routes->set404Override(function () {
    return view('errors/html/error_404');
});
$routes->setAutoRoute(true); // demi keamanan

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
*/
$routes->match(['get', 'post'], '/login', 'Login::index', ['as' => 'login']);
$routes->get('/logout', 'Login::logout', ['as' => 'logout']);

/*
|--------------------------------------------------------------------------
| FRONTEND ROUTES
|--------------------------------------------------------------------------
*/
$routes->get('/', 'Frontend::index');
$routes->get('sitemap.xml', 'Sitemap::index');
/* 
$routes->get('/home-optimized', 'Frontend::homeOptimized'); // Testing route for optimized home
$routes->get('/test-home', 'Frontend::testHome'); // Simple test route
$routes->get('/simple-test', 'Frontend::simpleTest'); // Very simple test route
$routes->get('/test-components', 'Frontend::testComponents'); // Test components route
$routes->get('/test-real-data', 'Frontend::testRealData'); // Test real data route
$routes->get('/test-models', 'Frontend::testModels'); // Test models route
$routes->get('/test-home-no-cache', 'Frontend::testHomeNoCache'); // Test home no cache route
$routes->get('/test-sample-data', 'Frontend::testSampleData'); // Test sample data route
$routes->get('/debug-data', 'DebugData::index'); // Debug route for testing data
*/
// TEST ROUTES - HANYA UNTUK DEVELOPMENT, JANGAN DEPLOY KE PRODUCTION!
if (ENVIRONMENT !== 'production') {
    $routes->get('/test-search', 'TestSearch::index'); // Test search functionality
    $routes->get('/check-database', 'CheckDatabase::index'); // Check database for debugging
}
$routes->get('peraturan', 'Frontend::peraturan');
$routes->get('peraturan/jenis/(:segment)', 'Frontend::peraturan/$1');
$routes->get('peraturan/terbaru', 'Frontend::peraturan', ['terbaru' => true]);
$routes->get('peraturan/populer', 'Frontend::peraturan', ['populer' => true]);
//$routes->get('peraturan/search', 'Peraturan::search');
$routes->get('peraturan/search', 'Frontend::peraturan');
$routes->get('peraturan/(:segment)', 'Peraturan::detail/$1');
$routes->get('peraturan/detail/(:num)', 'Peraturan::detailById/$1');
$routes->get('peraturan/download/(:num)', 'Peraturan::download/$1');
$routes->get('peraturan/download_lampiran/(:num)', 'Peraturan::download_lampiran/$1');
$routes->get('dokumen/kategori/(:segment)', 'Dokumen::kategori/$1');
$routes->get('dokumen/jenis/(:segment)', 'Dokumen::jenis/$1');
$routes->get('dokumen/tag/(:segment)', 'Dokumen::tag/$1');
$routes->get('berita', 'Frontend::berita');
$routes->get('berita/kategori/(:segment)', 'Frontend::berita/$1');
$routes->get('berita/(:segment)', 'Frontend::detailBerita/$1');
$routes->post('feedback/submit', 'Frontend::submitFeedback');
$routes->get('tentang', 'Frontend::tentang');
$routes->match(['get', 'post'], 'kontak', 'Frontend::kontak');
$routes->get('statistik', 'Frontend::statistik');
$routes->get('agenda', 'Frontend::agenda');
$routes->get('agenda/(:segment)', 'Frontend::detailAgenda/$1');
// ... existing code ...
//$routes->get('agenda/detail/(:segment)', 'Frontend::detailAgenda/$1');
// ... existing code ...
$routes->get('panduan', 'Frontend::panduan');
$routes->get('panduan-harmonisasi', 'Frontend::panduanHarmonisasi');

$routes->get('kebijakan-privasi', 'Frontend::kebijakanPrivasi');
$routes->get('syarat-ketentuan', 'Frontend::syaratKetentuan');
$routes->get('chatbot', 'Frontend::chatbot');
$routes->get('struktur-organisasi', 'Frontend::strukturOrganisasi');
$routes->get('sop', 'Frontend::sop');
$routes->get('visitor-stats', 'Frontend::getVisitorStats');
$routes->get('test-popular-keywords', 'Frontend::testPopularKeywords');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Requires Auth Filter)
|--------------------------------------------------------------------------
*/
$routes->group('', ['filter' => 'auth'], static function ($routes) {

    $routes->get('/dashboard', 'Dashboard::index');
    $routes->get('penugasan-dashboard', 'Harmonisasi::penugasanDashboard');

    // Penugasan (Kabag Hukum)
    $routes->group('penugasan', function ($routes) {
        $routes->get('/', 'Penugasan::index');
        $routes->get('tugaskan/(:num)', 'Penugasan::tugaskan/$1');
        $routes->post('assign', 'Penugasan::assign');
        $routes->post('proses-tugaskan', 'Penugasan::prosesTugaskan');
    });

    // Harmonisasi
    $routes->group('harmonisasi', function ($routes) {
        $routes->get('/', 'Harmonisasi::index');
        $routes->get('new', 'Harmonisasi::new');
        $routes->post('ajukan/(:num)', 'Harmonisasi::submit/$1'); // Route untuk mengajukan draft
        $routes->post('create', 'Harmonisasi::create');
        $routes->get('edit/(:num)', 'Harmonisasi::edit/$1'); // Route untuk edit draft
        $routes->post('update/(:num)', 'Harmonisasi::update/$1'); // Route untuk update draft
        //  $routes->get('show/(:num)', 'Harmonisasi::show/$1');
        $routes->get('detail/(:num)', 'Harmonisasi::show/$1'); // Added detail route mapping to show method
        $routes->get('download/(:num)', 'Harmonisasi::download/$1'); // Route untuk download dokumen
        $routes->get('tugaskan/(:num)', 'Harmonisasi::tugaskan/$1');
        $routes->post('assign', 'Harmonisasi::assign');
        $routes->post('verifikasi-selesai/(:num)', 'Harmonisasi::verifikasiSelesai/$1');
    });

    // Hasil (Modul terpisah untuk status Selesai & Ditolak)
    $routes->group('hasil', function ($routes) {
        $routes->get('/', 'Hasil::index');
        $routes->post('delete/(:num)', 'Hasil::delete/$1');
        $routes->post('sync/(:num)', 'Hasil::sync/$1'); // Route for Sync Ajuan to Peraturan
    });

    // Verifikasi
    $routes->group('verifikasi', function ($routes) {
        $routes->get('/', 'Verifikasi::index');
        $routes->get('proses/(:num)', 'Verifikasi::proses/$1');
        $routes->post('submitAksi', 'Verifikasi::submitAksi');
        $routes->get('download/(:num)', 'Verifikasi::download/$1'); // Route untuk download dokumen
    });

    // Validasi
    $routes->group('validasi', function ($routes) {
        $routes->get('/', 'Validasi::index');
        $routes->get('proses/(:num)', 'Validasi::proses/$1');
        $routes->post('submitAksi', 'Validasi::submitAksi');
        $routes->get('download/(:num)', 'Validasi::download/$1'); // Route untuk download dokumen

    });

    // Finalisasi
    $routes->group('finalisasi', function ($routes) {
        $routes->get('/', 'Finalisasi::index');
        $routes->get('proses/(:num)', 'Finalisasi::proses/$1');
        $routes->post('submitAksi', 'Finalisasi::submitAksi');
        $routes->get('download/(:num)', 'Finalisasi::download/$1'); // Route untuk download dokumen

    });

    // Paraf
    $routes->group('paraf', function ($routes) {
        $routes->get('/', 'Paraf::index');
        $routes->get('menungguParaf/(:segment)', 'Paraf::menungguParaf/$1');
        $routes->get('prosesParaf/(:num)', 'Paraf::prosesParaf/$1');
        $routes->post('submitParaf', 'Paraf::submitParaf');
        $routes->get('download/(:num)', 'Paraf::download/$1'); // Route untuk download dokumen
        $routes->get('detail/(:num)', 'Paraf::detail/$1'); // Route untuk detail ajuan
    });

    // Data Peraturan - Explicit routes for better reliability
    $routes->group('data_peraturan', function ($routes) {
        $routes->get('/', 'Data_peraturan::index');
        $routes->get('add', 'Data_peraturan::add');
        $routes->post('add', 'Data_peraturan::add');
        $routes->get('edit', 'Data_peraturan::edit');
        $routes->post('edit', 'Data_peraturan::edit');
        $routes->post('delete', 'Data_peraturan::delete');
        $routes->get('relasi_peraturan', 'Data_peraturan::relasi_peraturan');
        $routes->post('save_relasi', 'Data_peraturan::save_relasi');
        $routes->get('delete_relasi', 'Data_peraturan::delete_relasi');
        $routes->get('lampiran', 'Data_peraturan::lampiran');
        $routes->post('save_lampiran', 'Data_peraturan::save_lampiran');
        $routes->post('delete_lampiran', 'Data_peraturan::delete_lampiran');

        // AJAX endpoints
        $routes->match(['get', 'post'], 'ajax_list', 'Data_peraturan::ajax_list');
        $routes->get('getDataDT', 'Data_peraturan::getDataDT');
        $routes->get('getRelasiDataDT', 'Data_peraturan::getRelasiDataDT');
        $routes->get('ajaxGetPeraturan', 'Data_peraturan::ajaxGetPeraturan');
        $routes->get('ajaxGetInstansi', 'Data_peraturan::ajaxGetInstansi');
        $routes->get('ajaxGetTag', 'Data_peraturan::ajaxGetTag');
        $routes->post('add_tag_ajax', 'Data_peraturan::add_tag_ajax');
        $routes->get('ajaxSearchPeraturan', 'Data_peraturan::ajaxSearchPeraturan');
        $routes->get('ajaxGetJenisRelasiInfo', 'Data_peraturan::ajaxGetJenisRelasiInfo');
        $routes->get('getLampiranDataDT', 'Data_peraturan::getLampiranDataDT');
        $routes->post('delete_ajax', 'Data_peraturan::delete_ajax');

        // Metadata API endpoints
        $routes->get('getMetadataTemplate', 'Data_peraturan::getMetadataTemplate');
    });

    // Data Peraturan dengan dash - untuk kompatibilitas URL
    $routes->group('data-peraturan', function ($routes) {
        $routes->get('/', 'Data_peraturan::index');
        $routes->get('add', 'Data_peraturan::add');
        $routes->post('add', 'Data_peraturan::add');
        $routes->get('edit', 'Data_peraturan::edit');
        $routes->post('edit', 'Data_peraturan::edit');
        $routes->post('delete', 'Data_peraturan::delete');
        $routes->get('relasi_peraturan', 'Data_peraturan::relasi_peraturan');
        $routes->post('save_relasi', 'Data_peraturan::save_relasi');
        $routes->get('delete_relasi', 'Data_peraturan::delete_relasi');
        $routes->get('lampiran', 'Data_peraturan::lampiran');
        $routes->post('save_lampiran', 'Data_peraturan::save_lampiran');
        $routes->post('delete_lampiran', 'Data_peraturan::delete_lampiran');

        // AJAX endpoints
        $routes->match(['get', 'post'], 'ajax_list', 'Data_peraturan::ajax_list');
        $routes->get('getDataDT', 'Data_peraturan::getDataDT');
        $routes->get('getRelasiDataDT', 'Data_peraturan::getRelasiDataDT');
        $routes->get('ajaxGetPeraturan', 'Data_peraturan::ajaxGetPeraturan');
        $routes->get('ajaxGetInstansi', 'Data_peraturan::ajaxGetInstansi');
        $routes->get('ajaxGetTag', 'Data_peraturan::ajaxGetTag');
        $routes->post('add_tag_ajax', 'Data_peraturan::add_tag_ajax');
        $routes->get('ajaxSearchPeraturan', 'Data_peraturan::ajaxSearchPeraturan');
        $routes->get('ajaxGetJenisRelasiInfo', 'Data_peraturan::ajaxGetJenisRelasiInfo');
        $routes->get('getLampiranDataDT', 'Data_peraturan::getLampiranDataDT');
        $routes->post('delete_ajax', 'Data_peraturan::delete_ajax');

        // Metadata API endpoints
        $routes->get('getMetadataTemplate', 'Data_peraturan::getMetadataTemplate');
    });

    // Redirect permanen dari underscore → dash untuk backward-compatibility
    $routes->addRedirect('maintenance_notice', 'maintenance-notice');
    $routes->addRedirect('maintenance_notice/(:segment)', 'maintenance-notice/$1');

    // Maintenance Notice - admin CRUD
    $routes->group('maintenance-notice', function ($routes) {
        $routes->get('/', 'Maintenance_notice::index');
        $routes->get('getDataDT', 'Maintenance_notice::getDataDT');
        $routes->get('add', 'Maintenance_notice::add');
        $routes->post('add', 'Maintenance_notice::add');
        $routes->get('edit', 'Maintenance_notice::edit');
        $routes->post('edit', 'Maintenance_notice::edit');
        $routes->post('delete', 'Maintenance_notice::delete');
        $routes->match(['get', 'post'], 'ajax_list', 'Maintenance_notice::ajax_list');
    });

    // Legalisasi - TTE dan Paraf Dokumen
    $routes->group('legalisasi', function ($routes) {
        // Dashboard routes
        $routes->get('/', 'Legalisasi::index');
        // Versi dash dan slash (kompatibel dengan link di view)
        $routes->get('dashboard-sekda', 'Legalisasi::dashboardSekda');
        $routes->get('dashboard/sekda', 'Legalisasi::dashboardSekda');
        $routes->get('dashboard-walikota', 'Legalisasi::dashboardWalikota');
        $routes->get('dashboard/walikota', 'Legalisasi::dashboardWalikota');
        $routes->get('dashboard-wawako', 'Legalisasi::dashboardWawako');
        $routes->get('dashboard/wawako', 'Legalisasi::dashboardWawako');
        $routes->get('dashboard-asisten', 'Legalisasi::dashboardAsisten');
        $routes->get('dashboard/asisten', 'Legalisasi::dashboardAsisten');
        $routes->get('dashboard-opd', 'Legalisasi::dashboardOpd');
        $routes->get('dashboard/opd', 'Legalisasi::dashboardOpd');
        $routes->get('dashboard-kabag', 'Legalisasi::dashboardKabag');
        $routes->get('dashboard/kabag', 'Legalisasi::dashboardKabag');
        
        // Document routes
        $routes->get('detail/(:num)', 'Legalisasi::detail/$1');
        $routes->get('preview/(:num)', 'Legalisasi::preview/$1');
        $routes->get('download/(:num)', 'Legalisasi::download/$1');
        
        // TTE endpoints
        $routes->post('process-tte/(:num)', 'Legalisasi::processTTE/$1');
        $routes->post('process-tte-sekda/(:num)', 'Legalisasi::processTTESekda/$1');
        $routes->post('process-paraf/(:num)', 'Legalisasi::processParaf/$1');
        $routes->post('check-tte-certificate', 'Legalisasi::checkTteCertificate');
        $routes->post('validate-tte', 'Legalisasi::validateTTE');
        $routes->get('get-csrf-token', 'Legalisasi::getCsrfToken');
        $routes->get('get-ajuan-data/(:num)', 'Legalisasi::getAjuanData/$1');
        $routes->get('get-final-paraf-document/(:num)', 'Legalisasi::getFinalParafDocument/$1');
        
/*
        // Testing endpoints
        $routes->get('test-esign', 'Legalisasi::testESign');
        $routes->get('test-esign-connection', 'Legalisasi::testESignConnection');
        $routes->get('test-esign-auth', 'Legalisasi::testESignAuth');
        $routes->get('test-esign-auth-wrong', 'Legalisasi::testESignAuthWrong');
        $routes->get('test-esign-certificate', 'Legalisasi::testESignCertificate');
        $routes->get('test-esign-tte-service', 'Legalisasi::testESignTTEService');
        $routes->get('test-esign-all', 'Legalisasi::testESignAll');
        $routes->post('test-esign-real-certificate', 'Legalisasi::testESignRealCertificate');
        $routes->get('get-testing-credentials', 'Legalisasi::getTestingCredentials');
        $routes->get('get-tte-sekda-status', 'Legalisasi::getTTESekdaStatus');
*/
        $routes->get('generate-qr-code/(:num)', 'Legalisasi::generateQRCode/$1');
    });
    
    // Laporan - Standalone controller (Monitoring, Riwayat TTE, Laporan)
    $routes->get('laporan', 'Laporan::index');
    $routes->get('laporan/monitoring', 'Laporan::monitoring');
    $routes->get('laporan/riwayat-tte', 'Laporan::historyTte');
    $routes->get('laporan/history-tte', 'Laporan::historyTte');
});

/*
|--------------------------------------------------------------------------
| API ROUTES
|--------------------------------------------------------------------------
*/
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->post('chatbot/ask', 'ChatbotController::ask');
    $routes->post('chatbot/feedback', 'ChatbotController::feedback');
    // TODO: Implement new TTE API routes
    $routes->post('tte/callback', 'TteController::callback');
    
    // TTE API Routes
    $routes->post('tte/check-status', 'TTEController::cekStatusUser');
    $routes->post('tte/verify', 'TTEController::verifyDocument');
    $routes->post('tte/sign', 'TTEController::signDocument');
});

/*
|--------------------------------------------------------------------------
| JDIH INTEGRATION API ROUTES (Public Access)
|--------------------------------------------------------------------------
*/
// Route untuk integrasi JDIH Pusat
$routes->get('integrasiJDIH/integrasipadang', 'Api\JdihIntegrationController::integrasipadang');
$routes->get('integrasiJDIH/integrasipdg', 'Api\JdihIntegrationController::integrasipdg');
$routes->get('integrasiJDIH/integrasipadang-optimized', 'Api\JdihIntegrationController::integrasipadangOptimized');
$routes->get('integrasiJDIH/integrasipadang.php', 'Api\JdihIntegrationController::integrasipadangPhp');
$routes->get('integrasiJDIH/integrasipdg.php', 'Api\JdihIntegrationController::integrasipdg');

// JDIH Integration API Tracking Routes
$routes->get('integrasiJDIH/dashboard', 'Api\JdihIntegrationController::dashboard');
$routes->get('integrasiJDIH/stats', 'Api\JdihIntegrationController::stats');
$routes->get('integrasiJDIH/logs/(:segment)', 'Api\JdihIntegrationController::logs/$1');
$routes->get('integrasiJDIH/logfiles', 'Api\JdihIntegrationController::logfiles');

// JDIH Integration API Rate Limiting Management Routes
$routes->get('integrasiJDIH/rate-limit', 'Api\JdihIntegrationController::rateLimitStatus');
$routes->post('integrasiJDIH/unblock', 'Api\JdihIntegrationController::unblockIp');
$routes->get('integrasiJDIH/blocked-ips', 'Api\JdihIntegrationController::listBlockedIps');
$routes->post('integrasiJDIH/cleanup-rate-limit', 'Api\JdihIntegrationController::cleanupRateLimit');

// Visitor Stats API
$routes->get('api/visitor-stats', 'Frontend::getVisitorStats');

// TTS API
$routes->get('tts/synthesize', 'Tts::synthesize');
/*
|--------------------------------------------------------------------------
| PUBLIC API ROUTES (No authentication required)
|--------------------------------------------------------------------------
*/
$routes->get('api/tags/search', 'Api::searchTags');
$routes->get('api/tags/all', 'Api::getAllTags');
$routes->post('api/tags/create', 'Api::createTag');

// Test routes untuk debugging
/*
$routes->get('test/tags/search', 'TagsController::search');
$routes->get('test/tags/all', 'TagsController::all');
$routes->post('test/tags/create', 'TagsController::create');
*/

/*
|--------------------------------------------------------------------------
| VISITOR STATS API
|--------------------------------------------------------------------------
*/
$routes->get('api/visitor-stats', 'Frontend::getVisitorStats');

/*
|--------------------------------------------------------------------------
| TTE Testing Routes - TODO: Implement new TTE testing
|--------------------------------------------------------------------------
*/
// $routes->group('test-tte', function ($routes) {
//     $routes->get('', 'NewTteController::index');
//     $routes->match(['get', 'post'], 'test', 'NewTteController::test');
// });

/*
|--------------------------------------------------------------------------
| Environment-Based Routes
|--------------------------------------------------------------------------
*/
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}

// Data Agenda - admin CRUD
$routes->group('data_agenda', function ($routes) {
    $routes->get('/', 'Data_agenda::index');
    $routes->match(['post', 'get'], 'ajax_list', 'Data_agenda::ajax_list');
    $routes->get('getDataDT', 'Data_agenda::getDataDT');
    $routes->get('add', 'Data_agenda::add');
    $routes->post('add', 'Data_agenda::add');
    $routes->get('edit', 'Data_agenda::edit');
    $routes->post('edit', 'Data_agenda::edit');
    $routes->post('delete', 'Data_agenda::delete');
    // TODO: add add/edit routes later
});

// Harmonisasi AJAX (Server-side DataTables)
$routes->group('ajax/harmonisasi', ['filter' => 'auth'], function ($routes) {
    $routes->post('/', 'HarmonisasiAjax::list');
    $routes->post('hasil', 'HarmonisasiAjax::listHasil');
});

$routes->get('artikel_hukum/getDataDT', 'Artikel_hukum::getDataDT');

