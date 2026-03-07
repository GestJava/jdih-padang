<?php

/**
 * Peraturan Controller - Versi dengan JSON Metadata
 * Sesuai Permenkumham No. 8 Tahun 2019
 */

namespace App\Controllers;

use App\Models\WebPeraturanModel;

class Peraturan extends BaseController
{
    protected $peraturanModel;

    public function __construct()
    {
        $this->peraturanModel = new WebPeraturanModel();
    }

    public function detail($slug = null)
    {
        if (!$slug) {
            return redirect()->to('/peraturan');
        }

        // Query dengan JSON metadata
        $peraturan = $this->peraturanModel->getPeraturanDetailBySlug($slug);

        if (!$peraturan) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Peraturan tidak ditemukan');
        }



        // Increment hits
        $this->peraturanModel->incrementHits($peraturan['id_peraturan']);

        // Get related peraturan menggunakan method yang sudah ada
        $relatedPeraturan = $this->peraturanModel->getRelasiPeraturan($peraturan['id_peraturan']);

        // Get peraturan populer menggunakan method yang sudah ada
        $peraturan_populer = $this->peraturanModel->getPopularPeraturan(5);

        // Get lampiran jika ada
        $lampiran = $this->peraturanModel->getPeraturanLampiran($peraturan['id_peraturan']);

        // Get tag jika ada
        $tag = $this->peraturanModel->getPeraturanTags($peraturan['id_peraturan']);

        // Get metadata labels berdasarkan kategori
        $metadata_labels = $this->getMetadataLabels($peraturan['nama_jenis'] ?? 'Produk Hukum');

        $data = [
            'title' => $peraturan['judul'] . ' - JDIH Kota Padang',
            'peraturan' => $peraturan,
            'relatedPeraturan' => $relatedPeraturan,
            'peraturan_populer' => $peraturan_populer,
            'lampiran' => $lampiran,
            'tag' => $tag,
            'metadata' => $this->getMetadataForDisplay($peraturan),
            'metadata_labels' => $metadata_labels
        ];

        return view('frontend/pages/peraturan-detail', $data);
    }

    /**
     * Detail peraturan berdasarkan ID
     */
    public function detailById($id = null)
    {
        if (!$id) {
            return redirect()->to('/peraturan');
        }

        // Query berdasarkan ID
        $peraturan = $this->peraturanModel->getPeraturanDetail($id);

        if (!$peraturan) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Peraturan tidak ditemukan');
        }

        // Increment hits
        $this->peraturanModel->incrementHits($peraturan['id_peraturan']);

        // Get related peraturan menggunakan method yang sudah ada
        $relatedPeraturan = $this->peraturanModel->getRelasiPeraturan($peraturan['id_peraturan']);

        // Get peraturan populer menggunakan method yang sudah ada
        $peraturan_populer = $this->peraturanModel->getPopularPeraturan(5);

        // Get lampiran jika ada
        $lampiran = $this->peraturanModel->getPeraturanLampiran($peraturan['id_peraturan']);

        // Get tag jika ada
        $tag = $this->peraturanModel->getPeraturanTags($peraturan['id_peraturan']);

        // Get metadata labels berdasarkan kategori
        $metadata_labels = $this->getMetadataLabels($peraturan['nama_jenis'] ?? 'Produk Hukum');

        $data = [
            'title' => $peraturan['judul'] . ' - JDIH Kota Padang',
            'peraturan' => $peraturan,
            'relatedPeraturan' => $relatedPeraturan,
            'peraturan_populer' => $peraturan_populer,
            'lampiran' => $lampiran,
            'tag' => $tag,
            'metadata' => $this->getMetadataForDisplay($peraturan),
            'metadata_labels' => $metadata_labels
        ];

        return view('frontend/pages/peraturan-detail', $data);
    }

    /**
     * Get metadata untuk display berdasarkan kategori
     */
    private function getMetadataForDisplay($peraturan)
    {
        $kategori = $peraturan['nama_jenis'] ?? 'Produk Hukum';

        // Gunakan helper untuk mendapatkan metadata
        helper('metadata');
        return get_metadata_by_kategori($peraturan, $kategori);
    }

    /**
     * Get judul metadata yang sesuai berdasarkan kategori dokumen
     */
    private function getMetadataLabels($kategori)
    {
        helper('metadata');
        return get_metadata_labels_by_kategori($kategori);
    }

    /**
     * API untuk update metadata JSON
     */
    public function updateMetadata()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $id_peraturan = $this->request->getPost('id_peraturan');
        $metadata_key = $this->request->getPost('metadata_key');
        $metadata_value = $this->request->getPost('metadata_value');

        if (!$id_peraturan || !$metadata_key) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing required parameters']);
        }

        try {
            // Get current metadata
            $peraturan = $this->peraturanModel->find($id_peraturan);
            if (!$peraturan) {
                return $this->response->setJSON(['success' => false, 'message' => 'Peraturan tidak ditemukan']);
            }

            $current_metadata = $peraturan['metadata_json'] ?? '{}';
            $metadata = json_decode($current_metadata, true) ?: [];

            // Update metadata
            $metadata[$metadata_key] = $metadata_value;
            $new_metadata_json = json_encode($metadata, JSON_UNESCAPED_UNICODE);

            // Update database
            $this->peraturanModel->update($id_peraturan, [
                'metadata_json' => $new_metadata_json
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Metadata berhasil diupdate',
                'metadata' => $metadata
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API untuk get metadata JSON
     */
    public function getMetadata($id_peraturan)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $peraturan = $this->peraturanModel->find($id_peraturan);
            if (!$peraturan) {
                return $this->response->setJSON(['success' => false, 'message' => 'Peraturan tidak ditemukan']);
            }

            $metadata = json_decode($peraturan['metadata_json'] ?? '{}', true) ?: [];

            return $this->response->setJSON([
                'success' => true,
                'metadata' => $metadata,
                'kategori' => $peraturan['nama_jenis'] ?? 'Produk Hukum'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Bulk update metadata untuk kategori tertentu
     */
    public function bulkUpdateMetadata()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $kategori = $this->request->getPost('kategori');
        $metadata_updates = $this->request->getPost('metadata_updates');

        if (!$kategori || !$metadata_updates) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing required parameters']);
        }

        try {
            $updated_count = 0;

            foreach ($metadata_updates as $update) {
                $id_peraturan = $update['id_peraturan'];
                $metadata_key = $update['key'];
                $metadata_value = $update['value'];

                // Get current metadata
                $peraturan = $this->peraturanModel->find($id_peraturan);
                if (!$peraturan) continue;

                $current_metadata = $peraturan['metadata_json'] ?? '{}';
                $metadata = json_decode($current_metadata, true) ?: [];

                // Update metadata
                $metadata[$metadata_key] = $metadata_value;
                $new_metadata_json = json_encode($metadata, JSON_UNESCAPED_UNICODE);

                // Update database
                $this->peraturanModel->update($id_peraturan, [
                    'metadata_json' => $new_metadata_json
                ]);

                $updated_count++;
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Berhasil update {$updated_count} peraturan",
                'updated_count' => $updated_count
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Search peraturan dengan metadata JSON
     */
    public function searchWithMetadata()
    {
        $keyword = $this->request->getGet('keyword');
        $kategori = $this->request->getGet('kategori');
        $metadata_filter = $this->request->getGet('metadata_filter');

        $peraturan_list = $this->peraturanModel->searchWithMetadata(
            $keyword,
            $kategori,
            $metadata_filter
        );

        return $this->response->setJSON([
            'success' => true,
            'data' => $peraturan_list,
            'total' => count($peraturan_list)
        ]);
    }

    /**
     * Download dokumen peraturan
     */
    public function download($id)
    {
        // Validasi input
        $id = (int)$id;
        if ($id <= 0) {
            return redirect()->back()->with('error', 'ID peraturan tidak valid');
        }

        $peraturan = $this->peraturanModel->find($id);

        if (!$peraturan || !$peraturan['file_dokumen']) {
            log_message('error', 'PDF file not found for download - peraturan ID: ' . $id . ', file: ' . ($peraturan['file_dokumen'] ?? 'NULL'));
            return redirect()->back()->with('error', 'File dokumen tidak tersedia');
        }

        // Sanitasi nama file - izinkan karakter umum tapi blokir path traversal
        $filename = $peraturan['file_dokumen'];
        // Blokir: path traversal (..), null byte, dan karakter berbahaya
        if (preg_match('/(\.\.\/|\.\.\ |\x00|[<>:"|?*])/', $filename)) {
            log_message('error', 'Dangerous filename detected: ' . $filename);
            return redirect()->back()->with('error', 'Nama file tidak valid');
        }

        // Tambah counter download
        try {
            $this->peraturanModel->incrementDownloads($id);
        } catch (\Exception $e) {
            log_message('error', 'Failed to increment downloads: ' . $e->getMessage());
        }

        // Coba berbagai path untuk menemukan file PDF dengan validasi keamanan
        $possible_paths = [
            FCPATH . 'uploads/peraturan/' . $filename,
            ROOTPATH . '../uploads/peraturan/' . $filename,
            dirname(FCPATH) . '/uploads/peraturan/' . $filename,
            APPPATH . '../uploads/peraturan/' . $filename
        ];

        $file_path = null;
        foreach ($possible_paths as $path) {
            // Validasi path untuk mencegah directory traversal
            $real_path = realpath($path);
            if ($real_path && file_exists($real_path) && is_file($real_path)) {
                // Pastikan file berada dalam direktori uploads yang diizinkan
                if (strpos($real_path, 'uploads') !== false && strpos($real_path, 'peraturan') !== false) {
                    $file_path = $real_path;
                    break;
                }
            }
        }

        if (!$file_path) {
            log_message('error', 'PDF file not found for download - peraturan ID: ' . $id . ', file: ' . $filename);
            return redirect()->back()->with('error', 'File dokumen tidak ditemukan di server');
        }

        // Validasi tipe file
        $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            log_message('error', 'Invalid file type: ' . $file_extension . ' for file: ' . $filename);
            return redirect()->back()->with('error', 'Tipe file tidak diizinkan');
        }

        return $this->response->download($file_path, null);
    }

    /**
     * Preview dokumen peraturan untuk iframe - Versi Final
     */
    public function preview($id)
    {
        // Validasi input
        $id = (int)$id;
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setBody('ID peraturan tidak valid');
        }

        $peraturan = $this->peraturanModel->find($id);
        if (!$peraturan || !$peraturan['file_dokumen']) {
            log_message('error', 'PDF file not found for preview - peraturan ID: ' . $id . ', file: ' . ($peraturan['file_dokumen'] ?? 'NULL'));
            return $this->response->setStatusCode(404)->setBody('File dokumen tidak tersedia');
        }

        // Sanitasi nama file - izinkan karakter umum tapi blokir path traversal
        $filename = $peraturan['file_dokumen'];
        // Blokir: path traversal (..), null byte, dan karakter berbahaya
        if (preg_match('/(\.\.\/|\.\.\ |\x00|[<>:"|?*])/', $filename)) {
            log_message('error', 'Dangerous filename detected: ' . $filename);
            return $this->response->setStatusCode(400)->setBody('Nama file tidak valid');
        }

        // Coba berbagai path untuk menemukan file PDF dengan validasi keamanan
        $possible_paths = [
            FCPATH . 'uploads/peraturan/' . $filename,
            ROOTPATH . '../uploads/peraturan/' . $filename,
            dirname(FCPATH) . '/uploads/peraturan/' . $filename,
            APPPATH . '../uploads/peraturan/' . $filename
        ];

        $file_path = null;
        foreach ($possible_paths as $path) {
            // Validasi path untuk mencegah directory traversal
            $real_path = realpath($path);
            if ($real_path && file_exists($real_path) && is_file($real_path)) {
                // Pastikan file berada dalam direktori uploads yang diizinkan
                if (strpos($real_path, 'uploads') !== false && strpos($real_path, 'peraturan') !== false) {
                    $file_path = $real_path;
                    break;
                }
            }
        }

        if (!$file_path) {
            log_message('error', 'PDF file not found for preview - peraturan ID: ' . $id . ', file: ' . $filename);
            return $this->response->setStatusCode(404)->setBody('File dokumen tidak ditemukan di server');
        }

        // Validasi tipe file untuk preview (hanya PDF)
        $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        if ($file_extension !== 'pdf') {
            return $this->response->setStatusCode(400)->setBody('Preview hanya tersedia untuk file PDF');
        }

        // Bersihkan output buffer sebelum kirim header/file
        if (ob_get_length()) ob_end_clean();

        // Set headers untuk PDF preview yang kompatibel dengan iframe
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));
        // Hapus/override X-Frame-Options agar PDF bisa di-embed di iframe
        if (function_exists('header_remove')) header_remove('X-Frame-Options');
        header('Cache-Control: public, max-age=3600');

        // Tambahan header untuk kompatibilitas browser
        header('Accept-Ranges: bytes');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

        readfile($file_path);
        exit;
    }

    /**
     * Download lampiran peraturan
     */
    public function download_lampiran($id_lampiran)
    {
        // Load WebLampiranModel
        $lampiranModel = new \App\Models\WebLampiranModel();
        $lampiran = $lampiranModel->find($id_lampiran);

        if (!$lampiran || empty($lampiran['file_lampiran'])) {
            return redirect()->back()->with('error', 'File lampiran tidak ditemukan atau rusak.');
        }

        // Coba berbagai path untuk menemukan file lampiran (sama seperti method download)
        $possible_paths = [
            FCPATH . 'uploads/lampiran/' . $lampiran['file_lampiran'],
            ROOTPATH . '../uploads/lampiran/' . $lampiran['file_lampiran'],
            dirname(FCPATH) . '/uploads/lampiran/' . $lampiran['file_lampiran'],
            APPPATH . '../uploads/lampiran/' . $lampiran['file_lampiran']
        ];

        $file_path = null;
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                $file_path = $path;
                break;
            }
        }

        if (!$file_path) {
            log_message('error', 'Lampiran file not found for download - lampiran ID: ' . $id_lampiran . ', file: ' . $lampiran['file_lampiran']);
            return redirect()->back()->with('error', 'File fisik lampiran tidak ditemukan di server.');
        }

        // Increment download counter
        $lampiranModel->incrementDownload($id_lampiran);

        // Menggunakan helper download bawaan CodeIgniter
        return $this->response->download($file_path, null);
    }

    /**
     * Debug: List all peraturan with file_dokumen (PDF)
     */
    public function debugListPdf()
    {
        $list = $this->peraturanModel->where('file_dokumen IS NOT NULL')->where('file_dokumen !=', '')->findAll();
        echo "<h2>Daftar Peraturan dengan File PDF</h2>";
        echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Judul</th><th>File PDF</th><th>Preview Link</th><th>Direct Link</th></tr>";
        foreach ($list as $row) {
            $id = $row['id_peraturan'];
            $judul = htmlspecialchars($row['judul']);
            $file = htmlspecialchars($row['file_dokumen']);
            $preview = base_url('peraturan/preview/' . $id);
            $direct = base_url('uploads/peraturan/' . $file);
            echo "<tr><td>$id</td><td>$judul</td><td>$file</td><td><a href='$preview' target='_blank'>Preview</a></td><td><a href='$direct' target='_blank'>Direct</a></td></tr>";
        }
        echo "</table>";
        exit;
    }
}
