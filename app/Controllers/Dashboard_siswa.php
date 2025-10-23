<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\DashboardSiswaModel;

class Dashboard_siswa extends BaseController
{
	public function __construct() {
		parent::__construct();
		$this->model = new DashboardSiswaModel;
		$this->addJs($this->config->baseURL . 'public/vendors/chartjs/chart.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/material-icons/css.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		$this->addStyle($this->config->baseURL . 'public/themes/modern/css/dashboard.css');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/dashboard-siswa.js');
	}
	
	public function index()
	{
		$option_kelas = ['' => 'Semua'];
		$result = $this->model->getKelas();
		if ($result) {
			foreach ($result as $val) {
				$option_kelas[$val['id_kelas']] = 'Kelas ' . $val['nama_kelas'];
			}
		}
		$this->data['option_kelas'] = $option_kelas;
		
		$this->data['total_siswa'] = $this->model->getTotalSiswa();
		$this->data['total_pegawai'] = $this->model->getTotalPegawai();
		$this->data['total_siswa_baru'] = $this->model->getTotalSiswaBaru();
		$this->data['total_siswa_gender'] = $this->model->getTotalSiswaByGender();
		$this->data['total_siswa_baru_gender'] = $this->model->getTotalSiswaBaruByGender();
		$this->data['total_pegawai_gender'] = $this->model->getTotalPegawaiByGender();
		$this->view('dashboard-siswa.php', $this->data);
	}
	
	public function ajaxGetSiswaBaru() {
		$result['total'] = $this->model->getTotalSiswaBaru($_GET['tahun_ajaran']);
		$result['total_gender'] = $this->model->getTotalSiswaBaruByGender($_GET['tahun_ajaran']);
		echo json_encode($result);
	}
	
	public function getDataDTSiswa() {
		
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
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete-siswa btn-xs'
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
	
	public function getDataDTPegawai() {
		
		$this->hasPermission('read_all');
		
		$num_pegawai = $this->model->countAllPegawai();
		$pegawai = $this->model->getListPegawai();
		
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_pegawai;
		$result['recordsFiltered'] = $pegawai['total_filtered'];		
		
		helper('html');
		$path = ROOTPATH . 'public/images/pegawai/';
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($pegawai['data'] as $key => &$val) {
						
			$role = '';
			if ($val['judul_role']) {
				$split = explode(',', $val['judul_role']);
				foreach ($split as $judul_role) {
					$role .= '<span class="badge badge-role bg-secondary me-2 py-2 px-3">' . $judul_role . '</span>';
				}	
			}
						
			$val['judul_role'] = '<div style="white-space:break-spaces">' . $role . '</div>';
			$val['ignore_urut'] = $no;
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
													, 'attr' => ['class' => 'btn btn-danger btn-delete-pegawai btn-xs'
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
			
			$no++;
		}
					
		$result['data'] = $pegawai['data'];
		echo json_encode($result); exit();
	}
}