<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
			helper ('html');
			echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
				'url' => $config->baseURL . 'siswa-bayar',
				'icon' => 'fa fa-arrow-circle-left',
				'label' => 'List Bayar'
			]);
		?>
		<hr/>
		<?php
		
		if (!empty($message)) {
			show_message($message);
		}
		
		if (!@$bayar['tgl_invoice']) {
			$bayar['tgl_invoice'] = date('d-m-Y');
		}
		?>
		<form method="post" action="" class="form-horizontal form-siswa" enctype="multipart/form-data">
			<div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">No. Invoice</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="no_invoice" id="no-invoice" value="<?=set_value('no_invoice', @$bayar['no_invoice'])?>" readonly="readonly"/>
						<small class="text-muted">Digenerate otomatis oleh sistem</small>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tanggal Invoice</label>
					<div class="col-sm-6">
						<input class="form-control flatpickr tanggal-invoice flatpickr" type="text" name="tgl_invoice" value="<?=set_value('tgl_invoice', format_tanggal(@$bayar['tgl_invoice'], 'dd-mm-yyyy'))?>" required="required"/>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Siswa</label>
					<div class="col-sm-6">
						<div class="input-group">
							<input class="form-control" type="text" id="nama-siswa" name="nama_siswa" disabled="disabled" readonly="readonly" value="<?=set_value('nama_siswa', @$bayar['nama_siswa'] ?: '')?>" required="required"/>
							<button type="button" class="btn btn-outline-secondary cari-siswa"><i class="fas fa-search"></i> Cari</button>
							<a class="btn btn-outline-success add-customer" id="add-customer" href="javascript:void(0)"><i class="fas fa-plus"></i> Tambah</a>
						</div>
						<input class="form-control" type="hidden" name="id_siswa" id="id-siswa" value="<?=set_value('id_siswa', @$siswa['id_siswa'])?>" required="required"/>
					</div>
				</div>
				
				
				<div class="form-group row mb-3">
					<div class="col-sm-8">
						<?php
						// echo $penjualan['jenis_bayar']; die;
						$display = '';
						if (empty($bayar)) {
							// $display = ' ;display:none';
						}
						
						echo '
						<table style="width:100%' . $display . '" id="list-bayar" class="table table-stiped table-bordered mt-3">
							<thead>
								<tr>
									<th>No</th>
									<th>Jenis</th>
									<th>Bulan</th>
									<th>Nilai Bayar</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>';
								$no = 1;
								
								// Barang
								$display = '';
								$sub_total = 0;
								if (empty($bayar['detail'])) {
									// $display = ' style="display:none"';
									$bayar['detail'][] = [];
								}
								// echo '<pre>'; print_r($barang); die;
								$list_bulan = nama_bulan();
								$list_tahun = range(date('Y'), date('Y')-4);
								
								foreach ($bayar['detail'] as $val) {
									echo '<tr>
										<td>1</td>
										<td>' . options(['name' => 'id_jenis_kas_masuk[]', 'style' => 'width:auto'], $jenis_pembayaran)
												
										
										. '</td>
										<td>' . 
										
										'<div class="d-flex">'
												. options(['name' => 'spp_bulan[]', 'style' => 'width:auto'], $list_bulan)
												. options(['name' => 'spp_tahun[]', 'style' => 'width:auto'], $list_tahun)
												. '</div>'
										. '</td>
										<td><input class="form-control text-end number" name="nilai_bayar[]"/></td>
										<td><button type="button" class="btn text-danger del-row"><i class="fas fa-times"></i></button></td>
									</tr>';
								}
							echo '
							</tbody>
						</table>';
						?>
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