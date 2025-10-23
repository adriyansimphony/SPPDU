<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	<div class="card-body">
		<?php 
			helper ('html');
			echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
				'url' => $config->baseURL . 'riwayat-status-siswa',
				'icon' => 'fa fa-arrow-circle-left',
				'label' => 'Daftar Riwayat Status Siswa'
			]);
		?>
		<hr/>
		<form method="post" action="" class="form-horizontal form-siswa" enctype="multipart/form-data">
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Siswa</label>
				<div class="col-sm-5">
					<?=$riwayat['nama']?><input type="hidden" name="id_siswa" value="<?=$riwayat['id_siswa']?>"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">NIS</label>
				<div class="col-sm-5">
					<?=$riwayat['nis']?>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Status</label>
				<div class="col-sm-5">
					<?=options(['name' => 'id_status_siswa', 'id' => 'id-status-siswa'], $option_status, set_value('id_status_siswa', $riwayat['id_status_siswa']))?>
				</div>
			</div>
			<?php
			$display = set_value('id_status_siswa', $riwayat['id_status_siswa']) <= 2 ? '' : ' style="display:none"';
			?>
			<div class="row mb-3" id="row-pindah-ke"<?=$display?>>
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kelas Baru</label>
				<div class="col-sm-5">
					<?= options(['name' => 'id_kelas', 'id' => 'option-kelas', 'class' => 'select2'], $option_kelas, set_value('id_kelas', $riwayat['id_kelas']))?>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tahun Ajaran</label>
				<div class="col-sm-5">
					<?=options(['name' => 'id_tahun_ajaran'], $tahun_ajaran, set_value('id_tahun_ajaran', $riwayat['id_tahun_ajaran']))?>
					<small>Tahun ajaran saat siswa menduduki status baru.</small>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tanggal</label>
				<div class="col-sm-5">
					<?php
					$exp = explode('-', set_value('tgl_status', $riwayat['tgl_status']));
					$tgl_status = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
					?>
					<input type="text" class="form-control flatpickr" name="tgl_status" value="<?=$tgl_status?>"/>
					<small>Tanggal saat siswa menduduki status baru. Tanggal ini akan dicatat dalam riwayat status siswa.</small>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Keterangan</label>
				<div class="col-sm-5">
					<textarea name="keterangan" class="form-control"><?=set_value('keterangan', $riwayat['keterangan'])?></textarea>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-5">
					<button type="submit" name="submit" value="submit" class="btn btn-primary submit-data">Submit</button>
					<input type="hidden" name="id" id="id-siswa-riwayat-status" value="<?=@$_GET['id']?>"/>
				</div>
			</div>
		</form>
	</div>
</div>