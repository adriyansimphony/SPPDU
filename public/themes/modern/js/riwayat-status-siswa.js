/**
* Written by: Agus Prawoto Hadi
* Year		: 2023-2023
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	let dataTables = '';
	if ($('#table-result').length > 0) {
		
		const column = $.parseJSON($('#dataTables-column').html());
		const url = $('#dataTables-url').text();
	
		const settings = {
			"processing": true,
			"serverSide": true,
			"scrollX": true,
			"ajax": {
				"url": url,
				"type": "POST"
			},
			"columns": column
		}
		
		let $add_setting = $('#dataTables-setting');
		if ($add_setting.length > 0) {
			add_setting = $.parseJSON($('#dataTables-setting').html());
			for (k in add_setting) {
				settings[k] = add_setting[k];
			}
		}
		
		dataTables =  $('#table-result').DataTable( settings );
	}
	
	$('#option-status-siswa').change(function() {
		new_url = base_url + 'riwayat-status-siswa/getDataDT?id=' + $(this).val();
		dataTables.ajax.url( new_url ).load();
	});
	
	$('#btn-excel').click(function() {
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$spinner.prependTo($this);
		
		filename = 'Daftar Siswa - ' + format_date('dd-mm-yyyy') + '.xlsx';
		export_url = base_url + 'riwayat-status-siswa/ajaxExportExcel';
		fetch(export_url)
		  .then(resp => resp.blob())
		  .then(blob => {
				$this.prop('disabled', false);
				$spinner.remove();
				saveAs(blob, filename);
		  })
		.catch((xhr) => {
			$this.prop('disabled', false);
			$spinner.remove();
			console.log(xhr);
			alert('Ajax Error')
			
		});
	})
	
	$('body').delegate('.btn-delete', 'click', function(e) {
		e.preventDefault();
		id = $(this).attr('data-id');
		$bootbox = bootbox.confirm({
			message: $(this).attr('data-delete-title'),
			callback: function(confirmed) {
				if (confirmed) {
					$button = $bootbox.find('button');
					$button.attr('disabled', 'disabled');
					$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
					$spinner.prependTo($bootbox.find('.bootbox-accept'));
					$.ajax({
						type: 'POST',
						url: base_url + 'riwayat-status-siswa/ajaxDeleteData',
						data: 'id=' + id,
						dataType: 'json',
						success: function (data) {
							$bootbox.modal('hide');
							$spinner.remove();
							$button.removeAttr('disabled');
							if (data.status == 'ok') {
								const Toast = Swal.mixin({
									toast: true,
									position: 'top-end',
									showConfirmButton: false,
									timer: 2500,
									timerProgressBar: true,
									iconColor: 'white',
									customClass: {
										popup: 'bg-success text-light toast p-2'
									},
									didOpen: (toast) => {
										toast.addEventListener('mouseenter', Swal.stopTimer)
										toast.addEventListener('mouseleave', Swal.resumeTimer)
									}
								})
								Toast.fire({
									html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil dihapus</div>'
								})
								dataTables.draw();
								// $('#option-kelas').trigger('change');
							} else {
								show_alert('Error !!!', data.message, 'error');
							}
						},
						error: function (xhr) {
							$spinner.remove();
							$button.removeAttr('disabled');
							show_alert('Error !!!', xhr.responseText, 'error');
							console.log(xhr.responseText);
						}
					})
					return false;
				}
			},
			centerVertical: true
		});
	})
	
	if ($('.select2').length > 0) {
		$('.select2').select2({theme: 'bootstrap-5'});
	}
	
	$('#id-status-siswa').change(function() {
		value = parseInt($(this).val());
		if (value <= 4) {
			$('#row-pindah-ke').show();
		} else {
			$('#row-pindah-ke').hide();
		}
	})
	
	if ($('.flatpickr').length > 0) {
		$('.flatpickr').flatpickr({
			dateFormat: "d-m-Y"
		});
	}
	
	$('.submit-data').click(function(e) {
		e.preventDefault();
		
		if ($('input[name="tgl_status"]').val() == '') {
			bootbox.alert('<div class="alert alert-danger">Error: Tanggal belum dipilih</div>');
			return;
		}
		
		if ($('select[name="id_status_siswa"]').val() == '') {
			bootbox.alert('<div class="alert alert-danger">Error: Status belum dipilih</div>');
			return;
		}
		data = $('.form-siswa').serialize();
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
		$spinner.prependTo($this);
		$.ajax({
			type: 'POST',
			url: base_url + 'riwayat-status-siswa/ajaxSaveData',
			data: data,
			dataType: 'json',
			success: function (data) {
				$spinner.remove();
				$this.prop('disabled', false);
				if (data.status == 'ok') 
				{
					const Toast = Swal.mixin({
						toast: true,
						position: 'top-end',
						showConfirmButton: false,
						timer: 2500,
						timerProgressBar: true,
						iconColor: 'white',
						customClass: {
							popup: 'bg-success text-light toast p-2'
						},
						didOpen: (toast) => {
							toast.addEventListener('mouseenter', Swal.stopTimer)
							toast.addEventListener('mouseleave', Swal.resumeTimer)
						}
					})
					Toast.fire({
						html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil disimpan</div>'
					})
				} else {
					show_alert('Error !!!', data.message, 'error');
				}
			},
			error: function (xhr) {
				$spinner.remove();
				$this.prop('disabled', false);
				show_alert('Error !!!', xhr.responseText, 'error');
				console.log(xhr.responseText);
			}
		})
	})
	
	$('.btn-delete-all-riwayat').click(function() {
		
		$bootbox =  bootbox.dialog({
			title: 'Hapus Semua Data',
			message: '<div class="px-2">' +
						'<p>Tindakan ini akan menghapus semua data pada database tabel siswa_riwayat_status</p>' +
				'</div>'+
			'</form>',
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				success: {
					label: 'Hapus',
					className: 'btn-danger submit',
					callback: function() 
					{
						var $button = $bootbox.find('button').prop('disabled', true);
						var $button_submit = $bootbox.find('button.submit');
						
						$bootbox.find('.alert').remove();
						$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
						$button_submit.prepend($spinner);
						$button.prop('disabled', true);
						
						$.ajax({
							type: 'GET',
							url: base_url + 'riwayat-status-siswa/ajaxDeleteAllStatus',
							dataType: 'text',
							success: function (data) {
								data = $.parseJSON(data);
								console.log(data);
								$spinner.remove();
								$button.prop('disabled', false);
								
								if (data.status == 'ok') 
								{
									$bootbox.modal('hide');
									const Toast = Swal.mixin({
										toast: true,
										position: 'top-end',
										showConfirmButton: false,
										timer: 2500,
										timerProgressBar: true,
										iconColor: 'white',
										customClass: {
											popup: 'bg-success text-light toast p-2'
										},
										didOpen: (toast) => {
											toast.addEventListener('mouseenter', Swal.stopTimer)
											toast.addEventListener('mouseleave', Swal.resumeTimer)
										}
									})
									Toast.fire({
										html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil dihapus</div>'
									})
									
									dataTables.draw();
									$('#btn-export-container').find('button').prop('disabled', true);
								} else {
									Swal.fire({
										title: 'Error !!!',
										html: data.message,
										icon: 'error',
										showCloseButton: true,
										confirmButtonText: 'OK'
									})
								}
							},
							error: function (xhr) {
								console.log(xhr.responseText);
								$spinner.remove();
								$button.prop('disabled', false);
								Swal.fire({
									title: 'Error !!!',
									html: xhr.responseText,
									icon: 'error',
									showCloseButton: true,
									confirmButtonText: 'OK'
								})
							}
						})
						return false;
					}
				}
			}
		});
		
		
	});
});