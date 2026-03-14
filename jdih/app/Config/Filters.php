<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
	// Makes reading things below nicer,
	// and simpler to change out script that's used.
	public $aliases = [
		'csrf' => \CodeIgniter\Filters\CSRF::class,
		'toolbar' => \CodeIgniter\Filters\DebugToolbar::class,
		'honeypot' => \CodeIgniter\Filters\Honeypot::class,
		'invalidchars' => \CodeIgniter\Filters\InvalidChars::class,
		'secureheaders' => \CodeIgniter\Filters\SecureHeaders::class,
		'auth' => \App\Filters\AuthFilter::class,
		'bootstrap' => \App\Filters\Bootstrap::class,
		// DISABLED TEMPORARILY - causing user_session table error
		'session_validation' => \App\Filters\SessionValidationFilter::class,
		'disable_cache_logged_in' => \App\Filters\DisableCacheForLoggedInUsers::class,
		'disable_cache_search' => \App\Filters\DisableCacheForSearch::class,
	];

	// Always applied before every request
	public $globals = [
		'before' => [
			'bootstrap', // Must run first to set session properly
			'disable_cache_logged_in', // CRITICAL: Disable cache for logged-in users BEFORE cache check
			'disable_cache_search', // CRITICAL: Disable cache for search with filters BEFORE cache check
			'csrf' => ['except' => [
				'api/*',
				'integrasiJDIH/*',
				'jdih/integrasiJDIH/*',
				'builtin/*',
				'login',
				'logout',
				'register',
				'register/resendlink',
				'webjdih/login',
				'webjdih/logout',
				'webjdih/register',
				'webjdih/register/resendlink',
				'*/ajaxUploadFile',
				'filepicker/ajaxUploadFile',
				'data_peraturan/ajax_list',
				'data_peraturan/ajaxGetPeraturan',
				'data_peraturan/ajaxGetInstansi',
				'data_peraturan/ajaxGetTag',
				'data_peraturan/add_tag_ajax',
				'data_peraturan/ajaxSearchPeraturan',
				'data_peraturan/ajaxGetJenisRelasiInfo',
				'data_peraturan/getLampiranDataDT',
				// Add dash version for compatibility
				'data-peraturan/ajax_list',
				'data-peraturan/ajaxGetPeraturan',
				'data-peraturan/ajaxGetInstansi',
				'data-peraturan/ajaxGetTag',
				'data-peraturan/add_tag_ajax',
				'data-peraturan/ajaxSearchPeraturan',
				'data-peraturan/ajaxGetJenisRelasiInfo',
				'data-peraturan/getLampiranDataDT',
				// Harmonisasi AJAX endpoints
				'harmonisasi/ajax',
				'harmonisasi/hasilAjax',
				'harmonisasi/pending-drafts',
				'harmonisasi/bulk-submit',
				'ajax/harmonisasi*',
				// API endpoints for tags
				'api/tags/*'
			]],
			'honeypot',
		],
		'after' => [
			'toolbar',
			'honeypot',
			'secureheaders',
		],
	];

	// Works on all of a particular HTTP method
	// (GET, POST, etc) as BEFORE filters only
	//     like: 'post' => ['CSRF', 'throttle'],
	public $methods = [];

	// List filter aliases and any before/after uri patterns
	// that they should run on, like:
	//    'isLoggedIn' => ['before' => ['account/*', 'profiles/*']],
	public $filters = [];
}
