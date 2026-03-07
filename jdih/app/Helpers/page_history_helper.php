<?php

if (!function_exists('add_page_to_history')) {
    /**
     * Menambahkan halaman ke histori navigasi user
     * 
     * @param string $title Judul halaman
     * @param string $url URL halaman
     * @param string $type Tipe halaman (home, kategori, jenis, detail)
     * @param array $metadata Data tambahan (opsional)
     * @return void
     */
    function add_page_to_history($title, $url, $type = 'page', $metadata = [])
    {
        $session = session();
        $history = $session->get('page_history') ?? [];

        // Format data histori
        $page_data = [
            'title' => $title,
            'url' => $url,
            'type' => $type,
            'metadata' => $metadata,
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s')
        ];

        // Cek apakah halaman ini sudah ada di histori (untuk menghindari duplikasi berturut-turut)
        $last_page = end($history);
        if (!$last_page || $last_page['url'] !== $url) {
            $history[] = $page_data;
        }

        // Batasi histori maksimal 20 halaman
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }

        $session->set('page_history', $history);
    }
}

if (!function_exists('get_page_history')) {
    /**
     * Mengambil histori halaman user
     * 
     * @param int $limit Batas jumlah histori yang diambil
     * @return array
     */
    function get_page_history($limit = 10)
    {
        $session = session();
        $history = $session->get('page_history') ?? [];

        if ($limit && count($history) > $limit) {
            return array_slice($history, -$limit);
        }

        return $history;
    }
}

if (!function_exists('get_breadcrumb_from_history')) {
    /**
     * Membuat breadcrumb berdasarkan histori navigasi
     * 
     * @param int $max_items Maksimal item breadcrumb
     * @return array
     */
    function get_breadcrumb_from_history($max_items = 5)
    {
        $history = get_page_history($max_items);
        $breadcrumb = [];

        foreach ($history as $page) {
            $breadcrumb[] = [
                'title' => $page['title'],
                'url' => $page['url'],
                'active' => false
            ];
        }

        // Set halaman terakhir sebagai active
        if (!empty($breadcrumb)) {
            $breadcrumb[count($breadcrumb) - 1]['active'] = true;
            $breadcrumb[count($breadcrumb) - 1]['url'] = null; // Active item tidak perlu URL
        }

        return $breadcrumb;
    }
}

if (!function_exists('clear_page_history')) {
    /**
     * Menghapus histori halaman
     * 
     * @return void
     */
    function clear_page_history()
    {
        $session = session();
        $session->remove('page_history');
    }
}

if (!function_exists('get_previous_page')) {
    /**
     * Mengambil halaman sebelumnya dari histori
     * 
     * @return array|null
     */
    function get_previous_page()
    {
        $history = get_page_history();

        if (count($history) >= 2) {
            return $history[count($history) - 2]; // Halaman kedua dari terakhir
        }

        return null;
    }
}
