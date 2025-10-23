<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\DashboardKeuanganModel;

class Dashboard_keuangan extends BaseController
{
	public function __construct() {
		parent::__construct();
		$this->model = new DashboardKeuanganModel;
		$this->addJs($this->config->baseURL . 'public/vendors/chartjs/chart.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/material-icons/css.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		$this->addStyle($this->config->baseURL . 'public/themes/modern/css/dashboard.css');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/dashboard-keuangan.js');
	}
	
	public function index()
	{	
		$this->data['total_pendapatan'] = $this->model->getTotalPendapatan();
		$this->data['total_pengeluaran'] = $this->model->getTotalPengeluaran();
		$this->data['total_tagihan_spp'] = $this->model->getTotalTagihanSPP();
		$this->data['total_utang_pegawai'] = $this->model->getTotalUtangPegawai();
		$this->data['kas'] = $this->model->getSaldoKas();
		$this->data['jumlah_siswa_spp'] = $this->model->getTotalSiswaBelumBayarSPP();
		$this->data['jumlah_pegawai_utang'] = $this->model->getJumlahPegawaiUtang();

		
		$this->data['list_tahun_pendapatan'] = [];
		$this->data['list_tahun_pendapatan'][date('Y')] = date('Y');
		$result = $this->model->getListTahunPendapatan();
		if($result) {
			$this->data['list_tahun_pendapatan'] = [];
			foreach ($result as $val) {
				$this->data['list_tahun_pendapatan'][$val['tahun_bayar']] = $val['tahun_bayar'];
			}
		}
		// echo '<pre>'; print_r($this->data['list_tahun_pendapatan']); die;
		
		$this->data['list_tahun_pengeluaran'] = [];
		$this->data['list_tahun_pengeluaran'][date('Y')] = date('Y');
		$result = $this->model->getListTahunPengeluaran();
		if($result) {
			$this->data['list_tahun_pengeluaran'] = [];
			foreach ($result as $val) {
				$this->data['list_tahun_pengeluaran'][$val['tahun_pengeluaran']] = $val['tahun_pengeluaran'];
			}
		}
		
		// Chart
		$this->data['total_pendapatan_kategori'] = $this->model->getTotalPendapatanByKategori();
		$this->data['total_pengeluaran_kategori'] = $this->model->getTotalPengeluaranByKategori();
		
		$this->view('dashboard-keuangan.php', $this->data);
	}
	
	public function ajaxGetSiswaBaru() {
		$result['total'] = $this->model->getTotalSiswaBaru($_GET['tahun_ajaran']);
		$result['total_gender'] = $this->model->getTotalSiswaBaruByGender($_GET['tahun_ajaran']);
		echo json_encode($result);
	}
	
	public function getDataDTPendapatan() {
		
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
										. btn_link(
												['icon' => 'fas fa-edit'
													, 'url' => base_url() . '/siswa/edit?id=' . $val['id_siswa']
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_siswa']]
													, 'label' => ''
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete-siswa btn-xs'
																	, 'data-id' => $val['id_siswa']
																	, 'data-delete-title' => 'Hapus data siswa : <strong>' . $val['nama'] . '</strong> ?'
																]
													, 'label' => ''
												]) . 
										
										'</div>';
											
			$exp = explode(',', $val['nama_pendapatan_jenis']);
			$pendapatan_jenis = [];
			foreach ($exp as $item) {
				$pendapatan_jenis[$item] = $item;
			}
			$val['nama_pendapatan_jenis'] = join(', ', $pendapatan_jenis);
			$val['nilai_bayar'] = '<div class="text-end">' . format_number($val['nilai_bayar']) . '</div>';
			$val['tgl_bayar'] = format_tanggal($val['tgl_bayar'], 'dd-mm-yyyy', false);
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
	public function getDataDTPengeluaran() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataPengeluaran();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListDataPengeluaran();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$nama_bulan = nama_bulan();
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['ignore_urut'] = $no;			
			$val['ignore_action'] = '<div class="form-inline btn-action-group">'
										. btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1'
																	, 'data-id' => $val['id_pengeluaran']
																	
																]
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_pengeluaran']
																	
																	, 'data-delete-title' => 'Hapus data pengeluaran tanggal ' . format_date($val['tgl_pengeluaran']) .  ' dengan nilai ' . format_number($val['total_pengeluaran']) . ' ?'
																]
												]) . 
										
										'</div>';
			
			$exp = explode(',', $val['nama_pengeluaran']);
			if (count($exp) > 1) {
				$val['nama_pengeluaran'] = '<ul class="list-circle"><li>' . join('</li><li>', $exp) . '</li></ul>';
			}
			
			$exp = explode(',', $val['keterangan']);
			if (count($exp) > 1) {
				$val['keterangan'] = '<ul class="list-circle"><li>' . join('</li><li>', $exp) . '</li></ul>';
			}
			$val['total_pengeluaran'] = '<div class="text-end">' . format_number($val['total_pengeluaran']) . '</div>';
			$val['tgl_pengeluaran'] = '<div class="text-end">' . format_tanggal($val['tgl_pengeluaran'], 'dd-mm-yyyy') . '</div>';
					
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
}