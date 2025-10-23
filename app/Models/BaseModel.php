<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Models;
use App\Libraries\Auth;

class BaseModel extends \CodeIgniter\Model 
{
	protected $request;
	protected $session;
	private $auth;
	protected $pegawai;
	
	public function __construct() {
		parent::__construct();
		
		$this->request = \Config\Services::request();
		$this->session = \Config\Services::session();
		$pegawai = $this->session->get('pegawai');
		if ($pegawai)
			$this->pegawai = $this->getPegawaiById($pegawai['id_pegawai']);
		
		$this->auth = new \App\Libraries\Auth;
	}
	
	public function checkRememberme() 
	{
		if ($this->session->get('logged_in')) 
		{
			return true; 
		}
		
		helper('cookie');
		$cookie_login = get_cookie('remember');
	
		if ($cookie_login) 
		{
			list($selector, $cookie_token) = explode(':', $cookie_login);

			$sql = 'SELECT * FROM pegawai_token WHERE selector = ?';		
			$data = $this->db->query($sql, $selector)->getRowArray();
			
			if ($this->auth->validateToken($cookie_token, @$data['token'])) {
				
				if ($data['expires'] > date('Y-m-d H:i:s')) 
				{
					$pegawai_detail = $this->getPegawaiById($data['id_pegawai']);
					$this->session->set('pegawai', $pegawai_detail);
					$this->session->set('logged_in', true);
				}
			}
		}
		
		return false;
	}
	
	public function getPegawaiById($id_pegawai = null, $array = false) {
		
		if (!$id_pegawai) {
			if (!$this->pegawai) {
				return false;
			}
			$id_pegawai = $this->pegawai['id_pegawai'];
		}
		
		$query = $this->db->query('SELECT * FROM pegawai WHERE id_pegawai = ?', [$id_pegawai]);
		$pegawai = $query->getRowArray();
		
		if (!$pegawai) {
			return;
		}
		
		$pegawai['role'] = [];
		$query = $this->db->query('SELECT * FROM pegawai_role 
								LEFT JOIN role USING(id_role) 
								LEFT JOIN module USING(id_module) 
								WHERE id_pegawai = ? 
								ORDER BY  nama_role', [$id_pegawai]
							);
							
		$result = $query->getResultArray();
		if ($result) {
			foreach ($result as $val) {
				$pegawai['role'][$val['id_role']] = $val;
			}
		}
				
		$query = $this->db->query('SELECT * FROM module WHERE id_module = ?', [$pegawai['default_page_id_module']]);
		$pegawai['default_module'] = $query->getRowArray();
		
		return $pegawai;
	}
	
	public function getPegawaiSetting() {
		
		$result = $this->db->query('SELECT * FROM setting_pegawai WHERE id_pegawai = ? AND type = "layout"', [$this->session->get('pegawai')['id_pegawai']])
						->getRow();
		
		if (!$result) {
			$query = $this->db->query('SELECT * FROM setting WHERE type="layout"')
						->getResultArray();
			
			foreach ($query as $val) {
				$data[$val['param']] = $val['value'];
			}
			
			$result = new \StdClass;
			$result->param = json_encode($data);
		}
		return $result;
	}
	
	public function getAppLayoutSetting() {
		$result = $this->db->query('SELECT * FROM setting WHERE type="layout"')->getResultArray();
		return $result;
	}
	
	public function getDefaultPegawaiModule() {
		
		$where_role = $_SESSION['pegawai']['role'] ? join(',', array_keys($_SESSION['pegawai']['role'])) : 'null';
		$query = $this->db->query('SELECT * 
							FROM role 
							LEFT JOIN module USING(id_module)
							WHERE id_role IN (' . $where_role . ')'
							)
						->getRow();
		return $query;
	}
	
	public function getModule($nama_module) {
		$result = $this->db->query('SELECT * FROM module LEFT JOIN module_status USING(id_module_status) WHERE nama_module = ?', [$nama_module])
						->getRowArray();
		return $result;
	}
	
	public function getMenu($current_module = '') {
		
		/* $sql = 'SELECT * FROM menu_kategori WHERE aktif = "Y" ORDER BY urut';
		
		$sql = 'SELECT * FROM menu 
					LEFT JOIN menu_role USING (id_menu)
					LEFT JOIN module USING (id_module)
				WHERE aktif = 1 AND ( id_role IN ( ' . join(',', array_keys($_SESSION['pegawai']['role'])) . ') )
				ORDER BY urut'; */
		
		// Menu
		$where_role = $_SESSION['pegawai']['role'] ? join(',', array_keys($_SESSION['pegawai']['role'])) : 'null';
		$sql = 'SELECT * FROM menu 
					LEFT JOIN menu_role USING (id_menu) 
					LEFT JOIN module USING (id_module)
					LEFT JOIN menu_kategori USING(id_menu_kategori)
				WHERE menu_kategori.aktif = "Y" AND id_role IN ( ' . $where_role . ')
				ORDER BY menu_kategori.urut, menu.urut';
						
		$query_result = $this->db->query($sql)->getResultArray();
		
		$current_id = '';
		$menu = [];
		foreach ($query_result as $val) 
		{
			$menu[$val['id_menu']] = $val;
			$menu[$val['id_menu']]['highlight'] = 0;
			$menu[$val['id_menu']]['depth'] = 0;

			if ($current_module == $val['nama_module']) {
				
				$current_id = $val['id_menu'];
				$menu[$val['id_menu']]['highlight'] = 1;
			}
			
		}
	
		if ($current_id) {
			$this->menuCurrent($menu, $current_id);
		}
		
		$menu_kategori = [];
		foreach ($menu as $id_menu => $val) {
			if (!$id_menu)
				continue;
			
			$menu_kategori[$val['id_menu_kategori']][$val['id_menu']] = $val;
		}
		
		// Kategori
		$sql = 'SELECT * FROM menu_kategori WHERE aktif = "Y" ORDER BY urut';
		$query_result = $this->db->query($sql)->getResultArray();
		$result = [];
		foreach ($query_result as $val) {
			if (key_exists($val['id_menu_kategori'], $menu_kategori)) {
				$result[$val['id_menu_kategori']] = [ 'kategori' => $val, 'menu' => $menu_kategori[$val['id_menu_kategori']] ];
			}
		}		
		// echo '<pre>'; print_r($result); die;
		return $result;
	}
	
	// Highlight child and parent
	private function menuCurrent( &$result, $current_id) 
	{
		$parent = $result[$current_id]['id_parent'];

		$result[$parent]['highlight'] = 1; // Highlight menu parent
		if (@$result[$parent]['id_parent']) {
			$this->menuCurrent($result, $parent);
		}
	}
	
	public function getModulePermission($id_module) {
		$sql = 'SELECT * FROM module_permission LEFT JOIN role_module_permission USING (id_module_permission) WHERE id_module = ?';
		
		$result = $this->db->query($sql, [$id_module])->getResultArray();
		return $result;
	}
	
	public function getAllModulePermission($id_pegawai) {
		$sql = 'SELECT * FROM role_module_permission
				LEFT JOIN module_permission USING(id_module_permission)
				LEFT JOIN module USING(id_module)
				LEFT JOIN pegawai_role USING(id_role)
				WHERE id_pegawai = ?';
						
		$result = $this->db->query($sql, $id_pegawai)->getResultArray();
		return $result;
	}
	
	/* public function getModuleRole($id_module) {
		 $result = $this->db->query('SELECT * FROM module_role WHERE id_module = ? ', $id_module)->getResultArray();
		 return $result;
	} */

	public function validateFormToken($session_name = null, $post_name = 'form_token') {				

		$form_token = explode (':', $this->request->getPost($post_name));
		
		$form_selector = $form_token[0];
		$sess_token = $this->session->get('token');
		if ($session_name)
			$sess_token = $sess_token[$session_name];
	
		if (!key_exists($form_selector, $sess_token))
				return false;
		
		try {
			$equal = $this->auth->validateToken($sess_token[$form_selector], $form_token[1]);

			return $equal;
		} catch (\Exception $e) {
			return false;
		}
		
		return false;
	}
	
	// For role check BaseController->cekHakAkses
	public function getDataById($table, $column, $id) {
		$sql = 'SELECT * FROM ' . $table . ' WHERE ' . $column . ' = ?';
		return $this->db->query($sql, $id)->getResultArray();
	}
	
	public function checkPegawai($nip_pegawai) 
	{
		$query = $this->db->query('SELECT * FROM pegawai WHERE nip_pegawai = ?', [$nip_pegawai]);
		$pegawai = $query->getRowArray();
		
		if (!$pegawai)
			return;
		
		$pegawai = $this->getPegawaiById($pegawai['id_pegawai']);
		return $pegawai;
	}
	
	public function getSettingAplikasi() {
		$sql = 'SELECT * FROM setting WHERE type="app"';
		$query = $this->db->query($sql)->getResultArray();
		
		foreach($query as $val) {
			$settingAplikasi[$val['param']] = $val['value'];
		}
		return $settingAplikasi;
	}
	
	public function getSettingRegistrasi() {
		$sql = 'SELECT * FROM setting WHERE type="register"';
		$query = $this->db->query($sql)->getResultArray();
		foreach($query as $val) {
			$setting_register[$val['param']] = $val['value'];
		}
		return $setting_register;
	}
	
	public function getSetting($type) {
		$sql = 'SELECT * FROM setting WHERE type = ?'; 
		$result = $this->db->query($sql, $type)->getResultArray();
		$setting = [];
		foreach ($result as $val) {
			$setting[$val['param']] = $val['value'];
		}
		return $setting;
	}
	
	public function resetAutoIncrement($table) {
		$sql = 'SELECT COUNT(*) AS jml FROM ' . $table;
		$data = $this->db->query($sql)->getRowArray();
		$new_increment = $data ? $data['jml'] + 1 : 1;
		$sql = 'ALTER TABLE ' . $table . ' AUTO_INCREMENT ' . $new_increment;
		$this->db->query($sql);
		return $this->db->affectedRows();
	}
}