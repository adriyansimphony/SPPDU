<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal form-siswa px-3" enctype="multipart/form-data">
	<div class="row mb-3">
		<label class="col-xl-3 col-form-label">Status</label>
		<div class="col-sm-9">
			<?=options(['name' => 'id_status_siswa', 'id' => 'id-status-siswa'], $option_status, set_value('id_status_siswa', @$riwayat['id_status_siswa']))?>
		</div>
	</div>
	<?php
	$display = set_value('id_status_siswa', @$riwayat['id_status_siswa']) <= 2 ? '' : ' style="display:none"';
	?>
	<div class="row mb-3" id="row-pindah-ke"<?=$display?>>
		<label class="col-xl-3 col-form-label">Kelas Baru</label>
		<div class="col-sm-9">
			<?= options(['name' => 'id_kelas', 'id' => 'option-kelas-tujuan', 'class' => 'select2'], $option_kelas, set_value('id_kelas', @$riwayat['id_kelas']))?>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-xl-3 col-form-label">Tahun Ajaran</label>
		<div class="col-sm-9">
			<?=options(['name' => 'id_tahun_ajaran'], $tahun_ajaran, set_value('id_tahun_ajaran', @$riwayat['id_tahun_ajaran']))?>
			<small>Tahun ajaran saat siswa menduduki status baru.</small>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-xl-3 col-form-label">Tanggal</label>
		<div class="col-sm-9">
			<?php
			if ( @$riwayat['tgl_status'] ) {
				$exp = explode('-', set_value('tgl_status', @$riwayat['tgl_status']));
				$tgl_status = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
			} else {
				$tgl_status = date('d-m-Y');
			}
			?>
			<input type="text" class="form-control flatpickr" name="tgl_status" value="<?=$tgl_status?>"/>
			<small>Tanggal saat siswa menduduki status baru. Tanggal ini akan dicatat dalam riwayat status siswa.</small>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-xl-3 col-form-label">Keterangan</label>
		<div class="col-sm-9">
			<textarea name="keterangan" class="form-control"><?=set_value('keterangan', @$riwayat['keterangan'])?></textarea>
		</div>
	</div>
	<input type="hidden" name="id" id="id-siswa-riwayat-status" value="<?=@$_GET['id']?>"/>
	<input type="hidden" name="id_siswa" id="id-siswa" value="<?=@$_GET['id_siswa']?>"/>
</form>