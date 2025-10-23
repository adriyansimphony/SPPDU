<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal form-pegawai px-3" enctype="multipart/form-data">
	<div class="row mb-3">
		<label class="col-xl-3 col-form-label">Status</label>
		<div class="col-sm-9">
			<?=options(['name' => 'id_status_pegawai'], $option_status, set_value('id_status_pegawai', @$riwayat['id_status_pegawai']))?>
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
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-xl-3 col-form-label">Keterangan</label>
		<div class="col-sm-9">
			<textarea name="keterangan" class="form-control"><?=set_value('keterangan', @$riwayat['keterangan'])?></textarea>
		</div>
	</div>
	<input type="hidden" name="id" id="id-pegawai-riwayat-status" value="<?=@$_GET['id']?>"/>
	<input type="hidden" name="id_pegawai" id="id-pegawai" value="<?=@$_GET['id_pegawai']?>"/>
</form>