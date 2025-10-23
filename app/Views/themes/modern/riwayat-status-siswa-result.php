<div class="card">
	<div class="card-header">
		<h5 class="card-title">Riwayat Status Siswa</h5>
	</div>
	
	<div class="card-body">
		<div class="d-flex justify-content-between">
			<div class="text-sm-center">
				<button class="btn btn-danger btn-delete-all-riwayat btn-xs"><i class="fas fa-trash me-2"></i>Hapus Semua Data</button>
			</div>
			<div class="text-sm-center">
				
			</div>			
		</div>
		<hr/>
		<?php 
		if (!empty($message)) {
			show_alert($message);
		}
			
		$column =[
					'ignore_urut' => 'No'
					, 'nama' => 'Nama'
					, 'nis' => 'NIS'
					, 'nama_kelas' => 'Kelas'
					, 'nama_status_siswa' => 'Status'
					, 'tgl_status' => 'Tgl. Status'
					, 'tahun_ajaran' => 'Tahun Ajaran'
					, 'keterangan' => 'Keterangan'
					, 'ignore_action' => 'Action'
				];
		
		$settings['order'] = [2,'asc'];
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
		$disabled = !$jml_status_siswa ? 'disabled="disabled"' : '';
		?>
		<div class="row">
			<div class="col-sm-6 mb-3 text-center text-sm-start">
				<div class="input-group d-flex flex-nowrap" style="width:auto">
					<div class="input-group-text">Tampilkan Siswa</div>
					<form method="get">
						<?= options(['name' => 'id_status_siswa', 'id' => 'option-status-siswa'], $option_status, set_value('id_status_siswa'))?>
					</form>
				</div>
			</div>
			<div class="col-sm-6 mb-3 text-center text-sm-end" style="text-align:right">
				<div class="btn-group" id="btn-export-container">
					<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-excel" <?=$disabled?>><i class="fas fa-file-excel me-2"></i>XLSX</button>
				</div>
			</div>
		</div>
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
		<?php
			foreach ($column as $key => $val) {
				$column_dt[] = ['data' => $key];
			}
		?>
		<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
		<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
		<span id="dataTables-url" style="display:none"><?=base_url() . '/riwayat-status-siswa/getDataDT?id=' . @$_GET['id']?></span>
	</div>
</div>