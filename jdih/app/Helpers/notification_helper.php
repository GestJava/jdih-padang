<?php

use App\Models\HarmonisasiAjuanModel;
use App\Config\HarmonisasiStatus;

/**
 * Get total pending tasks for a user based on their role and status
 * 
 * @param array $user Session user data
 * @return array Counts grouped by module and details
 */
if (!function_exists('get_user_notifications')) {
    function get_user_notifications($user)
    {
        $db = \Config\Database::connect();
        $harmonisasiModel = new HarmonisasiAjuanModel();
        
        $notifications = [
            'total' => 0,
            'harmonisasi' => 0,
            'legalisasi' => 0,
            'penugasan' => 0,
            'verifikasi' => 0,
            'validasi' => 0,
            'finalisasi' => 0,
            'items' => []
        ];

        if (empty($user) || !isset($user['id_user'])) {
            return $notifications;
        }

        $userId = $user['id_user'];
        $role = strtolower($user['nama_role'] ?? '');
        $instansiId = $user['id_instansi'] ?? null;

        // 1. HARMONISASI TASKS
        // ------------------------------------------------------------
        
        // OPD/Admin Tasks: Draft (1) and Revisi (5)
        if (strpos($role, 'opd') !== false || strpos($role, 'instansi') !== false || strpos($role, 'admin') !== false) {
            $query = $harmonisasiModel->whereIn('id_status_ajuan', [HarmonisasiStatus::DRAFT, HarmonisasiStatus::REVISI]);
            
            if (strpos($role, 'admin') === false && $instansiId) {
                $query->where('id_instansi_pemohon', $instansiId);
            }
            
            $count = $query->countAllResults();
            
            if ($count > 0) {
                $notifications['harmonisasi'] += $count;
                $notifications['items'][] = [
                    'module' => 'harmonisasi',
                    'title' => 'Draft/Revisi Harmonisasi',
                    'count' => $count,
                    'url' => base_url('harmonisasi'),
                    'icon' => 'fas fa-edit'
                ];
            }
        }

        // Kabag Tasks: Diajukan (2) -> Now pointing to Penugasan module
        if (strpos($role, 'kabag') !== false || strpos($role, 'admin') !== false) {
            $count = $harmonisasiModel->where('id_status_ajuan', HarmonisasiStatus::DIAJUKAN)
                ->countAllResults();
            
            if ($count > 0) {
                $notifications['penugasan'] += $count; // Sidebar badge for penugasan module
                $notifications['items'][] = [
                    'module' => 'penugasan',
                    'title' => 'Penugasan Harmonisasi',
                    'count' => $count,
                    'url' => base_url('penugasan'),
                    'icon' => 'fas fa-user-plus'
                ];
            }
        }

        // Verifikator Tasks: Ditugaskan (3)
        if (strpos($role, 'verifikator') !== false || strpos($role, 'admin') !== false) {
            $query = $harmonisasiModel->where('id_status_ajuan', HarmonisasiStatus::VERIFIKASI);
            if (strpos($role, 'admin') === false) {
                $query->where('id_petugas_verifikasi', $userId);
            }
            $count = $query->countAllResults();
            
            if ($count > 0) {
                $notifications['verifikasi'] += $count; // Sidebar badge for verifikasi module
                $notifications['items'][] = [
                    'module' => 'verifikasi',
                    'title' => 'Verifikasi Harmonisasi',
                    'count' => $count,
                    'url' => base_url('verifikasi'),
                    'icon' => 'fas fa-check-circle'
                ];
            }
        }

        // Validator Tasks: Validasi (4)
        if (strpos($role, 'validator') !== false || strpos($role, 'admin') !== false) {
            $count = $harmonisasiModel->where('id_status_ajuan', HarmonisasiStatus::VALIDASI)
                ->countAllResults();
            
            if ($count > 0) {
                $notifications['validasi'] += $count; // Sidebar badge for validasi module
                $notifications['items'][] = [
                    'module' => 'validasi',
                    'title' => 'Validasi Harmonisasi',
                    'count' => $count,
                    'url' => base_url('validasi'),
                    'icon' => 'fas fa-tasks'
                ];
            }
        }

        // Finalisator Tasks: Finalisasi (6)
        if (strpos($role, 'finalisator') !== false || strpos($role, 'admin') !== false) {
            $count = $harmonisasiModel->where('id_status_ajuan', HarmonisasiStatus::FINALISASI)
                ->countAllResults();
            
            if ($count > 0) {
                $notifications['finalisasi'] += $count; // Sidebar badge for finalisasi module
                $notifications['items'][] = [
                    'module' => 'finalisasi',
                    'title' => 'Finalisasi Harmonisasi',
                    'count' => $count,
                    'url' => base_url('finalisasi'),
                    'icon' => 'fas fa-file-export'
                ];
            }
        }

        // 2. LEGALISASI TASKS (Paraf Workflow)
        // ------------------------------------------------------------
        
        $legalisasiTasks = [
            'opd' => ['status' => HarmonisasiStatus::PARAF_OPD, 'title' => 'Paraf OPD', 'url' => 'legalisasi/dashboard-opd', 'icon' => 'fas fa-stamp'],
            'kabag' => ['status' => HarmonisasiStatus::PARAF_KABAG, 'title' => 'Paraf Kabag Hukum', 'url' => 'legalisasi/dashboard-kabag', 'icon' => 'fas fa-stamp'],
            'asisten' => ['status' => HarmonisasiStatus::PARAF_ASISTEN, 'title' => 'Paraf Asisten', 'url' => 'legalisasi/dashboard-asisten', 'icon' => 'fas fa-stamp'],
            'sekda' => ['status' => HarmonisasiStatus::PARAF_SEKDA, 'title' => 'Paraf/TTE Sekda', 'url' => 'legalisasi/dashboard-sekda', 'icon' => 'fas fa-file-signature'],
            'wawako' => ['status' => HarmonisasiStatus::PARAF_WAWAKO, 'title' => 'Paraf Wawako', 'url' => 'legalisasi/dashboard-wawako', 'icon' => 'fas fa-stamp'],
            'walikota' => ['status' => HarmonisasiStatus::TTE_WALIKOTA, 'title' => 'TTE Walikota', 'url' => 'legalisasi/dashboard-walikota', 'icon' => 'fas fa-file-signature'],
        ];

        foreach ($legalisasiTasks as $taskRole => $config) {
            if (strpos($role, $taskRole) !== false || strpos($role, 'admin') !== false) {
                $query = $harmonisasiModel->where('id_status_ajuan', $config['status']);
                
                // Specific for OPD Paraf (based on instansi)
                if ($taskRole === 'opd' && strpos($role, 'admin') === false && $instansiId) {
                    $query->where('id_instansi_pemohon', $instansiId);
                }

                // Specific for Asisten (based on their assigned OPDs in instansi table)
                if ($taskRole === 'asisten' && strpos($role, 'admin') === false) {
                    $assignedInstansi = $db->table('instansi')
                        ->select('id')
                        ->where('id_asisten', $userId)
                        ->get()
                        ->getResultArray();
                    
                    if (empty($assignedInstansi)) {
                        $count = 0;
                    } else {
                        $instansiIds = array_column($assignedInstansi, 'id');
                        $query->whereIn('id_instansi_pemohon', $instansiIds);
                        $count = $query->countAllResults();
                    }
                } else {
                    $count = $query->countAllResults();
                }

                if ($count > 0) {
                    $notifications['legalisasi'] += $count;
                    
                    // Add specific key for sidebar badges (module name usually follows the URL or a specific key)
                    $notifications[$config['url']] = $count;
                    
                    $notifications['items'][] = [
                        'module' => $config['url'],
                        'title' => $config['title'],
                        'count' => $count,
                        'url' => base_url($config['url']),
                        'icon' => $config['icon']
                    ];
                }
            }
        }

        $notifications['total'] = $notifications['harmonisasi'] + $notifications['legalisasi'] + $notifications['penugasan'] + $notifications['verifikasi'] + $notifications['validasi'] + $notifications['finalisasi'];
        return $notifications;
    }
}
