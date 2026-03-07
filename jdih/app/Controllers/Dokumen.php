<?php

namespace App\Controllers;

use App\Controllers\PublicBaseController;

class Dokumen extends PublicBaseController
{
    /**
     * An instance of the IncomingRequest object.
     *
     * @var \CodeIgniter\HTTP\IncomingRequest
     */
    protected $request;

    protected $webPeraturanModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->webPeraturanModel = new \App\Models\WebPeraturanModel();
    }

    public function kategori($kategoriSlug = null)
    {
        if (!$kategoriSlug) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori tidak boleh kosong.');
        }

        helper(['page_history']);

        $jenisPeraturanModel = new \App\Models\WebJenisPeraturanModel();
        $data = [];

        if ($kategoriSlug === 'produk-hukum') {
            // Halaman perantara: Tampilkan 2 sub-kategori Produk Hukum
            $subKategoriListRaw = $jenisPeraturanModel
                ->select('kategori_nama, kategori_slug')->distinct()
                ->whereIn('kategori_slug', ['produk-hukum-peraturan', 'produk-hukum-non-peraturan'])
                ->orderBy('kategori_nama', 'DESC') // Peraturan dulu, baru Non-Peraturan
                ->findAll();

            $data['title'] = 'Kategori: Produk Hukum';
            $data['kategori_nama'] = 'Produk Hukum';
            $data['sub_kategori_list'] = $subKategoriListRaw;
            $data['jenis_list'] = [];

            // Tambahkan ke histori
            add_page_to_history(
                'Kategori: Produk Hukum',
                current_url(),
                'kategori',
                ['kategori_slug' => $kategoriSlug, 'sub_kategori_count' => count($subKategoriListRaw)]
            );
        } else {
            // Halaman akhir: Tampilkan daftar jenis dokumen dalam satu kategori
            $jenisListRaw = $jenisPeraturanModel->where('kategori_slug', $kategoriSlug)->orderBy('urutan', 'ASC')->findAll();

            if (empty($jenisListRaw)) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori Dokumen tidak ditemukan.');
            }

            $kategori_nama = $jenisListRaw[0]['kategori_nama'];
            $data['title'] = 'Kategori: ' . $kategori_nama;
            $data['kategori_nama'] = $kategori_nama;
            $data['jenis_list'] = $jenisListRaw;
            $data['sub_kategori_list'] = [];

            // Tambahkan ke histori
            add_page_to_history(
                'Kategori: ' . $kategori_nama,
                current_url(),
                'kategori',
                ['kategori_slug' => $kategoriSlug, 'jenis_count' => count($jenisListRaw)]
            );
        }

        return $this->renderView('dokumen-kategori', $data);
    }

    public function jenis($jenisSlug = null)
    {
        if (!$jenisSlug) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Jenis dokumen tidak valid.');
        }

        helper(['page_history']);

        // Ambil detail jenis dokumen dari database menggunakan slug
        $jenisPeraturanModel = new \App\Models\WebJenisPeraturanModel();
        $jenisDetail = $jenisPeraturanModel->where('slug_jenis', $jenisSlug)->first();

        if (!$jenisDetail) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Jenis dokumen tidak ditemukan.');
        }

        $jenisNama = $jenisDetail['nama_jenis'];
        $id_jenis_peraturan = $jenisDetail['id_jenis_peraturan']; // Ambil ID jenis dokumen

        // 3. Siapkan filter dan pagination
        $perPage = $this->request->getGet('per_page') ?: 10;
        $page = $this->request->getGet('page') ?: 1;
        $offset = ($page - 1) * $perPage;

        $filters = [
            'jenis' => $jenisSlug, // Gunakan slug, bukan ID, agar sesuai dengan model
            'tahun' => $this->request->getGet('tahun') ?: '',
            'status' => $this->request->getGet('status') ?: '',
            'keyword' => $this->request->getGet('keyword') ?: '',
            'sort' => $this->request->getGet('sort') ?: 'terbaru',
            'limit' => $perPage,
            'page' => $page
        ];

        // Ambil data peraturan
        $peraturanData = $this->webPeraturanModel->searchPeraturan($filters, $perPage);

        $data = [
            'title'             => 'Dokumen Hukum: ' . $jenisNama,
            'peraturan'         => $peraturanData,
            'pager'             => $this->webPeraturanModel->pager,
            'filters'           => $filters,
            'jenis_peraturan'   => $this->getJenisPeraturan(),
            'tahun_peraturan'   => $this->getTahunPeraturan(),
            'kategori_nama'     => $jenisNama,
            'total'             => $this->webPeraturanModel->pager->getTotal()
        ];

        // Tambahkan ke histori
        add_page_to_history(
            'Jenis: ' . $jenisNama,
            current_url(),
            'jenis',
            [
                'jenis_slug' => $jenisSlug,
                'kategori_slug' => $jenisDetail['kategori_slug'] ?? null,
                'total_dokumen' => $data['total']
            ]
        );

        return $this->renderView('peraturan', $data);
    }

    public function tag($id_tag = null)
    {
        if (!$id_tag || !is_numeric($id_tag)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tag tidak valid.');
        }

        $webTagModel = new \App\Models\WebTagModel();
        $webPeraturanTagModel = new \App\Models\WebPeraturanTagModel();

        $tag = $webTagModel->find($id_tag);
        if (!$tag) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tag tidak ditemukan.');
        }

        // Ambil semua ID peraturan yang memiliki tag ini
        $peraturanIdsData = $webPeraturanTagModel->where('id_tag', $id_tag)->findAll();
        $peraturanIds = array_column($peraturanIdsData, 'id_peraturan');

        // Jika tidak ada peraturan dengan tag ini, tetap tampilkan halaman dengan hasil kosong
        if (empty($peraturanIds)) {
            $peraturanIds = [0]; // ID yang tidak akan pernah ada, untuk memastikan query tidak error
        }

        $perPage = $this->request->getGet('per_page') ?: 10;

        $query = $this->webPeraturanModel
            ->select('web_peraturan.*, web_jenis_peraturan.nama_jenis, status_dokumen.nama_status as status_nama')
            ->join('web_jenis_peraturan', 'web_jenis_peraturan.id_jenis_peraturan = web_peraturan.id_jenis_dokumen', 'left')
            ->join('status_dokumen', 'status_dokumen.id = web_peraturan.id_status', 'left')
            ->whereIn('web_peraturan.id_peraturan', $peraturanIds)
            ->where('web_peraturan.is_published', 1);

        $data = [
            'title' => 'Dokumen Hukum dengan Tag: ' . $tag['nama_tag'],
            'peraturan' => $query->paginate($perPage, 'default'),
            'pager' => $this->webPeraturanModel->pager,
            'jenis_peraturan' => $this->getJenisPeraturan(),
            'list_tahun' => $this->webPeraturanModel->getPeraturanCountByTahun(),
            'kategori_nama' => 'Tag: ' . $tag['nama_tag'],
            'total' => $this->webPeraturanModel->countAllResults(false) // false to not reset query
        ];

        return $this->renderView('peraturan', $data);
    }

    private function getJenisPeraturan()
    {
        $jenisPeraturanModel = new \App\Models\WebJenisPeraturanModel();
        return $jenisPeraturanModel->select('nama_jenis, slug_jenis')
            ->orderBy('nama_jenis', 'ASC')
            ->findAll();
    }

    private function getTahunPeraturan()
    {
        $tahunData = $this->webPeraturanModel->select('tahun')
            ->distinct()
            ->orderBy('tahun', 'DESC')
            ->findAll();

        $tahun = [];
        foreach ($tahunData as $item) {
            $tahun[] = $item['tahun'];
        }

        return $tahun;
    }
}
