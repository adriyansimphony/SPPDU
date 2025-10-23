<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
			helper ('html');
			echo btn_link(['attr' => ['class' => 'btn btn-success btn-xs'],
				'url' => $config->baseURL . $current_module['nama_module'] . '/add',
				'icon' => 'fa fa-plus',
				'label' => 'Tambah Data'
			]);
			
			echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
				'url' => $config->baseURL . $current_module['nama_module'],
				'icon' => 'fa fa-arrow-circle-left',
				'label' => $current_module['judul_module']
			]);
		?>
		<hr/>
		<?php
		
		if (!empty($message)) {
			show_message($message);
		}
		
		if (@$siswa['tgl_lahir']) {
			$exp = explode('-', $siswa['tgl_lahir']);
			$tgl_lahir = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		} else {
			$tgl_lahir = date('d-m-Y');
		}
		?>
		<form method="post" action="" class="form-horizontal form-siswa" enctype="multipart/form-data">
			<div class="tab-content" id="myTabContent">
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Siswa</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="nama" value="<?=set_value('nama', @$siswa['nama'])?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Jenis Kelamin</label>
					<div class="col-sm-6">
						<?=options(['name' => 'jenis_kelamin'], ['L' => 'Laki-Laki', 'P' => 'Perempuan'], set_value('jenis_kelamin', @$siswa['jenis_kelamin']))?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Agama</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_agama'], $list_agama, set_value('id_agama', @$siswa['id_agama']))?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">NISN</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="nisn" value="<?=set_value('nisn', @$siswa['nisn'])?>"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">NIS</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="nis" value="<?=set_value('nis', @$siswa['nis'])?>"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">NIK</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="nik" value="<?=set_value('nik', @$siswa['nik'])?>"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tempat Lahir</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="tempat_lahir" value="<?=set_value('tempat_lahir', @$siswa['tempat_lahir'])?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tgl. Lahir</label>
					<div class="col-sm-6">
						<input class="form-control flatpickr" type="text" name="tgl_lahir" value="<?=set_value('tgl_lahir', @$tgl_lahir)?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Alamat</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="alamat" value="<?=set_value('alamat', @$siswa['alamat'])?>" required="required"/>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Propinsi</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_wilayah_propinsi', 'class' => 'propinsi select2'], $propinsi, set_value('id_wilayah_propinsi', $id_wilayah_propinsi) )?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kabupaten</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_wilayah_kabupaten', 'class' => 'kabupaten select2'], $kabupaten, set_value('id_wilayah_kabupaten', $id_wilayah_kabupaten))?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kecamatan</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_wilayah_kecamatan', 'class' => 'kecamatan select2'], $kecamatan, set_value('id_wilayah_kecamatan',$id_wilayah_kecamatan))?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kelurahan</label>
					<div class="col-sm-6" style="position:relative">
						<?=options(['name' => 'id_wilayah_kelurahan', 'class' => 'kelurahan select2'], $kelurahan, set_value('id_wilayah_kelurahan', $id_wilayah_kelurahan))?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Foto</label>
					<div class="col-sm-6">
						<?php
				
						if (!empty($siswa['foto']) ) 
						{
							$note = '';
							if (file_exists(ROOTPATH . 'public/images/siswa/' . $siswa['foto'])) {
								$image = $config->baseURL . 'public/images/siswa/' . $siswa['foto'];
							} else {
								$image = $config->baseURL . 'public/images/siswa/noimage.png';
								$note = '<small><b>Note</strong>: File <strong>public/images/siswa/' . $siswa['foto'] . '</strong> tidak ditemukan</small>';
							}
							echo '<div class="img-choose" style="margin:inherit;margin-bottom:10px">
									<div class="img-choose-container">
										<img src="'. $image . '?r=' . time() . '"/>
										<a href="javascript:void(0)" class="remove-img"><i class="fas fa-times"></i></a>
									</div>
								</div>
								' . $note .'
								';
						}
						?>
						<input type="hidden" class="foto-delete-img" name="foto_delete_img" value="0">
						<input type="hidden" class="foto-max-size" name="foto_max_size" value="300000"/>
						<input type="file" class="file form-control" name="foto">
							<?php if (!empty($form_errors['foto'])) echo '<small class="alert alert-danger">' . $form_errors['foto'] . '</small>'?>
							<small class="small" style="display:block">Maksimal 300Kb, Minimal 100px x 100px, Tipe file: .JPG, .JPEG, .PNG</small>
						<div class="upload-file-thumb"><span class="file-prop"></span></div>
					</div>
				</div>
				<div class="px-4 py-3 mt-4 mb-2 bg-lightgrey border">
					<h5>Status</h5>
				</div>
				<hr/>
				
				<?php 
				if (!empty($_GET['id'])) {
				?>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Status</label>
					<div class="col-sm-6">
						<button type="button" class="btn btn-success btn-add-status btn-xs"><i class="fas fa-plus me-2"></i>Tambah</button>
						<hr/>
						<?php
						$column =[
									'nama_status_siswa' => 'Status'
									, 'nama_kelas' => 'Kelas'
									, 'tgl_status' => 'Tanggal'
									, 'tahun_ajaran' => 'Tahun Ajaran'
									, 'ignore_action' => 'Aksi'
								];
						$th = '';
						foreach ($column as $val) {
							$th .= '<th>' . $val . '</th>'; 
						}
						?>
						<table id="tabel-riwayat-status" class="table display nowrap table-striped table-bordered" style="width:100%">
							<thead>
								<tr>
									<?=$th?>
								</tr>
							</thead>
							</table>
							<?php
								$settings['order'] = [2,'desc'];
								$index = 0;
								foreach ($column as $key => $val) {
									$column_dt[] = ['data' => $key];
									if (strpos($key, 'ignore') !== false) {
										$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
									}
									$index++;
								}
							?>
							<span id="dataTablesRiwayat-column" style="display:none"><?=json_encode($column_dt)?></span>
							<span id="dataTablesRiwayat-setting" style="display:none"><?=json_encode($settings)?></span>
							<span id="dataTablesRiwayat-url" style="display:none"><?=base_url() . '/siswa/getDataDTSiswaRiwayatStatus?id=' . $_GET['id']?></span>
						</table>
					</div>
				</div>
				<?php
				} else {
				?>
					<div class="row mb-3 row-status">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Status</label>
						<div class="col-sm-6">
							<div class="input-group">
								<?=options(['name' => 'id_status_siswa'], $list_status_siswa)?>
								<?=options(['name' => 'id_kelas'], $list_kelas)?>
							</div>
							<small>Status terakhir siswa. Masuk Baru HANYA untuk kelas 1 yang masuk dari penerimaan siswa baru.
						</div>
					</div>
					<div class="row mb-3 row-status">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tahun Ajaran</label>
						<div class="col-sm-6">
							<?=options(['name' => 'id_tahun_ajaran'], $list_tahun_ajaran)?>
							<small>Tahun Ajaran status siswa. Misal jika Status dipilih "Masuk Baru" dan "Kelas 1" maka Tahun Ajaran dipilih Tahun Ajaran saat siswa masuk ke Kelas 1.</small>
						</div>
					</div>
					<div class="row mb-3 row-status">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tanggal Status</label>
						<div class="col-sm-6">
							<input type="text" class="form-control flatpickr" name="tgl_status" value="<?=date('d-m-Y')?>"/>
							<small>Tanggal perubahan status siswa terakhir</small>
						</div>
					</div>
				<?php
				}?>
				<div class="px-4 py-3 mt-4 mb-2 bg-lightgrey border">
					<h5>Orang Tua</h5>
				</div>
				<hr/>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Ayah</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="nama_ayah" value="<?=set_value('nama_ayah', @$siswa['nama_ayah'])?>"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Ibu</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="nama_ibu" value="<?=set_value('nama_ibu', @$siswa['nama_ibu'])?>"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">No HP</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="no_hp" value="<?=set_value('no_hp', @$siswa['no_hp'])?>"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Status Orangtua</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_status_orangtua'], $list_status_orangtua, set_value('id_status_orangtua', @$siswa['id_status_orangtua']))?>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<button type="submit" name="submit" value="submit" class="btn btn-primary submit-data">Submit</button>
						<input type="hidden" name="id" id="id-siswa" value="<?=@$_GET['id']?>"/>
						<?php
						if ($action == 'add') {
							echo '<button type="button" class="btn btn-danger clear-form">Clear Form</button>';
						}
						?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>