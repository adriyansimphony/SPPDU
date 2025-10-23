<div class="card">
	<div class="card-header">
		<h5 class="card-title">Pembayaran Siswa</h5>
	</div>
	
	<div class="card-body">
		<div class="d-flex justify-content-between">
			<div class="text-sm-start text-center">
				<?php
				if (has_permission('create')) { ?>
					<div class="btn-group">
						<button class="btn btn-success btn-sm dropdown-toggle btn-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="fas fa-plus me-2"></i>Tambah Data
						</button>
						<ul class="dropdown-menu">
							<li>
								<button class="dropdown-item btn-add-pendapatan-siswa btn-xs " ><i class="fas fa-plus me-2"></i>Pembayaran Siswa</a>
								<button class="dropdown-item btn-bayar-utang btn-xs" ><i class="fas fa-plus me-2"></i>Pembayaran Utang</a>
								<button class="dropdown-item btn-add-pendapatan-lain btn-xs" ><i class="fas fa-plus me-2"></i>Pendapatan Lain</a>
							</li>
						</ul>
					</div>
					<a href="<?=base_url(). '/pendapatan/upload-excel'?>" target="blank" class="btn btn-success btn-xs"><i class="fas fa-file-excel me-2"></i>Upload Excel</a>
				<?php
				}
				if (has_permission('delete_all')) { ?>
					<button class="btn btn-danger btn-delete-all-data-pendapatan btn-xs"><i class="fas fa-trash me-2"></i>Hapus Semua Data</button>
				<?php } ?>
			</div>
			<div class="text-sm-center">
				
			</div>			
		</div>
		<hr/>
		<span style="display:none" id="query-string"><?=http_build_query($_GET)?></span>
		<?php 
		if (!empty($message)) {
			show_alert($message);
		}
			
		$column =[
					'ignore_urut' => 'No'
					, 'nama' => 'Nama'
					, 'jenis_pendapatan' => 'Jenis'
					, 'no_invoice' => 'No.Invoice'
					, 'nama_pendapatan_jenis' => 'Pembayaran'
					, 'total_bayar' => 'Jml. Bayar'
					, 'tgl_bayar' => 'Tgl. Bayar'
					, 'ignore_action' => 'Action'
				];
		
		$settings['order'] = [6,'desc'];
		$index = 0;
		$th = '';
		foreach ($column as $key => $val) {
			$th .= '<th>' . $val . '</th>'; 
			if (strpos($key, 'ignore') !== false) {
				$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
			}
			$index++;
		}
		helper ('html');
		$disabled = !$jml_pendapatan ? 'disabled="disabled"' : '';
		?>
		<div class="row">
			<div class="col-sm-6 mb-3 text-center text-sm-start">
				<div class="input-group d-flex flex-nowrap" style="width:auto">
					<div class="input-group">
						<span class="input-group-text">Periode</span>
						<input type="text" class="form-control" name="daterange" id="daterange" value="<?=$start_date?> s.d. <?=$end_date?>" />
					
						<div class="input-group-text">Tampilkan</div>
						<form method="get">
							<?=options(['name' => 'tampilkan_pendapatan', 'id' => 'tampilkan-pendapatan'], ['' => 'Semua', 'siswa' => 'Siswa', 'utang pegawai' => 'Utang Pegawai', 'lainnya' => 'Lainnya'], @$_GET['tampilkan_pendapatan'])?>
						</form>
					</div>
				</div>
			</div>
			<div class="col-sm-6 mb-3 text-center text-sm-end" style="text-align:right">
				<div class="btn-group">
					<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-excel-pendapatan" <?=$disabled?>><i class="fas fa-file-excel me-2"></i>XLSX</button>
				</div>
			</div>
		</div>
		<div class="table-responsive">
			<table id="table-result" class="table display table-striped table-bordered table-hover" style="width:100%">
			<thead>
				<tr>
					<?=$th?>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<?=$th?>
				</tr>
			</tfoot>
			</table>
		</div>
		<?php
			foreach ($column as $key => $val) {
				$column_dt[] = ['data' => $key];
			}
		?>
		<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
		<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
		<span id="dataTables-url" style="display:none"><?=base_url() . '/pendapatan/getDataDT'?></span>
	</div>
</div>