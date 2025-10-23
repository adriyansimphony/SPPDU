<?php
namespace App\Models;

class DashboardKeuanganModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getSaldoKas() {
		$cutoff_date = date('Y-m-d');
		$sql = 'SELECT SUM(kas) AS kas, SUM(pendapatan) AS pendapatan, SUM(pengeluaran) AS pengeluaran, kas + SUM(pendapatan) - SUM(pengeluaran) AS saldo_kas
				FROM(
					SELECT SUM(nilai) AS kas, 0 AS pendapatan, 0 AS pengeluaran 
					FROM penyesuaian_kas
					WHERE tgl_berlaku <= "' . $cutoff_date . '"
					UNION ALL
					SELECT 0 AS kas, SUM(total_bayar) AS pendapatan, 0 AS pengeluaran
					FROM pendapatan
					WHERE tgl_bayar <= "' . $cutoff_date . '"
					UNION ALL
					SELECT 0 AS kas, 0 AS pendapatan, SUM(total_pengeluaran) AS pengeluaran
					FROM pengeluaran
					WHERE tgl_pengeluaran <= "' . $cutoff_date . '"
				) AS tabel';
		$result = $this->db->query($sql)->getRowArray();
		return $result;
	}
	
	public function getTotalPendapatan() {
		$sql = 'SELECT SUM(total_bayar) AS total_pendapatan FROM pendapatan
				WHERE tgl_bayar LIKE "' . date('Y') . '%"';
		$result = $this->db->query($sql)->getRowArray();
		return $result['total_pendapatan'];
	}
	
	public function getTotalPengeluaran() {
		$sql = 'SELECT SUM(total_pengeluaran) AS total_pengeluaran FROM pengeluaran
				WHERE tgl_pengeluaran LIKE "' . date('Y') . '%"';
		$result = $this->db->query($sql)->getRowArray();
		return $result['total_pengeluaran'];
	}
	
	public function getListTahunPendapatan() {
		$sql = 'SELECT DISTINCT YEAR(tgl_bayar) AS tahun_bayar
				FROM pendapatan';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getListTahunPengeluaran() {
		$sql = 'SELECT DISTINCT YEAR(tgl_pengeluaran) AS tahun_pengeluaran
				FROM pengeluaran';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getTotalPendapatanByKategori() {
		$sql = 'SELECT nama_pendapatan_jenis, SUM(nilai_bayar) AS total_pendapatan
				FROM pendapatan_detail
				LEFT JOIN pendapatan USING(id_pendapatan)
				LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
				WHERE tgl_bayar LIKE "'. date('Y') . '%"
				GROUP BY id_pendapatan_jenis
				ORDER BY total_pendapatan DESC';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getTotalPengeluaranByKategori() {
		$sql = 'SELECT nama_kategori, SUM(total_pengeluaran) AS total_pengeluaran
				FROM pengeluaran
				LEFT JOIN pengeluaran_kategori USING(id_pengeluaran_kategori)
				WHERE tgl_pengeluaran LIKE "' . date('Y') . '%"
				GROUP BY id_pengeluaran_kategori
				ORDER BY total_pengeluaran DESC';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
public function getTotalTagihanSPP() {
	$sql = 'SELECT 
				SUM(siswa_tagihan.nilai_tagihan - IFNULL(siswa_tagihan.potongan, 0)) AS total_tagihan_setelah_potongan,
				SUM(tabel.nilai_bayar) AS total_bayar,
				SUM((siswa_tagihan.nilai_tagihan - IFNULL(siswa_tagihan.potongan, 0)) - IFNULL(tabel.nilai_bayar, 0)) AS saldo_spp
			FROM siswa_tagihan
			LEFT JOIN (
				SELECT id_siswa_tagihan, SUM(nilai_bayar) AS nilai_bayar 
				FROM pendapatan_detail 
				GROUP BY id_siswa_tagihan
			) AS tabel USING(id_siswa_tagihan)
			WHERE id_pendapatan_jenis = 1 
				AND periode_bulan < ' . date('n') . ' 
				AND periode_tahun <= ' . date('Y');
	
	$result = $this->db->query($sql)->getRowArray();
	return $result['saldo_spp'];
}

	
	public function getTotalSiswaBelumBayarSPP() {
		$sql = 'SELECT COUNT(*) AS jml_siswa FROM(
					SELECT COUNT(*) AS jml_siswa
					FROM siswa_tagihan
					LEFT JOIN (SELECT id_siswa_tagihan, SUM(nilai_bayar) AS nilai_bayar 
									FROM pendapatan_detail 
									GROUP BY id_siswa_tagihan)
									AS tabel
					USING(id_siswa_tagihan)
					WHERE id_pendapatan_jenis = 1 AND periode_bulan < ' . date('n') . ' AND periode_tahun <= ' . date('Y') . '
					GROUP BY id_siswa
				) AS tabel';
		// echo $sql; die;
		$result = $this->db->query($sql)->getRowArray();
		return $result['jml_siswa'];
	}
	
	public function getTotalUtangPegawai() {
		$sql = 'SELECT SUM(nilai_utang) AS nilai_utang, SUM(nilai_bayar) AS nilai_bayar, SUM(nilai_utang) - SUM(nilai_bayar) AS saldo_utang
				FROM pegawai_utang
				LEFT JOIN (SELECT id_pegawai_utang, SUM(nilai_bayar) AS nilai_bayar 
								FROM pendapatan_detail 
								WHERE id_pendapatan_jenis = 13
								GROUP BY id_pegawai_utang)
								AS tabel
				USING(id_pegawai_utang)';
		
		$result = $this->db->query($sql)->getRowArray();
		return $result['saldo_utang'];
	}
	
	public function getJumlahPegawaiUtang() {
		$sql = 'SELECT COUNT(*) AS jml_pegawai
				FROM (SELECT *
						FROM pegawai_utang
						LEFT JOIN (SELECT id_pegawai_utang, SUM(nilai_bayar) AS nilai_bayar 
										FROM pendapatan_detail 
										WHERE id_pendapatan_jenis = 13
										GROUP BY id_pegawai_utang)
										AS tabel
						USING(id_pegawai_utang)
						GROUP BY id_pegawai) AS tabel';
		
		$result = $this->db->query($sql)->getRowArray();
		return $result['jml_pegawai'];
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
		// echo $sql ; die;
		
		$result = $this->db->query($sql)->getRowArray();
		$total_filtered = $result ? $result['jml_data'] : 0;
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT *, SUM(total_bayar) AS nilai_bayar, GROUP_CONCAT(nama_pendapatan_jenis) AS nama_pendapatan_jenis
				, GROUP_CONCAT(IF(periode_bulan, periode_bulan, "-")) AS periode_bulan
				, GROUP_CONCAT(IF(periode_tahun, periode_tahun, "-")) AS periode_tahun
				, GROUP_CONCAT(IF(tahun_ajaran, tahun_ajaran, "-")) AS tahun_ajaran
				, CONCAT(IFNULL(siswa.nama, ""), IFNULL(nama_pembayar, ""), IFNULL(pegawai.nama, "")) AS nama
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
				' . $where . ' GROUP BY id_pendapatan ' . $order . ' LIMIT ' . $start . ', ' . $length;
		// echo $sql; die;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	public function countAllDataPengeluaran() {
	
		$sql = 'SELECT COUNT(*) AS jml 
				FROM pengeluaran WHERE id_pengeluaran IS NOT NULL AND tgl_pengeluaran LIKE "' . date('Y') . '%"' ;
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListDataPengeluaran() {

		$columns = $this->request->getPost('columns');
		
		$where = ' WHERE id_pengeluaran IS NOT NULL AND tgl_pengeluaran LIKE "' . date('Y') . '%"' ;
		
		// Search
		$search_all = @$this->request->getPost('search')['value'];
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
				FROM (SELECT id_pengeluaran FROM pengeluaran
				LEFT JOIN pengeluaran_detail USING(id_pengeluaran)
				LEFT JOIN jenis_bayar USING(id_jenis_bayar)
				LEFT JOIN pengeluaran_kategori USING(id_pengeluaran_kategori)
				' . $where . ' GROUP BY id_pengeluaran) AS tabel';
		
		$result = $this->db->query($sql)->getRowArray();
		$total_filtered = $result ? $result['jml_data'] : 0;
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT *, GROUP_CONCAT(nama_pengeluaran) AS nama_pengeluaran, GROUP_CONCAT(keterangan) AS keterangan
				FROM pengeluaran
				LEFT JOIN pengeluaran_detail USING(id_pengeluaran)
				LEFT JOIN jenis_bayar USING(id_jenis_bayar)
				LEFT JOIN pengeluaran_kategori USING(id_pengeluaran_kategori)
				' . $where . ' GROUP BY id_pengeluaran ' . $order . ' LIMIT ' . $start . ', ' . $length;
		// echo $sql;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}