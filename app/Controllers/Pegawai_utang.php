<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\PegawaiUtangModel;

class Pegawai_utang extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PegawaiUtangModel;	
		$this->data['site_title'] = 'Utang Pegawai';
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/themes/material_blue.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pegawai-utang.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$this->data['jml_data'] = $this->model->getJmlData();
		$this->view('pegawai-utang-result.php', $this->data);
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
	
	public function ajaxDeleteDataByIdBayar() {
		$result = $this->model->deleteDataByIdBayar($_POST['id']);
		// $result = true;
		if ($result) {
			$result = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data gagal dihapus'];
		}
		
		echo json_encode($result);
	}
	
	public function ajaxDeleteDataByIdUtang() {
		// $result = $this->model->deleteDataByIdUtang($_POST['id']);
		// $result = true;
		if ($result) {
			$result = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data gagal dihapus'];
		}
		
		echo json_encode($result);
		
	}
	
	public function ajaxGetFormDataBayar() {
		
		if (isset($_GET['id'])) {
			if ($_GET['id']) {
				$this->data['bayar'] = $this->model->getPegawaiUtangBayarById($_GET['id']);
			}
		}
		
		if (isset($_GET['id_pegawai_utang'])) {
			if ($_GET['id_pegawai_utang']) {
				$bayar = $this->model->getPegawaiUtangById($_GET['id_pegawai_utang']);
				$bayar['keterangan'] = '';
				
				$total_bayar = $this->model->getTotalBayarByIdUtang($_GET['id_pegawai_utang']);
				$bayar['keterangan'] = '';
				$bayar['detail'] = [];
				$bayar['detail'][] = ['nilai_utang' => $bayar['nilai_utang']
									, 'nilai_bayar' => 0
									, 'total_bayar' => $total_bayar
									, 'kurang' => $bayar['nilai_utang'] - $total_bayar
									,'id_pegawai_utang' => $_GET['id_pegawai_utang']
									];
				$this->data['bayar'] = $bayar;
			}
		}
		
		$result = $this->model->getAllMetodePembayaran();
		$metode_pembayaran =[];
		foreach ($result as $val) {
			$metode_pembayaran[$val['id_jenis_bayar']] = $val['nama_jenis_bayar'];
		}
		$this->data['metode_pembayaran'] = $metode_pembayaran;
		echo view('themes/modern/pegawai-utang-bayar-form.php', $this->data);
	}
	
	public function ajaxGetFormData() {
		
		if (isset($_GET['id'])) {
			if ($_GET['id']) {
				$this->data['utang'] = $this->model->getPegawaiUtangById($_GET['id']);
				if (!$this->data['utang'])
					return;
			}
		}
		
		$result = $this->model->getAllMetodePembayaran();
		$metode_pembayaran =[];
		foreach ($result as $val) {
			$metode_pembayaran[$val['id_jenis_bayar']] = $val['nama_jenis_bayar'];
		}
		$this->data['metode_pembayaran'] = $metode_pembayaran;
		echo view('themes/modern/pegawai-utang-form.php', $this->data);
	}
	
	public function ajaxSaveData() {

		$message = $this->model->saveData();
		echo json_encode($message);
	}
	
	public function ajaxSaveDataBayar() {

		$message = $this->model->saveDataBayar();
		echo json_encode($message);
	}
	
	public function getListBayar() {
		echo view('themes/modern/pegawai-utang-list-bayar-popup.php', $this->data);
	}
	
	public function getListDataBayarByIdUtang() {
		$data = $this->model->getDataBayarByIdUtang($_GET['id']);
		// print_r($data);
		echo json_encode($data);
	}
	
	public function getListUtang() {
		echo view('themes/modern/pegawai-utang-list-utang-popup.php', $this->data);
	}
	
	public function getListPegawai() {
		echo view('themes/modern/pegawai-utang-list-pegawai.php', $this->data);
	}
	
	public function generateExcel($output) 
	{
		$filepath = $this->model->writeExcel();
		$filename = 'Daftar Utang Pegawai.xlsx';
		
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
	
	public function getDataDTBayar() 
	{
		$this->hasPermission('read_all');
		
		$num_utang = $this->model->countAllBayarByIdUtang($_GET['id']);
		$utang = $this->model->getListBayarByIdUtang($_GET['id']);
		
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_utang;
		$result['recordsFiltered'] = $utang['total_filtered'];		
		
		helper('html');
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($utang['data'] as $key => &$val) {
			
			$val['ignore_urut'] = $no;
			$val['ignore_action'] = '<div class="input-group">' .
									btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit-bayar-detail btn-xs'
																	, 'data-id' => $val['id_pendapatan']
																]
													, 'label' => 'Edit'
												])
									. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete-bayar-detail btn-xs'
																	, 'data-id' => $val['id_pendapatan']
																]
													, 'label' => 'Delete'
												])
									. '
									</div>
									<textarea style="display:none" name="detail_utang[]">' . json_encode($val) . '</textarea>';

			$val['nilai_bayar'] = '<div class="text-end">' . format_number($val['nilai_bayar']) . '</div>';
			$val['tgl_bayar'] = '<div class="text-end">' . format_date($val['tgl_bayar']) . '</div>';
			
			$no++;
		}
					
		$result['data'] = $utang['data'];
		echo json_encode($result); exit();
	}
	
	public function getDataDTUtang() {
		
		$this->hasPermission('read_all');
		
		$num_utang = $this->model->countAllUtangByIdPegawai($_GET['id']);
		$utang = $this->model->getListUtangByIdPegawai($_GET['id']);
		
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_utang;
		$result['recordsFiltered'] = $utang['total_filtered'];		
		
		helper('html');
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($utang['data'] as $key => &$val) {
			
			$val['ignore_urut'] = $no;
			$val['ignore_pilih'] = '<div>' .
									btn_label(
												['icon' => 'fas fa-plus'
													, 'attr' => ['class' => 'btn btn-success btn-pilih-utang btn-xs me-1']
													, 'label' => 'Pilih'
												])
									. '
									<textarea style="display:none" name="detail_utang[]">' . json_encode($val) . '</textarea>
									</div>';
									
			$val['nama'] = '<div class="text-nowrap">' . $val['nama'] . '</div>';
			$val['nilai_utang'] = '<div class="text-end">' . format_number($val['nilai_utang']) . '</div>';
			$val['total_bayar'] = '<div class="text-end">' . format_number($val['total_bayar']) . '</div>';
			$val['kurang'] = '<div class="text-end">' . format_number($val['kurang']) . '</div>';
			$val['tgl_utang'] = '<div class="text-end">' . format_date($val['tgl_utang']) . '</div>';
			
			if ($val['nama_jabatan']) {
				$val['nama_jabatan'] = '<div class="text-wrap">' . $val['nama_jabatan'] . '</div>';
			}
			
			$no++;
		}
					
		$result['data'] = $utang['data'];
		echo json_encode($result); exit();
	}
	
	public function getDataDTPegawai() {
		
		$this->hasPermission('read_all');
		
		$num_pegawai = $this->model->countAllPegawai();
		$pegawai = $this->model->getListPegawai();
		
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_pegawai;
		$result['recordsFiltered'] = $pegawai['total_filtered'];		
		
		helper('html');
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($pegawai['data'] as $key => &$val) {
			
			$val['ignore_urut'] = $no;
			$val['ignore_pilih'] = '<div>' .
									btn_label(
												['icon' => 'fas fa-plus'
													, 'attr' => ['class' => 'btn btn-success btn-pilih-pegawai btn-xs me-1']
													, 'label' => 'Pilih'
												])
									. '
									<textarea style="display:none" name="detail_pegawai[]">' . json_encode($val) . '</textarea>
									</div>';
									
			if ($val['nama']) {
				$val['nama'] = '<div class="text-wrap">' . $val['nama'] . '</div>';
			}
			
			if ($val['nama_jabatan']) {
				$val['nama_jabatan'] = '<div class="text-wrap">' . $val['nama_jabatan'] . '</div>';
			}
			
			$no++;
		}
					
		$result['data'] = $pegawai['data'];
		echo json_encode($result); exit();
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
			$val['ignore_action_bayar'] = '<div class="form-inline btn-action-group">';
			
			if (has_permission('create')) {
				$val['ignore_action_bayar'] .= btn_label(
												['icon' => 'fas fa-plus'
													, 'attr' => ['class' => 'btn btn-success btn-add-bayar-utang btn-xs me-1', 'data-id' => $val['id_pegawai_utang']]
													, 'label' => 'Add'
												]);
			}
			
			if (has_permission('update_all')) {
				$val['ignore_action_bayar'] .= btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-info btn-edit-bayar-utang btn-xs me-1', 'data-id' => $val['id_pegawai_utang']]
													, 'label' => 'Edit'
												]);
			}
			
			if (has_permission('delete_all')) {
				$val['ignore_action_bayar'] .= btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete-bayar btn-xs'
																	, 'data-id' => $val['id_pegawai_utang']
																	, 'data-delete-title' => 'Hapus data utang : <strong>' . $val['nama'] . '</strong>'
																]
													, 'label' => 'Delete'
												]);
			}
			$val['ignore_action_bayar'] .= '</div>';
									
			$val['ignore_action'] = '<div class="form-inline btn-action-group">';
			
				if (has_permission('update_all')) {
					$val['ignore_action'] .= btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_pegawai_utang']]
													, 'label' => 'Edit'
												]);
				}
				if (has_permission('delete_all')) {
					$val['ignore_action'] .= btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_pegawai_utang']
																	, 'data-delete-title' => 'Hapus data utang : <strong>' . $val['nama'] . '</strong>'
																]
													, 'label' => 'Delete'
												]);
				}
										
				$val['ignore_action'] .= '</div>';
			$val['nilai_utang'] = '<div class="text-end">' . format_number($val['nilai_utang']) . '</div>';
			$val['total_pembayaran'] = '<div class="text-end">' . format_number($val['total_pembayaran']) . '</div>';
			$val['tgl_utang'] = '<div class="text-end">' . format_date($val['tgl_utang']) . '</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
