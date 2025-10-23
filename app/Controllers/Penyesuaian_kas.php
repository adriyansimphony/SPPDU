<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\PenyesuaianKasModel;

class Penyesuaian_kas extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PenyesuaianKasModel;	
		$this->data['site_title'] = 'Penyesuaian Kas';
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/penyesuaian-kas.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$this->data['jml_data'] = $this->model->getJmlData();
		$this->view('penyesuaian-kas-result.php', $this->data);
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
				$this->data['penyesuaian'] = $this->model->getPenyesuaianKasById($_GET['id']);
				if (!$this->data['penyesuaian'])
					return;
			}
		}
		echo view('themes/modern/penyesuaian-kas-form.php', $this->data);
	}
	
	public function ajaxUpdateData() {

		$message = $this->model->saveData();
		echo json_encode($message);
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
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_penyesuaian_kas']]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_penyesuaian_kas']
																	, 'data-delete-title' => 'Hapus data penyesuaian kas : <strong>' . $val['nama_penyesuaian_kas'] . '</strong>'
																]
													, 'label' => 'Delete'
												]) . 
										
										'</div>';
										
			$val['tgl_berlaku'] = format_tanggal($val['tgl_berlaku']);
			$plus_minus = substr($val['nilai'], 0, 1) == '-' ? '-' : '';
			$val['nilai'] = '<div class="text-end">' . $plus_minus . format_number($val['nilai']) . '</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
