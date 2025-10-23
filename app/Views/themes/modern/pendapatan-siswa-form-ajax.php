<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal form-siswa px-3" enctype="multipart/form-data">
	<div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Nama Siswa</label>
			<div class="col-sm-9">
				<div class="input-group">
					<input class="form-control" type="text" id="nama-siswa" name="nama_siswa" disabled="disabled" readonly="readonly" value="<?=set_value('nama_siswa', @$bayar['nama'] ?: '')?>" required="required"/>
					<button type="button" class="btn btn-outline-secondary cari-siswa"><i class="fas fa-search"></i> Cari</button>
					<a href="<?=base_url()?>/siswa/add" target="_blank" class="btn btn-outline-success add-customer" id="add-customer" href="javascript:void(0)"><i class="fas fa-plus"></i> Tambah</a>
				</div>
				<input type="hidden" name="id_siswa" id="id-siswa" value="<?=set_value('id_siswa', @$bayar['id_siswa'])?>"/>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">No. Invoice</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="no_invoice" id="no-invoice" value="<?=set_value('no_invoice', @$bayar['no_invoice'])?>" readonly="readonly"/>
				<small class="text-muted">Digenerate otomatis oleh sistem</small>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Tanggal Invoice</label>
			<div class="col-sm-9">
				<input class="form-control flatpickr tanggal-invoice flatpickr" type="text" name="tgl_invoice" value="<?=@$bayar['tgl_invoice'] ? format_tanggal(@$bayar['tgl_invoice'], 'dd-mm-yyyy') : date('d-m-Y')?>" required="required"/>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Tanggal Bayar</label>
			<div class="col-sm-9">
				<input class="form-control flatpickr tanggal-bayar flatpickr" type="text" name="tgl_bayar" value="<?=@$bayar['tgl_bayar'] ? format_tanggal(@$bayar['tgl_bayar'], 'dd-mm-yyyy') : date('d-m-Y')?>" required="required"/>
			</div>
		</div>
		<div class="form-group row mb-2">
			<label class="col-sm-3 col-form-label">Pembayaran</label>
			<div class="col-sm-9">
				<button type="button" class="add-item-pembayaran-siswa btn btn-success btn-xs"><i class="fas fa-plus me-2"></i>Add Item</button>
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
				<table style="width:100%' . $display . '" id="tabel-list-item-pendapatan" class="table table-stiped table-bordered mt-3">
					<thead>
						<tr>
							<th>No</th>
							<th>Nama Pembayaran</th>
							<th>Tagihan</th>
							<th style="width:130px">Nilai Bayar</th>
							<th>Kurang</th>
							<th></th>
						</tr>
					</thead>
					<tbody>';
						$no = 1;
					/* 	echo '<pre>';
						print_r($bayar); die; */
						// Barang
						$display = '';
						$sub_total = 0;
						if (empty($bayar['detail'])) {
							$bayar['detail'][] = [];
						}
						
						$list_bulan = nama_bulan();
						$range = range(date('Y'), date('Y')-4);
						
						$list_tahun = [];
						foreach ($range as $val) {
							$list_tahun[$val] = $val;
						}
						
						$total = 0;
						/* echo '<pre>';
						print_r($bayar['detail']);
						die; */
						$nama_bulan = nama_bulan();
						foreach ($bayar['detail'] as $val) 
						{
							$total += @$val['nilai_bayar'];
							$bulan = @$val['periode_bulan'] ? ' ' . $nama_bulan[$val['periode_bulan']] . ' ' . $val['periode_tahun'] : '';
							$tahun_ajaran = @$val['tahun_ajaran'] ? ' ' . $val['tahun_ajaran'] : '';
							$nilai_tagihan = @$val['nilai_tagihan'] ? $val['nilai_tagihan'] - $val['total_pembayaran'] + $val['nilai_bayar'] : '';
							$nilai_bayar = @$val['nilai_bayar'] ? $val['nilai_bayar'] : '';
							$nama_pembayaran = @$val['nama_pendapatan_jenis'] ? $val['nama_pendapatan_jenis'] . $bulan . $tahun_ajaran : '';
							$kurang = 0;
							if ($nilai_tagihan) {
								if ($nilai_tagihan > $val['nilai_bayar']) {
									$kurang = format_number($nilai_tagihan - $nilai_bayar);
								}
							}
						
							echo '<tr class="row-item-bayar">
								<td>' . $no . '</td>
								<td>' . $nama_pembayaran . '</td>
								<td class="text-end">' . format_number($nilai_tagihan) . '</td>
								<td><input class="form-control number item-nilai-bayar text-end" name="nilai_bayar[]" value="' . format_number($nilai_bayar) . '"/><textarea style="display:none" name="detail_jenis_pembayaran[]">' . json_encode($val) . '</textarea></td>
								<td class="text-end">' . $kurang . '</td>
								<td><button type="button" class="btn text-danger del-row"><i class="fas fa-times"></i></button></td>
							</tr>';
							
							$no++;
						}
						
						$total = $total ? format_number($total) : '';
						
						echo '
							<tfoot>
							<tr id="row-total-bayar">
								<td></td>
								<td colspan="2">Total</td>
								<td class="text-end fw-bold" style="padding-right:17px" id="total-item-nilai-bayar">' . $total . '</td>
								<td></td>
								<td></td>
							</tr>
							<tr id="row-total-bayar">
								<td></td>
								<td colspan="2"><div class="d-flex justify-content-between">Dibayar' . options(['name' => 'id_jenis_bayar', 'style' => 'width:auto'], $metode_pembayaran, set_value('id_jenis_bayar', @$bayar['id_jenis_bayar'])) . '</div></td>
								<td class="text-end">
									<input class="form-control number text-end" id="total-pembayaran" name="total_pembayaran" value="' . @format_number($bayar['total_pembayaran']) . '"/>
								</td>
								<td></td>
								<td></td>
							</tr>
							<tr id="row-total-bayar">
								<td></td>
								<td colspan="2">Kembali</td>
								<td class="text-end" style="padding-right:17px" id="kembali">' . @format_number($bayar['kembali']) . '</td>
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
	<input type="hidden" name="id" id="id-siswa-bayar" value="<?=set_value('id', @$bayar['id_siswa_bayar'])?>"/>
</form>