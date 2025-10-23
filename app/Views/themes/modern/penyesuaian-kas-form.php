<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal p-3">
	<div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Nama Penyesuaian</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="nama_penyesuaian_kas" value="<?=@$penyesuaian['nama_penyesuaian_kas']?>" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Nilai Penyesuaian</label>
			<div class="col-sm-9">
				<div class="input-group">
					<?php
						$selected_plus = $selected_minus = '';
						if (@$penyesuaian['nilai']) {
							$selected_plus = substr($penyesuaian['nilai'], 0,1) == '-' ? '' : 'selected="selected"';
							$selected_minus = substr($penyesuaian['nilai'], 0,1) == '-' ? 'selected="selected"' : '';
						}
					?>
					<select name="plus_minus" class="form-select" style="max-width:65px" >
						<option value="+" <?=$selected_plus?>>+</option>
						<option value="-" <?=$selected_minus?>>-</option>
					</select>
					<input class="form-control number" name="nilai" value="<?= format_number(str_replace('-', '', @$penyesuaian['nilai']))?>" />
				</div>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Tgl. Berlaku</label>
			<div class="col-sm-9">
				<?php
				if (empty($penyesuaian['tgl_berlaku'])) {
					$tgl_berlaku = date('d-m-Y');
				} else {
					list($y, $m, $d) = explode('-', $penyesuaian['tgl_berlaku']);
					$tgl_berlaku = $d . '-' . $m . '-' . $y;
				}
				?>
				<input type="text" name="tgl_berlaku" class="form-control flatpickr" value="<?=$tgl_berlaku?>"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Keterangan</label>
			<div class="col-sm-9">
				<textarea name="keterangan" class="form-control"><?=@$penyesuaian['keterangan']?></textarea>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
</form>