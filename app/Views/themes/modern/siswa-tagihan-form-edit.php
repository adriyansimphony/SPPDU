<?php 
	helper ('html');
?>
<form method="post" action="" class="form-horizontal form-siswa px-3">
	<div class="row mb-3">
		<label class="col-sm-4">Nama Siswa</label>
		<div class="col-sm-8">
			<?=$tagihan['nama']?>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-sm-4">NIS</label>
		<div class="col-sm-8">
			<?=$tagihan['nis']?>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-sm-4">Nama Tagihan</label>
		<div class="col-sm-8">
			<?=$tagihan['nama_pendapatan_jenis']?>
		</div>
	</div>
	<?php
	$nama_bulan = nama_bulan();
	if ($tagihan['using_periode'] == 'Y') 
	{
		switch ($tagihan['jenis_periode']) {
			case 'bulan';
				$title = 'Bulan';
				$value = $nama_bulan[$tagihan['periode_bulan']] . ' ' . $tagihan['periode_tahun'];
				break;
			case 'tahun';
				$title = 'Tahun';
				$value = $tagihan['periode_tahun'];
				break;
			case 'tahun_ajaran';
				$title = 'Tahun Ajaran';
				$value = $tagihan['tahun_ajaran'];
				break;
		}
		
		echo '<div class="row mb-3">
			<label class="col-sm-4">' . $title . '</label>
			<div class="col-sm-8">
				' . $value . '
			</div>
		</div>';
	}
	?>

	<div class="row mb-3">
		<label class="col-sm-4">Nilai Tagihan</label>
		<div class="col-sm-8">
			<!-- menampilkan nilai sebelum potongan: tagihan['nilai_tagihan'] + potongan -->
			<input name="nilai_tagihan" class="form-control number" id="nilai-tagihan" 
				value="<?=format_number( (int)$tagihan['nilai_tagihan'] + (int)($tagihan['potongan'] ?? 0) )?>"/>
			<small>Nilai sebelum Subsidi</small>
		</div>
	</div>

	<div class="row mb-3">
		<label class="col-sm-4">Subsidi</label>
		<div class="col-sm-8">
			<input name="potongan" class="form-control number" id="potongan" 
				value="<?=format_number($tagihan['potongan'] ?? 0)?>"/>
			<small>Masukkan jumlah Subsidi jika ada</small>
		</div>
	</div>

	<div class="row mb-3">
		<label class="col-sm-4">Total Setelah Subsidi</label>
		<div class="col-sm-8">
			<input id="total-setelah" class="form-control" readonly 
				value="<?=format_number((int)$tagihan['nilai_tagihan'])?>"/>
		</div>
	</div>

	<div class="row mb-3">
		<label class="col-sm-4">Keterangan</label>
		<div class="col-sm-8">
			<textarea name="keterangan" class="form-control"><?=$tagihan['keterangan']?></textarea>
		</div>
	</div>

	<input type="hidden" name="id" value="<?=$tagihan['id_siswa_tagihan']?>"/>
</form>

<script>
function toInt(v){ return parseInt(String(v).replace(/\./g,'')) || 0; }
function formatNumber(n){ return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); }

function updateTotalEdit(){
  const nilai = toInt(document.getElementById('nilai-tagihan').value);
  const pot = toInt(document.getElementById('potongan').value);
  const total = Math.max(0, nilai - pot);
  document.getElementById('total-setelah').value = formatNumber(total);
}

document.getElementById('nilai-tagihan').addEventListener('input', updateTotalEdit);
document.getElementById('potongan').addEventListener('input', updateTotalEdit);
updateTotalEdit();
</script>
