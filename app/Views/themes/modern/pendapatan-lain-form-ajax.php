<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal form-pendapatan-lain px-3" enctype="multipart/form-data">
	<div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Nama</label>
			<div class="col-sm-9">
				<div class="input-group">
					<input class="form-control" type="text" id="nama-pembayar" name="nama_pembayar" disabled="disabled" readonly="readonly" value="<?=set_value('nama_pembayar', @$bayar['nama_pembayar'] ?: '')?>" required="required"/>
					<button type="button" class="btn btn-outline-secondary cari-pembayar"><i class="fas fa-search"></i> Cari</button>
					<a href="<?=base_url()?>/pendapatan-lain/add" target="_blank" class="btn btn-outline-success btn-add-pembayar" id="add-pembayar" href="javascript:void(0)"><i class="fas fa-plus"></i> Tambah</a>
				</div>
				<input type="hidden" name="id_pembayar" id="id-pembayar" value="<?=set_value('id_pembayar', @$bayar['id_pembayar'])?>"/>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Invoice</label>
			<div class="col-sm-9">
				<?=options(['name' => 'using_invoice',  'id' => 'using-invoice'], ['N' => 'Tidak', 'Y' => 'Ya'], @$bayar['using_invoice'])?>
			</div>
		</div>
		<?php
		$display = @$bayar['using_invoice'] == 'Y' ? '' : ' style="display:none"';
		?>
		<div class="form-group row mb-3 row-invoice" <?=$display?>>
			<label class="col-sm-3 col-form-label">No. Invoice</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="no_invoice" id="no-invoice" value="<?=set_value('no_invoice', @$bayar['no_invoice'])?>" readonly="readonly"/>
				<small class="text-muted">Digenerate otomatis oleh sistem</small>
			</div>
		</div>
		<div class="form-group row mb-3 row-invoice" <?=$display?>>
			<label class="col-sm-3 col-form-label">Tgl. Invoice</label>
			<div class="col-sm-9">
				<input class="form-control flatpickr tanggal-invoice flatpickr" type="text" name="tgl_invoice" value="<?=@$bayar['tgl_invoice'] ? format_tanggal(@$bayar['tgl_invoice'], 'dd-mm-yyyy') : date('d-m-Y')?>" required="required"/>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Tgl. Terima</label>
			<div class="col-sm-9">
				<input class="form-control flatpickr tanggal-bayar flatpickr" type="text" name="tgl_bayar" value="<?=@$bayar['tgl_bayar'] ? format_tanggal(@$bayar['tgl_bayar'], 'dd-mm-yyyy') : date('d-m-Y')?>" required="required"/>
				<small>Tanggal terima pembayaran</small>
			</div>
		</div>
		<div class="form-group row mb-2">
			<label class="col-sm-3 col-form-label">Pembayaran</label>
			<div class="col-sm-9">
				<button type="button" class="add-item-pembayaran btn btn-success btn-xs"><i class="fas fa-plus me-2"></i>Add Item</button>
			</div>
		</div>
		<div class="form-group row mb-3">
			<div class="col-sm-12">
				<?php
				helper('html');
				// echo $penjualan['jenis_bayar']; die;
				$display = '';
				$table_visible = 1;
				if (empty($bayar)) {
					$display = ' ;display:none';
					$table_visible = 0;
				}
				
				echo '
				<table style="width:100%' . $display . '" id="tabel-list-item-pendapatan" class="table table-stiped table-bordered mt-3">
					<thead>
						<tr>
							<th>No</th>
							<th>Nama Pembayaran</th>
							<th style="width:130px">Nilai Bayar</th>
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
						foreach ($bayar['detail'] as $val) 
						{
							$total += @$val['nilai_bayar'];
							$nilai_bayar = @$val['nilai_bayar'] ? $val['nilai_bayar'] : '';
							
							echo '<tr class="row-item-bayar">
								<td>' . $no . '</td>
								<td>' . @$val['nama_pendapatan_jenis'] . '</td>
								<td><input class="form-control number item-nilai-bayar text-end" name="nilai_bayar[]" value="' . format_number($nilai_bayar) . '"/><textarea style="display:none" name="detail_jenis_pendapatan[]">' . json_encode($val) . '</textarea></td>
								<td><button type="button" class="btn text-danger del-row"><i class="fas fa-times"></i></button></td>
							</tr>';
							
							$no++;
						}
						
						$total = $total ? format_number($total) : '';
						
						echo '
							<tfoot>
							<tr id="row-total-bayar">
								<td></td>
								<td><div class="d-flex justify-content-between">Total ' . options(['name' => 'id_jenis_bayar', 'style' => 'width:auto'], $metode_pembayaran, set_value('id_jenis_bayar', @$bayar['id_jenis_bayar'])) . '</div></td>
								<td class="text-end fw-bold" style="padding-right:17px" id="total-item-nilai-bayar">' . $total . '</td>
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
	<input type="hidden" name="id" id="id-pendapatan" value="<?=set_value('id', @$bayar['id_pendapatan'])?>"/>
</form>