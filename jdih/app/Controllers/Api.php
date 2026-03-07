<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\WebTagModel;

class Api extends Controller
{
    protected $tagModel;

    public function __construct()
    {
        // Initialize without authentication requirements
        $this->tagModel = new WebTagModel();
    }

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /**
     * Public endpoint untuk pencarian tag (tidak memerlukan autentikasi)
     * 
     * @return JSON
     */
    public function searchTags()
    {
        // Set CORS headers
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type');

        // Ambil parameter dari request
        $keyword = $this->request->getGet('q');
        $page = $this->request->getGet('page') ?? 1;
        $limit = 50; // Increase limit for better UX
        $offset = ($page - 1) * $limit;

        // Debug log
        log_message('debug', 'API searchTags called with keyword: ' . $keyword);

        // Jika tidak ada keyword, ambil semua tag (untuk initial load)
        if (empty($keyword)) {
            $tags = $this->tagModel->getAllTags();
            $total = count($tags);

            // Apply pagination manually for all tags
            $tags = array_slice($tags, $offset, $limit);
        } else {
            // Ambil data tag berdasarkan keyword dari model
            $tags = $this->tagModel->getTagsByKeyword($keyword, $limit, $offset);
            $total = $this->tagModel->countTagsByKeyword($keyword);
        }

        // Debug log
        log_message('debug', 'API searchTags found ' . count($tags) . ' tags');

        // Format data untuk Select2
        $results = [];
        foreach ($tags as $tag) {
            $results[] = [
                'id' => $tag['id_tag'],
                'text' => $tag['nama_tag']
            ];
        }

        // Kembalikan response dalam format yang diharapkan oleh Select2
        return $this->response->setJSON([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $limit) < $total
            ]
        ]);
    }

    /**
     * Public endpoint untuk mendapatkan semua tag
     * 
     * @return JSON
     */
    public function getAllTags()
    {
        $tags = $this->tagModel->getAllTags();

        $results = [];
        foreach ($tags as $tag) {
            $results[] = [
                'id' => $tag['id_tag'],
                'text' => $tag['nama_tag']
            ];
        }

        return $this->response->setJSON(['results' => $results]);
    }

    /**
     * Endpoint untuk membuat tag baru
     * 
     * @return JSON
     */
    public function createTag()
    {
        // Set CORS headers
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type');

        // Validasi request method
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Method tidak diizinkan'
            ])->setStatusCode(405);
        }

        // Validasi CSRF
        $csrfName = csrf_token();
        $csrfValue = csrf_hash();
        $postedCsrfValue = $this->request->getPost($csrfName);

        if ($postedCsrfValue !== $csrfValue) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid security token'
            ])->setStatusCode(403);
        }

        // Ambil nama tag dari POST
        $nama_tag = trim($this->request->getPost('nama_tag'));

        // Validasi nama tag
        if (empty($nama_tag)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Nama tag tidak boleh kosong'
            ])->setStatusCode(400);
        }

        // Cek apakah tag sudah ada
        $existing_tag = $this->tagModel->where('nama_tag', $nama_tag)->first();
        if ($existing_tag) {
            return $this->response->setJSON([
                'status' => 'success',
                'id_tag' => $existing_tag['id_tag'],
                'message' => 'Tag sudah ada'
            ]);
        }

        // Buat slug dari nama tag
        $slug = $this->tagModel->createSlug($nama_tag);

        // Simpan tag baru
        $data = [
            'nama_tag' => $nama_tag,
            'slug_tag' => $slug
        ];

        try {
            $this->tagModel->save($data);
            $id_tag = $this->tagModel->getInsertID();

            return $this->response->setJSON([
                'status' => 'success',
                'id_tag' => $id_tag,
                'message' => 'Tag berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            log_message('error', '[ERROR] Gagal menambahkan tag: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menyimpan tag: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
