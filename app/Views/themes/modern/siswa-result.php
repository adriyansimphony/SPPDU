<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	
	<div class="card-body">
		<div class="d-flex justify-content-between">
			<div class="text-sm-center">
				<a href="<?=base_url()?>/siswa/add" class="btn btn-success btn-xs"><i class="fa fa-plus pe-1"></i> Tambah Data</a>
				<a href="<?=base_url()?>/siswa/uploadexcel" class="btn btn-success btn-xs"><i class="fas fa-arrow-up-from-bracket pe-1"></i> Upload Excel</a>
				<button class="btn btn-danger btn-delete-all-siswa btn-xs"><i class="fas fa-trash me-2"></i>Hapus Semua Siswa</button>
			</div>
			<div class="text-sm-center">
				
			</div>			
		</div>
		<hr/>
		<?php 
		if (!empty($msg)) {
			show_alert($msg);
		}
			
		$column =[
					'ignore_urut' => 'No'
					, 'ignore_foto' => 'Foto'
					, 'nama' => 'Nama'
					, 'tgl_lahir' => 'TTL'
					, 'alamat' => 'Alamat'
					, 'nisn' => 'NISN'
					, 'nis' => 'NIS'
					, 'nama_kelas' => 'Kelas'
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
		$disabled = !$jml_siswa ? 'disabled="disabled"' : '';
		?>
		<div class="row">
			<div class="col-sm-6 mb-3 text-center text-sm-start">
				<div class="input-group d-flex flex-nowrap" style="width:auto">
					<div class="input-group-text">Tampilkan Siswa</div>
					<form method="get">
						<?= options(['name' => 'id_kelas', 'id' => 'option-kelas'], $option_kelas, set_value('id_kelas'))?>
					</form>
				</div>
			</div>
			<div class="col-sm-6 mb-3 text-center text-sm-end" style="text-align:right">
				<div class="btn-group">
					<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-pdf" <?=$disabled?>><i class="fas fa-file-pdf me-2"></i>PDF</button>
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
		<span id="dataTables-url" style="display:none"><?=base_url() . '/siswa/getDataDT?id_kelas=' . @$_GET['id_kelas']?></span>
	</div>
</div>