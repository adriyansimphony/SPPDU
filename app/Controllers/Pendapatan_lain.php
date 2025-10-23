<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\PendapatanLainModel;

class Pendapatan_lain extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PendapatanLainModel;	
		$this->data['site_title'] = 'Pendapatan Lain';
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/themes/material_blue.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-loader.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-fapicker.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pendapatan-lain.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		$this->data['jml_data'] = $this->model->getJmlDataPendapatanLain();
		$this->view('pendapatan-lain-result.php', $this->data);
	}
	
	public function ajaxGetFormData() {
		
		if (!empty($_GET['id'])) {
			$this->data['bayar'] = $this->model->getPendapatanLainById($_GET['id']);
			if (!$this->data['bayar']) {
				echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
				exit;
			}
		}
		$result = $this->model->getAllPendapatanJenis();
		$pendapatan_jenis =[];
		foreach ($result as $val) {
			$pendapatan_jenis[$val['id_pendapatan_jenis']] = $val['nama_pendapatan_jenis'];
		}
		
		$result = $this->model->getAllMetodePembayaran();
		$metode_pembayaran =[];
		foreach ($result as $val) {
			$metode_pembayaran[$val['id_jenis_bayar']] = $val['nama_jenis_bayar'];
		}
		
		$this->data['pendapatan_jenis'] = $pendapatan_jenis;
		$this->data['metode_pembayaran'] = $metode_pembayaran;
		$this->data['action'] = 'add';
		echo view('themes/modern/pendapatan-lain-form-ajax.php', $this->data);
	}
	
	/* public function add() {
		$this->data['action'] = 'add';
		$result = $this->model->getAllPembayaranJenis();
		$jenis_pembayaran =[];
		foreach ($result as $val) {
			$jenis_pembayaran[$val['id_pendapatan_jenis']] = $val['nama_pendapatan_jenis'];
		}
		$this->data['jenis_pembayaran'] = $jenis_pembayaran;
		$this->data['title'] = 'Tambah Pembayaran';
		
		$this->view('pendapatan-siswa-form.php', $this->data);
	} */
	
	public function invoicePdf() 
	{
		require_once('app/ThirdParty/Tcpdf/tcpdf.php');
		require_once('app/Helpers/util_helper.php');
		
		$bayar = $this->model->getSiswaBayarById();
		if (!$bayar) {
			$this->errorDataNotFound();
			return false;
		}
		
		$identitas = $this->model->getIdentitas();
		$setting = $this->model->getSetting('invoice');
		
		$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

		$pdf->setPageUnit('mm');

		// set document information
		$pdf->SetCreator($identitas['nama_sekolah']);
		$pdf->SetAuthor($identitas['nama_sekolah']);
		$pdf->SetTitle('Invoice #' .$bayar['no_invoice']);
		$pdf->SetSubject('Invoice Pembayaran');

		$margin_left = 15; //mm
		$margin_right = 15; //mm
		$margin_top = 7; //mm
		$font_size = 10;

		$pdf->SetAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		$pdf->SetProtection(array('modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'), '', null, 0, null);

		// set default font subsetting mode
		$pdf->setFontSubsetting(true);

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('dejavusans', '', $font_size + 4, '', true);
		$pdf->SetMargins($margin_left, $margin_top, $margin_right, false);

		$pdf->AddPage();

		$pdf->SetTextColor(50,50,50);
		// $pdf->Image(ROOTPATH . 'public/images/' . $setting['logo'], 10, 20, 0, 0, 'JPG', 'https://jagowebdev.com');
		// File, X, Y, W, H
		$image_width = 23;
		$pdf->Image(ROOTPATH . 'public/images/sekolah/' . $identitas['logo'], $margin_left, $margin_top + 3, $image_width, 0, 'PNG', '');

		$image_dim = getimagesize(ROOTPATH . 'public/images/sekolah/' . $identitas['logo']);
		// $x = $margin_left + ($image_dim[0] * 0.2645833333) + 5;
		$x = $margin_left + $image_width + 3;
		// $x = 20;
		$pdf->SetXY($x, $margin_top + 3);
		$pdf->Cell(0, 9, $identitas['nama_sekolah'], 0, 1, 'L', 0, '', 0, false, 'T', 'M' );

		//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')
		$pdf->SetX($x);
		$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );
		$pdf->Cell(0, 0, $identitas['alamat_sekolah'], 0, 1, 'L', 0, '', 0, false, 'T', 'M' );
		$pdf->SetX($x);
		$pdf->Cell(0, 0, $identitas['nama_kelurahan'] . ', ' . $identitas['nama_kecamatan'] . ', ' . $identitas['nama_kabupaten'] . ', ' .$identitas['nama_propinsi'], 0, 1, 'L', 0, '', 0, false, 'T', 'M' );
		$pdf->SetX($x);
		$pdf->Cell(0, 0, 'Telp: ' . $identitas['no_telp'] , 0, 1, 'L', 0, '', 0, false, 'T', 'M' );

		$barcode_style = array(
			'position' => 'R',
			'align' => 'C',
			'stretch' => false,
			'fitwidth' => true,
			'cellfitalign' => '',
			'border' => false,
			'hpadding' => 'auto',
			'vpadding' => 'auto',
			'fgcolor' => array(0,0,0),
			'bgcolor' => false, //array(255,255,255),
			'text' => true,
			'font' => 'helvetica',
			'fontsize' => $font_size,
			'stretchtext' => false
		);

		$pdf->SetY($margin_top + 25);
		// $pdf->write1DBarcode($bayar['no_invoice'], 'C128', '', '', '', 20, 0.4, $barcode_style, 'N');

		$pdf->ln(6);
		$pdf->SetFont ('helvetica', 'B', $font_size + 5, '', 'default', true );
		$pdf->Cell(0, 0, 'BUKTI PEMBAYARAN', 0, 1, 'C', 0, '', 0, false, 'T', 'M' );

		$pdf->ln(6);
		$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );
		
		$tbl = <<<EOD
		<table border="0" cellspacing="0" cellpadding="1">
			<tbody>
				<tr border="0">
					<td style="width:12%">Nama</td>
					<td style="width:1%">:</td>
					<td style="width:37%">$bayar[nama]</td>
					<td style="width:12%">No. Invoice</td>
					<td style="width:1%">:</td>
					<td style="width:37%">$bayar[no_invoice]</td>
				</tr>
				<tr border="0">
					<td style="width:12%">NIS</td>
					<td style="width:1%">:</td>
					<td style="width:37%">$bayar[nis]</td>
					<td style="width:12%">Tgl. Invoice</td>
					<td style="width:1%">:</td>
					<td style="width:37%">$bayar[tgl_invoice]</td>
				</tr>
			</tbody>
			</table>
		EOD;
		
		$pdf->writeHTML($tbl, false, false, false, false, '');
		
		$y =  $pdf->GetY();
		$pdf->SetY($y);
	
		$pdf->ln(5);
		$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );
		$border_color = '#CECECE';
		// $background_color = '#efeff0';
		$background_color = '#FFFFFF';
		
	/* 	$tbl = <<<EOD
		<table border="1" cellspacing="0" cellpadding="5">
			<thead>
				<tr border="1" style="background-color:$background_color">
					<th style="width:5%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">No</th>
					<th style="width:30%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">Nama Pembayaran</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Jumlah Tagihan</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Jumlah Bayar</th>
					<th style="width:45%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Kurang</th>
				</tr>
			</thead>
			<tbody>
		EOD; */
		
		$tbl = <<<EOD
		<table border="0" cellspacing="0" cellpadding="5">
			<thead>
				<tr border="1" style="background-color:$background_color;  font-weight:bold">
					<th style="width:5%;border-top-color:$border_color;border-bottom-color:$border_color" align="center"><b>No</b></th>
					<th style="width:65%;border-top-color:$border_color;border-bottom-color:$border_color" align="left"><strong>Nama Pembayaran</strong></th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color; font-weight:bold" align="right">Jumlah Bayar</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color" align="right">Kurang</th>
				</tr>
			</thead>
			<tbody>
		EOD;

			$no = 1;
			$format_number = 'format_number';
			$format_date = 'format_date';
			// echo '<pre>';
			// print_r($bayar); die;
			$nama_bulan = nama_bulan();
			$total_bayar = 0;
			$total_kurang = 0;
			foreach ($bayar['detail'] as $val) {
				$total_bayar += $val['nilai_bayar'];
				$kurang = 0;
				if ($val['nilai_tagihan']) {
					if ($val['total_pembayaran'] < $val['nilai_tagihan']) {
						$kurang = $val['nilai_tagihan'] - $val['total_pembayaran'];
					}
				}
				$total_kurang += $kurang;
				$bulan = $val['periode_bulan'] ? ' ' . $nama_bulan[$val['periode_bulan']] . ' ' . $val['periode_tahun'] : '';
				$tahun_ajaran = $val['tahun_ajaran'] ? ' ' . $val['tahun_ajaran'] : '';
				$nama_pembayaran = $val['nama_pendapatan_jenis'] . $bulan . $tahun_ajaran;
				$tbl .= <<<EOD
					<tr>
						<td style="width:5%;border-bottom-color:$border_color" align="center">$no</td>
						<td style="width:65%;border-bottom-color:$border_color">$nama_pembayaran</td>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color" align="right">{$format_number($val['nilai_bayar'], true)}</th>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color" align="right">{$format_number($kurang)}</th>
					</tr>
		EOD;
		
		/* $tbl .= <<<EOD
					<tr>
						<td style="width:5%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">$no</td>
						<td style="width:30%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">$val[nama_pendapatan_jenis]</td>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['nilai_tagihan'], true)}</th>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['nilai_bayar'])}</th>
						<th style="width:35%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($kurang)}</th>
					</tr> */

			$no++;
			}
		
		$tbl .= <<<EOD
					<tr>
						<td style="width:5%;border-bottom-color:$border_color" align="center"></td>
						<td style="width:65%;border-bottom-color:$border_color; font-weight:bold">TOTAL</td>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color; font-weight:bold" align="right">{$format_number($total_bayar, true)}</th>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;font-weight:bold" align="right"></th>
					</tr>
					<tr>
						<td style="width:5%;border-bottom-color:$border_color" align="center"></td>
						<td style="width:65%;border-bottom-color:$border_color; font-weight:bold">$bayar[nama_jenis_bayar]</td>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color; font-weight:bold" align="right">{$format_number($bayar['total_pembayaran'], true)}</th>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color" align="right"></th>
					</tr>
					<tr>
						<td style="width:5%;border-bottom-color:$border_color" align="center"></td>
						<td style="width:65%;border-bottom-color:$border_color; font-weight:bold">Kembali</td>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color; font-weight:bold" align="right">{$format_number($bayar['kembali'], true)}</th>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;font-weight:bold" align="right"></th>
					</tr>
					<tr>
						<td style="width:5%;border-bottom-color:$border_color" align="center"></td>
						<td style="width:65%;border-bottom-color:$border_color; font-weight:bold">Total kurang bayar</td>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color; font-weight:bold" align="right"></th>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;font-weight:bold" align="right">{$format_number($total_kurang)}</th>
					</tr>
		EOD;
		
		$tbl .= '</tbody>
			</table>';

		$pdf->writeHTML($tbl, false, false, false, false, '');
		$pdf->ln(5);
		
		if ($setting['tampilkan_riwayat_bayar'] == 'Y') {
			if ($bayar['riwayat_bayar']) {
				$pdf->SetFont ('helvetica', 'B', $font_size, '', '', true );
				$pdf->Cell(0, 0, 'Riwayat Pembayaran' , 0, 1);
				$pdf->SetFont ('helvetica', '', $font_size, '', '', true );
				$pdf->ln(5);
				$tbl = <<<EOD
				<table border="0" cellspacing="0" cellpadding="5">
					<thead>
						<tr border="1" style="background-color:$background_color;  font-weight:bold">
							<th style="width:5%;border-top-color:$border_color;border-bottom-color:$border_color" align="center"><b>No</b></th>
							<th style="width:45%;border-top-color:$border_color;border-bottom-color:$border_color" align="left"><strong>Nama Pembayaran</strong></th>
							<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color; font-weight:bold" align="right">Tagihan</th>
							<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color; font-weight:bold" align="right">Jumlah Bayar</th>
							<th style="width:20%;border-top-color:$border_color;border-bottom-color:$border_color" align="right">Tanggal Bayar</th>
						</tr>
					</thead>
					<tbody>
				EOD;
				$no =1;
				foreach ($bayar['riwayat_bayar'] as $kode => $arr)			
				{
					$num_jenis_bayar = 0;
					$total_bayar = 0;
					foreach ($arr as $val) 
					{
						$bulan = $val['periode_bulan'] ? ' ' . $nama_bulan[$val['periode_bulan']] . ' ' . $val['periode_tahun'] : '';
						$tahun_ajaran = $val['tahun_ajaran'] ? ' ' . $val['tahun_ajaran'] : '';
						$nama_pembayaran = $val['nama_pendapatan_jenis'] . $bulan . $tahun_ajaran;
						
						$tagihan = $num_jenis_bayar == 0 ? $val['nilai_tagihan'] : $val['nilai_tagihan'] - $total_bayar;
						$total_bayar += $val['nilai_bayar'];
						$nomor = $num_jenis_bayar == 0 ? $no : '';
						$tbl .= <<<EOD
							<tr>
								<td style="width:5%;border-bottom-color:$border_color" align="center">$nomor</td>
								<td style="width:45%;border-bottom-color:$border_color">$nama_pembayaran</td>
								<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color" align="right">{$format_number($tagihan, true)}</th>
								<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color" align="right">{$format_number($val['nilai_bayar'])}</th>
								<th style="width:20%;border-top-color:$border_color;border-bottom-color:$border_color" align="right">{$format_date($val['tgl_bayar'])}</th>
							</tr>
				EOD;
						$num_jenis_bayar++;
					}
					
					if ($val['nilai_tagihan'] > $total_bayar) {
					$tagihan = $val['nilai_tagihan'] - $total_bayar;
					$tbl .= <<<EOD
							<tr>
								<td style="width:5%;border-bottom-color:$border_color" align="center"></td>
								<td style="width:45%;border-bottom-color:$border_color">$nama_pembayaran</td>
								<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color" align="right">{$format_number($tagihan, true)}</th>
								<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color" align="right">-</th>
								<th style="width:20%;border-top-color:$border_color;border-bottom-color:$border_color" align="right">-</th>
							</tr>
				EOD;
					}
					$no++;
				}
				
				$tbl .= '</tbody>
				</table>';
				$pdf->writeHTML($tbl, false, false, false, false, '');
			}
		}
		
		
		$pdf->ln(1);
		$pdf->SetFont ('helvetica', '', $font_size, '', '', true );
		$pdf->SetX(120);
		$pdf->Cell(0, 0, $setting['kota_tandatangan'] . ', ' . format_date($bayar['tgl_invoice']), 0, 1, 'L');
		
		$pegawai = $this->model->getPegawaiById($bayar['id_pegawai_input']);
		$pdf->ln(1);
		$pdf->SetX(120);
		$pdf->Cell(0, 0, 'Petugas,', 0, 1, 'L');
		$pdf->ln(13);
		$pdf->SetX(120);
		$pdf->Cell(0, 0, $pegawai['nama'], 0, 1, 'L');
		
		// $pdf->SetY(-20);
		if ($setting['gunakan_footer']) {
			
			if ($setting['posisi_footer'] == 'paling_bawah') {
				$pdf->SetY(-20);
			} else {
				$pdf->ln(6);
			}
			
			// $pdf->writeHTML('<hr style="background-color:#FFFFFF; border-bottom-color:#CCCCCC;height:0"/>', false, false, false, false, '');
			$pdf->writeHTML('<div style="background-color:#FFFFFF; border-bottom-color:#ababab;height:0"></div>', false, false, false, false, '');
			$pdf->ln(2);
			$pdf->SetFont ('helvetica', 'I', $font_size, '', '', true );
			$pdf->SetTextColor(50,50,50);
			$pdf->SetTextColor(100,100,100);
			$pdf->Cell(0, 0, $setting['footer_text'], 0, 1, 'L');
		}

		$filename = 'Invoice-' . str_replace(['/', '\\'], '_', $bayar['no_invoice']) . '.pdf';
		$filepath_invoice = ROOTPATH . 'public/tmp/' . $filename;
		
		if (!empty($_GET['email'])) 
		{	
			$filepath = ROOTPATH . 'public/tmp/invoice_'. time() . '.pdf';
			$pdf->Output($filepath, 'F');
			
			if (@$_GET['email']) {
				$email = $_GET['email'];
			} else {
				$email = $order['customer']['email'];
			}
			$email_config = new \Config\EmailConfig;
			$email_data = array('from_email' => $email_config->from
							, 'from_title' => 'Jagowebdev.com'
							, 'to_email' => $email
							, 'to_name' => $bayar['customer']['nama_customer']
							, 'email_subject' => 'Invoice: ' . $order['order']['no_invoice']
							, 'email_content' => '<h2>Hi, ' . $order['customer']['nama_customer'] . '</h2><p>Berikut terlampir invoice pembelian atas nama ' . $order['customer']['nama_customer'] . '.</p><p>Anda dapat mengunduhnya pada bagian Attachment.<br/><br/><p>Salam</p>'
							, 'attachment' => ['path' => $filepath, 'name' => $filename]
			);
			
			require_once('app/Libraries/SendEmail.php');
			
			$emaillib = new \App\Libraries\SendEmail;
			$emaillib->init();
			$send_email =  $emaillib->send($email_data);

			unlink($filepath);
			if ($send_email['status'] == 'ok') {
				$message['status'] = 'ok';
				$message['message'] = 'Invoice berhasil dikirim ke alamat email: ' . $email;
			} else {
				$message['status'] = 'error';
				$message['message'] = 'Invoice gagal dikirim ke alamat email: ' . $email . '<br/>Error: ' . $send_email['message'];
			}
			
			echo json_encode($message);
			exit();
		}
		
		if (@$_GET['ajax'] == 'true') {
			$pdf->Output($filepath_invoice, 'F');
			$content = file_get_contents($filepath_invoice);
			echo $content;
			delete_file($filepath_invoice);
		} else {
			$pdf->Output($filename, 'D');
		}
		exit;
	}
	
	public function printInvoice() 
	{
		$this->data['identitas'] = $this->model->getIdentitas();
		$this->data['bayar'] = $this->model->getPendapatanLainById();
		if (!$this->data['bayar']) {
			$this->errorDataNotFound();
			return false;
		}
		
		$setting = $this->model->getSetting('invoice');
		$this->data['setting'] = $setting;
		echo view('themes/modern/pendapatan-lain-print-invoice.php', $this->data);
	}
	
	public function generateExcel($output) 
	{
		$filepath = $this->model->writeExcel();
		$filename = 'Daftar Siswa.xlsx';
		
		switch ($output) {
			case 'raw':
				$content = file_get_contents($filepath);
				echo $content;
				delete_file($filepath);
				break;
			case 'file':
				return $filepath;
				break;
			default:
				header('Content-disposition: attachment; filename="'. $filename .'"');
				header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
				header('Content-Transfer-Encoding: binary');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');  
				$content = file_get_contents($filepath);
				delete_file($filepath);
				echo $content;
		}
		exit;
	}
	
	public function ajaxExportExcel() 
	{
		$output = '';
		if (@$_GET['ajax'] == 'true') {
			$output = 'raw';
		}
		$this->generateExcel($output); 
	}
	
	/* public function ajaxGetFormAdd() {
		
		$result = $this->model->getAllGroupKelas();
		$group_kelas = [];
		foreach ($result as $val) {
			$group_kelas[$val['group_kelas']] = 'Kelas ' . $val['group_kelas'];
		}
				
		$this->data['group_kelas'] = $group_kelas;
		$this->data['siswa'] = $this->model->getSiswaByGroupKelas(1);
		echo view('themes/modern/spp-siswa-form-add.php', $this->data);
	} */
	
	/* public function ajaxGetSiswaByGroupKelas() {
		$result = $this->model->getSiswaByGroupKelas($_GET['id']);
		echo json_encode($result);
	} */
	
	/* public function ajaxGetFormEdit() {
		$spp = $this->model->getSppSiswaById();
		if (!$spp) {
			echo '<div class="alert alert-danger">Error: Data tidak ditemukan</div>';
			exit;
		}
		$this->data['spp'] = $spp;
		echo view('themes/modern/spp-siswa-form-edit.php', $this->data);
		
	} */
	
	public function ajaxSaveData() {
		$message = $this->model->saveData();
		echo json_encode($message);
	}
	
	public function ajaxSaveDataEdit() {
		$message = $this->model->saveDataEdit();
		echo json_encode($message);
	}
	
	public function ajaxDeleteData() {
		$result = $this->model->deleteData();
		
		if ($result) {
			$message = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			echo json_encode($message);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Data gagal dihapus']);
		}
	}
	
	public function ajaxDeleteAllData() {
		$result = $this->model->deleteAllData();
		echo json_encode($result);
	}
	
	public function getListJenisPembayaran() {
		echo view('themes/modern/pendapatan-lain-list-jenis-pembayaran.php', $this->data);
	}
	
	public function getDataDTJenisPembayaran() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataPembayaranJenis();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListDataPembayaranJenis();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$nama_bulan = nama_bulan();
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['ignore_urut'] = $no;			
			$val['ignore_pilih'] = '<div>' .
									btn_label(
												['icon' => 'fas fa-plus'
													, 'attr' => ['class' => 'btn btn-success btn-pilih-pembayaran btn-xs me-1']
													, 'label' => 'Pilih'
												])
									. '
									<textarea style="display:none" name="detail_jenis_pendapatan[]">' . json_encode($val) . '</textarea>
									</div>';
			
			$val['nama_pendapatan_jenis'] = '<div style="min-width:200px">' . $val['nama_pendapatan_jenis'] . '</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
	public function getListPembayar() {
		echo view('themes/modern/pembayar-list-popup.php', $this->data);
	}
	
	public function getDataDTPembayar() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataPembayar();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListDataPembayar();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$nama_bulan = nama_bulan();
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['ignore_urut'] = $no;
			$val['ignore_pilih'] = '<div>' .
									btn_label(
												['icon' => 'fas fa-plus'
													, 'attr' => ['class' => 'btn btn-success btn-pilih-pembayar btn-xs me-1'
																	, 'data-id' => $val['id_pembayar']
																]
													, 'label' => 'Pilih'
												])
									. '
									<span style="display:none"> ' . json_encode($val) . '</span>
									</div>';
			
			$val['nama'] = '<div style="min-width:200px">' . $val['nama_pembayar'] . '</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
	public function getDataDT() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllData();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListData();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$nama_bulan = nama_bulan();
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['ignore_urut'] = $no;
			$val['ignore_action'] = '<div class="form-inline btn-action-group">';
				if (has_permission('update_all')) {	
					$val['ignore_action'] .= btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit-pendapatan-lain btn-xs me-1'
																	, 'data-id' => $val['id_pendapatan']
																	
																]
												]);
				}
				
				if (has_permission('delete_all')) {	
					$val['ignore_action'] .= btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete-pendapatan-lain btn-xs'
																	, 'data-id' => $val['id_pendapatan']
																	
																	, 'data-delete-title' => 'Hapus data pendapatan dari <strong>' . $val['nama_pembayar'] .  '</strong> dengan nilai ' . format_number($val['nilai_bayar']) . ' ?'
																]
												]);
				}
										
										'</div>';
			
			$val['nama_pembayar'] = '<div style="min-width:200px">' . $val['nama_pembayar'] . '</div>';
			$val['nilai_bayar'] = '<div class="text-end">' . format_number($val['nilai_bayar']) . '</div>';
			$val['tgl_bayar'] = '<div class="text-end text-nowrap">' . format_date($val['tgl_bayar']) . '</div>';
			// $val['nilai_spp'] = '<div class="text-end">' . format_number($val['nilai_spp']) . '</div>';
			$exp_jenis = explode(',', $val['nama_pendapatan_jenis']);
			$list_pembayaran = [];
			$nama_bulan = nama_bulan();
			if ($exp_jenis && count($exp_jenis) > 1) {
				foreach ($exp_jenis as $index => $item) {
					$list_pembayaran[] = '<div class="text-nowrap">' . $item . '</div>';
				}
				if (count($list_pembayaran) == 1){
					$val['nama_pendapatan_jenis'] = $list_pembayaran[0];
				} else {
					$val['nama_pendapatan_jenis'] = '<ul class="list-circle"><li>' . join('</li><li>', $list_pembayaran) . '</li></ul>';
				}
			}
			
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
