<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal p-3">
	<div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Nama Pendapatan</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="nama_pendapatan_jenis" value="<?=@$pendapatan['nama_pendapatan_jenis']?>"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Kategori</label>
			<div class="col-sm-9">
				<?=options(['name' => 'kategori'], ['internal' => 'Internal', 'eksternal' => 'Eksternal'], @$pendapatan['kategori'])?>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Gunakan Periode</label>
			<div class="col-sm-9">
				<?=options(['name' => 'using_periode', 'id' => 'using-periode'], ['N' => 'Tidak', 'Y' => 'Ya'], @$pendapatan['using_periode'])?>
			</div>
		</div>
		<?php
		$display = @$pendapatan['using_periode'] == 'Y' ? '' : ' style="display:none"';
		?>
		<div class="row mb-3" id="row-jenis-periode" <?=$display?>>
			<label class="col-sm-3 col-form-label">Jenis Periode</label>
			<div class="col-sm-9">
				<?=options(['name' => 'jenis_periode'], ['bulan' => 'Bulan', 'tahun' => 'Tahun', 'tahun_ajaran' => 'Tahun Ajaran'], @$pendapatan['jenis_periode'])?>
			</div>
		</div>
		<div class="row mb-3" id="row-jenis-periode">
			<label class="col-sm-3 col-form-label">Sumber</label>
			<div class="col-sm-9">
				<?=options(['name' => 'id_pendapatan_sumber[]', 'id' => 'id-pendapatan-sumber', 'multiple' => 'multiple', 'class' => 'select2', 'required' => 'required'], $option_pendapatan_sumber, @$pendapatan_sumber_selected)?>
			</div>
		</div>
		<?php
		$display = ' style="display:none"';
		if (@$pendapatan_sumber_selected) {
			if (in_array(1, $pendapatan_sumber_selected)) {
				$display =  '';
			}
		}

		?>
		<div class="row mb-3" id="row-perlu-tagihan-siswa" <?=$display?>>
			<label class="col-sm-3 col-form-label">Tagihan Siswa</label>
			<div class="col-sm-9">
				<?=options(['name' => 'perlu_tagihan_siswa', 'id' => 'perlu-tagihan-siswa'], ['N' => 'Tidak', 'Y' => 'Ya'], @$pendapatan['perlu_tagihan_siswa'])?>
				<small>Apakah jenis pendapatan perlu dibuat tagihan siswa</small>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
</form>