<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
require ROOTPATH . 'app/ThirdParty/PhpSpreadsheet/autoload.php';
use App\Models\SiswaTagihanModel;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Siswa_tagihan extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new SiswaTagihanModel;	
		$this->data['site_title'] = 'Tagihan Siswa';
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/themes/material_blue.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/siswa-tagihan.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		$this->data['jml_tagihan'] = $this->model->getJmlTagihan();
		$this->data['option_kelas'] = $this->getOptionKelas();
		$this->view('siswa-tagihan-result.php', $this->data);
	}
	
	private function getOptionKelas() {
		$result = $this->model->getAllGroupKelas();
		$option_kelas = ['' => 'Semua Kelas'];
		foreach ($result as $val) {
			if (!$val['group_kelas']) {
				continue;
			}
			$option_kelas[$val['group_kelas']] = 'Kelas ' . $val['group_kelas'];
		}
		return $option_kelas;
	}
	
	public function generateExcel($output) 
	{
		$filepath = $this->model->writeExcel();
		$filename = 'Rincian Tagihan Siswa.xlsx';
		
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
	
	public function ajaxGetFormAdd() {
		
		$result = $this->model->getJenisIuranSiswa();
		$jenis_tagihan = [];
		foreach ($result as $val) {
			$jenis_tagihan[$val['id_pendapatan_jenis']] = $val['nama_pendapatan_jenis'];
		}
		
		$result = $this->model->getPendapatanJenis();
		$pendapatan_jenis = [];
		foreach ($result as $val) {
			$pendapatan_jenis[$val['id_pendapatan_jenis']] = $val;
		}
		
		$result = $this->model->getTahunAjaran();
		$tahun_ajaran = [];
		foreach ($result as $val) {
			$tahun_ajaran[$val['id_tahun_ajaran']] = $val['tahun_ajaran'];
		}
				
		$this->data['group_kelas'] = $this->getOptionKelas();
		$this->data['jenis_tagihan'] = $jenis_tagihan;
		$this->data['siswa'] = $this->model->getSiswaByGroupKelas(1);
		$this->data['pendapatan_jenis'] = $pendapatan_jenis;
		$this->data['tahun_ajaran'] = $tahun_ajaran;
		echo view('themes/modern/siswa-tagihan-form-add.php', $this->data);
	}
	
	public function ajaxGetSiswaByGroupKelas() {
		$result = $this->model->getSiswaByGroupKelas($_GET['id']);
		echo json_encode($result);
	}
	
	public function ajaxGetFormEdit() {
		$tagihan = $this->model->getSiswaTagihanById();
		if (!$tagihan) {
			echo '<div class="alert alert-danger">Error: Data tidak ditemukan</div>';
			exit;
		}
		$this->data['tagihan'] = $tagihan;
		echo view('themes/modern/siswa-tagihan-form-edit.php', $this->data);
		
	}
	
	public function ajaxSaveDataAdd() {
		$message = $this->model->saveDataAdd();
		echo json_encode($message);
	}
	
	public function ajaxSaveDataEdit() {
		$message = $this->model->saveDataEdit();
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
	
	public function ajaxDeleteAllData() {
		$result = $this->model->deleteAllData();
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
		
		$nama_bulan = nama_bulan();
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['ignore_urut'] = $no;
			
			$delete_title = ', jenis tagihan: ' . $val['nama_pendapatan_jenis'];
			if ($val['using_periode'] == 'Y') 
			{
				switch ($val['jenis_periode']) {
					case 'bulan';
						$delete_title .= ' Bulan ' . $nama_bulan[$val['periode_bulan']] . ' ' . $val['periode_tahun'];
						break;
					case 'tahun';
						$delete_title .= ' Tahun ' . $val['periode_tahun'];
						break;
					case 'tahun_ajaran';
						$delete_title .= ' Tahun Ajaran ' . $val['tahun_ajaran'];
						break;
					
				}
			}
			
			$val['ignore_action'] = '<div class="form-inline btn-action-group">'
										. btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1'
																	, 'data-id' => $val['id_siswa_tagihan']
																	
																]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_siswa_tagihan']
																	
																	, 'data-delete-title' => 'Hapus data tagihan siswa : <strong>' . $val['nama'] .  '</strong> ' . $delete_title . ' ?'
																]
													, 'label' => 'Delete'
												]) . 
										
										'</div>';
			
			$val['nama'] = '<div style="min-width:200px">' . $val['nama'] . '</div>';
			// $val['periode_bulan'] = $val['periode_bulan'] ? '<div class="text-end">' . $nama_bulan[$val['periode_bulan']] . '</div>' : '';
			// $val['tahun_ajaran'] = $val['tahun_ajaran'] ? '<div class="text-end">' . $val['tahun_ajaran'] . '</div>' : '';
			$bg_kurang_bayar = $val['kurang_bayar'] > 0 ? 'text-danger' : 'text-success';
			$val['potongan'] = '<div class="text-end">' . format_number($val['potongan']) . '</div>';
			$val['nilai_tagihan'] = '<div class="text-end">' . format_number($val['nilai_tagihan']) . '</div>';
			$val['nilai_bayar_tagihan'] = '<div class="text-end">' . format_number($val['nilai_bayar_tagihan']) . '</div>';
			$val['kurang_bayar'] = '<div class="text-end ' . $bg_kurang_bayar . '">' . format_number($val['kurang_bayar']) . '</div>';
			// $val['periode_bulan'] = $val['periode_bulan'] ? '<div class="text-end">' . $val['periode_bulan'] . '</div>' : '';
			
			$periode_bulan = $val['periode_bulan'] ? $nama_bulan[$val['periode_bulan']] . ' ' : '';
			$periode_tahun = $val['periode_tahun'] ? $val['periode_tahun'] . ' ' : '';
			$val['nama_pendapatan_jenis'] = '<div class="text-nowrap">' . $val['nama_pendapatan_jenis'] . ' ' . $periode_bulan . $periode_tahun . $val['tahun_ajaran'] . '</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
