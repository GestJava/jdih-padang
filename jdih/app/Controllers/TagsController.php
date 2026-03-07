<?php

namespace App\Controllers;

use App\Models\WebTagModel;
use CodeIgniter\HTTP\ResponseInterface;

class TagsController extends BaseController
{
    protected $tagModel;

    public function __construct()
    {
        $this->tagModel = new WebTagModel();
    }

    /**
     * Test endpoint untuk pencarian tags
     */
    public function search()
    {
        // Set CORS headers
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type');

        // Log request
        log_message('debug', 'TagsController::search called');

        // Ambil parameter dari request
        $keyword = $this->request->getGet('q');
        $page = $this->request->getGet('page') ?? 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        log_message('debug', 'Search params - keyword: ' . $keyword . ', page: ' . $page);

        try {
            // Jika tidak ada keyword, ambil semua tag (untuk initial load)
            if (empty($keyword)) {
                $tags = $this->tagModel->getAllTags();
                $total = count($tags);
                $tags = array_slice($tags, $offset, $limit);
            } else {
                // Ambil data tag berdasarkan keyword dari model
                $tags = $this->tagModel->getTagsByKeyword($keyword, $limit, $offset);
                $total = $this->tagModel->countTagsByKeyword($keyword);
            }

            log_message('debug', 'Found ' . count($tags) . ' tags');

            // Format data untuk Select2
            $results = [];
            foreach ($tags as $tag) {
                $results[] = [
                    'id' => $tag['id_tag'],
                    'text' => $tag['nama_tag']
                ];
            }

            $response = [
                'results' => $results,
                'pagination' => [
                    'more' => ($page * $limit) < $total
                ]
            ];

            log_message('debug', 'Response: ' . json_encode($response));

            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', 'TagsController::search error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => true,
                'message' => 'Internal server error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Test endpoint untuk membuat tag baru
     */
    public function create()
    {
        // Set CORS headers
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type');

        log_message('debug', 'TagsController::create called');

        // Validasi request method
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Method tidak diizinkan'
            ])->setStatusCode(405);
        }

        // Ambil nama tag dari POST
        $nama_tag = trim($this->request->getPost('nama_tag'));

        log_message('debug', 'Creating tag: ' . $nama_tag);

        // Validasi nama tag
        if (empty($nama_tag)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Nama tag tidak boleh kosong'
            ])->setStatusCode(400);
        }

        try {
            // Cek apakah tag sudah ada
            $existing_tag = $this->tagModel->where('nama_tag', $nama_tag)->first();
            if ($existing_tag) {
                log_message('debug', 'Tag already exists: ' . $existing_tag['id_tag']);
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

            $this->tagModel->save($data);
            $id_tag = $this->tagModel->getInsertID();

            log_message('debug', 'Tag created successfully with ID: ' . $id_tag);

            return $this->response->setJSON([
                'status' => 'success',
                'id_tag' => $id_tag,
                'message' => 'Tag berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'TagsController::create error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menyimpan tag: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Test endpoint untuk mendapatkan semua tags
     */
    public function all()
    {
        // Set CORS headers
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type');

        log_message('debug', 'TagsController::all called');

        try {
            $tags = $this->tagModel->getAllTags();

            $results = [];
            foreach ($tags as $tag) {
                $results[] = [
                    'id' => $tag['id_tag'],
                    'text' => $tag['nama_tag']
                ];
            }

            log_message('debug', 'Returning ' . count($results) . ' tags');

            return $this->response->setJSON(['results' => $results]);
        } catch (\Exception $e) {
            log_message('error', 'TagsController::all error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => true,
                'message' => 'Internal server error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
