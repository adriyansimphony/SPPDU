<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers\Builtin;
use App\Models\Builtin\PegawaiModel;
use \Config\App;

class Pegawai extends \App\Controllers\BaseController
{
	protected $model;
	protected $moduleURL;
	
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PegawaiModel;	
		$this->formValidation =  \Config\Services::validation();
		$this->data['site_title'] = 'Halaman Profil';
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');

		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/bootstrap-datepicker/js/bootstrap-datepicker.js' );
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/date-picker.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/file-upload.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/bootstrap-datepicker/css/bootstrap-datepicker3.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/wilayah.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/builtin/js/pegawai.js');

		helper(['cookie', 'form']);
		// echo '<pre>'; print_r($this->pegawaiPermission); die;
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		$this->data['jml_pegawai'] = $this->model->getJmlPegawai();
		$this->data['title'] = 'Data Pegawai';		
		$this->view('builtin/pegawai/result.php', $this->data);
	}
	
	public function ajaxGetPegawaiAdmin() {
		$result = $this->model->getPegawaiAdmin();
		echo json_encode($result);
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
		$this->hasPermission('create');
		
		$breadcrumb['Add'] = '';
		$this->data['action'] = 'add';
		
		$this->setData();
		$data = $this->data;
		$data['title'] = 'Tambah Pegawai';
		$setting = $this->model->getSettingRegister();
		$data['setting_registrasi'] = [];
		foreach ($setting as $val) {
			$data['setting_registrasi'][$val['param']] = $val['value'];
		}
		
		$error = false;
		if ($this->request->getPost('submit'))
		{
			$data['message'] = $this->saveData();
			if ($data['message']['status'] == 'ok') {
				$result = $this->model->getPegawaiById($data['message']['id_pegawai'], true);
			
				if (!$result) {
					$this->errorDataNotFound();
					return;
				} else {
					$data = array_merge($data, $result);
				}
			}
		}

		$this->view('builtin/pegawai/form.php', $data);
	}
	
	public function edit()
	{
		if (!key_exists('update_all', $this->pegawaiPermission)) {
			if ($_GET['id'] != $this->session->get('pegawai')['id_pegawai']) {
				$this->printError('Anda tidak berhak mengubah data pegawai ini');
				return;
			}
		}
		
		$result = $this->model->getPegawaiById($this->request->getGet('id'), true);
		
		if (!$result) {
			$this->errorDataNotFound();
			return;
		}
		
		$this->data['pegawai_form'] = $result;
		$this->data['pegawai_form']['id_jabatan'] = $this->model->getJabatanByIdPegawai($result['id_pegawai']);
		$this->data['action'] = 'edit';
		
		$this->setData($result['id_wilayah_kelurahan']);
		$data = $this->data;
		$data['title'] = 'Edit Pegawai';
		$breadcrumb['Edit'] = '';
			
		// Submit
		$data['message'] = [];
		if ($this->request->getPost('submit')) 
		{
			$save_message = $this->saveData();
			$data = array_merge( $data, $save_message);
		}
		
		$this->view('builtin/pegawai/form.php', $data);
	}
	
	public function ajaxSaveData() {
		$message = $this->saveData();
		echo json_encode($message);
	}
	
	public function ajaxSaveDataStatus() {
		$result = $this->model->saveDataStatus();
		echo json_encode($result);
	}
	
	public function setData( $id_wilayah_kelurahan = null ) {
		$result = $this->model->getAllJabatan();
		$list_jabatan = [];
		foreach ($result as $val) {
			$list_jabatan[$val['id_jabatan']] = $val['nama_jabatan'];
		}
		
		$result = $this->model->getAllAgama();
		$list_agama = [];
		foreach ($result as $val) {
			$list_agama[$val['id_agama']] = $val['agama'];
		}
		
		$this->data['list_jabatan'] = $list_jabatan;
		$this->data['list_agama'] = $list_agama;
		$this->data['roles'] = $this->model->getRoles();
		$this->data['pegawai_permission'] = $this->pegawaiPermission;
		$this->data['list_module'] = $this->model->getListModules();
		$this->data['list_status_pegawai'] = $this->model->getListStatusPegawai();
		$wilayah = new \App\Controllers\Wilayah();
		$data_wilayah = $wilayah->getDataWilayah($id_wilayah_kelurahan);
		$this->data = array_merge($this->data, $data_wilayah);
		return $data_wilayah;
	}
	
	private function saveData() 
	{		
		$form_errors = $this->validateForm();
		$error = false;		
		
		if ($form_errors) {
			$result = ['status' => 'error', 'message' => $form_errors];
			$error = true;
		}
		
		if (!$error) {				
			$result = $this->model->saveData($this->pegawaiPermission);
		}
		
		return $result;
	}
	
	public function uploadExcel() 
	{
		$this->hasPermission('create');
		
		$breadcrumb['Upload Excel'] = '';
		$this->data['title'] = 'Upload Data Pegawai';
				
		$error = false;
		if ($this->request->getPost('submit'))
		{
			$form_errors = $this->validateFormUpload();
			if ($form_errors) {
				$this->data['message']['status'] = 'error';
				$this->data['message']['content'] = $form_errors;
			} else {
				$this->data['message'] = $this->model->uploadExcel();	
			}
		}

		$this->view('builtin/pegawai/form-uploadexcel.php', $this->data);
	}
	
	function validateFormUpload() {

		$form_errors = [];
		
		if ($_FILES['file_excel']['error'] == 1) {
            $form_errors['file_excel'] = 'Ukuran file terlalu besar';
	    } else if ($_FILES['file_excel']['name']) 
		{
			$file_type = $_FILES['file_excel']['type'];
			$allowed = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
				
			if (!in_array($file_type, $allowed)) {
				$form_errors['file_excel'] = 'Tipe file harus ' . join(', ', $allowed);
			}
		} else {
			$form_errors['file_excel'] = 'File belum dipilih';
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
	
	public function ajaxDeleteData() 
	{
		$delete_permission = $this->hasPermissionPrefix('delete');
		if ($delete_permission) {
			$delete = $this->model->deletePegawai();
			if ($delete) {
				$result = ['status' => 'ok', 'message' => 'Data pegawai berhasil dihapus'];
			} else {
				$result = ['status' => 'warning', 'message' => 'Tidak ada data yang dihapus'];
			}
		} else {
			$result = ['status' => 'error', 'message' => 'Role Anda tidak diperkenankan untuk menghapus data'];
		}
		
		echo json_encode($result);
	}
	
	public function ajaxDeleteAllPegawai() {
		$delete_permission = $this->hasPermission('delete_all');
		if ($delete_permission) {
			$message = $this->model->deleteAllPegawai();
		} else {
			$message = ['status' => 'error', 'message' => 'Role Anda tidak diperkenankan untuk menghapus data semua pegawai'];
		}
		
		echo json_encode($message);
	}
	
	private function validateForm() {
	
		$validation =  \Config\Services::validation();
		$validation->setRule('nama', 'Nama', 'trim|required');
		$validation->setRule('nip_pegawai', 'NIP Pegawai', 'trim|required');
		// $validation->setRule('email', 'Email', 'trim|required|valid_email');
		$validation->setRule('id_jabatan', 'Jabatan', 'required');
		$validation->setRule('id_role', 'Role', 'required');
		
		if ($this->request->getPost('id')) {
			if ($this->request->getPost('email') != $this->request->getPost('email_lama')) {
				$validation->setRules(
					['email' => [
							'label'  => 'Email',
							'rules'  => 'required|valid_email|is_unique[pegawai.email]',
							'errors' => [
								'is_unique' => 'Email sudah digunakan'
								, 'valid_email' => 'Email tidak valid'
								, 'required' => 'Email harus diisi'
							]
						]
					]
				);
			}
			
			if ($this->request->getPost('nip_pegawai') != $this->request->getPost('nip_pegawai_lama')) 
			{
				$validation->setRules(
					[
						'nip_pegawai' => [
							'label'  => 'Email',
							'rules'  => 'trim|required|is_unique[pegawai.nip_pegawai]',
							'errors' => [
								'is_unique' => 'NIP Pegawai sudah digunakan',
								'required' => 'NIP Pegawai harus diisi'
							]
						]
					]
				);
			}
		} else {
			if ($this->hasPermission('create')) 
			{
				$validation->setRule('password', 'Password', 'trim|required|min_length[3]');
				$validation->setRules(
					[
						'email' => [
							'label'  => 'Email',
							'rules'  => 'required|valid_email|is_unique[pegawai.email]',
							'errors' => [
								'is_unique' => 'Email sudah digunakan'
								, 'valid_email' => 'Email tidak valid'
							]
						],
						'nip_pegawai' => [
							'label'  => 'Email',
							'rules'  => 'trim|required|is_unique[pegawai.nip_pegawai]',
							'errors' => [
								'is_unique' => 'NIP Pegawai sudah digunakan',
								'required' => 'NIP Pegawai harus diisi'
							]
						],
						'ulangi_password' => [
							'label'  => 'Ulangi Password',
							'rules'  => 'required|matches[password]',
							'errors' => [
								'required' => 'Ulangi password tidak boleh kosong'
								, 'matches' => 'Ulangi password tidak cocok dengan password'
							]
						]
					]
					
				);
			}
		}
		
		$valid = $validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();

		$file = $this->request->getFile('avatar');
		if ($file && $file->getName())
		{
			if ($file->isValid())
			{
				$type = $file->getMimeType();
				$allowed = ['image/png', 'image/jpeg', 'image/jpg'];
				
				if (!in_array($type, $allowed)) {
					$form_errors['avatar'] = 'Tipe file harus ' . join(', ', $allowed);
				}
				
				if ($file->getSize() > 300 * 1024) {
					$form_errors['avatar'] = 'Ukuran file maksimal 300Kb';
				}
				
				$info = \Config\Services::image()
						->withFile($file->getTempName())
						->getFile()
						->getProperties(true);
				
				if ($info['height'] < 100 || $info['width'] < 100) {
					$form_errors['avatar'] = 'Dimensi file minimal: 100px x 100px';
				}
				
			} else {
				$form_errors['avatar'] = $file->getErrorString().'('.$file->getError().')';
			}
		}
		
		if ($this->request->getPost('option_default_page') == 'url') {
			if (empty($this->request->getPost('default_page_url'))) {
				$form_errors['url'] = 'Url tidak boleh kosong';
			}
		}
		
		return $form_errors;
	}
	
	public function edit_password()
	{
		$data['title'] = 'Edit Password';
		$breadcrumb['Edit Password'] = '';
		
		$form_errors = null;
		$this->data['status'] = '';
		
		if ($this->request->getPost('submit')) 
		{
			$result = $this->model->getPegawaiById();
			$error = false;
			
			if ($result) {
				
				if (!password_verify($this->request->getPost('password_old'), $result['password'])) {
					$error = true;
					$this->data['message'] = ['status' => 'error', 'message' => 'Password lama tidak cocok'];
				}
			} else {
				$error = true;
				$this->data['message'] = ['status' => 'error', 'message' => 'Data pegawai tidak ditemukan'];
			}
		
			if (!$error) {
		
				$this->formValidation->setRule('password_new', 'Password', 'trim|required');
				$this->formValidation->setRule('password_new_confirm', 'Confirm Password', 'trim|required|matches[password_new]');
					
				$this->formValidation->withRequest($this->request)->run();
				$errors = $this->formValidation->getErrors();
				
				$custom_validation = new \App\Libraries\FormValidation;
				$custom_validation->checkPassword('password_new', $this->request->getPost('password_new'));
			
				$form_errors = array_merge($custom_validation->getErrors(), $errors);
					
				if ($form_errors) {
					$this->data['message'] = ['status' => 'error', 'message' => $form_errors];
				} else {
					$update = $this->model->updatePassword();
					if ($update) {
						$this->data['message'] = ['status' => 'ok', 'message' => 'Password Anda berhasil diupdate'];
					} else {
						$this->data['message'] = ['status' => 'error', 'message' => 'Password Anda gagal diupdate... Mohon hubungi admin. Terima Kasih...'];
					}
				}
			}
		}
		
		$this->data['title'] = 'Edit Password';
		$this->data['form_errors'] = $form_errors;
		$this->data['pegawai'] = $this->model->getPegawaiById($this->pegawai['id_pegawai']);
		$this->view('builtin/pegawai/form-edit-password.php', $this->data);
	}
	
	public function ajaxGetFormStatus() 
	{
		$this->hasPermissionPrefix('update');
		$this->data['title'] = 'Edit Riwayat Status Pegawai';
		
		$data_riwayat = [];
		if (!empty($_GET['id'])) {
			$data_riwayat = $this->model->getRiwayatPegawaiById($_GET['id']);
			if (empty($data_riwayat)) {
				$this->errorDataNotFound();
				return;
			}
		}
			
		$result = $this->model->getAllStatusPegawai();
		foreach ($result as $val) {
			$option_status[$val['id_status_pegawai']] = $val['nama_status_pegawai'];
		}
				
		$this->data['breadcrumb']['Edit'] = '';
		$this->data['action'] = 'edit';
		$this->data['riwayat'] = $data_riwayat;
		$this->data['option_status'] = $option_status;
		echo view('themes/modern/builtin/pegawai/form-status.php', $this->data);
	}
	
	public function getDataDT() {
		
		$this->hasPermission('read_all');
		
		$num_pegawai = $this->model->countAllPegawai($this->whereOwn('id_pegawai'));
		// $pegawai = $this->model->getListPegawais($this->actionPegawai, $this->whereOwn('id_pegawai'));
		$pegawai = $this->model->getListPegawai($this->whereOwn('id_pegawai'));
		
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_pegawai;
		$result['recordsFiltered'] = $pegawai['total_filtered'];		
		
		helper('html');
		$path = ROOTPATH . 'public/images/pegawai/';
		
		foreach ($pegawai['data'] as $key => &$val) {
			
			if ($val['foto']) {
				if (file_exists($path . $val['foto'])) {
					$foto = $val['foto'];
				} else {
					$foto = 'default.png';
				}
				
			} else {
				$foto = 'default.png';
			}
			
			$role = '';
			if ($val['judul_role']) {
				$split = explode(',', $val['judul_role']);
				foreach ($split as $judul_role) {
					$role .= '<span class="badge badge-role bg-secondary me-2 py-2 px-3">' . $judul_role . '</span>';
				}	
			}
						
			$val['judul_role'] = '<div style="white-space:break-spaces">' . $role . '</div>';
			$val['ignore_foto'] = '<img src="'. $this->config->baseURL . 'public/images/pegawai/' . $foto . '">';
			
			$val['ignore_action'] = '<div class="form-inline btn-action-group">'
										. btn_link(
												['icon' => 'fas fa-edit'
													, 'url' => base_url() . '/builtin/pegawai/edit?id=' . $val['id_pegawai']
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_pegawai']]
													, 'label' => 'Edit'
												]);
			
			if ($this->hasPermission('delete_own') || $this->hasPermission('delete_all')) {
				$val['ignore_action'] .= btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_pegawai']
																	, 'data-delete-title' => 'Hapus data pegawai : <strong>' . $val['nama'] . '</strong> ?'
																]
													, 'label' => 'Delete'
												]);
			}
			
			$val['ignore_action'] .= '</div>';
			
			if ($val['nama']) {
				$val['nama'] = '<div class="text-wrap">' . $val['nama'] . '</div>';
			}
			
			if ($val['nama_jabatan']) {
				$val['nama_jabatan'] = '<div class="text-wrap">' . $val['nama_jabatan'] . '</div>';
			}
		}
					
		$result['data'] = $pegawai['data'];
		echo json_encode($result); exit();
	}
	
	public function getDataDTPegawaiRiwayatStatus() {
		
		$num_pegawai = $this->model->countAllPegawaiRiwayatStatus($_GET['id']);
		$status_pegawai = $this->model->getListPegawaiRiwayatStatus($_GET['id']);
		
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_pegawai;
		$result['recordsFiltered'] = $status_pegawai['total_filtered'];		
		
		helper('html');
		
		foreach ($status_pegawai['data'] as $key => &$val) {
			
			$val['tgl_status'] = format_date($val['tgl_status']);
			$val['ignore_action'] = '<div class="form-inline btn-group">'
										. btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit-status btn-xs', 'data-id' => $val['id_pegawai_riwayat_status']]
													, 'label' => ''
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete-status btn-xs'
																, 'data-id' => $val['id_pegawai_riwayat_status']
																, 'data-delete-title' => 'Hapus data status pegawai: <strong>' . $val['nama_status_pegawai'] . '</strong>?'
															]
													, 'label' => ''
												]).
										'</div>';
			
			
		}
					
		$result['data'] = $status_pegawai['data'];
		echo json_encode($result); exit();
	}
}
