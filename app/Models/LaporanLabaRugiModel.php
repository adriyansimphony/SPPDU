<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Models;

class LaporanLabaRugiModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getIdentitasSekolah() {
		$sql = 'SELECT * FROM identitas_sekolah
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan)
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
				
				';
		$result = $this->db->query($sql)->getRowArray();
		return $result;
	}
	
	public function getPegawai() {
		$sql = 'SELECT * FROM
				pegawai
				LEFT JOIN pegawai_jabatan USING(id_pegawai)
				LEFT JOIN jabatan USING(id_jabatan)';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getKategori() 
	{
		$result = [];
		
		$sql = 'SELECT * FROM pengeluaran_kategori
				ORDER BY urut';
		
		$kategori = $this->db->query($sql)->getResultArray();

		foreach ($kategori as $val) 
		{
			$result[$val['id_pengeluaran_kategori']] = $val;
			$result[$val['id_pengeluaran_kategori']]['depth'] = 0;			
		}		
		return $result;
	}
	
	public function getPendapatan($start_date, $end_date) {
		$sql = 'SELECT nama_pendapatan_jenis, SUM(nilai_bayar) AS jumlah_pendapatan FROM pendapatan
				LEFT JOIN pendapatan_detail USING(id_pendapatan)
				LEFT JOIN pendapatan_jenis USING(id_pendapatan_jenis)
				WHERE tgl_bayar >= "'. $start_date . '" AND tgl_bayar <= "' . $end_date . '"
				GROUP BY id_pendapatan_jenis';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getPengeluaran($start_date, $end_date) {
		$sql = 'SELECT *, SUM(total_pengeluaran) AS total_pengeluaran FROM pengeluaran
				LEFT JOIN pengeluaran_kategori USING(id_pengeluaran_kategori)
				WHERE tgl_pengeluaran >= "'. $start_date . '" AND tgl_pengeluaran <= "' . $end_date . '"
				GROUP BY id_pengeluaran_kategori';
		// echo $sql; die;
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
}
?>