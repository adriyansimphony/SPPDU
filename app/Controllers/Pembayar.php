<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\PembayarModel;

class Pembayar extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PembayarModel;	
		$this->data['site_title'] = 'Pembayar';

		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pembayar.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$this->data['jml_data'] = $this->model->getJmlData();
		$this->view('pembayar-result.php', $this->data);
	}
	
	public function ajaxDeleteData() {

		$result = $this->model->deleteData();
		if ($result) {
			$result = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data gagal dihapus'];
		}
		echo json_encode($result);
	}
	
	public function ajaxDeleteAllData() {

		$result = $this->model->deleteAllData();
		// $result = true;
		if ($result) {
			$result = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data gagal dihapus'];
		}
		
		echo json_encode($result);
	}
	
	public function ajaxGetFormData() {
		
		if (isset($_GET['id'])) {
			if ($_GET['id']) {
				$this->data['pembayar'] = $this->model->getPembayarById($_GET['id']);
				if (!$this->data['pembayar'])
					return;
			}
		}
		
		echo view('themes/modern/pembayar-form.php', $this->data);
	}
	
	public function ajaxSaveData() {
		
		$form_errors = $this->validateForm();
	
		if ($form_errors) {
			$message['status'] = 'error';
			$message['message'] = $form_errors;
		} else {
			$message = $this->model->saveData();
		}

		echo json_encode($message);
	}
	
	private function validateForm() {
	
		$validation =  \Config\Services::validation();
		$validation->setRule('nama_pembayar', 'Nama', 'trim|required');
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();
		
		return $form_errors;
	}
	
	public function getDataDT() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllData();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListData();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['ignore_urut'] = $no;
			$val['ignore_action'] = '<div class="form-inline btn-action-group">'
										. btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_pembayar']]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_pembayar']
																	, 'data-delete-title' => 'Hapus data pembayar : <strong>' . $val['nama_pembayar'] . '</strong>'
																]
													, 'label' => 'Delete'
												]) . 
										
										'</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
