<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal form-pendapatan-lain px-3" enctype="multipart/form-data">
	<div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Nama Pegawai</label>
			<div class="col-sm-9">
				<div class="input-group">
					<input class="form-control" type="text" id="nama-pegawai" name="nama_pegawai" disabled="disabled" readonly="readonly" value="<?=set_value('nama_pegawai', @$utang['nama'] ?: '')?>" required="required"/>
					<button type="button" class="btn btn-outline-secondary cari-pegawai-utang"><i class="fas fa-search"></i> Cari</button>
					<a href="<?=base_url()?>/builtin/pegawai/add" target="_blank" class="btn btn-outline-success btn-add-pegawai" id="add-pegawai" href="javascript:void(0)"><i class="fas fa-plus"></i> Tambah</a>
				</div>
				<input type="hidden" name="id_pegawai" id="id-pegawai" value="<?=set_value('id_pegawai', @$utang['id_pegawai'])?>"/>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Metode</label>
			<div class="col-sm-9">
				<?=options(['name' => 'id_jenis_bayar'], $metode_pembayaran, set_value('id_jenis_bayar', @$utang['id_jenis_bayar']))?>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Nilai Utang</label>
			<div class="col-sm-9">
				<input class="form-control number" type="text" name="nilai_utang" value="<?=set_value('nilai_utang', @format_number($utang['nilai_utang']))?>"/>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Tanggal Utang</label>
			<div class="col-sm-9">
				<input class="form-control flatpickr tanggal-utang flatpickr" type="text" name="tgl_utang" value="<?=@$utang['tgl_utang'] ? format_tanggal(@$utang['tgl_utang'], 'dd-mm-yyyy') : date('d-m-Y')?>" required="required"/>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Keterangan</label>
			<div class="col-sm-9">
				<textarea class="form-control" name="keterangan"><?=set_value('keterangan', @$utang['keterangan'])?></textarea>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" id="id-pegawai-utang" value="<?=set_value('id', @$utang['id_pegawai_utang'])?>"/>
</form>