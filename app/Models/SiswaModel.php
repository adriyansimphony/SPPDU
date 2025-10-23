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

class SiswaModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getKelas() {
		$sql ='SELECT * FROM siswa_kelas GROUP BY id_kelas';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getAllAgama() {
		$sql = 'SELECT * FROM agama';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
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
	
	public function getAllStatusOrangtua() {
		$sql ='SELECT * FROM status_orangtua';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getJmlSiswa() {
		$sql ='SELECT COUNT(*) AS jml FROM siswa';
		$result = $this->db->query($sql)->getRowArray();
		return $result['jml'];
	}
	
	public function getListStatusSiswa() {
		$sql ='SELECT * FROM status_siswa WHERE id_status_siswa < 5';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getAllTahunAjaran() {
		$sql ='SELECT * FROM tahun_ajaran ORDER BY tahun_ajaran DESC';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getRiwayatSiswaById($id) {
		$sql = 'SELECT * FROM siswa_riwayat_status WHERE id_siswa_riwayat_status = ?';
		$result = $this->db->query($sql, $id)->getRowArray();
		return $result;
	}
	
	public function deleteStatus() {
		$result = $this->db->table('siswa_riwayat_status')->delete(['id_siswa_riwayat_status' => $_POST['id']]);
		return $result;
	}
	
	public function deleteData() {
		$sql = 'SELECT * FROM siswa WHERE id_siswa = ?';
		$siswa = $this->db->query($sql, $_POST['id'])->getRowArray();
		$path_foto = ROOTPATH . '/public/images/siswa/' . $siswa['foto'];
		if ($siswa['foto']) {
			if (file_exists($path_foto)) {
				$unlink = delete_file( $path_foto );
				if (!$unlink) {
					return false;
				}
			}
		}
		
		$this->db->transStart();
		// Jml orangtua
		$sql = 'SELECT COUNT(*) AS jml FROM orangtua WHERE id_orangtua = ?';
		$result = $this->db->query($sql, $siswa['id_orangtua'])->getRowArray();
		if ($result['jml'] == 1) {
			$this->db->table('orangtua')->delete(['id_orangtua' => $siswa['id_orangtua']]);
		}
		
		// Riwayat
		$this->db->table('siswa_riwayat_status')->delete(['id_siswa' => $_POST['id']]);
		$this->db->table('siswa')->delete(['id_siswa' => $_POST['id']]);
		
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	public function deleteAllSiswa() {
		
		$list_table = [
						'siswa',
						'orangtua',
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
	
	public function getSiswaById($id) {
		$sql = 'SELECT * FROM siswa 
				LEFT JOIN orangtua USING(id_orangtua)
				WHERE id_siswa = ?';
		$result = $this->db->query($sql, trim($id))->getRowArray();
		return $result;
	}
	
	public function writeExcel() 
	{
		require_once(ROOTPATH . "/app/ThirdParty/PHPXlsxWriter/xlsxwriter.class.php");
		
		$colls = [
					'no' 			=> ['type' => '#,##0', 'width' => 5, 'title' => 'No'],
					'nama' 	=> ['type' => 'string', 'width' => 30, 'title' => 'Nama'],
					'jenis_kelamin' 	=> ['type' => 'string', 'width' => 10, 'title' => 'Jenis Kelamin'],
					'agama' 	=> ['type' => 'string', 'width' => 10, 'title' => 'Agama'],
					'nisn' 	=> ['type' => 'string', 'width' => 10, 'title' => 'NISN'],
					'nis' 	=> ['type' => 'string', 'width' => 10, 'title' => 'NIS'],
					'nik' 	=> ['type' => 'string', 'width' => 18, 'title' => 'NIK'],
					'tempat_lahir' 	=> ['type' => 'string', 'width' => 15, 'title' => 'Tempat Lahir'],
					'tgl_lahir' 	=> ['type' => 'string', 'width' => 20, 'title' => 'Tanggal Lahir'],
					'alamat' 	=> ['type' => 'string', 'width' => 30, 'title' => 'Alamat'],
					'nama_kelurahan' 	=> ['type' => 'string', 'width' => 20, 'title' => 'Kelurahan'],
					'nama_kecamatan' 	=> ['type' => 'string', 'width' => 20, 'title' => 'Kecamatan'],
					'nama_kabupaten' 	=> ['type' => 'string', 'width' => 20, 'title' => 'Kabupaten'],
					'nama_propinsi' 	=> ['type' => 'string', 'width' => 20, 'title' => 'Propinsi'],
					'nama_kelas' 	=> ['type' => 'string', 'width' => 10, 'title' => 'Kelas'],
					'nama_status_siswa' 	=> ['type' => 'string', 'width' => 10, 'title' => 'Status Siswa'],
					'nama_ayah' 	=> ['type' => 'string', 'width' => 20, 'title' => 'Nama Ayah'],
					'nama_ibu' 	=> ['type' => 'string', 'width' => 20, 'title' => 'Nama Ibu'],
					'nama_status_orangtua' 	=> ['type' => 'string', 'width' => 15, 'title' => 'Status Orangtua'],
					'no_hp' 	=> ['type' => 'string', 'width' => 20, 'title' => 'No. HP'],
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
		foreach ($table_column as &$val) {
			if ($val == 'nama_kabupaten') {
				$val = 'CONCAT(jenis_kabupaten_kota, " ", nama_kabupaten) AS nama_kabupaten';
			}
		}
		$table_column = join(', ', $table_column);
		
		$sql = 'SELECT ' .  $table_column  . '
				FROM siswa
				LEFT JOIN agama USING(id_agama)
				LEFT JOIN siswa_kelas USING(id_siswa)
				LEFT JOIN orangtua USING(id_orangtua)
				LEFT JOIN status_orangtua USING(id_status_orangtua)
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan)
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)';
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
			if (key_exists('tgl_lahir', $row)) {
				if ($row['tgl_lahir']) {
					$row['tgl_lahir'] = format_date($row['tgl_lahir']);
				}
			}
			$writer->writeSheetRow($sheet_name, $row);
			$no++;
		}
		
		$tmp_file = ROOTPATH . 'public/tmp/siswa_' . time() . '.xlsx.tmp';
		$writer->writeToFile($tmp_file);
		return $tmp_file;
	}
	
	public function saveDataStatus() {
		
		$this->db->transStart();
		
		$jenis_status = '';
		if (!empty($_POST['id_status_siswa'])) {
			$sql = 'SELECT * FROM status_siswa WHERE id_status_siswa = ?';
			$result = $this->db->query($sql, $_POST['id_status_siswa'])->getRowArray();
			$jenis_status = $result['jenis_status'];
		}
		
		$status_kelas = ['masuk_baru', 'naik_kelas', 'pindah_kelas', 'pindah_masuk'];
		
		$id_kelas = in_array($jenis_status, $status_kelas) ? $_POST['id_kelas'] : null;
		// $this->db->table('siswa')->update(['id_kelas' => $id_kelas], ['id_siswa' => $_POST['id_siswa']]);
		
		$exp = explode('-', $_POST['tgl_status']);
		$tgl_status = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		$data_db = [
						'id_siswa' => $_POST['id_siswa']
						, 'tgl_status' => $tgl_status
						, 'id_status_siswa' => $_POST['id_status_siswa']
						, 'id_kelas' => $id_kelas
						, 'id_tahun_ajaran' => $_POST['id_tahun_ajaran']
						, 'keterangan' => $_POST['keterangan']
					];
		
		if (!empty($_POST['id'])) {
			$this->db->table('siswa_riwayat_status')->update($data_db, ['id_siswa_riwayat_status' => $_POST['id']]);
		} else {
			$this->db->table('siswa_riwayat_status')->insert($data_db);
		}
		$this->db->transComplete();
		if ($this->db->transStatus()) {
			return ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		} else {
			return ['status' => 'error', 'message' => 'Data gagal disimpan'];
		}
	}
	
	public function saveData() 
	{
		helper('upload_file');
		
		$exp = explode('-', $_POST['tgl_lahir']);
		$tgl_lahir = $exp[2].'-'.$exp[1].'-'.$exp[0];
		$data_db['nama'] = $_POST['nama'];
		$data_db['jenis_kelamin'] = $_POST['jenis_kelamin'];
		$data_db['id_agama'] = $_POST['id_agama'];
		$data_db['nisn'] = $_POST['nisn'];
		$data_db['nis'] = $_POST['nis'];
		$data_db['nik'] = $_POST['nik'];
		$data_db['tempat_lahir'] = $_POST['tempat_lahir'];
		$data_db['tgl_lahir'] = $tgl_lahir;
		$data_db['alamat'] = $_POST['alamat'];
		$data_db['id_wilayah_kelurahan'] = $_POST['id_wilayah_kelurahan'];
		
		$new_name = null;
		$siswa['foto'] = '';
		
		$path = ROOTPATH . '/public/images/siswa/';
		if ($_POST['id']) {
			$sql = 'SELECT * FROM siswa WHERE id_siswa = ?';
			$siswa = $this->db->query($sql, $_POST['id'])->getRowArray();
			$new_name = $siswa['foto'];
			$path_foto = $path . $siswa['foto'];
			
			if ($_POST['foto_delete_img']) {
				if ($siswa['foto']) {
					$del = delete_file($path_foto);
					$new_name = null;
					if (!$del) {
						$result['status'] = 'error';
						$result['message'] = 'Gagal menghapus gambar lama';
						return $result;
					}
				}
			}
		}
		
		if ($_FILES['foto']['name']) 
		{
			//old file
			if ($_POST['id']) {
				if ($siswa['foto']) {
					if (file_exists($path_foto)) {
						$unlink = delete_file($path_foto);
						if (!$unlink) {
							$result['message']['status'] = 'error';
							$result['message']['message'] = 'Gagal menghapus gambar lama';
							return $result;
						}
					}
				}
			}
			
			$new_name = upload_image($path, $_FILES['foto'], 300,300);
			if (!$new_name) {
				$result['message']['status'] = 'error';
				$result['message']['message'] = 'Error saat memperoses gambar';
				return $result;
			}
		}
		
		$data_db['foto'] = $new_name;
		
		$data_db_orangtua['nama_ayah'] = $_POST['nama_ayah'];
		$data_db_orangtua['nama_ibu'] = $_POST['nama_ibu'];
		$data_db_orangtua['no_hp'] = $_POST['no_hp'];
		$data_db_orangtua['id_status_orangtua'] = $_POST['id_status_orangtua'];
		
		if ($_POST['id']) 
		{
			$data_db['tgl_edit'] = date('Y-m-d');
			$data_db['id_pegawai_edit'] = $_SESSION['pegawai']['id_pegawai'];
			
			$query = $this->db->table('orangtua')->update($data_db_orangtua, ['id_orangtua' => $siswa['id_orangtua']]);
			$query = $this->db->table('siswa')->update($data_db, ['id_siswa' => $_POST['id']]);
			
			if ($query) {
				$result['status'] = 'ok';
				$result['message'] = 'Data berhasil disimpan';
			} else {
				$result['status'] = 'error';
				$result['message'] = 'Data gagal disimpan';
			}

		} else {

			$data_db['tgl_input'] = date('Y-m-d');
			$data_db['id_pegawai_input'] = $_SESSION['pegawai']['id_pegawai'];
			
			$query = $this->db->table('orangtua')->insert($data_db_orangtua);
			$id_orangtua = $this->db->insertID();
			$data_db['id_orangtua'] = $id_orangtua;
			$query = $this->db->table('siswa')->insert($data_db);
			$id_siswa = $this->db->insertID();
			
			$data_db_riwayat['id_siswa'] = $id_siswa;
			$data_db_riwayat['id_status_siswa'] = $_POST['id_status_siswa'];
			$data_db_riwayat['id_kelas'] = $_POST['id_kelas'];
			$data_db_riwayat['id_tahun_ajaran'] = $_POST['id_tahun_ajaran'];
			
			$exp = explode('-', $_POST['tgl_status']);
			$data_db_riwayat['tgl_status'] = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
			$query = $this->db->table('siswa_riwayat_status')->insert($data_db_riwayat);
			
			if ($query) {
				$result['status'] = 'ok';
				$result['message'] = 'Data berhasil disimpan';
				$result['id_siswa'] = $id_siswa;
			} else {
				$result['status'] = 'error';
				$result['message'] = 'Data gagal disimpan';
			}
		}
		
		return $result;
	}
	
	private function getWilayahKelurahan($nama_kelurahan, $nama_kecamatan, $nama_kabupaten, $nama_propinsi) {
		$sql = 'SELECT * FROM wilayah_kelurahan 
		LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
		LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
		LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
		WHERE nama_kelurahan = ? AND nama_kecamatan = ? AND nama_kabupaten = ? AND nama_propinsi = ?';
		$result = $this->db->query($sql, [ $nama_kelurahan, $nama_kecamatan, $nama_kabupaten, $nama_propinsi ])->getRowArray();
		return $result;
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
		
		// $siswa = $orangtua = $kategori = $barang_harga = $barang_image = [];
		$id_user = $this->session->get('pegawai')['id_pegawai'];
		$this->db->transStart();
		$num = 0;
		foreach ($reader->getSheetIterator() as $sheet) 
		{
			if (strtolower($sheet->getName()) == 'siswa') 
			{
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
						
						$data_value[$field] = trim($val);
					}
					
					if ($data_value) {
						
						// Id Kelas
						$sql = 'SELECT * FROM kelas WHERE nama_kelas = ?';
						$result = $this->db->query($sql, $data_value['kelas'])->getRowArray();
						if ($result) {
							$id_kelas = $result['id_kelas'];
						} else {
							$this->db->table('kelas')->insert(['nama_kelas' => $data_value['kelas']]);
							$id_kelas = $this->db->insertID();
						}
						
						// Orangtua
						$sql = 'SELECT * FROM orangtua WHERE nama_ayah = ? AND nama_ibu = ?';
						$result = $this->db->query($sql, [$data_value['nama_ayah'], $data_value['nama_ibu']])->getRowArray();
						if ($result) {
							$id_orangtua = $result['id_orangtua'];
						} else {
							
							$id_status_orangtua = null;
							if ($data_value['status_orangtua']) {
								$sql = 'SELECT * FROM status_orangtua WHERE nama_status_orangtua = ?';
								$result = $this->db->query($sql, $data_value['status_orangtua'])->getRowArray();
								$id_status_orangtua = $result ? $result['id_status_orangtua'] : null;
							}
							
							$orangtua = [
										'nama_ayah' => $data_value['nama_ayah']
										, 'nama_ibu' => $data_value['nama_ibu']
										, 'no_hp' => $data_value['no_hp']
										, 'id_status_orangtua' => $id_status_orangtua
									];
							
							$this->db->table('orangtua')->insert($orangtua);
							$id_orangtua = $this->db->insertID();
						}
											
						//Id Wilayah Kelurahan
						$wilayah = $this->getWilayahKelurahan($data_value['kelurahan'], $data_value['kecamatan'], $data_value['kabupaten'], $data_value['propinsi']);
						$id_wilayah_kelurahan = $wilayah ? $wilayah['id_wilayah_kelurahan'] : null;
						
						// Id Agama
						$sql = 'SELECT * FROM agama WHERE agama = ?';
						$result = $this->db->query($sql, $data_value['agama'])->getRowArray();
						if ($result) {
							$id_agama = $result['id_agama'];
						} else {
							$this->db->table('agama')->insert(['agama' => $data_value['agama']]);
							$id_agama = $this->db->insertID();
						}

						$siswa = ['nama' => $data_value['nama']
										, 'jenis_kelamin' => $data_value['jenis_kelamin']
										, 'id_agama' => $id_agama
										, 'nisn' => $data_value['nisn']
										, 'nis' => $data_value['nis']
										, 'nik' => $data_value['nik']
										, 'tempat_lahir' => $data_value['tempat_lahir']
										, 'tgl_lahir' => $data_value['tanggal_lahir']
										, 'alamat' => $data_value['alamat']
										, 'id_wilayah_kelurahan' => $id_wilayah_kelurahan
										, 'id_orangtua' => $id_orangtua
										, 'tgl_input' => date('Y-m-d H:i:s')
										, 'id_pegawai_input' => $id_user
									];
						$this->db->table('siswa')->insert($siswa);
						$id_siswa = $this->db->insertID();
						
						// Status
						if ($data_value['status_siswa']) 
						{
							// Status
							$sql = 'SELECT * FROM status_siswa WHERE jenis_status = ?';
							$result = $this->db->query($sql, $data_value['status_siswa'])->getRowArray();
							$id_status_siswa = $result ? $result['id_status_siswa'] : null;
							
							if ($id_status_siswa) {
							
								// Tahun Ajaran
								$sql = 'SELECT * FROM tahun_ajaran WHERE tahun_ajaran = ?';
								$result = $this->db->query($sql, $data_value['tahun_ajaran'])->getRowArray();
								if ($result) {
									$id_tahun_ajaran = $result['id_tahun_ajaran'];
								} else {
									$this->db->table('tahun_ajaran')->insert(['tahun_ajaran' => $data_value['tahun_ajaran']]);
									$id_tahun_ajaran = $this->db->insertID();
								}
								
								$exp = explode('-', $data_value['tanggal_status']);
								
								$tgl_status = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
								$data_db_status['id_siswa'] = $id_siswa;
								$data_db_status['tgl_status'] = $tgl_status;
								$data_db_status['id_status_siswa'] = $id_status_siswa;
								$data_db_status['id_kelas'] = $id_kelas;
								$data_db_status['id_tahun_ajaran'] = $id_tahun_ajaran;
								$this->db->table('siswa_riwayat_status')->insert($data_db_status);
							}
						}
						$num++;
					}
				}
				break;
			}
		}
		
		$reader->close();
		delete_file($path . $filename);
		
		if (!$num) {
			return ['status' => 'error', 'message' => 'Data file excel kosong'];
		}
		
		$this->db->transComplete();
		if ($this->db->transStatus()) {
			$result = ['status' => 'ok', 'message' => 'Data berhasil di masukkan ke dalam tabel siswa sebanyak ' . format_ribuan($num) . ' baris'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data gagal disimpan'];
		}
		
		return $result;
	}
	
	public function countAllData($where) {
		$sql = 'SELECT COUNT(*) AS jml FROM siswa' . $where;
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData($where) {

    $columns = $this->request->getPost('columns');

    // Filter berdasarkan kelas (jika ada di URL)
    if (!empty($_GET['id_kelas'])) {
        $where .= ' AND kelas.id_kelas = ' . intval($_GET['id_kelas']);
    }

    // ==========================
    // 🔍 Pencarian (Search)
    // ==========================
    $search_all = @$this->request->getPost('search')['value'];
    if ($search_all) {
        $columns[]['data'] = 'tempat_lahir';
        $where_col = [];

        foreach ($columns as $val) {
            if (strpos($val['data'], 'ignore_search') !== false) 
                continue;
            if (strpos($val['data'], 'ignore') !== false)
                continue;

            // Tentukan prefix tabel untuk setiap kolom
            switch ($val['data']) {
                case 'nama_kelas':
                    $field = 'kelas.nama_kelas';
                    break;
                case 'id_kelas':
                    $field = 'kelas.id_kelas';
                    break;
                case 'nama':
                    $field = 'siswa.nama';
                    break;
                case 'alamat':
                    $field = 'siswa.alamat';
                    break;
                case 'tgl_lahir':
                    $field = 'siswa.tgl_lahir';
                    break;
                case 'tempat_lahir':
                    $field = 'siswa.tempat_lahir';
                    break;
                case 'nisn':
                    $field = 'siswa.nisn';
                    break;
                case 'nis':
                    $field = 'siswa.nis';
                    break;
                default:
                    $field = 'siswa.' . $val['data'];
            }

            $where_col[] = $field . ' LIKE "%' . $this->db->escapeLikeString($search_all) . '%"';
        }

        $where .= ' AND (' . join(' OR ', $where_col) . ') ';
    }

    // ==========================
    // 🔃 Urutan (Order)
    // ==========================
    $order_data = $this->request->getPost('order');
    $order = '';
    if (!empty($order_data)) {
        $col_index = $order_data[0]['column'];
        $dir = strtoupper($order_data[0]['dir']);
        $col_name = $columns[$col_index]['data'];

        if (strpos($col_name, 'ignore_search') === false) {
            // Gunakan prefix tabel juga
            switch ($col_name) {
                case 'nama_kelas':
                    $order_by = 'kelas.nama_kelas ' . $dir;
                    break;
                default:
                    $order_by = 'siswa.' . $col_name . ' ' . $dir;
            }
            $order = ' ORDER BY ' . $order_by;
        }
    }

    // ==========================
    // 📊 Query Total Filtered
    // ==========================
    $sql = 'SELECT COUNT(*) AS jml_data FROM siswa 
            LEFT JOIN siswa_kelas ON siswa_kelas.id_siswa = siswa.id_siswa
            LEFT JOIN kelas ON kelas.id_kelas = siswa_kelas.id_kelas
            LEFT JOIN orangtua ON orangtua.id_orangtua = siswa.id_orangtua
            LEFT JOIN status_orangtua ON status_orangtua.id_status_orangtua = orangtua.id_status_orangtua
            ' . $where;

    $total_filtered = $this->db->query($sql)->getRowArray()['jml_data'] ?? 0;

    // ==========================
    // 📄 Query Data
    // ==========================
    $start = $this->request->getPost('start') ?: 0;
    $length = $this->request->getPost('length') ?: 10;

    $sql = 'SELECT siswa.*, kelas.nama_kelas, orangtua.nama_ayah, orangtua.nama_ibu, status_orangtua.nama_status_orangtua 
            FROM siswa
            LEFT JOIN siswa_kelas ON siswa_kelas.id_siswa = siswa.id_siswa
            LEFT JOIN kelas ON kelas.id_kelas = siswa_kelas.id_kelas
            LEFT JOIN orangtua ON orangtua.id_orangtua = siswa.id_orangtua
            LEFT JOIN status_orangtua ON status_orangtua.id_status_orangtua = orangtua.id_status_orangtua
            ' . $where . $order . ' 
            LIMIT ' . intval($start) . ', ' . intval($length);

    $data = $this->db->query($sql)->getResultArray();

    // ==========================
    // 🧩 Return
    // ==========================
    return [
        'data' => $data,
        'total_filtered' => $total_filtered
    ];
}

	
	public function countAllSiswaRiwayatStatus($id) {
		$query = $this->db->query('SELECT COUNT(*) as jml 
					FROM siswa_riwayat_status
					WHERE id_siswa = ?', $id)->getRow();
		return $query->jml;
	}
	
	public function getListSiswaRiwayatStatus($id) {
		
		// Get siswa
		$columns = $this->request->getPost('columns');
		$order_by = '';
		
		// Search
		$search_all = @$this->request->getPost('search')['value'];
		$where = ' WHERE id_siswa = ' . $id . ' ';
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
		
		$sql = 'SELECT COUNT(*) AS jml FROM siswa_riwayat_status
				LEFT JOIN kelas USING(id_kelas)
				LEFT JOIN status_siswa USING(id_status_siswa)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				' . $where;
				
		$query = $this->db->query($sql)->getRowArray();
		$total_filtered = $query['jml'];
		
		$sql = 'SELECT * FROM siswa_riwayat_status
				LEFT JOIN kelas USING(id_kelas)
				LEFT JOIN status_siswa USING(id_status_siswa)
				LEFT JOIN tahun_ajaran USING(id_tahun_ajaran)
				' . $where . $order;
			
		$data = $this->db->query($sql)->getResultArray();
		return ['data' => $data, 'total_filtered' => $total_filtered];
		
	}
}
?>