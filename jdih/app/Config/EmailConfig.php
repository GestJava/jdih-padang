<?php
namespace Config;

class EmailConfig {
	
	public $provider = 'Standard';
	// public $provider = 'Google';
	// public $provider = 'AmazonSES';

	public $client;
	public $from;
	public $fromTitle;
	public $emailSupport;
	
	public function __construct()
	{
		// Initialize client array with environment variables
		$this->client = [
			'standard' => [
				'host' => getenv('email.host') ?: 'mail.jagowebdev.com',
				'username' => getenv('email.username') ?: 'support@jagowebdev.com',
				'password' => getenv('email.password') ?: 'Password'
			],
			'google' => [
				'client_id' => '',
				'client_secret' => '',
				'refresh_token' => ''
			],
			'ses' => [
				'username' => '',
				'password' => ''
			]
		];
		
		// Disesuaikan dengan konfigurasi username
		$this->from = getenv('email.from') ?: 'support@jagowebdev.com';
		$this->fromTitle = getenv('email.fromTitle') ?: 'Jagowebdev.com';
		$this->emailSupport = getenv('email.from') ?: 'support@jagowebdev.com';
	}
}