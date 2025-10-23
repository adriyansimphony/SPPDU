<?php
namespace App\Models;

class DashboardSiswaModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getTotalSiswaBaru( $tahun_ajaran = null ) {
		$sql = 'SELECT COUNT(*) AS jml 
				FROM siswa_riwayat_status 
				LEFT JOIN status_siswa USING(id_status_siswa)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				WHERE jenis_status = "masuk_baru"
					AND tahun_ajaran = ?';
		
		if (!$tahun_ajaran) {
			$tahun_ajaran = date('Y') . '/' . (date('Y') + 1);
		}
		$result =  $this->db->query($sql, $tahun_ajaran)->getRowArray();
		return $result['jml'];
	}
	
	public function getTotalSiswaBaruByGender($tahun_ajaran = null) {
		$sql = 'SELECT 
					COUNT(IF(jenis_kelamin = "P", id_siswa, null)) AS jml_perempuan,
					COUNT(IF(jenis_kelamin = "L", id_siswa, null)) AS jml_laki
				FROM siswa_riwayat_status 
				LEFT JOIN status_siswa USING(id_status_siswa)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				LEFT JOIN siswa USING(id_siswa)
				WHERE jenis_status = "masuk_baru"
					AND tahun_ajaran = ?';
		
		if (!$tahun_ajaran) {
			$tahun_ajaran = date('Y') . '/' . (date('Y') + 1);
		}
		$result =  $this->db->query($sql, $tahun_ajaran)->getRowArray();
		return $result;
	}
	
	public function getTotalSiswa() {
		$sql = 'SELECT COUNT(*) AS jml FROM siswa';
		$result =  $this->db->query($sql)->getRowArray();
		return $result['jml'];
	}
	
	public function getTotalPegawai() {
		$sql = 'SELECT COUNT(*) AS jml FROM pegawai';
		$result =  $this->db->query($sql)->getRowArray();
		return $result['jml'];
	}
	
	public function getTotalSiswaByGender() {
		$sql = 'SELECT COUNT(IF(jenis_kelamin = "L", id_siswa, null)) AS jml_laki,
					COUNT(IF(jenis_kelamin = "P", id_siswa, null)) AS jml_perempuan
				FROM siswa';
		$result =  $this->db->query($sql)->getRowArray();
		return $result;
	}
	
	public function getTotalPegawaiByGender() {
		$sql = 'SELECT 
					COUNT(IF(jenis_kelamin = "P", id_pegawai, null)) AS jml_perempuan,
					COUNT(IF(jenis_kelamin = "L", id_pegawai, null)) AS jml_laki
				FROM pegawai';
		$result =  $this->db->query($sql)->getRowArray();
		return $result;
	}
	
	public function getKelas() {
		$sql ='SELECT * FROM siswa_kelas GROUP BY id_kelas';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function countAllData() {
		$sql = 'SELECT COUNT(*) AS jml FROM siswa';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData() {

		$columns = $this->request->getPost('columns');
		
		$where = ' WHERE 1=1 ';
		if (!empty($_GET['id_kelas'])) {
			$where .= ' AND id_kelas = ' . $_GET['id_kelas'];  
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
		$sql = 'SELECT COUNT(*) AS jml_data FROM siswa 
				LEFT JOIN siswa_kelas USING (id_siswa)
				LEFT JOIN orangtua USING(id_orangtua) 
				LEFT JOIN status_orangtua USING(id_status_orangtua)
				' . $where;
		$total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT * FROM siswa
				LEFT JOIN siswa_kelas USING (id_siswa)
				LEFT JOIN orangtua USING(id_orangtua)
				LEFT JOIN status_orangtua USING(id_status_orangtua)
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
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
		$where = ' WHERE 1=1 ';
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
				(SELECT pegawai.*, GROUP_CONCAT(judul_role) AS judul_role FROM pegawai 
				LEFT JOIN pegawai_role USING(id_pegawai) 
				LEFT JOIN role ON pegawai_role.id_role = role.id_role
				LEFT JOIN pegawai_jabatan USING(id_pegawai)
				LEFT JOIN jabatan USING(id_jabatan)
				' . $where . '
				GROUP BY id_pegawai) AS tabel';
				
		$query = $this->db->query($sql)->getRowArray();
		$total_filtered = $query['jml'];
		
		$sql = 'SELECT pegawai.*, GROUP_CONCAT(nama_jabatan SEPARATOR " + ") AS nama_jabatan, GROUP_CONCAT(judul_role) AS judul_role FROM pegawai 
				LEFT JOIN pegawai_role USING(id_pegawai) 
				LEFT JOIN role ON pegawai_role.id_role = role.id_role
				LEFT JOIN pegawai_jabatan USING(id_pegawai)
				LEFT JOIN jabatan USING(id_jabatan)
				' . $where . '
				GROUP BY id_pegawai
				' . $order;
		
		
		$data = $this->db->query($sql)->getResultArray();
		return ['data' => $data, 'total_filtered' => $total_filtered];
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function getListTahun() {
		$sql= 'SELECT YEAR(tgl_transaksi) AS tahun
				FROM toko_penjualan
				GROUP BY tahun';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getTotalItemTerjual($tahun) 
	{
		$sql = 'SELECT jml, jml_prev, ROUND((jml - jml_prev)/ jml_prev * 100, 2) AS growth
				FROM (
					SELECT COUNT(IF(tgl_transaksi LIKE "' . $tahun . '%", id_barang, NULL)) AS jml,
							COUNT(IF(tgl_transaksi LIKE "' . ($tahun - 1) . '%", id_barang, NULL)) AS jml_prev	
					FROM toko_penjualan_detail
					LEFT JOIN toko_penjualan USING(id_penjualan)
					WHERE tgl_transaksi LIKE "' . $tahun . '%" OR tgl_transaksi LIKE "' . ($tahun - 1) . '%"
				) AS tabel';
		return $this->db->query($sql)->getRowArray();
	}
	
	public function getTotalJumlahTransaksi($tahun) 
	{
		$sql = 'SELECT jml, jml_prev, ROUND((jml - jml_prev)/ jml_prev * 100, 2) AS growth
				FROM (
					SELECT COUNT(IF(tgl_transaksi LIKE "' . $tahun . '%", id_penjualan, NULL)) AS jml,
							COUNT(IF(tgl_transaksi LIKE "' . ($tahun - 1) . '%", id_penjualan, NULL)) AS jml_prev
					FROM toko_penjualan
					WHERE tgl_transaksi LIKE "' . $tahun . '%" OR tgl_transaksi LIKE "' . ($tahun - 1) . '%"
				) AS tabel';
		return $this->db->query($sql)->getRowArray();
	}
	
	public function getTotalPelangganAktif($tahun) 
	{
		$sql = 'SELECT jml, jml_prev, ROUND( (jml-jml_prev) / jml_prev * 100 ) AS  growth, total FROM (
					SELECT COUNT(jml) AS jml, COUNT(jml_prev) AS jml_prev, (SELECT COUNT(*) FROM toko_pelanggan) AS total
					FROM (
						SELECT MAX(IF(tgl_transaksi LIKE "' . $tahun . '%", 1, NULL)) AS jml,
								MAX(IF(tgl_transaksi LIKE "' . ( $tahun - 1 ) . '%", 1, NULL)) AS jml_prev
						 FROM toko_penjualan
						WHERE tgl_transaksi LIKE "' . $tahun . '%" OR tgl_transaksi LIKE "' . ($tahun - 1) . '%"
						GROUP BY id_pelanggan
					) AS tabel
				) tabel_utama';
				
		return $this->db->query($sql)->getRowArray();
	}
	
	public function getTotalNilaiPenjualan($tahun) {
		$sql = 'SELECT jml, jml_prev, ROUND((jml - jml_prev)/ jml_prev * 100, 2) AS growth
				FROM (
					SELECT SUM(IF(tgl_transaksi LIKE "' . $tahun . '%", total_harga, NULL)) AS jml,
							SUM(IF(tgl_transaksi LIKE "' . ($tahun - 1) . '%", total_harga, NULL)) AS jml_prev
					FROM toko_penjualan
					WHERE tgl_transaksi LIKE "' . $tahun . '%" OR tgl_transaksi LIKE "' . ($tahun - 1) . '%"
				) AS tabel';
		return $this->db->query($sql)->getRowArray();
	}
	
	public function getPembelianPelangganTerbesar ($tahun) {
		$sql = 'SELECT id_pelanggan, foto, nama_pelanggan, SUM(total_harga) AS total_harga FROM toko_penjualan
				LEFT JOIN toko_pelanggan USING(id_pelanggan)
				WHERE YEAR(tgl_transaksi) = ' . $tahun . '
				GROUP BY id_pelanggan
				ORDER BY total_harga DESC
				LIMIT 5';
		return $this->db->query($sql)->getResultArray();
	}
	
	public function getSeriesPenjualan($list_tahun) {
		
		$result = [];
		foreach ($list_tahun as $tahun) {
			 $sql = 'SELECT MONTH(tgl_transaksi) AS bulan, COUNT(id_penjualan) as JML, SUM(total_harga) total
					FROM toko_penjualan
					WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31"
					GROUP BY MONTH(tgl_transaksi)';
			
			$result[$tahun] = $this->db->query($sql)->getResultArray();
		}
		return $result;
	}
	
	public function getSeriesTotalPenjualan($list_tahun) {
		
		$result = [];
		foreach ($list_tahun as $tahun) {
			 $sql = 'SELECT SUM(total_harga) AS total
					FROM toko_penjualan
					WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31"';
			
			$result[$tahun] = $this->db->query($sql)->getResultArray();
		}
		return $result;
	}
	
	public function getItemTerjual($tahun) {
		$sql = 'SELECT id_barang, nama_barang, COUNT(id_barang) AS jml
				FROM toko_penjualan_detail
				LEFT JOIN toko_penjualan USING(id_penjualan)
				LEFT JOIN toko_barang USING(id_barang)
				WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31"
				GROUP BY id_barang
				ORDER BY jml DESC LIMIT 7';
				
        $result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getKategoriTerjual($tahun) {
		$sql = 'SELECT id_kategori, nama_kategori, COUNT(id_barang) AS jml, SUM(harga) AS nilai
				FROM toko_penjualan_detail
				LEFT JOIN toko_penjualan USING(id_penjualan)
				LEFT JOIN toko_barang USING(id_barang)
				LEFT JOIN toko_barang_kategori USING(id_kategori)
				WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31"
				GROUP BY id_kategori
				ORDER BY nilai DESC LIMIT 7';
				
        $result = $this->db->query($sql)->getResultArray();
		return $result;
	}
		
	public function getItemTerbaru() {
		$sql = 'SELECT * FROM toko_barang ORDER BY tgl_input DESC LIMIT 5';
		return $this->db->query($sql)->getResultArray();
	}
	
	public function penjualanTerbaru($tahun) {
		$sql = 'SELECT nama_pelanggan, SUM(jml_barang) AS jml_barang, MAX(total_harga) AS total_harga, MAX(tgl_transaksi) AS tgl_transaksi FROM toko_penjualan 
				LEFT JOIN toko_penjualan_detail USING(id_penjualan)
				LEFT JOIN toko_pelanggan USING(id_pelanggan)
				WHERE tgl_transaksi LIKE "' . $tahun . '%"
				GROUP BY id_penjualan
				ORDER BY tgl_transaksi DESC LIMIT 50';
		
		return $this->db->query($sql)->getResultArray();
		
	}
	
	public function countAllDataPejualanTerbesar($tahun) {
		$sql = 'SELECT COUNT(*) as jml
				FROM (SELECT id_barang FROM toko_penjualan_detail
					LEFT JOIN toko_penjualan USING(id_penjualan)
					WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31"
					GROUP BY id_barang) AS tabel';
				
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListDataPenjualanTerbesar($tahun) {

		$columns = $this->request->getPost('columns');

		// Search
		$where = ' WHERE 1=1 ';
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
		$order_data = $this->request->getPost('order');
		$order = '';
		if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by;
		}

		// Query Total Filtered
		$sql = '
				SELECT tabel_utama.*, COUNT(*) AS jml_data 
				FROM (
					SELECT tabel.*, ROUND(total_harga / total_penjualan * 100, 0) AS kontribusi 
					FROM (
						SELECT id_barang, nama_barang, harga_satuan, COUNT(id_barang) AS jml_terjual, SUM(harga) AS total_harga,
							(SELECT SUM(harga) FROM toko_penjualan_detail LEFT JOIN toko_penjualan USING(id_penjualan) WHERE tgl_transaksi >= "'. $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31") AS total_penjualan
						FROM toko_penjualan_detail
						LEFT JOIN toko_penjualan USING(id_penjualan)
						LEFT JOIN toko_barang USING(id_barang)
						 
						GROUP BY id_barang
					) AS tabel
				) AS tabel_utama
				' . $where;
				
		// echo $sql; die;
		$total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = '
				SELECT * FROM (
					SELECT tabel.*, ROUND(total_harga / total_penjualan * 100, 0) AS kontribusi 
					FROM (
						SELECT id_barang, nama_barang, harga_satuan, COUNT(id_barang) AS jml_terjual, SUM(harga) AS total_harga,
							(SELECT SUM(harga) FROM toko_penjualan_detail LEFT JOIN toko_penjualan USING(id_penjualan) WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31") AS total_penjualan
						FROM toko_penjualan_detail
						LEFT JOIN toko_penjualan USING(id_penjualan)
						LEFT JOIN toko_barang USING(id_barang)
						WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31"
						GROUP BY id_barang
					) AS tabel
				) AS tabel_utama
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;

		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}