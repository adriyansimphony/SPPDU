<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Models;

use CodeIgniter\Database\Exceptions\DatabaseException;

class SiswaTagihanModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getJenisIuranSiswa() {
		$sql = 'SELECT * FROM pendapatan_jenis 
				LEFT JOIN pendapatan_jenis_sumber USING(id_pendapatan_jenis)
				WHERE id_pendapatan_sumber = 1';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getTahunAjaran() {
		$sql = 'SELECT * FROM tahun_ajaran ORDER BY tahun_ajaran DESC';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getPendapatanJenis() {
		$sql = 'SELECT * FROM pendapatan_jenis';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getPendapatanJenisById($id) {
		$sql = 'SELECT * FROM pendapatan_jenis WHERE id_pendapatan_jenis = ?';
		$result = $this->db->query($sql, $id)->getRowArray();
		return $result;
	}
	
	public function getAllGroupKelas() {
		$sql ='SELECT * FROM kelas GROUP BY group_kelas';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getSiswaByGroupKelas($group_kelas) {
		$where = $group_kelas ? ' WHERE group_kelas = ' . $group_kelas : '';
		$sql ='SELECT * FROM siswa LEFT JOIN siswa_kelas USING(id_siswa) ' . $where;
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getJmlTagihan() {
		$sql ='SELECT COUNT(*) AS jml FROM siswa_tagihan';
		$result = $this->db->query($sql)->getRowArray();
		return $result['jml'];
	}
	
	public function getSiswaTagihanById() {
		$sql = 'SELECT * 
				FROM siswa_tagihan 
				LEFT JOIN siswa USING(id_siswa)
				LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				WHERE id_siswa_tagihan = ?';
		$result = $this->db->query($sql, $_GET['id'])->getRowArray();
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
		$result = $this->db->table('siswa_tagihan')
					->delete(['id_siswa_tagihan' => $_POST['id']]);
		return $this->db->affectedRows();
	}
	
	public function deleteAllData() {
		
		$list_table = [
						'siswa_tagihan'
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
		
		// --- bagian header colls (ganti isi $colls dengan ini) ---
	$colls = [
		'no'            => ['type' => '#,##0', 'width' => 5,  'title' => 'No'],
		'nama'          => ['type' => 'string','width' => 30, 'title' => 'Nama'],
		'nisn'          => ['type' => 'string','width' => 12, 'title' => 'NISN'],
		'nis'           => ['type' => 'string','width' => 10, 'title' => 'NIS'],
		'nama_kelas'    => ['type' => 'string','width' => 12, 'title' => 'Kelas'],
		'nilai_tagihan' => ['type' => '#,##0', 'width' => 15, 'title' => 'Tagihan Setelah Potongan'],
		'potongan'      => ['type' => '#,##0', 'width' => 15, 'title' => 'Potongan'],
		'nilai_bayar'   => ['type' => '#,##0', 'width' => 15, 'title' => 'Dibayar'],
		'saldo_tagihan' => ['type' => '#,##0', 'width' => 15, 'title' => 'Sisa Tagihan']
	];

		
		$col_type = $col_width = $col_header = [];
		foreach ($colls as $field => $val) {
			$col_type[$field] = $val['type'];
			$col_header[$field] = $val['title'];
			$col_header_type[$field] = 'string';
			$col_width[] = $val['width'];
		}
		
		// SQL
		$sql = 'SELECT nama, nisn, nis, nama_kelas, nilai_tagihan, potongan, nilai_bayar_tagihan, nilai_tagihan - IFNULL(nilai_bayar_tagihan, 0) AS saldo_tagihan 
        FROM siswa_tagihan
        LEFT JOIN siswa USING(id_siswa)
        LEFT JOIN siswa_kelas USING(id_siswa)
        LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
        LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
        LEFT JOIN (SELECT id_siswa_tagihan, SUM(nilai_bayar) AS nilai_bayar_tagihan
                        FROM pendapatan_detail
                        GROUP BY id_siswa_tagihan
                    ) AS tabel USING(id_siswa_tagihan)';
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
		
		$tmp_file = ROOTPATH . 'public/tmp/tagihan_siswa_' . time() . '.xlsx.tmp';
		$writer->writeToFile($tmp_file);
		return $tmp_file;
	}
	
	public function saveDataAdd() {
	// ambil input, hapus format ribuan
	$nilai_input = isset($_POST['nilai_tagihan']) ? (int) str_replace('.', '', $_POST['nilai_tagihan']) : 0;
	$potongan = isset($_POST['potongan']) ? (int) str_replace('.', '', $_POST['potongan']) : 0;

	// validasi: potongan negatif -> set 0. potongan tidak boleh lebih besar dari nilai_input
	if ($potongan < 0) $potongan = 0;
	if ($potongan > $nilai_input) $potongan = $nilai_input;

	$nilai_final = max(0, $nilai_input - $potongan);

	$jenis = $this->getPendapatanJenisById($_POST['id_pendapatan_jenis']);
	$data_db = [];
	foreach ($_POST['id_siswa'] as $val) 
	{
		if ($jenis['using_periode'] == 'Y') {
			if ($jenis['jenis_periode'] == 'bulan') {
				$start = strtotime($_POST['tahun_awal'] . '-' . $_POST['bulan_awal'] . '-01');
				$end = strtotime($_POST['tahun_akhir'] . '-' . $_POST['bulan_akhir'] . '-01');
				while($start <= $end) 
				{
					$tahun = date('Y', $start);
					$bulan = date('n', $start);
					$data_db[] = [
						'id_siswa' => $val,
						'id_pendapatan_jenis' => $_POST['id_pendapatan_jenis'],
						'periode_bulan' => $bulan,
						'periode_tahun' => $tahun,
						'id_tahun_ajaran' => null,
						'nilai_tagihan' => $nilai_final,
						'potongan' => $potongan,
						'keterangan' => $_POST['keterangan']
					];
					$this->db->table('siswa_tagihan')->delete([
						'id_siswa' => $val,
						'id_pendapatan_jenis' => $_POST['id_pendapatan_jenis'],
						'periode_bulan' => $bulan,
						'periode_tahun' => $tahun
					]);
					$start = strtotime('+1 month', $start);
				}
			} else if ($jenis['jenis_periode'] == 'tahun') {
				$data_db[] = [
					'id_siswa' => $val,
					'id_pendapatan_jenis' => $_POST['id_pendapatan_jenis'],
					'periode_bulan' => null,
					'periode_tahun' => $_POST['periode_tahun'],
					'id_tahun_ajaran' => null,
					'nilai_tagihan' => $nilai_final,
					'potongan' => $potongan,
					'keterangan' => $_POST['keterangan']
				];
				$this->db->table('siswa_tagihan')->delete([
					'id_siswa' => $val,
					'id_pendapatan_jenis' => $_POST['id_pendapatan_jenis'],
					'periode_tahun' => $_POST['periode_tahun']
				]);
			} else if ($jenis['jenis_periode'] == 'tahun_ajaran') {
				$data_db[] = [
					'id_siswa' => $val,
					'id_pendapatan_jenis' => $_POST['id_pendapatan_jenis'],
					'periode_bulan' => null,
					'periode_tahun' => null,
					'id_tahun_ajaran' => $_POST['id_tahun_ajaran'],
					'nilai_tagihan' => $nilai_final,
					'potongan' => $potongan,
					'keterangan' => $_POST['keterangan']
				];
				$this->db->table('siswa_tagihan')->delete([
					'id_siswa' => $val,
					'id_pendapatan_jenis' => $_POST['id_pendapatan_jenis'],
					'id_tahun_ajaran' => $_POST['id_tahun_ajaran']
				]);
			}
		} else {
			$data_db[] = [
				'id_siswa' => $val,
				'id_pendapatan_jenis' => $_POST['id_pendapatan_jenis'],
				'periode_bulan' => null,
				'periode_tahun' => null,
				'id_tahun_ajaran' => null,
				'nilai_tagihan' => $nilai_final,
				'potongan' => $potongan,
				'keterangan' => $_POST['keterangan']
			];
			$this->db->table('siswa_tagihan')->delete([
				'id_siswa' => $val, 
				'id_pendapatan_jenis' => $_POST['id_pendapatan_jenis']
			]);
		}
	}
	
	$this->db->table('siswa_tagihan')->insertBatch($data_db);
	
	if ($this->db->affectedRows() >= 0) {
		// insertBatch tidak mengembalikan false kalau sukses; kita asumsikan ok jika tidak exception
		return ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
	} else {
		return ['status' => 'error', 'message' => 'Data gagal disimpan'];
	}
}

	
	public function saveDataEdit() {
	// ambil input bersih
	$nilai_input = isset($_POST['nilai_tagihan']) ? (int) str_replace('.', '', $_POST['nilai_tagihan']) : 0;
	$potongan = isset($_POST['potongan']) ? (int) str_replace('.', '', $_POST['potongan']) : 0;

	// validasi sederhana
	if ($potongan < 0) $potongan = 0;
	if ($potongan > $nilai_input) $potongan = $nilai_input;

	$nilai_final = max(0, $nilai_input - $potongan);

	$result = $this->db->table('siswa_tagihan')
					->update([
						'nilai_tagihan' => $nilai_final,
						'potongan' => $potongan,
						'keterangan' => $_POST['keterangan']
					], ['id_siswa_tagihan' => $_POST['id']]);
	
	if ($result) {
		return ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
	} else {
		return ['status' => 'error', 'message' => 'Data gagal disimpan'];
	}
}

	
	public function countAllData($where) {
		
		if (!empty($_GET['kelas'])) {
			$where .= ' AND group_kelas = ' . $_GET['kelas'];  
		}
		
		$sql = 'SELECT COUNT(*) AS jml 
				FROM siswa_tagihan
				LEFT JOIN siswa_kelas USING(id_siswa)' . $where;
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData($where) {

		$columns = $this->request->getPost('columns');
		
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
				
				if (strpos($val['data'], 'kurang_bayar') !== false)
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
				FROM siswa_tagihan
				LEFT JOIN siswa USING(id_siswa)
				LEFT JOIN siswa_kelas USING(id_siswa)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
				LEFT JOIN (SELECT id_siswa_tagihan, SUM(nilai_bayar) AS nilai_bayar_tagihan
								FROM pendapatan_detail
								GROUP BY id_siswa_tagihan
							) AS tabel USING(id_siswa_tagihan)
				' . $where;
		$total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT * FROM (SELECT *, nilai_tagihan - IFNULL(nilai_bayar_tagihan,0) AS kurang_bayar
				FROM siswa_tagihan
				LEFT JOIN siswa USING(id_siswa)
				LEFT JOIN siswa_kelas USING(id_siswa)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
				LEFT JOIN (SELECT id_siswa_tagihan, SUM(nilai_bayar) AS nilai_bayar_tagihan
								FROM pendapatan_detail
								GROUP BY id_siswa_tagihan
							) AS tabel USING(id_siswa_tagihan)
				' . $where . ') AS tabel ' . $order . ' LIMIT ' . $start . ', ' . $length;
		// echo $sql; die;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>