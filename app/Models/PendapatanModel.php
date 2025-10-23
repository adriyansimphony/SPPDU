<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Models;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class PendapatanModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getListUtangPegawai() {
		$sql = 'SELECT *, nilai_utang - IFNULL(nilai_bayar,0) AS saldo_utang, GROUP_CONCAT(nama_jabatan SEPARATOR " + ") AS nama_jabatan 
				FROM pegawai_utang
				LEFT JOIN (SELECT id_pegawai_utang, SUM(nilai_bayar) AS nilai_bayar 
								FROM pendapatan_detail 
								GROUP BY id_pegawai_utang
							) AS tabel USING(id_pegawai_utang)
				LEFT JOIN pegawai USING(id_pegawai)
				LEFT JOIN pegawai_jabatan USING(id_pegawai)
				LEFT JOIN jabatan USING(id_jabatan)
				GROUP BY id_pegawai_utang
				HAVING saldo_utang > 0';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getListTagihanSiswa() {
		$sql = 'SELECT *, nilai_tagihan - IFNULL(nilai_bayar_tagihan, 0) AS saldo_tagihan FROM siswa_tagihan
				LEFT JOIN siswa USING(id_siswa)
				LEFT JOIN siswa_kelas USING(id_siswa)
				LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				LEFT JOIN (SELECT id_siswa_tagihan, SUM(nilai_bayar) AS nilai_bayar_tagihan
								FROM pendapatan_detail
								GROUP BY id_siswa_tagihan
							) AS tabel USING(id_siswa_tagihan)';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getListPembayar() {
		$sql = 'SELECT * 
				FROM pembayar';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getListPendapatan() {
		$sql = 'SELECT * 
				FROM pendapatan_jenis';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getJmlPendapatan() {
		$sql = 'SELECT COUNT(*) AS jml';
		$result = $this->db->query($sql)->getRowArray();
		return $result['jml'];
	}
	
	public function writeExcel() 
	{
		require_once(ROOTPATH . "/app/ThirdParty/PHPXlsxWriter/xlsxwriter.class.php");
		
		$colls = [
					'no' 			=> ['type' => '#,##0', 'width' => 5, 'title' => 'No'],
					'nama' 	=> ['type' => 'string', 'width' => 30, 'title' => 'Nama'],
					'nis' 	=> ['type' => 'string', 'width' => 8, 'title' => 'nis'],
					'kelas' 	=> ['type' => 'string', 'width' => 6, 'title' => 'kelas'],
					'jenis_pendapatan' 	=> ['type' => 'string', 'width' => 16, 'title' => 'jenis_pendapatan'],
					'no_invoice' 	=> ['type' => 'string', 'width' => 21, 'title' => 'No. Invoice'],
					'tgl_invoice' 	=> ['type' => 'string', 'width' => 21, 'title' => 'Tgl. Invoice'],
					'nama_pembayaran' 	=> ['type' => 'string', 'width' => 22, 'title' => 'Nama Pembayaran'],
					'total_bayar' 	=> ['type' => '#,##0', 'width' => 16, 'title' => 'Total Pembayaran'],
					'tgl_bayar' 	=> ['type' => 'string', 'width' => 30, 'title' => 'Tanggal Pembayaran']
				];
		
		$col_type = $col_width = $col_header = [];
		foreach ($colls as $field => $val) {
			$col_type[$field] = $val['type'];
			$col_header[$field] = $val['title'];
			$col_header_type[$field] = 'string';
			$col_width[] = $val['width'];
		}
		
		// SQL
		$where = ' WHERE 1=1';
		$having = '';
		if (!empty($_GET['tampilkan_pendapatan'])) {
			$having .= ' HAVING jenis_pendapatan="' . $_GET['tampilkan_pendapatan'] . '"';
		}
		
		if (!empty($_GET['daterange'])) {
			$exp = explode(' s.d. ', $_GET['daterange']);
			list($d, $m, $y) = explode('-', $exp[0]);
			$start_date = $y . '-' . $m . '-' . $d;
			
			list($d, $m, $y) = explode('-', $exp[1]);
			$end_date = $y . '-' . $m . '-' . $d;
			$where .= ' AND tgl_bayar >= "' . $start_date . '" AND tgl_bayar <= "' . $end_date . '" ';
		}
		$sql = 'SELECT CONCAT(IFNULL(siswa.nama, ""), IFNULL(nama_pembayar, ""), IFNULL(pegawai.nama, "")) AS nama
				, nis
				, nama_kelas
				, IF(pendapatan.id_siswa != "", "Siswa", IF(id_pegawai_utang != "", "Utang Pegawai", "Lainnya")) AS jenis_pendapatan
				, no_invoice
				, tgl_invoice
				, GROUP_CONCAT(IF(periode_bulan, periode_bulan, "-")) AS periode_bulan
				, GROUP_CONCAT(IF(periode_tahun, periode_tahun, "-")) AS periode_tahun
				, GROUP_CONCAT(IF(tahun_ajaran, tahun_ajaran, "-")) AS tahun_ajaran
				, GROUP_CONCAT(nama_pendapatan_jenis) AS nama_pendapatan_jenis
				, total_bayar
				, tgl_bayar
				FROM pendapatan
				LEFT JOIN siswa USING(id_siswa)
				LEFT JOIN siswa_kelas USING(id_siswa)
				LEFT JOIN pembayar USING(id_pembayar)
				LEFT JOIN pendapatan_detail USING(id_pendapatan)
				LEFT JOIN pegawai_utang USING(id_pegawai_utang)
				LEFT JOIN pegawai USING(id_pegawai)
				LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
				LEFT JOIN siswa_tagihan USING(id_siswa_tagihan)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				' . $where . ' GROUP BY id_pendapatan ' . $having;
				
		$query = $this->db->query($sql);
		
		// Excel
		$sheet_name = strtoupper('Data Pendapatan');
		$writer = new \XLSXWriter();
		$writer->setAuthor('Jagowebdev');
		
		$writer->writeSheetHeader($sheet_name, $col_header_type, $col_options = ['widths'=> $col_width, 'suppress_row'=>true]);
		$writer->writeSheetRow($sheet_name, $col_header);
		$writer->updateFormat($sheet_name, $col_type);
		
		$no = 1;
		while ($row = $query->getUnbufferedRow('array')) {
			array_unshift($row, $no);
			$exp_jenis = explode(',', $row['nama_pendapatan_jenis']);
			$exp_periode_bulan = explode(',', $row['periode_bulan']);
			$exp_periode_tahun = explode(',', $row['periode_tahun']);
			$exp_tahun_ajaran = explode(',', $row['tahun_ajaran']);
			$list_pembayaran = [];
			$nama_bulan = nama_bulan();
			if ($exp_jenis && count($exp_jenis) > 1) 
			{
				foreach ($exp_jenis as $index => $item) {
					
					$pembayaran = $item;
					if ($exp_periode_bulan[$index] != '-') {
						$pembayaran .= ' ' . $nama_bulan[$exp_periode_bulan[$index]] . ' ' . $exp_periode_tahun[$index];
					}
					
					if ($exp_tahun_ajaran[$index] != '-') {
						$pembayaran .= ' ' . $exp_tahun_ajaran[$index];
					}
					if (!in_array($pembayaran, $list_pembayaran)) {
						$list_pembayaran[] = $pembayaran;
					}
				}
				if (count($list_pembayaran) == 1){
					$row['nama_pendapatan_jenis'] = $list_pembayaran[0];
				} else {
					$row['nama_pendapatan_jenis'] = join(',', $list_pembayaran);
				}
			}
			$row['tgl_invoice'] = format_tanggal($row['tgl_invoice'], 'dd-mm-yyyy');
			$row['tgl_bayar'] = format_tanggal($row['tgl_bayar'], 'dd-mm-yyyy');
			unset($row['periode_bulan'], $row['periode_tahun'], $row['tahun_ajaran']);
			
			$writer->writeSheetRow($sheet_name, $row);
			$no++;
		}
		
		$tmp_file = ROOTPATH . 'public/tmp/pendapatan_' . time() . '.xlsx.tmp';
		$writer->writeToFile($tmp_file);
		return $tmp_file;
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
	
	public function uploadExcel() 
	{
		ini_set('max_execution_time', '900');
		
		helper(['upload_file', 'format']);		
		$file = $this->request->getFile('file_excel');
		if (! $file->isValid())
		{
			throw new RuntimeException($file->getErrorString().'('.$file->getError().')');
		}
				
		require_once 'app/ThirdParty/Spout/src/Spout/Autoloader/autoload.php';
		
		$path = ROOTPATH . 'public/tmp/';
		$filename = upload_file($path, $_FILES['file_excel']);
		$reader = ReaderEntityFactory::createReaderFromFile($path . $filename);
		$reader->open($path . $filename);
		
		$id_user = $this->session->get('pegawai')['id_pegawai'];
		$this->db->transStart();
		$num = 0;
		foreach ($reader->getSheetIterator() as $sheet) 
		{
			if (strtolower($sheet->getName()) == 'data') 
			{
				$pendapatan = [];
				$siswa_pendapatan_detail = [];
				$utang_pegawai = [];
				$utang_pegawai_detail = [];
				foreach ($sheet->getRowIterator() as $num_row => $row) 
				{
					$cols = $row->toArray();
					if ($num_row == 1) {
						$field_table = $cols;
						$field_name = array_map('strtolower', $field_table);
						continue;
					}
					
					$data_value = [];
					foreach ($field_name as $num_col => $field) 
					{
						$val = null;
						if (key_exists($num_col, $cols) && $cols[$num_col] != '') {
							$val = $cols[$num_col];
						}
						
						if ($val instanceof \DateTime) {
							$val = $val->format('Y-m-d H:i:s');
						}
						
						$data_value[strtolower($field)] = trim($val);
					}
					
					if ($data_value) {
												
						$id_pendapatan_jenis = null;
						$id_tagihan_siswa = null;
						$id_pegawai_utang = null;
						$id_pembayar = null;
						$id_siswa = null;
						$using_invoice = 'Y';
						
						if ($data_value['nama_tagihan_siswa']) 
						{
							$exp = explode('_', $data_value['nama_tagihan_siswa']);
							$id_tagihan_siswa = end($exp);
							$sql = 'SELECT id_siswa, id_pendapatan_jenis FROM siswa_tagihan WHERE id_siswa_tagihan = ?';
							$tagihan_siswa = $this->db->query($sql, $id_tagihan_siswa)->getRowArray();
							$id_pendapatan_jenis = $tagihan_siswa['id_pendapatan_jenis'];
							$id_siswa = $tagihan_siswa['id_siswa'];
						} else if ($data_value['utang_pegawai']) 
						{
							$id_pendapatan_jenis = 13;
							$exp = explode('_', $data_value['utang_pegawai']);
							$id_pegawai_utang = end($exp);
						} else if ($data_value['nama_pembayar']) 
						{
							$exp = explode('_', $data_value['nama_pembayar']);
							$id_pembayar = end($exp);
							
							$exp = explode('_', $data_value['nama_pendapatan']);
							$id_pendapatan_jenis = end($exp);
							$using_invoice = $data_value['using_invoice'] == 'Ya' ? 'Y' : 'N';
						}
						
						// Tgl Invoice
						$tgl_invoice = null;
						if ($using_invoice == 'Y') {
							if (empty($data_value['tgl_invoice'])) {
								$data_value['tgl_invoice'] = date('d-m-Y');
							}
							list ($d, $m, $y) = explode('-', $data_value['tgl_invoice']);
							$tgl_invoice = $y . '-' . $m . '-' . $d;
						}
						
						list ($d, $m, $y) = explode('-', $data_value['tgl_bayar']);
						$tgl_bayar = $y . '-' . $m . '-' . $d;
						$id_jenis_bayar = strtolower($data_value['jenis_pembayaran']) == 'tunai' ? 1 : 2;
						
						$pendapatan[$data_value['kelompok']] = [
															'id_siswa' => $id_siswa
															, 'id_pembayar' => $id_pembayar
															, 'using_invoice' => $using_invoice
															, 'no_invoice' => ''
															, 'no_squence' => ''
															, 'tgl_invoice' => $tgl_invoice
															, 'id_jenis_bayar' => $id_jenis_bayar
															, 'total_bayar' => ''
															, 'tgl_bayar' => $tgl_bayar
															, 'keterangan' => $data_value['keterangan']
															, 'id_pegawai_input' => $id_user
															, 'tgl_input' => date('Y-m-d')
														];
						
						
						$pendapatan_detail[$data_value['kelompok']][] = [
															'id_pendapatan' => ''
															, 'id_pendapatan_jenis' => $id_pendapatan_jenis
															, 'id_siswa_tagihan' => $id_tagihan_siswa
															, 'id_pegawai_utang' => $id_pegawai_utang
															, 'nilai_bayar' => $data_value['nilai_bayar']
														];
					}
				}
				
				$num_detail = 0;
				if ($pendapatan) 
				{
					
					foreach ($pendapatan as $kelompok => &$val) 
					{
						$val['no_invoice'] = null;
						$val['no_squence'] = null;
						if ($val['using_invoice'] == 'Y') {
							$invoice = $this->generateInvoice();
							$val['no_invoice'] = $invoice['no_invoice'];
							$val['no_squence'] = $invoice['no_squence'];
						}
						$total_bayar = 0;
						foreach ($pendapatan_detail[$kelompok] as $item) {
							$total_bayar += $item['nilai_bayar'];
						}
						$val['total_bayar'] = $total_bayar;
						$val['total_pembayaran'] = $total_bayar;
						
						$this->db->table('pendapatan')->insert($val);
						$id_pendapatan = $this->db->insertID();
						
						foreach ($pendapatan_detail[$kelompok] as &$item) {
							$item['id_pendapatan'] = $id_pendapatan;
							$num_detail++;
						}
						
						$this->db->table('pendapatan_detail')->insertBatch($pendapatan_detail[$kelompok]);
					}					
				}
				break;
			}
		}
		
		$reader->close();
		delete_file($path . $filename);
		
		if (!$num_detail) {
			return ['status' => 'error', 'message' => 'Data file excel kosong'];
		}
		
		$this->db->transComplete();
		if ($this->db->transStatus()) {
			$message = ['status' => 'ok', 'message' => 'Data berhasil dimasukkan ke database tabel pendapatan sebanyak ' . count($pendapatan) . ' baris dan tabel pendapatan_detail sebanyak ' . $num_detail . ' baris'];
		} else {
			$message = ['status' => 'error', 'message' => 'Data gagal dimasukkan ke database'];
		}
		
		return $message;
	}
	
	public function deleteAllData() {
		
		$list_table = [
						'pendapatan'
						, 'pendapatan_detail'
					];
					
		try {
			$this->db->transException(true)->transStart();
			
			foreach ($list_table as $table) 
			{
				$this->db->table($table)->delete(['id_siswa IS NOT NULL' => null]);
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
		
	public function countAllData() {
		$sql = 'SELECT COUNT(*) AS jml 
				FROM pendapatan';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData() {

		$columns = $this->request->getPost('columns');
		
		$where = ' WHERE 1=1';
		$having = '';
		if (!empty($_GET['tampilkan_pendapatan'])) {
			$having .= ' HAVING jenis_pendapatan="' . $_GET['tampilkan_pendapatan'] . '"';
		}
		
		if (!empty($_GET['daterange'])) {
			$exp = explode(' s.d. ', $_GET['daterange']);
			list($d, $m, $y) = explode('-', $exp[0]);
			$start_date = $y . '-' . $m . '-' . $d;
			
			list($d, $m, $y) = explode('-', $exp[1]);
			$end_date = $y . '-' . $m . '-' . $d;
			$where .= ' AND tgl_bayar >= "' . $start_date . '" AND tgl_bayar <= "' . $end_date . '" ';
		}
		
		// Search
		$search_all = @$this->request->getPost('search')['value'];
		if ($search_all) {

			foreach ($columns as $val) {
				
				if (strpos($val['data'], 'ignore_search') !== false) 
					continue;
				
				if (strpos($val['data'], 'ignore') !== false)
					continue;
				
				if ($val['data'] == 'tampilkan_pendapatan') {
					// $having .= ' HAVING jenis_pendapatan LIKE "' . $_GET['tampilkan_pendapatan'] . '"';
					continue;
				}
				
				if ($val['data'] == 'jenis_pendapatan') {
					$where_col[] = 'nama_pendapatan_jenis LIKE "%' . $search_all . '%"';
					continue;
				}
				
				if ($val['data'] == 'nama') {
					$where_col[] = 'siswa.nama  LIKE "%' . $search_all . '%"';
					$where_col[] = 'pegawai.nama  LIKE "%' . $search_all . '%"';
					$where_col[] = 'pembayar.nama_pembayar  LIKE "%' . $search_all . '%"';
					continue;
				}
					
				
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
					SELECT id_pendapatan
					, IF(pendapatan.id_siswa != "", "Siswa", IF(id_pegawai_utang != "", "Utang Pegawai", "Lainnya")) AS jenis_pendapatan
					FROM pendapatan
					LEFT JOIN siswa USING(id_siswa)
					LEFT JOIN siswa_kelas USING(id_siswa)
					LEFT JOIN pendapatan_detail USING(id_pendapatan)
					LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
					LEFT JOIN pegawai_utang USING(id_pegawai_utang)
					LEFT JOIN pegawai USING(id_pegawai)
					LEFT JOIN pembayar USING(id_pembayar)
				
				' . $where . ' GROUP BY id_pendapatan ' . $having . ') AS tabel';
		$result = $this->db->query($sql)->getRowArray();
		$total_filtered = $result ? $result['jml_data'] : 0;
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT *, SUM(total_bayar) AS nilai_bayar
				, GROUP_CONCAT(nama_pendapatan_jenis) AS nama_pendapatan_jenis
				, GROUP_CONCAT(IF(periode_bulan, periode_bulan, "-")) AS periode_bulan
				, GROUP_CONCAT(IF(periode_tahun, periode_tahun, "-")) AS periode_tahun
				, GROUP_CONCAT(IF(tahun_ajaran, tahun_ajaran, "-")) AS tahun_ajaran
				, CONCAT(IFNULL(siswa.nama, ""), IFNULL(nama_pembayar, ""), IFNULL(pegawai.nama, "")) AS nama
				, GROUP_CONCAT(nilai_utang) AS nilai_utang
				, GROUP_CONCAT(tgl_utang) AS tgl_utang
				, GROUP_CONCAT(nilai_bayar) AS detail_nilai_bayar
				, IF(pendapatan.id_siswa != "", "Siswa", IF(id_pegawai_utang != "", "Utang Pegawai", "Lainnya")) AS jenis_pendapatan
				FROM pendapatan
				
				LEFT JOIN siswa_kelas USING(id_siswa)
				LEFT JOIN pembayar USING(id_pembayar)
				LEFT JOIN pendapatan_detail USING(id_pendapatan)
				LEFT JOIN pegawai_utang USING(id_pegawai_utang)
				LEFT JOIN pegawai USING(id_pegawai)
				LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
				LEFT JOIN siswa_tagihan USING(id_siswa_tagihan)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				LEFT JOIN siswa ON pendapatan.id_siswa = siswa.id_siswa
				' . $where . ' GROUP BY id_pendapatan ' . $having . $order . ' LIMIT ' . $start . ', ' . $length;
	// echo $sql; die;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>