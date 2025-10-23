<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;

require ROOTPATH . 'app/ThirdParty/PhpSpreadsheet/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
		
use App\Models\LaporanLabaRugiModel;

class Laporan_laba_rugi extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new LaporanLabaRugiModel;	
		$this->data['site_title'] = 'Laporan Laba Rugi';
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/moment/moment.min.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/daterangepicker/daterangepicker.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/daterangepicker/daterangepicker.css');
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');
			
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/laporan-laba-rugi.js');
	}
	
	public function getParent($list, $id, &$data=[]) {
		
		if (key_exists($id, $list)) {
			if ($list[$id]['id_parent']) {
				$data[] = $id;
				$this->getParent($list, $list[$id]['id_parent'], $data);
			} else {
				$data[] = $id;
			}
		} 
		return $data;
		
	}
	
	public function index()
	{
		$result = $this->setData();
		$this->data = array_merge($this->data, $result);
		$this->view('laporan-laba-rugi.php', $this->data);
	}
	
	public function setData() 
	{
		$this->hasPermissionPrefix('read');
		
		if (empty($_GET['daterange'])) {
			$start_date = '01-01-' . date('Y');
			$end_date = date('d-m-Y');
		} else {
			list($start_date, $end_date) = explode(' s.d. ', $_GET['daterange']);
		}
			
		$exp = explode('-', $start_date);
		$start_date_db = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		$exp = explode('-', $end_date);
		$end_date_db = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		$pengeluaran = $this->model->getPengeluaran($start_date_db, $end_date_db);
		
		$list_kategori = $this->model->getKategori();
		
		if (empty($_GET['tgl_tandatangan'])) {
			$result['tgl_tandatangan'] = format_tanggal(date('Y-m-d'));
		} else {
			list($d, $m, $y) = explode('-', $_GET['tgl_tandatangan']);
			$result['tgl_tandatangan'] = format_tanggal($y . '-' . $m . '-' . $d);	
		}
		
		$pegawai = $this->model->getPegawai();
		if (empty($_GET['id_pegawai'])) {
			$result['nama_pegawai'] = $pegawai[0]['nama'];
			$result['nama_jabatan'] = $pegawai[0]['nama_jabatan'];
			$result['nip_pegawai'] = $pegawai[0]['nip_pegawai'];
		} else {
			list($id_pegawai, $id_jabatan) = explode('_', $_GET['id_pegawai']);
			foreach($pegawai as $val) {
				if ($val['id_pegawai'] == $id_pegawai && $val['id_jabatan'] == $id_jabatan) {
					$result['nama_pegawai'] = $val['nama'];
					$result['nama_jabatan'] = $val['nama_jabatan'];
					$result['nip_pegawai'] = $val['nip_pegawai'];
					break;
				}
			}
		}
		
		foreach ($pengeluaran as &$val) {
			$list_parent = $this->getParent($list_kategori, $val['id_pengeluaran_kategori']);
			$val['tree'] = $list_parent;
		}
	
		/* echo '<pre>';
		print_r($pengeluaran); */

		$result['pendapatan'] = $this->model->getPendapatan($start_date_db, $end_date_db);
		$result['pengeluaran'] = $pengeluaran;
		$result['list_kategori'] = $list_kategori;
		$result['start_date'] = $start_date;
		$result['end_date'] = $end_date;
		$result['start_date_db'] = $start_date_db;
		$result['end_date_db'] = $end_date_db;
		$result['pegawai'] = $pegawai;
		$result['identitas'] = $this->model->getIdentitasSekolah();
		return $result;
	}
	
	public function printLaporan() 
	{
		$result = $this->setData();
		$this->data = array_merge($this->data, $result);
		echo view('themes/modern/laporan-laba-rugi-print.php', $this->data);
	}
	
	public function ajaxExportExcel() {
		$data = $this->setData();
		extract($data);

		$excel = new Spreadsheet();
		
		// Set document properties
		$excel->getProperties()->setCreator('Jagowebdev.com')
			->setLastModifiedBy('Jagowebdev.com')
			->setTitle('Laporan Laba Rugi')
			->setSubject('Laporan Laba Rugi')
			->setDescription('Laporan Laba Rugi')
			->setKeywords('laporan laba rugi')
			->setCategory('laporan laba rugi');
		
		$excel->setActiveSheetIndex(0);
		$sheet = $excel->getActiveSheet();
		$sheet->mergeCells('A1:C1');
		$sheet->mergeCells('A2:C2');
		$sheet->mergeCells('A3:C3');
		
		$sheet->getColumnDimension('A')->setWidth(40);
		$sheet->getColumnDimension('B')->setWidth(13);
		$sheet->getColumnDimension('C')->setWidth(13);
		
		$sheet->getStyle('A1:C3')
				->getAlignment()
				->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
				
		$sheet->setCellValue('A1', 'LAPORAN LABA RUGI')
			->setCellValue('A2', $identitas['nama_sekolah'])
			->setCellValue('A3', format_tanggal($start_date_db) . ' s.d. ' . format_tanggal($end_date_db));
		
		$sheet->getStyle('A4:A50')
				->getAlignment()
				->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
				
		$num_row = 5;
		// Pendapatan
		$sheet->setCellValue('A' . $num_row, 'Pendapatan');
		$sheet->getStyle('A' . $num_row . ':' . 'C' . $num_row)->getFont()->setBold(true);
		
		$total_pendapatan = 0;
		foreach ($pendapatan as $val) {
			if (!$val['nama_pendapatan_jenis']) {
				continue;
			}
			$num_row++;
			$sheet->setCellValue('A' . $num_row, $val['nama_pendapatan_jenis']);
			$sheet->setCellValue('C' . $num_row, $val['jumlah_pendapatan']);
			$total_pendapatan += $val['jumlah_pendapatan'];
		}
		$sheet->setCellValue('A' . ++$num_row, 'Total pendapatan');
		$sheet->setCellValue('C' . $num_row, $total_pendapatan);
		$sheet->getStyle('A' . $num_row . ':' . 'C' . $num_row)->getFont()->setBold(true);
		
		$sheet->setCellValue('A' . ++$num_row, 'Pengeluaran');
		$sheet->getStyle('A' . $num_row . ':' . 'C' . $num_row)->getFont()->setBold(true);
		
		$total_pengeluaran = 0;
		$list_pengeluaran = [];
		
		if (empty($_GET['tampilkan_pengeluaran'])) {
			$_GET['tampilkan_pengeluaran'] = 'resume';
		}
		
		if ($_GET['tampilkan_pengeluaran'] == 'resume') 
		{
			foreach ($pengeluaran as $val) 
			{
				$id_kategori = $val['tree'][ count($val['tree']) - 1 ];
				
				$nama_kategori = $list_kategori[$id_kategori]['nama_kategori'];
				$list_pengeluaran[$id_kategori]['nama_kategori'] =  $nama_kategori;
				
				if (!key_exists('total_pengeluaran', $list_pengeluaran[$id_kategori])) {
					$list_pengeluaran[$id_kategori]['total_pengeluaran'] = 0;
				}
				$list_pengeluaran[$id_kategori]['total_pengeluaran'] += $val['total_pengeluaran'];
				
				$total_pengeluaran += $val['total_pengeluaran'];
			}
			
			foreach ($list_pengeluaran as $val) {
				$sheet->setCellValue('A' . ++$num_row, $val['nama_kategori']);
				$sheet->setCellValue('C' . $num_row, $val['total_pengeluaran']);
			}
			
		} else {

			foreach ($pengeluaran as $val) 
			{
				$id_parent = $val['tree'][ count($val['tree']) - 1 ];
				
				$nama_kategori_parent = $list_kategori[$id_parent]['nama_kategori'];
				$list_pengeluaran[$id_parent]['nama_kategori'] =  $nama_kategori_parent;
				$list_pengeluaran[$id_parent]['item'][] =  ['total_pengeluaran' => $val['total_pengeluaran']
															, 'nama_kategori' => $list_kategori[$val['id_pengeluaran_kategori']]['nama_kategori']
															];
				
				if (!key_exists('total_pengeluaran', $list_pengeluaran[$id_parent])) {
					$list_pengeluaran[$id_parent]['total_pengeluaran'] = 0;
				}
				$list_pengeluaran[$id_parent]['total_pengeluaran'] += $val['total_pengeluaran'];
				
				$total_pengeluaran += $val['total_pengeluaran'];
			}
			
			foreach ($list_pengeluaran as $val) 
			{
				$sheet->setCellValue('A' . ++$num_row, $val['nama_kategori']);
				$total = 0;
				foreach ($val['item'] as $item) {
					
					$sheet->setCellValue('A' . ++$num_row, $item['nama_kategori']);
					$sheet->setCellValue('B' . $num_row, $item['total_pengeluaran']);
					$sheet->getStyle('A'.$num_row)->getAlignment()->setIndent(1);
					$total += $item['total_pengeluaran'];
				}
				$sheet->setCellValue('A' . ++$num_row, 'Total ' . $val['nama_kategori']);
				$sheet->getStyle('A'.$num_row)->getAlignment()->setIndent(1);
				$sheet->setCellValue('C' . $num_row, $total);
			}
		}
					
		$laba_rugi = $total_pendapatan - $total_pengeluaran;
		$laba_rugi_text = $laba_rugi >= 0 ? 'Laba' : '(Rugi)';
		
		$sheet->setCellValue('A' . ++$num_row, 'Total Pengeluaran');
		$sheet->setCellValue('C' . $num_row, $total_pengeluaran);
		$sheet->getStyle('A' . $num_row . ':' . 'C' . $num_row)->getFont()->setBold(true);
		
		$sheet->setCellValue('A' . ++$num_row, $laba_rugi_text);
		$sheet->setCellValue('C' . $num_row, $laba_rugi);
		$sheet->getStyle('A' . $num_row . ':' . 'C' . $num_row)->getFont()->setBold(true);
		
		$sheet->getStyle('B')->getNumberFormat()->setFormatCode('#,0');
		$sheet->getStyle('C')->getNumberFormat()->setFormatCode('#,0');
		
		$sheet->getStyle('B' . (++$num_row) . ':B' . ($num_row + 10))
				->getAlignment()
				->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
				
		$sheet->setCellValue('B' . ++$num_row, $identitas['kota_tandatangan'] . ', ' . $tgl_tandatangan);
		$sheet->setCellValue('B' . ++$num_row, $nama_jabatan);
		
		$num_row += 4;
		$sheet->setCellValue('B' . $num_row, $nama_pegawai);
		$sheet->setCellValue('B' . ++$num_row, 'NIP ' . $nip_pegawai);
		
		$sheet->getStyle('B' . $num_row . ':B' . $num_row)
				->getAlignment()
				->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
				
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Laporan Laba Rugi.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');
		
		$writer = new Xlsx($excel);
		$writer->save('php://output');
		
	}
	
	public function ajaxExportPdf() 
	{
		require_once('app/ThirdParty/Tcpdf/tcpdf.php');
		require_once('app/Helpers/util_helper.php');
		
		$result = $this->setData();
		extract($result);
		

		$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

		$pdf->setPageUnit('mm');

		// set document information
		$pdf->SetCreator($identitas['nama_sekolah']);
		$pdf->SetAuthor($identitas['nama_sekolah']);
		$pdf->SetTitle('Laporan Laba Rugi ' . $_GET['daterange']);
		$pdf->SetSubject('Laporan Laba Rugi');

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
		
		$pdf->ln(8);
		$pdf->SetFont ('helvetica', 'B', $font_size + 3, '', 'default', true );
		$pdf->Cell(0, 0, 'Laporan Laba Rugi', 0, 1, 'C', 0, '', 0, false, 'T', 'M' );
		$pdf->Cell(0, 0, $identitas['nama_sekolah'], 0, 1, 'C', 0, '', 0, false, 'T', 'M' );
		$pdf->Cell(0, 0, format_tanggal($start_date_db) . ' s.d. ' . format_tanggal($end_date_db), 0, 1, 'C', 0, '', 0, false, 'T', 'M' );
		
		$pdf->ln(6);
		
		$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );
		$tbl = <<<EOD
		<table border="0" cellspacing="0" cellpadding="1">
			<tbody>
				<tr border="0">
					<td style="width:75%;font-weight:bold">Pendapatan</td>
					<td style="width:8%"></td>
					<td style="width:12%"></td>
					
				</tr>
		EOD;
		
		$total_pendapatan = 0;
		$format_number = 'format_number';
		foreach ($pendapatan as $val) {
			if (!$val['nama_pendapatan_jenis']) {
				continue;
			}
			$tbl .= <<<EOD
					<tr>
						<td style="width:75%;" align="left">$val[nama_pendapatan_jenis]</td>
						<td style="width:12%;"></td>
						<td style="width:13%;" align="right">{$format_number($val['jumlah_pendapatan'], true)}</td>
					</tr>
			EOD;
			$total_pendapatan += $val['jumlah_pendapatan'];
		}
		
		$tbl .= <<<EOD
					<tr>
						<td style="width:75%;font-weight:bold" align="left">Total Pendapatan</td>
						<td style="width:12%;"></td>
						<td style="width:13%;font-weight:bold" align="right">{$format_number($total_pendapatan, true)}</td>
					</tr>
					<tr>
						<td style="width:75%;font-weight:bold" align="left">Pengeluaran</td>
						<td style="width:12%;"></td>
						<td style="width:13%;"></td>
					</tr>
			EOD;
		
		$total_pengeluaran = 0;
		$list_pengeluaran = [];
		
		if (empty($_GET['tampilkan_pengeluaran'])) {
			$_GET['tampilkan_pengeluaran'] = 'resume';
		}
		
		if ($_GET['tampilkan_pengeluaran'] == 'resume') 
		{
			foreach ($pengeluaran as $val) 
			{
				$id_kategori = $val['tree'][ count($val['tree']) - 1 ];
				
				$nama_kategori = $list_kategori[$id_kategori]['nama_kategori'];
				$list_pengeluaran[$id_kategori]['nama_kategori'] =  $nama_kategori;
				
				if (!key_exists('total_pengeluaran', $list_pengeluaran[$id_kategori])) {
					$list_pengeluaran[$id_kategori]['total_pengeluaran'] = 0;
				}
				$list_pengeluaran[$id_kategori]['total_pengeluaran'] += $val['total_pengeluaran'];
				
				$total_pengeluaran += $val['total_pengeluaran'];
			}
			
			foreach ($list_pengeluaran as $val) {
			
				$tbl .= <<<EOD
					<tr>
						<td style="width:75%;">$val[nama_kategori]</td>
						<td style="width:12%;"></td>
						<td style="width:13%;" align="right">{$format_number($val['total_pengeluaran'], true)}</td>
					</tr>
			EOD;
			}
		} else {

			foreach ($pengeluaran as $val) 
			{
				$id_parent = $val['tree'][ count($val['tree']) - 1 ];
				
				$nama_kategori_parent = $list_kategori[$id_parent]['nama_kategori'];
				$list_pengeluaran[$id_parent]['nama_kategori'] =  $nama_kategori_parent;
				$list_pengeluaran[$id_parent]['item'][] =  ['total_pengeluaran' => $val['total_pengeluaran']
															, 'nama_kategori' => $list_kategori[$val['id_pengeluaran_kategori']]['nama_kategori']
															];
				
				if (!key_exists('total_pengeluaran', $list_pengeluaran[$id_parent])) {
					$list_pengeluaran[$id_parent]['total_pengeluaran'] = 0;
				}
				$list_pengeluaran[$id_parent]['total_pengeluaran'] += $val['total_pengeluaran'];
				
				$total_pengeluaran += $val['total_pengeluaran'];
			}
			
			foreach ($list_pengeluaran as $val) 
			{
				$tbl .= <<<EOD
							<tr>
								<td style="width:75%;">$val[nama_kategori]</td>
								<td style="width:12%;"></td>
								<td style="width:13%;"></td>
							</tr>
					EOD;
					
				$total = 0;
				foreach ($val['item'] as $item) {
					$tbl .= <<<EOD
								<tr>
									<td style="width:75%;text-indent:25px;">$item[nama_kategori]</td>
									<td style="width:12%;" align="right">{$format_number($item['total_pengeluaran'], true)}</td>
									<td style="width:13%;"></td>
								</tr>
						EOD;
					$total += $item['total_pengeluaran'];
				}
				
				$tbl .= <<<EOD
							<tr>
								<td style="width:75%;text-indent:25px">Total $val[nama_kategori]</td>
								<td style="width:12%;"></td>
								<td style="width:13%;" align="right">{$format_number($total, true)}</td>
							</tr>
					EOD;
			}
		}
		
		$laba_rugi = $total_pendapatan - $total_pengeluaran;
		$laba_rugi_text = $laba_rugi >= 0 ? 'Laba' : '(Rugi)';
		
		$tbl .= <<<EOD
					<tr>
						<td style="width:75%;font-weight:bold">Total Pengeluaran</td>
						<td style="width:12%;"></td>
						<td style="width:13%;font-weight:bold" align="right">{$format_number($total_pengeluaran, true)}</td>
					</tr>
					<tr>
						<td style="width:75%;font-weight:bold">$laba_rugi_text</td>
						<td style="width:12%;"></td>
						<td style="width:13%;font-weight:bold" align="right">{$format_number($laba_rugi, true)}</td>
					</tr>
			EOD;
			
		$tbl .= '</tbody>
			</table>';
		
		$pdf->writeHTML($tbl, false, false, false, false, '');
		$pdf->ln(8);
		
		$tbl = <<<EOD
					<table>
						<tbody>
							<tr>
								<td style="width:70%"></td>
								<td>$identitas[kota_tandatangan], $tgl_tandatangan</td>
							</tr>
							<tr>
								<td style="width:70%"></td>
								<td>$nama_jabatan</td>
							</tr>
							<tr>
								<td style="width:70%"></td>
								<td><br/><br/><br/><br/>$nama_pegawai</td>
							</tr>
							<tr>
								<td style="width:70%"></td>
								<td>$nip_pegawai</td>
							</tr>
						</tbody>
					</table>
			EOD;
			
						
		$pdf->writeHTML($tbl, false, false, false, false, '');
		
		$filename = 'Laporan Laba Rugi ' . $_GET['daterange'] . '.pdf';
		$pdf->Output($filename, 'D');

	}
		
	/* private function buildKategoriList($arr, $id_parent = '', &$result = [])
	{
		
		foreach ($arr as $key => $val) 
		{
			$result[$val['id_pengeluaran_kategori']] = ['attr' => ['data-parent' => $id_parent, 'data-icon' => $val['icon'], 'data-new' => $val['new']]
													, 'text' => $val['nama_kategori']
												];
			if (key_exists('children', $val))
			{
				$result[$val['id_pengeluaran_kategori']]['attr']['disabled'] = 'disabled';
				$this->buildKategoriList($val['children'], $val['id_pengeluaran_kategori'], $result);
			}
		}
		return $result;
	} */
	
	public function ajaxDeleteData() {

		$result = $this->model->deleteData();
		echo json_encode($result);
	}
	
	public function ajaxDeleteAllKelas() {

		$result = $this->model->deleteAllKelas();
		// $result = true;
		if ($result) {
			$result = ['status' => 'ok', 'message' => 'Data kelas berhasil dihapus'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data kelas gagal dihapus'];
		}
		
		echo json_encode($result);
	}
	
	public function ajaxGetFormData() {
		
		if (isset($_GET['id'])) {
			if ($_GET['id']) {
				$this->data['kelas'] = $this->model->getKelasById($_GET['id']);
				if (!$this->data['kelas'])
					return;
			}
		}
		$list_kelas = [1 => 'Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'];
		$this->data['list_kelas'] = $list_kelas;
		echo view('themes/modern/kelas-form.php', $this->data);
	}
	
	public function ajaxUpdateData() {

		$message = $this->model->saveData();
		echo json_encode($message);
	}
	
	public function getDataDT() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllData( $this->whereOwn() );
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListData( $this->whereOwn() );
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['ignore_urut'] = $no;
			$val['ignore_action'] = '<div class="form-inline btn-action-group">'
										. btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_kelas']]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_kelas']
																	, 'data-delete-title' => 'Hapus data kelas: <strong>' . $val['nama_kelas'] . '</strong>'
																]
													, 'label' => 'Delete'
												]) . 
										
										'</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
