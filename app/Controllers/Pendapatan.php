<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
require ROOTPATH . 'app/ThirdParty/PhpSpreadsheet/autoload.php';
use App\Models\PendapatanModel;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Pendapatan extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PendapatanModel;	
		$this->data['site_title'] = 'Pendapatan';
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/themes/material_blue.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/moment/moment.min.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/daterangepicker/daterangepicker.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/daterangepicker/daterangepicker.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-loader.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-fapicker.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pendapatan.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pendapatan-siswa.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pendapatan-lain.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pegawai-utang.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		if (empty($_GET['daterange'])) {
			$start_date = '01-01-' . date('Y');
			$end_date = date('d-m-Y');
		} else {
			list($start_date, $end_date) = explode(' s.d. ', $_GET['daterange']);
		}
			
		$exp = explode('-', $start_date);
		$start_date_db = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		$exp = explode('-', $end_date);
		$end_date_db = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		$this->data['start_date'] = $start_date;
		$this->data['end_date'] = $end_date;
		$this->data['start_date_db'] = $start_date_db;
		$this->data['end_date_db'] = $end_date_db;
		$this->data['jml_pendapatan'] = $this->model->getJmlPendapatan();
		$this->view('pendapatan-result.php', $this->data);
	}
	
	public function upload_excel() {
		
		$this->hasPermission('create');
		
		$breadcrumb['Upload Excel'] = '';
		$this->data['title'] = 'Upload Data Pendapatan';
				
		$error = false;
		if ($this->request->getPost('submit'))
		{
			$form_errors = $this->validateFormExcel();
			if ($form_errors) {
				$this->data['message']['status'] = 'error';
				$this->data['message']['content'] = $form_errors;
			} else {
				$this->data['message'] = $this->model->uploadExcel();	
			}
		}
		
		$this->view('pendapatan-upload-excel.php', $this->data);
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
	
	public function formatUploadFIleExcel() 
	{		
		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(ROOTPATH . "public/files/Format Data Pendapatan.xlsx");

		// Siswa
		/* $spreadsheet->setActiveSheetIndexByName('LIST_SISWA');
		$sheet = $spreadsheet->getActiveSheet();
		$list_siswa = $this->model->getListSiswa();
				
		$num_row = 0;
		$sheet->getColumnDimension('A')->setWidth(40);
		$sheet->setCellValue('A' . ++$num_row, 'NAMA_SISWA');
		$sheet->setCellValue('B' . $num_row, 'ID');
		$sheet->setCellValue('C' . $num_row, 'KELAS');
		$sheet->setCellValue('D' . $num_row, 'NIS');
		if ($list_siswa) {
			foreach ($list_siswa as $val) {
				$sheet->setCellValue('A' . ++$num_row, $val['nama'] . '_' . $val['id_siswa']);
				$sheet->setCellValue('B' . $num_row, $val['id_siswa']);
				$sheet->setCellValue('C' . $num_row, $val['nama_kelas']);
				$sheet->setCellValue('D' . $num_row, $val['nis']);
			}
		} */
		
		/* // Pegawai
		$spreadsheet->setActiveSheetIndexByName('LIST_PEGAWAI');
		$sheet = $spreadsheet->getActiveSheet();
		$list_pegawai = $this->model->getListPegawai();
				
		$num_row = 0;
		$sheet->getColumnDimension('A')->setWidth(40);
		$sheet->getColumnDimension('C')->setWidth(27);
		$sheet->setCellValue('A' . ++$num_row, 'NAMA_PEGAWAI');
		$sheet->setCellValue('B' . $num_row, 'ID');
		$sheet->setCellValue('C' . $num_row, 'JABATAN');
		if ($list_pegawai) {
			foreach ($list_pegawai as $val) {
				$sheet->setCellValue('A' . ++$num_row, $val['nama'] . '_' . $val['id_pegawai']);
				$sheet->setCellValue('B' . $num_row, $val['id_pegawai']);
				$sheet->setCellValue('C' . $num_row, $val['nama_jabatan']);
			}
		} */
		
		// Tagihan Siswa
		$spreadsheet->setActiveSheetIndexByName('LIST_NAMA_DAN_TAGIHAN_SISWA');
		$sheet = $spreadsheet->getActiveSheet();
		$list_tagihan_siswa = $this->model->getListTagihanSiswa();
		$num_row = 0;
		$nama_bulan = nama_bulan();
		foreach($list_tagihan_siswa as &$val) 
		{
			if ($val['periode_bulan']) {
				$val['periode_bulan'] = ' ' . $nama_bulan[$val['periode_bulan']] . ' ';
			}
			$val['tahun_ajaran'] = $val['tahun_ajaran'] ? ' ' . $val['tahun_ajaran'] : '';
			$periode = $val['periode_bulan'] . $val['periode_tahun'] . $val['tahun_ajaran'];
			$val['nama_tagihan_full'] = $val['nama_pendapatan_jenis'] . $periode;
		}

		$sheet->getColumnDimension('A')->setWidth(55);
		$sheet->getColumnDimension('C')->setWidth(15);
		$sheet->getColumnDimension('D')->setWidth(15);
		$sheet->getColumnDimension('E')->setWidth(15);
		$sheet->setCellValue('A' . ++$num_row, 'NAMA_DAN_TAGIHAN_SISWA');
		$sheet->setCellValue('B' . $num_row, 'ID_TAGIHAN');
		$sheet->setCellValue('C' . $num_row, 'NILAI_TAGIHAN');
		$sheet->setCellValue('D' . $num_row, 'SUDAH_DIBAYAR');
		$sheet->setCellValue('E' . $num_row, 'SALDO_TAGIHAN');
		$sheet->getStyle('C:E')->getNumberFormat()->setFormatCode('#,0');

		if ($list_tagihan_siswa) {
			foreach ($list_tagihan_siswa as $val) {
				$sheet->setCellValue('A' . ++$num_row, $val['nama'] . '_' . $val['nama_kelas'] . '_' . $val['nama_tagihan_full'] . '_' . $val['id_siswa_tagihan']);
				$sheet->setCellValue('B' . $num_row, $val['id_siswa_tagihan']);
				$sheet->setCellValue('C' . $num_row, $val['nilai_tagihan']);
				$sheet->setCellValue('D' . $num_row, $val['nilai_bayar_tagihan']);
				$sheet->setCellValue('E' . $num_row, $val['saldo_tagihan']);
			}
		}
		
		// Utang Pegawai
		$spreadsheet->setActiveSheetIndexByName('LIST_UTANG_PEGAWAI');
		$sheet = $spreadsheet->getActiveSheet();
		$list_utang_pegawai = $this->model->getListUtangPegawai();
				
		$num_row = 0;
		$sheet->getColumnDimension('A')->setWidth(40);
		$sheet->getColumnDimension('C')->setWidth(25);
		$sheet->getColumnDimension('D')->setWidth(17);
		$sheet->getColumnDimension('E')->setWidth(15);
		$sheet->getColumnDimension('F')->setWidth(15);
		$sheet->getColumnDimension('G')->setWidth(15);
		$sheet->setCellValue('A' . ++$num_row, 'UTANG_PEGAWAI');
		$sheet->setCellValue('B' . $num_row, 'ID');
		$sheet->setCellValue('C' . $num_row, 'JABATAN');
		$sheet->setCellValue('D' . $num_row, 'TGL_UTANG');
		$sheet->setCellValue('E' . $num_row, 'NILAI_UTANG');
		$sheet->setCellValue('F' . $num_row, 'NILAI_BAYAR');
		$sheet->setCellValue('G' . $num_row, 'SALDO UTANG');
		$sheet->getStyle('E:G')->getNumberFormat()->setFormatCode('#,0');
		if ($list_utang_pegawai) {
			foreach ($list_utang_pegawai as $val) {
				$sheet->setCellValue('A' . ++$num_row, $val['nama'] . '_' . format_number($val['nilai_utang']) . '_' . $val['id_pegawai_utang']);
				$sheet->setCellValue('B' . $num_row, $val['id_pegawai_utang']);
				$sheet->setCellValue('C' . $num_row, $val['nama_jabatan']);
				$sheet->setCellValue('D' . $num_row, format_tanggal($val['tgl_utang']));
				$sheet->setCellValue('E' . $num_row, $val['nilai_utang']);
				$sheet->setCellValue('F' . $num_row, $val['nilai_bayar']);
				$sheet->setCellValue('G' . $num_row, $val['saldo_utang']);
			}
		}
		
		// Pembayar
		$spreadsheet->setActiveSheetIndexByName('LIST_PEMBAYAR');
		$sheet = $spreadsheet->getActiveSheet();
		$list_pembayar = $this->model->getListPembayar();
		
		$num_row = 0;
		$sheet->getColumnDimension('A')->setWidth(20);
		$sheet->getColumnDimension('C')->setWidth(27);
		$sheet->setCellValue('A' . ++$num_row, 'NAMA_PEMBAYAR');
		$sheet->setCellValue('B' . $num_row, 'ID');
		$sheet->setCellValue('C' . $num_row, 'ALAMAT');

		if ($list_pembayar) {
			foreach ($list_pembayar as $val) {
				$sheet->setCellValue('A' . ++$num_row, $val['nama_pembayar'] . '_' . $val['id_pembayar']);
				$sheet->setCellValue('B' . $num_row, $val['id_pembayar']);
				$sheet->setCellValue('C' . $num_row, $val['alamat']);
			}
		}
		
		// List Pendapatan
		$spreadsheet->setActiveSheetIndexByName('LIST_PENDAPATAN');
		$sheet = $spreadsheet->getActiveSheet();
		$list_pendapatan = $this->model->getListPendapatan();
		
		$num_row = 0;
		$sheet->getColumnDimension('A')->setWidth(20);
		$sheet->setCellValue('A' . ++$num_row, 'NAMA_PENDAPATAN');
		$sheet->setCellValue('B' . $num_row, 'ID');

		if ($list_pendapatan) {
			foreach ($list_pendapatan as $val) {
				$sheet->setCellValue('A' . ++$num_row, $val['nama_pendapatan_jenis'] . '_' . $val['id_pendapatan_jenis']);
				$sheet->setCellValue('B' . $num_row, $val['id_pendapatan_jenis']);
			}
		}
		
		$spreadsheet->setActiveSheetIndexByName('DATA');
		
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Format Data Pendapatan.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');
		
		$writer = new Xlsx($spreadsheet);
		$writer->save('php://output');
	}

	public function generateExcel($output) 
	{
		$filepath = $this->model->writeExcel();
		$filename = 'Rincian Pendapatan.xlsx';
		
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
	
	public function ajaxDeleteAllData() {
		$result = $this->model->deleteAllData();
		echo json_encode($result);
	}
	
	public function getDataDT() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllData();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListData();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$nama_bulan = nama_bulan();
		$no = $this->request->getPost('start') + 1 ?: 1;
	
		foreach ($query['data'] as $key => &$val) 
		{
			$btn_edit_class = 'btn-edit-pendapatan-siswa';
			$btn_delete_class = 'btn-delete-pendapatan-siswa';
			$id_pendapatan = $val['id_pendapatan'];
			$delete_message = 'Hapus data pembayaran siswa : <strong>' . $val['nama'] .  '</strong> dengan No. Invoice: ' . $val['no_invoice'];
			$detail_pembayaran_utang = '';
			if (!$val['id_siswa']) {
				if ($val['id_pegawai_utang']) {
					$btn_edit_class = 'btn-edit-bayar-detail';
					$btn_delete_class = 'btn-delete-bayar-detail';
					$detail_pembayaran_utang = [];
					$detail_pembayaran_utang['no_invoice'] = $val['no_invoice'];
					$detail_pembayaran_utang['detail'] = [];
					$exp = explode(',', $val['nilai_utang']);
					$exp_tgl_utang = explode(',', $val['tgl_utang']);
					$exp_nilai_bayar = explode(',', $val['detail_nilai_bayar']);
					foreach ($exp as $index => $item) {
						$detail_pembayaran_utang['detail'][] = ['nilai_utang' => format_number($item)
														, 'tgl_utang' => format_tanggal($exp_tgl_utang[$index])
														, 'nilai_bayar' => format_number($exp_nilai_bayar[$index])
													];
					}
				} else {
					$btn_edit_class = 'btn-edit-pendapatan-lain';
					$btn_delete_class = 'btn-delete-pendapatan-lain';
					$delete_message = 'Hapus data pendapatan dari <strong>' . $val['nama'] .  '</strong> dengan nilai sebesar Rp. ' . format_number($val['nilai_bayar']);
				}
			}
			
			if ($detail_pembayaran_utang) {
				$detail_pembayaran_utang = '<textarea style="display:none" name="detail_pembayaran_utang[]">' . json_encode($detail_pembayaran_utang) . '</textarea>';
			}
			$val['ignore_urut'] = $no;
			$val['ignore_action'] = '<div class="form-inline btn-action-group justify-content-end">';
							if ($val['id_siswa']) {
									
								$val['ignore_action'] .= btn_label(
													['icon' => 'fas fa-print'
														, 'attr' => ['class' => 'btn btn-primary btn-print-invoice-siswa btn-xs me-1'
																	, 'data-url' => base_url() . '/pendapatan-siswa/printInvoice?id=' . $id_pendapatan
																	]
													])
											. btn_link(
												['url' => base_url() . '/pendapatan-siswa/invoicePdf?id=' . $val['id_pendapatan']
													,'label' => ''
													, 'icon' => 'far fa-file-pdf'
													, 'attr' => ['data-filename' => 'Invoice-' . $val['no_invoice']
																, 'target' => '_blank'
																, 'class' => 'btn btn-danger btn-xs save-pdf-siswa me-1'
																, 'data-bs-toggle' => 'tooltip'
																, 'data-bs-title' => 'Download Invoice (PDF)'
															]
												]
											);
							}
							
							if (has_permission('update_all')) {
								$val['ignore_action'] .= btn_label(
													['icon' => 'fas fa-edit'
														, 'attr' => ['class' => 'btn btn-success ' . $btn_edit_class . ' btn-xs me-1'
																		, 'data-id' => $id_pendapatan
																	]
													]);
							}
							
							if (has_permission('delete_all')) {
								$val['ignore_action'] .= btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger ' . $btn_delete_class . ' btn-xs'
																	, 'data-id' => $val['id_pendapatan']
																	, 'data-delete-title' => $delete_message . ' ?'
																]
												]) . 
										$detail_pembayaran_utang . 
										
										'</div>';
							}
			
			$val['nama'] = '<div style="min-width:200px">' . $val['nama'] . '</div>';
			$val['nilai_bayar'] = '<div class="text-end">' . format_number($val['nilai_bayar']) . '</div>';
			$val['tgl_bayar'] = '<div class="text-end text-nowrap">' . format_tanggal($val['tgl_bayar'], 'dd-mm-yyyy') . '</div>';
			// $val['nilai_spp'] = '<div class="text-end">' . format_number($val['nilai_spp']) . '</div>';
			$exp_jenis = explode(',', $val['nama_pendapatan_jenis']);
			$exp_periode_bulan = explode(',', $val['periode_bulan']);
			$exp_periode_tahun = explode(',', $val['periode_tahun']);
			$exp_tahun_ajaran = explode(',', $val['tahun_ajaran']);
			$list_pembayaran = [];
			$nama_bulan = nama_bulan();
			if ($exp_jenis && count($exp_jenis) > 1) {
				$cek_pembayaran = [];
				foreach ($exp_jenis as $index => $item) {
					
					$pembayaran = $item;
					if ($exp_periode_bulan[$index] != '-') {
						$pembayaran .= ' ' . $nama_bulan[$exp_periode_bulan[$index]] . ' ' . $exp_periode_tahun[$index];
					}
					
					if ($exp_tahun_ajaran[$index] != '-') {
						$pembayaran .= ' ' . $exp_tahun_ajaran[$index];
					}
					if (!in_array($pembayaran, $cek_pembayaran)) {
						$list_pembayaran[] = '<div class="">' . $pembayaran . '</div>';
						$cek_pembayaran[] = $pembayaran;
					}
				}
				if (count($list_pembayaran) == 1){
					$val['nama_pendapatan_jenis'] = $list_pembayaran[0];
				} else {
					$val['nama_pendapatan_jenis'] = '<ul class="list-circle"><li>' . join('</li><li>', $list_pembayaran) . '</li></ul>';
				}
			}
			
			$val['jenis_pendapatan'] = '<div class="text-nowrap">' . $val['jenis_pendapatan'] . '</div>';
			$val['total_bayar'] = '<div class="text-end">' . format_number($val['total_bayar']) . '</div>';
			$no++;
		}
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
