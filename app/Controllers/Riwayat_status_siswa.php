<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\RiwayatStatusSiswaModel;

class Riwayat_status_siswa extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new RiwayatStatusSiswaModel;	
		$this->data['site_title'] = 'Riwayat Status Siswa';
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/themes/material_blue.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/riwayat-status-siswa.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		$result = $this->model->getAllStatusSiswa();
		$option_status = ['' => 'Semua Status'];
		foreach ($result as $val) {
			$option_status[$val['id_status_siswa']] = $val['nama_status_siswa'];
		}

		$this->data['jml_status_siswa'] = $this->model->getJmlStatusSiswa();
		$this->data['option_status'] = $option_status;
		$this->view('riwayat-status-siswa-result.php', $this->data);
	}
	
	public function generateExcel($output) 
	{
		$filepath = $this->model->writeExcel();
		$filename = 'Daftar Siswa.xlsx';
		
		switch ($output) {
			case 'raw':
				$content = file_get_contents($filepath);
				echo $content;
				delete_file($filepath);
				break;
			case 'file':
				return $filepath;
				break;
			default:
				header('Content-disposition: attachment; filename="'. $filename .'"');
				header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
				header('Content-Transfer-Encoding: binary');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');  
				$content = file_get_contents($filepath);
				delete_file($filepath);
				echo $content;
		}
		exit;
	}
	
	public function ajaxExportExcel() 
	{
		$output = '';
		if (@$_GET['ajax'] == 'true') {
			$output = 'raw';
		}
		$this->generateExcel($output); 
	}
		
	public function edit()
	{
		$this->hasPermissionPrefix('update');
		$this->data['title'] = 'Edit Riwayat Status Siswa';
		
		// Submit
		$data['message'] = [];
		if (isset($_POST['submit'])) 
		{
			$form_errors = false;
							
			if ($form_errors) {
				$data['message']['status'] = 'error';
				$data['message']['message'] = $form_errors;
			} else {
				
				// $query = false;
				$message = $this->model->saveData();
				$data['message'] = $message;
			}
		}
		
		$data_riwayat = $this->model->getRiwayatSiswaById($_GET['id']);
		if (empty($data_riwayat)) {
			$this->errorDataNotFound();
			return;
		}
			
		$result = $this->model->getAllStatusSiswa();
		foreach ($result as $val) {
			$option_status[$val['id_status_siswa']] = $val['nama_status_siswa'];
		}
		
		$result = $this->model->getAllKelas();
		foreach ($result as $val) {
			$option_kelas[$val['id_kelas']] = $val['nama_kelas'];
		}
		
		$result = $this->model->getAllTahunAjaran();
		foreach ($result as $val) {
			$tahun_ajaran[$val['id_tahun_ajaran']] = $val['tahun_ajaran'];
		}
		
		$this->data['breadcrumb']['Edit'] = '';
		$this->data['action'] = 'edit';
		$this->data['riwayat'] = $data_riwayat;
		$this->data['option_status'] = $option_status;
		$this->data['option_kelas'] = $option_kelas;
		$this->data['tahun_ajaran'] = $tahun_ajaran;
		$this->view('riwayat-status-siswa-form.php', $this->data);
	}
	
	public function ajaxSaveData() {
		$message = $this->model->saveData();
		echo json_encode($message);
	}
	
	public function ajaxDeleteData() {
		$result = $this->model->deleteData();
		
		if ($result) {
			$message = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			echo json_encode($message);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Data gagal dihapus']);
		}
	}
	
	public function ajaxDeleteAllStatus() {
		$result = $this->model->deleteAllStatus();
		echo json_encode($result);
	}
	
	public function getDataDT() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllData( $this->whereOwn() );
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListData( $this->whereOwn() );
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['ignore_urut'] = $no;
			$exp = explode('-', $val['tgl_status']);
			$val['tgl_status'] = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
			$val['ignore_action'] = '<div class="form-inline btn-action-group">'
										. btn_link(
												['icon' => 'fas fa-edit'
													, 'url' => base_url() . '/riwayat-status-siswa/edit?id=' . $val['id_siswa_riwayat_status']
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_siswa_riwayat_status']]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_siswa_riwayat_status']
																	, 'data-delete-title' => 'Hapus data riwayat status siswa : <strong>' . $val['nama'] . '</strong> ?'
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
