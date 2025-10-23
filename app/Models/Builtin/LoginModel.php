<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Models\Builtin;
use App\Libraries\Auth;

class LoginModel extends \App\Models\BaseModel
{	
	public function recordLogin() 
	{
		$nip_pegawai = $this->request->getPost('nip_pegawai'); 
		$data_pegawai = $this->db->query('SELECT id_pegawai 
									FROM pegawai
									WHERE nip_pegawai = ?', [$nip_pegawai]
								)
							->getRow();

		$data = array('id_pegawai' => $data_pegawai->id_pegawai
					, 'id_activity' => 1
					, 'time' => date('Y-m-d H:i:s')
				);
		
		$this->db->table('pegawai_login_activity')->insert($data);
	}
	
	public function setPegawaiToken($pegawai) 
	{
		$auth = new Auth;
		$token = $auth->generateDbToken();
		$expired_time = time() + (7*24*3600); // 7 day
		setcookie('remember', $token['selector'] . ':' . $token['external'], $expired_time, '/');
		
		$data_db = array ( 'id_pegawai' => $pegawai['id_pegawai']
						, 'selector' => $token['selector']
						, 'token' => $token['db']
						, 'action' => 'remember'
						, 'created' => date('Y-m-d H:i:s')
						, 'expires' => date('Y-m-d H:i:s', $expired_time)
					);

		$this->db->table('pegawai_token')->insert($data_db);
	}
	
	public function deleteAuthCookie($id_pegawai) 
	{
		$this->db->table('pegawai_token')->delete(['action' => 'remember', 'id_pegawai' => $id_pegawai]);
		setcookie('remember', '', time() - 360000, '/');	
	}
	
	public function getSettingRegistrasi() 
	{
		$sql = 'SELECT * FROM setting WHERE type="register"';
		$query = $this->db->query($sql)->getResultArray();
		
		return $query;
	}
	
	/* See base model
	public function checkPegawai($nip_pegawai) 
	{
		
	} */
}
?>