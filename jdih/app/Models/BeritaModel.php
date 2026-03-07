<?php

namespace App\Models;

use CodeIgniter\Model;

class BeritaModel extends Model
{
    protected $table            = 'web_berita';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; // Bisa juga 'object' atau class custom Anda
    protected $useSoftDeletes   = false; // Set true jika ingin menggunakan soft delete

    // Kolom yang diizinkan untuk diisi melalui form (mass assignment)
    protected $allowedFields    = [
        'judul',
        'slug',
        'ringkasan',
        'isi_berita',
        'gambar',
        'tanggal_publish',
        'status',
        'penulis_id',
        'kategori_id',
        'view_count'
    ];

    // Dates
    protected $useTimestamps = true; // Otomatis mengisi created_at dan updated_at
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // Jika menggunakan soft deletes

    // Validation
    protected $validationRules      = [
        'judul'           => 'required|min_length[5]|max_length[255]',
        'slug'            => 'max_length[255]|is_unique[web_berita.slug,id,{id}]', // Slug is generated automatically, so it's not required on input
        'isi_berita'      => 'required',
        'tanggal_publish' => 'required|valid_date',
        'status'          => 'required|in_list[published,draft,archived]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateSlug'];
    protected $beforeUpdate   = ['generateSlug'];

    /**
     * Callback untuk men-generate slug secara otomatis dari judul.
     */
    protected function generateSlug(array $data)
    {
        if (isset($data['data']['judul']) && (empty($data['data']['slug']) || !isset($data['data']['slug']))) {
            $slug = url_title($data['data']['judul'], '-', true);

            // Pastikan slug unik
            $originalSlug = $slug;
            $counter = 1;
            // Cek apakah slug sudah ada, dan jika ada, apakah itu untuk record yang sama (saat update)
            while ($this->where('slug', $slug)->where('id !=', $data['id'][0] ?? 0)->countAllResults() > 0) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $data['data']['slug'] = $slug;
        }
        return $data;
    }

    /**
     * Mengambil berita terbaru untuk ditampilkan di beranda.
     * @param int $limit Jumlah berita yang ingin diambil.
     * @return array
     */
    public function getLatestBerita($limit = 3)
    {
        return $this->where('status', 'published')
            ->orderBy('tanggal_publish', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Mengambil detail berita berdasarkan slug.
     * @param string $slug
     * @return array|null
     */
    public function getBeritaBySlug($slug)
    {
        return $this->where('slug', $slug)
            ->where('status', 'published')
            ->first();
    }

    /**
     * Mengambil detail berita berdasarkan slug tanpa memeriksa status.
     * Digunakan untuk preview oleh admin.
     * @param string $slug
     * @return array|null
     */
    public function getAnyBeritaBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Mengambil daftar berita dengan paginasi, bisa difilter per kategori.
     * @param string|null $kategori_slug
     * @return $this
     */
    public function getBeritaPaginated($kategori_slug = null)
    {
        $this->select('web_berita.*, web_berita_kategori.nama_kategori, web_berita_kategori.slug_kategori as kategori_slug')
            ->join('web_berita_kategori', 'web_berita_kategori.id = web_berita.kategori_id', 'left')
            ->where('web_berita.status', 'published');

        if ($kategori_slug) {
            $this->where('web_berita_kategori.slug_kategori', $kategori_slug);
        }

        $this->orderBy('web_berita.tanggal_publish', 'DESC');

        return $this;
    }

    /**
     * Menaikkan jumlah view_count berita.
     * @param int $id ID berita
     */
    public function incrementViewCount($id)
    {
        $this->set('view_count', 'view_count+1', false)
            ->where('id', $id)
            ->update();
    }

    public function getAllBeritaForAdmin()
    {
        return $this->select('web_berita.*, web_berita_kategori.nama_kategori, user.nama as nama_penulis')
            ->join('web_berita_kategori', 'web_berita_kategori.id = web_berita.kategori_id', 'left')
            ->join('user', 'user.id_user = web_berita.penulis_id', 'left')
            ->orderBy('web_berita.tanggal_publish', 'DESC')
            ->findAll();
    }

    public function getKategori()
    {
        return $this->db->table('web_berita_kategori')->get()->getResultArray();
    }

    public function setData($id = '')
    {
        $data = [];
        $data['kategori_list'] = $this->getKategori();
        $data['berita'] = [
            'id' => '',
            'judul' => '',
            'slug' => '',
            'isi_berita' => '',
            'kategori_id' => '',
            'tanggal_publish' => date('Y-m-d H:i:s'),
            'status' => 'draft',
            'gambar' => ''
        ];

        if ($id) {
            $berita_data = $this->find($id);
            if ($berita_data) {
                $data['berita'] = $berita_data;
            }
        }
        return $data;
    }

    public function deleteData($id)
    {
        $berita = $this->find($id);
        if ($berita && !empty($berita['gambar']) && file_exists(FCPATH . 'uploads/berita/' . $berita['gambar'])) {
            unlink(FCPATH . 'uploads/berita/' . $berita['gambar']);
        }

        if ($this->delete($id)) {
            return ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
        }
        return ['status' => 'error', 'message' => 'Data gagal dihapus'];
    }

    public function saveData($request)
    {
        $post = $request->getPost();
        $id = $post['id'] ?? null;

        // Base data
        $data = [
            'judul'           => $post['judul'],
            'isi_berita'      => $post['isi_berita'],
            'kategori_id'     => $post['kategori_id'],
            'tanggal_publish' => $post['tanggal_publish'],
            'status'          => $post['status'],
            'penulis_id'      => session()->get('user')['id_user'] // Menggunakan id_user sesuai skema
        ];

        // Jika ini adalah proses update, tambahkan ID ke data
        if ($id) {
            $data['id'] = $id;
        }

        // Handle file upload
        $img_file = $request->getFile('gambar');
        if ($img_file && $img_file->isValid() && !$img_file->hasMoved()) {
            // Jika mengupdate, hapus gambar lama
            if ($id) {
                $current_data = $this->find($id);
                if (!empty($current_data['gambar']) && file_exists(FCPATH . 'uploads/berita/' . $current_data['gambar'])) {
                    unlink(FCPATH . 'uploads/berita/' . $current_data['gambar']);
                }
            }
            $newName = $img_file->getRandomName();
            $img_file->move(FCPATH . 'uploads/berita', $newName);
            $data['gambar'] = $newName;
        }

        // Save data
        if ($this->save($data) === false) {
            return ['status' => 'error', 'message' => $this->errors()];
        }

        $new_id = $this->getInsertID() ?: $id;
        return ['status' => 'ok', 'message' => 'Data berhasil disimpan.', 'id' => $new_id];
    }

    /**
     * Get berita statistics
     * 
     * @return array
     */
    public function getBeritaStats()
    {
        $stats = [];

        // Total berita published
        $stats['total_berita'] = $this->where('status', 'published')->countAllResults(false);

        // Total views
        $stats['total_views'] = $this->selectSum('view_count')->where('status', 'published')->get()->getRow()->view_count ?? 0;

        // Berita by category
        $categoryStats = $this->db->table('web_berita wb')
            ->select('wbk.nama_kategori, COUNT(wb.id) as jumlah')
            ->join('web_berita_kategori wbk', 'wbk.id = wb.kategori_id', 'left')
            ->where('wb.status', 'published')
            ->groupBy('wbk.nama_kategori')
            ->get()
            ->getResultArray();

        $stats['by_category'] = [];
        foreach ($categoryStats as $category) {
            $stats['by_category'][$category['nama_kategori']] = $category['jumlah'];
        }

        // Berita by month (last 12 months)
        $monthStats = $this->select('YEAR(tanggal_publish) as tahun, MONTH(tanggal_publish) as bulan, COUNT(*) as jumlah')
            ->where('status', 'published')
            ->where('tanggal_publish >=', date('Y-m-d', strtotime('-12 months')))
            ->groupBy('YEAR(tanggal_publish), MONTH(tanggal_publish)')
            ->orderBy('tahun DESC, bulan DESC')
            ->get()
            ->getResultArray();

        $stats['by_month'] = $monthStats;

        return $stats;
    }

    /**
     * Get popular berita based on view count
     * 
     * @param int $limit
     * @return array
     */
    public function getPopularBerita($limit = 10)
    {
        return $this->select('web_berita.*, web_berita_kategori.nama_kategori')
            ->join('web_berita_kategori', 'web_berita_kategori.id = web_berita.kategori_id', 'left')
            ->where('web_berita.status', 'published')
            ->orderBy('web_berita.view_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
