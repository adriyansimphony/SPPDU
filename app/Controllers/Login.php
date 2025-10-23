<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\Builtin\LoginModel;
use \Config\App;
use App\Libraries\Auth;

class Login extends \App\Controllers\BaseController
{
	protected $model = '';
	
	public function __construct() {
		parent::__construct();
		$this->model = new LoginModel;	
		$this->data['site_title'] = 'Login ke akun Anda';
		
		helper(['cookie', 'form']);
	}
	
	public function index()
	{
		$this->mustNotLoggedIn();
		$this->data['status'] = '';
		if ($this->request->getPost('password')) {
			
			$this->login();
			if ($this->session->get('logged_in')) {
				return redirect()->to($this->config->baseURL);
			}
		}
		
		$query = $this->model->getSettingRegistrasi();
		foreach($query as $val) {
			$this->data['setting_registrasi'][$val['param']] = $val['value'];
		}
		
		csrf_settoken();
		$this->data['style'] = ' style="max-width:375px"';
		return view('themes/modern/builtin/login', $this->data);
	}
	
	private function login()
	{
		// Check Token
		$validation_message = csrf_validation();

		// Cek CSRF token
		if ($validation_message) {
			$this->data['status'] = 'error';
			$this->data['message'] = $validation_message['message'];
			return;
		}
		
		$error = false;
		$pegawai = $this->model->checkPegawai($this->request->getPost('nip_pegawai'));
		if ($pegawai) {
			if ($pegawai['verified'] == 0) {
				$message = 'Pegawai belum aktif';
				$error = true;
			}
			
			if ($pegawai['status'] != 'active') {
				$message = 'Status akun Anda ' . ucfirst($pegawai['status']);
				$error = true;
			}
			
			if (!$error) {
				if (!password_verify($this->request->getPost('password'), $pegawai['password'])) {
					$message = 'NIP Pegawai dan/atau Password tidak cocok';
					$error = true;
				}
			}
			
		} else {
			$message = 'Pegawai tidak ditemukan';
			$error = true;
		}
		
		if ($error)
		{
			$this->data['status'] = 'error';
			$this->data['message'] = $message;
			return;
		}
		
		if ($this->request->getPost('remember')) 
		{
			$this->model->setPegawaiToken($pegawai);
		}

		$this->session->set('pegawai', $pegawai);
		$this->session->set('logged_in', true);
		$this->model->recordLogin();
	}
	
	public function refreshLoginData() 
	{
		$email = $this->session->get('pegawai')['email'];
		$result = $this->model->checkPegawai($email);
		$this->session->set('pegawai', $result);
	}
	
	public function logout() 
	{
		$pegawai = $this->session->get('pegawai');
		if ($pegawai) {
			$this->model->deleteAuthCookie($this->session->get('pegawai')['id_pegawai']);
		}
		$this->session->destroy();
		// $this->session->stop();
		header('location: ' . $this->config->baseURL . 'login');
		exit;
		// return redirect()->to($this->config->baseURL . 'login');
		// exit;
		// return redirect()->to($this->config->baseURL . 'login');
	}
}
