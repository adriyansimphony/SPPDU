<div class="card">
	<div class="card-header">
		<h5 class="card-title">Tagihan Siswa</h5>
	</div>
	
	<div class="card-body">
		<div class="d-flex justify-content-between">
			<div class="text-sm-start text-center">
				<button class="btn btn-success btn-add-tagihan btn-xs"><i class="fas fa-plus me-2"></i>Tambah Data</button>
				<button class="btn btn-danger btn-delete-all-data btn-xs"><i class="fas fa-trash me-2"></i>Hapus Semua Data</button>
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
					, 'nama_pendapatan_jenis' => 'Nama Tagihan'
					, 'nilai_tagihan' => 'Tagihan'
					, 'potongan' => 'Subsidi'
					, 'nilai_bayar_tagihan' => 'Dibayar'
					, 'kurang_bayar' => 'Kurang'
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
		$disabled = !$jml_tagihan ? 'disabled="disabled"' : '';
		?>
		<div class="row">
			<div class="col-sm-6 mb-3 text-center text-sm-start">
				<div class="input-group d-flex flex-nowrap" style="width:auto">
					<div class="input-group-text">Tampilkan Siswa</div>
					<form method="get">
						<?=options(['name' => 'group_kelas', 'id' => 'option-kelas-result'], $option_kelas, @$_GET['kelas'])?>
					</form>
				</div>
			</div>
			<div class="col-sm-6 mb-3 text-center text-sm-end" style="text-align:right">
				<div class="btn-group">
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
		<span id="dataTables-url" style="display:none"><?=base_url() . '/siswa-tagihan/getDataDT?kelas=' . @$_GET['kelas']?></span>
	</div>
</div>