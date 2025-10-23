<?php
helper('html');
/* echo '<pre>';
print_r($pengeluaran);
die; */
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Laporan Kas</h5>
	</div>
	<div class="card-body">
		<form method="get" action="" class="form-horizontal p-3" enctype="multipart/form-data">
			<div class="row mb-3">
				<label class="col-sm-2 col-form-label">Periode</label>
				<div class="col-sm-5">
					<div class="input-group">
						<span class="input-group-text">
							<i class="far fa-calendar"></i>
						</span>
						<input type="text" class="form-control" name="daterange" id="daterange" value="<?=$start_date?> s.d. <?=$end_date?>" />
					</div>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-2 col-form-label">Pengeluaran</label>
				<div class="col-sm-5">
					<?=options(['name' => 'tampilkan_pengeluaran', 'class' => 'tampilkan-pengeluaran'], ['resume' => 'Resume', 'detail' => 'Detail'], set_value('tampilkan_pengeluaran', 'resume'))?>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-2 col-form-label">Tgl. Tandatangan</label>
				<div class="col-sm-5">
					<div class="input-group">
						<span class="input-group-text">
							<i class="far fa-calendar"></i>
						</span>
						<input type="text" class="form-control flatpickr" name="tgl_tandatangan" value="<?=date('d-m-Y')?>" />
					</div>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-2 col-form-label">Pejabat</label>
				<div class="col-sm-5">
					<?php
					$option_pegawai = [];
					foreach ($pegawai as $val) {
						$option_pegawai[$val['id_pegawai'] . '_' . $val['id_jabatan']] = $val['nama'] . ' (' . $val['nama_jabatan'] . ')';
					}
					?>
					<?=options(['name' => 'id_pegawai', 'class' => 'select2'], $option_pegawai, set_value('id_pegawai', ''))?>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-2 col-form-label">Submit</label>
				<div class="col-sm-5">
					<button type="submit" class="btn btn-primary" name="submit" value="submit">Submit</button>
				</div>
			</div>
		</form>
		<hr/>
		<span style="display:none" id="query-string"><?=http_build_query($_GET)?></span>
		<div class="row mb-3">
			<div class="col-sm-12">
				<div class="table-responsive" style="max-width:600px">
					<div class="text-end">
						<div class="btn-group">
							<button data-url="<?=base_url()?>/laporan-kas/printLaporan" class="btn btn-outline-secondary btn-print"><i class="fas fa-print me-1"></i>Print</button>
							<button class="btn btn-outline-secondary btn-excel"><i class="far fa-file-excel me-1"></i>Excel</button>
							<button class="btn btn-outline-secondary btn-pdf"><i class="far fa-file-pdf me-1"></i>PDF</button>
						</div>
					</div>
					<table class="table">
						<tr>
							<td colspan="3">
								<div class="text-center mt-3 mb-4">
									<h5>Laporan Kas</h5>
									<h5><?=$identitas['nama_sekolah']?></h5>
									<h5>Periode <?=format_tanggal($start_date_db)?> s.d. <?=format_tanggal($end_date_db)?></h5>
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
						<div>
							<?php
							echo $identitas['kota_tandatangan'] . ', ' . $tgl_tandatangan . '<br/>' . $nama_jabatan
								. '<br/><br/><br/>' . $nama_pegawai . '<br/>NIP ' . $nip_pegawai;
						?>
						</div>
					</div>
				</div>
				
		</div>
		<?php

		function build_kategori($list, $list_kategori) 
		{
			// global $list_kategori;
			// print_r($list_kategori); die;
			$new = array();
			$current = &$new;
			foreach($list as $key => $value)
			{
				$current[$value] = array();
				$current[$value]['nama_kategori'] = $list_kategori[$value]['nama_kategori'];
				$current = &$current[$value];
			}
			return $new;
		}
		
		/* function build_kategori($list, $result = []) 
		{
			global $list_kategori;
			if (empty($list)) { return null; }
			$id = array_shift($list);
			$result[array_shift($list)] = ['nama_kategori' => 'nama', 'data' => build_kategori($list, $result)];
			return $result;
		} */
		
		?>
	</div>
</div>