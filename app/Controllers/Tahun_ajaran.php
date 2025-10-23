<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\TahunAjaranModel;

class Tahun_ajaran extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new TahunAjaranModel;	
		$this->data['site_title'] = 'Tahun Ajaran';
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/tahun-ajaran.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$data = $this->data;
		$this->view('tahun-ajaran-result.php', $data);
	}
	
	public function ajaxDeleteData() {

		$result = $this->model->deleteData();
		// $result = true;
		if ($result) {
			$result = ['status' => 'ok', 'message' => 'Data Tahun Ajaran berhasil dihapus'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data Tahun Ajaran gagal dihapus'];
		}
		
		echo json_encode($result);
	}
	
	public function ajaxGetFormData() {
		
		if (isset($_GET['id'])) {
			if ($_GET['id']) {
				$this->data['form_data'] = $this->model->getTahunAjaranById($_GET['id']);
				if (!$this->data['form_data'])
					return;
			}
		}
		
		$start = 2015;
		$end = date('Y') + 1;
		$list_tahun_ajaran = [];
		for ($i = $end; $i>=$start; $i--) {
			$tahun_ajaran = ($i-1) . '/' . $i;
			$list_tahun_ajaran[ $tahun_ajaran ] = $tahun_ajaran;
		}
		$this->data['list_tahun_ajaran'] = $list_tahun_ajaran;
		echo view('themes/modern/tahun-ajaran-form.php', $this->data);
	}
	
	public function ajaxUpdateData() {

		$message = $this->model->saveData();
		echo json_encode($message);
	}
	
	public function edit()
	{
		$this->hasPermissionPrefix('update', 'mahasiswa');
		
		$this->data['title'] = 'Edit ' . $this->currentModule['judul_module'];;
		$data = $this->data;
		
		if (empty($_GET['id'])) {
			$this->errorDataNotFound();
			return;
		}
				
		// Submit
		$data['msg'] = [];
		if (isset($_POST['submit'])) 
		{
			$form_errors = false;
							
			if ($form_errors) {
				$data['msg']['status'] = 'error';
				$data['msg']['content'] = $form_errors;
			} else {
				
				// $query = false;
				$message = $this->model->saveData();
				$data = array_merge($data, $message);
			}
		}
		
		$data['breadcrumb']['Edit'] = '';
		
		$data_mahasiswa = $this->model->getMahasiswaById($_GET['id']);
		if (empty($data_mahasiswa)) {
			$this->errorDataNotFound();
			return;
		}
		$data = array_merge($data, $data_mahasiswa);
		
		$this->view('image-upload-form.php', $data);
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
			$val['ignore_action'] = '<div class="form-inline btn-action-group">'
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_tahun_ajaran']
																	, 'data-delete-title' => 'Hapus data kelas: <strong>' . $val['tahun_ajaran'] . '</strong>'
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
