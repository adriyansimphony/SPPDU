<?php
namespace App\Models\Builtin;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use CodeIgniter\Database\Exceptions\DatabaseException;

class PegawaiModel extends \App\Models\BaseModel
{	
	public function getRoles() {
		$sql = 'SELECT * FROM role';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getPegawaiAdmin() {
		$sql = 'SELECT * 
				FROM pegawai 
				LEFT JOIN pegawai_role USING(id_pegawai)
				LEFT JOIN role USING (id_role) 
				WHERE nama_role = "admin"';
		return $this->db->query($sql)->getResultArray();
	}
	
	public function getJmlPegawai() {
		$sql = 'SELECT COUNT(*) AS jml FROM pegawai';
		$pegawai = $this->db->query($sql)->getRowArray();
		return $pegawai['jml'];
	}
	
	public function getAllAgama() {
		$sql = 'SELECT * FROM agama';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getSettingRegister() {
		$sql = 'SELECT * FROM setting WHERE type="register"';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getListModules() {
		
		$sql = 'SELECT * FROM module LEFT JOIN module_status USING(id_module_status) ORDER BY nama_module';
		return $this->db->query($sql)->getResultArray();
	}
	
	public function getAllJabatan() {
		$sql = 'SELECT * FROM jabatan ORDER BY urut';
		return $this->db->query($sql)->getResultArray();
	}
	
	public function getJabatanByIdPegawai($id) {
		$sql = 'SELECT * FROM pegawai_jabatan LEFT JOIN jabatan USING(id_jabatan) WHERE id_pegawai = ? ORDER BY urut';
		$data = $this->db->query($sql, $id)->getResultArray();
		$result = [];
		foreach ($data as $val) {
			$result[$val['id_jabatan']] = $val['id_jabatan'];
		}
		return $result;
	}
	
	public function getAllStatusPegawai() {
		$sql ='SELECT * FROM status_pegawai';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getListStatusPegawai() {
		$sql = 'SELECT * FROM status_pegawai';
		$data = $this->db->query($sql)->getResultArray();
		foreach ($data as $val) {
			$result[$val['id_status_pegawai']] = $val['nama_status_pegawai'];
		}
		return $result;
	}
	
	public function getRiwayatPegawaiById($id) {
		$sql = 'SELECT * FROM pegawai_riwayat_status WHERE id_pegawai_riwayat_status = ?';
		$result = $this->db->query($sql, $id)->getRowArray();
		return $result;
	}
	
	public function saveDataStatus() {
		
		$this->db->transStart();
		
		$exp = explode('-', $_POST['tgl_status']);
		$tgl_status = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		$data_db = [
						'id_pegawai' => $_POST['id_pegawai']
						, 'tgl_status' => $tgl_status
						, 'id_status_pegawai' => $_POST['id_status_pegawai']
						, 'keterangan' => $_POST['keterangan']
					];
		
		if (!empty($_POST['id'])) {
			$this->db->table('pegawai_riwayat_status')->update($data_db, ['id_pegawai_riwayat_status' => $_POST['id']]);
		} else {
			$this->db->table('pegawai_riwayat_status')->insert($data_db);
		}
		$this->db->transComplete();
		if ($this->db->transStatus()) {
			return ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		} else {
			return ['status' => 'error', 'message' => 'Data gagal disimpan'];
		}
	}
	
	public function saveData($pegawai_permission = []) 
	{ 
		helper('upload_file');
		
		$fields = ['nama', 'jenis_kelamin', 'id_agama', 'no_hp', 'nip_pegawai', 'nik', 'email', 'tempat_lahir', 'tgl_lahir', 'id_wilayah_kelurahan','alamat'];
		if (in_array('update_all', $pegawai_permission)) {
			$add_field = ['status', 'default_page_id_role', 'default_page_id_module', 'default_page_url'];
			$fields = array_merge($fields, $add_field);
		}

		foreach ($fields as $field) 
		{
			if ($field == 'tgl_lahir') {
				$exp = explode('-', $_POST[$field]);
				$tanggal = $exp[2].'-'.$exp[1].'-'.$exp[0];
				$data_db[$field] = $tanggal;
				continue;
			}
			
			$data_db[$field] = $this->request->getPost($field);
		}
		
		if ( (!empty($_POST['option_ubah_password']) && $_POST['option_ubah_password'] == 'Y') 
				||  (empty($_POST['option_ubah_password'])) 
		   ) {
			$data_db['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
		}
		
		$data_db['default_page_type'] = $this->request->getPost('option_default_page');
			
		$new_name = null;
		$pegawai['foto'] = '';
		
		$path = ROOTPATH . '/public/images/pegawai/';
		if ($_POST['id']) {
			$sql = 'SELECT * FROM pegawai WHERE id_pegawai = ?';
			$pegawai = $this->db->query($sql, $_POST['id'])->getRowArray();
			$new_name = $pegawai['foto'];
			$path_foto = $path . $pegawai['foto'];
			
			if ($_POST['foto_delete_img']) {
				if ($pegawai['foto']) {
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
				if ($pegawai['foto']) {
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
			
			$new_name = \upload_image($path, $_FILES['foto'], 300,300);
			if (!$new_name) {
				$result['message']['status'] = 'error';
				$result['message']['message'] = 'Error saat memperoses gambar';
				return $result;
			}
		}
		
		$data_db['foto'] = $new_name;
		$this->db->transStart();
			
		// Save database
		if ($this->request->getPost('id')) {
			$data_db['tgl_edit'] = date('Y-m-d');
			$data_db['id_pegawai_edit'] = $_SESSION['pegawai']['id_pegawai'];
			
			$id_pegawai = $this->request->getPost('id');
			$this->db->table('pegawai')->update($data_db, ['id_pegawai' => $id_pegawai]);
			
			// Jabatan
			if (!empty($_POST['id_jabatan'])) {
				$this->db->table('pegawai_jabatan')->delete(['id_pegawai' => $_POST['id']]);
			}
		} else {
			$data_db['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
			$data_db['verified'] = 1;
			$data_db['tgl_input'] = date('Y-m-d');
			$data_db['id_pegawai_input'] = $_SESSION['pegawai']['id_pegawai'];
			
			$this->db->table('pegawai')->insert($data_db);
			$id_pegawai = $this->db->insertID();
		}
		
		// Jabatan
		$data_db_jabatan = [];
		if (!empty($_POST['id_jabatan'])) {
			foreach ($_POST['id_jabatan'] as $val) {
				$data_db_jabatan[] = ['id_pegawai' => $id_pegawai, 'id_jabatan' => $val];
			}
			$this->db->table('pegawai_jabatan')->delete(['id_pegawai' => $id_pegawai]);
			$this->db->table('pegawai_jabatan')->insertBatch($data_db_jabatan);
		}
				
		if (in_array('update_all', $pegawai_permission)) {
			$data_db = [];
			foreach ($_POST['id_role'] as $id_role) {
				$data_db[] = ['id_pegawai' => $id_pegawai, 'id_role' => $id_role];
			}
			
			$this->db->table('pegawai_role')->delete(['id_pegawai' => $id_pegawai]);
			$this->db->table('pegawai_role')->insertBatch($data_db);
		}
		
		$this->db->transComplete();
		$trans = $this->db->transStatus();
		
		if ($trans) {
			$result = ['status' =>'ok', 'message' => 'Data berhasil disimpan', 'id_pegawai' => $id_pegawai];

			if ($this->session->get('pegawai')['id_pegawai'] == $id_pegawai) {
				// Reload data pegawai
				$this->session->set('pegawai', $this->getPegawaiById($this->session->get('pegawai')['id_pegawai']) );
			}
		} else {
			$result = ['status' => 'error', 'message' => 'Data gagal disimpan'];
		}
								
		return $result;
	}
	
	private function getPegawaiByEmail($email) {
		$sql = 'SELECT * FROM pegawai WHERE email = ?';
		return $this->db->query($sql, $email)->getRowArray();
	}
	
	private function getPegawaiByNip($nip_pegawai) {
		$sql = 'SELECT * FROM pegawai WHERE nip_pegawai = ?';
		return $this->db->query($sql, $nip_pegawai)->getRowArray();
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
	
	public function writeExcel() 
	{
		require_once(ROOTPATH . "/app/ThirdParty/PHPXlsxWriter/xlsxwriter.class.php");
						
		// $sql = $this->sqlQuery();	
		
		$colls = [
					'no' 			=> ['type' => '#,##0', 'width' => 5, 'title' => 'No'],
					'nama' 			=> ['type' => 'string', 'width' => 30, 'title' => 'Nama'],
					'jenis_kelamin' => ['type' => 'string', 'width' => 13, 'title' => 'Jenis Kelamin'],
					'agama' 		=> ['type' => 'string', 'width' => 13, 'title' => 'Agama'],
					'nip_pegawai' 	=> ['type' => 'string', 'width' => 15, 'title' => 'NIP'],
					'nik' 			=> ['type' => 'string', 'width' => 18, 'title' => 'NIK'],
					'nama_jabatan' 	=> ['type' => 'string', 'width' => 20, 'title' => 'Jabatan'],
					'tempat_lahir' 	=> ['type' => 'string', 'width' => 15, 'title' => 'Tempat Lahir'],
					'tgl_lahir' 	=> ['type' => 'string', 'width' => 20, 'title' => 'Tanggal Lahir'],
					'email' 		=> ['type' => 'string', 'width' => 22, 'title' => 'Email'],
					'no_hp' 		=> ['type' => 'string', 'width' => 15, 'title' => 'No. HP'],
					'alamat' 		=> ['type' => 'string', 'width' => 30, 'title' => 'Alamat'],
					'nama_kelurahan' 	=> ['type' => 'string', 'width' => 20, 'title' => 'Kelurahan'],
					'nama_kecamatan' 	=> ['type' => 'string', 'width' => 20, 'title' => 'Kecamatan'],
					'nama_kabupaten' 	=> ['type' => 'string', 'width' => 20, 'title' => 'Kabupaten'],
					'nama_propinsi' 		=> ['type' => 'string', 'width' => 20, 'title' => 'Propinsi'],
					'nama_status_pegawai' 	=> ['type' => 'string', 'width' => 15, 'title' => 'Status'],
					'tgl_status' 	=> ['type' => 'string', 'width' => 15, 'title' => 'Tanggal Status']
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
			if ($val == 'nama_jabatan') {
				$val = 'GROUP_CONCAT(DISTINCT nama_jabatan SEPARATOR " + ") AS nama_jabatan';
			}
		}
		$table_column = join(', ', $table_column);
		
		$sql = 'SELECT ' .  $table_column  . '
				FROM pegawai
				LEFT JOIN pegawai_jabatan USING(id_pegawai)
				LEFT JOIN pegawai_status USING(id_pegawai)
				LEFT JOIN jabatan USING(id_jabatan)
				LEFT JOIN agama USING(id_agama)
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan)
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
				GROUP BY id_pegawai';
		
		$query = $this->db->query($sql);
		
		// Excel
		$sheet_name = strtoupper('Daftar Pegawai');
		$writer = new \XLSXWriter();
		$writer->setAuthor('Jagowebdev');
		
		$writer->writeSheetHeader($sheet_name, $col_header_type, $col_options = ['widths'=> $col_width, 'suppress_row'=>true]);
		$writer->writeSheetRow($sheet_name, $col_header);
		$writer->updateFormat($sheet_name, $col_type);
		
		$no = 1;
		while ($row = $query->getUnbufferedRow('array')) {
			array_unshift($row, $no);
			if (key_exists('jenis_kelamin', $row)) {
				if ($row['jenis_kelamin'] == 'L') {
					$row['jenis_kelamin'] = 'Laki-Laki';
				} else if ($row['jenis_kelamin'] == 'P') {
					$row['jenis_kelamin'] = 'Perempuan';
				} else {
					$row['jenis_kelamin'] = '';
				}
			}
			
			if (key_exists('tgl_lahir', $row)) {
				$row['tgl_lahir'] = format_date($row['tgl_lahir']);
			}
			
			if (key_exists('tgl_bergabung', $row)) {
				$row['tgl_bergabung'] = format_date($row['tgl_bergabung']);
			}
			$writer->writeSheetRow($sheet_name, $row);
			$no++;
		}
		
		$tmp_file = ROOTPATH . 'public/tmp/pegawai_' . time() . '.xlsx.tmp';
		$writer->writeToFile($tmp_file);
		return $tmp_file;
	}
	
	public function uploadExcel() 
	{
		helper(['upload_file', 'format']);
		$path = ROOTPATH . 'public/tmp/';
		
		$file = $this->request->getFile('file_excel');
		if (! $file->isValid())
		{
			throw new RuntimeException($file->getErrorString().'('.$file->getError().')');
		}
				
		require_once 'app/ThirdParty/Spout/src/Spout/Autoloader/autoload.php';
		
		$filename = upload_file($path, $_FILES['file_excel']);
		$reader = ReaderEntityFactory::createReaderFromFile($path . $filename);
		$reader->open($path . $filename);
		
		$sql = 'SELECT * FROM role';
		$data = $this->db->query($sql)->getResultArray();
		$roles = [];
		foreach($data as $val) {
			$roles[$val['nama_role']] = $val['id_role'];
		}
		
		$warning = [];
		$error_message = [];
		$row_inserted = 0;
		$sql = 'SELECT * FROM module WHERE nama_module = ?';
		$module_pegawai = $this->db->query($sql, 'builtin/pegawai')->getRowArray();
		foreach ($reader->getSheetIterator() as $sheet) 
		{
			$num_row = 0;
			
			if (strtolower($sheet->getName()) == 'pegawai') 
			{
				foreach ($sheet->getRowIterator() as $num_row => $row) 
				{
					$error = false;
					$role_list = [];
					$data_db = [];
					$data_db_role = [];
					$error_message_row = [];
					
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
							$val = $val->format('d-m-Y H:i:s');
						}
						
						if ($field == 'role') {
							if (trim($val)) {
								$exp = explode(',', $val);
								$role_list = array_map('trim', $exp);
							}
							continue;
						}
						
						if ($field == 'password') {
							$val = password_hash($val, PASSWORD_DEFAULT);
						}
						
						if ($field == 'email') {
							if ($this->getPegawaiByEmail($field)) {
								$error_message_row[] = 'Email ' . $field . ' sudah digunakan';
								$error = true;
							}
						}
						
						if ($field == 'nip_pegawai') {
							if ($this->getPegawaiByNip($field)) {
								$error_message_row[] = 'NIP Pegawai ' . $field . ' sudah digunakan';
								$error = true;
							}
						}
												
						$data_value[trim($field)] = $val;
					}

					if ($data_value && !$error) 
					{				
						$wilayah = $this->getWilayahKelurahan($data_value['kelurahan'], $data_value['kecamatan'], $data_value['kabupaten'], $data_value['propinsi']);
						$id_wilayah_kelurahan = $wilayah ? $wilayah['id_wilayah_kelurahan'] : null;
						
						$tgl_lahir = null;
						if (!empty($data_value['tanggal_lahir'])) {
							$exp = explode(' ', $data_value['tanggal_lahir']);
							$tanggal = $exp[0];
							$exp = explode('-', $tanggal);
							$tgl_lahir = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
						}
						
						// Id Agama
						$sql = 'SELECT * FROM agama WHERE agama = ?';
						$result = $this->db->query($sql, $data_value['agama'])->getRowArray();
						if ($result) {
							$id_agama = $result['id_agama'];
						} else {
							$this->db->table('agama')->insert(['agama' => $data_value['agama']]);
							$id_agama = $this->db->insertID();
						}
												
						$pegawai = ['nama' => $data_value['nama']
										, 'nik' => $data_value['nik']
										, 'nip_pegawai' => $data_value['nip_pegawai']
										, 'email' => $data_value['email']
										, 'tempat_lahir' => $data_value['tempat_lahir']
										, 'id_agama' => $id_agama
										, 'jenis_kelamin' => $data_value['jenis_kelamin']
										, 'tgl_lahir' => $tgl_lahir
										, 'alamat' => $data_value['alamat']
										, 'id_wilayah_kelurahan' => $id_wilayah_kelurahan
										, 'no_hp' => $data_value['no_hp']
										, 'password' => password_hash($data_value['password'], PASSWORD_DEFAULT)
										, 'status' => 'active'
										, 'verified' => 1
										, 'default_page_type' => 'id_module'
										, 'default_page_url' => null
										, 'default_page_id_module' => $module_pegawai['id_module']
										, 'default_page_id_role' => null
										, 'tgl_input' => date('Y-m-d')
										, 'id_pegawai_input' => $this->session->get('pegawai')['id_pegawai']
									];
						
						$this->db->table('pegawai')->insert($pegawai);
						$id_pegawai = $this->db->insertID();
						
						// Jabatan
						$exp = explode('+', $data_value['jabatan']);
						foreach ($exp as $nama_jabatan) 
						{
							$nama_jabatan = trim($nama_jabatan);
							$sql = 'SELECT * FROM jabatan WHERE nama_jabatan = ?';
							$result = $this->db->query($sql, $nama_jabatan)->getRowArray();
							if ($result) {
								$id_jabatan = $result['id_jabatan'];
							} else {
								$this->db->table('jabatan')->insert(['nama_jabatan' => $nama_jabatan]);
								$id_jabatan = $this->db->insertID();
							}
							$this->db->table('pegawai_jabatan')->insert(['id_pegawai' => $id_pegawai, 'id_jabatan' => $id_jabatan]);
						}
						
						// Status Pegawai
						$tgl_bergabung = null;
						if (!empty($data_value['tanggal_bergabung'])) {
							$exp = explode(' ', $data_value['tanggal_bergabung']);
							$tanggal = $exp[0];
							$exp = explode('-', $tanggal);
							$tgl_bergabung = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
							$this->db->table('pegawai_riwayat_status')->insert(['id_pegawai' => $id_pegawai, 'id_status_pegawai' => 1, 'tgl_status' => $tgl_bergabung]);
						}
									
						$data_db_role = [];
						if ($role_list) {
							foreach ($role_list as $role_name) {
								if (key_exists($role_name, $roles)) {
									$data_db_role[] = ['id_pegawai' => $id_pegawai, 'id_role' => $roles[$role_name]];
								} else {
									$warning[] = 'Role ' . $role_name . ' pada pegawai ' . $nama . ' tidak ada di tabel pegawai_role';
								}
							}
							
							if ($data_db_role) {
								$query = $this->db->table('pegawai_role')->insertBatch($data_db_role);
							}
						} else {
							$warning[] = 'Role untuk pegawai ' . $nama . ' belum didefinisikan';
						}
						
						
						if (true) {
							$row_inserted++;
						} else {
							$error_message_row[] =  'Data gagal disimpan';
						}
					}

					if ($error_message_row) {
						$error_message[] = 'Baris ' . $num_row . ': ' . join(', ', $error_message_row);
					}
					
					$num_row += 1;
				}
				break;
			}
		}
		
		$reader->close();
		delete_file($path . $filename);
		
		$message = [];
		$message['ok'] = 'Data berhasil di masukkan ke dalam tabel pegawai sebanyak ' . format_ribuan($row_inserted) . ' baris';
		if ($warning) {
			$message['warning'] = $warning; 
		}

		if ($error_message) {
			$message['error'] = $error_message;
		}
		
		$result['status'] = 'upload_excel';
		$result['message'] = $message;
		
		return $result;
	}
	
	public function deleteStatus() {
		$result = $this->db->table('pegawai_riwayat_status')->delete(['id_pegawai_riwayat_status' => $_POST['id']]);
		return $result;
	}
	
	public function deletePegawai() 
	{
		$id_pegawai = $this->request->getPost('id');
		$sql = 'SELECT * FROM pegawai WHERE id_pegawai = ?';
		$pegawai = $this->db->query($sql, $id_pegawai)->getRowArray();
		if (!$pegawai) {
			return false;
		}
			
		$this->db->transStart();
		$this->db->table('pegawai')->delete(['id_pegawai' => $id_pegawai]);
		$this->db->table('pegawai_jabatan')->delete(['id_pegawai' => $id_pegawai]);
		$this->db->table('pegawai_role')->delete(['id_pegawai' => $id_pegawai]);
		$delete = $this->db->affectedRows();
		$this->db->transComplete();
		$trans = $this->db->transStatus();
		
		if ($trans) {
			if (!empty($pegawai['avatar'])) {
				delete_file(ROOTPATH . 'public/images/pegawai/' . $pegawai['avatar']);
			}
		} else {
			return false;
		}
		
		return true;
	}
	
	public function deleteAllPegawai() 
	{
		// List role
		$sql = 'SELECT id_pegawai 
				FROM pegawai 
				LEFT JOIN pegawai_role USING(id_pegawai) 
				LEFT JOIN role USING (id_role) 
				WHERE nama_role = "admin"';
		$result = $this->db->query($sql)->getResultArray();
		$list_role = [];
		foreach ($result as $val) {
			$list_role[] = $val['id_pegawai'];
		}
		
		// List Pegawai
		$id_pegawai = $this->request->getPost('id');
		$sql = 'SELECT * 
				FROM pegawai 
				LEFT JOIN pegawai_role USING(id_pegawai) 
				LEFT JOIN role USING (id_role) 
				WHERE id_pegawai NOT IN (' . join(',', $list_role) . ')';
		$pegawai = $this->db->query($sql, $id_pegawai)->getRowArray();
		if (!$pegawai) {
			return ['status' => 'error', 'message' => 'Tidak ditemukan pegawai yang memiliki role bukan admin'];
		}
					
		$this->db->transStart();
				
		// Pegawai
		$sql = 'DELETE FROM pegawai WHERE id_pegawai NOT IN (' . join(',', $list_role) . ')';
		$this->db->query($sql);
		
		$sql = 'SELECT MAX(id_pegawai) AS max FROM pegawai';
		$data = $this->db->query($sql)->getRowArray();
		$max = $data['max'] + 1;
		$sql = 'ALTER TABLE pegawai AUTO_INCREMENT ' . $max;
		$this->db->query($sql);
		
		// Jabatan
		$sql = 'DELETE FROM pegawai_jabatan WHERE id_pegawai NOT IN (' . join(',', $list_role) . ')';
		$this->db->query($sql);
		
		// Role
		$sql = 'DELETE FROM pegawai_role WHERE id_pegawai NOT IN (' . join(',', $list_role) . ')';
		$this->db->query($sql);
		
		// Riwayat Status
		$sql = 'DELETE FROM pegawai_riwayat_status WHERE id_pegawai NOT IN (' . join(',', $list_role) . ')';
		$this->db->query($sql);
		
		$sql = 'SELECT MAX(id_pegawai_riwayat_status) AS max FROM pegawai_riwayat_status';
		$data = $this->db->query($sql)->getRowArray();
		$max = $data['max'] + 1;
		$sql = 'ALTER TABLE pegawai_riwayat_status AUTO_INCREMENT ' . $max;
		$this->db->query($sql);
				
		$this->db->transComplete();
		$trans = $this->db->transStatus();
		
		if ($trans) {
			foreach ($pegawai as $val) {
				if ($pegawai['foto']) {
					delete_file(ROOTPATH . 'public/images/pegawai/' . $pegawai['foto']);
				}
			}
		} else {
			return ['status' => 'error', 'message' => 'Tidak ada data yang dihapus'];
		}
		
		return ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
	}

	public function updatePassword() {
		$password_hash = password_hash($this->request->getPost('password_new'), PASSWORD_DEFAULT);
		$update = $this->db->query('UPDATE pegawai SET password = ? 
									WHERE id_pegawai = ? ', [$password_hash, $this->pegawai['id_pegawai']]
								);		
		return $update;
	}
	
	public function countAllPegawai($where = null) {
		$query = $this->db->query('SELECT COUNT(*) as jml FROM pegawai' . $where)->getRow();
		return $query->jml;
	}
	
	public function getListPegawai($where) {
		
		// Get pegawai
		$columns = $this->request->getPost('columns');
		$order_by = '';
		
		// Search
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
		
		$sql = 'SELECT pegawai.*, GROUP_CONCAT(DISTINCT nama_jabatan SEPARATOR " + ") AS nama_jabatan, GROUP_CONCAT(DISTINCT judul_role) AS judul_role FROM pegawai 
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
	
	public function countAllPegawaiRiwayatStatus($id) {
		$query = $this->db->query('SELECT COUNT(*) as jml 
					FROM pegawai_riwayat_status
					WHERE id_pegawai = ?', $id)->getRow();
		return $query->jml;
	}
	
	public function getListPegawaiRiwayatStatus($id) {
		
		// Get pegawai
		$columns = $this->request->getPost('columns');
		$order_by = '';
		
		// Search
		$search_all = @$this->request->getPost('search')['value'];
		$where = ' WHERE id_pegawai = ' . $id . ' ';
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
		
		$sql = 'SELECT COUNT(*) AS jml FROM pegawai_riwayat_status
				LEFT JOIN pegawai USING(id_pegawai)
				LEFT JOIN status_pegawai USING(id_status_pegawai)
				' . $where;
				
		$query = $this->db->query($sql)->getRowArray();
		$total_filtered = $query['jml'];
		
		$sql = 'SELECT * FROM pegawai_riwayat_status
				LEFT JOIN pegawai USING(id_pegawai)
				LEFT JOIN status_pegawai USING(id_status_pegawai)
				' . $where . $order;
			
		$data = $this->db->query($sql)->getResultArray();
		return ['data' => $data, 'total_filtered' => $total_filtered];
		
	}
}
?>