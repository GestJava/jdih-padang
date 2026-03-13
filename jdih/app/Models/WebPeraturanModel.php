<?php

namespace App\Models;

use CodeIgniter\Model;

class WebPeraturanModel extends Model
{
    protected $table      = 'web_peraturan';
    protected $primaryKey = 'id_peraturan';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id_jenis_dokumen',
        'nomor',
        'tahun',
        'judul',
        'slug',
        'tgl_penetapan',
        'tgl_pengundangan',
        'tempat_penetapan',
        'penandatangan',
        'id_instansi',
        'sumber',
        'id_status',
        'abstrak_teks',
        'catatan_teks',
        'file_dokumen',
        'hits',
        'downloads',
        'is_published',
        'created_at',
        'updated_at',
        'teu',
        'bidang_hukum'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    private $filePath;

    public function __construct()
    {
        parent::__construct();
        $path = FCPATH . 'uploads/peraturan/';
        $this->filePath = str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Mengambil semua jenis peraturan yang aktif.
     *
     * @return array
     */
    public function getJenisPeraturan()
    {
        return $this->db->table('web_jenis_peraturan')
            ->select('id_jenis_peraturan AS id_jenis_dokumen, nama_jenis, slug_jenis')
            ->where('is_active', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Increment the download count for a specific regulation.
     *
     * @param int $id The ID of the regulation.
     * @return bool
     */
    public function incrementDownloads($id)
    {
        return $this->where($this->primaryKey, $id)
            ->set('downloads', 'downloads+1', false)
            ->update();
    }

    public function generateUniqueSlug($judul, $id_peraturan = null)
    {
        $slug = url_title(strtolower($judul), '-', true);
        $slugBase = $slug;
        $i = 1;
        while (
            $this->where('slug', $slug)
            ->where('id_peraturan !=', $id_peraturan)
            ->countAllResults() > 0
        ) {
            $slug = $slugBase . '-' . $i++;
        }
        return $slug;
    }

    public function saveData($peraturan_data, $file)
    {
        helper('url'); // Load URL helper for slug generation

        $return_data = ['id_peraturan' => null, 'error' => ['message' => null]];

        // Handle file upload
        $newFileName = null;
        $uploadPath = $this->filePath; // Gunakan filePath yang sudah diinisialisasi di constructor

        // Verifikasi direktori upload
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0777, true)) {
                $return_data['error']['message'] = 'Gagal membuat direktori upload: ' . $uploadPath;
                log_message('error', '[ERROR] Failed to create upload directory: ' . $uploadPath);
                return $return_data;
            }
        }

        // Penanganan file upload yang lebih baik
        if (!empty($peraturan_data['id_peraturan'])) {
            // Edit peraturan yang sudah ada - file bisa opsional
            if ($file !== null && $file->getError() === 0) {
                // Ada file baru yang diupload
                if ($file->isValid() && !$file->hasMoved()) {
                    // Hapus file lama terlebih dahulu
                    $oldData = $this->find($peraturan_data['id_peraturan']);
                    if ($oldData && !empty($oldData['file_dokumen'])) {
                        $oldFilePath = $uploadPath . $oldData['file_dokumen'];
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                            log_message('info', 'Old file deleted: ' . $oldFilePath);
                        }
                    }

                    $newFileName = $file->getRandomName();

                    if ($file->move($uploadPath, $newFileName)) {
                        $peraturan_data['file_dokumen'] = $newFileName;
                        log_message('info', 'New file uploaded: ' . $newFileName);
                    } else {
                        $errorMsg = 'Gagal memindahkan file yang diunggah: ' . $file->getErrorString();
                        $return_data['error']['message'] = $errorMsg;
                        log_message('error', '[ERROR] ' . $errorMsg);
                        return $return_data;
                    }
                } else {
                    $errorMsg = 'File tidak valid: ' . $file->getErrorString();
                    $return_data['error']['message'] = $errorMsg;
                    log_message('error', '[ERROR] ' . $errorMsg);
                    return $return_data;
                }
            }
            // Jika tidak ada file baru, gunakan file yang sudah ada
        } else {
            // Peraturan baru - file wajib
            if ($file === null || $file->getError() !== 0) {
                $errorMsg = 'File dokumen wajib diunggah untuk peraturan baru.';
                if ($file !== null) {
                    $errorMsg .= ' Error: ' . $file->getErrorString();
                }
                $return_data['error']['message'] = $errorMsg;
                log_message('error', '[ERROR] ' . $errorMsg);
                return $return_data;
            }

            if ($file->isValid() && !$file->hasMoved()) {
                $newFileName = $file->getRandomName();

                if ($file->move($uploadPath, $newFileName)) {
                    $peraturan_data['file_dokumen'] = $newFileName;
                } else {
                    $errorMsg = 'Gagal memindahkan file yang diunggah: ' . $file->getErrorString();
                    $return_data['error']['message'] = $errorMsg;
                    log_message('error', '[ERROR] ' . $errorMsg);
                    return $return_data;
                }
            } else {
                $errorMsg = 'File tidak valid: ' . $file->getErrorString();
                $return_data['error']['message'] = $errorMsg;
                log_message('error', '[ERROR] ' . $errorMsg);
                return $return_data;
            }
        }

        // Generate slug unik dari judul
        if (!empty($peraturan_data['judul'])) {
            $id_peraturan = $peraturan_data['id_peraturan'] ?? null;
            $peraturan_data['slug'] = $this->generateUniqueSlug($peraturan_data['judul'], $id_peraturan);
        }

        // Save data using CodeIgniter Model's save method
        try {
            $insertedID = $this->save($peraturan_data);
            if ($insertedID === false) {
                $errors = $this->errors();
                $errorMsg = $errors ? implode(', ', $errors) : 'Gagal menyimpan data peraturan ke database.';
                $return_data['error']['message'] = $errorMsg;
                log_message('error', '[ERROR] Database save error: ' . $errorMsg);
            } else {
                $return_data['id_peraturan'] = $this->getInsertID() ?: $peraturan_data[$this->primaryKey] ?? $insertedID; // Pastikan ID diambil dengan benar
                if (empty($return_data['id_peraturan'])) {
                    $errorMsg = 'Gagal mendapatkan ID peraturan setelah penyimpanan.';
                    $return_data['error']['message'] = $errorMsg;
                    log_message('error', '[ERROR] ' . $errorMsg);
                }
            }
        } catch (\Exception $e) {
            log_message('error', '[ERROR] Exception in WebPeraturanModel::saveData: ' . $e->getMessage());
            $return_data['error']['message'] = 'Terjadi kesalahan sistem saat menyimpan data: ' . $e->getMessage();
        }

        return $return_data;
    }

    /**
     * Mendapatkan detail peraturan berdasarkan ID
     * 
     * @param int $id_peraturan
     * @return array|null
     */
    public function getPeraturanDetail($id_peraturan)
    {
        // 1. Get the main regulation data first using a basic query to ensure it's found.
        $peraturan = $this->db->table($this->table)
            ->select('*') // Ensure all fields including file_dokumen are selected
            ->where($this->primaryKey, $id_peraturan)
            ->get()
            ->getRowArray();

        if (!$peraturan) {
            return null; // If the main record doesn't exist, exit.
        }

        // 2. Get related data separately. This is more robust against broken foreign keys in migrated data.
        if (!empty($peraturan['id_jenis_dokumen'])) {
            $jenis = $this->db->table('web_jenis_peraturan')->where('id_jenis_peraturan', $peraturan['id_jenis_dokumen'])->get()->getRowArray();
            $peraturan['nama_jenis'] = $jenis['nama_jenis'] ?? null;
            $peraturan['kategori_nama'] = $jenis['kategori_nama'] ?? null;
        } else {
            $peraturan['nama_jenis'] = null;
            $peraturan['kategori_nama'] = null;
        }

        if (!empty($peraturan['id_status'])) {
            $status = $this->db->table('status_dokumen')->where('id', $peraturan['id_status'])->get()->getRowArray();
            $peraturan['status'] = $status['nama_status'] ?? null; // Keep the alias 'status'
        } else {
            $peraturan['status'] = null;
        }

        if (!empty($peraturan['id_instansi'])) {
            $instansi = $this->db->table('instansi')->where('id', $peraturan['id_instansi'])->get()->getRowArray();
            $peraturan['nama_instansi'] = $instansi['nama_instansi'] ?? null;
        } else {
            $peraturan['nama_instansi'] = null;
        }

        // 3. Format dates
        if (!empty($peraturan['tgl_penetapan'])) {
            $peraturan['tanggal_penetapan_formatted'] = date('d F Y', strtotime($peraturan['tgl_penetapan']));
        }
        if (!empty($peraturan['tgl_pengundangan'])) {
            $peraturan['tanggal_pengundangan_formatted'] = date('d F Y', strtotime($peraturan['tgl_pengundangan']));
        }

        return $peraturan;
    }

    /**
     * Mendapatkan peraturan terkait (status peraturan)
     * 
     * @param int $id_peraturan
     * @return array
     */
    public function getRelasiPeraturan($id_peraturan)
    {
        $db = $this->db;
        $relasi_sumber = $db->table('web_peraturan_relasi r')
            ->select('r.jenis_relasi, p.id_peraturan, j.nama_jenis as jenis_peraturan, p.nomor, p.tahun, p.judul, p.slug')
            ->join('web_peraturan p', 'r.id_peraturan_terkait = p.id_peraturan')
            ->join('web_jenis_peraturan j', 'p.id_jenis_dokumen = j.id_jenis_peraturan', 'left')
            ->where('r.id_peraturan_sumber', $id_peraturan)
            ->get()->getResultArray();

        $relasi_target = $db->table('web_peraturan_relasi r')
            ->select('r.jenis_relasi, p.id_peraturan, j.nama_jenis as jenis_peraturan, p.nomor, p.tahun, p.judul, p.slug')
            ->join('web_peraturan p', 'r.id_peraturan_sumber = p.id_peraturan')
            ->join('web_jenis_peraturan j', 'p.id_jenis_dokumen = j.id_jenis_peraturan', 'left')
            ->where('r.id_peraturan_terkait', $id_peraturan)
            ->get()->getResultArray();

        foreach ($relasi_target as &$item) {
            switch (strtolower($item['jenis_relasi'])) {
                case 'mengubah':
                    $item['jenis_relasi'] = 'Diubah oleh';
                    break;
                case 'mencabut':
                    $item['jenis_relasi'] = 'Dicabut oleh';
                    break;
            }
        }

        return array_merge($relasi_sumber, $relasi_target);
    }

    /**
     * Menambah jumlah hits/views peraturan
     *
     * @param int $id_peraturan
     * @return bool
     */
    public function incrementHits($id_peraturan)
    {
        return $this->set('hits', 'hits+1', false)->where('id_peraturan', $id_peraturan)->update();
    }

    /**
     * Mendapatkan peraturan terbaru
     *
     * @param int $limit
     * @return array
     */
    public function getLatestPeraturan($limit = 8)
    {
        return $this->select('web_peraturan.id_peraturan, web_peraturan.judul, web_peraturan.nomor, web_peraturan.tahun, web_peraturan.slug, web_peraturan.tgl_penetapan, web_peraturan.file_dokumen, web_jenis_peraturan.nama_jenis')
            ->join('web_jenis_peraturan', 'web_jenis_peraturan.id_jenis_peraturan = web_peraturan.id_jenis_dokumen', 'left')
            ->where('web_peraturan.is_published', 1)
            ->orderBy('web_peraturan.tgl_penetapan', 'DESC')
            ->orderBy('web_peraturan.id_peraturan', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Mendapatkan peraturan terpopuler berdasarkan hits
     *
     * @param int $limit
     * @return array
     */
    public function getPopularPeraturan($limit = 5)
    {
        $cacheKey = 'popular_peraturan_' . $limit;
        if ($cachedData = cache($cacheKey)) {
            return $cachedData;
        }

        $result = $this->select('web_peraturan.id_peraturan, web_peraturan.judul, web_peraturan.hits, web_peraturan.slug AS slug_peraturan')
            ->where('web_peraturan.is_published', 1)
            ->orderBy('web_peraturan.hits', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        // Cache selama 30 menit
        cache()->save($cacheKey, $result, 1800);

        return $result;
    }

    // --- Datatables Methods for Admin ---

    private function _get_datatables_query($request)
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->select('p.id_peraturan, p.nomor, p.tahun, p.judul, p.file_dokumen, j.nama_jenis, i.nama_instansi, s.nama_status');
        $builder->join('web_jenis_peraturan j', 'j.id_jenis_peraturan = p.id_jenis_dokumen', 'left');
        $builder->join('instansi i', 'i.id = p.id_instansi', 'left');
        $builder->join('status_dokumen s', 's.id = p.id_status', 'left');

        $searchValue = $request->getPost('search')['value'] ?? '';
        if ($searchValue) {
            $builder->groupStart();
            $builder->like('p.judul', $searchValue);
            $builder->orLike('p.nomor', $searchValue);
            $builder->orLike('p.tahun', $searchValue);
            $builder->orLike('j.nama_jenis', $searchValue);
            $builder->orLike('i.nama_instansi', $searchValue);
            $builder->orLike('s.nama_status', $searchValue);
            $builder->groupEnd();
        }

        // Handle sorting - Fixed column mapping for all 9 columns
        $order = $request->getPost('order');
        if ($order && isset($order[0])) {
            // Complete column mapping for all 9 columns
            $columnMap = [
                0 => 'p.id_peraturan', // No (auto-generated)
                1 => 'j.nama_jenis',   // Jenis Peraturan
                2 => 'p.nomor',        // Nomor
                3 => 'p.tahun',        // Tahun
                4 => 'p.judul',        // Judul
                5 => 'i.nama_instansi', // Pemrakarsa
                6 => 's.nama_status',   // Status
                7 => 'p.file_dokumen',  // File (not sortable)
                8 => 'p.id_peraturan'   // Aksi (not sortable)
            ];

            $columnIndex = (int)$order[0]['column'];
            $columnName = $columnMap[$columnIndex] ?? 'p.id_peraturan';
            $dir = strtoupper($order[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';

            // Only apply ordering if column is sortable
            if ($columnIndex !== 0 && $columnIndex !== 8) {
                if ($columnIndex === 7) {
                    // Mengurutkan berdasarkan keberadaan file di luar query Pagination PHP
                    $builder->orderBy("(p.file_dokumen IS NOT NULL AND p.file_dokumen != '')", $dir, false);
                } else {
                    $builder->orderBy($columnName, $dir);
                }
            }
        } else {
            // Default order
            $builder->orderBy('p.tahun', 'DESC');
            $builder->orderBy('p.id_peraturan', 'DESC');
        }

        return $builder;
    }

    public function getDatatables($request)
    {
        $builder = $this->_get_datatables_query($request);

        // Get paging parameters
        $length = (int)($request->getPost('length') ?? 10);
        $start = (int)($request->getPost('start') ?? 0);

        // Apply paging
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $query = $builder->get();
        return $query->getResult();
    }

    public function countFiltered($request)
    {
        $builder = $this->_get_datatables_query($request);
        return $builder->countAllResults();
    }

    public function countAll()
    {
        // Optimasi: Cache total count untuk mengurangi query
        $cacheKey = 'web_peraturan_total_count';
        $totalCount = cache($cacheKey);

        if ($totalCount === null) {
            $builder = $this->db->table($this->table);
            $totalCount = $builder->countAllResults();

            // Cache selama 5 menit
            cache()->save($cacheKey, $totalCount, 300);
        }

        return $totalCount;
    }

    /**
     * Mencari peraturan berdasarkan filter untuk halaman web.
     *
     * @param array $filters Filter (keyword, jenis, tahun, status, sort, page)
     * @param int   $perPage Jumlah item per halaman
     * @return array Hasil paginasi dari query builder
     */
    public function searchPeraturan($filters = [], $perPage = 10)
    {
        // Reset query builder untuk memastikan tidak ada kontaminasi dari query sebelumnya
        // PENTING: resetQuery() harus dipanggil SEBELUM membangun query baru
        $this->builder()->resetQuery();
        
        // Validasi dan sanitasi input
        $perPage = (int)$perPage;
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 10; // Default value jika invalid
        }

        // Query dasar - DISTINCT akan ditambahkan jika ada filter tag
        // Hapus 'id_peraturan' dari select karena sudah termasuk dalam 'web_peraturan.*'
        $this->select('web_peraturan.*, j.nama_jenis, j.slug_jenis, s.nama_status')
            ->join('web_jenis_peraturan j', 'j.id_jenis_peraturan = web_peraturan.id_jenis_dokumen', 'left')
            ->join('status_dokumen s', 's.id = web_peraturan.id_status', 'left')
            ->where('web_peraturan.is_published', 1);
        
        // Catatan: DISTINCT akan ditambahkan di bagian tag filter jika diperlukan

        // KEAMANAN: Sanitasi dan validasi keyword untuk mencegah XSS dan SQL Injection
        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);

            // Mapping keyword (alias) untuk JDIH (e.g. Perwal -> Peraturan Walikota)
            $keywordLower = strtolower($keyword);
            $aliasMap = [
                'perwal' => 'Peraturan Walikota',
                'perwako' => 'Peraturan Walikota',
                'perda' => 'Peraturan Daerah',
                'kepwal' => 'Keputusan Walikota',
                'instruksi walikota' => 'Inwal'
            ];
            
            $searchKeywords = [$keyword];
            if (isset($aliasMap[$keywordLower])) {
                $searchKeywords[] = $aliasMap[$keywordLower];
            }

            // Relaxed sanitization: allow alphanumeric, space, dash, dot, slash, and parentheses
            // common in document numbers (e.g. 902/2025)
            $sanitizedKeywords = [];
            foreach ($searchKeywords as $kw) {
                $kw = preg_replace('/[^a-zA-Z0-9\s\-\.\/\(\)]/', '', $kw);
                $kw = substr($kw, 0, 100);
                if ($kw !== '' && strlen($kw) >= 2) {
                    $sanitizedKeywords[] = $kw;
                }
            }

            if (!empty($sanitizedKeywords)) {
                $this->groupStart();
                foreach ($sanitizedKeywords as $kw) {
                    $this->orGroupStart();
                    $this->like('web_peraturan.judul', $kw);
                    $this->orLike('web_peraturan.nomor', $kw);
                    $this->orLike('web_peraturan.tahun', $kw);
                    $this->orLike('web_peraturan.abstrak_teks', $kw);
                    $this->orLike('j.nama_jenis', $kw); // Include document type name
                    $this->groupEnd();
                }
                $this->groupEnd();
            }
        }

        // Validasi jenis
        if (!empty($filters['jenis']) && $filters['jenis'] !== 'semua') {
            $jenis = trim($filters['jenis']);
            if (preg_match('/^[a-zA-Z0-9\-_]+$/', $jenis)) {
                $this->where('j.slug_jenis', $jenis);
            }
        }

        // Validasi tahun
        if (!empty($filters['tahun']) && $filters['tahun'] !== 'semua') {
            $tahun = (int)$filters['tahun'];
            if ($tahun >= 1900 && $tahun <= date('Y') + 10) {
                $this->where('web_peraturan.tahun', $tahun);
            }
        }

        // Validasi status
        if (!empty($filters['status']) && $filters['status'] !== 'semua') {
            $status = (int)$filters['status'];
            if ($status > 0) {
                $this->where('web_peraturan.id_status', $status);
            }
        }

        // Validasi tag - bisa berupa ID tunggal atau array ID tag
        // Jika array, berarti mencari peraturan yang memiliki salah satu dari tag-tag tersebut
        if (!empty($filters['tag'])) {
            // Handle array ID tag (untuk pencarian berdasarkan kata/konten tag)
            if (is_array($filters['tag'])) {
                $tagIds = array_map('intval', $filters['tag']);
                $tagIds = array_filter($tagIds, function($id) { return $id > 0; }); // Hapus ID 0 atau negatif
                
                if (!empty($tagIds)) {
                    // Gunakan INNER JOIN dengan WHERE IN untuk mencari peraturan yang memiliki salah satu tag
                    // DISTINCT akan menghindari duplikasi jika satu peraturan punya multiple tags
                    $this->distinct()
                        ->join('web_peraturan_tag pt', 'pt.id_peraturan = web_peraturan.id_peraturan', 'inner')
                        ->whereIn('pt.id_tag', $tagIds);
                    
                    // Log untuk debugging
                    log_message('debug', 'Tag filter - Tag IDs: ' . json_encode($tagIds) . ', Using INNER JOIN with WHERE IN and DISTINCT');
                } else {
                    // Jika tidak ada tag valid, pastikan tidak ada hasil
                    $this->where('1', '0', false);
                    log_message('debug', 'Tag filter - No valid tag IDs found');
                }
            } 
            // Handle ID tag tunggal (untuk backward compatibility)
            elseif (is_numeric($filters['tag'])) {
                $tag_id = (int)$filters['tag'];
                if ($tag_id > 0) {
                    // Gunakan INNER JOIN langsung dengan web_peraturan_tag untuk filter peraturan yang memiliki tag
                    // DISTINCT akan menghindari duplikasi jika satu peraturan punya multiple tags
                    $this->distinct()
                        ->join('web_peraturan_tag pt', 'pt.id_peraturan = web_peraturan.id_peraturan', 'inner')
                        ->where('pt.id_tag', $tag_id);
                    
                    // Log untuk debugging
                    log_message('debug', 'Tag filter - Tag ID: ' . $tag_id . ', Using INNER JOIN with DISTINCT');
                }
            }
        }

        // Sorting logic dengan validasi
        $sort = $filters['sort'] ?? 'terbaru';
        $allowed_sorts = ['terbaru', 'terlama', 'populer', 'abjad'];

        if (!in_array($sort, $allowed_sorts)) {
            $sort = 'terbaru';
        }

        switch ($sort) {
            case 'populer':
                $this->orderBy('web_peraturan.hits', 'DESC');
                break;
            case 'terlama':
                $this->orderBy('web_peraturan.tgl_penetapan', 'ASC');
                $this->orderBy('web_peraturan.id_peraturan', 'ASC');
                break;
            case 'abjad':
                $this->orderBy('web_peraturan.judul', 'ASC');
                break;
            case 'terbaru':
            default:
                $this->orderBy('web_peraturan.tgl_penetapan', 'DESC');
                $this->orderBy('web_peraturan.id_peraturan', 'DESC');
                break;
        }

        // Return paginated results dengan error handling
        try {
            // Log filter untuk debugging (hanya di development)
            if (ENVIRONMENT !== 'production') {
                log_message('debug', 'Search filters: ' . json_encode($filters));
            }
            
            // Panggil paginate() untuk mendapatkan hasil dengan pagination
            // paginate() akan otomatis menangani countAllResults() secara internal
            $results = $this->paginate($perPage);
            
            // Log untuk debugging (hanya di development)
            if (ENVIRONMENT !== 'production' && isset($this->pager)) {
                log_message('debug', 'Pagination - Total: ' . $this->pager->getTotal() . ', Page: ' . $this->pager->getCurrentPage() . ', Results count: ' . count($results));
            }
            
            return $results;
        } catch (\Exception $e) {
            // Log error dan return empty result
            log_message('error', 'Search peraturan error: ' . $e->getMessage());
            log_message('error', 'Search peraturan filters: ' . json_encode($filters));
            return [];
        }
    }

    /**
     * Mendapatkan jumlah peraturan berdasarkan jenis.
     *
     * @return array
     */
    public function getPeraturanCountByJenis()
    {
        $cacheKey = 'peraturan_count_by_jenis_v2';
        if (! $cachedData = cache($cacheKey)) {
            $cachedData = $this->db->table('web_jenis_peraturan j')
                ->select('j.nama_jenis, j.slug_jenis, COUNT(p.id_peraturan) as jumlah')
                ->join('web_peraturan p', 'j.id_jenis_peraturan = p.id_jenis_dokumen AND p.is_published = 1', 'left')
                ->where('j.is_active', 1)
                ->groupBy('j.id_jenis_peraturan')
                ->orderBy('j.urutan', 'ASC')
                ->get()->getResultArray();
            // Simpan di cache selama 24 jam
            cache()->save($cacheKey, $cachedData, 86400);
        }
        return $cachedData;
    }

    /**
     * Mendapatkan jumlah peraturan berdasarkan tahun.
     *
     * @return array
     */
    public function getPeraturanCountByTahun()
    {
        $cacheKey = 'peraturan_count_by_tahun_v2';
        if (! $cachedData = cache($cacheKey)) {
            $cachedData = $this->select('tahun, COUNT(id_peraturan) as jumlah')
                ->where('is_published', 1)
                ->groupBy('tahun')
                ->orderBy('tahun', 'DESC')
                ->get()
                ->getResultArray();
            // Simpan di cache selama 24 jam
            cache()->save($cacheKey, $cachedData, 86400);
        }
        return $cachedData;
    }

    /**
     * Mendapatkan detail peraturan berdasarkan slug
     * 
     * @param string $slug
     * @return array|null
     */
    public function getPeraturanDetailBySlug($slug)
    {
        $detail = $this->where('slug', $slug)->first();
        if ($detail) {
            return $this->getPeraturanDetail($detail['id_peraturan']);
        }
        return null;
    }

    /**
     * Mencari peraturan untuk Select2 AJAX di admin (disederhanakan, tanpa filter publish).
     * 
     * @param string $search Kata kunci pencarian
     * @param int|null $id_exclude ID peraturan yang dikecualikan dari hasil
     * @return array
     */
    public function searchPeraturanForAdmin($search = '', $id_exclude = null)
    {
        $builder = $this->select('web_peraturan.id_peraturan, web_peraturan.judul, web_peraturan.nomor, web_peraturan.tahun, web_jenis_peraturan.nama_jenis')
            ->join('web_jenis_peraturan', 'web_jenis_peraturan.id_jenis_peraturan = web_peraturan.id_jenis_dokumen', 'left');

        // Filter berdasarkan pencarian
        if (!empty($search)) {
            $builder->groupStart()
                ->like('web_peraturan.judul', $search)
                ->orLike('web_peraturan.nomor', $search)
                ->orLike('web_peraturan.tahun', $search)
                ->groupEnd();
        }

        // Kecualikan ID tertentu (biasanya peraturan sumber yang sedang diedit)
        if (!empty($id_exclude)) {
            $builder->where('web_peraturan.id_peraturan !=', $id_exclude);
        }

        // Batasi hasil untuk performa
        $builder->limit(50);
        $builder->orderBy('web_peraturan.tahun', 'DESC');
        $builder->orderBy('web_peraturan.tgl_penetapan', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Mengambil peraturan teratas berdasarkan kolom tertentu (hits atau downloads).
     *
     * @param int    $limit   Jumlah data yang akan diambil.
     * @param string $orderBy Kolom untuk pengurutan ('hits' atau 'downloads').
     * @return array
     */
    public function getTopPeraturan($limit = 5, $orderBy = 'hits')
    {
        // Pastikan kolom orderBy valid untuk keamanan
        if (!in_array($orderBy, ['hits', 'downloads'])) {
            $orderBy = 'hits'; // Default ke 'hits' jika tidak valid
        }

        return $this->select('web_peraturan.id_peraturan, web_peraturan.judul, web_peraturan.nomor, web_peraturan.tahun, web_peraturan.slug, web_peraturan.hits, web_peraturan.downloads, web_jenis_peraturan.nama_jenis')
            ->join('web_jenis_peraturan', 'web_jenis_peraturan.id_jenis_peraturan = web_peraturan.id_jenis_dokumen', 'left')
            ->where('web_peraturan.is_published', 1)
            ->orderBy($orderBy, 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Mengambil peraturan terbaru berdasarkan tanggal penetapan.
     *
     * @param int $limit Jumlah data yang akan diambil.
     * @return array
     */
    public function getNewestPeraturan($limit = 5)
    {
        return $this->select('web_peraturan.id_peraturan, web_peraturan.judul, web_peraturan.nomor, web_peraturan.tahun, web_peraturan.slug, web_peraturan.tgl_penetapan, web_jenis_peraturan.nama_jenis')
            ->join('web_jenis_peraturan', 'web_jenis_peraturan.id_jenis_peraturan = web_peraturan.id_jenis_dokumen', 'left')
            ->where('web_peraturan.is_published', 1)
            ->orderBy('web_peraturan.tgl_penetapan', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Clear cache untuk peraturan
     */
    public function clearPeraturanCache()
    {
        try {
            // Clear cache dengan error handling
            $cacheKeys = [
                'peraturan_count_by_jenis_v2',
                'peraturan_count_by_tahun_v2',
                'web_peraturan_total_count'
            ];

            foreach ($cacheKeys as $key) {
                try {
                    cache()->delete($key);
                } catch (\Exception $e) {
                    // Log error tapi jangan stop execution
                    log_message('warning', 'Failed to delete cache key: ' . $key . ' - ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Clear peraturan cache error: ' . $e->getMessage());
        }
    }

    // Override parent methods untuk auto-clear cache
    public function insert($data = null, bool $returnID = true)
    {
        $result = parent::insert($data, $returnID);
        if ($result) {
            $this->clearPeraturanCache();
        }
        return $result;
    }

    public function update($id = null, $data = null): bool
    {
        $result = parent::update($id, $data);
        if ($result) {
            $this->clearPeraturanCache();
        }
        return $result;
    }

    public function delete($id = null, bool $purge = false)
    {
        $data = $this->find($id);
        if ($data && !empty($data['file_dokumen'])) {
            $filePath = $this->filePath . $data['file_dokumen'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        $result = parent::delete($id, $purge);
        if ($result) {
            $this->clearPeraturanCache();
        }
        return $result;
    }

    /**
     * Get tags for a specific peraturan
     * 
     * @param int $id_peraturan
     * @return array
     */
    public function getPeraturanTags($id_peraturan)
    {
        return $this->db->table('web_peraturan_tag wpt')
            ->select('wt.id_tag, wt.nama_tag, wt.slug_tag')
            ->join('web_tag wt', 'wt.id_tag = wpt.id_tag')
            ->where('wpt.id_peraturan', $id_peraturan)
            ->get()
            ->getResultArray();
    }

    /**
     * Get lampiran for a specific peraturan
     * 
     * @param int $id_peraturan
     * @return array
     */
    public function getPeraturanLampiran($id_peraturan)
    {
        return $this->db->table('web_lampiran')
            ->where('id_peraturan', $id_peraturan)
            ->orderBy('urutan', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get peraturan statistics
     * 
     * @return array
     */
    public function getPeraturanStats()
    {
        $cacheKey = 'peraturan_stats_v2';
        if ($cachedData = cache($cacheKey)) {
            return $cachedData;
        }

        // OPTIMASI: Gabungkan 3 query pertama menjadi 1 query dengan aggregation
        // Query 1: Total, hits, dan downloads dalam 1 query
        $baseStats = $this->select('COUNT(*) as total_peraturan, SUM(hits) as total_hits, SUM(downloads) as total_downloads')
            ->where('is_published', 1)
            ->get()
            ->getRowArray();

        $stats = [
            'total_peraturan' => (int)($baseStats['total_peraturan'] ?? 0),
            'total_hits' => (int)($baseStats['total_hits'] ?? 0),
            'total_downloads' => (int)($baseStats['total_downloads'] ?? 0),
        ];

        // Query 2: Peraturan by status
        $statusStats = $this->db->table('web_peraturan wp')
            ->select('sd.nama_status, COUNT(wp.id_peraturan) as jumlah')
            ->join('status_dokumen sd', 'sd.id = wp.id_status', 'left')
            ->where('wp.is_published', 1)
            ->groupBy('sd.nama_status')
            ->get()
            ->getResultArray();

        $stats['by_status'] = [];
        foreach ($statusStats as $status) {
            if (!empty($status['nama_status'])) {
                $stats['by_status'][$status['nama_status']] = (int)$status['jumlah'];
            }
        }

        // Query 3: Peraturan by year (last 5 years) - dihapus karena tidak digunakan di view statistik
        // Data tahunan sudah diambil dari getPeraturanCountByTahun() yang lebih lengkap

        // Cache selama 30 menit
        cache()->save($cacheKey, $stats, 1800);

        return $stats;
    }

    /**
     * Get peraturan count by instansi
     * 
     * @return array
     */
    public function getPeraturanCountByInstansi()
    {
        $cacheKey = 'peraturan_count_by_instansi_v2';
        if ($cachedData = cache($cacheKey)) {
            return $cachedData;
        }

        $result = $this->db->table('web_peraturan wp')
            ->select('i.nama_instansi, COUNT(wp.id_peraturan) as jumlah')
            ->join('instansi i', 'i.id = wp.id_instansi', 'inner')
            ->where('wp.is_published', 1)
            ->where('wp.id_instansi IS NOT NULL')
            ->where('wp.id_instansi !=', 0)
            ->where('i.nama_instansi IS NOT NULL')
            ->where('i.nama_instansi !=', '')
            ->groupBy('i.nama_instansi')
            ->orderBy('jumlah', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        // Cache selama 30 menit
        cache()->save($cacheKey, $result, 1800);

        return $result;
    }

    /**
     * Get peraturan count by month for specific year
     * 
     * @param int $year
     * @return array
     */
    public function getPeraturanCountByMonth($year)
    {
        $months = [
            'Jan' => 1,
            'Feb' => 2,
            'Mar' => 3,
            'Apr' => 4,
            'Mei' => 5,
            'Jun' => 6,
            'Jul' => 7,
            'Ags' => 8,
            'Sep' => 9,
            'Okt' => 10,
            'Nov' => 11,
            'Des' => 12
        ];

        $result = [];

        foreach ($months as $monthName => $monthNum) {
            $count = $this->where('is_published', 1)
                ->where('YEAR(tgl_penetapan)', $year)
                ->where('MONTH(tgl_penetapan)', $monthNum)
                ->countAllResults(false);

            $result[$monthName] = $count;
        }

        return $result;
    }

    /**
     * Get peraturan count by jenis for specific year
     * 
     * @param int $year
     * @return array
     */
    public function getPeraturanCountByJenisForYear($year)
    {
        $cacheKey = 'peraturan_count_by_jenis_year_' . $year;
        if ($cachedData = cache($cacheKey)) {
            return $cachedData;
        }

        $result = $this->db->table('web_peraturan wp')
            ->select('wjp.nama_jenis, COUNT(wp.id_peraturan) as jumlah')
            ->join('web_jenis_peraturan wjp', 'wjp.id_jenis_peraturan = wp.id_jenis_dokumen', 'inner')
            ->where('wp.is_published', 1)
            ->where('wp.tahun', $year)
            ->where('wjp.nama_jenis IS NOT NULL')
            ->where('wjp.nama_jenis !=', '')
            ->groupBy('wjp.nama_jenis')
            ->orderBy('jumlah', 'DESC')
            ->get()
            ->getResultArray();

        // Cache selama 30 menit
        cache()->save($cacheKey, $result, 1800);

        return $result;
    }
}
