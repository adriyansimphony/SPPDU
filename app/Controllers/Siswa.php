<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\SiswaModel;

class Siswa extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new SiswaModel;	
		$this->data['site_title'] = 'Siswa';
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
				
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/bootstrap-datepicker/js/bootstrap-datepicker.js' );
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/date-picker.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/file-upload.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/siswa.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/bootstrap-datepicker/css/bootstrap-datepicker3.css');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/wilayah.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$data = $this->data;
		$option_kelas = ['' => 'Semua'];
		$result = $this->model->getKelas();
		if ($result) {
			foreach ($result as $val) {
				$option_kelas[$val['id_kelas']] = 'Kelas ' . $val['nama_kelas'];
			}
		}
		$data['option_kelas'] = $option_kelas;
		$data['jml_siswa'] = $this->model->getJmlSiswa();
		$this->view('siswa-result.php', $data);
	}
	
	public function ajaxGetFormStatus() 
	{
		$this->hasPermissionPrefix('update');
		$this->data['title'] = 'Edit Riwayat Status Siswa';
		
		$data_riwayat = [];
		if (!empty($_GET['id'])) {
			$data_riwayat = $this->model->getRiwayatSiswaById($_GET['id']);
			if (empty($data_riwayat)) {
				$this->errorDataNotFound();
				return;
			}
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
		echo view('themes/modern/siswa-form-status.php', $this->data);
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
	
	public function add() 
	{
		$this->setData();
		$data = $this->data;
		
		$data['title'] = 'Tambah Data Siswa';
		$data['breadcrumb']['Add'] = '';
		$data['action'] = 'add';
		
		$data['message'] = [];
		if (isset($_POST['submit'])) 
		{
			$form_errors = false;
							
			if ($form_errors) {
				$data['message']['status'] = 'error';
				$data['message']['message'] = $form_errors;
			} else {
				
				$message = $this->model->saveData();
				$data['message'] = $message;
				$data['breadcrumb']['Edit'] = '';
				$data_siswa = $this->model->getSiswaById($message['id_siswa']);
				$data = array_merge($data, $data_siswa);
			}
		}
	
		$this->view('siswa-form.php', $data);
	}
	
	public function edit()
	{
		$this->hasPermissionPrefix('update', 'siswa');
		$this->data['title'] = 'Edit ' . $this->currentModule['judul_module'];
		
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
		
		$data_siswa = $this->model->getSiswaById($_GET['id']);
		if (empty($data_siswa)) {
			$this->errorDataNotFound();
			return;
		}
		
		$this->setData($data_siswa['id_wilayah_kelurahan']);
		$data = $this->data;
		
		if (empty($_GET['id'])) {
			$this->errorDataNotFound();
			return;
		}
		
		$data['breadcrumb']['Edit'] = '';
		$data['action'] = 'edit';
		$data['siswa'] = $data_siswa;
		$this->view('siswa-form.php', $data);
	}
	
	public function ajaxSaveData() {
		$message = $this->model->saveData();
		echo json_encode($message);
	}
	
	public function ajaxSaveDataStatus() {
		$result = $this->model->saveDataStatus();
		echo json_encode($result);
	}
	
	private function setData($id_wilayah_kelurahan = null) {
		
		$result = $this->model->getAllKelas();
		$list_kelas = [];
		foreach ($result as $val) {
			$list_kelas[$val['id_kelas']] = 'Kelas ' . $val['nama_kelas'];
		}

		// $result = $this->model->getAllStatusSiswa();
		$result = $this->model->getListStatusSiswa();
		$list_status_siswa = [];
		foreach ($result as $val) {
			$list_status_siswa[$val['id_status_siswa']] = $val['nama_status_siswa'];
		}
		
		$result = $this->model->getAllStatusOrangtua();
		$list_status_orangtua = [];
		foreach ($result as $val) {
			$list_status_orangtua[$val['id_status_orangtua']] = $val['nama_status_orangtua'];
		}
		
		$result = $this->model->getAllTahunAjaran();
		$list_tahun_ajaran = [];
		foreach ($result as $val) {
			$list_tahun_ajaran[$val['id_tahun_ajaran']] = $val['tahun_ajaran'];
		}
		
		$result = $this->model->getAllAgama();
		$list_agama = [];
		foreach ($result as $val) {
			$list_agama[$val['id_agama']] = $val['agama'];
		}
		
		$this->data['list_kelas'] = $list_kelas;
		$this->data['list_agama'] = $list_agama;
		$this->data['list_tahun_ajaran'] = $list_tahun_ajaran;
		$this->data['list_status_siswa'] = $list_status_siswa;
		$this->data['list_status_orangtua'] = $list_status_orangtua;
		
		$wilayah = new \App\Controllers\Wilayah();
		$data_wilayah = $wilayah->getDataWilayah($id_wilayah_kelurahan);
		$this->data = array_merge($this->data, $data_wilayah);
		return $data_wilayah;
	}
	
	public function uploadexcel() {
		if (isset($_POST['submit'])) 
		{
			$form_errors = $this->validateFormExcel();
			if ($form_errors) {
				$this->data['message']['status'] = 'error';
				$this->data['message']['message'] = $form_errors;
			} else {
				$this->data['message'] = $this->model->uploadExcel();	
			}
		}
		$this->data['title'] = 'Upload Excel';
		$this->view('siswa-upload-excel.php', $this->data);
	}
	
	function validateFormExcel() {

		$form_errors = [];

		if ($_FILES['file_excel']['name']) 
		{
				$file_type = $_FILES['file_excel']['type'];
				$allowed = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
				
				if (!in_array($file_type, $allowed)) {
					$form_errors['file_excel'] = 'Tipe file harus ' . join(', ', $allowed);
				}
		} else {
			$form_errors['file_excel'] = 'File excel belum dipilih';
		}
		
		return $form_errors;
	}
	
	public function ajaxDeleteStatus() {
		$result = $this->model->deleteStatus();
		
		if ($result) {
			$message = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			echo json_encode($message);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Data gagal dihapus']);
		}
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
	
	public function ajaxDeleteAllSiswa() {
		$result = $this->model->deleteAllSiswa();
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
			$image = 'noimage.png';
			if ($val['foto']) {
				if (file_exists('public/images/siswa/' . $val['foto'])) {
					$image = $val['foto'];
				}
			}
			
			$val['ignore_foto'] = '<div class="list-foto"><img src="'. $this->config->baseURL.'public/images/siswa/' . $image . '"/></div>';
			$val['tgl_lahir'] = $val['tempat_lahir'] . ', '. format_tanggal($val['tgl_lahir']);
			
			$val['ignore_urut'] = $no;
			
			$val['ignore_action'] = '<div class="form-inline btn-action-group">'
										. btn_link(
												['icon' => 'fas fa-edit'
													, 'url' => base_url() . '/siswa/edit?id=' . $val['id_siswa']
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_siswa']]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_siswa']
																	, 'data-delete-title' => 'Hapus data siswa : <strong>' . $val['nama'] . '</strong> ?'
																]
													, 'label' => 'Delete'
												]) . 
										
										'</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
	public function getDataDTSiswaRiwayatStatus() {
		$num_status = $this->model->countAllSiswaRiwayatStatus($_GET['id']);
		$status_siswa = $this->model->getListSiswaRiwayatStatus($_GET['id']);
		
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_status;
		$result['recordsFiltered'] = $status_siswa['total_filtered'];		
		
		helper('html');
		
		foreach ($status_siswa['data'] as $key => &$val) {
			$val['tgl_status'] = format_date($val['tgl_status']);
			$val['ignore_action'] = '<div class="input-group d-flex flex-nowrap">'
										. btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit-status btn-xs', 'data-id' => $val['id_siswa_riwayat_status']]
													, 'label' => ''
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete-status btn-xs'
																	, 'data-id' => $val['id_siswa_riwayat_status']
																	, 'data-delete-title' => 'Hapus data status siswa: <strong>' . $val['nama_status_siswa'] . '</strong>?'
																]
													, 'label' => ''
												]).
										'</div>';
			
			
		}
					
		$result['data'] = $status_siswa['data'];
		echo json_encode($result); exit();
	}
	
}
