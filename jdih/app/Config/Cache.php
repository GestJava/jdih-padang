<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cache extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Primary Handler
     * --------------------------------------------------------------------------
     *
     * The name of the preferred handler that should be used. If for some reason
     * it is not available, the $backupHandler will be used in its place.
     */
    public string $handler = 'file';

    /**
     * --------------------------------------------------------------------------
     * Backup Handler
     * --------------------------------------------------------------------------
     *
     * The name of the handler that will be used in case the first one is
     * unreachable. Commonly this will be the file handler, but can be
     * any of the handlers listed below.
     */
    public string $backupHandler = 'dummy';

    /**
     * --------------------------------------------------------------------------
     * Cache Directory Path
     * --------------------------------------------------------------------------
     *
     * The path to where cache files should be stored, if using a file-based
     * cache handler.
     */
    public string $storePath = WRITEPATH . 'cache/';

    /**
     * --------------------------------------------------------------------------
     * Cache Include Query String
     * --------------------------------------------------------------------------
     *
     * Whether to take the URL query string into consideration when generating
     * output cache files. Valid options are:
     *
     *    false      = Disabled
     *    true       = Enabled, take all query parameters into account.
     *                 Please be aware that this may result in numerous cache
     *                 files generated for the same page over and over again.
     *    ['q']      = Enabled, but only take into account the specified list
     *                 of query parameters.
     */
    public $cacheQueryString = true;

    /**
     * --------------------------------------------------------------------------
     * Key Prefix
     * --------------------------------------------------------------------------
     *
     * This string is added to all cache item names to help avoid collisions
     * if you run multiple applications with the same cache engine.
     */
    public string $prefix = 'jdih_';

    /**
     * --------------------------------------------------------------------------
     * Default TTL
     * --------------------------------------------------------------------------
     *
     * The default number of seconds that items should be stored for when
     * none is specified.
     *
     * WARNING: This is not used by framework handlers where 60 seconds is
     * hard-coded, but may be useful to projects and modules.
     */
    public int $ttl = 3600; // 1 hour default

    /**
     * --------------------------------------------------------------------------
     * Reserved Characters
     * --------------------------------------------------------------------------
     *
     * A string of reserved characters that will not be allowed in keys or tags.
     * Strings that contain any of the characters in this string will be
     * encoded/escaped.
     */
    public string $reservedCharacters = '{}()/\@:';

    /**
     * --------------------------------------------------------------------------
     * File settings
     * --------------------------------------------------------------------------
     * Your file storage preferences can be specified below, if you are using
     * the File driver.
     *
     * storePath - Path to your cache folder
     * mode      - File mode, typically 0640
     */
    public array $file = [
        'storePath' => WRITEPATH . 'cache/',
        'mode'      => 0640,
    ];

    /**
     * --------------------------------------------------------------------------
     * Memcached settings
     * --------------------------------------------------------------------------
     * Your Memcached servers can be specified below, if you are using
     * the Memcached drivers.
     */
    public array $memcached = [
        'host'   => '127.0.0.1',
        'port'   => 11211,
        'weight' => 1,
        'raw'    => false,
    ];

    /**
     * --------------------------------------------------------------------------
     * Redis settings
     * --------------------------------------------------------------------------
     * Your Redis server can be specified below, if you are using
     * the Redis or Predis drivers.
     */
    public array $redis = [
        'host'     => '127.0.0.1',
        'password' => null,
        'port'     => 6379,
        'timeout'  => 0,
        'database' => 0,
    ];

    /**
     * --------------------------------------------------------------------------
     * Available Handlers
     * --------------------------------------------------------------------------
     * This is an array of cache engine alias' and class names. Only engines
     * that are listed here are allowed to be used.
     */
    public array $validHandlers = [
        'dummy'     => \CodeIgniter\Cache\Handlers\DummyHandler::class,
        'file'      => \CodeIgniter\Cache\Handlers\FileHandler::class,
        'memcached' => \CodeIgniter\Cache\Handlers\MemcachedHandler::class,
        'predis'    => \CodeIgniter\Cache\Handlers\PredisHandler::class,
        'redis'     => \CodeIgniter\Cache\Handlers\RedisHandler::class,
        'wincache'  => \CodeIgniter\Cache\Handlers\WincacheHandler::class,
    ];

    /**
     * --------------------------------------------------------------------------
     * Web Page Caching: Cache Directory
     * --------------------------------------------------------------------------
     *
     * For full-page caching, where should the cache files be stored?
     * This can be either a full or relative path to the directory where you
     * would like the cache files stored. It must be writable by the
     * web server.
     *
     * If you are using the FileHandler the path can contain subdirectories.
     */
    public string $cacheDir = WRITEPATH . 'cache/page/';

    /**
     * --------------------------------------------------------------------------
     * JDIH Specific Cache Settings
     * --------------------------------------------------------------------------
     * Custom cache durations for different types of data
     */
    public array $jdihCacheDurations = [
        // Static data (rarely changes)
        'jenis_peraturan' => 7200,    // 2 hours
        'status_dokumen' => 7200,     // 2 hours
        'global_data' => 3600,        // 1 hour

        // Dynamic data (changes more frequently)
        'visitor_stats' => 300,       // 5 minutes
        'homepage_data' => 600,       // 10 minutes
        'peraturan_list' => 300,      // 5 minutes
        'berita_list' => 900,         // 15 minutes

        // Counts and statistics
        'kategori_counts' => 3600,    // 1 hour
        'tahun_counts' => 3600,       // 1 hour
        'jenis_counts' => 1800,       // 30 minutes
        'popular_tags' => 1800,       // 30 minutes
        'popular_keywords' => 900,    // 15 minutes - lebih pendek agar keyword baru cepat muncul

        // Search results
        'search_results' => 600,      // 10 minutes

        // Heavy queries
        'statistik_data' => 1800,     // 30 minutes
        'agenda_calendar' => 900,     // 15 minutes
    ];

    /**
     * --------------------------------------------------------------------------
     * Cache Tags for Easy Invalidation
     * --------------------------------------------------------------------------
     * Define cache tags for easy cache invalidation
     */
    public array $cacheTags = [
        'peraturan' => [
            'homepage_data',
            'peraturan_list',
            'kategori_counts',
            'jenis_counts',
            'tahun_counts',
            'popular_tags',
            'statistik_data'
        ],
        'berita' => [
            'homepage_data',
            'berita_list'
        ],
        'agenda' => [
            'homepage_data',
            'agenda_calendar'
        ],
        'visitor' => [
            'visitor_stats',
            'global_data'
        ],
        'master_data' => [
            'jenis_peraturan',
            'status_dokumen',
            'global_data'
        ]
    ];
}
