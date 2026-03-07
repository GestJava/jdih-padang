<?php

namespace App\Services;

use App\Models\NomorSequenceModel;
use App\Models\HarmonisasiAjuanModel;
use App\Models\HarmonisasiJenisPeraturanModel;

class LegalisasiNumberingService
{
    protected $nomorSequenceModel;
    protected $harmonisasiAjuanModel;
    protected $jenisPeraturanModel;
    protected $db;

    public function __construct()
    {
        $this->nomorSequenceModel = new NomorSequenceModel();
        $this->harmonisasiAjuanModel = new HarmonisasiAjuanModel();
        $this->jenisPeraturanModel = new HarmonisasiJenisPeraturanModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Generate nomor berdiri sendiri untuk jenis peraturan tertentu
     * Terintegrasi dengan BSRE TTE system
     */
    public function generateNomorForTTE($ajuan_id, $jenis_peraturan)
    {
        $tahun = date('Y');

        try {
            // 1. GET NEXT SEQUENCE (ATOMIC)
            $urutan = $this->nomorSequenceModel->getNextSequenceForJenis($jenis_peraturan, $tahun);

            // 2. LOG NUMBERING ACTIVITY
            $this->logNumberingActivity($ajuan_id, $jenis_peraturan, $urutan, $urutan);

            return [
                'success' => true,
                'urutan' => $urutan,
                'tahun' => $tahun,
                'jenis' => $jenis_peraturan
            ];
        } catch (\Exception $e) {
            log_message('error', 'Numbering Generation Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get nomor lengkap berdasarkan urutan (untuk keperluan display)
     */
    public function getNomorLengkap($jenis_peraturan, $urutan, $tahun = null)
    {
        $tahun = $tahun ?? date('Y');
        return $this->buildNomorLengkap($jenis_peraturan, $urutan, $tahun);
    }

    /**
     * Build nomor lengkap berdasarkan jenis peraturan
     */
    private function buildNomorLengkap($jenis_peraturan, $urutan, $tahun)
    {
        $templates = [
            // SEKDA AUTHORITY (TTE Final di Sekda)
            'Keputusan Sekda' => [
                'template' => '{urutan}',
                'prefix' => 'KEPDA',
                'authority' => 'sekda',
                'display' => '{urutan}'
            ],
            'Instruksi Sekda' => [
                'template' => '{urutan}',
                'prefix' => 'INSTRUKSI_SEKDA',
                'authority' => 'sekda',
                'display' => '{urutan}'
            ],
            'Surat Edaran Sekda' => [
                'template' => '{urutan}',
                'prefix' => 'SE_SEKDA',
                'authority' => 'sekda',
                'display' => '{urutan}'
            ],

            // WALIKOTA AUTHORITY (TTE Final di Walikota)
            'Peraturan Walikota' => [
                'template' => '{urutan}',
                'prefix' => 'PERWAL',
                'authority' => 'walikota',
                'display' => '{urutan}'
            ],
            'Keputusan Walikota' => [
                'template' => '{urutan}',
                'prefix' => 'KEPWAL',
                'authority' => 'walikota',
                'display' => '{urutan}'
            ],
            'Instruksi Walikota' => [
                'template' => '{urutan}',
                'prefix' => 'INSTRUKSI_WAL',
                'authority' => 'walikota',
                'display' => '{urutan}'
            ],
            'Surat Edaran Walikota' => [
                'template' => '{urutan}',
                'prefix' => 'SE_WAL',
                'authority' => 'walikota',
                'display' => '{urutan}'
            ],
            'Peraturan Daerah' => [
                'template' => '{urutan}',
                'prefix' => 'PERDA',
                'authority' => 'walikota',
                'display' => '{urutan}'
            ]
        ];

        $config = $templates[$jenis_peraturan] ?? [
            'template' => '{urutan}',
            'prefix' => 'UNKNOWN',
            'authority' => 'walikota',
            'display' => '{urutan}'
        ];

        $nomor_lengkap = str_replace(['{urutan}'], [$urutan], $config['template']);
        $format_display = str_replace(['{urutan}'], [$urutan], $config['display']);

        return [
            'nomor_lengkap' => $nomor_lengkap,
            'prefix' => $config['prefix'],
            'authority' => $config['authority'],
            'format_display' => $format_display
        ];
    }

    /**
     * Get preview nomor selanjutnya untuk dashboard
     */
    public function getNextNumbersPreview($tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        $jenis_list = [
            'Peraturan Walikota',
            'Keputusan Walikota',
            'Instruksi Walikota',
            'Surat Edaran Walikota',
            'Peraturan Daerah',
            'Keputusan Sekda',
            'Instruksi Sekda',
            'Surat Edaran Sekda'
        ];

        $previews = [];
        foreach ($jenis_list as $jenis) {
            $sequence = $this->nomorSequenceModel
                ->where('jenis_peraturan', $jenis)
                ->where('tahun', $tahun)
                ->first();

            $next_number = $sequence ? ($sequence['last_number'] + 1) : 1;
            $previews[$jenis] = $next_number;
        }

        return $previews;
    }

    /**
     * Get numbering statistics untuk monitoring
     */
    public function getNumberingStatistics($tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        return $this->nomorSequenceModel->getNumberingStatistics($tahun);
    }

    /**
     * Validate sequence integrity
     */
    public function validateAllSequences($tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        $sequences = $this->nomorSequenceModel->getSequenceStatus($tahun);
        $validation_results = [];

        foreach ($sequences as $seq) {
            $validation = $this->nomorSequenceModel->validateSequenceIntegrity(
                $seq['jenis_peraturan'],
                $tahun
            );

            $validation_results[] = [
                'jenis' => $seq['jenis_peraturan'],
                'validation' => $validation,
                'sequence_data' => $seq
            ];
        }

        return $validation_results;
    }

    /**
     * Get document type untuk BSRE API berdasarkan jenis peraturan
     */
    public function getDocumentTypeForBSRE($jenis_peraturan)
    {
        $bsre_types = [
            'Keputusan Sekda' => 'keputusan_sekda',
            'Instruksi Sekda' => 'instruksi_sekda',
            'Surat Edaran Sekda' => 'surat_edaran_sekda',
            'Peraturan Walikota' => 'peraturan_walikota',
            'Keputusan Walikota' => 'keputusan_walikota',
            'Instruksi Walikota' => 'instruksi_walikota',
            'Surat Edaran Walikota' => 'surat_edaran_walikota',
            'Peraturan Daerah' => 'peraturan_daerah'
        ];

        return $bsre_types[$jenis_peraturan] ?? 'dokumen_umum';
    }

    /**
     * Check apakah jenis peraturan memerlukan TTE di Sekda atau Walikota
     */
    public function getTTEAuthority($jenis_peraturan)
    {
        $sekda_types = ['Keputusan Sekda', 'Instruksi Sekda', 'Surat Edaran Sekda'];

        return in_array($jenis_peraturan, $sekda_types) ? 'sekda' : 'walikota';
    }

    /**
     * Get expected TTE status berdasarkan jenis peraturan
     */
    public function getExpectedTTEStatus($jenis_peraturan)
    {
        $authority = $this->getTTEAuthority($jenis_peraturan);

        return $authority === 'sekda' ? 11 : 13; // Status TTE Sekda atau TTE Walikota
    }

    /**
     * Log numbering activity untuk audit trail
     */
    private function logNumberingActivity($ajuan_id, $jenis_peraturan, $urutan, $nomor_data)
    {
        log_message('info', "NUMBERING GENERATED: Ajuan {$ajuan_id} - {$jenis_peraturan} - Urutan {$urutan}");

        // Bisa ditambahkan ke tabel audit khusus jika diperlukan
        $audit_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ajuan_id' => $ajuan_id,
            'jenis_peraturan' => $jenis_peraturan,
            'urutan' => $urutan,
            'nomor_data' => $nomor_data,
            'user_id' => session()->get('user')['id_user'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        // Save to audit log file
        $log_file = WRITEPATH . 'logs/numbering_' . date('Y-m-d') . '.log';
        file_put_contents($log_file, json_encode($audit_data) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Reset sequence untuk tahun baru (admin only)
     */
    public function resetSequenceForNewYear($tahun)
    {
        if (!$this->hasPermission('admin_sequence')) {
            throw new \Exception('Permission denied for sequence reset');
        }

        $jenis_list = [
            'Peraturan Walikota',
            'Keputusan Walikota',
            'Instruksi Walikota',
            'Surat Edaran Walikota',
            'Peraturan Daerah',
            'Keputusan Sekda',
            'Instruksi Sekda',
            'Surat Edaran Sekda'
        ];

        foreach ($jenis_list as $jenis) {
            $this->nomorSequenceModel->insert([
                'jenis_peraturan' => $jenis,
                'tahun' => $tahun,
                'last_number' => 0,
                'authority_level' => $this->getTTEAuthority($jenis),
                'prefix_nomor' => $this->getPrefix($jenis),
                'format_template' => $this->getTemplate($jenis)
            ]);
        }

        log_message('info', "Sequence reset completed for year {$tahun}");

        return true;
    }

    private function getPrefix($jenis_peraturan)
    {
        $prefixes = [
            'Keputusan Sekda' => 'KEPDA',
            'Instruksi Sekda' => 'INSTRUKSI_SEKDA',
            'Surat Edaran Sekda' => 'SE_SEKDA',
            'Peraturan Walikota' => 'PERWAL',
            'Keputusan Walikota' => 'KEPWAL',
            'Instruksi Walikota' => 'INSTRUKSI_WAL',
            'Surat Edaran Walikota' => 'SE_WAL',
            'Peraturan Daerah' => 'PERDA'
        ];

        return $prefixes[$jenis_peraturan] ?? 'UNKNOWN';
    }

    private function getTemplate($jenis_peraturan)
    {
        $templates = [
            'Keputusan Sekda' => '{urutan}',
            'Instruksi Sekda' => '{urutan}',
            'Surat Edaran Sekda' => '{urutan}',
            'Peraturan Walikota' => '{urutan}',
            'Keputusan Walikota' => '{urutan}',
            'Instruksi Walikota' => '{urutan}',
            'Surat Edaran Walikota' => '{urutan}',
            'Peraturan Daerah' => '{urutan}'
        ];

        return $templates[$jenis_peraturan] ?? '{urutan}';
    }

    private function hasPermission($permission)
    {
        // Check user permission (implement based on your permission system)
        $user = session()->get('user');
        return isset($user['permissions']) && in_array($permission, $user['permissions']);
    }
}
