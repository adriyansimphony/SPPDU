<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
			helper ('html');
		if (!empty($message)) {
			show_message($message);
		}
		?>
		<form method="post" action="" id="form-setting" enctype="multipart/form-data">
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Sekolah</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="nama_sekolah" value="<?=set_value('nama_sekolah', @$sekolah['nama_sekolah'])?>" required="required"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Email</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="email" value="<?=set_value('email', @$sekolah['email'])?>" required="required"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">No. Telepon</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="no_telp" value="<?=set_value('no_telp', @$sekolah['no_telp'])?>" required="required"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">No. HP/WA</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="no_hp" value="<?=set_value('no_hp', @$sekolah['no_hp'])?>" required="required"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Alamat</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="alamat_sekolah" value="<?=set_value('alamat_sekolah', @$sekolah['alamat_sekolah'])?>" required="required"/>
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
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Logo</label>
				<div class="col-sm-5">
					<?php
			
					if (!empty($sekolah['logo']) ) 
					{
						$note = '';
						if (file_exists(ROOTPATH . 'public/images/sekolah/' . $sekolah['logo'])) {
							$image = $config->baseURL . 'public/images/sekolah/' . $sekolah['logo'];
						} else {
							$image = $config->baseURL . 'public/images/sekolah/noimage.png';
							$note = '<small><b>Note</strong>: File <strong>public/images/sekolah/' . $sekolah['logo'] . '</strong> tidak ditemukan</small>';
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
					<input type="hidden" class="logo-delete-img" name="logo_delete_img" value="0">
					<input type="hidden" class="logo-max-size" name="logo_max_size" value="300000"/>
					<input type="file" class="file form-control" name="logo">
						<?php if (!empty($form_errors['logo'])) echo '<small class="alert alert-danger">' . $form_errors['logo'] . '</small>'?>
						<small class="small" style="display:block">Maksimal 300Kb, Minimal 100px x 100px, Tipe file: .JPG, .JPEG, .PNG</small>
					<div class="upload-file-thumb"><span class="file-prop"></span></div>
				</div>
			</div>
			<div class="px-4 py-3 my-4 bg-lightgrey border">
				<h5>Tanda Tangan</h5>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kota Tandatangan</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="kota_tandatangan" value="<?=set_value('kota_tandatangan', @$sekolah['kota_tandatangan'])?>"/>
				</div>
			</div>
			<div class="px-4 py-3 my-4 bg-lightgrey border">
				<h5>Sosial Media</h5>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Website</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="website" value="<?=set_value('website', @$sekolah['website'])?>"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Facebook</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="facebook" value="<?=set_value('facebook', @$sekolah['facebook'])?>"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Youtube</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="youtube" value="<?=set_value('youtube', @$sekolah['youtube'])?>"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Telegram</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="telegram" value="<?=set_value('telegram', @$sekolah['telegram'])?>"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Twitter</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="twitter" value="<?=set_value('twitter', @$sekolah['twitter'])?>"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Instagram</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="instagram" value="<?=set_value('instagram', @$sekolah['instagram'])?>"/>
				</div>
			</div>
				
			<div class="row">
				<div class="col-sm-5">
					<button type="submit" name="submit" id="btn-submit" value="submit" class="btn btn-primary">Submit</button>
					<input type="hidden" name="id" value="<?=$sekolah['id_identitas_sekolah']?>">
				</div>
			</div>
		</form>
	</div>
</div>