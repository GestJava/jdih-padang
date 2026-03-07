<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\FileHandler;

class App extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Base Site URL
     * --------------------------------------------------------------------------
     *
     * URL to your CodeIgniter application. Typically this will be a base URL
     * WITH a trailing slash:
     *
     *    http://example.com/
     *
     * If this is not set then CodeIgniter will try guess the protocol, domain
     * and path to your installation. However, you should always configure this
     * explicitly and never rely on auto-guessing, especially in production
     * environments.
     *
     * DEVELOPMENT MODE:
     * - Using HTTP protocol
     * - No SSL/HTTPS
     * - Cache disabled
     * 
     * PRODUCTION MODE:
     * - Auto-detect HTTPS/HTTP
     * - Use production domain
     * - Cache enabled
     */
    public string $baseURL;

    /**
     * --------------------------------------------------------------------------
     * Index File
     * --------------------------------------------------------------------------
     *
     * Set to empty string for clean URLs. If you are using mod_rewrite to remove
     * the page set this variable so that it is blank.
     */
    public string $indexPage = '';

    /**
     * --------------------------------------------------------------------------
     * Application Timezone
     * --------------------------------------------------------------------------
     *
     * The default timezone that will be used in your application to display
     * dates with the date helper, and can be retrieved through app_timezone()
     */
    public string $appTimezone = 'Asia/Jakarta';

    // Images Path and URL (auto-detect based on environment)
    public string $imagesPath = FCPATH . 'images/';
    public string $imagesURL;

    /**
     * --------------------------------------------------------------------------
     * TTE Configuration
     * --------------------------------------------------------------------------
     *
     * Configuration for Tanda Tangan Elektronik (TTE) service
     */
    public $tteApiUrl;
    public $tteApiKey;
    public $tteClientId;
    public $tteClientSecret;

    public function __construct()
    {
        parent::__construct();

        // Auto-detect base URL based on environment
        $this->detectBaseURL();

        // Set images URL based on base URL
        $this->imagesURL = $this->baseURL . 'images/';

        // Load TTE configuration from environment
        $this->tteApiUrl = $_ENV['TTE_API_URL'] ?? '';
        $this->tteApiKey = $_ENV['TTE_API_KEY'] ?? '';
        $this->tteClientId = $_ENV['TTE_CLIENT_ID'] ?? 'bsre';
        $this->tteClientSecret = $_ENV['TTE_CLIENT_SECRET'] ?? 'securetransactions!!!';
    }

    /**
     * Auto-detect base URL based on environment
     */
    private function detectBaseURL()
    {
        // Check if we're in production (jdih.padang.go.id)
        if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'jdih.padang.go.id') !== false) {
            // Production environment
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $this->baseURL = $protocol . '://jdih.padang.go.id/';
        } elseif (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === '103.141.74.108' || strpos($_SERVER['HTTP_HOST'], '103.141.74.108') !== false)) {
            // Server IP - baseURL tanpa /jdih/ untuk TTE API compatibility
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $this->baseURL = $protocol . '://103.141.74.108/';
        } elseif (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'webjdih.test') !== false) {
            // macOS Valet development (webjdih.test)
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $this->baseURL = $protocol . '://webjdih.test/';
        } elseif (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
            // Localhost development - Force HTTP to prevent redirect issues
            $this->baseURL = 'http://localhost/webjdih/';
        } else {
            // Auto-detect for other environments
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $path = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            $this->baseURL = $protocol . '://' . $host . $path . '/';
        }
    }

    // Custom configurations
    public array $checkRoleAction = ['enable_global' => true, 'field' => 'id_user_input'];
    public string $idWilayahKelurahan = '35604';

    public array $csrf = [
        // Load csrf_helper.php
        // CRITICAL SECURITY FIX: Enable CSRF protection untuk mencegah CSRF attack
        'enable' => true,

        // Automatic insert token into cookie
        'auto_settoken' => false,

        // Auto compare cookie token and $_POST token and exit program when an error occurred
        'auto_check' => false,

        // Form field, e.g., <input type="hidden" name="csrf_app_token" value="..."/>
        'name' => 'csrf_app_token',

        // Cookie lifetime in seconds (2 hours)
        'expire' => 7200
    ];

    /**
     * Rate Limiting Configuration
     * 
     * Configuration untuk mencegah brute force attack pada login
     */
    public array $rateLimit = [
        // Enable/disable rate limiting
        'enable' => true,

        // Maksimal percobaan login sebelum lockout
        'max_attempts' => 5,

        // Waktu lockout dalam detik (15 menit)
        'lockout_time' => 900,

        // Reset attempts setelah waktu tertentu
        'reset_time' => 900,

        // Log failed attempts
        'log_attempts' => true
    ];

    /**
     * Allowed Hostnames in the Site URL other than the hostname in the baseURL.
     * If you want to accept multiple Hostnames, set this.
     *
     * E.g., When your site URL ($baseURL) is 'http://example.com/', and your site
     * also accepts 'http://media.example.com/' and 'http://accounts.example.com/':
     * ['media.example.com', 'accounts.example.com']
     *
     * @var string[]
     * @phpstan-var list<string>
     */
    public array $allowedHostnames = [];

    /**
     * --------------------------------------------------------------------------
     * URI PROTOCOL
     * --------------------------------------------------------------------------
     *
     * This item determines which server global should be used to retrieve the
     * URI string. The default setting of 'REQUEST_URI' works for most servers.
     * If your links do not seem to work, try one of the other options:
     *
     * 'REQUEST_URI'    Uses $_SERVER['REQUEST_URI']
     * 'QUERY_STRING'   Uses $_SERVER['QUERY_STRING']
     * 'PATH_INFO'      Uses $_SERVER['PATH_INFO']
     *
     * WARNING: If you set this to 'PATH_INFO', URIs will always be URL-decoded!
     */
    public string $uriProtocol = 'REQUEST_URI';

    /**
     * --------------------------------------------------------------------------
     * Default Locale
     * --------------------------------------------------------------------------
     *
     * The Locale roughly represents the language and location that your visitor
     * is viewing the site from. It affects the language strings and other
     * strings (like currency markers, numbers, etc), that your program
     * should run under for this request.
     */
    public string $defaultLocale = 'en';

    /**
     * --------------------------------------------------------------------------
     * Negotiate Locale
     * --------------------------------------------------------------------------
     *
     * If true, the current Request object will automatically determine the
     * language to use based on the value of the Accept-Language header.
     *
     * If false, no automatic detection will be performed.
     */
    public bool $negotiateLocale = false;

    /**
     * --------------------------------------------------------------------------
     * Supported Locales
     * --------------------------------------------------------------------------
     *
     * If $negotiateLocale is true, this array lists the locales supported
     * by the application in descending order of priority. If no match is
     * found, the first locale will be used.
     *
     * @var string[]
     */
    public array $supportedLocales = ['en'];

    /**
     * --------------------------------------------------------------------------
     * Default Character Set
     * --------------------------------------------------------------------------
     *
     * This determines which character set is used by default in various methods
     * that require a character set to be provided.
     *
     * @see http://php.net/htmlspecialchars for a list of supported charsets.
     */
    public string $charset = 'UTF-8';

    /**
     * --------------------------------------------------------------------------
     * Force Global Secure Requests
     * --------------------------------------------------------------------------
     *
     * If true, this will force every request made to this application to be
     * made via a secure connection (HTTPS). If the incoming request is not
     * secure, the user will be redirected to a secure version of the page
     * and the HTTP Strict Transport Security header will be set.
     * 
     * IMPORTANT: Set to false for localhost/development environment
     * Set to true only in production with valid SSL certificate
     */
    public bool $forceGlobalSecureRequests = false;

    /**
     * Force HTTPS for specific routes only
     * 
     * Routes that should always use HTTPS, even in development
     */
    public array $forceHttpsRoutes = [
        // Add specific routes that need HTTPS here
        // 'api/secure-endpoint',
        // 'admin/sensitive-data'
    ];

    /**
     * Protocol consistency settings
     * 
     * Prevents protocol mismatch issues in development
     */
    public array $protocolSettings = [
        'forceHttpForLocalhost' => true,
        'preventHttpsRedirect' => true,
        'corsAllowAll' => true
    ];

    /**
     * --------------------------------------------------------------------------
     * Session Driver
     * --------------------------------------------------------------------------
     *
     * The session storage driver to use:
     * - `CodeIgniter\Session\Handlers\FileHandler`
     * - `CodeIgniter\Session\Handlers\DatabaseHandler`
     * - `CodeIgniter\Session\Handlers\MemcachedHandler`
     * - `CodeIgniter\Session\Handlers\RedisHandler`
     *
     * @deprecated use Config\Session::$driver instead.
     */
    public string $sessionDriver = FileHandler::class;

    /**
     * --------------------------------------------------------------------------
     * Session Cookie Name
     * --------------------------------------------------------------------------
     *
     * The session cookie name, must contain only [0-9a-z_-] characters
     *
     * @deprecated use Config\Session::$cookieName  instead.
     */
    public string $sessionCookieName = 'ci_session';

    /**
     * --------------------------------------------------------------------------
     * Session Expiration
     * --------------------------------------------------------------------------
     *
     * The number of SECONDS you want the session to last.
     * Setting to 0 (zero) means expire when the browser is closed.
     *
     * @deprecated use Config\Session::$expiration instead.
     */
    public int $sessionExpiration = 7200;

    /**
     * --------------------------------------------------------------------------
     * Session Save Path
     * --------------------------------------------------------------------------
     *
     * The location to save sessions to and is driver dependent.
     *
     * For the 'files' driver, it's a path to a writable directory.
     * WARNING: Only absolute paths are supported!
     *
     * For the 'database' driver, it's a table name.
     * Please read up the manual for the format with other session drivers.
     *
     * IMPORTANT: You are REQUIRED to set a valid save path!
     *
     * @deprecated use Config\Session::$savePath instead.
     */
    public string $sessionSavePath = WRITEPATH . 'session';

    /**
     * --------------------------------------------------------------------------
     * Session Match IP
     * --------------------------------------------------------------------------
     *
     * Whether to match the user's IP address when reading the session data.
     *
     * WARNING: If you're using the database driver, don't forget to update
     *          your session table's PRIMARY KEY when changing this setting.
     *
     * @deprecated use Config\Session::$matchIP instead.
     */
	public bool $sessionMatchIP = true; // CRITICAL: Enable IP matching untuk mencegah session sharing antar browser/IP

    /**
     * --------------------------------------------------------------------------
     * Session Time to Update
     * --------------------------------------------------------------------------
     *
     * How many seconds between CI regenerating the session ID.
     *
     * @deprecated use Config\Session::$timeToUpdate instead.
     */
    public int $sessionTimeToUpdate = 300;

    /**
     * --------------------------------------------------------------------------
     * Session Regenerate Destroy
     * --------------------------------------------------------------------------
     *
     * Whether to destroy session data associated with the old session ID
     * when auto-regenerating the session ID. When set to FALSE, the data
     * will be later deleted by the garbage collector.
     *
     * @deprecated use Config\Session::$regenerateDestroy instead.
     */
	public bool $sessionRegenerateDestroy = true; // CRITICAL: Destroy old session saat regenerate untuk keamanan

    /**
     * --------------------------------------------------------------------------
     * Cookie Prefix
     * --------------------------------------------------------------------------
     *
     * Set a cookie name prefix if you need to avoid collisions.
     *
     * @deprecated use Config\Cookie::$prefix property instead.
     */
    public string $cookiePrefix = '';

    /**
     * --------------------------------------------------------------------------
     * Cookie Domain
     * --------------------------------------------------------------------------
     *
     * Set to `.your-domain.com` for site-wide cookies.
     *
     * @deprecated use Config\Cookie::$domain property instead.
     */
    public string $cookieDomain = '';

    /**
     * --------------------------------------------------------------------------
     * Cookie Path
     * --------------------------------------------------------------------------
     *
     * Typically will be a forward slash.
     *
     * @deprecated use Config\Cookie::$path property instead.
     */
    public string $cookiePath = '/';

    /**
     * --------------------------------------------------------------------------
     * Cookie Secure
     * --------------------------------------------------------------------------
     *
     * Cookie will only be set if a secure HTTPS connection exists.
     *
     * @deprecated use Config\Cookie::$secure property instead.
     */
	public bool $cookieSecure = true; // CRITICAL: Only send cookies over HTTPS (set false jika masih pakai HTTP)

    /**
     * --------------------------------------------------------------------------
     * Cookie HttpOnly
     * --------------------------------------------------------------------------
     *
     * Cookie will only be accessible via HTTP(S) (no JavaScript).
     *
     * @deprecated use Config\Cookie::$httponly property instead.
     */
	public bool $cookieHTTPOnly = true; // CRITICAL: Prevent JavaScript access to cookies

    /**
     * --------------------------------------------------------------------------
     * Cookie SameSite
     * --------------------------------------------------------------------------
     *
     * Configure cookie SameSite setting. Allowed values are:
     * - None
     * - Lax
     * - Strict
     * - ''
     *
     * Defaults to `Lax` for compatibility with modern browsers. Setting '' (empty string)
     * means no SameSite attribute will be set on cookies. The default setting is `Lax`
     * which is the more secure default which will work for most applications.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite
     *
     * @deprecated use Config\Cookie::$samesite property instead.
     */
    public string $cookieSameSite = 'Lax';

    /**
     * --------------------------------------------------------------------------
     * Reverse Proxy IPs
     * --------------------------------------------------------------------------
     *
     * If your server is behind a reverse proxy, you must whitelist the proxy
     * IP addresses from which CodeIgniter should trust headers such as
     * X-Forwarded-For or Client-IP in order to properly identify
     * the visitor's IP address.
     *
     * You need to set a proxy IP address or IP address with subnets and
     * the HTTP header for the client IP address.
     *
     * Here are some examples:
     *
     * 1. [ '10.0.1.200' ]
     * 2. [ '10.0.1.0/24' ]
     * 3. [ '10.0.1.0/255.255.255.0' ]
     * 4. [ '10.0.1.200/24' ]
     * 5. [ '10.0.1.200/255.255.255.0' ]
     * 6. [ '10.0.1.200' => 'X-Client-IP' ]
     * 7. [ '10.0.1.0/24' => 'X-Forwarded-For' ]
     * 8. [ '10.0.1.0/255.255.255.0' => 'X-Forwarded-For' ]
     *
     * @var array<string, string>
     */
    public array $proxyIPs = [];

    /**
     * --------------------------------------------------------------------------
     * CSRF Token Name
     * --------------------------------------------------------------------------
     *
     * The token name.
     *
     * @deprecated Use `Config\Security` $tokenName property instead of using this property.
     */
    public string $CSRFTokenName = 'csrf_test_name';

    /**
     * --------------------------------------------------------------------------
     * CSRF Header Name
     * --------------------------------------------------------------------------
     *
     * The header name.
     *
     * @deprecated Use `Config\Security` $headerName property instead of using this property.
     */
    public string $CSRFHeaderName = 'X-CSRF-TOKEN';

    /**
     * --------------------------------------------------------------------------
     * CSRF Cookie Name
     * --------------------------------------------------------------------------
     *
     * The cookie name.
     *
     * @deprecated Use `Config\Security` $cookieName property instead of using this property.
     */
    public string $CSRFCookieName = 'csrf_cookie_name';

    /**
     * --------------------------------------------------------------------------
     * CSRF Expire
     * --------------------------------------------------------------------------
     *
     * The number in seconds the token should expire.
     *
     * @deprecated Use `Config\Security` $expire property instead of using this property.
     */
    public int $CSRFExpire = 7200;

    /**
     * --------------------------------------------------------------------------
     * CSRF Regenerate
     * --------------------------------------------------------------------------
     *
     * Regenerate token on every submission?
     *
     * @deprecated Use `Config\Security` $regenerate property instead of using this property.
     */
    public bool $CSRFRegenerate = true;

    /**
     * --------------------------------------------------------------------------
     * CSRF Redirect
     * --------------------------------------------------------------------------
     *
     * Redirect to previous page with error on failure?
     *
     * @deprecated Use `Config\Security` $redirect property instead of using this property.
     */
    public bool $CSRFRedirect = false;

    /**
     * --------------------------------------------------------------------------
     * CSRF SameSite
     * --------------------------------------------------------------------------
     *
     * Setting for CSRF SameSite cookie token. Allowed values are:
     * - None
     * - Lax
     * - Strict
     * - ''
     *
     * Defaults to `Lax` as recommended in this link:
     *
     * @see https://portswigger.net/web-security/csrf/samesite-cookies
     *
     * @deprecated `Config\Cookie` $samesite property is used.
     */
    public string $CSRFSameSite = 'Lax';

    /**
     * --------------------------------------------------------------------------
     * Content Security Policy
     * --------------------------------------------------------------------------
     *
     * Enables the Response's Content Secure Policy to restrict the sources that
     * can be used for images, scripts, CSS files, audio, video, etc. If enabled,
     * the Response object will populate default values for the policy from the
     * `ContentSecurityPolicy.php` file. Controllers can always add to those
     * restrictions at run time.
     *
     * For a better understanding of CSP, see these documents:
     *
     * @see http://www.html5rocks.com/en/tutorials/security/content-security-policy/
     * @see http://www.w3.org/TR/CSP/
     */
    public bool $CSPEnabled = false;
}
