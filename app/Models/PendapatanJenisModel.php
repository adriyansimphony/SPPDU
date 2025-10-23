<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Models;

class PendapatanJenisModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getJmlJenisPemasukan() {
		$sql = 'SELECT COUNT(*) AS jml_data FROM pendapatan_jenis';
		$result = $this->db->query($sql)->getRowArray();
		return $result['jml_data'];
	}
	
	public function getPemasukanSumber() {
		$sql = 'SELECT * FROM pendapatan_sumber';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function deleteData() {
		$result = $this->db->table('pendapatan_jenis')->delete(['id_pendapatan_jenis' => $_POST['id']]);
		return $result;
	}
	
	public function deleteAllData() {
		
		$list_table = [
						'pendapatan_jenis',
						'pendapatan_jenis_sumber'
					];
					
		try {
			$this->db->transException(true)->transStart();
			
			foreach ($list_table as $table) {
				$this->db->table($table)->emptyTable();
				$sql = 'ALTER TABLE ' . $table . ' AUTO_INCREMENT 1';
				$this->db->query($sql);
			}
			
			$this->db->transComplete();
			
			if ($this->db->transStatus() == true)
				return ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			
			return ['status' => 'error', 'message' => 'Database error'];
			
		} catch (DatabaseException $e) {
			return ['status' => 'error', 'message' => $e->getMessage()];
		}
	}
	
	public function getPemasukanJenisById($id) {
		$sql = 'SELECT *, GROUP_CONCAT(id_pendapatan_sumber) AS id_pendapatan_sumber 
				FROM pendapatan_jenis 
				LEFT JOIN pendapatan_jenis_sumber USING(id_pendapatan_jenis)
				WHERE id_pendapatan_jenis = ?';
		$result = $this->db->query($sql, trim($id))->getRowArray();
		return $result;
	}
	
	public function saveData() 
	{
		$data_db['nama_pendapatan_jenis'] = $_POST['nama_pendapatan_jenis'];
		$data_db['kategori'] = $_POST['kategori'];
		$data_db['using_periode'] = $_POST['using_periode'];
		$data_db['jenis_periode'] = $_POST['using_periode'] == 'Y' ? $_POST['jenis_periode'] : null;
		$data_db['perlu_tagihan_siswa'] = 'N';
		if (in_array(1, $_POST['id_pendapatan_sumber'])) {
			$data_db['perlu_tagihan_siswa'] = $_POST['perlu_tagihan_siswa'];
		}
		
		$query = false;
		
		$this->db->table('pendapatan_jenis_sumber')->delete(['id_pendapatan_jenis' => $_POST['id']]);
		if ($_POST['id']) 
		{
			$query = $this->db->table('pendapatan_jenis')->update($data_db, ['id_pendapatan_jenis' => $_POST['id']]);	
			$result['id_pendapatan_jenis'] = $_POST['id'];
		} else {
			$query = $this->db->table('pendapatan_jenis')->insert($data_db);
			if ($query) {
				$result['id_pendapatan_jenis'] = $this->db->insertID();
			}
		}
		
		foreach ($_POST['id_pendapatan_sumber'] as $val) {
			$data_db_sumber[] = ['id_pendapatan_jenis' => $result['id_pendapatan_jenis'], 'id_pendapatan_sumber' => $val];
		}
		$this->db->table('pendapatan_jenis_sumber')->insertBatch($data_db_sumber);
		
		
		if ($query) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
	
	public function countAllData() {
		$sql = 'SELECT COUNT(*) AS jml FROM pendapatan_jenis ';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData() {

		$columns = $this->request->getPost('columns');

		// Search
		$search_all = @$this->request->getPost('search')['value'];
		$where = ' WHERE 1=1 ';
		if ($search_all) {
			
			foreach ($columns as $val) {
				
				if (strpos($val['data'], 'ignore_search') !== false) 
					continue;
				
				if (strpos($val['data'], 'ignore') !== false)
					continue;
				
				$where_col[] = $val['data'] . ' LIKE "%' . $search_all . '%"';
			}
			 $where .= ' AND (' . join(' OR ', $where_col) . ') ';
		}
		
		// Order		
		$order_data = $this->request->getPost('order');
		$order = '';
		if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by;
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml_data
				FROM (
					SELECT id_pendapatan_jenis FROM pendapatan_jenis 
					LEFT JOIN pendapatan_jenis_sumber USING(id_pendapatan_jenis)
					LEFT JOIN pendapatan_sumber USING(id_pendapatan_sumber)
					' . $where . ' GROUP BY id_pendapatan_jenis
				) AS tabel';
		$total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT *, GROUP_CONCAT(nama_pendapatan_sumber SEPARATOR ", ") AS nama_pendapatan_sumber, GROUP_CONCAT(id_pendapatan_sumber) AS id_pendapatan_sumber
				FROM pendapatan_jenis 
				LEFT JOIN pendapatan_jenis_sumber USING(id_pendapatan_jenis)
				LEFT JOIN pendapatan_sumber USING(id_pendapatan_sumber)
				' . $where . ' GROUP BY id_pendapatan_jenis ' . $order  . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>