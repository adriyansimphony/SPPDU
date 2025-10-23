<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\KelasModel;

class Kelas extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new KelasModel;	
		$this->data['site_title'] = 'Kelas';
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/kelas.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$this->data['jml_kelas'] = $this->model->getJmlKelas();
		$this->view('kelas-result.php', $this->data);
	}
	
	public function ajaxDeleteData() {

		$result = $this->model->deleteData();
		if ($result) {
			$result = ['status' => 'ok', 'message' => 'Data kelas berhasil dihapus'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data kelas gagal dihapus'];
		}
		echo json_encode($result);
	}
	
	public function ajaxDeleteAllKelas() {

		$result = $this->model->deleteAllKelas();
		// $result = true;
		if ($result) {
			$result = ['status' => 'ok', 'message' => 'Data kelas berhasil dihapus'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data kelas gagal dihapus'];
		}
		
		echo json_encode($result);
	}
	
	public function ajaxGetFormData() {
		
		if (isset($_GET['id'])) {
			if ($_GET['id']) {
				$this->data['kelas'] = $this->model->getKelasById($_GET['id']);
				if (!$this->data['kelas'])
					return;
			}
		}
		$list_kelas = [1 => 'Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6', 'Kelas 7', 'Kelas 8', 'Kelas 9','Kelas 10','Kelas 11', 'Kelas 12'];
		$this->data['list_kelas'] = $list_kelas;
		echo view('themes/modern/kelas-form.php', $this->data);
	}
	
	public function ajaxUpdateData() {

		$message = $this->model->saveData();
		echo json_encode($message);
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
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_kelas']]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_kelas']
																	, 'data-delete-title' => 'Hapus data kelas: <strong>' . $val['nama_kelas'] . '</strong>'
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
