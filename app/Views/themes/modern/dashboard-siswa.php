<?php helper('html')?>
<div class="card-body dashboard">
	<div class="row">
		<div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
			<div class="card text-bg-primary shadow">
				<div class="card-body card-stats">
					<div class="description">
						<h5 class="card-title h4"><?=!empty($total_siswa) ? format_number($total_siswa) : 0?></h5>
						<p class="card-text">Total Siswa</p>
						
					</div>
					<div class="icon bg-warning-light">
						<!-- <i class="fas fa-clipboard-list"></i> -->
						<i class="material-icons">group</i>
					</div>
				</div>
				<div class="card-footer">
					<div class="card-footer-left">
						<?=$total_siswa . '/' . $total_siswa?>
					</div>
					<div class="card-footer-right">
						<p>100%</p>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
			<div class="card text-white bg-success shadow">
				<div class="card-body card-stats">
					<div class="description">
						<h5 class="card-title"><?=!empty($total_siswa_gender['jml_laki']) ? format_number($total_siswa_gender['jml_laki']) : 0?></h5>
						<p class="card-text">Siswa Laki Laki</p>
					</div>
					<div class="icon">
						<!-- <i class="fas fa-shopping-cart"></i>-->
						<i class="material-icons">face</i>
					</div>
				</div>
				<div class="card-footer">
					<div class="card-footer-left">
						<?=$total_siswa_gender['jml_laki'] . '/' . $total_siswa?>
					</div>
					<div class="card-footer-right">
						<p><?=round($total_siswa_gender['jml_laki']/$total_siswa*100) ?>%</p>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
			<div class="card text-white bg-warning shadow">
				<div class="card-body card-stats">
					<div class="description">
						<h5 class="card-title"><?=!empty($total_siswa_gender['jml_perempuan']) ? format_number($total_siswa_gender['jml_perempuan']) : 0?></h5>
						<p class="card-text">Siswa Perempuan</p>
					</div>
					<div class="icon">
						<!-- <i class="fas fa-money-bill-wave"></i> -->
						<i class="material-icons">face_3</i>
					</div>
				</div>
				<div class="card-footer">
					<div class="card-footer-left">
						<?=$total_siswa_gender['jml_perempuan'] . '/' . $total_siswa?>
					</div>
					<div class="card-footer-right">
						<p><?=round($total_siswa_gender['jml_perempuan']/$total_siswa*100) ?>%</p>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
			<div class="card text-white bg-danger shadow">
				<div class="card-body card-stats">
					<div class="description">
						<h5 class="card-title"><?=format_number($total_siswa_baru)?></h5>
						<p class="card-text">Siswa Baru <?=date('Y') . '/' . (date('Y') + 1)?></p>
					</div>
					<div class="icon">
						<!-- <i class="fas fa-money-bill-wave"></i> -->
						<i class="material-icons">group_add</i>
					</div>
				</div>
				<div class="card-footer">
					<div class="card-footer-left">
						L: <?=$total_siswa_baru_gender['jml_laki']?> P: <?=$total_siswa_baru_gender['jml_perempuan']?>
					</div>
					<div class="card-footer-right">
						<p>100%</p>
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
						<h5 class="card-title">Siswa</h5>
					</div>
				</div>
				<div class="card-body">
					<?php
					if (!$total_siswa) {
						echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
					} else {
						$column =[
									'ignore_urut' => 'No'
									, 'nama' => 'Nama'
									, 'nis' => 'NIS'
									, 'nisn' => 'NISN'
									, 'nama_kelas' => 'Kelas'
									, 'ignore_action' => 'Action'
								];
						
						$settings['order'] = [2,'asc'];
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
						$disabled = !$total_siswa ? 'disabled="disabled"' : '';
						?>
						<div class="row">
							<div class="col-sm-6 mb-3 text-center text-sm-start">
								<div class="input-group d-flex flex-nowrap" style="width:auto">
									<div class="input-group-text">Tampilkan Siswa</div>
									<form method="get">
										<?= options(['name' => 'id_kelas', 'id' => 'option-kelas'], $option_kelas, set_value('id_kelas'))?>
									</form>
								</div>
							</div>
							<div class="col-sm-6 mb-3 text-center text-sm-end" style="text-align:right">
								<div class="btn-group">
									<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-excel-siswa" <?=$disabled?>><i class="fas fa-file-excel me-2"></i>XLSX</button>
								</div>
							</div>
						</div>
						<table id="tabel-siswa" class="table display table-striped table-bordered table-hover" style="width:100%">
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
						<span id="dataTablesSiswa-column" style="display:none"><?=json_encode($column_dt)?></span>
						<span id="dataTablesSiswa-setting" style="display:none"><?=json_encode($settings)?></span>
						<span id="dataTablesSiswa-url" style="display:none"><?=base_url() . '/dashboard-siswa/getDataDTSiswa?id_kelas=' . @$_GET['id_kelas']?></span>
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
						<h5 class="card-title">Siswa Baru</h5>
					</div>
					<div class="card-header-end">
						<div class="d-flex">
						<?php
							$start_date = date('Y');
							$option_tahun_ajaran = [];
							for ($i = $start_date; $i >= $start_date - 4; $i--) {
								$tahun_ajaran = $i . '/' . $i+1;
								$option_tahun_ajaran[$tahun_ajaran] = $tahun_ajaran;
							}
						?>
						<?=options(['name' => 'tahun_ajaran', 'id' => 'option-tahun-ajaran-siswa-baru'], $option_tahun_ajaran)?>
						</div>
					</div>
				</div>
				<div class="card-body d-flex justify-content-center align-items-center flex-wrap chart-siswa-gender-card">
					<div class="chart-gender-siswa-container">
						<div style="overflow: auto; width:100%">
							<?php
							if ($total_siswa) {
								echo '<canvas id="chart-gender-siswa" style="margin:auto"></canvas>';
							} else {
								echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
							}
							?>
							
						</div>
						<div class="chart-gender-siswa-detail d-flex align-items-center justify-content-center mt-5">
							<div class="text-light rounded px-2 py-1 me-2" style="background:#8571bd">L: <span class="jml-laki"><?=$total_siswa_baru_gender['jml_laki']?></span></div>
							<div class="text-bg-success rounded px-2 py-1 me-2">P: <span class="jml-perempuan"><?=$total_siswa_baru_gender['jml_perempuan']?></span></div>
							<div class="text-bg-warning rounded px-2 py-1">T: <span class="jml-total"><?=$total_siswa_baru?></span></div>
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
						<h5 class="card-title">Pegawai </h5>
					</div>
					<div class="card-header-end">
						
					</div>
				</div>
				<div class="card-body d-flex justify-content-center align-items-center flex-wrap chart-siswa-gender-card">
					<div style="overflow: auto; width:100%">
						<?php
						if ($total_pegawai) {
							echo '<canvas id="chart-gender-pegawai" style="margin:auto"></canvas>';
						} else {
							echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
						}
						?>
						
					</div>
					<div class="chart-gender-pegawai-detail d-flex align-items-center justify-content-center mt-5">
						<div class="text-light rounded px-2 py-1 me-2" style="background:#76a7c6">L: <span class="jml-laki"><?=$total_pegawai_gender['jml_laki']?></span></div>
						<div class="text-light rounded px-2 py-1 me-2" style="background:#aeb7ae">P: <span class="jml-perempuan"><?=$total_pegawai_gender['jml_perempuan']?></span></div>
						<div class="text-light rounded px-2 py-1" style="background:#5dbfa3">T: <span class="jml-total"><?=$total_pegawai?></span></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-12 col-lg-8 mb-4">
			<div class="card" style="height:100%">
				<div class="card-header">
					<div class="card-header-start">
						<h5 class="card-title">Pegawai</h5>
					</div>
				</div>
				<div class="card-body">
					<?php
					$column_dt = [];
					$settings = [];
					if (!$total_pegawai) {
						echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
					} else {
						$column =[
									'ignore_urut' => 'No'
									, 'nama' => 'Nama'
									, 'nik' => 'NIK'
									, 'nip_pegawai' => 'NIP'
									, 'no_hp' => 'No. HP'
									, 'ignore_action' => 'Action'
								];
						
						$settings['order'] = [1,'asc'];
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
						<table id="tabel-pegawai" class="table display table-striped table-bordered table-hover" style="width:100%">
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
						<span id="dataTablesPegawai-column" style="display:none"><?=json_encode($column_dt)?></span>
						<span id="dataTablesPegawai-setting" style="display:none"><?=json_encode($settings)?></span>
						<span id="dataTablesPegawai-url" style="display:none"><?=base_url() . '/dashboard-siswa/getDataDTPegawai'?></span>
					<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
	
<script type="text/javascript">
function dynamicColors() {
	var r = Math.floor(Math.random() * 255);
	var g = Math.floor(Math.random() * 255);
	var b = Math.floor(Math.random() * 255);
	return "rgba(" + r + "," + g + "," + b + ", 0.8)";
}
let siswa_gender = <?=json_encode(array_values($total_siswa_gender))?>;
let siswa_baru_gender = <?=json_encode(array_values($total_siswa_baru_gender))?>;
let pegawai_gender = <?=json_encode(array_values($total_pegawai_gender))?>;
</script>