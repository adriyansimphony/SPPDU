/**
* Written by: Agus Prawoto Hadi
* Year		: 2023-2023
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	$('.flatpickr').flatpickr({
		dateFormat: "d-m-Y"
	});
	
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
	
	let dataTablesRiwayat = '';
	if ($('#tabel-riwayat-status').length) {
		
		column_riwayat = $.parseJSON($('#dataTablesRiwayat-column').html());
		url_riwayat = $('#dataTablesRiwayat-url').text();
		
		 var setting_riwayat = {
			 "dom": 'tr',
			"processing": true,
			"serverSide": true,
			"scrollX": true,
			"ajax": {
				"url": url_riwayat,
				"type": "POST"
			},
			"columns": column_riwayat
		}
		
		$add_setting_riwayat = $('#dataTablesRiwayat-setting');
		if ($add_setting_riwayat.length > 0) {
			add_setting_riwayat = $.parseJSON($('#dataTablesRiwayat-setting').html());
			for (k in add_setting_riwayat) {
				setting_riwayat[k] = add_setting_riwayat[k];
			}
		}
		
		dataTablesRiwayat =  $('#tabel-riwayat-status').DataTable( setting_riwayat );
	}
	
	$('#option-kelas').change(function() {
		new_url = base_url + 'siswa/getDataDT?id_kelas=' + $(this).val();
		dataTables.ajax.url( new_url ).load();
	});
	
	$('#btn-excel').click(function() {
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$spinner.prependTo($this);
		
		filename = 'Daftar Siswa - ' + format_date('dd-mm-yyyy') + '.xlsx';
		export_url = base_url + 'siswa/ajaxExportExcel';
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
						url: base_url + 'siswa/ajaxDeleteData',
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
								// dataTables.draw();
								$('#option-kelas').trigger('change');
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
	
	$('.clear-form').click(function(e) {
		$form = $('.form-siswa');
		$form.find('input, textarea').val('');
		$form.find('.remove-img').trigger('click');
		$select = $form.find('select');
		$select.each(function(i, elm) {
			$elm = $(elm);
			if (!$elm.hasClass('propinsi') && !$elm.hasClass('kabupaten') && !$elm.hasClass('kecamatan') && !$elm.hasClass('kelurahan')) {
				$elm.find('option').prop("selected", false);
				$('.select2').select2({
					theme: 'bootstrap-5'
				})
			}
		});
		$image_choose = $form.find('.upload-file-thumb').hide();
		$image_choose.find('img').remove();
		$image_choose.find('.file-prop').empty();
	})
	
	$('.submit-data').click(function(e) {
		e.preventDefault();
		form = $('.form-siswa')[0];
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
		$spinner.prependTo($this);
		$.ajax({
			type: 'POST',
			url: base_url + 'siswa/ajaxSaveData',
			data: new FormData(form),
			processData: false,
			contentType: false,
			dataType: 'json',
			success: function (data) {
				
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
					$('.file').val('');
					$upload_image_thumb = $('.upload-file-thumb');
					$file = $('.file').parent();
					$new_image = $upload_image_thumb.find('img');
					if ($new_image.length > 0) {
						image_url = base_url + 'public/images/siswa/' + file.name + '?r=' + (Math.random() * 1000);
						$file.find('.img-choose').remove();
						$img_choose = '<div class="img-choose" style="margin:inherit;margin-bottom:10px">'
							+ '<div class="img-choose-container">'
								+ '<img src="' + image_url + '"/>'
								+ '<a href="javascript:void(0)" class="remove-img"><i class="fas fa-times"></i></a>'
							+ '</div>'
						+ '</div>';
						$file.prepend($img_choose);
					}
					$upload_image_thumb.find('img').remove();
					$upload_image_thumb.find('.file-prop').empty();
					$upload_image_thumb.hide();
					
					$('#id-siswa').val(data.id_siswa);
				} else {
					show_alert('Error !!!', data.message, 'error');
				}
				
				$spinner.remove();
				$this.prop('disabled', false);
			},
			error: function (xhr) {
				$spinner.remove();
				$this.prop('disabled', false);
				show_alert('Error !!!', xhr.responseText, 'error');
				console.log(xhr.responseText);
			}
		})
	})
	
	$('.btn-delete-all-siswa').click(function() {
		
		$bootbox =  bootbox.dialog({
			title: 'Hapus Semua Data',
			message: '<div class="px-2">' +
						'<p>Tindakan ini akan menghapus semua data pada database tabel:</p>' +
						'<ul class="list-circle">' + 
							'<li>siswa</li>' + 
							'<li>orangtua</li>' + 
							'<li>siswa_riwayat_status</li>' + 
						'<ul>' + 
				'</div>'+
			'</form>',
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				success: {
					label: 'Delete',
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
							url: base_url + 'siswa/ajaxDeleteAllSiswa',
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
	
	$('#tabel-riwayat-status').delegate('.btn-delete-status', 'click', function(e) {
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
						url: base_url + 'siswa/ajaxDeleteStatus',
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
								dataTablesRiwayat.draw();
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
	
	$('#tabel-riwayat-status').delegate('.btn-edit-status', 'click', function(e) {
		
		e.preventDefault();
		showForm('edit', $(this).attr('data-id'));
	})
	
	$('.btn-add-status').click(function(e) {
		e.preventDefault();
		showForm();
	})
		
	function showForm(type='add', id = '') {
		$bootbox =  bootbox.dialog({
			title: type == 'add' ? 'Tambah Status' : 'Edit Status',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				success: {
					label: 'Submit',
					className: 'btn-success submit',
					callback: function() 
					{
						$bootbox.find('.alert').remove();
						$spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
						$button_submit.prepend($spinner);
						$button.prop('disabled', true);
						
						form = $bootbox.find('form')[0];
						$.ajax({
							type: 'POST',
							url: base_url + 'siswa/ajaxSaveDataStatus',
							data: new FormData(form),
							processData: false,
							contentType: false,
							dataType: 'json',
							success: function (data) {
								$spinner.remove();
								$button.prop('disabled', false);
								$bootbox.modal('hide');
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
										html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil disimpan</div>'
									})
									if (type == 'edit') {
										dataTablesRiwayat.draw(false);
									} else {
										dataTablesRiwayat.draw();
									}
								} else {
									show_alert('Error !!!', data.message, 'error');
								}
							},
							error: function (xhr) {
								$spinner.remove();
								$button.prop('disabled', false);
								show_alert('Error !!!', xhr.responseText, 'error');
								console.log(xhr.responseText);
							}
						})
						return false;
					}
				}
			}
		});
		// $bootbox.find('.modal-dialog').css('max-width', '700px');
		var $button = $bootbox.find('button').prop('disabled', true);
		var $button_submit = $bootbox.find('button.submit');
		
		$.get(base_url + 'siswa/ajaxGetFormStatus?id=' + id + '&id_siswa=' + $('#id-siswa').val(), function(html){
			$button.prop('disabled', false);
			$bootbox.find('.modal-body').empty().append(html);
			$('.flatpickr').flatpickr({
				dateFormat: "d-m-Y"
			});
			$('.select2').select2({
				theme: 'bootstrap-5'
				,dropdownParent: $(".bootbox")
			})
		});
	};
	
	$('body').delegate('#id-status-siswa', 'change', function() {

		if (parseInt($(this).val()) < 5) {
			$('#row-pindah-ke').show();
		} else {
			$('#row-pindah-ke').hide();
		}
	})
});