<?php
// require_once(ROOTPATH . 'app/ThirdParty/TclibBarcode/autoload.php');
?>
<html>
<head>
	<title>Print Nota</title>
	<style>
	@page {
		/* size:    A4;*/
		margin: 1cm;
		margin-bottom: 0.5cm;
	}
	
	p {
		margin: 0;
		padding: 0;
	}
	
	.brand-text {
		font-size: 120%;
	}

	body {
		font-size: 14px;
		font-family: arial, helvetica;
		margin: 0;
		padding: 0;
		
	}
	.container {
		margin: auto;
		max-width: 793px;
		position: relative;
		margin-top: 0px;
		padding-top: 0px;
		max-width: 21cm;
	}
	.fw-bold {
		font-weight: bold;
	}
	.identitas-container {
		display: flex;
		width: 100%;
	}
	.identitas-container .detail {
		margin-left: 10px;
	}
	.detail {
		display: flex;
		flex-direction: column;
		justify-content: center;
	}
	.detail p {
		margin: 0 7px;
	}
	table {
		font-size: 14px;
	}
	
	.barcode-text {
		font-size: 20px;
		text-align: center;
	}
	header {
		text-align: center;
		margin: auto;
	}
	footer {
		width: 100%;
		text-align: center;
	}
	hr {
		margin: 10px 0;
		padding: 0;
		height: 1px;
		border: 0;
		border-bottom: 1px solid rgb(49,49,49);
		width: 100%;
		
	}
	.nama-item {
		font-weight: bold;
	}
	
	.harga-item {
		display: flex;
		justify-content: flex-end;
		margin: 0;
		padding: 0;
	}
	
	table {
		border-collapse: collapse;
	}
	.no-border td {
		border: 0;
	}
	
	.text-right {
		text-align: right;
	}
	
	.nama-perusahaan {
		font-weight: bold;
		font-size: 120%;
		margin-bottom: 3px;
	}
	
	.text-bold {
		font-weight: bold;
	}
	
	.logo-container {
		display: flex;
		justify-content: space-between;
	}
	.invoice-text {
		text-align: center;
		margin: 30px 0 25px 0;
	}
	
	td {
		vertical-align: top;
	}
	
	.border th,
	.border td {
		border-top: 1px solid #CECECE;
	}
	.border tr:last-child td {
		border-bottom: 1px solid #CECECE;
	}
	.padding th,
	.padding td {
		padding: 8px 12px;
	}
	.d-flex-between {
		display: flex;
		justify-content: space-between;
	}
	.text-end {
		text-align: right;
	}
	.badge {
		display: flex;
		justify-content: flex-end;
		margin-bottom: 25px;
		margin-right: -30px;
		 display: flex;
    /* justify-content: flex-end; */
		margin-bottom: 0;
		margin-right: 0;
		/* margin-top: 500px; */
		position: absolute;
		right: 0;
		top:0;
	}
	.badge-text {
		padding: 10px 20px;
		
	}
	.bg-danger {
		background: red;
	}
	.bg-success {
		background: #45e445;
		color: #0e861e;
	}
	</style>
	
</head>
<body onload="window.print()">
	<?php
		/* $pelanggan = $penjualan['nama_customer'] ? $penjualan['nama_customer'] : 'Umum';
		$barcode = new \Com\Tecnick\Barcode\Barcode();
		$bobj = $barcode->getBarcodeObj('C128', $order['order']['no_invoice'], -2, 55, 'black', array(0, 0, 0, 0)); */

	?>
	<div class="container">
		<div class="logo-container">
			<div class="identitas-container">
				<img style="max-width: 85px;" src="<?=base_url()?>/public/images/sekolah/<?=$identitas['logo']?>"/>
				<div class="detail">
					<p class="brand-text"><?=$identitas['nama_sekolah']?></p>
					<p><?=$identitas['alamat_sekolah']?></p>
					<p><?=$identitas['nama_kelurahan'] . ', ' . $identitas['nama_kecamatan'] . ', ' . $identitas['nama_kabupaten'] . ', ' .$identitas['nama_propinsi']?></p>
					<p>Telp: <?=$identitas['no_telp']?></p>
				</div>
			</div>
		</div>
		<div class="invoice-text">
			<h2 style="font-size:25px">BUKTI PEMBAYARAN</h1>	
		</div>
		<table class="no-border" style="width:100%;margin-bottom:20px" cellspacing="0" cellpadding="1">
			<tr>
				<td style="width:12%">Nama</td>
				<td style="width:1%">:</td>
				<td style="width:37%"><?=$bayar['nama']?></td>
				<td style="width:12%">No. Invoice</td>
				<td style="width:1%">:</td>
				<td style="width:37%"><?=$bayar['no_invoice']?></td>
			</tr>
			<tr border="0">
				<td style="width:12%">NIS</td>
				<td style="width:1%">:</td>
				<td style="width:37%"><?=$bayar['nis']?></td>
				<td style="width:12%">Tgl. Invoice</td>
				<td style="width:1%">:</td>
				<td style="width:37%"><?=$bayar['tgl_invoice']?></td>
			</tr>
		</table>
		<table cellspacing="0" cellpadding="0" style="width:100%" class="border padding">
			<thead>
				<tr>
					<th style="width:5%">No</td>
					<th style="width:65%;text-align:left">Nama Pembayaran</td>
					<th style="width:15%" class="text-end">Jumlah Bayar</td>
					<th style="width:15%" class="text-end">Kurang</td>
				</tr>
			</thead>
			<tbody>
			<?php
				$no = 1;
				$total_bayar = $total_kurang = 0;
				$nama_bulan = nama_bulan();
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
					
					echo '
					<tr>
						<td>' . $no . '</td>
						<td>' . $nama_pembayaran . '</td>
						<td class="text-end">' . format_number($val['nilai_bayar']) . '</td>
						<td class="text-end">' . format_number($kurang) . '</td>
					</tr>';
					$no++;
				}
				
				echo '
					<tr>
						<td></td>
						<td class="fw-bold">TOTAL</td>
						<td class="text-end">' . format_number($total_bayar) . '</td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td class="fw-bold">' . $bayar['nama_jenis_bayar'] . '</td>
						<td class="text-end fw-bold">' . format_number($bayar['total_pembayaran']) . '</th>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td class="fw-bold">Kembali</td>
						<td class="text-end fw-bold">' . format_number($bayar['kembali']) . '</td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td class="fw-bold">Total kurang bayar</td>
						<td></td>
						<td class="text-end fw-bold">' . format_number($total_kurang) . '</td>
					</tr>
				</tbody>
			</table>';
			if ($setting['tampilkan_riwayat_bayar'] == 'Y') {
				if ($bayar['riwayat_bayar']) {
					
					echo '
					<div class="d-flex-between">
						<h3>Riwayat Pembayaran</h3>
					</div>
					<table  style="width:100%" class="border padding" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th style="width:5%">No</th>
								<th style="width:45%">Nama Pembayaran</th>
								<th style="width:15%">Tagihan</th>
								<th style="width:15%">Subsidi</th>
								<th style="width:15%">Jumlah Bayar</th>
								<th style="width:20%">Tanggal Bayar</th>
							</tr>
						</thead>
						<tbody>';
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

							// ambil nilai potongan/subsidi dari kolom potongan (siswa_tagihan)
							$subsidi = isset($val['potongan']) ? $val['potongan'] : 0;
							
							$tagihan = $num_jenis_bayar == 0 ? $val['nilai_tagihan'] : $val['nilai_tagihan'] - $total_bayar;
							$total_bayar += $val['nilai_bayar'];
							$nomor = $num_jenis_bayar == 0 ? $no : '';
							echo '
								<tr>
									<td>' . $nomor . '</td>
									<td>' . $nama_pembayaran . '</td>
									<td>' . format_number($tagihan) . '</td>
									<td>' . format_number($subsidi) . '</td>
									<td>' . format_number($val['nilai_bayar']) . '</td>
									<td>' . format_date($val['tgl_bayar']) . '</td>
								</tr>';
							$num_jenis_bayar++;
						}
						
						if ($val['nilai_tagihan'] > $total_bayar) {
						$tagihan = $val['nilai_tagihan'] - $total_bayar;
						echo '
								<tr>
									<td></td>
									<td>' . $nama_pembayaran . '</td>
									<td>' . format_number($tagihan) . '</td>
									<td>-</td>
									<td>-</td>
								</tr>';
						}
						$no++;
					}
					
					echo '</tbody>
					</table>';
				}
			}
			?>
			
			<div style="margin-left: 70%; margin-top:20px">
				<p><?=$setting['kota_tandatangan'] . ', ' . format_date($bayar['tgl_invoice'])?></p>
				<p style="margin-top:7px">Petugas</p>
				<p style="margin-top:50px"><?=$pegawai['nama']?></p>
			</div>
	</div>
	<?php
	if ($setting['gunakan_footer'] == 'Y') {
		
		if ($setting['posisi_footer'] == 'setelah_isi_invoice') {
			$style = 'border-top:1px solid #CECECE;padding-top:10px;text-align:left;margin-top:25px';
		} else {
			$style = 'position:fixed; bottom:0; border-top:1px solid #CECECE;padding-top:10px;text-align:left';
		}
		
		echo '<footer style="' . $style . '">
				<em>' . $setting['footer_text'] . '</em>
			</footer>';
	}
	?>
</body>
<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', () => {
		setTimeout(function() {
			window.close();
		}, 7000);
		
	});		
</script>
</html>