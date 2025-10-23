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

class UbahStatusSiswaModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
		
	public function getAllKelas() {
		$sql ='SELECT * FROM kelas';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getAllTahunAjaran() {
		$sql ='SELECT * FROM tahun_ajaran';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getAllStatusSiswa() {
		$sql ='SELECT * FROM status_siswa';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function saveData() 
	{
		$this->db->transStart();
		
		$id_kelas = $_POST['id_status_siswa'] <= 4 ? $_POST['id_kelas_tujuan'] : null;	
		$exp = explode('-', $_POST['tgl_status']);
		$tgl_status = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		foreach ($_POST['id_siswa'] as $val) {
			$data_db[] = ['id_siswa' => $val
							, 'tgl_status' => $tgl_status
							, 'id_status_siswa' => $_POST['id_status_siswa']
							, 'id_kelas' => $id_kelas
							, 'id_tahun_ajaran' => $_POST['id_tahun_ajaran']
							, 'keterangan' => $_POST['keterangan']
						];
		}
		$this->db->table('siswa_riwayat_status')->insertBatch($data_db);
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	public function getSiswaByIdKelas($id) {
		$sql = 'SELECT * FROM siswa 
				LEFT JOIN siswa_kelas USING(id_siswa) 
				LEFT JOIN kelas USING(id_kelas) WHERE id_kelas = ?';
		$result = $this->db->query($sql, $id)->getResultArray();
		return $result;
	}
}
?>