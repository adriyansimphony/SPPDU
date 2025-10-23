<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Models;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use CodeIgniter\Database\Exceptions\DatabaseException;

class RiwayatStatusSiswaModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getAllKelas() {
		$sql ='SELECT * FROM kelas';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getAllStatusSiswa() {
		$sql ='SELECT * FROM status_siswa';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getJmlStatusSiswa() {
		$sql ='SELECT COUNT(*) AS jml FROM siswa_riwayat_status';
		$result = $this->db->query($sql)->getRowArray();
		return $result['jml'];
	}
	
	public function getRiwayatSiswaById($id) {
		$sql ='SELECT siswa_riwayat_status.*, status_siswa.*, siswa.*, 
				kelas.nama_kelas AS nama_kelas, id_tahun_ajaran, tahun_ajaran
				FROM siswa_riwayat_status 
				LEFT JOIN status_siswa USING(id_status_siswa) 
				LEFT JOIN siswa USING(id_siswa)
				LEFT JOIN kelas USING(id_kelas)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				WHERE id_siswa_riwayat_status = ?';
		$result = $this->db->query($sql, $id)->getRowArray();
		return $result;
	}
	
	public function getAllTahunAjaran() {
		$sql = 'SELECT * FROM tahun_ajaran';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function deleteData() {
		$result = $this->db->table('siswa_riwayat_status')->delete(['id_siswa_riwayat_status' => $_POST['id']]);
		return $result;
	}
	
	public function deleteAllStatus() {
		
		$list_table = [
						'siswa_riwayat_status'
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
	
	public function writeExcel() 
	{
		require_once(ROOTPATH . "/app/ThirdParty/PHPXlsxWriter/xlsxwriter.class.php");
		
		$colls = [
					'no' 			=> ['type' => '#,##0', 'width' => 5, 'title' => 'No'],
					'nama' 	=> ['type' => 'string', 'width' => 30, 'title' => 'Nama'],
					'nisn' 	=> ['type' => 'string', 'width' => 12, 'title' => 'NISN'],
					'nis' 	=> ['type' => 'string', 'width' => 10, 'title' => 'NIS'],
					'nama_kelas' 	=> ['type' => 'string', 'width' => 7, 'title' => 'Kelas'],
					'nama_status_siswa' 	=> ['type' => 'string', 'width' => 15, 'title' => 'Status Siswa'],
					'tgl_status' 	=> ['type' => 'string', 'width' => 15, 'title' => 'Tanggal Status'],
					'tahun_ajaran' 	=> ['type' => 'string', 'width' => 15, 'title' => 'Tahun Ajaran'],
					'keterangan' 	=> ['type' => 'string', 'width' => 15, 'title' => 'Keterangan'],
				];
		
		$col_type = $col_width = $col_header = [];
		foreach ($colls as $field => $val) {
			$col_type[$field] = $val['type'];
			$col_header[$field] = $val['title'];
			$col_header_type[$field] = 'string';
			$col_width[] = $val['width'];
		}
		
		// SQL
		$table_column = $colls;
		unset($table_column['no']);
		$table_column = array_keys($table_column);
		
		$table_column = join(', ', $table_column);
		
		$sql = 'SELECT nama, nisn, nis, nama_kelas, nama_status_siswa, tgl_status, tahun_ajaran
				FROM siswa_riwayat_status 
				LEFT JOIN status_siswa USING(id_status_siswa) 
				LEFT JOIN siswa USING(id_siswa)
				LEFT JOIN kelas USING(id_kelas)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)';
		$query = $this->db->query($sql);
		
		// Excel
		$sheet_name = strtoupper('Daftar Siswa');
		$writer = new \XLSXWriter();
		$writer->setAuthor('Jagowebdev');
		
		$writer->writeSheetHeader($sheet_name, $col_header_type, $col_options = ['widths'=> $col_width, 'suppress_row'=>true]);
		$writer->writeSheetRow($sheet_name, $col_header);
		$writer->updateFormat($sheet_name, $col_type);
		
		$no = 1;
		while ($row = $query->getUnbufferedRow('array')) {
			array_unshift($row, $no);
			if (key_exists('tgl_status', $row)) {
				$row['tgl_status'] = format_date($row['tgl_status']);
			}
			$writer->writeSheetRow($sheet_name, $row);
			$no++;
		}
		
		$tmp_file = ROOTPATH . 'public/tmp/riwayat_status_siswa_' . time() . '.xlsx.tmp';
		$writer->writeToFile($tmp_file);
		return $tmp_file;
	}
	
	public function saveData() {
		
		$this->db->transStart();
		
		$id_kelas = $_POST['id_status_siswa'] <= 4 ? $_POST['id_kelas'] : null;
		$exp = explode('-', $_POST['tgl_status']);
		$tgl_status = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		$data_db = [
						'tgl_status' => $tgl_status
						, 'id_status_siswa' => $_POST['id_status_siswa']
						, 'id_kelas' => $_POST['id_kelas']
						, 'id_kelas' => $id_kelas
						, 'id_tahun_ajaran' => $_POST['id_tahun_ajaran']
						, 'keterangan' => $_POST['keterangan']
					];
		
		$this->db->table('siswa_riwayat_status')->update($data_db, ['id_siswa_riwayat_status' => $_POST['id']]);
		$this->db->transComplete();
		if ($this->db->transStatus()) {
			return ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		} else {
			return ['status' => 'error', 'message' => 'Data gagal disimpan'];
		}
	}
	
	public function countAllData($where) {
		$sql = 'SELECT COUNT(*) AS jml FROM siswa' . $where;
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData($where) {

		$columns = $this->request->getPost('columns');
		
		if (!empty($_GET['id'])) {
			$where .= ' AND id_status_siswa = ' . $_GET['id'];  
		}
		// Search
		$search_all = @$this->request->getPost('search')['value'];
		if ($search_all) {
			// Additional Search
			$columns[]['data'] = 'tempat_lahir';
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
		$sql = 'SELECT COUNT(*) as jml_data
				FROM siswa_riwayat_status 
				LEFT JOIN status_siswa USING(id_status_siswa) 
				LEFT JOIN siswa USING(id_siswa)
				LEFT JOIN kelas USING(id_kelas)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				' . $where;
		$total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT siswa_riwayat_status.*, status_siswa.*, siswa.*, nama_kelas, tahun_ajaran
				FROM siswa_riwayat_status 
				LEFT JOIN status_siswa USING(id_status_siswa) 
				LEFT JOIN siswa USING(id_siswa)
				LEFT JOIN kelas USING(id_kelas)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>