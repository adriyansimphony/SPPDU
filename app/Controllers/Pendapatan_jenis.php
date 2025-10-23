<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\PendapatanJenisModel;

class Pendapatan_jenis extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PendapatanJenisModel;	
		$this->data['site_title'] = 'Jenis Pendapatan';
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pendapatan-jenis.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$this->data['jml_jenis_pendapatan'] = $this->model->getJmlJenisPemasukan();
		$this->view('pendapatan-jenis-result.php', $this->data);
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
				$this->data['pendapatan'] = $this->model->getPemasukanJenisById($_GET['id']);
				if (!$this->data['pendapatan'])
					return;
			}
		}
		
		$sumber = $this->model->getPemasukanSumber();
		foreach ($sumber as $val) {
			$option_pendapatan_sumber[$val['id_pendapatan_sumber']] = $val['nama_pendapatan_sumber'];
		}
		
		$this->data['option_pendapatan_sumber'] = $option_pendapatan_sumber;
		$this->data['pendapatan_sumber_selected'] = explode(',', @$this->data['pendapatan']['id_pendapatan_sumber']);
		echo view('themes/modern/pendapatan-jenis-form.php', $this->data);
	}
	
	public function ajaxSaveData() {
		$error = $this->validateForm();
		if ($error) {
			$message = ['status' => 'error', 'message' => $error];
		} else {
			$message = $this->model->saveData();
		}
		echo json_encode($message);
	}
	
	private function validateForm() {
		$error = false;
		if (empty($_POST['id_pendapatan_sumber'])) {
			$error = 'Sumber harus dipilih';
		}
		return $error;
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
		// $nama_kategori = ['iuran_wajib_siswa' => 'Iuran Wajib Siswa', 'iuran_sukarela' => 'Iuran Sukarela', 'lainnya' => 'Lainnya'];
		$jenis_periode = ['bulan' => 'Bulan', 'tahun' => 'Tahun', 'tahun_ajaran' => 'Tahun Ajaran'];
		foreach ($query['data'] as $key => &$val) 
		{
			$val['ignore_urut'] = $no;
			// $val['nama_kategori'] = $nama_kategori[$val['nama_kategori']];
			$val['jenis_periode'] = $val['jenis_periode'] ? $jenis_periode[$val['jenis_periode']] : '';
			$val['kategori'] = ucwords($val['kategori']);
			
			$exp = explode(',', $val['id_pendapatan_sumber']);
			$val['perlu_tagihan_siswa'] = in_array(1, $exp) ? $val['perlu_tagihan_siswa'] : '-';
			$val['ignore_action'] = '<div class="form-inline btn-action-group">'
										. btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_pendapatan_jenis']]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_pendapatan_jenis']
																	, 'data-delete-title' => 'Hapus data jenis pendapatan : <strong>' . $val['nama_pendapatan_jenis'] . '</strong>'
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
