<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Models;

class PegawaiUtangModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getJmlData() {
		$sql = 'SELECT COUNT(*) AS jml_data FROM pegawai_utang';
		$result = $this->db->query($sql)->getRowArray();
		return $result['jml_data'];
	}
	
	public function getAllMetodePembayaran() {
		$sql = 'SELECT * FROM jenis_bayar';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getDataBayarByIdUtang($id) 
	{
		$sql = 'SELECT * 
				FROM pendapatan
				LEFT JOIN pendapatan_detail USING(id_pendapatan)
				LEFT JOIN pegawai_utang USING(id_pegawai_utang)
				LEFT JOIN pegawai USING(id_pegawai)
				WHERE id_pegawai_utang = ?';
		$result = $this->db->query($sql, $id)->getResultArray();
		if ($result) {
			foreach ($result as &$val) {
				$sql = 'SELECT * 
						FROM pendapatan_detail 
						LEFT JOIN pegawai_utang USING(id_pegawai_utang)
						WHERE id_pendapatan = ?';
				$detail = $this->db->query($sql, $val['id_pendapatan'])->getResultArray();
				// echo '<pre>'; print_r($detail); die;
				foreach ($detail as &$item) {
					$item['nilai_bayar'] = format_number($item['nilai_bayar']);
					$item['nilai_utang'] = format_number($item['nilai_utang']);
					$item['tgl_utang'] = format_date($item['tgl_utang']);
				}
				$val['detail'] = $detail;
			}
		}
		return $result;
	}
	
	public function getTotalBayarByIdUtang($id) {
		$sql = 'SELECT SUM(nilai_bayar) AS total_bayar FROM pendapatan_detail WHERE id_pegawai_utang = ?';
		$result = $this->db->query($sql, $id)->getRowArray();
		return $result['total_bayar'];
	}
	
	public function deleteDataByIdBayar($id) {
		$this->db->transStart();
		$this->db->table('pendapatan')->delete(['id_pendapatan' => $id]);
		$this->db->table('pendapatan_detail')->delete(['id_pendapatan' => $id]);
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	public function deleteDataByIdUtang($id) {
		// $this->db->transStart();
		$sql = 'DELETE FROM pendapatan WHERE id_pendapatan IN (SELECT id_pendapatan FROM pendapatan_detail WHERE id_pegawai_utang = ?)';
		$this->db->query($sql, $id);
		$this->db->table('pendapatan_detail')->delete(['id_pegawai_utang' => $id]);
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	public function deleteData() {
		$this->db->table('pegawai_utang')->delete(['id_pegawai_utang' => $_POST['id']]);
		$this->db->table('pegawai_utang_bayar')->delete(['id_pegawai_utang' => $_POST['id']]);
		return $result;
	}
	
	public function deleteAllData() {
		
		$list_table = [
						'pegawai_utang'
					];
					
		try {
			$this->db->transException(true)->transStart();
			
			foreach ($list_table as $table) {
				$this->db->table($table)->emptyTable();
				$this->resetAutoIncrement($table);
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
					'no' 	=> ['type' => '#,##0', 'width' => 5, 'title' => 'No'],
					'nama' 	=> ['type' => 'string', 'width' => 30, 'title' => 'Nama'],
					'nip' 	=> ['type' => 'string', 'width' => 12, 'title' => 'NIP'],
					'jabatan' 	=> ['type' => 'string', 'width' => 20, 'title' => 'Jabatan'],
					'utang' 	=> ['type' => '#,##0', 'width' => 14, 'title' => 'Utang'],
					'tgl_utang' => ['type' => 'string', 'width' => 15, 'title' => 'Tanggal Utang'],
					'dibayar' 	=> ['type' => '#,##0', 'width' => 15, 'title' => 'Dibayar'],
					'saldo_utang' => ['type' => '#,##0', 'width' => 15, 'title' => 'Saldo Utang']
				];
		
		$col_type = $col_width = $col_header = [];
		foreach ($colls as $field => $val) {
			$col_type[$field] = $val['type'];
			$col_header[$field] = $val['title'];
			$col_header_type[$field] = 'string';
			$col_width[] = $val['width'];
		}
		
		// SQL
		$sql = 'SELECT nama, nip_pegawai, GROUP_CONCAT(nama_jabatan SEPARATOR " + ") AS nama_jabatan
						, nilai_utang, tgl_utang, IFNULL(nilai_bayar,0) AS nilai_bayar, nilai_utang - IFNULL(nilai_bayar,0) AS saldo_utang
				FROM pegawai_utang
				LEFT JOIN (SELECT id_pegawai_utang, SUM(nilai_bayar) AS nilai_bayar 
								FROM pendapatan_detail 
								GROUP BY id_pegawai_utang
							) AS tabel USING(id_pegawai_utang)
				LEFT JOIN pegawai USING(id_pegawai)
				LEFT JOIN pegawai_jabatan USING(id_pegawai)
				LEFT JOIN jabatan USING(id_jabatan)
				GROUP BY id_pegawai_utang';
		$query = $this->db->query($sql);
		
		// Excel
		$sheet_name = strtoupper('Daftar Utang Pegawai');
		$writer = new \XLSXWriter();
		$writer->setAuthor('Jagowebdev');
		
		$writer->writeSheetHeader($sheet_name, $col_header_type, $col_options = ['widths'=> $col_width, 'suppress_row'=>true]);
		$writer->writeSheetRow($sheet_name, $col_header);
		$writer->updateFormat($sheet_name, $col_type);
		
		$no = 1;
		while ($row = $query->getUnbufferedRow('array')) {
			array_unshift($row, $no);
			if (key_exists('tgl_utang', $row)) {
				$row['tgl_utang'] = format_tanggal($row['tgl_utang'], 'dd-mm-yyyy');
			}
			$writer->writeSheetRow($sheet_name, $row);
			$no++;
		}
		
		$tmp_file = ROOTPATH . 'public/tmp/daftar_utang_pegawai_' . time() . '.xlsx.tmp';
		$writer->writeToFile($tmp_file);
		return $tmp_file;
	}
	
	public function getPegawaiUtangById($id) {
		$sql = 'SELECT *
				FROM pegawai_utang
				LEFT JOIN jenis_bayar USING(id_jenis_bayar)
				LEFT JOIN pegawai USING(id_pegawai)
				WHERE id_pegawai_utang = ?';
		$result = $this->db->query($sql, trim($id))->getRowArray();
		return $result;
	}
	
	public function getPegawaiUtangBayarById($id) 
	{
		$id = trim($id);
		$sql = 'SELECT *, pendapatan.keterangan AS keterangan, pendapatan.id_jenis_bayar AS id_jenis_bayar
				FROM pendapatan
				LEFT JOIN (SELECT * FROM pendapatan_detail GROUP BY id_pendapatan) AS tabel USING(id_pendapatan)
				LEFT JOIN pegawai_utang USING(id_pegawai_utang)
				LEFT JOIN pegawai USING(id_pegawai)
				WHERE id_pendapatan = ?';
		$result = $this->db->query($sql, $id)->getRowArray();
		
		if ($result) {
			$sql = 'SELECT *, tabel_bayar.total_bayar - nilai_bayar AS total_bayar, nilai_utang - tabel_bayar.total_bayar  AS kurang
					FROM pendapatan_detail
					LEFT JOIN pendapatan USING(id_pendapatan)
					LEFT JOIN pegawai_utang USING(id_pegawai_utang)
					LEFT JOIN pegawai USING(id_pegawai)
					LEFT JOIN (
						SELECT SUM(nilai_bayar) AS total_bayar, id_pegawai_utang
						FROM pendapatan_detail
						LEFT JOIN pendapatan USING(id_pendapatan)
						GROUP BY id_pegawai_utang
					) AS tabel_bayar USING(id_pegawai_utang)
					WHERE id_pendapatan = ?';
			$result['detail'] = $this->db->query($sql, $id)->getResultArray();
					
		}
		return $result;
	}
	
	public function saveData() 
	{
		$data_db['id_pegawai'] = $_POST['id_pegawai'];
		$data_db['id_jenis_bayar'] = $_POST['id_jenis_bayar'];
		$data_db['keterangan'] = $_POST['keterangan'];
		$data_db['nilai_utang'] = str_replace('.', '', $_POST['nilai_utang']);
		$exp = explode('-', $_POST['tgl_utang']);
		$data_db['tgl_utang'] = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		$data_db['using_invoice'] = 'Y';
		$query = false;
		
		if ($_POST['id']) 
		{
			$query = $this->db->table('pegawai_utang')->update($data_db, ['id_pegawai_utang' => $_POST['id']]);	
			$result['id_pegawai_utang'] = $_POST['id'];
		} else {
			$query = $this->db->table('pegawai_utang')->insert($data_db);
			if ($query) {
				$result['id_pegawai_utang'] = $this->db->insertID();
			}
		}
		
		if ($query) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
	
	public function saveDataBayar() 
	{
		// $data_db['id_pegawai_utang'] = $_POST['id_pegawai_utang'];
		// $data_db['using_i'] = $_POST['id_jenis_bayar'];
		
		// $data_db['nilai_utang'] = str_replace('.', '', $_POST['nilai_utang']);
		// $exp = explode('-', $_POST['tgl_invoice']);
		// $data_db['tgl_invoice'] = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		// $this->db->transStart();
		$_POST['using_invoice'] = 'Y';
		
		$data_db['id_siswa'] = null;
		$data_db['id_pembayar'] = null;
		$data_db['total_pembayaran'] = null;
		$data_db['kembali'] = null;
		$data_db['using_invoice'] = 'Y';
		
		$exp = explode('-', $_POST['tgl_bayar']);
		$data_db['tgl_bayar'] = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		$data_db['keterangan'] = $_POST['keterangan'];
		$data_db['id_jenis_bayar'] = $_POST['id_jenis_bayar'];
		
		$total_bayar = 0;
		foreach ($_POST['nilai_bayar'] as $index => $val) {
			$total_bayar += str_replace('.', '', $val);
		}
		
		$data_db['total_bayar'] = $total_bayar;
		
		// Invoice
		$sql = 'LOCK TABLES pendapatan WRITE, setting WRITE, pendapatan_detail WRITE';
		$this->db->query($sql);
				
		if (empty($_POST['id'])) 
		{
			$data_db['no_invoice'] = null;
			$data_db['no_squence'] = null;
			$data_db['tgl_invoice'] = null;
		
			if ($_POST['using_invoice'] == 'Y') {
				$invoice = $this->generateInvoice();
				$data_db['no_invoice'] = $invoice['no_invoice'];
				$data_db['no_squence'] = $invoice['no_squence'];
				$exp = explode('-', $_POST['tgl_invoice']);
				$data_db['tgl_invoice'] = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
			}
						
		} else {
			if ($_POST['using_invoice'] == 'Y') {
				$sql = 'SELECT * FROM pendapatan WHERE id_pendapatan = ?';
				$result = $this->db->query($sql, $_POST['id'])->getRowArray();
				if (!$result['no_invoice']) {
					$invoice = $this->generateInvoice();
					$data_db['no_invoice'] = $invoice['no_invoice'];
					$data_db['no_squence'] = $invoice['no_squence'];
				}
				$exp = explode('-', $_POST['tgl_invoice']);
				$data_db['tgl_invoice'] = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
			}
		}
		
		//-- Invoice
		
		if ($_POST['id']) 
		{
			$data_db['tgl_update'] = date('Y-m-d');;
			$data_db['id_pegawai_update'] = $_SESSION['pegawai']['id_pegawai'];
			
			$this->db->table('pendapatan')->update($data_db, ['id_pendapatan' => $_POST['id']]);	
			$result['id_pendapatan'] = $_POST['id'];
		} else {
			$data_db['tgl_input'] = date('Y-m-d');;
			$data_db['id_pegawai_input'] = $_SESSION['pegawai']['id_pegawai'];
			$query = $this->db->table('pendapatan')->insert($data_db);
			if ($query) {
				$result['id_pendapatan'] = $this->db->insertID();
			}
		}
		
		foreach ($_POST['nilai_bayar'] as $index => $val) 
		{
			$detail = json_decode($_POST['detail_utang'][$index], true);
			$data_db_detail[] = ['id_pendapatan' => $result['id_pendapatan'],
									'nilai_bayar' => str_replace('.', '', $val),
									'id_pegawai_utang' => $detail['id_pegawai_utang'],
									'id_pendapatan_jenis' => 13
								];
		}
		$this->db->table('pendapatan_detail')->delete(['id_pendapatan' => $result['id_pendapatan']]);
		$this->db->table('pendapatan_detail')->insertBatch($data_db_detail);
		$this->db->transComplete();
		
		if ($this->db->transStatus()) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
	
	private function generateInvoice() {
		$sql = 'SELECT * FROM setting WHERE type="invoice"';
		$result = $this->db->query($sql)->getResultArray();
		foreach ($result as $val) {
			if ($val['param'] == 'no_invoice') {
				$pola_no_invoice = $val['value'];
			}
			
			if ($val['param'] == 'jml_digit') {
				$jml_digit = $val['value'];
			}
		}
		
		$sql = 'SELECT MAX(no_squence) AS value FROM pendapatan WHERE tgl_invoice LIKE "' . date('Y') . '%"';
		$result = $this->db->query($sql)->getRowArray();
		$no_squence = $result['value'] + 1;
		$no_invoice = str_pad($no_squence, $jml_digit, "0", STR_PAD_LEFT);
		$no_invoice = str_replace('{{nomor}}', $no_invoice, $pola_no_invoice);
		$no_invoice = str_replace('{{tahun}}', date('Y'), $no_invoice);
		
		return ['no_squence' => $no_squence, 'no_invoice' => $no_invoice];
	}
	
	public function countAllData() {
		$sql = 'SELECT COUNT(*) AS jml FROM pegawai_utang ';
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
				FROM pegawai_utang 
				LEFT JOIN pegawai USING(id_pegawai)
				LEFT JOIN pegawai_jabatan USING(id_pegawai)
				LEFT JOIN jabatan USING(id_jabatan)
				LEFT JOIN (SELECT SUM(nilai_bayar) AS total_pembayaran, id_pegawai_utang
							FROM pendapatan_detail
							GROUP BY id_pegawai_utang)
					AS tabel_bayar USING(id_pegawai_utang)
				' . $where;
		$total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT *, GROUP_CONCAT(DISTINCT nama_jabatan SEPARATOR " + ") AS nama_jabatan, 
					IFNULL(total_pembayaran, 0) AS total_pembayaran,
					IF(nilai_utang - IFNULL(total_pembayaran, 0) > 0, "kurang", "lunas") AS status
				FROM pegawai_utang
				LEFT JOIN pegawai USING(id_pegawai)
				LEFT JOIN pegawai_jabatan USING(id_pegawai)
				LEFT JOIN jabatan USING(id_jabatan)
				LEFT JOIN (SELECT IFNULL(SUM(nilai_bayar), 0) AS total_pembayaran, id_pegawai_utang
							FROM pendapatan_detail
							GROUP BY id_pegawai_utang)
					AS tabel_bayar USING(id_pegawai_utang)
				' . $where . ' GROUP BY id_pegawai_utang ' . $order  . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	public function countAllPegawai($where = null) {
		$query = $this->db->query('SELECT COUNT(*) as jml FROM pegawai' . $where)->getRow();
		return $query->jml;
	}
	
	public function getListPegawai() {
		
		// Get pegawai
		$columns = $this->request->getPost('columns');
		$order_by = '';
		
		// Search
		$where  = ' WHERE 1=1 ';
		$search_all = @$this->request->getPost('search')['value'];
		if ($search_all) {
			
			foreach ($columns as $val) {
				if (strpos($val['data'], 'ignore') !== false)
					continue;
				
				$where_col[] = $val['data'] . ' LIKE "%' . $search_all . '%"';
			}
			 $where .= ' AND (' . join(' OR ', $where_col) . ') ';
		}
		
		// Order
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		
		$order_data = $this->request->getPost('order');
		$order = '';
		if (!empty($_POST['columns']) && strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by . ' LIMIT ' . $start . ', ' . $length;
		}
		
		$sql = 'SELECT COUNT(*) as jml FROM
				(SELECT pegawai.*, GROUP_CONCAT(DISTINCT nama_jabatan SEPARATOR " + ") AS nama_jabatan FROM pegawai 
				LEFT JOIN pegawai_jabatan USING(id_pegawai)
				LEFT JOIN jabatan USING(id_jabatan)
				' . $where . '
				GROUP BY id_pegawai) AS tabel';
				
		$query = $this->db->query($sql)->getRowArray();
		$total_filtered = $query['jml'];
		
		$sql = 'SELECT pegawai.*, GROUP_CONCAT(DISTINCT nama_jabatan SEPARATOR " + ") AS nama_jabatan FROM pegawai 
				LEFT JOIN pegawai_jabatan USING(id_pegawai)
				LEFT JOIN jabatan USING(id_jabatan)
				' . $where . '
				GROUP BY id_pegawai
				' . $order;
		
		
		$data = $this->db->query($sql)->getResultArray();
		return ['data' => $data, 'total_filtered' => $total_filtered];
		
	}
	
	
	public function countAllUtangByIdPegawai($id) {
		$query = $this->db->query('SELECT COUNT(*) as jml FROM pegawai_utang WHERE id_pegawai = ? ', $id)
				->getRow();
		return $query->jml;
	}
	
	public function getListUtangByIdPegawai($id) {
		
		// Get pegawai
		$columns = $this->request->getPost('columns');
		$order_by = '';
		
		// Search
		$where  = ' WHERE 1=1 ';
		$search_all = @$this->request->getPost('search')['value'];
		if ($search_all) {
			
			foreach ($columns as $val) {
				if (strpos($val['data'], 'ignore') !== false)
					continue;
				
				$where_col[] = $val['data'] . ' LIKE "%' . $search_all . '%"';
			}
			 $where .= ' AND (' . join(' OR ', $where_col) . ') ';
		}
		
		// Order
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		
		$order_data = $this->request->getPost('order');
		$order = '';
		if (!empty($_POST['columns']) && strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by . ' LIMIT ' . $start . ', ' . $length;
		}
		
		$sql = 'SELECT COUNT(*) as jml 
				FROM pegawai_utang
				LEFT JOIN pegawai USING(id_pegawai)
				LEFT JOIN pegawai_jabatan USING(id_pegawai)
				LEFT JOIN jabatan USING(id_jabatan)
				LEFT JOIN (SELECT id_pegawai_utang, SUM(nilai_bayar) AS total_bayar 
							FROM pendapatan_detail 
							GROUP BY id_pegawai_utang
						) AS tabel USING(id_pegawai_utang)
				' . $where . ' AND id_pegawai = ? GROUP BY id_pegawai_utang ';
				
		$query = $this->db->query($sql, $id)->getRowArray();
		$total_filtered = $query ? $query['jml'] : 0;
		
		$sql = 'SELECT *, nilai_utang - IFNULL(total_bayar,0) AS kurang, GROUP_CONCAT(DISTINCT nama_jabatan SEPARATOR " + ") AS nama_jabatan, 
				IFNULL(total_bayar, 0) AS total_bayar
				FROM pegawai_utang
				LEFT JOIN pegawai USING(id_pegawai)
				LEFT JOIN pegawai_jabatan USING(id_pegawai)
				LEFT JOIN jabatan USING(id_jabatan)
				LEFT JOIN (SELECT id_pegawai_utang, SUM(nilai_bayar) AS total_bayar 
							FROM pendapatan_detail 
							GROUP BY id_pegawai_utang
						) AS tabel USING(id_pegawai_utang)
				' . $where . ' AND id_pegawai = ? GROUP BY id_pegawai_utang ' . $order;
		
		
		$data = $this->db->query($sql, $id)->getResultArray();
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	public function countAllBayarByIdUtang($id) {
		$sql = 'SELECT COUNT(*) as jml
				FROM pendapatan_detail
				WHERE id_pegawai_utang = ?';
		$query = $this->db->query($sql, $id)
				->getRow();
		return $query->jml;
	}
	
	public function getListBayarByIdUtang($id) {
	
		$columns = $this->request->getPost('columns');
		$order_by = '';
		
		// Search
		$where  = ' WHERE 1=1 ';
		$search_all = @$this->request->getPost('search')['value'];
		if ($search_all) {
			
			foreach ($columns as $val) {
				if (strpos($val['data'], 'ignore') !== false)
					continue;
				
				$where_col[] = $val['data'] . ' LIKE "%' . $search_all . '%"';
			}
			 $where .= ' AND (' . join(' OR ', $where_col) . ') ';
		}
		
		// Order
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		
		$order_data = $this->request->getPost('order');
		$order = '';
		if (!empty($_POST['columns']) && strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by . ' LIMIT ' . $start . ', ' . $length;
		}
		
		$sql = 'SELECT COUNT(*) AS jml 
				FROM pendapatan_detail
				LEFT JOIN pendapatan USING(id_pendapatan)
				' . $where . ' AND id_pegawai_utang = ?';
				
		$query = $this->db->query($sql, $id)->getRowArray();
		$total_filtered = $query ? $query['jml'] : 0;
		
		$sql = 'SELECT * 
				FROM pendapatan_detail
				LEFT JOIN pendapatan USING(id_pendapatan)
				' . $where . ' AND id_pegawai_utang = ? ' . $order;
		
		$result = $this->db->query($sql, $id)->getResultArray();
		
		if ($result) {
			foreach ($result as &$val) {
				$sql = 'SELECT * FROM pendapatan_detail
						LEFT JOIN pegawai_utang USING(id_pegawai_utang)
						WHERE id_pendapatan = ' . $val['id_pendapatan'];
				$detail = $this->db->query($sql)->getResultArray();
				foreach ($detail as &$item) {
					$item['nilai_bayar'] = format_number($item['nilai_bayar']);
					$item['nilai_utang'] = format_number($item['nilai_utang']);
					$item['tgl_utang'] = format_date($item['tgl_utang']);
				}
				$val['detail'] = $detail;
			}
		}
		
		$data = $result;
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>