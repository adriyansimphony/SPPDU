<?php helper('html')?>
<div class="card-body dashboard">
	<div class="row">
		<div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
			<div class="card text-bg-primary shadow">
				<div class="card-body card-stats">
					<div class="description">
						<h5 class="card-title h4"><?=!empty($kas) ? format_number($kas['saldo_kas']) : 0?></h5>
						<p class="card-text">Saldo Kas</p>
						
					</div>
					<div class="icon bg-warning-light">
						<!-- <i class="fas fa-clipboard-list"></i> -->
						<i class="material-icons">account_balance_wallet</i>
					</div>
				</div>
				<div class="card-footer">
					<div class="card-footer-left">
						<i class="material-icons me-2 mt-1" style="font-size:110%">calendar_today</i>Per <?=format_tanggal(date('Y-m-d'))?>
					</div>
					<div class="card-footer-right">
						
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
			<div class="card text-white bg-success shadow">
				<div class="card-body card-stats">
					<div class="description">
						<h5 class="card-title"><?=format_number($total_pendapatan - $total_pengeluaran)?></h5>
						<p class="card-text">Laba/Rugi</p>
					</div>
					<div class="icon">
						<!-- <i class="fas fa-shopping-cart"></i>-->
						<i class="material-icons">local_mall</i>
					</div>
				</div>
				<div class="card-footer">
					<div class="card-footer-left">
						<i class="material-icons me-2 mt-1" style="font-size:110%">calendar_today</i>Per <?=format_tanggal(date('Y-m-d'))?>
					</div>
					<div class="card-footer-right">
						
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
			<div class="card text-white bg-warning shadow">
				<div class="card-body card-stats">
					<div class="description">
						<h5 class="card-title"><?=!empty($total_tagihan_spp) ? format_number($total_tagihan_spp) : 0?></h5>
						<p class="card-text">SPP Belum Dibayar</p>
					</div>
					<div class="icon">
						<!-- <i class="fas fa-money-bill-wave"></i> -->
						<i class="material-icons">sticky_note_2</i>
					</div>
				</div>
				<div class="card-footer">
					<div class="card-footer-left">
						<i class="material-icons me-2 mt-1" style="font-size:110%">account_circle</i><?=$jumlah_siswa_spp?> siswa
					</div>
					<div class="card-footer-right">
						
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
			<div class="card text-white bg-danger shadow">
				<div class="card-body card-stats">
					<div class="description">
						<h5 class="card-title"><?=!empty($total_utang_pegawai) ? format_number($total_utang_pegawai) : 0?></h5>
						<p class="card-text">Utang Pegawai</p>
					</div>
					<div class="icon">
						<!-- <i class="fas fa-money-bill-wave"></i> -->
						<i class="material-icons">payments</i>
					</div>
				</div>
				<div class="card-footer">
					<div class="card-footer-left">
						<i class="material-icons me-2 mt-1" style="font-size:110%">person</i><?=$jumlah_pegawai_utang?> pegawai
					</div>
					<div class="card-footer-right">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 col-lg-8 mb-4">
			<div class="card" style="height:100%">
				<div class="card-header">
					<div class="card-header-start">
						<h5 class="card-title">Rincian Pendapatan</h5>
					</div>
					<div class="card-header-end">
						<div class="d-flex">
							<?=options(['name' => 'pendapatan_kategori', 'id' => 'pendapatan-kategori'], $list_tahun_pendapatan)?>
						</div>
					</div>
				</div>
				<div class="card-body">
					<?php
					$column_dt = [];
					$settings = [];
					if (!$total_pendapatan) {
						echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
					} else {
						$column =[
							'ignore_urut' => 'No'
							, 'nama' => 'Nama'
							, 'nama_pendapatan_jenis' => 'Pendapatan'
							, 'nilai_bayar' => 'Jml. Bayar'
							, 'tgl_bayar' => 'Tgl. Bayar'
							, 'ignore_action' => 'Action'
						];
						
						$settings['order'] = [3,'desc'];
						$index = 0;
						$th = '';
						foreach ($column as $key => $val) {
							$th .= '<th>' . $val . '</th>'; 
							if (strpos($key, 'ignore') !== false) {
								$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
							}
							$index++;
						}
						helper ('html');
						?>
						<table id="tabel-pendapatan" class="table display table-striped table-bordered table-hover" style="width:100%">
						<thead>
							<tr>
								<?=$th?>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<?=$th?>
							</tr>
						</tfoot>
						</table>
						<?php
							foreach ($column as $key => $val) {
								$column_dt[] = ['data' => $key];
							}
						?>
						<span id="dataTablesPendapatan-column" style="display:none"><?=json_encode($column_dt)?></span>
						<span id="dataTablesPendapatan-setting" style="display:none"><?=json_encode($settings)?></span>
						<span id="dataTablesPendapatan-url" style="display:none"><?=base_url() . '/dashboard-keuangan/getDataDTPendapatan'?></span>
					<?php
					}
					?>
				</div>
			</div>
		</div>
		<div class="col-md-12 col-lg-4 mb-4">
			<div class="card" style="height:100%">
				<div class="card-header">
					<div class="card-header-start">
						<h5 class="card-title">Pendapatan Terbesar</h5>
					</div>
					<div class="card-header-end">
						<div class="d-flex">
							<?=options(['name' => 'pendapatan_kategori', 'id' => 'pendapatan-kategori'], $list_tahun_pendapatan)?>
						</div>
					</div>
				</div>
				<div class="card-body d-flex justify-content-center align-items-center flex-wrap chart-pendapatan-card">
					<div class="chart-pendapatan-container">
						<div style="overflow: auto; width:100%">
							<?php
							if ($total_pendapatan) {
								echo '<canvas id="chart-pendapatan" style="margin:auto"></canvas>';
							} else {
								echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
							}
							?>
							
						</div>
					</div>
					<div class="alert alert-danger" style="display:none">Data tidak ditemukan</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 col-lg-4 mb-4">
			<div class="card" style="height:100%">
				<div class="card-header">
					<div class="card-header-start">
						<h5 class="card-title">Pengeluaran Terbesar</h5>
					</div>
					<div class="card-header-end">
						<div class="d-flex">
							<?=options(['name' => 'pengeluaran_kategori', 'id' => 'pengeluaran-kategori'], $list_tahun_pengeluaran)?>
						</div>
					</div>
				</div>
				<div class="card-body d-flex justify-content-center align-items-center flex-wrap chart-pengeluaran-card">
					<div class="chart-pengeluaran-container">
						<div style="overflow: auto; width:100%">
							<?php
							if ($total_pendapatan) {
								echo '<canvas id="chart-pengeluaran" style="margin:auto"></canvas>';
							} else {
								echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
							}
							?>
							
						</div>
					</div>
					<div class="alert alert-danger" style="display:none">Data tidak ditemukan</div>
				</div>
			</div>
		</div>
		<div class="col-md-12 col-lg-8 mb-4">
			<div class="card" style="height:100%">
				<div class="card-header">
					<div class="card-header-start">
						<h5 class="card-title">Rincian Pengeluaran</h5>
					</div>
					<div class="card-header-end">
						<div class="d-flex">
							<?=options(['name' => 'pendapatan_kategori', 'id' => 'pendapatan-kategori'], $list_tahun_pendapatan)?>
						</div>
					</div>
				</div>
				<div class="card-body">
					<?php
					$column_dt = [];
					$settings = [];
					if (!$total_pengeluaran) {
						echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
					} else {
						$column =[
							'ignore_urut' => 'No'
							, 'nama_kategori' => 'Kategori'
							, 'total_pengeluaran' => 'Jumlah'
							, 'tgl_pengeluaran' => 'Tgl. Pengeluaran'
							, 'nama_jenis_bayar' => 'Jenis'
							, 'ignore_action' => 'Action'
						];
		
						$settings['order'] = [3,'desc'];
						$index = 0;
						$th = '';
						foreach ($column as $key => $val) {
							$th .= '<th>' . $val . '</th>'; 
							if (strpos($key, 'ignore_search') !== false) {
								$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
							}
							$index++;
						}
						
						?>
						<div class="table-responsive">
						<table id="tabel-pengeluaran" class="table display table-striped table-bordered table-hover" style="width:100%">
						<thead>
							<tr>
								<?=$th?>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<?=$th?>
							</tr>
						</tfoot>
						</table>
						</div>
						<?php
							foreach ($column as $key => $val) {
								$column_dt[] = ['data' => $key];
							}
						?>
						<span id="dataTablesPengeluaran-column" style="display:none"><?=json_encode($column_dt)?></span>
						<span id="dataTablesPengeluaran-setting" style="display:none"><?=json_encode($settings)?></span>
						<span id="dataTablesPengeluaran-url" style="display:none"><?=base_url() . '/dashboard-keuangan/getDataDTPengeluaran'?></span>
					<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
$total_jumlah_pendapatan_kategori = [];
$nama_pendapatan_kategori = [];
foreach ($total_pendapatan_kategori as $val) {
	$total_jumlah_pendapatan_kategori[] = $val['total_pendapatan'];
	$nama_pendapatan_kategori[] = $val['nama_pendapatan_jenis'];
}

$total_jumlah_pengeluaran_kategori = [];
$nama_pengeluaran_kategori = [];
foreach ($total_pengeluaran_kategori as $val) {
	$total_jumlah_pengeluaran_kategori[] = $val['total_pengeluaran'];
	$nama_pengeluaran_kategori[] = $val['nama_kategori'];
}
?>
<script type="text/javascript">
function dynamicColors() {
	var r = Math.floor(Math.random() * 255);
	var g = Math.floor(Math.random() * 255);
	var b = Math.floor(Math.random() * 255);
	return "rgba(" + r + "," + g + "," + b + ", 0.8)";
}
total_pendapatan_kategori = <?=json_encode($total_jumlah_pendapatan_kategori)?>;
nama_pendapatan_kategori = <?=json_encode($nama_pendapatan_kategori)?>;
total_pengeluaran_kategori = <?=json_encode($total_jumlah_pengeluaran_kategori)?>;
nama_pengeluaran_kategori = <?=json_encode($nama_pengeluaran_kategori)?>
</script>