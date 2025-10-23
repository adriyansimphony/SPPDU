<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Models;

class PendapatanSiswaModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getAllMetodePembayaran() {
		$sql = 'SELECT * FROM jenis_bayar';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getAllPembayaranJenis() {
		$sql = 'SELECT * FROM pendapatan_jenis';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getAllGroupKelas() {
		$sql ='SELECT * FROM kelas GROUP BY group_kelas';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getSiswaByGroupKelas($group_kelas) {
		$sql ='SELECT * FROM siswa LEFT JOIN siswa_kelas USING(id_siswa) WHERE group_kelas = ?';
		$result = $this->db->query($sql, $group_kelas)->getResultArray();
		return $result;
	}
	
	/* public function getJmlStatusSiswa() {
		$sql ='SELECT COUNT(*) AS jml FROM siswa_riwayat_status';
		$result = $this->db->query($sql)->getRowArray();
		return $result['jml'];
	} */
	
	public function getIdentitas() {
		$sql = 'SELECT * FROM identitas_sekolah 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan)
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)';
		$result = $this->db->query($sql)->getRowArray();
		return $result;
	}
	
	public function getSiswaBayarById() 
{
	$sql = 'SELECT * 
			FROM pendapatan
			LEFT JOIN siswa USING(id_siswa)
			LEFT JOIN siswa_kelas USING(id_siswa)
			LEFT JOIN jenis_bayar USING(id_jenis_bayar)
			WHERE id_pendapatan = ?';
	$result = $this->db->query($sql, $_GET['id'])->getRowArray();
	$result['detail'] = '';
	$result['riwayat_bayar'] = [];
	if ($result) {
		// 🔹 Tambahan kolom potongan
		$sql = 'SELECT pendapatan_detail.*,
						pendapatan_utama.tgl_bayar,
						pendapatan_jenis.nama_pendapatan_jenis,
						siswa_tagihan.nilai_tagihan,
						siswa_tagihan.potongan,
						tahun_ajaran.tahun_ajaran,
						siswa_tagihan.periode_bulan,
						siswa_tagihan.periode_tahun,
						(SELECT SUM(nilai_bayar) AS total_pembayaran 
							FROM pendapatan_detail
							LEFT JOIN pendapatan USING(id_pendapatan)
							WHERE tgl_bayar <= pendapatan_utama.tgl_bayar 
							AND id_siswa_tagihan = siswa_tagihan.id_siswa_tagihan
							GROUP BY id_siswa_tagihan
						) AS total_pembayaran
				FROM pendapatan_detail
				LEFT JOIN pendapatan AS pendapatan_utama USING(id_pendapatan)
				LEFT JOIN siswa_tagihan USING(id_siswa_tagihan)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				LEFT JOIN pendapatan_jenis ON pendapatan_detail.id_pendapatan_jenis = pendapatan_jenis.id_pendapatan_jenis
				WHERE id_pendapatan = ?';
		$result['detail'] = $this->db->query($sql, $result['id_pendapatan'])->getResultArray();

		// Riwayat bayar (biarkan default)
		$sql = 'SELECT *
				FROM pendapatan_detail
				LEFT JOIN pendapatan AS pendapatan_utama USING(id_pendapatan)
				LEFT JOIN siswa_tagihan USING(id_siswa_tagihan)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				LEFT JOIN pendapatan_jenis ON pendapatan_detail.id_pendapatan_jenis = pendapatan_jenis.id_pendapatan_jenis
				WHERE id_siswa_tagihan IN (
					SELECT id_siswa_tagihan 
					FROM pendapatan_detail
					LEFT JOIN pendapatan USING(id_pendapatan)
					WHERE id_pendapatan = ?
				) AND tgl_bayar <= ( SELECT tgl_bayar  FROM pendapatan WHERE id_pendapatan = ? )';
		$riwayat_bayar = $this->db->query($sql, [$result['id_pendapatan'], $result['id_pendapatan']])->getResultArray();
		foreach ($riwayat_bayar as $val) {
			$result['riwayat_bayar'][$val['id_siswa_tagihan']] = [];
		}
		foreach ($riwayat_bayar as $val) {
			$result['riwayat_bayar'][$val['id_siswa_tagihan']][] = $val;
		}
	}
	return $result;
}

	
	/* public function getRiwayatSiswaById($id) {
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
	} */
	
	/* public function getAllTahunAjaran() {
		$sql = 'SELECT * FROM tahun_ajaran';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	 */
	public function deleteData() {
		$this->db->table('pendapatan_detail')
					->delete(['id_pendapatan' => $_POST['id']]);
		$this->db->table('pendapatan')
					->delete(['id_pendapatan' => $_POST['id']]);
		return $this->db->affectedRows();
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
		
		// $this->db->transStart();
		
		$data_db['id_siswa'] = $_POST['id_siswa'];
		$data_db['using_invoice'] = 'Y';
		// Invoice
		$sql = 'LOCK TABLES pendapatan WRITE, setting WRITE, pendapatan_detail WRITE';
		$this->db->query($sql);
		
		if (empty($_POST['id'])) 
		{
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
			$data_db['no_invoice'] = $no_invoice;
			$data_db['no_squence'] = $no_squence;
			$data_db['tgl_invoice'] = date('Y-m-d');
						
		} else {
			$exp = explode('-', $_POST['tgl_invoice']);
			$data_db['tgl_invoice'] = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		}
		
		//-- Invoice
		$total = 0;
		foreach ($_POST['nilai_bayar'] as $index => $val) {
			$total += str_replace('.', '', $val);
		}
		
		$total_pembayaran = str_replace('.', '', $_POST['total_pembayaran']);
		
		$data_db['total_bayar'] = $total;
		$data_db['total_pembayaran'] = $total_pembayaran;
		$data_db['kembali'] = $total_pembayaran > $total ? $total_pembayaran - $total : 0;
		$data_db['id_jenis_bayar'] = $_POST['id_jenis_bayar'];
		
		$exp = explode('-', $_POST['tgl_bayar']);
		$data_db['tgl_bayar'] = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		if (!empty($_POST['id']))  
		{
			$data_db['id_pegawai_update'] = $_SESSION['pegawai']['id_pegawai'];
			$data_db['tgl_update'] = date('Y-m-d H:i:s');
			$this->db->table('pendapatan')->update($data_db, ['id_pendapatan' => $_POST['id']]);
			$this->db->table('pendapatan_detail')->delete(['id_pendapatan' => $_POST['id']]);
			$id_pendapatan = $_POST['id'];
		} else {
			$data_db['id_pegawai_input'] = $_SESSION['pegawai']['id_pegawai'];
			$data_db['tgl_input'] = date('Y-m-d H:i:s');
			$this->db->table('pendapatan')->insert($data_db);
			$id_pendapatan = $this->db->insertID();
		}
		
		// Detail
		foreach ($_POST['detail_jenis_pembayaran'] as $index => $val) 
		{
			$val = json_decode($val, true);
			$data_db = [];
			$data_db['id_pendapatan'] = $id_pendapatan;
			$data_db['id_pendapatan_jenis'] = $val['id_pendapatan_jenis'];
			$data_db['id_siswa_tagihan'] = $val['id_siswa_tagihan'] ?: null;
			$data_db['nilai_bayar'] = str_replace('.', '', $_POST['nilai_bayar'][$index]);
			
			$this->db->table('pendapatan_detail')->insert($data_db);
		}
		
		$sql = 'UNLOCK TABLES';
		$this->db->query($sql);
		
		
		/* 
		
		
		
		$data_db = [];
		foreach ($_POST['id_siswa'] as $val) 
		{
			for ($i = $_POST['bulan_awal']; $i <= $_POST['bulan_akhir']; $i++) {
				$data_db[] = ['id_siswa' => $val
							, 'bulan' => $i
							, 'tahun' => $_POST['tahun']
							, 'nilai_spp' => str_replace('.', '', $_POST['nilai_spp'])
							, 'keterangan' => $_POST['keterangan']
						];
				$this->db->table('siswa_spp_nilai')->delete(['id_siswa' => $val, 'bulan' => $i, 'tahun' => $_POST['tahun']]);
			}
		}
		
		$this->db->table('siswa_spp_nilai')->insertBatch($data_db); */
		
		$this->db->transComplete();
		if ($this->db->transStatus()) {
			return ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		} else {
			return ['status' => 'error', 'message' => 'Data gagal disimpan'];
		}
	}
	
	/* public function saveDataEdit() {
		
		$result = $this->db->table('siswa_spp_nilai')
						->update(['nilai_spp' => str_replace('.', '', $_POST['nilai_spp'])
									, 'keterangan' => $_POST['keterangan']
								], ['id_siswa' => $_POST['id_siswa']
									, 'bulan' => $_POST['bulan']
									, 'tahun' => $_POST['tahun']
								]										
							);
		
		if ($result) {
			return ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		} else {
			return ['status' => 'error', 'message' => 'Data gagal disimpan'];
		}
	} */
	
	public function countAllDataPembayaranJenis($id_siswa) {
		
		/* if (!empty($_GET['kelas'])) {
			$where .= ' AND group_kelas = ' . $_GET['kelas'];  
		} */
		
		$sql = 'SELECT COUNT(*) as jml FROM (
					SELECT nama_pendapatan_jenis
						, id_pendapatan_jenis
						, id_siswa_tagihan
						, periode_bulan
						, periode_tahun
						, tahun_ajaran
						, id_tahun_ajaran
						, nilai_tagihan
						, total_pembayaran
						, nilai_tagihan - IFNULL(total_pembayaran, 0) AS kurang 
					FROM siswa_tagihan 
					LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
					LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
					LEFT JOIN (
							SELECT SUM(nilai_bayar) AS total_pembayaran, id_siswa_tagihan FROM pendapatan_detail GROUP BY id_siswa_tagihan
					) AS tabel_bayar USING(id_siswa_tagihan) 
					WHERE id_siswa = ' . $id_siswa . '
					HAVING kurang > 0
				) AS tabel';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListDataPembayaranJenis($id_siswa) {

		$columns = $this->request->getPost('columns');
		
		$where = ' WHERE 1=1 AND id_siswa = ' . $id_siswa;
		if (!empty($_GET['kelas'])) {
			$where .= ' AND group_kelas = ' . $_GET['kelas'];  
		}
		// Search
		$search_all = @$this->request->getPost('search')['value'];
		if ($search_all) {
			// Additional Search
			$columns[]['data'] = 'periode_bulan';
			$columns[]['data'] = 'periode_tahun';
			$columns[]['data'] = 'tahun_ajaran';
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
		if ($order_data) {
			if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
				$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
				$order = ' ORDER BY ' . $order_by;
			}
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml_data FROM (
					SELECT nama_pendapatan_jenis
						, periode_bulan
						, periode_tahun
						, tahun_ajaran
						, nilai_tagihan FROM siswa_tagihan 
					LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
					LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
					' . $where . '
				) AS tabel';
				
		$result = $this->db->query($sql)->getRowArray();
		$total_filtered = $result ? $result['jml_data'] : 0;
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT * FROM (
					SELECT nama_pendapatan_jenis
	, id_pendapatan_jenis
	, id_siswa_tagihan
	, periode_bulan
	, periode_tahun
	, tahun_ajaran
	, id_tahun_ajaran
	, nilai_tagihan
	, potongan  -- ✅ tambahkan kolom potongan
	, total_pembayaran
	, nilai_tagihan - IFNULL(total_pembayaran, 0) AS kurang 
FROM siswa_tagihan 
 
					LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
					LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
					LEFT JOIN (
							SELECT SUM(nilai_bayar) AS total_pembayaran, id_siswa_tagihan FROM pendapatan_detail GROUP BY id_siswa_tagihan
					) AS tabel_bayar USING(id_siswa_tagihan) 
					' . $where . '
					HAVING kurang > 0
				) AS tabel
				
					UNION ALL
SELECT nama_pendapatan_jenis
	, id_pendapatan_jenis
	, "" AS id_siswa_tagihan
	, "" AS periode_bulan
	, "" AS periode_tahun
	, "" AS tahun_ajaran
	, "" AS id_tahun_ajaran
	, "" AS nilai_tagihan
	, "" AS potongan       -- ✅ tambahkan potongan kosong di sini
	, "" AS total_pembayaran
	, "" AS nilai_tagihan
FROM pendapatan_jenis 
					LEFT JOIN pendapatan_jenis_sumber USING(id_pendapatan_jenis)
					WHERE id_pendapatan_sumber = 1 AND perlu_tagihan_siswa = "N"

					' . $order . ' LIMIT ' . $start . ', ' . $length;
		// echo $sql; die;
		$data = $this->db->query($sql)->getResultArray();
		/* echo '<pre>';
		print_r($sql);
		die; */
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	
	
	public function countAllData() {
		$where = '';
		if (!empty($_GET['kelas'])) {
			$where = ' WHERE group_kelas = ' . $_GET['kelas'];  
		}
		
		$sql = 'SELECT COUNT(*) AS jml 
				FROM pendapatan 
				LEFT JOIN siswa USING(id_siswa)
				LEFT JOIN siswa_kelas USING(id_siswa) ' . $where;
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData() {

		$columns = $this->request->getPost('columns');
		
		$where = ' WHERE 1=1 AND pendapatan.id_siswa !="" AND pendapatan.id_siswa IS NOT NULL ';
		if (!empty($_GET['kelas'])) {
			$where .= ' AND group_kelas = ' . $_GET['kelas'];  
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
		$sql = 'SELECT COUNT(*) AS jml_data
				FROM (SELECT id_pendapatan FROM pendapatan
				LEFT JOIN siswa USING(id_siswa)
				LEFT JOIN siswa_kelas USING(id_siswa)
				LEFT JOIN pendapatan_detail USING(id_pendapatan)
				LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
				
				' . $where . ' GROUP BY id_pendapatan) AS tabel';
		$result = $this->db->query($sql)->getRowArray();
		$total_filtered = $result ? $result['jml_data'] : 0;
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT *, SUM(total_bayar) AS nilai_bayar, GROUP_CONCAT(nama_pendapatan_jenis) AS nama_pendapatan_jenis
				, GROUP_CONCAT(IF(periode_bulan, periode_bulan, "-")) AS periode_bulan
				, GROUP_CONCAT(IF(periode_tahun, periode_tahun, "-")) AS periode_tahun
				, GROUP_CONCAT(IF(tahun_ajaran, tahun_ajaran, "-")) AS tahun_ajaran
				FROM pendapatan
				LEFT JOIN siswa USING(id_siswa)
				LEFT JOIN siswa_kelas USING(id_siswa)
				LEFT JOIN pendapatan_detail USING(id_pendapatan)
				LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
				LEFT JOIN siswa_tagihan USING(id_siswa_tagihan)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				' . $where . ' GROUP BY id_pendapatan ' . $order . ' LIMIT ' . $start . ', ' . $length;
		// echo $sql; die;
		$data = $this->db->query($sql)->getResultArray();		
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	
	public function countAllDataSiswa() {
		
		$sql = 'SELECT COUNT(*) AS jml 
				FROM siswa';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListDataSiswa() {

		$columns = $this->request->getPost('columns');
		
		$where = ' WHERE 1=1 ';
		if (!empty($_GET['kelas'])) {
			$where .= ' AND group_kelas = ' . $_GET['kelas'];  
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
		$sql = 'SELECT COUNT(*) AS jml_data
				FROM siswa
				LEFT JOIN siswa_kelas USING(id_siswa)
				' . $where;
		$total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT *
				FROM siswa
				LEFT JOIN siswa_kelas USING(id_siswa)
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>