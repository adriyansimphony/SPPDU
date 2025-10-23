<?php
namespace App\Models;

class IdentitasSekolahModel extends \App\Models\BaseModel
{
	public function getIdentitasSekolah() {
		$sql = 'SELECT * FROM identitas_sekolah';
		$result = $this->db->query($sql)->getRowArray();
		return $result;
	}
	
	public function saveData() 
	{
		helper('upload_file');
		$data_db = ['nama_sekolah' => $_POST['nama_sekolah']
					, 'alamat_sekolah' => $_POST['alamat_sekolah']
					, 'id_wilayah_kelurahan' => $_POST['id_wilayah_kelurahan']
					, 'email' => $_POST['email']
					, 'no_telp' => $_POST['no_telp']
					, 'no_hp' => $_POST['no_hp']
					, 'website' => $_POST['website']
					, 'facebook' => $_POST['facebook']
					, 'youtube' => $_POST['youtube']
					, 'telegram' => $_POST['telegram']
					, 'twitter' => $_POST['twitter']
					, 'instagram' => $_POST['instagram']
					, 'kota_tandatangan' => $_POST['kota_tandatangan']
				];
		
		$path = ROOTPATH . '/public/images/sekolah/';
		if ($_POST['id']) {
			$sql = 'SELECT * FROM identitas_sekolah WHERE id_identitas_sekolah = ?';
			$sekolah = $this->db->query($sql, $_POST['id'])->getRowArray();
			$new_name = $sekolah['logo'];
			$path_logo = $path . $sekolah['logo'];
			
			if ($_POST['logo_delete_img']) {
				if ($sekolah['logo']) {
					$del = delete_file($path_logo);
					$new_name = null;
					if (!$del) {
						$result['status'] = 'error';
						$result['message'] = 'Gagal menghapus gambar lama';
						return $result;
					}
				}
			}
		}
		
		if ($_FILES['logo']['name']) 
		{
			//old file
			if ($_POST['id']) {
				if ($sekolah['logo']) {
					if (file_exists($path_logo)) {
						$unlink = delete_file($path_logo);
						if (!$unlink) {
							$result['message']['status'] = 'error';
							$result['message']['message'] = 'Gagal menghapus gambar lama';
							return $result;
						}
					}
				}
			}
			
			$new_name = upload_image($path, $_FILES['logo'], 300,300);
			if (!$new_name) {
				$result['message']['status'] = 'error';
				$result['message']['message'] = 'Error saat memperoses gambar';
				return $result;
			}
		}
		
		$data_db['logo'] = $new_name;

		$query_result = $this->db->table('identitas_sekolah')->update($data_db, ['id_identitas_sekolah' => $_POST['id']]);
		
		if ($query_result) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
}
?>