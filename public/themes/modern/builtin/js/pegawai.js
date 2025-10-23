/**
* Written by: Agus Prawoto Hadi
* Year		: 2022
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	$('.flatpickr').flatpickr({
		dateFormat: "d-m-Y"
	});
	
	if ($('#table-result').length) {
		column = $.parseJSON($('#dataTables-column').html());
		url = $('#dataTables-url').text();
		
		 var settings = {
			"processing": true,
			"serverSide": true,
			"scrollX": true,
			"ajax": {
				"url": url,
				"type": "POST",
				/* "dataSrc": function (json) {
					console.log(json)
				} */
			},
			"columns": column,
			"initComplete": function( settings, json ) {
				dataTables.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
					$row = $(this.node());
					/* this
						.child(
							$(
								'<tr>'+
									'<td>'+rowIdx+'.1</td>'+
									'<td>'+rowIdx+'.2</td>'+
									'<td>'+rowIdx+'.3</td>'+
									'<td>'+rowIdx+'.4</td>'+
								'</tr>'
							)
						)
						.show(); */
				} );
			 }
		}
		
		$add_setting = $('#dataTables-setting');
		if ($add_setting.length > 0) {
			add_setting = $.parseJSON($('#dataTables-setting').html());
			for (k in add_setting) {
				settings[k] = add_setting[k];
			}
		}
		
		dataTables =  $('#table-result').DataTable( settings );
	}
	
	if ($('#tabel-riwayat-status').length) {
		
		column_riwayat = $.parseJSON($('#dataTablesRiwayat-column').html());
		url = $('#dataTablesRiwayat-url').text();
		
		 var setting_riwayat = {
			 "dom": 'tr',
			"processing": true,
			"serverSide": true,
			"scrollX": true,
			"ajax": {
				"url": url,
				"type": "POST",
				/* "dataSrc": function (json) {
					console.log(json)
				} */
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
		
	$('.select2').select2({
		theme: 'bootstrap-5'
	})
	
	$('.select-role').change(function() {

		list_role = $(this).val();
		list_option = '';
		$('.select-role').find('option').each(function(i, elm) 
		{
			$elm = $(elm)
			value = $elm.attr('value');
			label = $elm.html();
			if (list_role.includes(value)) {
				list_option += '<option value="' + value + '">' + label  + '</option>';
			}
		})
		current_value = $('.default-page-id-role').val();
		$select = $('.default-page-id-role').children('select');
		$select.empty();
		
		if (list_option) {
			
			$select.append(list_option);
			if (!current_value) {
				current_value = $select.find('option:eq(0)').val();
			} 
			$select.val(current_value);
		} else {
			$select.append('<option value="">-- Pilih Role --</option>');
		}
		
	})
	
	$('#option-default-page').change(function(){
		$this = $(this);
		$parent = $this.parent();
		$parent.find('.default-page').hide();
		if ($this.val() == 'url') {
			$parent.find('.default-page-url').show();
		} else if ($this.val() == 'id_module') {
			$parent.find('.default-page-id-module').show();
		} else {
			$parent.find('.default-page-id-role').show();
		}
	})
	
	$('.submit-data').click(function(e) {
		e.preventDefault();
		form = $('.form-pegawai')[0];
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
		$spinner.prependTo($this);
		$.ajax({
			type: 'POST',
			url: base_url + 'builtin/pegawai/ajaxSaveData',
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
						image_url = base_url + 'public/images/pegawai/' + file.name + '?r=' + (Math.random() * 1000);
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
					email = $('input[name="email"]').val();
					$('input[name="email_lama"]').val(email);
					
					$('#id-pegawai').val(data.id_pegawai);
				
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
	
	$('.clear-form').click(function(e) {
		$form = $('.form-pegawai');
		$form.find('input, textarea').val('');
		$form.find('.remove-img').trigger('click');
		$select = $form.find('select');
		$select.each(function(i, elm) {
			$elm = $(elm);
			
			if ($elm.attr('name') == 'option_default_page') {
				$elm.val('id_module').trigger('change');
			} else if ($elm.attr('name') == 'default_page_id_module') {
				$elm.val(5);
			} else if (!$elm.hasClass('propinsi') && !$elm.hasClass('kabupaten') && !$elm.hasClass('kecamatan') && !$elm.hasClass('kelurahan')) {
				$elm.find('option').prop("selected", false);
				$('.select2').select2({
					theme: 'bootstrap-5'
				})
				// $(elm).val(value);
			}
		});
		$image_choose = $form.find('.upload-file-thumb').hide();
		$image_choose.find('img').remove();
		$image_choose.find('.file-prop').empty();
	})
	
	$('#btn-excel').click(function() {
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$spinner.prependTo($this);
		
		filename = 'Daftar Pegawai - ' + format_date('dd-mm-yyyy') + '.xlsx';
		export_url = base_url + 'builtin/pegawai/ajaxExportExcel';
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
						url: base_url + 'builtin/pegawai/ajaxDeleteData',
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
	
	$('.btn-delete-all-pegawai').click(function() {
		$bootbox =  bootbox.dialog({
			title: 'Edit Data',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				success: {
					label: 'Hapus',
					className: 'btn-danger submit',
					callback: function() 
					{
						$bootbox.find('.alert').remove();
						$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
						$button_submit.prepend($spinner);
						$button.prop('disabled', true);
						
						$.ajax({
							type: 'POST',
							url: base_url + 'builtin/pegawai/ajaxDeleteAllPegawai',
							dataType: 'text',
							success: function (data) {
								data = $.parseJSON(data);
								console.log(data);
								$spinner.remove();
								$button.prop('disabled', false);
								
								if (data.status == 'ok') {
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
									show_alert('Error !!!', data.message, 'error');
								}
							},
							error: function (xhr) {
								show_alert('Error !!!', xhr.responseText, 'error');
								console.log(xhr.responseText);
							}
						})
						return false;
					}
				}
			}
		});
		
		var $button = $bootbox.find('button').prop('disabled', true);
		var $button_submit = $bootbox.find('button.submit');
		
		$.get(base_url + 'builtin/pegawai/ajaxGetPegawaiAdmin', function(data){
			list_pegawai = '';
			if (data) {
				data = JSON.parse(data);
				list_pegawai = '<ul class="list-circle">';
				data.map(function(v) {
					list_pegawai += '<li>' + v.nama + '</li>';
				})
				list_pegawai += '</ul>';
			}
			
			if (list_pegawai) {
				content = '<div>Semua data pegawai akan dihapus <strong>kecuali pegawai dengan role admin</strong>. Berikut pegawai dengan role admin:</div>' + list_pegawai;
				$button.prop('disabled', false);
			} else {
				content = 'Untuk dapat menghapus semua data pegawai setidaknya harus ada satu pegawai dengan role admin';
				$bootbox.find('.close, .bootbox-cancel').prop('disabled', false);
			}
			$bootbox.find('.modal-body').empty().append(content);
		});
	});
	
	/* RIWAYAT STATUS */
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
						url: base_url + 'builtin/pegawai/ajaxDeleteStatus',
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
							url: base_url + 'builtin/pegawai/ajaxSaveDataStatus',
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
		
		$.get(base_url + 'builtin/pegawai/ajaxGetFormStatus?id=' + id + '&id_pegawai=' + $('#id-pegawai').val(), function(html){
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
	
	$('#option-ubah-password').change(function() {
		if ($(this).val() == 'Y') {
			$('#password-container').show();
		} else {
			$('#password-container').hide();
		}
	});
});