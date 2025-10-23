<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal p-3">
	<div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Tahun Ajaran</label>
			<div class="col-sm-9">
				<?=options(['name' => 'tahun_ajaran'], $list_tahun_ajaran, @$tahun_ajaran['tahun_ajaran'])?>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
</form>