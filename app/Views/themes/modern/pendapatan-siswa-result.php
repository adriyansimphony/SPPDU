<div class="card">
	<div class="card-header">
		<h5 class="card-title">Pembayaran Siswa</h5>
	</div>
	
	<div class="card-body">
		<div class="d-flex justify-content-between">
			<div class="text-sm-start text-center">
				<?php if (has_permission('create')) { ?>
					<button class="btn btn-success btn-add-pendapatan-siswa btn-xs"><i class="fas fa-plus me-2"></i>Pembayaran Siswa</button>
				<?php } ?>
				<?php if (has_permission('delete_all')) { ?>
					<button class="btn btn-danger btn-delete-all-data btn-xs"><i class="fas fa-trash me-2"></i>Hapus Semua Data</button>
				<?php } ?>
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
					, 'no_invoice' => 'No.Invoice'
					, 'nama_pendapatan_jenis' => 'Pembayaran'
					, 'total_bayar' => 'Jml. Bayar'
					, 'tgl_bayar' => 'Tgl. Bayar'
					, 'ignore_action' => 'Action'
				];
		
		$settings['order'] = [7,'desc'];
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
		?>
		<div class="row">
			<div class="col-sm-6 mb-3 text-center text-sm-start">
				<div class="input-group d-flex flex-nowrap" style="width:auto">
					<div class="input-group-text">Tampilkan Siswa</div>
					<form method="get">
						<?=options(['name' => 'group_kelas', 'id' => 'option-kelas'], $option_kelas, @$_GET['kelas'])?>
					</form>
				</div>
			</div>
		</div>
		<table id="table-result-pendapatan-siswa" class="table display table-striped table-bordered table-hover" style="width:100%">
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
		<span id="dataTables-url" style="display:none"><?=base_url() . '/pendapatan-siswa/getDataDT?kelas=' . @$_GET['kelas']?></span>
	</div>
</div>