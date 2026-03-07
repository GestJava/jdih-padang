<?php

namespace App\Services;

use App\Models\HarmonisasiAjuanModel;
use App\Models\HarmonisasiDokumenModel;
use App\Models\HarmonisasiHistoriModel;
use App\Models\HarmonisasiTteLogModel;
use App\Services\DocumentSigningService;
use App\Services\LegalisasiNumberingService;
use App\Config\HarmonisasiStatus;

class LegalisasiTTEService
{
    protected $harmonisasiAjuanModel;
    protected $harmonisasiDokumenModel;
    protected $harmonisasiHistoriModel;
    protected $harmonisasiTteLogModel;
    protected $documentSigningService;
    protected $numberingService;
    protected $db;

    public function __construct()
    {
        $this->harmonisasiAjuanModel = new HarmonisasiAjuanModel();
        $this->harmonisasiDokumenModel = new HarmonisasiDokumenModel();
        $this->harmonisasiHistoriModel = new HarmonisasiHistoriModel();
        $this->harmonisasiTteLogModel = new HarmonisasiTteLogModel();
        $this->documentSigningService = new DocumentSigningService();
        $this->numberingService = new LegalisasiNumberingService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Process TTE lengkap dengan BSRE integration dan independent numbering
     */
    public function processTTEWithBSRE($ajuan_id, $user_id, $nik, $passphrase, $options = [])
    {
        $this->db->transStart();

        try {
            // 1. VALIDATE AJUAN DAN JENIS
            $ajuan = $this->harmonisasiAjuanModel->find($ajuan_id);
            if (!$ajuan) {
                throw new \Exception('Ajuan tidak ditemukan');
            }

            $jenis = $this->getJenisPeraturan($ajuan['id_jenis_peraturan']);
            if (!$jenis) {
                throw new \Exception('Jenis peraturan tidak ditemukan');
            }

            // 2. VALIDATE TTE AUTHORITY & STATUS
            $this->validateTTEReadiness($ajuan, $jenis, $user_id);

            // 3. GENERATE NOMOR BERDIRI SENDIRI UNTUK JENIS INI
            $numbering_result = $this->numberingService->generateNomorForTTE($ajuan_id, $jenis['nama_jenis']);

            if (!$numbering_result['success']) {
                throw new \Exception('Gagal generate nomor: ' . $numbering_result['error']);
            }

            log_message('info', "LEGALISASI TTE: Generated number for {$jenis['nama_jenis']}: {$numbering_result['nomor_lengkap']}");

            // 4. GET LATEST DOCUMENT
            $latest_doc_path = $this->getLatestDocumentPath($ajuan_id, 'dokumen_final');

            if (!$latest_doc_path || !file_exists($latest_doc_path)) {
                throw new \Exception('Dokumen final tidak ditemukan: ' . ($latest_doc_path ?? 'null'));
            }

            // 5. PROCESS TTE DENGAN BSRE (sudah include numbering)
            $tte_result = $this->documentSigningService->signDocument(
                $latest_doc_path,
                $this->numberingService->getDocumentTypeForBSRE($jenis['nama_jenis']),
                $nik,
                $passphrase,
                [
                    'document_number' => $numbering_result['nomor_lengkap'],
                    'nomor_urut' => $numbering_result['urutan'],
                    'tahun' => $numbering_result['tahun'],
                    'jenis_peraturan' => $jenis['nama_jenis'],
                    'authority' => $numbering_result['authority'],
                    'ajuan_id' => $ajuan_id
                ]
            );

            if (!$tte_result['success']) {
                throw new \Exception('TTE BSRE gagal: ' . ($tte_result['message'] ?? 'Unknown error'));
            }

            // 6. UPDATE HARMONISASI_AJUAN
            $next_status = $this->getPostTTEStatus($jenis['nama_jenis']);

            $update_data = [
                'nomor_peraturan' => $numbering_result['nomor_lengkap'],
                'tahun_peraturan' => $numbering_result['tahun'],
                'nomor_urut_jenis' => $numbering_result['urutan'],
                'tanggal_penetapan' => date('Y-m-d'),
                'numbered_document_path' => $tte_result['processed_path'] ?? $latest_doc_path,
                'tte_file_path' => $tte_result['signed_path'],
                'tte_signed_at' => date('Y-m-d H:i:s'),
                'tte_signed_by' => $user_id,
                'tte_nik' => $nik,
                'document_hash' => hash_file('sha256', $tte_result['signed_path']),
                'id_status_ajuan' => $next_status
            ];

            // Add authority-specific fields
            if ($numbering_result['authority'] === 'sekda') {
                $update_data['paraf_sekda_by'] = $user_id;
                $update_data['paraf_sekda_at'] = date('Y-m-d H:i:s');
            } else {
                // Walikota TTE - update all previous paraf data if not set
                if (empty($ajuan['paraf_wawako_by'])) {
                    $update_data['paraf_wawako_by'] = $user_id;
                    $update_data['paraf_wawako_at'] = date('Y-m-d H:i:s');
                }
            }

            $this->harmonisasiAjuanModel->update($ajuan_id, $update_data);

            // 7. SAVE DOCUMENT RECORDS
            $this->saveDocumentRecords($ajuan_id, $user_id, $numbering_result, $tte_result);

            // 8. LOG TTE ACTIVITY
            $this->logTTEActivity($ajuan_id, $user_id, $numbering_result, $tte_result);

            // 9. LOG HISTORY
            $this->logStatusHistory($ajuan_id, $user_id, $ajuan['id_status_ajuan'], $next_status, $numbering_result);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Database transaction failed');
            }

            log_message('info', "LEGALISASI TTE SUCCESS: Ajuan {$ajuan_id} - {$numbering_result['nomor_lengkap']}");

            return [
                'success' => true,
                'nomor_peraturan' => $numbering_result['nomor_lengkap'],
                'urutan_dalam_jenis' => $numbering_result['urutan'],
                'jenis_peraturan' => $jenis['nama_jenis'],
                'authority' => $numbering_result['authority'],
                'tte_file_path' => $tte_result['signed_path'],
                'document_hash' => hash_file('sha256', $tte_result['signed_path']),
                'message' => "TTE berhasil! Dokumen {$jenis['nama_jenis']} telah ditandatangani dengan nomor: {$numbering_result['nomor_lengkap']} (Urutan ke-{$numbering_result['urutan']} untuk jenis ini di tahun {$numbering_result['tahun']})"
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();

            // Cleanup temporary files
            if (isset($tte_result['processed_path']) && file_exists($tte_result['processed_path'])) {
                unlink($tte_result['processed_path']);
            }

            log_message('error', "LEGALISASI TTE ERROR: Ajuan {$ajuan_id} - " . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Process paraf (non-TTE) untuk intermediate steps
     */
    public function processParaf($ajuan_id, $user_id, $paraf_type)
    {
        try {
            $ajuan = $this->harmonisasiAjuanModel->find($ajuan_id);
            if (!$ajuan) {
                throw new \Exception('Ajuan tidak ditemukan');
            }

            // Get next status berdasarkan paraf type
            $next_status = $this->getNextStatusAfterParaf($ajuan['id_status_ajuan'], $paraf_type);

            // Update paraf data
            $update_data = [
                'id_status_ajuan' => $next_status
            ];

            switch ($paraf_type) {
                case 'opd':
                    $update_data['paraf_opd_by'] = $user_id;
                    $update_data['paraf_opd_at'] = date('Y-m-d H:i:s');
                    break;
                case 'kabag':
                    $update_data['paraf_kabag_by'] = $user_id;
                    $update_data['paraf_kabag_at'] = date('Y-m-d H:i:s');
                    break;
                case 'asisten':
                    $update_data['paraf_asisten_by'] = $user_id;
                    $update_data['paraf_asisten_at'] = date('Y-m-d H:i:s');
                    break;
                case 'sekda':
                    $update_data['paraf_sekda_by'] = $user_id;
                    $update_data['paraf_sekda_at'] = date('Y-m-d H:i:s');
                    break;
                case 'wawako':
                    $update_data['paraf_wawako_by'] = $user_id;
                    $update_data['paraf_wawako_at'] = date('Y-m-d H:i:s');
                    break;
            }

            $this->harmonisasiAjuanModel->update($ajuan_id, $update_data);

            // Log history
            $this->harmonisasiHistoriModel->insert([
                'id_ajuan' => $ajuan_id,
                'id_user_aksi' => $user_id,
                'id_status_sebelumnya' => $ajuan['id_status_ajuan'],
                'id_status_sekarang' => $next_status,
                'keterangan' => "Paraf {$paraf_type} diberikan"
            ]);

            return [
                'success' => true,
                'message' => "Paraf {$paraf_type} berhasil diberikan",
                'next_status' => $next_status
            ];
        } catch (\Exception $e) {
            log_message('error', "Paraf Processing Error: " . $e->getMessage());
            throw $e;
        }
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    private function validateTTEReadiness($ajuan, $jenis, $user_id)
    {
        // Check status
        $expected_status = $this->numberingService->getExpectedTTEStatus($jenis['nama_jenis']);

        if ($ajuan['id_status_ajuan'] != $expected_status) {
            throw new \Exception("Status ajuan tidak sesuai untuk TTE. Expected: {$expected_status}, Actual: {$ajuan['id_status_ajuan']}");
        }

        // Check permission
        $authority = $this->numberingService->getTTEAuthority($jenis['nama_jenis']);
        $required_permission = $authority === 'sekda' ? 'tte_sekda' : 'tte_walikota';

        // Note: Implement permission check based on your system
        // if (!$this->hasPermission($required_permission)) {
        //     throw new \Exception("Tidak memiliki permission untuk TTE {$authority}");
        // }
    }

    private function getLatestDocumentPath($ajuan_id, $tipe_dokumen)
    {
        $dokumen = $this->harmonisasiDokumenModel
            ->where('id_ajuan', $ajuan_id)
            ->where('tipe_dokumen', $tipe_dokumen)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$dokumen) {
            return null;
        }

        return WRITEPATH . 'uploads/' . $dokumen['path_file_storage'];
    }

    private function getJenisPeraturan($id_jenis)
    {
        return $this->db->table('harmonisasi_jenis_peraturan')
            ->where('id', $id_jenis)
            ->get()
            ->getRowArray();
    }

    private function getPostTTEStatus($jenis_peraturan)
    {
        // Setelah TTE (baik Sekda maupun Walikota), status menjadi "Ditetapkan"
        return 16; // Status: Ditetapkan
    }

    private function getNextStatusAfterParaf($current_status, $paraf_type)
    {
        $status_map = [
            'opd' => 8,      // Paraf OPD → Paraf Kabag
            'kabag' => 9,    // Paraf Kabag → Paraf Asisten
            'asisten' => 11, // Paraf Asisten → Paraf Sekda
            'sekda' => 12,   // Paraf Sekda → Paraf Wawako
            'wawako' => 13   // Paraf Wawako → TTE Walikota
        ];

        return $status_map[$paraf_type] ?? ($current_status + 1);
    }

    private function saveDocumentRecords($ajuan_id, $user_id, $numbering_result, $tte_result)
    {
        // Save numbered document (jika ada processed_path yang berbeda)
        if (isset($tte_result['processed_path']) && $tte_result['processed_path'] !== $tte_result['signed_path']) {
            $this->harmonisasiDokumenModel->insert([
                'id_ajuan' => $ajuan_id,
                'id_user_uploader' => $user_id,
                'tipe_dokumen' => 'dokumen_numbered',
                'nama_file_original' => "Dokumen_Bernomor_{$numbering_result['prefix']}_{$numbering_result['urutan']}_{$numbering_result['tahun']}.pdf",
                'path_file_storage' => str_replace(WRITEPATH . 'uploads/', '', $tte_result['processed_path'])
            ]);
        }

        // Save TTE document
        $this->harmonisasiDokumenModel->insert([
            'id_ajuan' => $ajuan_id,
            'id_user_uploader' => $user_id,
            'tipe_dokumen' => 'dokumen_tte',
            'nama_file_original' => "Dokumen_TTE_{$numbering_result['prefix']}_{$numbering_result['urutan']}_{$numbering_result['tahun']}.pdf",
            'path_file_storage' => str_replace(WRITEPATH . 'uploads/', '', $tte_result['signed_path'])
        ]);
    }

    private function logTTEActivity($ajuan_id, $user_id, $numbering_result, $tte_result)
    {
        $this->harmonisasiTteLogModel->insert([
            'id_ajuan' => $ajuan_id,
            'id_user_penandatangan' => $user_id,
            'jenis_aksi' => 'sign',
            'status_tte' => 'success',
            'file_signed_path' => $tte_result['signed_path'],
            'signature_info' => json_encode([
                'nomor_peraturan' => $numbering_result['nomor_lengkap'],
                'jenis_peraturan' => $numbering_result['jenis'],
                'urutan_dalam_jenis' => $numbering_result['urutan'],
                'tahun' => $numbering_result['tahun'],
                'prefix' => $numbering_result['prefix'],
                'authority_level' => $numbering_result['authority'],
                'tte_timestamp' => date('Y-m-d H:i:s'),
                'bsre_integration' => true,
                'bsre_response' => $tte_result,
                'processed_by' => 'LegalisasiTTEService'
            ])
        ]);
    }

    private function logStatusHistory($ajuan_id, $user_id, $status_before, $status_after, $numbering_result)
    {
        $this->harmonisasiHistoriModel->insert([
            'id_ajuan' => $ajuan_id,
            'id_user_aksi' => $user_id,
            'id_status_sebelumnya' => $status_before,
            'id_status_sekarang' => $status_after,
            'keterangan' => "TTE {$numbering_result['authority']} berhasil dengan nomor: {$numbering_result['nomor_lengkap']} " .
                "(Urutan ke-{$numbering_result['urutan']} untuk {$numbering_result['jenis']} tahun {$numbering_result['tahun']})"
        ]);
    }

    // ============================================================
    // QUERY METHODS FOR DASHBOARDS
    // ============================================================

    /**
     * Get ajuan yang memerlukan TTE Sekda (final authority)
     */
    public function getAjuanForSekdaTTE()
    {
        return $this->harmonisasiAjuanModel
            ->select('harmonisasi_ajuan.*, harmonisasi_jenis_peraturan.nama_jenis, instansi.nama_instansi, user.nama as nama_pemohon')
            ->join('harmonisasi_jenis_peraturan', 'harmonisasi_jenis_peraturan.id = harmonisasi_ajuan.id_jenis_peraturan')
            ->join('instansi', 'instansi.id = harmonisasi_ajuan.id_instansi_pemohon', 'left')
            ->join('user', 'user.id_user = harmonisasi_ajuan.id_user_pemohon', 'left')
            ->where('harmonisasi_ajuan.id_status_ajuan', HarmonisasiStatus::PARAF_SEKDA) // 11
            ->whereIn('harmonisasi_jenis_peraturan.nama_jenis', [
                'Keputusan Sekda',
                'Instruksi Sekda',
                'Surat Edaran Sekda'
            ])
            ->orderBy('harmonisasi_ajuan.tanggal_pengajuan', 'ASC')
            ->findAll();
    }

    /**
     * Get ajuan yang perlu paraf Sekda (intermediate, bukan TTE)
     */
    public function getAjuanForSekdaParaf()
    {
        return $this->harmonisasiAjuanModel
            ->select('harmonisasi_ajuan.*, harmonisasi_jenis_peraturan.nama_jenis, instansi.nama_instansi, user.nama as nama_pemohon')
            ->join('harmonisasi_jenis_peraturan', 'harmonisasi_jenis_peraturan.id = harmonisasi_ajuan.id_jenis_peraturan')
            ->join('instansi', 'instansi.id = harmonisasi_ajuan.id_instansi_pemohon', 'left')
            ->join('user', 'user.id_user = harmonisasi_ajuan.id_user_pemohon', 'left')
            ->where('harmonisasi_ajuan.id_status_ajuan', HarmonisasiStatus::PARAF_SEKDA) // 11
            ->whereNotIn('harmonisasi_jenis_peraturan.nama_jenis', [
                'Keputusan Sekda',
                'Instruksi Sekda',
                'Surat Edaran Sekda'
            ])
            ->orderBy('harmonisasi_ajuan.tanggal_pengajuan', 'ASC')
            ->findAll();
    }

    /**
     * Get ajuan yang memerlukan TTE Walikota (final authority)
     */
    public function getAjuanForWalikotaTTE()
    {
        return $this->harmonisasiAjuanModel
            ->select('harmonisasi_ajuan.*, harmonisasi_jenis_peraturan.nama_jenis, instansi.nama_instansi, user.nama as nama_pemohon')
            ->join('harmonisasi_jenis_peraturan', 'harmonisasi_jenis_peraturan.id = harmonisasi_ajuan.id_jenis_peraturan')
            ->join('instansi', 'instansi.id = harmonisasi_ajuan.id_instansi_pemohon', 'left')
            ->join('user', 'user.id_user = harmonisasi_ajuan.id_user_pemohon', 'left')
            ->where('harmonisasi_ajuan.id_status_ajuan', HarmonisasiStatus::TTE_WALIKOTA) // 13
            ->whereNotIn('harmonisasi_jenis_peraturan.nama_jenis', [
                'Keputusan Sekda',
                'Instruksi Sekda',
                'Surat Edaran Sekda'
            ])
            ->orderBy('harmonisasi_ajuan.tanggal_pengajuan', 'ASC')
            ->findAll();
    }

    /**
     * Get statistics untuk dashboard
     */
    public function getLegalisasiStatistics($authority = 'all')
    {
        $current_month = date('Y-m');

        $stats = [];

        if ($authority === 'sekda' || $authority === 'all') {
            $stats['tte_sekda_pending'] = $this->harmonisasiAjuanModel
                ->join('harmonisasi_jenis_peraturan', 'harmonisasi_jenis_peraturan.id = harmonisasi_ajuan.id_jenis_peraturan')
                ->where('harmonisasi_ajuan.id_status_ajuan', 11)
                ->whereIn('harmonisasi_jenis_peraturan.nama_jenis', ['Keputusan Sekda', 'Instruksi Sekda', 'Surat Edaran Sekda'])
                ->countAllResults();

            $stats['tte_sekda_bulan_ini'] = $this->harmonisasiAjuanModel
                ->join('harmonisasi_jenis_peraturan', 'harmonisasi_jenis_peraturan.id = harmonisasi_ajuan.id_jenis_peraturan')
                ->where('DATE_FORMAT(harmonisasi_ajuan.tte_signed_at, "%Y-%m")', $current_month)
                ->whereIn('harmonisasi_jenis_peraturan.nama_jenis', ['Keputusan Sekda', 'Instruksi Sekda', 'Surat Edaran Sekda'])
                ->countAllResults();
        }

        if ($authority === 'walikota' || $authority === 'all') {
            $stats['tte_walikota_pending'] = $this->harmonisasiAjuanModel
                ->join('harmonisasi_jenis_peraturan', 'harmonisasi_jenis_peraturan.id = harmonisasi_ajuan.id_jenis_peraturan')
                ->where('harmonisasi_ajuan.id_status_ajuan', 13)
                ->whereNotIn('harmonisasi_jenis_peraturan.nama_jenis', ['Keputusan Sekda', 'Instruksi Sekda', 'Surat Edaran Sekda'])
                ->countAllResults();

            $stats['tte_walikota_bulan_ini'] = $this->harmonisasiAjuanModel
                ->join('harmonisasi_jenis_peraturan', 'harmonisasi_jenis_peraturan.id = harmonisasi_ajuan.id_jenis_peraturan')
                ->where('DATE_FORMAT(harmonisasi_ajuan.tte_signed_at, "%Y-%m")', $current_month)
                ->whereNotIn('harmonisasi_jenis_peraturan.nama_jenis', ['Keputusan Sekda', 'Instruksi Sekda', 'Surat Edaran Sekda'])
                ->countAllResults();
        }

        return $stats;
    }
}
