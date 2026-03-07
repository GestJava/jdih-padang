<?php

/**
 * Peraturan Model - Versi dengan JSON Metadata
 * Sesuai Permenkumham No. 8 Tahun 2019
 */

namespace App\Models;

use CodeIgniter\Model;

class PeraturanModel extends Model
{
    protected $table = 'web_peraturan';
    protected $primaryKey = 'id_peraturan';
    protected $allowedFields = [
        'id_lama',
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
        'metadata_json',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get peraturan dengan metadata JSON dan join data
     */
    public function getPeraturanWithMetadata($slug)
    {
        $builder = $this->db->table('web_peraturan wp');
        $builder->select('
            wp.*,
            wjd.nama_jenis as jenis_peraturan,
            wjd.kategori_slug as kategori_slug,
            instansi.nama_instansi,
            status_dokumen.nama_status
        ');
        $builder->join('web_jenis_peraturan wjd', 'wp.id_jenis_dokumen = wjd.id_jenis_peraturan', 'left');
        $builder->join('instansi', 'wp.id_instansi = instansi.id', 'left');
        $builder->join('status_dokumen', 'wp.id_status = status_dokumen.id', 'left');
        $builder->where('wp.slug', $slug);
        $builder->where('wp.is_published', 1);

        $result = $builder->get()->getRowArray();

        if ($result) {
            // Decode JSON metadata jika ada
            if (!empty($result['metadata_json'])) {
                $result['metadata_decoded'] = json_decode($result['metadata_json'], true);
            }
        }

        return $result;
    }

    /**
     * Search peraturan dengan metadata JSON
     */
    public function searchWithMetadata($keyword = '', $kategori = '', $metadata_filter = [])
    {
        $builder = $this->db->table('web_peraturan wp');
        $builder->select('
            wp.*,
            wjd.nama_jenis as jenis_peraturan,
            wjd.kategori_slug as kategori_slug,
            instansi.nama_instansi,
            status_dokumen.nama_status
        ');
        $builder->join('web_jenis_peraturan wjd', 'wp.id_jenis_dokumen = wjd.id_jenis_peraturan', 'left');
        $builder->join('instansi', 'wp.id_instansi = instansi.id', 'left');
        $builder->join('status_dokumen', 'wp.id_status = status_dokumen.id', 'left');
        $builder->where('wp.is_published', 1);

        // Keyword search
        if (!empty($keyword)) {
            $builder->groupStart();
            $builder->like('wp.judul', $keyword);
            $builder->orLike('wp.nomor', $keyword);
            $builder->orLike('wp.abstrak_teks', $keyword);
            $builder->orLike('wp.penandatangan', $keyword);
            // Search dalam JSON metadata
            $builder->orLike('wp.metadata_json', $keyword);
            $builder->groupEnd();
        }

        // Kategori filter
        if (!empty($kategori)) {
            $builder->where('wjd.nama_jenis', $kategori);
        }

        // Metadata filter
        if (!empty($metadata_filter) && is_array($metadata_filter)) {
            foreach ($metadata_filter as $key => $value) {
                if (!empty($value)) {
                    // Search dalam JSON metadata menggunakan JSON_EXTRACT
                    $builder->where("JSON_UNQUOTE(JSON_EXTRACT(wp.metadata_json, '$.{$key}')) LIKE", "%{$value}%");
                }
            }
        }

        $builder->orderBy('wp.created_at', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get peraturan berdasarkan kategori dengan metadata
     */
    public function getPeraturanByKategori($kategori, $limit = 10, $offset = 0)
    {
        $builder = $this->db->table('web_peraturan wp');
        $builder->select('
            wp.*,
            wjd.nama_jenis as jenis_peraturan,
            wjd.kategori_slug as kategori_slug,
            instansi.nama_instansi,
            status_dokumen.nama_status
        ');
        $builder->join('web_jenis_peraturan wjd', 'wp.id_jenis_dokumen = wjd.id_jenis_peraturan', 'left');
        $builder->join('instansi', 'wp.id_instansi = instansi.id', 'left');
        $builder->join('status_dokumen', 'wp.id_status = status_dokumen.id', 'left');
        $builder->where('wjd.nama_jenis', $kategori);
        $builder->where('wp.is_published', 1);
        $builder->orderBy('wp.created_at', 'DESC');
        $builder->limit($limit, $offset);

        return $builder->get()->getResultArray();
    }

    /**
     * Get metadata untuk peraturan tertentu
     */
    public function getMetadata($id_peraturan)
    {
        $peraturan = $this->find($id_peraturan);
        if (!$peraturan) {
            return null;
        }

        $metadata = json_decode($peraturan['metadata_json'] ?? '{}', true);
        return $metadata ?: [];
    }

    /**
     * Update metadata untuk peraturan tertentu
     */
    public function updateMetadata($id_peraturan, $metadata_updates)
    {
        $peraturan = $this->find($id_peraturan);
        if (!$peraturan) {
            return false;
        }

        $current_metadata = json_decode($peraturan['metadata_json'] ?? '{}', true) ?: [];
        $new_metadata = array_merge($current_metadata, $metadata_updates);

        return $this->update($id_peraturan, [
            'metadata_json' => json_encode($new_metadata, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * Bulk update metadata untuk kategori tertentu
     */
    public function bulkUpdateMetadataByKategori($kategori, $metadata_updates)
    {
        // Get all peraturan dengan kategori tertentu
        $builder = $this->db->table('web_peraturan wp');
        $builder->join('web_jenis_peraturan wjd', 'wp.id_jenis_dokumen = wjd.id_jenis_peraturan', 'left');
        $builder->where('wjd.nama_jenis', $kategori);

        $peraturan_list = $builder->get()->getResultArray();

        $updated_count = 0;
        foreach ($peraturan_list as $peraturan) {
            $current_metadata = json_decode($peraturan['metadata_json'] ?? '{}', true) ?: [];
            $new_metadata = array_merge($current_metadata, $metadata_updates);

            if ($this->update($peraturan['id_peraturan'], [
                'metadata_json' => json_encode($new_metadata, JSON_UNESCAPED_UNICODE)
            ])) {
                $updated_count++;
            }
        }

        return $updated_count;
    }

    /**
     * Get statistics metadata usage
     */
    public function getMetadataStatistics()
    {
        $builder = $this->db->table('web_peraturan');
        $builder->select('
            COUNT(*) as total_peraturan,
            COUNT(CASE WHEN metadata_json IS NOT NULL AND metadata_json != "{}" THEN 1 END) as total_with_metadata,
            COUNT(CASE WHEN metadata_json IS NULL OR metadata_json = "{}" THEN 1 END) as total_without_metadata
        ');

        $result = $builder->get()->getRowArray();

        // Get breakdown by kategori
        $builder2 = $this->db->table('web_peraturan wp');
        $builder2->select('
            wjd.nama_jenis as kategori,
            COUNT(*) as total,
            COUNT(CASE WHEN wp.metadata_json IS NOT NULL AND wp.metadata_json != "{}" THEN 1 END) as with_metadata
        ');
        $builder2->join('web_jenis_peraturan wjd', 'wp.id_jenis_dokumen = wjd.id_jenis_peraturan', 'left');
        $builder2->groupBy('wjd.nama_jenis');
        $builder2->orderBy('total', 'DESC');

        $result['breakdown'] = $builder2->get()->getResultArray();

        return $result;
    }

    /**
     * Get related peraturan berdasarkan metadata
     */
    public function getRelatedPeraturanByMetadata($id_peraturan, $limit = 5)
    {
        $peraturan = $this->find($id_peraturan);
        if (!$peraturan || empty($peraturan['metadata_json'])) {
            return $this->getRelatedPeraturan($id_peraturan, $peraturan['id_jenis_dokumen'] ?? 0, $limit);
        }

        $metadata = json_decode($peraturan['metadata_json'], true);
        if (!$metadata) {
            return $this->getRelatedPeraturan($id_peraturan, $peraturan['id_jenis_dokumen'] ?? 0, $limit);
        }

        $builder = $this->db->table('web_peraturan wp');
        $builder->select('
            wp.*,
            wjd.nama_jenis as jenis_peraturan,
            wjd.kategori_slug as kategori_slug,
            instansi.nama_instansi,
            status_dokumen.nama_status
        ');
        $builder->join('web_jenis_peraturan wjd', 'wp.id_jenis_dokumen = wjd.id_jenis_peraturan', 'left');
        $builder->join('instansi', 'wp.id_instansi = instansi.id', 'left');
        $builder->join('status_dokumen', 'wp.id_status = status_dokumen.id', 'left');
        $builder->where('wp.id_peraturan !=', $id_peraturan);
        $builder->where('wp.is_published', 1);

        // Search berdasarkan metadata yang sama
        $builder->groupStart();
        foreach ($metadata as $key => $value) {
            if (!empty($value)) {
                $builder->orWhere("JSON_UNQUOTE(JSON_EXTRACT(wp.metadata_json, '$.{$key}'))", $value);
            }
        }
        $builder->groupEnd();

        $builder->orderBy('wp.created_at', 'DESC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }

    /**
     * Increment hits
     */
    public function incrementHits($id_peraturan)
    {
        return $this->db->table('web_peraturan')
            ->where('id_peraturan', $id_peraturan)
            ->set('hits', 'hits + 1', false)
            ->update();
    }

    /**
     * Get related peraturan (existing method)
     */
    public function getRelatedPeraturan($id_peraturan, $id_jenis_dokumen, $limit = 5)
    {
        $builder = $this->db->table('web_peraturan wp');
        $builder->select('
            wp.*,
            wjd.nama_jenis as jenis_peraturan,
            wjd.kategori_slug as kategori_slug,
            instansi.nama_instansi,
            status_dokumen.nama_status
        ');
        $builder->join('web_jenis_peraturan wjd', 'wp.id_jenis_dokumen = wjd.id_jenis_peraturan', 'left');
        $builder->join('instansi', 'wp.id_instansi = instansi.id', 'left');
        $builder->join('status_dokumen', 'wp.id_status = status_dokumen.id', 'left');
        $builder->where('wp.id_peraturan !=', $id_peraturan);
        $builder->where('wp.id_jenis_dokumen', $id_jenis_dokumen);
        $builder->where('wp.is_published', 1);
        $builder->orderBy('wp.created_at', 'DESC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }
}
