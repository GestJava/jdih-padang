<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Config\HarmonisasiStatus;

class HarmonisasiAjuanModel extends Model
{
    protected $table            = 'harmonisasi_ajuan';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'judul_peraturan',
        'id_jenis_peraturan',
        'id_instansi_pemohon',
        'id_user_pemohon',
        'id_status_ajuan',
        'id_petugas_verifikasi',
        'tanggal_pengajuan',
        'tanggal_selesai',
        'keterangan',
        'tte_signed_at',
        'tte_file_path',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Base query dengan semua JOIN yang diperlukan
     * Mengurangi duplikasi kode
     */
    private function getBaseQueryWithJoins()
    {
        return $this->db->table($this->table . ' ha')
            ->select([
                'ha.*',
                'ha.id as id_ajuan',
                'u.nama as nama_pemohon',
                'i.nama_instansi',
                'j.nama_jenis',
                's.nama_status',
                'v.nama as nama_verifikator'
            ])
            ->join('user u', 'u.id_user = ha.id_user_pemohon', 'left')
            ->join('user v', 'v.id_user = ha.id_petugas_verifikasi', 'left')
            ->join('instansi i', 'i.id = ha.id_instansi_pemohon', 'left')
            ->join('harmonisasi_jenis_peraturan j', 'j.id = ha.id_jenis_peraturan', 'left')
            ->join('harmonisasi_status s', 's.id = ha.id_status_ajuan', 'left');
    }

    // Method untuk mengambil data ajuan untuk user tertentu
    public function getAjuanForUser($userId)
    {
        return $this->getBaseQueryWithJoins()
            ->where('ha.id_user_pemohon', $userId)
            ->orderBy('ha.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    // Method untuk mengambil data ajuan untuk instansi tertentu
    public function getAjuanForInstansi($instansiId)
    {
        return $this->getBaseQueryWithJoins()
            ->where('ha.id_instansi_pemohon', $instansiId)
            ->orderBy('ha.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getAjuanDetail($id)
    {
        return $this->getBaseQueryWithJoins()
            ->where('ha.id', $id)
            ->get()
            ->getRowArray();
    }

    // Method untuk mengambil data ajuan dengan detail relasi untuk seluruh daftar atau satu item
    public function getAjuanWithDetails($id = null)
    {
        $query = $this->getBaseQueryWithJoins();

        if ($id !== null) {
            $query->where('ha.id', $id);
            return $query->get()->getRowArray();
        }

        return $query->orderBy('ha.created_at', 'DESC')->get()->getResultArray();
    }

    // Method untuk mengambil ajuan berdasarkan beberapa status (untuk dashboard verifikator, validator, dll)
    public function getAjuanByStatus(array $status_ids)
    {
        return $this->getBaseQueryWithJoins()
            ->whereIn('ha.id_status_ajuan', $status_ids)
            ->orderBy('ha.updated_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    // Method untuk mengambil ajuan berdasarkan status tunggal
    public function getAjuanBySingleStatus($status_id)
    {
        return $this->getBaseQueryWithJoins()
            ->where('ha.id_status_ajuan', $status_id)
            ->orderBy('ha.updated_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    // Method untuk mengambil ajuan yang perlu ditugaskan (status: Diajukan ke Kabag)
    public function getAjuanForPenugasan()
    {
        return $this->getBaseQueryWithJoins()
            ->where('ha.id_status_ajuan', HarmonisasiStatus::DIAJUKAN)
            ->orderBy('ha.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    // Method untuk mengambil ajuan dengan detail berdasarkan status tertentu
    public function getAjuanWithDetailsByStatus($status_id)
    {
        return $this->getBaseQueryWithJoins()
            ->where('ha.id_status_ajuan', $status_id)
            ->orderBy('ha.updated_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    // Method untuk mengambil ajuan yang perlu diverifikasi oleh verifikator tertentu
    public function getAjuanForVerifier($id_user)
    {
        return $this->getBaseQueryWithJoins()
            ->where('ha.id_petugas_verifikasi', $id_user)
            ->where('ha.id_status_ajuan', HarmonisasiStatus::VERIFIKASI)
            ->orderBy('ha.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    // Method untuk mengambil ajuan yang perlu divalidasi (status: Proses Validasi)
    public function getAjuanForValidation()
    {
        return $this->getAjuanWithDetailsByStatus(HarmonisasiStatus::VALIDASI);
    }

    // Method untuk mengambil ajuan yang perlu difinalisasi (status: Proses Finalisasi)  
    public function getAjuanForFinalisasi()
    {
        return $this->getAjuanByStatus([HarmonisasiStatus::FINALISASI, HarmonisasiStatus::REVISI_FINALISASI]);
    }

    // Method untuk mengambil ajuan yang sudah selesai
    public function getAjuanSelesai()
    {
        return $this->getAjuanWithDetailsByStatus(HarmonisasiStatus::SELESAI);
    }

    // Method untuk statistik workflow
    public function getWorkflowStats()
    {
        $stats = [];
        $workflowSequence = HarmonisasiStatus::getWorkflowSequence();

        foreach ($workflowSequence as $status) {
            $count = $this->where('id_status_ajuan', $status)->countAllResults(false);
            $stats[HarmonisasiStatus::getStatusName($status)] = $count;
        }

        return $stats;
    }

    // Method untuk statistik finalisator
    public function getFinalisatorStats()
    {
        return $this->db->table('user u')
            ->select([
                'u.nama',
                'COUNT(ha.id) as jumlah'
            ])
            ->join('harmonisasi_ajuan ha', 'ha.id_user_pemohon = u.id_user', 'left')
            ->where('ha.id_status_ajuan', HarmonisasiStatus::FINALISASI)
            ->groupBy('u.id_user, u.nama')
            ->orderBy('jumlah', 'DESC')
            ->limit(4)
            ->get()
            ->getResultArray();
    }

    // ============================================================
    // STATISTIK TAHUN INI - UNTUK SEMUA USER
    // ============================================================

    /**
     * Mengambil statistik beban kerja verifikator (Top 5)
     * Menggantikan N+1 query pada controller
     */
    public function getVerifikatorWorkloadStats($limit = 5)
    {
        return $this->db->table('user u')
            ->select('u.nama, COUNT(ha.id) as jumlah')
            ->join('harmonisasi_ajuan ha', 'ha.id_petugas_verifikasi = u.id_user AND ha.id_status_ajuan = ' . HarmonisasiStatus::VERIFIKASI, 'left')
            ->join('user_role ur', 'ur.id_user = u.id_user')
            ->where('ur.id_role', 7) // Role ID 7 = Verifikator
            ->groupBy('u.id_user, u.nama')
            ->orderBy('jumlah', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    // ============================================================
    // STATISTIK TAHUN INI - UNTUK SEMUA USER
    // ============================================================

    /**
     * Total ajuan yang masuk tahun ini
     */
    public function getTotalAjuanByYear($year)
    {
        return $this->where('YEAR(created_at)', $year)
            ->countAllResults(false);
    }

    /**
     * Total ajuan yang selesai tahun ini
     */
    public function getTotalSelesaiByYear($year)
    {
        return $this->where('YEAR(created_at)', $year)
            ->where('id_status_ajuan', HarmonisasiStatus::SELESAI)
            ->countAllResults(false);
    }

    /**
     * Total ajuan yang ditolak tahun ini
     */
    public function getTotalDitolakByYear($year)
    {
        return $this->where('YEAR(created_at)', $year)
            ->where('id_status_ajuan', HarmonisasiStatus::DITOLAK)
            ->countAllResults(false);
    }

    /**
     * Total ajuan yang masih dalam proses tahun ini
     */
    public function getTotalProsesByYear($year)
    {
        return $this->where('YEAR(created_at)', $year)
            ->whereIn('id_status_ajuan', [
                HarmonisasiStatus::DIAJUKAN,        // 2
                HarmonisasiStatus::VERIFIKASI,      // 3
                HarmonisasiStatus::VALIDASI,        // 4
                HarmonisasiStatus::REVISI,          // 5
                HarmonisasiStatus::FINALISASI,      // 6
                HarmonisasiStatus::PARAF_OPD,       // 7
                HarmonisasiStatus::PARAF_KABAG,     // 8
                HarmonisasiStatus::PARAF_ASISTEN,   // 9
                HarmonisasiStatus::REVISI_FINALISASI, // 10
                HarmonisasiStatus::PARAF_SEKDA,      // 11
                HarmonisasiStatus::PARAF_WAWAKO,     // 12
                HarmonisasiStatus::TTE_WALIKOTA      // 13
            ])
            ->countAllResults(false);
    }

    // ============================================================
    // STATISTIK TAHUN INI - UNTUK USER TERTENTU
    // ============================================================

    /**
     * Total ajuan yang ditugaskan ke user tertentu tahun ini
     */
    public function getTotalAjuanByYearAndUser($year, $user_id)
    {
        return $this->where('YEAR(created_at)', $year)
            ->where('id_petugas_verifikasi', $user_id)
            ->countAllResults(false);
    }

    /**
     * Total ajuan yang selesai oleh user tertentu tahun ini
     */
    public function getTotalSelesaiByYearAndUser($year, $user_id)
    {
        return $this->where('YEAR(created_at)', $year)
            ->where('id_petugas_verifikasi', $user_id)
            ->where('id_status_ajuan', HarmonisasiStatus::SELESAI)
            ->countAllResults(false);
    }

    /**
     * Total ajuan yang ditolak oleh user tertentu tahun ini
     */
    public function getTotalDitolakByYearAndUser($year, $user_id)
    {
        return $this->where('YEAR(created_at)', $year)
            ->where('id_petugas_verifikasi', $user_id)
            ->where('id_status_ajuan', HarmonisasiStatus::DITOLAK)
            ->countAllResults(false);
    }

    /**
     * Total ajuan yang masih dalam proses oleh user tertentu tahun ini
     */
    public function getTotalProsesByYearAndUser($year, $user_id)
    {
        return $this->where('YEAR(created_at)', $year)
            ->where('id_petugas_verifikasi', $user_id)
            ->whereIn('id_status_ajuan', [
                HarmonisasiStatus::DIAJUKAN,        // 2
                HarmonisasiStatus::VERIFIKASI,      // 3
                HarmonisasiStatus::VALIDASI,        // 4
                HarmonisasiStatus::REVISI,          // 5
                HarmonisasiStatus::FINALISASI,      // 6
                HarmonisasiStatus::PARAF_OPD,       // 7
                HarmonisasiStatus::PARAF_KABAG,     // 8
                HarmonisasiStatus::PARAF_ASISTEN,   // 9
                HarmonisasiStatus::REVISI_FINALISASI, // 10
                HarmonisasiStatus::PARAF_SEKDA,      // 11
                HarmonisasiStatus::PARAF_WAWAKO,     // 12
                HarmonisasiStatus::TTE_WALIKOTA      // 13
            ])
            ->countAllResults(false);
    }

    // ============================================================
    // METHOD UNTUK MODUL PARAF
    // ============================================================

    /**
     * Mengambil ajuan berdasarkan status dan instansi tertentu
     */
    public function getAjuanByStatusAndInstansi($status_id, $instansi_id)
    {
        return $this->getBaseQueryWithJoins()
            ->where('ha.id_status_ajuan', $status_id)
            ->where('ha.id_instansi_pemohon', $instansi_id)
            ->orderBy('ha.updated_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Mengambil ajuan yang menunggu paraf OPD
     */
    public function getAjuanMenungguParafOPD()
    {
        return $this->getAjuanBySingleStatus(HarmonisasiStatus::PARAF_OPD);
    }

    /**
     * Mengambil ajuan yang menunggu paraf Kabag
     */
    public function getAjuanMenungguParafKabag()
    {
        return $this->getAjuanBySingleStatus(HarmonisasiStatus::PARAF_KABAG);
    }

    /**
     * Mengambil ajuan yang menunggu paraf Asisten
     */
    public function getAjuanMenungguParafAsisten()
    {
        return $this->getAjuanBySingleStatus(HarmonisasiStatus::PARAF_ASISTEN);
    }

    /**
     * Mengambil ajuan yang menunggu paraf Sekda
     */
    public function getAjuanMenungguParafSekda()
    {
        return $this->getAjuanBySingleStatus(HarmonisasiStatus::PARAF_SEKDA);
    }

    /**
     * Mengambil ajuan yang menunggu paraf Wawako
     */
    public function getAjuanMenungguParafWawako()
    {
        return $this->getAjuanBySingleStatus(HarmonisasiStatus::PARAF_WAWAKO);
    }

    /**
     * Mengambil ajuan yang menunggu TTE Walikota
     */
    public function getAjuanMenungguTTEWalikota()
    {
        return $this->getAjuanBySingleStatus(HarmonisasiStatus::TTE_WALIKOTA);
    }

    /**
     * Statistik paraf untuk dashboard
     */
    public function getParafStats()
    {
        $stats = [
            'menunggu_opd' => $this->where('id_status_ajuan', HarmonisasiStatus::PARAF_OPD)->countAllResults(false),
            'menunggu_kabag' => $this->where('id_status_ajuan', HarmonisasiStatus::PARAF_KABAG)->countAllResults(false),
            'menunggu_asisten' => $this->where('id_status_ajuan', HarmonisasiStatus::PARAF_ASISTEN)->countAllResults(false),
            'menunggu_sekda' => $this->where('id_status_ajuan', HarmonisasiStatus::PARAF_SEKDA)->countAllResults(false),
            'menunggu_wawako' => $this->where('id_status_ajuan', HarmonisasiStatus::PARAF_WAWAKO)->countAllResults(false),
            'menunggu_walikota' => $this->where('id_status_ajuan', HarmonisasiStatus::TTE_WALIKOTA)->countAllResults(false)
        ];

        return $stats;
    }

    /**
     * Statistik paraf berdasarkan instansi
     */
    public function getParafStatsByInstansi($instansi_id)
    {
        $stats = [
            'menunggu_opd' => $this->where('id_status_ajuan', HarmonisasiStatus::PARAF_OPD)
                ->where('id_instansi_pemohon', $instansi_id)->countAllResults(false),
            'menunggu_kabag' => $this->where('id_status_ajuan', HarmonisasiStatus::PARAF_KABAG)
                ->where('id_instansi_pemohon', $instansi_id)->countAllResults(false),
            'menunggu_asisten' => $this->where('id_status_ajuan', HarmonisasiStatus::PARAF_ASISTEN)
                ->where('id_instansi_pemohon', $instansi_id)->countAllResults(false),
            'menunggu_sekda' => $this->where('id_status_ajuan', HarmonisasiStatus::PARAF_SEKDA)
                ->where('id_instansi_pemohon', $instansi_id)->countAllResults(false),
            'menunggu_wawako' => $this->where('id_status_ajuan', HarmonisasiStatus::PARAF_WAWAKO)
                ->where('id_instansi_pemohon', $instansi_id)->countAllResults(false),
            'menunggu_walikota' => $this->where('id_status_ajuan', HarmonisasiStatus::TTE_WALIKOTA)
                ->where('id_instansi_pemohon', $instansi_id)->countAllResults(false)
        ];

        return $stats;
    }

    /**
     * Mengambil histori paraf untuk ajuan tertentu
     */
    public function getHistoriParaf($ajuan_id)
    {
        return $this->db->table('harmonisasi_histori hh')
            ->select([
                'hh.*',
                'u.nama as nama_user',
                'hs_sebelum.nama_status as status_sebelumnya',
                'hs_sekarang.nama_status as status_sekarang'
            ])
            ->join('user u', 'u.id_user = hh.id_user_aksi')
            ->join('harmonisasi_status hs_sebelum', 'hs_sebelum.id = hh.id_status_sebelumnya')
            ->join('harmonisasi_status hs_sekarang', 'hs_sekarang.id = hh.id_status_sekarang')
            ->where('hh.id_ajuan', $ajuan_id)
            ->whereIn('hh.id_status_sekarang', [
                HarmonisasiStatus::PARAF_OPD,
                HarmonisasiStatus::PARAF_KABAG,
                HarmonisasiStatus::PARAF_ASISTEN,
                HarmonisasiStatus::PARAF_SEKDA,
                HarmonisasiStatus::PARAF_WAWAKO,
                HarmonisasiStatus::TTE_WALIKOTA
            ])
            ->orderBy('hh.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}
