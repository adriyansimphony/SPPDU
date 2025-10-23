<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal form-pendapatan-lain px-3" enctype="multipart/form-data">
	<div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Nama Pegawai</label>
			<div class="col-sm-9">
				<div class="input-group">
					<input class="form-control" type="text" id="nama-pegawai" name="nama_pegawai" disabled="disabled" readonly="readonly" value="<?=set_value('nama_pegawai', @$bayar['nama'] ?: '')?>" required="required"/>
					<?php
					if (empty($_GET['id']) && empty($_GET['id_pegawai_utang'])) { ?>
						<button type="button" class="btn btn-outline-secondary cari-pegawai-utang"><i class="fas fa-search"></i> Cari</button>
						<?php
					}
					?>
				</div>
				<input type="hidden" name="id_pegawai" id="id-pegawai" value="<?=set_value('id_pegawai', @$bayar['id_pegawai'])?>"/>
			</div>
		</div>
		<div class="form-group row mb-3 row-invoice">
			<label class="col-sm-3 col-form-label">Invoice</label>
			<div class="col-sm-9">
				<div class="input-group">
					<span class="input-group-text">Nomor</span>
					<input class="form-control" type="text" name="no_invoice" id="no-invoice" value="<?=set_value('no_invoice', @$bayar['no_invoice'])?>" readonly="readonly"/>
					<span class="input-group-text">Tanggal</span>
					<input style="max-width:120px" class="form-control flatpickr tanggal-invoice flatpickr" type="text" name="tgl_invoice" value="<?=@$bayar['tgl_invoice'] ? format_tanggal(@$bayar['tgl_invoice'], 'dd-mm-yyyy') : date('d-m-Y')?>" required="required"/>
				</div>
				<small class="text-muted">Digenerate otomatis oleh sistem</small>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Tgl. Bayar</label>
			<div class="col-sm-9">
				<input class="form-control flatpickr tanggal-bayar flatpickr" type="text" name="tgl_bayar" value="<?=@$bayar['tgl_bayar'] ? format_tanggal(@$bayar['tgl_bayar'], 'dd-mm-yyyy') : date('d-m-Y')?>" required="required"/>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Keterangan</label>
			<div class="col-sm-9">
				<textarea class="form-control" name="keterangan[]"><?=@$bayar['keterangan']?></textarea>
			</div>
		</div>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label">Bayar</label>
			<div class="col-sm-9">
				<button class="btn btn-success btn-xs btn-cari-utang"><i class="fas fa-plus me-2"></i>Pilih Utang</button>
			</div>
		</div>
		<div class="form-group row mb-3">
			<div class="col-sm-12">
				<?php
				helper('html');
				
				$display = '';
				$table_visible = 1;
				if (empty($bayar)) {
					$display = ' ;display:none';
					$table_visible = 0;
				}
				
				echo '
				<table style="width:100%' . $display . '" id="tabel-list-item-utang" class="table table-stiped table-bordered mt-3">
					<thead>
						<tr>
							<th>No</th>
							<th>Utang</th>
							<th>Sudah Dibayar</th>
							<th>Nilai Bayar</th>
							<th>Kurang</th>
							<th></th>
						</tr>
					</thead>
					<tbody>';
						
						$no = 1;
						// Barang
						$display = '';
						$sub_total = 0;
						if (empty($bayar['detail'])) {
							$bayar['detail'][] = [];
						}
	
						$total = 0;
						foreach ($bayar['detail'] as $index => $val) 
						{
							// echo '<pre>'; print_r($bayar['detail']); die;
							$total += @$val['nilai_bayar'];
							$nilai_bayar = @$val['nilai_bayar'] ? $val['nilai_bayar'] : '';
							
							echo '<tr class="row-item-bayar">
								<td class="text-end">' . $no . '</td>
								<td class="text-end utang">' . @format_number($val['nilai_utang']) . '</td>
								<td class="text-end dibayar">' . @format_number($val['total_bayar']) . '</td>
								<td class="text-end"><input class="form-control number item-nilai-bayar text-end" style="max-width:100px; float:right" name="nilai_bayar[]" value="' . format_number($nilai_bayar) . '"/><textarea style="display:none" name="detail_utang[]">' . json_encode($val) . '</textarea></td>
								<td class="text-end kurang">' . @format_number($val['kurang']) . '</td>
								<td><button type="button" class="btn btn-outline-danger btn-xs del-row-bayar-utang"><i class="fas fa-times"></i></button></td>
							</tr>';
							
							$no++;
						}
						
						$total = $total ? format_number($total) : '';
						echo '
							<tfoot>
								<tr id="row-total-bayar">
									<td></td>
									<td colspan="2"><div class="d-flex justify-content-between">Total ' . options(['name' => 'id_jenis_bayar', 'style' => 'width:auto'], $metode_pembayaran, set_value('id_jenis_bayar', @$bayar['id_jenis_bayar'])) . '</div></td>
									<td class="text-end" style="padding-right:18px" id="total-item-nilai-bayar">' . $total . '</td>
									<td></td>
									<td></td>
								</tr>
							</tfoot>';
					echo '
					</tbody>
				</table>
				<input type="hidden" id="table-show" value="' . $table_visible . '"';
				?>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" id="id-pegawai-utang" value="<?=set_value('id', @$bayar['id_pegawai_utang'])?>"/>
</form>