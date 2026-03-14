<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\HarmonisasiAjuanModel;
use App\Config\HarmonisasiStatus;

/**
 * AJAX endpoint untuk DataTables server-side processing
 */
class HarmonisasiAjax extends BaseController
{
    public function __construct()
    {
        // Force session web to use existing 'harmonisasi' module so BaseController finds entry
        $session = session();
        $web = $session->get('web');
        if (!$web || ($web['nama_module'] ?? '') !== 'harmonisasi') {
            $web['nama_module'] = 'harmonisasi';
            $web['module_url'] = base_url('harmonisasi');
            $web['method_name'] = 'ajax-list';
            $session->set('web', $web);
        }

        parent::__construct();
    }

    /**
     * Server-side list
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function list(): ResponseInterface
    {
        helper('html');
        // Pastikan user login
        $user = session()->get('user');
        if (!$user || empty($user['id_user'])) {
            return $this->response->setJSON([
                'error' => 'Unauthorized'
            ])->setStatusCode(401);
        }

        $request = $this->request;

        $draw   = intval($request->getPost('draw'));   // dari DataTables
        $start  = intval($request->getPost('start'));
        $length = intval($request->getPost('length'));
        $search = $request->getPost('search')['value'] ?? '';

        // Kolom yang diperbolehkan untuk pengurutan
        $columns = [
            'ha.id',              // 0
            'ha.judul_peraturan', // 1
            'j.nama_jenis',       // 2
            'i.nama_instansi',    // 3
            'ha.tanggal_pengajuan', // 4
            's.nama_status'       // 5
        ];

        $orderColIdx = intval($request->getPost('order')[0]['column'] ?? 1);
        $orderDir    = $request->getPost('order')[0]['dir'] ?? 'asc';
        $orderCol    = $columns[$orderColIdx] ?? 'ha.tanggal_pengajuan';

        $db = db_connect();
        $builder = $db->table('harmonisasi_ajuan ha'); // set alias ha sejak awal

        // Aliases dan JOIN (mirip getBaseQueryWithJoins)
        $builder->select([
            'ha.*',
            'ha.id               AS id_ajuan',
            'u.nama              AS nama_pemohon',
            'i.nama_instansi',
            'j.nama_jenis',
            's.nama_status'
        ])
            ->join('user u', 'u.id_user = ha.id_user_pemohon', 'left')
            ->join('instansi i', 'i.id = ha.id_instansi_pemohon', 'left')
            ->join('harmonisasi_jenis_peraturan j', 'j.id = ha.id_jenis_peraturan', 'left')
            ->join('harmonisasi_status s', 's.id = ha.id_status_ajuan', 'left');

        // PERMISSION – filter berdasarkan read_own jika tidak punya read_all
        if (!$this->hasPermission('read_all') && $this->hasPermission('read_own')) {
            $builder->where('ha.id_user_pemohon', $user['id_user']);
        }

        // FILTER: Exclude status SELESAI (14) dan DITOLAK (15)
        $builder->whereNotIn('ha.id_status_ajuan', [14, 15]);

        // CUSTOM FILTERS (from AJAX data)
        $customFilters = $request->getPost('custom_filters');
        if ($customFilters) {
            if (!empty($customFilters['status'])) {
                $builder->where('ha.id_status_ajuan', $customFilters['status']);
            }
            if (!empty($customFilters['jenis'])) {
                $builder->where('ha.id_jenis_peraturan', $customFilters['jenis']);
            }
            if (!empty($customFilters['start_date'])) {
                $builder->where('ha.tanggal_pengajuan >=', $customFilters['start_date'] . ' 00:00:00');
            }
            if (!empty($customFilters['end_date'])) {
                $builder->where('ha.tanggal_pengajuan <=', $customFilters['end_date'] . ' 23:59:59');
            }
        }

        // Hitung total record (dengan base filter, tanpa search/limit)
        $recordsTotal = (clone $builder)->countAllResults(false);

        // SEARCH
        if ($search !== '') {
            $builder->groupStart()
                ->like('ha.judul_peraturan', $search)
                ->orLike('i.nama_instansi', $search)
                ->orLike('j.nama_jenis', $search)
                ->orLike('s.nama_status', $search)
                ->groupEnd();
        }

        // Hitung filtered record (setelah search) – tetap pakai builder tapi hanya COUNT(*)
        $recordsFiltered = (clone $builder)->countAllResults(false);

        // ORDER & LIMIT
        $builder->orderBy($orderCol, $orderDir)
            ->limit($length, $start);

        $query   = $builder->get();
        $results = $query->getResultArray();

        // Build JSON rows
        $data = [];
        foreach ($results as $row) {
            $tanggal = $row['tanggal_pengajuan'] && $row['tanggal_pengajuan'] != '0000-00-00 00:00:00'
                ? date('d/m/Y H:i', strtotime($row['tanggal_pengajuan']))
                : '-';

            $data[] = [
                '',                        // 0: row number (render di client)
                $row['judul_peraturan'],   // 1
                $row['nama_jenis'],        // 2
                $row['nama_instansi'],     // 3
                $tanggal,                  // 4
                $row['id_status_ajuan'],   // 5 – id status
                $row['id_ajuan']           // 6 – id ajuan (untuk action)
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
            'csrf_token'      => csrf_hash(),
        ]);
    }
}
