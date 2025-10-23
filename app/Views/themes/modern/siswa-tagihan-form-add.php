<form method="post" action="" class="form-horizontal form-siswa px-3" enctype="multipart/form-data">
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Nama Tagihan</label>
		<div class="col-sm-9">
			<?php
			helper('html');
			?>
			<?=options(['name' => 'id_pendapatan_jenis', 'id' => 'id-pendapatan-jenis'], $jenis_tagihan)?>
		</div>
	</div>
	<div class="row mb-3 row-periode-bulan">
		<label class="col-sm-3 col-form-label">Periode</label>
		<div class="col-sm-9">
			<div class="input-group">
				<?php
				$nama_bulan = nama_bulan();
				$end_year = date('Y') + 1;
				$start_year = $end_year -5;
				$option_year = [];
				for ($i = $end_year; $i >= $start_year; $i--) {
					$option_year[$i] = $i;
				}
				echo options(['name' => 'bulan_awal', 'id' => 'bulan-awal', 'style' => 'width:75px'], $nama_bulan, 7)
					. options(['name' => 'tahun_awal', 'id' => 'tahun-awal'], $option_year, date('Y'))
					. '<span class="input-group-text">s.d.</span>'
					. options(['name' => 'bulan_akhir', 'id' => 'bulan-akhir', 'style' => 'width:75px'], $nama_bulan, 7)
					. options(['name' => 'tahun_akhir', 'id' => 'tahun-akhir'], $option_year);
				?>
				
			</div>
		</div>
	</div>
	<div class="row mb-3 row-periode-tahun" style="display:none">
		<label class="col-sm-3 col-form-label">Periode</label>
		<div class="col-sm-9">
			<div class="input-group">
				<?php
				$end_year = date('Y') + 1;
				$start_year = $end_year -5;
				$option_year = [];
				for ($i = $end_year; $i >= $start_year; $i--) {
					$option_year[$i] = $i;
				}
				echo options(['name' => 'periode_tahun', 'id' => 'tahun'], $option_year, date('Y'));
				?>
				
			</div>
		</div>
	</div>
	<div class="row mb-3 row-periode-tahun-ajaran" style="display:none">
		<label class="col-sm-3 col-form-label">Periode</label>
		<div class="col-sm-9">
			<div class="input-group">
				<?php
				echo options(['name' => 'id_tahun_ajaran'], $tahun_ajaran);
				?>
				
			</div>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Nilai Tagihan</label>
		<div class="col-sm-9">
			<input name="nilai_tagihan" id="nilai-tagihan" class="form-control number" value=""/>
			<small>Nilai tagihan yang harus dibayar siswa</small>
		</div>
	</div>
	<!-- setelah input nilai_tagihan -->
		<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Subsidi</label>
		<div class="col-sm-9">
			<input name="potongan" id="potongan" class="form-control number" value="0"/>
			<small>Masukkan Subsidi (jika ada)</small>
		</div>
		</div>

		<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Total Setelah Subsidi</label>
		<div class="col-sm-9">
			<input id="total-setelah" class="form-control" readonly />
		</div>
		</div>
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Pilih Siswa</label>
		<div class="col-sm-9">
			<div style="position:relative">
				<?php
				helper('html');
				?>
				<?= options(['name' => 'id_kelas', 'id' => 'option-kelas'], $group_kelas, set_value('id_kelas'))?>
			</div>
			<table id="tabel-siswa" class="mt-5 table table-bordered table-striped table-hover">
				<thead>
					<tr>
						<th>No</th>
						<th>Nama</th>
						<th>NIS</th>
						<th>Kelas</th>
						<th>
							<div class="form-check text-start fw-bold">
								  <input class="form-check-input" type="checkbox" value="" id="check-all">
								  <label for="check-all">
									Semua
								  </label>
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="6" class="align-middle text-center" style="height:50vh"></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Siswa Dipilih</label>
		<div class="col-sm-5 fw-bold">
			<span class="fw-bold" id="jml-siswa-dipilih">0</span> Siswa
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Keterangan</label>
		<div class="col-sm-9">
			<textarea name="keterangan" class="form-control"></textarea>
		</div>
	</div>
	<div class="text-danger"><em><strong>Note:</strong> Data lama akan diganti dengan data yang baru</em></div>
	<span style="display:none" id="pendapatan-jenis"><?=json_encode($pendapatan_jenis)?></span>
</form>

<script>
function toInt(v){ return parseInt(String(v).replace(/\./g,'')) || 0; }
function formatNumber(n){ return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); }

function updateTotalAdd(){
  const nilai = toInt(document.getElementById('nilai-tagihan').value);
  const pot = toInt(document.getElementById('potongan').value);
  const total = Math.max(0, nilai - pot);
  document.getElementById('total-setelah').value = formatNumber(total);
}

document.getElementById('nilai-tagihan').addEventListener('input', updateTotalAdd);
document.getElementById('potongan').addEventListener('input', updateTotalAdd);
updateTotalAdd();
</script>