<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\IdentitasSekolahModel;

class Identitas_sekolah extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();

		$this->model = new IdentitasSekolahModel;	
		$this->data['site_title'] = 'Identitas Sekolah';
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/file-upload.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/wilayah.js');
	}
	
	public function index() 
	{		
		if (!empty($_POST['submit'])) 
		{
			$form_errors = $this->validateForm();
			
			if ($form_errors) {
				$this->data['message'] = ['status' => 'error', 'message' => $form_errors];
			} else {
				
				if (!$this->hasPermission('update_all'))
				{
					$this->data['message'] = ['status' => 'error', 'message' => 'Role anda tidak diperbolehkan melakukan perubahan'];
				} else {
					$result = $this->model->saveData();
					$this->data['message'] = ['status' => $result['status'], 'message' => $result['message']];
				}
			}
		}
		
		$sekolah = $this->model->getIdentitasSekolah();
		$this->setData($sekolah['id_wilayah_kelurahan']);
		$this->data['sekolah'] = $sekolah;
		$this->data['title'] = 'Identitas Sekolah';
		$this->view('identitas-sekolah-form.php', $this->data);
	}
	
	private function setData($id_wilayah_kelurahan = null) {
		
		$wilayah = new \App\Controllers\Wilayah();
		$data_wilayah = $wilayah->getDataWilayah($id_wilayah_kelurahan);
		$this->data = array_merge($this->data, $data_wilayah);
		return $data_wilayah;
	}

	private function validateForm() 
	{
		$validation =  \Config\Services::validation();		
		$validation->setRule('nama_sekolah', 'Nama Sekolah', 'trim|required');
		$validation->setRule('alamat_sekolah', 'Alamat Sekolah', 'trim|required');
		$validation->setRule('id_wilayah_kelurahan', 'Wilayah', 'trim|required');
		
		$validation->withRequest($this->request)
					->run();
		$form_errors =  $validation->getErrors();
		return $form_errors;
	}	
}