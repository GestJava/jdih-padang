<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\WebPeraturanModel;
use App\Models\BeritaModel;

class Sitemap extends Controller
{
    public function index()
    {
        $peraturanModel = new WebPeraturanModel();
        $beritaModel = new BeritaModel();

        // 1. Static Pages
        $urls = [
            base_url('/'),
            base_url('peraturan'),
            base_url('berita'),
            base_url('agenda'),
            base_url('statistik'),
            base_url('tentang'),
            base_url('kontak'),
            base_url('panduan'),
            base_url('struktur-organisasi'),
            base_url('sop'),
            base_url('kebijakan-privasi'),
            base_url('syarat-ketentuan'),
        ];

        // 2. Peraturan (Dokumen Hukum) - Published only
        // Limit to 2000 recent items to avoid memory issues
        // Assuming 'status' = 1 is published, catch potential errors if status col doesn't exist
        try {
            $peraturan = $peraturanModel
                ->select('slug, updated_at, created_at')
                ->where('slug !=', '')
                ->where('is_published', 1)
                ->orderBy('created_at', 'DESC')
                ->limit(2000)
                ->find();

            foreach ($peraturan as $p) {
                // Gunakan updated_at atau created_at sebagai lastmod
                $date = $p['updated_at'] ?? $p['created_at'];
                if (empty($date)) $date = date('Y-m-d H:i:s');
                
                $urls[] = [
                    'loc' => base_url('peraturan/' . $p['slug']),
                    'lastmod' => date('c', strtotime($date)),
                    'priority' => '0.8',
                    'changefreq' => 'monthly'
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'Sitemap generation error (Peraturan): ' . $e->getMessage());
        }

        // 3. Berita - Published only
        try {
            $berita = $beritaModel
                ->select('slug, tanggal_publish, updated_at')
                ->where('status', 'published')
                ->orderBy('tanggal_publish', 'DESC')
                ->limit(500)
                ->find();

            foreach ($berita as $b) {
                $date = $b['updated_at'] ?? $b['tanggal_publish'];
                
                $urls[] = [
                    'loc' => base_url('berita/' . $b['slug']),
                    'lastmod' => date('c', strtotime($date)),
                    'priority' => '0.6',
                    'changefreq' => 'weekly'
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'Sitemap generation error (Berita): ' . $e->getMessage());
        }

        // Generate XML
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        foreach ($urls as $url) {
            $loc = is_array($url) ? $url['loc'] : $url;
            $lastmod = is_array($url) ? $url['lastmod'] : date('c');
            $priority = is_array($url) ? $url['priority'] : '0.5';
            $changefreq = is_array($url) ? $url['changefreq'] : 'monthly';

            $xml .= "\t<url>\n";
            $xml .= "\t\t<loc>" . htmlspecialchars($loc) . "</loc>\n";
            $xml .= "\t\t<lastmod>" . $lastmod . "</lastmod>\n";
            $xml .= "\t\t<changefreq>" . $changefreq . "</changefreq>\n";
            $xml .= "\t\t<priority>" . $priority . "</priority>\n";
            $xml .= "\t</url>\n";
        }

        $xml .= "</urlset>";

        return $this->response
            ->setContentType('application/xml')
            ->setBody($xml);
    }
}
