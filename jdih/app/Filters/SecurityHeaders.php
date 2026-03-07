<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class SecurityHeaders implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Nothing to do before
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add security headers
        $headers = [
            "X-Frame-Options" => "SAMEORIGIN",
            "X-XSS-Protection" => "1; mode=block", 
            "X-Content-Type-Options" => "nosniff",
            "Referrer-Policy" => "strict-origin-when-cross-origin",
            "Permissions-Policy" => "geolocation=(), microphone=(), camera=()",
            "X-Robots-Tag" => "noindex, nofollow, nosnippet, noarchive"
        ];
        
        foreach ($headers as $name => $value) {
            $response->setHeader($name, $value);
        }
        
        return $response;
    }
}