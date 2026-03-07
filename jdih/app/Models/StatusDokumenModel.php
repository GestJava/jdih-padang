<?php

namespace App\Models;

use CodeIgniter\Model;

class StatusDokumenModel extends Model
{
    protected $table            = 'status_dokumen';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['nama_status'];
}
