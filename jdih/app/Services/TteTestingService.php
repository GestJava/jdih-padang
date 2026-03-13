<?php

namespace App\Services;

/**
 * TteTestingService
 * 
 * Provides mock responses for TTE integration during development
 * to avoid network blocks and dependency on real API.
 */
class TteTestingService
{
    /**
     * Mock response for checkUserStatus
     */
    public function mockCheckUserStatus(string $nik): array
    {
        log_message('info', "TTE [MOCK]: Checking status for NIK {$nik}");
        
        // Return active status for demo NIKs or standard format
        return [
            'status_code' => 1111,
            'status' => 'AKTIF',
            'message' => 'User is registered and active in TTE system',
            'data' => [
                'nik' => $nik,
                'nama' => 'User JDIH Mock',
                'nip' => '199001012015011001',
                'jabatan' => 'Testing Official',
                'instansi' => 'Pemerintah Kota Padang',
                'status_sertifikat' => 'AKTIF'
            ]
        ];
    }

    /**
     * Mock response for signDocument
     */
    public function mockSignDocument(string $nik, string $password, string $documentPath, array $metadata = []): array
    {
        log_message('info', "TTE [MOCK]: Signing document {$documentPath} for NIK {$nik}");
        
        if (!file_exists($documentPath)) {
            return [
                'success' => false,
                'message' => 'Source document not found in mock service'
            ];
        }

        // Simulasikan delay API
        usleep(500000); 

        // Generate mock signed path
        $filename = basename($documentPath, '.pdf') . '_signed.pdf';
        $uploadDir = WRITEPATH . 'uploads/harmonisasi/tte/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $signedPath = $uploadDir . $filename;
        
        // In mock mode, we just copy the enhanced file as the "signed" file
        // This allows testing the full workflow without a real signature
        if (copy($documentPath, $signedPath)) {
            return [
                'success' => true,
                'status_code' => 200,
                'message' => 'Document signed successfully (MOCK MODE)',
                'signed_path' => $signedPath,
                'file' => [base64_encode(file_get_contents($signedPath))],
                'metadata' => array_merge($metadata, [
                    'signed_at' => date('Y-m-d H:i:s'),
                    'signed_by' => $nik,
                    'mock_mode' => true
                ])
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create mock signed file'
        ];
    }
}
