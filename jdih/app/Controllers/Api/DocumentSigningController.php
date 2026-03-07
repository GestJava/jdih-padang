<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\DocumentSigningService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class DocumentSigningController extends BaseController
{
    use ResponseTrait;
    
    protected $signingService;
    
    public function __construct()
    {
        $this->signingService = new DocumentSigningService();
        helper(['form', 'url', 'file']);
    }
    
    /**
     * Endpoint untuk menandatangani dokumen
     */
    public function sign(): ResponseInterface
    {
        try {
            // Validasi input
            $rules = [
                'document_id' => 'required|numeric',
                'document_type' => 'required|string',
                'nik' => 'required|numeric|exact_length[16]',
                'passphrase' => 'required|min_length[8]',
                'file_path' => 'required|string'
            ];
            
            if (!$this->validate($rules)) {
                return $this->failValidationErrors($this->validator->getErrors());
            }
            
            $data = $this->request->getJSON(true);
            
            // Proses penandatanganan
            $result = $this->signingService->signDocument(
                $data['file_path'],
                $data['document_type'],
                $data['nik'],
                $data['passphrase']
            );
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Dokumen berhasil ditandatangani',
                'data' => [
                    'signed_path' => $result['signed_path'],
                    'document_number' => $result['document_number']
                ]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in DocumentSigningController: ' . $e->getMessage());
            
            return $this->failServerError($e->getMessage());
        }
    }
}
