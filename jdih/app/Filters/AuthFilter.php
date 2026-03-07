<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = Services::session();
        $response = Services::response();

        // Cek apakah ini AJAX request
        $isAjax = $request->isAJAX() || $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

        // Jika user belum login
        if (!$session->has('logged_in') || $session->get('logged_in') !== true) {
            if ($isAjax) {
                // Return JSON error untuk AJAX request, bukan redirect
                return $response->setJSON([
                    'error' => 'Sesi tidak valid. Silakan login kembali.',
                    'redirect' => base_url('login')
                ])->setStatusCode(401);
            }
            return redirect()->to('login')->with('error', 'Silakan login terlebih dahulu');
        }

        // Periksa data user di session
        if (!$session->has('user')) {
            $session->remove('logged_in');
            if ($isAjax) {
                // Return JSON error untuk AJAX request, bukan redirect
                return $response->setJSON([
                    'error' => 'Sesi telah berakhir. Silakan login kembali.',
                    'redirect' => base_url('login')
                ])->setStatusCode(401);
            }
            return redirect()->to('login')->with('error', 'Sesi telah berakhir');
        }

        return $request;
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
