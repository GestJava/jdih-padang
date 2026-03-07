<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\SessionManager;

class SessionValidationFilter implements FilterInterface
{
    /**
     * Validasi session di setiap request
     * Jika session tidak valid (login dari perangkat lain), logout otomatis
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = \Config\Services::session();

        // Skip validation untuk halaman login dan routes yang tidak memerlukan auth
        $uri = $request->getUri();
        $path = $uri->getPath();

        $skipPaths = ['/login', '/logout', '/register', '/recovery', '/activation'];
        foreach ($skipPaths as $skipPath) {
            if (strpos($path, $skipPath) !== false) {
                return $request;
            }
        }

        // Jika user sudah login, validasi session
        if ($session->has('logged_in') && $session->get('logged_in') === true) {
            $sessionManager = new SessionManager();

            // Validasi session dengan database
            $isValid = $sessionManager->validateCurrentSession();

            if (!$isValid) {
                // Session tidak valid, redirect ke login dengan pesan
                $session->setFlashdata('logout_reason', 'Anda telah login dari perangkat lain. Session ini telah berakhir.');
                return redirect()->to('/login');
            }
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
