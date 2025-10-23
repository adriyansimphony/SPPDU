<div class="card">
	<div class="card-header">
		<h5 class="card-title">Pendapatan Lain</h5>
	</div>
	
	<div class="card-body">
		<div class="d-flex justify-content-between">
			<div class="text-sm-start text-center">
				<?php
					$disabled = $jml_data ? '' : ' disabled="disabled"';
				?>
				<button class="btn btn-success btn-add-pendapatan-lain btn-xs"><i class="fas fa-plus me-2"></i>Pendapatan Lain</button>
				<?php if (has_permission('delete_all')) { ?>
					<button class="btn btn-danger btn-delete-all-data btn-xs" <?=$disabled?>><i class="fas fa-trash me-2"></i>Hapus Semua Data</button>
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
					, 'nama_pembayar' => 'Nama'
					, 'no_invoice' => 'No.Invoice'
					, 'nama_pendapatan_jenis' => 'Pembayaran'
					, 'nilai_bayar' => 'Jml. Bayar'
					, 'tgl_bayar' => 'Tgl. Bayar'
					, 'ignore_action' => 'Action'
				];
		
		$settings['order'] = [5,'desc'];
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
		<table id="table-result-pendapatan-lain" class="table display table-striped table-bordered table-hover" style="width:100%">
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
		<span id="dataTables-url" style="display:none"><?=base_url() . '/pendapatan-lain/getDataDT'?></span>
	</div>
</div>