<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
			helper('html');
			helper('builtin/util');
			if (in_array('create', $pegawai_permission)) {
				echo btn_link(['attr' => ['class' => 'btn btn-success btn-xs'],
					'url' => $module_url . '/add',
					'icon' => 'fa fa-plus',
					'label' => 'Tambah Pegawai'
				]);
				
				echo btn_link(['attr' => ['class' => 'btn btn-success btn-xs'],
					'url' => $module_url . '/uploadexcel',
					'icon' => 'fas fa-arrow-up-from-bracket',
					'label' => 'Upload Excel'
				]);
			}
			
			echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
				'url' => $module_url,
				'icon' => 'fa fa-arrow-circle-left',
				'label' => 'Daftar Pegawai'
			]);
		?>
		<hr/>
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		
		if (@$pegawai_form['tgl_lahir']) {
			$exp = explode('-', $pegawai_form['tgl_lahir']);
			$tgl_lahir = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		} else {
			$tgl_lahir = date('d-m-Y');
		}
		
		if (@$pegawai_form['tgl_bergabung']) {
			$exp = explode('-', $pegawai_form['tgl_bergabung']);
			$tgl_bergabung = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		} else {
			$tgl_bergabung = date('d-m-Y');
		}
		?>
		<form method="post" action="" class="form-horizontal form-pegawai" enctype="multipart/form-data">
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Foto</label>
				<div class="col-sm-5">
					<?php
			
					if (!empty($pegawai_form['foto']) ) 
					{
						$note = '';
						if (file_exists(ROOTPATH . 'public/images/pegawai/' . $pegawai_form['foto'])) {
							$image = $config->baseURL . 'public/images/pegawai/' . $pegawai_form['foto'];
						} else {
							$image = $config->baseURL . 'public/images/pegawai/noimage.png';
							$note = '<small><b>Note</strong>: File <strong>public/images/pegawai/' . $pegawai_form['foto'] . '</strong> tidak ditemukan</small>';
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
			
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="nama" value="<?=set_value('nama', @$pegawai_form['nama'])?>" placeholder="" required="required"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Jenis Kelamin</label>
				<div class="col-sm-5">
					<?=options(['name' => 'jenis_kelamin'], ['L' => 'Laki-Laki', 'P' => 'Perempuan'], set_value('jenis_kelamin', @$siswa['jenis_kelamin']))?>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Agama</label>
				<div class="col-sm-5">
					<?=options(['name' => 'id_agama'], $list_agama, set_value('id_agama', @$pegawai_form['id_agama']))?>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">No. HP</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="no_hp" value="<?=set_value('no_hp', @$pegawai_form['no_hp'])?>" required="required"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">NIP</label>
				<div class="col-sm-5">
					<?php 
					$readonly = 'readonly="readonly" class="disabled"';
					if (@$pegawai_permission['update_all']) {
						$readonly = '';
					}
					?>
					<input class="form-control" type="text" name="nip_pegawai" <?=$readonly?> value="<?=set_value('nip_pegawai', @$pegawai_form['nip_pegawai'])?>" placeholder="" required="required"/>
					<input type="hidden" name="nip_pegawai_lama" value="<?=set_value('nip_pegawai', @$pegawai_form['nip_pegawai'])?>" />
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">NIK</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="nik" value="<?=set_value('nik', @$pegawai_form['nik'])?>"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Email</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="email" value="<?=set_value('email', @$pegawai_form['email'])?>" placeholder="" required="required"/>
					<input type="hidden" name="email_lama" value="<?=set_value('email', @$pegawai_form['email'])?>" />
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tempat Lahir</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="tempat_lahir" value="<?=set_value('tempat_lahir', @$pegawai_form['tempat_lahir'])?>" placeholder="" required="required"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tgl. Lahir</label>
				<div class="col-sm-5">
					<input class="form-control flatpickr" type="text" name="tgl_lahir" value="<?=set_value('tgl_lahir', @$tgl_lahir)?>" required="required"/>
				</div>
			</div>
			<?php
			if (@$pegawai_permission['update_all']) {
			?>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Jabatan</label>
					<div class="col-sm-5">
						<?=options(['name' => 'id_jabatan[]', 'class' => 'select2', 'multiple' => 'multiple'], $list_jabatan, set_value('id_jabatan', @$pegawai_form['id_jabatan']))?>
					</div>
				</div>
			<?php
			}
			?>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Alamat</label>
				<div class="col-sm-5">
					<textarea class="form-control" name="alamat"><?=set_value('alamat', @$pegawai_form['alamat'])?></textarea>
				</div>
			</div>
			<div class="form-group row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Propinsi</label>
				<div class="col-sm-5">
					<?=options(['name' => 'id_wilayah_propinsi', 'class' => 'propinsi select2'], $propinsi, set_value('id_wilayah_propinsi', $id_wilayah_propinsi) )?>
				</div>
			</div>
			<div class="form-group row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kabupaten</label>
				<div class="col-sm-5">
					<?=options(['name' => 'id_wilayah_kabupaten', 'class' => 'kabupaten select2'], $kabupaten, set_value('id_wilayah_kabupaten', $id_wilayah_kabupaten))?>
				</div>
			</div>
			<div class="form-group row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kecamatan</label>
				<div class="col-sm-5">
					<?=options(['name' => 'id_wilayah_kecamatan', 'class' => 'kecamatan select2'], $kecamatan, set_value('id_wilayah_kecamatan',$id_wilayah_kecamatan))?>
				</div>
			</div>
			<div class="form-group row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kelurahan</label>
				<div class="col-sm-5" style="position:relative">
					<?=options(['name' => 'id_wilayah_kelurahan', 'class' => 'kelurahan select2'], $kelurahan, set_value('id_wilayah_kelurahan', $id_wilayah_kelurahan))?>
				</div>
			</div>
			<?php
			if (@$pegawai_permission['update_all']) {
				?>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Status Akun</label>
					<div class="col-sm-5">
						<?php echo options(['name' => 'status'], ['active' => 'Aktif', 'suspended' => 'Suspended'], set_value('status', @$pegawai_form['status'])); ?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Role</label>
					<div class="col-sm-5">
						<?php
						foreach ($roles as $key => $val) {
							$role_options[$val['id_role']] = $val['judul_role'];
						}
						
						if (!empty($pegawai_form['role'])) {
							foreach ($pegawai_form['role'] as $val) {
								$id_role_selected[] = $val['id_role'];
							}
						}
						
						if (!empty($_POST) && empty($_POST['id_role'])) {
							$selected = '';
						} else {
							$selected = set_value('id_role', @$id_role_selected);
						}
						
						echo options(['name' => 'id_role[]', 'multiple' => 'multiple', 'class' => 'select2 select-role'], $role_options, set_value('id_role', @$id_role_selected));
						?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Halaman Default</label>
					<div class="col-sm-5">
						<?php
						if (empty(@$pegawai_form['default_page_type'])) {
							$pegawai_form['default_page_type'] = 'id_module';
							$pegawai_form['id_module'] = 5;
						}
						$default_page_type = set_value('option_default_page', @$pegawai_form['default_page_type']);
						?>
						<?=options(['name' => 'option_default_page', 'id' => 'option-default-page', 'class' => 'mb-2'], ['url' => 'URL', 'id_module' => 'Module', 'id_role' => 'Role'], $default_page_type )?>
						<?php
						$display_url = $default_page_type == 'url' ? '' : ' style="display:none"';
						$display_module = $default_page_type == 'id_module' ? '' : ' style="display:none"';
						$display_role = $default_page_type == 'id_role' ? '' : ' style="display:none"';
						
						?>
						<div class="default-page-url default-page" <?=$display_url?>>
							<input type="text" class="form-control" name="default_page_url" value="<?=set_value('default_page_url', @$pegawai_form['default_page_url'])?>"/>
							<small>Gunakan {{BASE_URL}} untuk menggunakan base url aplikasi, misal: {{BASE_URL}}builtin/pegawai/edit?id=1</small>
						</div>
						<div class="default-page-id-module default-page" <?=$display_module?>>
							<?php
							foreach ($list_module as $val) {
								$options[$val['id_module']] = $val['nama_module'] . ' - ' . $val['judul_module'];
							}
							
							if (empty(@$pegawai_form['default_page_id_module'])) {
								@$pegawai_form['default_page_id_module'] = 5;
							}
							
							echo options(['name' => 'default_page_id_module'], $options, set_value('default_page_id_module', @$pegawai_form['default_page_id_module'])); 
							?>
							<span class="text-muted">Pastikan pegawai memiliki hak akses ke module</span>
						</div>
						<?php
						$default_page_role = [];
						if (!empty($roles)) {
							foreach ($roles as $val) {
								$default_page_role[$val['id_role']] = $val['judul_role'];
							}
						}
						if (!$default_page_role) {
							$default_page_role = ['' => '-- Pilih Role --'];
						}
						?>
						<div class="default-page-id-role default-page" <?=$display_role?>>
							<?=options(['name' => 'default_page_id_role'], $default_page_role, set_value('default_page_id_role', @$pegawai_form['default_page_id_role']));?>
							<small>Halaman default sama dengan halaman default <a title="Halaman Role" href="<?=base_url() . '/builtin/role'?>" target="blank">role</a></small>
						</div>
					</div>
				</div>
				<?php
				if (!empty($_GET['id'])) { ?>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Ubah Password</label>
						<div class="col-sm-5">
							<?= options(['name' => 'option_ubah_password', 'id' => 'option-ubah-password'], ['N' => 'Tidak', 'Y' => 'Ya'], set_value('option_ubah_password', '')) ?>
						</div>
					</div>
					<?php
				}
				$display = (!empty($_POST['option_ubah_password']) && $_POST['option_ubah_password'] == 'Y') || empty($_GET['id']) ? '' : ' style="display:none"';
					?>
					
				<div id="password-container" <?=$display?>>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Password Baru</label>
						<div class="col-sm-5">
							<input class="form-control" type="password" name="password" value="<?=set_value('password', '')?>"/>
						</div>
					</div>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Ulangi Password Baru</label>
						<div class="col-sm-5">
							<input class="form-control" type="password" name="ulangi_password" value="<?=set_value('ulangi_password', '')?>"/>
						</div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Status Pegawai</label>
					<div class="col-sm-5">
						<?php 
						if (!empty($_GET['id'])) {
						?>
							<button type="button" class="btn btn-success btn-xs btn-add-status"><i class="fas fa-plus me-2"></i>Tambah</button>
							<hr/>
							<?php
							$column =[
										'nama_status_pegawai' => 'Status'
										, 'tgl_status' => 'Tanggal'
										, 'keterangan' => 'Keterangan'
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
									$settings['order'] = [1,'desc'];
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
								<span id="dataTablesRiwayat-url" style="display:none"><?=base_url() . '/builtin/pegawai/getDataDTPegawaiRiwayatStatus?id=' . $_GET['id']?></span>
							</table>
						<?php
						} else {
						?>
							<div class="input-group">
								<span class="input-group-text">
									Mulai Bergabung
								</span>
								<input type="text" class="form-control flatpickr" value="<?=date('d-m-Y')?>"/>
							</div>
						
						<?php
						}?>
					</div>
				</div>
			<?php
			}
			?>
			<div class="row">
				<div class="col-sm-8">
					<button type="submit" name="submit" value="submit" class="btn btn-primary submit-data">Submit</button>
					<input type="hidden" name="id" id="id-pegawai" value="<?=@$pegawai_form['id_pegawai']?>"/>
					<?php
					if ($action == 'add') {
						echo '<button type="button" class="btn btn-danger clear-form">Clear Form</button>';
					}
					?>
				</div>
			</div>
		</form>
	</div>
</div>