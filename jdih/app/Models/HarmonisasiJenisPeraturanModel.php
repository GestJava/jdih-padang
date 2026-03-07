<?php

namespace App\Models;

use CodeIgniter\Model;

class HarmonisasiJenisPeraturanModel extends Model
{
    protected $table            = 'harmonisasi_jenis_peraturan';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['nama_jenis'];
}
