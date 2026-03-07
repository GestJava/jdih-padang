<?php

namespace App\Models;

use CodeIgniter\Model;

class WebPeraturanRelasiModel extends Model
{
    protected $table      = 'web_peraturan_relasi';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id_peraturan',
        'id_peraturan_sumber',
        'id_peraturan_terkait',
        'jenis_relasi',
        'id_jenis_relasi',
        'status_before_change',
        'created_by',
        'keterangan'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mendapatkan semua peraturan terkait dengan peraturan tertentu
     *
     * @param int $id_peraturan
     * @return array
     */
    public function getPeraturanTerkait($id_peraturan)
    {
        // Common fields to select from web_peraturan
        $select_fields = 'p.id_peraturan, p.nomor, p.tahun, p.judul, p.tgl_penetapan, jjd.nama_jenis, pr.jenis_relasi, pr.keterangan';

        // Result set 1: Relasi dimana peraturan ini adalah SUMBER (misal: peraturan ini MENGUBAH peraturan lain)
        $builder = $this->db->table($this->table . ' pr');
        $builder->select($select_fields);
        $builder->join('web_peraturan p', 'p.id_peraturan = pr.id_peraturan_terkait');
        $builder->join('web_jenis_peraturan jjd', 'jjd.id_jenis_peraturan = p.id_jenis_dokumen', 'left');
        $builder->where('pr.id_peraturan_sumber', $id_peraturan);
        $builder->where('p.is_published', 1);
        $builder->orderBy('p.tahun', 'DESC');
        $builder->orderBy('p.tgl_penetapan', 'DESC');
        $result1 = $builder->get()->getResultArray();

        // Result set 2: Relasi dimana peraturan ini adalah TERKAIT (misal: peraturan ini DIUBAH oleh peraturan lain)
        $builder2 = $this->db->table($this->table . ' pr');
        $builder2->select($select_fields);
        $builder2->join('web_peraturan p', 'p.id_peraturan = pr.id_peraturan_sumber');
        $builder2->join('web_jenis_peraturan jjd', 'jjd.id_jenis_peraturan = p.id_jenis_dokumen', 'left');
        $builder2->where('pr.id_peraturan_terkait', $id_peraturan);
        $builder2->where('p.is_published', 1);
        $builder2->orderBy('p.tahun', 'DESC');
        $builder2->orderBy('p.tgl_penetapan', 'DESC');
        $result2 = $builder2->get()->getResultArray();

        // Gabungkan kedua result set
        return array_merge($result1, $result2);
    }

    /**
     * Menghapus semua relasi untuk peraturan tertentu
     *
     * @param int $id_peraturan
     * @return boolean
     */
    public function deleteByPeraturanId($id_peraturan)
    {
        // Hapus relasi dimana peraturan ini adalah sumber
        $this->where('id_peraturan', $id_peraturan)->delete();

        // Hapus relasi dimana peraturan ini adalah target
        return $this->where('id_peraturan_terkait', $id_peraturan)->delete();
    }

    /**
     * Menambahkan relasi untuk peraturan tertentu
     *
     * @param int $id_peraturan
     * @param array $relasi_data
     * @return boolean
     */
    public function addRelasiToPeraturan($id_peraturan, $relasi_data)
    {
        $data = [];
        foreach ($relasi_data as $relasi) {
            $data[] = [
                'id_peraturan' => $id_peraturan,
                'id_peraturan_terkait' => $relasi['id_peraturan_terkait'],
                'jenis_relasi' => $relasi['jenis_relasi'],
                'keterangan' => $relasi['keterangan'] ?? ''
            ];
        }

        if (!empty($data)) {
            return $this->insertBatch($data);
        }

        return true;
    }

    /**
     * Mendapatkan semua relasi untuk peraturan tertentu
     *
     * @param int $id_peraturan
     * @return array
     */
    public function getRelasiByPeraturan($id_peraturan)
    {
        $builder = $this->db->table($this->table . ' pr');
        $builder->select('pr.*, p.nomor, p.tahun, p.judul, jjd.nama_jenis');
        $builder->join('web_peraturan p', 'p.id_peraturan = pr.id_peraturan_terkait');
        $builder->join('web_jenis_peraturan jjd', 'jjd.id_jenis_peraturan = p.id_jenis_dokumen', 'left');
        $builder->where('pr.id_peraturan_sumber', $id_peraturan); // Menggunakan id_peraturan_sumber agar konsisten
        $builder->orderBy('p.tahun', 'DESC');
        $builder->orderBy('p.tgl_penetapan', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Menyimpan relasi peraturan (menghapus relasi lama dan menambahkan yang baru)
     *
     * @param int $id_peraturan ID peraturan sumber
     * @param array $relasi_data Data relasi ['mengubah' => [...], 'mencabut' => [...]]
     * @return boolean
     */
    public function saveRelasi($id_peraturan, $relasi_data)
    {
        // Hapus relasi lama
        $this->where('id_peraturan_sumber', $id_peraturan)->delete();

        // Simpan relasi baru
        if (!empty($relasi_data['mengubah'])) {
            foreach ($relasi_data['mengubah'] as $id_terkait) {
                $this->insert([
                    'id_peraturan_sumber' => $id_peraturan,
                    'id_peraturan_terkait' => $id_terkait,
                    'jenis_relasi' => 'mengubah'
                ]);
            }
        }

        if (!empty($relasi_data['mencabut'])) {
            foreach ($relasi_data['mencabut'] as $id_terkait) {
                $this->insert([
                    'id_peraturan_sumber' => $id_peraturan,
                    'id_peraturan_terkait' => $id_terkait,
                    'jenis_relasi' => 'mencabut'
                ]);
            }
        }

        return true;
    }

    /**
     * Mendapatkan relasi peraturan berdasarkan jenis relasi
     *
     * @param int $id_peraturan ID peraturan sumber
     * @param string $jenis_relasi Jenis relasi ('mengubah', 'mencabut', dll)
     * @return array
     */
    public function getRelasiPeraturan($id_peraturan, $jenis_relasi)
    {
        $builder = $this->db->table($this->table . ' wr');
        $builder->select('wr.id_peraturan_sumber, wr.id_peraturan_terkait, wr.jenis_relasi, wp.nomor, wp.tahun, wp.judul as judul_peraturan_terkait, jjd.nama_jenis');
        $builder->join('web_peraturan wp', 'wp.id_peraturan = wr.id_peraturan_terkait');
        $builder->join('web_jenis_peraturan jjd', 'jjd.id_jenis_peraturan = wp.id_jenis_dokumen', 'left');
        $builder->where('wr.id_peraturan_sumber', $id_peraturan);
        $builder->where('wr.jenis_relasi', $jenis_relasi);
        $builder->orderBy('wp.tahun', 'DESC');
        $builder->orderBy('wp.tgl_penetapan', 'DESC');

        $result = $builder->get()->getResultArray();

        // Pastikan selalu mengembalikan array, meskipun kosong
        return $result ? $result : [];
    }

    /**
     * Mendapatkan semua relasi peraturan untuk admin (optimized - 1 query)
     *
     * @param int $id_peraturan ID peraturan sumber
     * @return array
     */
    public function getAllRelasiForAdmin($id_peraturan)
    {
        $builder = $this->db->table($this->table . ' wr');
        $builder->select('wr.id_peraturan_sumber, wr.id_peraturan_terkait, wr.jenis_relasi, wp.nomor, wp.tahun, wp.judul as judul_peraturan_terkait, jjd.nama_jenis');
        $builder->join('web_peraturan wp', 'wp.id_peraturan = wr.id_peraturan_terkait');
        $builder->join('web_jenis_peraturan jjd', 'jjd.id_jenis_peraturan = wp.id_jenis_dokumen', 'left');
        $builder->where('wr.id_peraturan_sumber', $id_peraturan);
        $builder->orderBy('wr.jenis_relasi', 'ASC'); // Urutkan berdasarkan jenis relasi
        $builder->orderBy('wp.tahun', 'DESC');
        $builder->orderBy('wp.tgl_penetapan', 'DESC');

        $result = $builder->get()->getResultArray();

        return $result ? $result : [];
    }

    /**
     * Simpan relasi dengan auto-update status dan audit trail
     * 
     * @param array $data Data relasi
     * @return int|bool ID relasi yang baru dibuat atau false jika gagal
     */
    public function saveRelasiWithStatusUpdate($data)
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // 1. Validasi input
            if (empty($data['id_jenis_relasi']) || empty($data['id_peraturan_sumber']) || empty($data['id_peraturan_terkait'])) {
                throw new \Exception('Data relasi tidak lengkap');
            }

            // 2. Load models yang diperlukan
            $jenisRelasiModel = new \App\Models\WebJenisRelasiModel();
            $peraturanModel = new \App\Models\WebPeraturanModel();
            $statusLogModel = new \App\Models\WebPeraturanStatusLogModel();

            // 3. Ambil data jenis relasi
            $jenisRelasi = $jenisRelasiModel->find($data['id_jenis_relasi']);
            if (!$jenisRelasi) {
                throw new \Exception('Jenis relasi tidak ditemukan');
            }

            // 4. Simpan status sebelum perubahan jika auto-update
            if ($jenisRelasi['auto_update_status'] && $jenisRelasi['status_target']) {
                $peraturanTerkait = $peraturanModel->find($data['id_peraturan_terkait']);
                if ($peraturanTerkait) {
                    $data['status_before_change'] = $peraturanTerkait['id_status'];
                }
            }

            // 5. Set created_by dari session
            $data['created_by'] = session('user_id');

            // 6. Set jenis_relasi untuk backward compatibility
            $data['jenis_relasi'] = $jenisRelasi['kode_jenis'];

            // 7. Simpan relasi
            $relasiId = $this->insert($data);
            if (!$relasiId) {
                throw new \Exception('Gagal menyimpan relasi');
            }

            // 8. Update status peraturan terkait jika diperlukan
            if ($jenisRelasi['auto_update_status'] && $jenisRelasi['status_target']) {
                $oldStatus = $data['status_before_change'] ?? null;
                $newStatus = $jenisRelasi['status_target'];

                // Update status peraturan
                $peraturanModel->update($data['id_peraturan_terkait'], ['id_status' => $newStatus]);

                // Log perubahan status
                $statusLogModel->logStatusChange(
                    $data['id_peraturan_terkait'],
                    $oldStatus,
                    $newStatus,
                    'relasi_add',
                    $relasiId,
                    'Auto-update status karena relasi ' . $jenisRelasi['nama_jenis'],
                    session('user_id')
                );
            }

            $db->transCommit();
            return $relasiId;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error saving relasi with status update: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Hapus relasi dengan rollback status otomatis
     * 
     * @param int $id_sumber
     * @param int $id_terkait
     * @return bool
     */
    public function deleteRelasiWithStatusRollback($id_sumber, $id_terkait)
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // 1. Ambil data relasi sebelum dihapus
            $relasi = $this->where('id_peraturan_sumber', $id_sumber)
                ->where('id_peraturan_terkait', $id_terkait)
                ->first();

            if (!$relasi) {
                throw new \Exception('Relasi tidak ditemukan');
            }

            // 2. Load models yang diperlukan
            $peraturanModel = new \App\Models\WebPeraturanModel();
            $statusLogModel = new \App\Models\WebPeraturanStatusLogModel();

            // 3. Rollback status jika ada status sebelumnya
            if ($relasi['status_before_change']) {
                $peraturanTerkait = $peraturanModel->find($id_terkait);
                $currentStatus = $peraturanTerkait['id_status'];

                // Update status ke status sebelumnya
                $peraturanModel->update($id_terkait, ['id_status' => $relasi['status_before_change']]);

                // Log rollback status
                $statusLogModel->logStatusChange(
                    $id_terkait,
                    $currentStatus,
                    $relasi['status_before_change'],
                    'relasi_delete',
                    $relasi['id'],
                    'Auto rollback status saat relasi dihapus',
                    session('user_id')
                );
            }

            // 4. Hapus relasi
            $result = $this->where('id_peraturan_sumber', $id_sumber)
                ->where('id_peraturan_terkait', $id_terkait)
                ->delete();

            if (!$result) {
                throw new \Exception('Gagal menghapus relasi');
            }

            $db->transCommit();
            return true;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error deleting relasi with status rollback: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mendapatkan relasi dengan informasi lengkap termasuk jenis relasi
     * 
     * @param int $id_peraturan
     * @return array
     */
    public function getRelasiWithJenisRelasi($id_peraturan)
    {
        return $this->select('web_peraturan_relasi.*,
                            jr.nama_jenis, jr.deskripsi as jenis_deskripsi,
                            jr.auto_update_status, jr.status_target,
                            wp.nomor, wp.tahun, wp.judul as judul_peraturan_terkait,
                            jjd.nama_jenis as jenis_peraturan,
                            sd.nama_status as status_before_nama')
            ->join('web_jenis_relasi jr', 'jr.id_jenis_relasi = web_peraturan_relasi.id_jenis_relasi', 'left')
            ->join('web_peraturan wp', 'wp.id_peraturan = web_peraturan_relasi.id_peraturan_terkait')
            ->join('web_jenis_peraturan jjd', 'jjd.id_jenis_peraturan = wp.id_jenis_dokumen', 'left')
            ->join('status_dokumen sd', 'sd.id = web_peraturan_relasi.status_before_change', 'left')
            ->where('web_peraturan_relasi.id_peraturan_sumber', $id_peraturan)
            ->orderBy('jr.urutan', 'ASC')
            ->orderBy('wp.tahun', 'DESC')
            ->findAll();
    }

    /**
     * Cek apakah ada konflik relasi (circular reference)
     * 
     * @param int $id_sumber
     * @param int $id_terkait
     * @return bool
     */
    public function hasCircularReference($id_sumber, $id_terkait)
    {
        // Cek apakah sudah ada relasi sebaliknya
        $existing = $this->where('id_peraturan_sumber', $id_terkait)
            ->where('id_peraturan_terkait', $id_sumber)
            ->first();

        return $existing !== null;
    }

    /**
     * Mendapatkan statistik relasi per jenis
     * 
     * @return array
     */
    public function getRelasiStatistics()
    {
        return $this->select('jr.nama_jenis, COUNT(*) as total_relasi')
            ->join('web_jenis_relasi jr', 'jr.id_jenis_relasi = web_peraturan_relasi.id_jenis_relasi')
            ->groupBy('web_peraturan_relasi.id_jenis_relasi')
            ->orderBy('total_relasi', 'DESC')
            ->findAll();
    }
}
