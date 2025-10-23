<?php
helper('html');
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Setting Invoice</h5>
	</div>
	<div class="card-body">
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		?>
		<form method="post" action="" style="max-width: 750px" class="form-horizontal p-3" enctype="multipart/form-data">
			<div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Logo</label>
					<div class="col-sm-9">
						Logo yang digunakan adalah logo pada menu Identitas Sekolah
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Kota Tanda Tangan</label>
					<div class="col-sm-9">
						<input type="text" name="kota_tandatangan" class="form-control" value="<?=set_value('kota_tandatangan', @$setting_invoice['kota_tandatangan'])?>"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Tampilkan Riwayat Pembayaran</label>
					<div class="col-sm-9">
						<?=options(['name' => 'tampilkan_riwayat_bayar'], ['N' => 'Tidak', 'Y' => 'Ya'], @$setting_invoice['tampilkan_riwayat_bayar'])?>
						<small class="text-muted">Jika Ya, maka riwayat pembayaran atas tagihan yang dibayar akan ditampilkan dibawah</small>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Gunakan Footer</label>
					<div class="col-sm-9">
						<?=options(['name' => 'gunakan_footer', 'id' => 'gunakan-footer'], ['N' => 'Tidak', 'Y' => 'Ya'], @$setting_invoice['gunakan_footer'])?>
					</div>
				</div>
				<?php
				$display = @$setting_invoice['gunakan_footer'] == 'Y' ? '' : ' style="display: none"';
				?>
				<div class="row mb-3 row-footer" <?=$display?>>
					<label class="col-sm-3 col-form-label">Posisi Footer</label>
					<div class="col-sm-9">
						<?=options(['name' => 'posisi_footer'], ['paling_bawah' => 'Paling Bawah Halaman', 'setelah_isi_invoice' => 'Setelah Isi Invoice'], @$setting_invoice['posisi_footer'])?>
					</div>
				</div>
				<div class="row mb-3 row-footer" <?=$display?>>
					<label class="col-sm-3 col-form-label">Footer Text</label>
					<div class="col-sm-9">
						<textarea name="footer_text" class="form-control"><?=set_value('footer_text', @$setting_invoice['footer_text'])?></textarea>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-sm-12">
						<div class="px-4 py-2 bg-lightgrey">Invoice</div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Nomor Invoice</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="no_invoice" value="<?=@$setting_invoice['no_invoice']?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Jumlah Digit</label>
					<div class="col-sm-9">
						<?=options(['name' => 'jml_digit_invoice'], ['4' => '4', '5' => '5', '6' => '6'], @$setting_invoice['jml_digit'])?>
						<small class="text-muted">Jumlah digit nomor invoice, contoh 6 digit: 000001</small>
					</div>
				</div>
				<button type="submit" class="btn btn-primary" name="submit" value="submit">Submit</button>
			</div>
			<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
		</form>
	</div>
</div>