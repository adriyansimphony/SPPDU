<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	<?php
	helper('html');
	?>
	<div class="card-body">
		<form method="post" action="" class="form-horizontal form-siswa" enctype="multipart/form-data">
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Pilih Siswa</label>
				<div class="col-sm-5">
					<div style="position:relative">
						<?= options(['name' => 'id_kelas_asal', 'id' => 'option-kelas-asal', 'class' => 'select2'], $option_kelas, set_value('id_kelas'))?>
					</div>
					<table id="tabel-siswa" class="mt-5 table table-bordered table-striped table-hover">
						<thead>
							<tr>
								<th>No</th>
								<th>Nama</th>
								<th>NIS</th>
								<th>Kelas</th>
								<th>
									<div class="form-check text-start fw-bold">
										  <input class="form-check-input" type="checkbox" value="" id="check-all">
										  <label class="form-check-label" for="check-all">
											Semua
										  </label>
									</div>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="6" class="align-middle text-center" style="height:50vh"></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Siswa Dipilih</label>
				<div class="col-sm-5 fw-bold">
					<span class="fw-bold" id="jml-siswa-dipilih">0</span> Siswa
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Status</label>
				<div class="col-sm-5">
					<?=options(['name' => 'id_status_siswa', 'id' => 'id-status-siswa'], $status_siswa)?>
				</div>
			</div>
			<div class="row mb-3" id="row-pindah-ke" style="display:none">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kelas Baru</label>
				<div class="col-sm-5">
					<?= options(['name' => 'id_kelas_tujuan', 'id' => 'option-kelas-tujuan', 'class' => 'select2'], $option_kelas, set_value('id_kelas'))?>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tahun Ajaran</label>
				<div class="col-sm-5">
					<?php
					$selected = '';
					foreach ($tahun_ajaran as $id => $val) {
						if (substr(trim($val), 0, 4) == date('Y')) {
							$selected = $id;
						}
					}
					?>
					<?=options(['name' => 'id_tahun_ajaran'], $tahun_ajaran, $selected)?>
					<small>Tahun ajaran saat siswa menduduki status baru.</small>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tanggal</label>
				<div class="col-sm-5">
					<input type="text" class="form-control flatpickr" name="tgl_status"/>
					<small>Tanggal saat siswa menduduki status baru. Tanggal ini akan dicatat dalam riwayat status siswa.</small>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Keterangan</label>
				<div class="col-sm-5">
					<textarea name="keterangan" class="form-control"></textarea>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-5">
					<button type="submit" name="submit" value="submit" class="btn btn-primary submit-data">Submit</button>
				</div>
			</div>
		</form>
	</div>
</div>