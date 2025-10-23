<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Models;

class SettingInvoiceModel extends \App\Models\BaseModel
{
	public function getSettingInvoice() {
		$sql = 'SELECT * FROM setting WHERE type = ?';
		$result = $this->db->query($sql, 'invoice')->getResultArray();
		return $result;
	}
	
	public function getSettingNotaRetur() {
		$sql = 'SELECT * FROM setting WHERE type = ?';
		$result = $this->db->query($sql, 'nota_retur')->getResultArray();
		return $result;
	}
	
	public function getSettingNotaTransfer() {
		$sql = 'SELECT * FROM setting WHERE type = ?';
		$result = $this->db->query($sql, 'nota_transfer')->getResultArray();
		return $result;
	}
	
	public function saveSetting() 
	{
		$result = [];
		
		$data_db[] = ['type' => 'invoice', 'param' => 'no_invoice', 'value' => $_POST['no_invoice']];
		$data_db[] = ['type' => 'invoice', 'param' => 'jml_digit', 'value' => $_POST['jml_digit_invoice']];
		$data_db[] = ['type' => 'invoice', 'param' => 'kota_tandatangan', 'value' => $_POST['kota_tandatangan']];
		$data_db[] = ['type' => 'invoice', 'param' => 'tampilkan_riwayat_bayar', 'value' => $_POST['tampilkan_riwayat_bayar']];
		$data_db[] = ['type' => 'invoice', 'param' => 'gunakan_footer', 'value' => $_POST['gunakan_footer']];
		$data_db[] = ['type' => 'invoice', 'param' => 'posisi_footer', 'value' => $_POST['posisi_footer']];
		$data_db[] = ['type' => 'invoice', 'param' => 'footer_text', 'value' => $_POST['footer_text']];
		
		helper('upload_file');
		
		// Logo Login
		$error = false;
		$sql = 'SELECT * FROM setting WHERE type="invoice" AND param="logo"';
		$setting = $this->db->query($sql)->getRowArray();
		
		$logo_invoice_lama = $setting['value'];
		$path = ROOTPATH . 'public/images/';
		if (!empty($_FILES['logo']['name'])) 
		{
			//old file
			if ($logo_invoice_lama) {
				if (file_exists($path . $logo_invoice_lama)) {
					$unlink = delete_file($path . $logo_invoice_lama);
					if (!$unlink) {
						$result['status'] = 'error';
						$result['message'] = 'Gagal menghapus gambar lama';
						$error = true;
					}
				}
			}
			
			$filename = \upload_file($path, $_FILES['logo']);
			$data_db[] = ['type' => 'invoice', 'param' => 'logo', 'value' => $filename];
		} else {
			$data_db[] = ['type' => 'invoice', 'param' => 'logo', 'value' => $logo_invoice_lama];
		}
		
		if ($error) {
			return $result;
		}
		
		$this->db->transStart();
		$this->db->table('setting')->delete(['type' => 'invoice']);
		$this->db->table('setting')->insertBatch($data_db);
		$this->db->transComplete();
		
		if ($this->db->transStatus()) {
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