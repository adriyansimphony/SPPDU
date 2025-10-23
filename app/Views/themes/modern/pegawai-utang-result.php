<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	
	<div class="card-body">
		<div class="text-center text-sm-start">
			<?php if (has_permission('create')) { ?>
				<a href="<?=current_url()?>/add" class="btn btn-success btn-xs btn-add"><i class="fa fa-plus me-1"></i> Tambah Utang</a>
				<button type="button" class="btn btn-primary btn-xs btn-bayar-utang me-1"><i class="fa fa-plus pe-1"></i> Tambah Bayar</a>
			<?php } if (has_permission('delete_all')) { ?>
				<button class="btn btn-danger btn-delete-all-data btn-xs" <?=$jml_data ? '' : 'disabled'?>><i class="fas fa-trash me-2"></i>Hapus Semua Data</button>
			<?php } ?>
		</div>
		<hr/>
		<?php 
		if (!empty($msg)) {
			show_alert($msg);
		}
			
		$column =[
					'ignore_urut' => 'No'
					, 'nama' => 'Nama Pegawai'
					, 'nilai_utang' => 'Nilai Utang'
					, 'tgl_utang' => 'Tanggal Utang'
					, 'total_pembayaran' => 'Bayar'
					, 'status' => 'Status'
					, 'ignore_action_bayar' => 'Bayar'
					, 'ignore_action' => 'Action'
				];
		
		$settings['order'] = [1,'asc'];
		$index = 0;
		$th = '';
		foreach ($column as $key => $val) {
			$th .= '<th>' . $val . '</th>'; 
			if (strpos($key, 'ignore_search') !== false) {
				$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
			}
			$index++;
		}
		$disabled = !$jml_data ? 'disabled="disabled"' : '';
		?>
		<div class="row">
			<div class="col-sm-6 mb-3 text-center text-sm-start">
				
			</div>
			<div class="col-sm-6 mb-3 text-center text-sm-end" style="text-align:right">
				<div class="btn-group">
					<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-excel" <?=$disabled?>><i class="fas fa-file-excel me-2"></i>XLSX</button>
				</div>
			</div>
		</div>
		<table id="table-result-utang-pegawai" class="table display table-striped table-bordered table-hover" style="width:100%">
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

		<?php
			foreach ($column as $key => $val) {
				$column_dt[] = ['data' => $key];
			}
		?>
		<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
		<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
		<span id="dataTables-url" style="display:none"><?=base_url() . '/pegawai-utang/getDataDT'?></span>
	</div>
</div>