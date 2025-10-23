<?php 

$column =[
			'ignore_urut' => 'No'
			, 'nama' => 'Nama'
			, 'nama_jabatan' => 'Jabatan'
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

<table id="pegawwai-modal-table-result" class="table display table-striped table-bordered table-hover" style="width:100%">
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
<span id="pegawwai-modal-dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
<span id="pegawwai-modal-dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
<span id="pegawwai-modal-dataTables-url" style="display:none"><?=base_url() . '/pegawai-utang/getDataDTPegawai'?></span>