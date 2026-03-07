<?php

/**
 *	App Name	: Admin Template Codeigniter 4	
 *	Author		: Agus Prawoto Hadi
 *	Website		: https://jagowebdev.com
 *	Year		: 2020-2023
 */

namespace App\Models\Builtin;

use App\Libraries\Auth;

class LoginModel extends \App\Models\BaseModel
{
	public function recordLogin()
	{
		$username = $this->request->getPost('username');
		$data_user = $this->db->query(
			'SELECT id_user 
									FROM user
									WHERE username = ?',
			[$username]
		)
			->getRow();

		$data = array(
			'id_user' => $data_user->id_user,
			'id_activity' => 1,
			'time' => date('Y-m-d H:i:s')
		);

		$this->db->table('user_login_activity')->insert($data);
	}

	public function setUserToken($user)
	{
		$auth = new Auth;
		$token = $auth->generateDbToken();
		$expired_time = time() + (7 * 24 * 3600); // 7 day
		setcookie('remember', $token['selector'] . ':' . $token['external'], $expired_time, '/');

		$data_db = array(
			'id_user' => $user['id_user'],
			'selector' => $token['selector'],
			'token' => $token['db'],
			'action' => 'remember',
			'created' => date('Y-m-d H:i:s'),
			'expires' => date('Y-m-d H:i:s', $expired_time)
		);

		$this->db->table('user_token')->insert($data_db);
	}

	public function deleteAuthCookie($id_user)
	{
		try {
			// Use query builder with limit for better performance
			$this->db->table('user_token')
				->where('action', 'remember')
				->where('id_user', $id_user)
				->limit(10) // Limit to prevent long-running queries
				->delete();
		} catch (\Exception $e) {
			// Log error but don't throw exception to prevent blocking logout
			log_message('warning', 'Failed to delete user tokens: ' . $e->getMessage());
		}

		// Clear cookie regardless of database operation result
		setcookie('remember', '', time() - 360000, '/');
	}

	public function getSettingRegistrasi()
	{
		$sql = 'SELECT * FROM setting WHERE type="register"';
		$query = $this->db->query($sql)->getResultArray();

		return $query;
	}

	/* See base model
	public function checkUser($username) 
	{
		
	} */
}
