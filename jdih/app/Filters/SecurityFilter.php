<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class SecurityFilter implements FilterInterface
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
     * @param array|null $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Skip security checks for CLI requests
        if (is_cli()) {
            return;
        }

        $security = config('Security');

        // Rate limiting
        if ($security->rateLimiting['enabled']) {
            $this->checkRateLimit($request);
        }

        // SQL Injection protection
        if ($security->sqlInjection['enabled']) {
            $this->checkSqlInjection($request);
        }

        // XSS protection
        if ($security->xssProtection['enabled']) {
            $this->checkXSS($request);
        }

        // Directory traversal protection
        if ($security->directoryTraversal['enabled']) {
            $this->checkDirectoryTraversal($request);
        }

        // IP blocking
        if ($security->ipBlocking['enabled']) {
            $this->checkIPBlocking($request);
        }

        // Input validation
        $this->validateInput($request);
    }

    /**
     * We don't have anything to do here.
     *
     * @param array|null $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add security headers
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->setHeader('X-XSS-Protection', '1; mode=block');
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->setHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit(RequestInterface $request)
    {
        $security = config('Security');
        $cache = Services::cache();
        $ip = $request->getIPAddress();
        $cacheKey = 'rate_limit_' . md5($ip);

        $requests = $cache->get($cacheKey) ?? 0;

        if ($requests >= $security->rateLimiting['maxRequests']) {
            log_message('warning', 'Rate limit exceeded for IP: ' . $ip);
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Too many requests. Please try again later.');
        }

        $cache->save($cacheKey, $requests + 1, $security->rateLimiting['window']);
    }

    /**
     * Check for SQL injection attempts
     */
    private function checkSqlInjection(RequestInterface $request)
    {
        $security = config('Security');
        $input = $request->getVar() ?: [];

        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $value = strtoupper($value);
                foreach ($security->sqlInjection['blockedKeywords'] as $keyword) {
                    if (strpos($value, $keyword) !== false) {
                        log_message('error', 'SQL injection attempt detected: ' . $keyword . ' in ' . $key . ' from IP: ' . $request->getIPAddress());
                        throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid input detected.');
                    }
                }
            }
        }
    }

    /**
     * Check for XSS attempts
     */
    private function checkXSS(RequestInterface $request)
    {
        $security = config('Security');
        $input = $request->getGet() + $request->getPost();

        foreach ($input as $key => $value) {
            if (is_string($value)) {
                foreach ($security->inputValidation['blockedPatterns'] as $pattern) {
                    if (preg_match($pattern, $value)) {
                        log_message('error', 'XSS attempt detected in ' . $key . ' from IP: ' . $request->getIPAddress());
                        throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid input detected.');
                    }
                }
            }
        }
    }

    /**
     * Check for directory traversal attempts
     */
    private function checkDirectoryTraversal(RequestInterface $request)
    {
        $security = config('Security');
        $uri = $request->getUri()->getPath();

        foreach ($security->directoryTraversal['blockedPatterns'] as $pattern) {
            if (strpos($uri, $pattern) !== false) {
                log_message('error', 'Directory traversal attempt detected: ' . $pattern . ' from IP: ' . $request->getIPAddress());
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid path detected.');
            }
        }
    }

    /**
     * Check IP blocking
     */
    private function checkIPBlocking(RequestInterface $request)
    {
        $security = config('Security');
        $ip = $request->getIPAddress();

        // Check if IP is in blacklist
        if (in_array($ip, $security->ipBlocking['blockedIPs'])) {
            log_message('error', 'Blocked IP attempt: ' . $ip);
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied.');
        }

        // Check if IP is in whitelist (if whitelist is not empty)
        if (!empty($security->ipBlocking['whitelistIPs']) && !in_array($ip, $security->ipBlocking['whitelistIPs'])) {
            log_message('error', 'Non-whitelisted IP attempt: ' . $ip);
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied.');
        }
    }

    /**
     * Validate input length and content
     */
    private function validateInput(RequestInterface $request)
    {
        $security = config('Security');
        $input = $request->getGet() + $request->getPost();

        foreach ($input as $key => $value) {
            if (is_string($value)) {
                // Check length limits
                foreach ($security->inputValidation['maxLength'] as $field => $maxLength) {
                    if ($key === $field && strlen($value) > $maxLength) {
                        log_message('error', 'Input too long: ' . $key . ' from IP: ' . $request->getIPAddress());
                        throw new \CodeIgniter\Exceptions\PageNotFoundException('Input too long.');
                    }
                }
            }
        }
    }
}
