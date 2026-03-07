<?php

namespace App\Models;

use CodeIgniter\Model;

class AgendaModel extends Model
{
    protected $table            = 'web_agenda';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; // Bisa juga 'object' atau nama class custom
    protected $useSoftDeletes   = false; // Set true jika ingin menggunakan soft deletes (perlu kolom deleted_at)

    // Kolom yang diizinkan untuk diisi melalui metode insert, update, save
    protected $allowedFields    = [
        'judul_agenda',
        'slug',
        'deskripsi_singkat',
        'deskripsi_lengkap',
        'tanggal_mulai',
        'tanggal_selesai',
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'penyelenggara',
        'target_peserta',
        'kontak_person_nama',
        'kontak_person_email',
        'kontak_person_telepon',
        'gambar_agenda',
        'status_agenda',
        'created_by',
        'updated_by'
    ];

    // Menggunakan timestamps untuk created_at dan updated_at
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // Jika menggunakan soft deletes

    // Validasi (opsional, bisa juga di controller)
    // protected $validationRules      = [];
    // protected $validationMessages   = [];
    // protected $skipValidation       = false;
    // protected $cleanValidationRules = true;

    // Callbacks (opsional)
    // protected $allowCallbacks = true;
    // protected $beforeInsert   = [];
    // protected $afterInsert    = [];
    // protected $beforeUpdate   = [];
    // protected $afterUpdate    = [];
    // protected $beforeFind     = [];
    // protected $afterFind      = [];
    // protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // === Datatables (Admin) ===
    private array $columnOrder = [null, 'judul_agenda', 'tanggal_mulai', 'waktu_mulai', 'lokasi', 'status_agenda'];
    private array $columnSearch = ['judul_agenda', 'lokasi', 'penyelenggara'];

    private function _get_datatables_query($request)
    {
        $builder = $this->select('*');

        // Pencarian global
        $searchValue = $request->getPost('search')['value'] ?? null;
        if ($searchValue) {
            $builder->groupStart();
            foreach ($this->columnSearch as $item) {
                $builder->orLike($item, $searchValue);
            }
            $builder->groupEnd();
        }

        // Urutan kolom
        $order = $request->getPost('order');
        if ($order && isset($order[0]['column'])) {
            $colIndex = $order[0]['column'];
            $dir = $order[0]['dir'] === 'asc' ? 'ASC' : 'DESC';
            if (isset($this->columnOrder[$colIndex]) && $this->columnOrder[$colIndex] !== null) {
                $builder->orderBy($this->columnOrder[$colIndex], $dir);
            } else {
                $builder->orderBy('tanggal_mulai', 'DESC');
            }
        } else {
            $builder->orderBy('tanggal_mulai', 'DESC');
        }

        return $builder;
    }

    public function getDatatables($request)
    {
        $builder = $this->_get_datatables_query($request);
        $length = $request->getPost('length');
        $start = $request->getPost('start');
        if ($length != -1) {
            $builder->limit($length, $start);
        }
        return $builder->get()->getResult();
    }

    public function countFiltered($request)
    {
        $builder = $this->_get_datatables_query($request);
        return $builder->countAllResults();
    }

    public function countAll()
    {
        return $this->countAllResults();
    }

    /**
     * Mengambil daftar tahun unik dari data agenda.
     *
     * @return array
     */
    public function getAgendaFiltered($bulan, $tahun)
    {
        $builder = $this->orderBy('tanggal_mulai', 'DESC');

        if (!empty($tahun)) {
            $builder->where('YEAR(tanggal_mulai)', $tahun);
        }

        if (!empty($bulan)) {
            $builder->where('MONTH(tanggal_mulai)', $bulan);
        }

        return $builder;
    }

    public function getTahunAgenda()
    {
        return $this->distinct()
            ->select('YEAR(tanggal_mulai) as tahun')
            ->orderBy('tahun', 'DESC')
            ->findAll();
    }

    /**
     * Mengambil agenda berdasarkan slug.
     *
     * @param string $slug
     * @return array|object|null
     */
    public function getAgendaBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Mengambil agenda yang akan datang dan sedang berlangsung, diurutkan berdasarkan tanggal mulai.
     *
     * @param int $limit Jumlah data yang ingin diambil
     * @param int $offset Posisi awal pengambilan data (untuk pagination)
     * @return array
     */
    public function getUpcomingAndOngoingAgendas($limit = 10, $offset = 0)
    {
        $today = date('Y-m-d'); // Mendapatkan tanggal hari ini

        return $this->groupStart() // Memulai grup kondisi
            ->where($this->table . '.tanggal_selesai >=', $today) // Kondisi pertama: tanggal selesai ada dan belum lewat
            ->orWhere($this->table . '.tanggal_selesai IS NULL')  // Kondisi kedua: atau tanggal selesai NULL
            ->groupEnd()   // Menutup grup kondisi
            ->orderBy('tanggal_mulai', 'ASC')
            ->findAll($limit, $offset);
    }

    /**
     * Mengambil semua agenda dengan paginasi.
     *
     * @param int $perPage Jumlah item per halaman
     * @return array Data agenda dan link paginasi
     */
    public function getAllAgendasPaginated($perPage = 10, array $filters = [])
    {
        $builder = $this->orderBy('tanggal_mulai', 'DESC');

        if (!empty($filters['tahun'])) {
            $builder->where('YEAR(tanggal_mulai)', $filters['tahun']);
        }

        if (!empty($filters['bulan'])) {
            $builder->where('MONTH(tanggal_mulai)', $filters['bulan']);
        }

        return [
            'agenda' => $builder->paginate($perPage),
            'pager'  => $this->pager,
        ];
    }

    /**
     * Mengambil semua agenda untuk tampilan kalender, dengan opsi filter.
     *
     * @param array $filters Filter berdasarkan tahun dan bulan
     * @return array Data agenda
     */
    public function getAgendasForCalendar(array $filters = [])
    {
        $builder = $this->orderBy('tanggal_mulai', 'ASC'); // ASC untuk urutan di kalender

        if (!empty($filters['tahun'])) {
            $builder->where('YEAR(tanggal_mulai)', $filters['tahun']);
        }

        if (!empty($filters['bulan'])) {
            $builder->where('MONTH(tanggal_mulai)', $filters['bulan']);
        }
        // Jika tidak ada filter tahun & bulan, mungkin kita ingin default ke bulan & tahun saat ini
        // atau beberapa bulan ke depan agar tidak terlalu banyak data sekaligus.
        // Untuk saat ini, kita ambil semua jika tidak ada filter spesifik.

        return $builder->findAll();
    }

    /**
     * Mengambil agenda yang akan datang dan hari ini untuk widget di homepage.
     * @param int $limit Jumlah agenda yang ingin ditampilkan.
     * @return array
     */
    public function getUpcomingAgenda($limit = 3)
    {
        // Mengambil tanggal hari ini dalam format YYYY-MM-DD
        $today = date('Y-m-d');

        // Menggunakan Query Builder untuk mengambil data
        // 1. Filter agenda yang tanggal mulainya hari ini atau setelahnya
        // 2. Urutkan berdasarkan tanggal mulai yang paling dekat
        // 3. Batasi jumlah hasil sesuai parameter $limit
        return $this->where('tanggal_mulai >=', $today)
            ->orderBy('tanggal_mulai', 'ASC')
            ->limit($limit)
            ->find();
    }

    /**
     * Get agenda statistics
     * 
     * @return array
     */
    public function getAgendaStats()
    {
        $stats = [];

        // Total agenda
        $stats['total_agenda'] = $this->countAllResults(false);

        // Agenda by status
        $statusStats = $this->select('status_agenda, COUNT(*) as jumlah')
            ->groupBy('status_agenda')
            ->get()
            ->getResultArray();

        $stats['by_status'] = [];
        foreach ($statusStats as $status) {
            $stats['by_status'][$status['status_agenda']] = $status['jumlah'];
        }

        // Upcoming agenda (next 30 days)
        $nextMonth = date('Y-m-d', strtotime('+30 days'));
        $today = date('Y-m-d');
        $stats['upcoming_agenda'] = $this->where('tanggal_mulai >=', $today)
            ->where('tanggal_mulai <=', $nextMonth)
            ->countAllResults(false);

        // Agenda by month (last 12 months)
        $monthStats = $this->select('YEAR(tanggal_mulai) as tahun, MONTH(tanggal_mulai) as bulan, COUNT(*) as jumlah')
            ->where('tanggal_mulai >=', date('Y-m-d', strtotime('-12 months')))
            ->groupBy('YEAR(tanggal_mulai), MONTH(tanggal_mulai)')
            ->orderBy('tahun DESC, bulan DESC')
            ->get()
            ->getResultArray();

        $stats['by_month'] = $monthStats;

        return $stats;
    }
}
