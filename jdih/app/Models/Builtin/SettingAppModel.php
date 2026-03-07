<?php
namespace App\Models\Builtin;

class SettingAppModel extends \App\Models\BaseModel
{
	public function getSettingAplikasi() {
		$sql = 'SELECT * FROM setting WHERE type="app"';
		$query = $this->db->query($sql)->getResultArray();
		return $query;
	}
	
	public function getUserSetting() {
		$sql = 'SELECT * FROM setting_user WHERE id_user = ? AND type="layout"';
		$data = $this->db->query($sql, $_SESSION['user']['id_user'])
					->getResultArray();
		return $data;
	}
	
	public function saveData($files = [])
    {
        helper(['util', 'upload_file']);

        $sql = 'SELECT * FROM setting WHERE type="app"';
        $query = $this->db->query($sql)->getResultArray();

        foreach ($query as $val) {
            $curr_db[$val['param']] = $val['value'];
        }

        $path = ROOTPATH . 'images/';
        $result = ['status' => 'error', 'message' => 'Unknown error'];

        // Define file inputs and their corresponding DB fields
        $file_inputs = [
            'logo_login' => 'logo_login',
            'logo_app' => 'logo_app',
            'favicon' => 'favicon',
            'logo_register' => 'logo_register'
        ];

        $new_file_names = [];

        foreach ($file_inputs as $input_name => $db_field) {
            $file = $files[$input_name] ?? null;
            $new_file_names[$db_field] = $curr_db[$db_field]; // Keep old file name by default

            if ($file && $file->isValid() && !$file->hasMoved()) {
                // Delete old file
                if ($curr_db[$db_field] && file_exists($path . $curr_db[$db_field])) {
                    if (!delete_file($path . $curr_db[$db_field])) {
                        $result['message'] = 'Gagal menghapus gambar lama: ' . $curr_db[$db_field];
                        return $result;
                    }
                }
                // Upload new file
                $new_name = upload_file($path, $file);
                if ($new_name) {
                    $new_file_names[$db_field] = $new_name;
                } else {
                    $result['message'] = 'Gagal mengunggah ' . $input_name;
                    return $result;
                }
            }
        }

        $data_db = [
            ['type' => 'app', 'param' => 'logo_login', 'value' => $new_file_names['logo_login']],
            ['type' => 'app', 'param' => 'logo_app', 'value' => $new_file_names['logo_app']],
            ['type' => 'app', 'param' => 'footer_login', 'value' => htmlentities($_POST['footer_login'])],
            ['type' => 'app', 'param' => 'btn_login', 'value' => $_POST['btn_login']],
            ['type' => 'app', 'param' => 'footer_app', 'value' => htmlentities($_POST['footer_app'])],
            ['type' => 'app', 'param' => 'background_logo', 'value' => $_POST['background_logo']],
            ['type' => 'app', 'param' => 'judul_web', 'value' => $_POST['judul_web']],
            ['type' => 'app', 'param' => 'deskripsi_web', 'value' => $_POST['deskripsi_web']],
            ['type' => 'app', 'param' => 'favicon', 'value' => $new_file_names['favicon']],
            ['type' => 'app', 'param' => 'logo_register', 'value' => $new_file_names['logo_register']]
        ];

        $this->db->transStart();
        $this->db->table('setting')->delete(['type' => 'app']);
        $this->db->table('setting')->insertBatch($data_db);
        $this->db->transComplete();

        if ($this->db->transStatus()) {
            $file_name = ROOTPATH . 'themes/modern/builtin/css/login-header.css';
            $css = '.login-header {background-color: ' . $_POST['background_logo'] . ';}.edit-logo-login-container {background: ' . $_POST['background_logo'] . ';}';

            if (file_exists($file_name)) {
                file_put_contents($file_name, $css);
            }

            $result['status'] = 'ok';
            $result['message'] = 'Data berhasil disimpan';
        } else {
            $result['status'] = 'error';
            $result['message'] = 'Data gagal disimpan ke database';
        }

        return $result;
    }
}
?>