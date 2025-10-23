<?php 

$column =[
			'ignore_urut' => 'No'
			, 'nilai_bayar' => 'Bayar'
			, 'tgl_bayar' => 'Tanggal Bayar'
			, 'no_invoice' => 'No. Invoice'
			, 'ignore_action' => 'Edit'
		];

$settings['order'] = [1,'asc'];
$index = 0;
$th = '';
foreach ($column as $key => $val) {
	$th .= '<th>' . $val . '</th>'; 
	if (strpos($key, 'ignore') !== false) {
		$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
	}
	$index++;
}

?>
<div class="modal-panel-header">Hutang: </div>
<div class="p-3">
<table id="jwdmodal-table-result" class="table display table-striped table-bordered table-hover" style="width:100%">
<thead>
	<tr>
		<?=$th?>
	</tr>
</thead>
</table>
</div>
<?php
	foreach ($column as $key => $val) {
		$column_dt[] = ['data' => $key];
	}
$id_lokasi_usaha = '';
if (!empty($_GET['id_lokasi_usaha'])) {
	$id_lokasi_usaha = '?id_lokasi_usaha=' . $_GET['id_lokasi_usaha'];
}
?>
<span id="jwdmodal-dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
<span id="jwdmodal-dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
<span id="jwdmodal-dataTables-url" style="display:none"><?=base_url() . '/pegawai-utang/getDataDTBayar?id=' . $_GET['id']?></span>