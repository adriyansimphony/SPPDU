<html>
<head>
	<title>Print Nota</title>
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>/public/vendors/bootstrap/css/bootstrap.min.css?r=<?=time()?>"/>
	<style>
	@page {
		size:    A4;
	}
	
	tr {
		border-top: hidden;
		border-bottom: hidden;
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
	}
	.container {
		margin: auto;
		max-width: 793px;
		position: relative;
		margin-top: 10px;
		padding-top: 10px;
	}
	.identitas-container {
		display: flex;
		width: 100%;
		line-height: 140%;
	}
	.identitas-container img{
		max-height: 96px;
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
		border: 1px solid #CCCCCC;
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
	<div class="container">
		
		<div class="logo-container">
			<div class="identitas-container">
				<img src="<?=base_url()?>/public/images/sekolah/<?=$identitas['logo']?>"/>
				<div class="detail">
					<p class="brand-text"><?=$identitas['nama_sekolah']?></p>
					<p><?=$identitas['alamat_sekolah']?></p>
					<p><?=$identitas['nama_kelurahan'] . ', ' . $identitas['nama_kecamatan']?></p>
					<p><?=$identitas['nama_kabupaten'] . ', ' . $identitas['nama_propinsi']?></p>
				</div>
			</div>
		</div>

		<table cellspacing="0" cellpadding="0" style="width:100%" class="table">
			<tr>
				<td colspan="3">
					<div class="text-center mt-3 mb-4">
						<h6 class="mb-0">Laporan Kas</h6>
						<h6 class="mb-0"><?=$identitas['nama_sekolah']?></h6>
						<h6>Periode <?=format_tanggal($start_date_db)?> s.d. <?=format_tanggal($end_date_db)?></h6>
					</div>
				
				</td>
			</tr>
			<tr>	
				<td colspan="2" class="fw-bold">Saldo kas <?=format_tanggal($start_date_db)?></td>
				<td class="fw-bold text-end"><?=format_number($kas_awal)?></td>
			</tr>
			<?php
			$total_penyesuaian_kas = 0;
			if ($penyesuaian_kas) {
				echo '<tr>	
						<td colspan="3" class="fw-bold">Penyesuaian Kas</td>
					</tr>';
				$total_penyesuaian_kas = $penyesuaian_kas['penambahan_kas'] + $penyesuaian_kas['pengurangan_kas'];
			}
			
			if (@$penyesuaian_kas['penambahan_kas']) {
				echo '<tr>
						<td colspan="2">Penambahan Kas</td>
						<td class="text-end">' . format_number($penyesuaian_kas['penambahan_kas']) . '</td>
					</tr>';
			}
			
			if (@$penyesuaian_kas['pengurangan_kas']) {
				echo '<tr>
						<td colspan="2">Pengurangan Kas</td>
						<td class="text-end">' . format_number($penyesuaian_kas['pengurangan_kas']) . '</td>
					</tr>';
			}
			
			if ($penyesuaian_kas) {
				echo '<tr>	
						<td colspan="2" class="fw-bold">Total Penyesuaian Kas</td>
						<td colspan="2" class="fw-bold text-end">' . format_number($total_penyesuaian_kas) . '</td>
					</tr>';
			}
			
			echo '<tr>	
				<td colspan="3" class="fw-bold">Pendapatan</td>
			</tr>';
			
			$total_pendapatan = 0;
			foreach ($pendapatan as $val) {
				if (!$val['nama_pendapatan_jenis']) {
					continue;
				}
				echo '<tr>
						<td>' . $val['nama_pendapatan_jenis'] . '</td>
						<td></td>
						<td class="text-end" style="width:100px">' . format_number($val['jumlah_pendapatan']) . '</td>
					</tr>';
				$total_pendapatan += $val['jumlah_pendapatan'];
			}
			?>
			<tr>
				<td class="fw-bold">Total Pendapatan</td>
				<td></td>
				<td class="text-end fw-bold"><?=format_number($total_pendapatan)?></td>
			</tr>
			<tr>
				<td class="fw-bold">Pengeluarann</td>
				<td></td>
				<td></td>
			</tr>
			<?php

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
					echo '<tr>
								<td>' . $val['nama_kategori'] . '</td>
								<td></td>
								<td class="text-end">' . format_number($val['total_pengeluaran']) . '</td>
							</tr>';
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
					echo '<tr>
							<td>' . $val['nama_kategori'] . '</td>
							<td></td>
							<td></td>
						</tr>';
					$total = 0;
					foreach ($val['item'] as $item) {
						echo '<tr>
							<td><ul class="list-circle ms-2"><li class="list-circle">' . $item['nama_kategori'] . '</li></ul></td>
							<td class="text-end">' . format_number($item['total_pengeluaran']) . '</td>
							<td></td>
						</tr>';
						$total += $item['total_pengeluaran'];
					}
					echo '<tr>
							<td><div class="ms-3">Total ' . $val['nama_kategori'] . '</div></td>
							<td></td>
							<td class="text-end">' . format_number($total) . '</td>
						</tr>';
				}
			}
			
			$saldo_kas = $total_penyesuaian_kas + $total_pendapatan - $total_pengeluaran;
			?>
			<tr>
				<td class="fw-bold">Total Pengeluaran</td>
				<td></td>
				<td class="text-end fw-bold">-<?=format_number($total_pengeluaran)?></td>
			</tr>
			<tr>
				<td class="fw-bold">Saldo Kas <?=format_tanggal($end_date_db)?></td>
				<td></td>
				<td class="text-end fw-bold"><?=format_number($saldo_kas)?></td>
			</tr>
		</table>
		<div class="d-flex justify-content-end">
			<div class="me-5">
				<?php
				echo $identitas['kota_tandatangan'] . ', ' . $tgl_tandatangan . '<br/>' . $nama_jabatan
					. '<br/><br/><br/>' . $nama_pegawai . '<br/>NIP ' . $nip_pegawai;
			?>
			</div>
		</div>
				
		
	</div>
</body>
<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', () => {
		setTimeout(function() {
			window.close();
		}, 7000);
		
	});		
</script>
</html>