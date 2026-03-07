<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk tabel announcements (pengumuman/maintenance notice)
 */
class AnnouncementModel extends Model
{
    protected $table            = 'announcements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $allowedFields    = [
        'title',
        'heading',
        'message',
        'contact_name',
        'contact_position',
        'status', // active / inactive
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // === Datatables (Admin) ===
    private array $columnOrder  = [null, 'title', 'status', 'updated_at'];
    private array $columnSearch = ['title', 'message', 'contact_name'];

    private function _get_datatables_query($request)
    {
        $builder = $this->select('*');

        // Global search
        $searchValue = $request->getPost('search')['value'] ?? null;
        if ($searchValue) {
            $builder->groupStart();
            foreach ($this->columnSearch as $item) {
                $builder->orLike($item, $searchValue);
            }
            $builder->groupEnd();
        }

        // Order
        $order = $request->getPost('order');
        if ($order && isset($order[0]['column'])) {
            $colIndex = $order[0]['column'];
            $dir      = $order[0]['dir'] === 'asc' ? 'ASC' : 'DESC';
            if (isset($this->columnOrder[$colIndex]) && $this->columnOrder[$colIndex] !== null) {
                $builder->orderBy($this->columnOrder[$colIndex], $dir);
            } else {
                $builder->orderBy('updated_at', 'DESC');
            }
        } else {
            $builder->orderBy('updated_at', 'DESC');
        }

        return $builder;
    }

    public function getDatatables($request)
    {
        $builder = $this->_get_datatables_query($request);
        $length  = $request->getPost('length');
        $start   = $request->getPost('start');
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
}
