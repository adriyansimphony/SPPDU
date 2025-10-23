<?php 

$column =[
			'ignore_urut' => 'No'
			, 'nama' => 'Nama'
			, 'nilai_utang' => 'Utang'
			, 'total_bayar' => 'Bayar'
			, 'kurang' => 'Kurang'
			, 'tgl_utang' => 'Tgl. Utang'
			, 'ignore_pilih' => 'Pilih'
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
<div class="modal-panel-header"></div>
<table id="list-utang-table-result" class="table display table-striped table-bordered table-hover" style="width:100%">
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
$id_lokasi_usaha = '';
if (!empty($_GET['id_lokasi_usaha'])) {
	$id_lokasi_usaha = '?id_lokasi_usaha=' . $_GET['id_lokasi_usaha'];
}
?>
<span id="list-utang-dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
<span id="list-utang-dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
<span id="list-utang-dataTables-url" style="display:none"><?=base_url() . '/pegawai-utang/getDataDTUtang?id=' . $_GET['id']?></span>