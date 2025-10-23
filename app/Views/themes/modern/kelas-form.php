<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal p-3">
	<div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Nama Kelas</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="nama_kelas" value="<?=@$kelas['nama_kelas']?>" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Group Kelas</label>
			<div class="col-sm-9">
				<?=options(['name' => 'group_kelas'], $list_kelas, @$kelas['group_kelas'])?>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
</form>