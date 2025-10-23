<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\UbahStatusSiswaModel;

class Ubah_status_siswa extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();

		$this->model = new UbahStatusSiswaModel;	
		$this->data['site_title'] = 'Ubah Status Siswa';
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/themes/material_blue.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/ubah-status-siswa.js');
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
		
		$result = $this->model->getAllKelas();
		if ($result) {
			foreach ($result as $val) {
				$option_kelas[$val['id_kelas']] = 'Kelas ' . $val['nama_kelas'];
			}
		}
		
		$result = $this->model->getAllTahunAjaran();
		if ($result) {
			foreach ($result as $val) {
				$tahun_ajaran[$val['id_tahun_ajaran']] = $val['tahun_ajaran'];
			}
		}
		
		$result = $this->model->getAllStatusSiswa();
		if ($result) {
			foreach ($result as $val) {
				$status_siswa[$val['id_status_siswa']] = $val['nama_status_siswa'];
			}
		}
		
		$this->data['tahun_ajaran'] = $tahun_ajaran;
		$this->data['option_kelas'] = $option_kelas;
		$this->data['status_siswa'] = $status_siswa;
		$this->data['title'] = 'Ubah Status Siswa';
		$this->view('ubah-status-siswa-form.php', $this->data);
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
	
	public function ajaxGetSiswaByIdKelas() {
		$result = $this->model->getSiswaByIdKelas($_GET['id']);
		echo json_encode($result);
	}
	
	public function ajaxDeleteAllSiswa() {
		$result = $this->model->deleteAllSiswa();
		echo json_encode($result);
	}
	
	public function ajaxSaveData() 
	{
		if (empty($_POST['id_siswa'])) {
			$result = ['status' => 'error', 'message' => 'Siswa belum dipilih'];
		} else if (empty($_POST['id_kelas_tujuan'])) {
			$result = ['status' => 'error', 'message' => 'Kelas tujuan belum dipilih'];
		} else {
			$update = $this->model->saveData();
			if ($update) {
				$result = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
			} else {
				$result = ['status' => 'error', 'message' => 'Data gagal disimpan'];
			}
		}
		echo json_encode($result);
	}
}