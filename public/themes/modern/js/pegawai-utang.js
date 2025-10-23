/**
* Written by: Agus Prawoto Hadi
* Year		: 2023-2023
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	dataTablesModal = '';
	if ($('#table-result-utang-pegawai').length) {
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
		
		dataTables =  $('#table-result-utang-pegawai').DataTable( settings );
	}
	
	$('body').delegate('.cari-pegawai-utang', 'click', function(e) {
		
		e.preventDefault();
		$('.modal-backdrop').hide();
		$('.bootbox').css('z-index', '10');
		
		$bootbox_custom = bootbox.dialog({
			title: 'Pilih Pegawai',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			onEscape: function() {
				$('.modal-backdrop').show();
				$('.bootbox').css('z-index', '');
			},
			backdrop: true,
			buttons: false
		});
		
		$bootbox_custom.find('.modal-dialog').css('max-width', '650px');
		$bootbox_custom.find('.modal-body').addClass('p-4');
		$.get(base_url + '/pegawai-utang/getListPegawai', function(html)
		{
			$bootbox_custom.find('.modal-body').empty().append(html);
			const column = $.parseJSON($('#pegawwai-modal-dataTables-column').html());
			const url = $('#pegawwai-modal-dataTables-url').text();
		
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
			
			let $add_setting = $('#pegawwai-modal-dataTables-setting');
			if ($add_setting.length > 0) {
				add_setting = $.parseJSON($('#pegawwai-modal-dataTables-setting').html());
				for (k in add_setting) {
					settings[k] = add_setting[k];
				}
			}
			
			dataTablesModal =  $('#pegawwai-modal-table-result').DataTable( settings );
		});		  
		
		$(document)
		.undelegate('.btn-pilih-pegawai', 'click')
		.delegate('.btn-pilih-pegawai', 'click', function() 
		{			
			// Pegawai popup
			$this = $(this);
			$this.attr('disabled', 'disabled');
			pegawai = JSON.parse($(this).next().text())
			$('#id-pegawai').val(pegawai.id_pegawai);
			$('#nama-pegawai').val(pegawai.nama);
			$('#tabel-list-item-utang').find('.del-row-bayar-utang').trigger('click');
			$bootbox_custom.modal('hide');
			modal_backdrop_length = $('.modal-backdrop').length;
			if (modal_backdrop_length > 1) {
				$('.modal-backdrop').eq( modal_backdrop_length - 1 ).show();
			} else {
				$('.modal-backdrop').show();
			}
			
			$('.bootbox').css('z-index', '');
		});
	});
	
	$('body').delegate('.btn-cari-utang', 'click', function(e) {
		
		id = $('#id-pegawai').val();
		e.preventDefault();
	
		error_message = '';							
								
		$form = $bootbox.find('form');
		if (!error_message && $form.find('#id-pegawai').val() == '') {
			error_message = 'Pegawai belum dipilih';
		}
		
		if (error_message) {
			$('.modal-backdrop').hide();
			$bootbox.css('z-index', '10');
			bootbox.alert(error_message, function() {
				$bootbox.css('z-index', '');
				$('.modal-backdrop').show();
			})
			return false;
		}
				
		$('.modal-backdrop').hide();
		$bootbox.css('z-index', '10');
		
		$bootbox_custom = bootbox.dialog({
			title: 'Pilih Utang',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			onEscape: function() {
				close_modal();
			},
			backdrop: true,
			buttons: false
		});
		
		$bootbox_custom.find('.modal-dialog').css('max-width', '850px');
		$bootbox_custom.find('.modal-body').addClass('p-4');
		$.get(base_url + '/pegawai-utang/getListUtang?id=' + id, function(html)
		{			
			$bootbox_custom.find('.modal-body').empty().append(html);
			$table = $('#tabel-list-item-utang');
			
			var list_utang = '<span class="belum-ada mb-2">Utang belum dipilih</span>';			
			if ($('#table-show').val() == 1) {

				$textarea_table = $table.find('tbody').eq(0).find('textarea');
				var list_utang = '';
				$textarea_table.each (function (i, elm) {
					utang = JSON.parse($(elm).val());
					list_utang += '<small  class="px-3 py-2 me-2 mb-2 text-success bg-success bg-opacity-10 border border-success rounded-2">' + format_ribuan(utang.nilai_utang) + '</small>';
				});
			}
			
			$bootbox_custom.find('.modal-panel-header').prepend('<div class="list-utang-terpilih pb-3 d-flex flex-wrap">' + list_utang + '</div>');
			
			const column = $.parseJSON($('#list-utang-dataTables-column').html());
			const url = $('#list-utang-dataTables-url').text();
		
			const settings = {
				"processing": true,
				"serverSide": true,
				"scrollX": true,
				"ajax": {
					"url": url,
					"type": "POST"
				},
				"columns": column,
				initComplete: function(json) {
					// console.log(json);
				},
				"drawCallback": function( settings ) 
				{
					if ($('#table-show').val() == 1) {

						list_selected = [];
						$textarea = $('#tabel-list-item-utang').find('textarea');
						$textarea.each(function(i, elm) {
							data = JSON.parse($(elm).val());
							list_selected.push(data.id_pegawai_utang);
						});
						
						$textarea_popup = $bootbox_custom.find('textarea');
						$textarea_popup.each(function(i, elm) {
							data = JSON.parse($(elm).val());
							if ($.inArray(data.id_pegawai_utang, list_selected) >= 0 ) {
								console.log(data);
								console.log(list_selected);
								$(elm).parent().find('button').prop('disabled', true);
							}
						});
					}
					// $bootbox_custom.find('.btn-pilih-utang').prop('disabled', true);
				}
			}
			
			let $add_setting = $('#list-utang-dataTables-setting');
			if ($add_setting.length > 0) {
				add_setting = $.parseJSON($('#list-utang-dataTables-setting').html());
				for (k in add_setting) {
					settings[k] = add_setting[k];
				}
			}
			
			dataTablesModal =  $('#list-utang-table-result').DataTable( settings );
		});		  
		
		$(document)
		.undelegate('.btn-pilih-utang', 'click')
		.delegate('.btn-pilih-utang', 'click', function() {
			/* $('.modal-backdrop').show();
			$bootbox.css('z-index', ''); */
			
			// Utang popup
			$this = $(this);
			$tr_popup = $this.parents('tr').eq(0);
			utang_terpilih = $tr_popup.find('td').eq(2).text();
			utang_dibayar = $tr_popup.find('td').eq(3).text();
			utang_kurang = $tr_popup.find('td').eq(4).text();
			$this.attr('disabled', 'disabled');
			utang = JSON.parse($(this).next().text())
			$('#id-utang').val(utang.id_pegawai);
			$('#nama-utang').val(utang.nama);
			
			// $bootbox_custom.modal('hide');
			$detail_utang = $this.parent().children('textarea');
			$tbody_target = $('#tabel-list-item-utang').find('tbody');
			$target_row = $tbody_target.find('tr');
			$new_tr = $tbody_target.find('tr:last').clone();
			$new_tr.find('input').val('');
			$new_tr.find('td').eq(1).html(utang_terpilih);
			$new_tr.find('td').eq(2).html(utang_dibayar);
			$new_tr.find('td').eq(3).find('textarea').remove();
			$new_tr.find('td').eq(3).append($detail_utang);
			$new_tr.find('td').eq(4).html(utang_kurang);
			
			if ($('#table-show').val() == 0) {
				$new_tr.find('td').eq(0).text($target_row.length);
				$target_row.replaceWith($new_tr);
			} else {
				$new_tr.find('td').eq(0).text($target_row.length + 1);
				$tbody_target.append($new_tr);
			}
			$table.show();
			$('#table-show').val(1);
			
			$('.list-utang-terpilih').find('.belum-ada').remove();
			$('.list-utang-terpilih').append('<small  class="px-3 py-2 me-2 mb-2 text-success bg-success bg-opacity-10 border border-success rounded-2">' + utang_terpilih + '</small>');

		});
	});
	
	$('body').delegate('.item-nilai-bayar', 'keyup', function() {
		$this = $(this);
		$tr = $this.parents('tr').eq(0);
		data = JSON.parse($this.parent().find('textarea').val());
		if (!data.total_bayar) {
			data.total_bayar = 0;
		}
		utang = parseInt(data.nilai_utang);
		dibayar = parseInt(data.total_bayar);
		bayar_sekarang = parseInt(this.value.replace(/\D/,''));
		kurang = utang - dibayar - bayar_sekarang;
		if (kurang < 0) {
			kurang = 0;
		}
		// console.log(kurang);
		$tr.find('.kurang').text(format_ribuan(kurang));
		calculate_total()
	});
	
	$('body').delegate('.del-row-bayar-utang', 'click', function() {
		$this = $(this);
		
		$tabel_list_item = $('#tabel-list-item-utang');
		$tr = $tabel_list_item.find('tbody').find('tr');
		if ($tr.length == 1) {
			$tr.find('input').val('');
			$('#total-item-nilai-bayar').val('');
			// $('#total-pembayaran').trigger('keyup');
			$tabel_list_item.hide();
			$('#table-show').val(0);
		} else {
			$this.parents('tr:eq(0)').remove();
			$tr = $('#tabel-list-item-utang').find('tbody').find('tr');
			$tr.each(function(i, elm) {
				$(elm).find('td').eq(0).text(i + 1);
			})
		}
		calculate_total();
	})
	
	function calculate_total() 
	{
		$input_harga = $('.item-nilai-bayar');

		total = 0;
		$input_harga.each(function(i, elm) 
		{
			value = $(elm).val();
			total += setInt( value );
		});
		$('#total-item-nilai-bayar').text(format_ribuan(total));
		$('#total-pembayaran').trigger('keyup');	
		// $('#total-pembayaran').val(format_ribuan(total));
	}
		
	$('body').delegate('.btn-edit', 'click', function(e) {
		e.preventDefault();
		showForm('edit', $(this).attr('data-id'))
	})
	
	$('body').delegate('.btn-add', 'click', function(e) {
		e.preventDefault();
		showForm();
	})
	
	$('#table-result').delegate('.btn-delete', 'click', function(e) {
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
						url: base_url + 'pegawai-utang/ajaxDeleteData',
						data: 'id=' + id,
						dataType: 'json',
						success: function (data) {
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
		
	function showForm(type='add', id = '') {
		$bootbox =  bootbox.dialog({
			title: 'Edit Data',
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
							url: base_url + 'pegawai-utang/ajaxSaveData',
							data: new FormData(form),
							processData: false,
							contentType: false,
							dataType: 'json',
							success: function (data) {
								
								$spinner.remove();
								$button.prop('disabled', false);
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
										dataTables.draw(false);
									} else {
										dataTables.draw();
									}
									
									$bootbox.modal('hide');
									$('.btn-delete-all-data').prop('disabled', false);
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
	
		var $button = $bootbox.find('button').prop('disabled', true);
		var $button_submit = $bootbox.find('button.submit');
		
		$bootbox.find('.modal-dialog').addClass('modal-size-medium');
		$.get(base_url + 'pegawai-utang/ajaxGetFormData?id=' + id, function(html){
			$button.prop('disabled', false);
			$bootbox.find('.modal-body').empty().append(html);
			$('.flatpickr').flatpickr({
				dateFormat: "d-m-Y"
			});
		});
	};
	
	$('body').delegate('.btn-edit-bayar-utang', 'click', function() {
		id = $(this).attr('data-id');
		$bootbox =  bootbox.dialog({
			title: 'List Pembayaran',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			buttons: false
		});
	
		var $button = $bootbox.find('button').prop('disabled', true);
		var $button_submit = $bootbox.find('button.submit');
		
		// $bootbox.find('.modal-dialog').addClass('modal-size-medium');
		$bootbox.find('.modal-dialog').css('max-width', '700px');
		$.get(base_url + 'pegawai-utang/getListBayar?id=' + id, function(html){
			$button.prop('disabled', false);
			$bootbox.find('.modal-body').empty().append(html);
			
			const column = $.parseJSON($('#jwdmodal-dataTables-column').html());
			const url = $('#jwdmodal-dataTables-url').text();
		
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
			
			let $add_setting = $('#jwdmodal-dataTables-setting');
			if ($add_setting.length > 0) {
				add_setting = $.parseJSON($('#jwdmodal-dataTables-setting').html());
				for (k in add_setting) {
					settings[k] = add_setting[k];
				}
			}
			
			dataTablesModal =  $('#jwdmodal-table-result').DataTable( settings );
		});
	});
	
	function close_modal() 
	{
		modal_backdrop_length = $('.modal-backdrop').length;
		if (modal_backdrop_length) {
			if (modal_backdrop_length > 1) {
				// alert();
				$('.modal-backdrop').eq(modal_backdrop_length - 2).show();
			} else {
				$('.modal-backdrop').show();
			}
		}
		$('.bootbox').css('z-index', '');
	}
	
	$('body').delegate('.btn-delete-bayar-utang', 'click', function() {
		$this = $(this);
		$bootbox_delete =  bootbox.dialog({
			title: 'Hapus Data Pembayaran',
			onEscape: function() {
				close_modal();
			},
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			buttons: {
				cancel: {
					label: 'Cancel',
					callback: function() {
						close_modal();
					}
				},
				success: {
					label: 'Hapus',
					className: 'btn-danger submit',
					callback: function() 
					{
						$spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
						$button_submit.prepend($spinner);
						$button.prop('disabled', true);
			
						$.ajax({
							type: 'POST',
							url: base_url + 'pegawai-utang/ajaxDeleteDataByIdUtang',
							data: 'id=' + $this.attr('data-id'),
							dataType: 'json',
							success: function (data) {
								$spinner.remove();
								$button.prop('disabled', false);
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
									$bootbox_delete.modal('hide');	
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
		
		$bootbox_delete.find('.modal-dialog').css('max-width', '600px');
		$button = $bootbox_delete.find('button').prop('disabled', true);
		$button_submit = $bootbox_delete.find('button.submit');
		
		id = $(this).attr('data-id');
		$.get(base_url + 'pegawai-utang/getListDataBayarByIdUtang?id=' + id, function(data){
			data = JSON.parse(data);
			$button.prop('disabled', false);
			
			if (!data.length) {
				html = 'Data pembayaran belum ada';
				$button_submit.prop('disabled', true);
			} else {
				html = '<p class="px-2">Data pembayaran berikut akan dihapus (mungkin akan berpengaruh pada pembayaran utang yang lain):</p><div class="px-4"><ol>';
				data.map(function(data_bayar) {
					html += '<li><p>No. invoice ' + data_bayar.no_invoice + ' dengan rincian pembayaran sebagai berikut:</p>' +
							'<table class="table">' +
								'<thead>' +
									'<tr>' +
										'<th>No</th>' +
										'<th>Utang</th>' +
										'<th>Tanggal Utang</th>' +
										'<th>Pembayaran (dihapus)</th>' +
									'</tr>' +
								'</thead>' +
								'<tbody>';
						data_bayar.detail.map(function(item, i) {
							html += '<tr>' +
										'<td>' + (i + 1) + '</td>' +
										'<td class="text-end">' + item.nilai_utang + '</td>' +
										'<td class="text-end">' + item.tgl_utang + '</td>' +
										'<td class="text-end">' + item.nilai_bayar + '</td>' +
									'</tr>';
						});
					html += '</tbody></table></li>';
					
				});
				html += '</ol></div>';
			}
			$bootbox_delete.find('.modal-body').empty().append(html);
		});
	});
		
	$('body').delegate('.btn-delete-bayar-detail', 'click', function() {
		$this = $(this);
		data_utang = $this.parents('tr').find('textarea').val();
		data_utang = JSON.parse(data_utang);
				
		message = '<div class="px-3"><p>Hapus data pembayaran utang pada invoice ' + data_utang.no_invoice + '?</p><p>Data pembayaran yang akan dihapus adalah sebagai berikut:</p>' +
						'<table class="table">' +
							'<thead>' +
								'<tr>' +
									'<th>No</th>' +
									'<th>Utang</th>' +
									'<th>Tanggal Utang</th>' +
									'<th>Pembayaran (dihapus)</th>' +
								'</tr>' +
							'</thead>' +
							'<tbody>';
					data_utang.detail.map(function(v, i) {
						message += '<tr>' +
									'<td>' + (i + 1) + '</td>' +
									'<td class="text-end">' + v.nilai_utang + '</td>' +
									'<td class="text-end">' + v.tgl_utang + '</td>' +
									'<td class="text-end">' + v.nilai_bayar + '</td>' +
								'</tr>';
					});
		message += '</tbody></table></div>';
		
		$('.modal-backdrop').hide();
		$('.bootbox').css('z-index', '10');
		
		$bootbox_delete =  bootbox.dialog({
			title: 'Hapus Data Pembayaran',
			onEscape: function() {
				close_modal();
			},
			message: message,
			buttons: {
				cancel: {
					label: 'Cancel',
					callback: function() {
						close_modal();
					}
				},
				success: {
					label: 'Hapus',
					className: 'btn-danger submit',
					callback: function() 
					{
						$spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
						$button_submit.prepend($spinner);
						$button.prop('disabled', true);
			
						$.ajax({
							type: 'POST',
							url: base_url + 'pegawai-utang/ajaxDeleteDataByIdBayar',
							data: 'id=' + $this.attr('data-id'),
							dataType: 'json',
							success: function (data) {
								
								$spinner.remove();
								$button.prop('disabled', false);
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
									
									close_modal();
									dataTables.draw();
									if (dataTablesModal) {
										dataTablesModal.draw();
									}
									$bootbox_delete.modal('hide');
									
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
		$bootbox_delete.find('.modal-dialog').css('max-width', '600px');
		$button = $bootbox_delete.find('button');
		$button_submit = $bootbox_delete.find('button.submit');
	})
	
	$('body').delegate('.btn-add-bayar-utang', 'click', function() {
		$this = $(this);
		showFormBayarUtang('', '', '', '', $this.attr('data-id'));
	})
	
	$('body').delegate('.btn-bayar-utang', 'click', function() {
		showFormBayarUtang();
	})
		
	$('body').delegate('.btn-edit-bayar-detail', 'click', function() {
		$('.modal-backdrop').hide();
		$('.bootbox').css('z-index', '10');
		showFormBayarUtang( 'edit', $(this).attr('data-id'), function()
		{
			$('.modal-backdrop').show();
			$('.bootbox').css('z-index', '');
			// alert();
		}, function() {
			dataTablesModal.draw();
		});
	})
	
	function showFormBayarUtang(type='add', id = '', onEscapeCallback = '', submitCallback = '', id_pegawai_utang = '') {
		$bootbox =  bootbox.dialog({
			title: 'Edit Data',
			onEscape: function() {
				if (typeof onEscapeCallback == 'function') {
					onEscapeCallback();
				}
			},
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			buttons: {
				cancel: {
					label: 'Cancel',
					callback: function() {
						if (typeof onEscapeCallback == 'function') {
							onEscapeCallback();
						}
					}
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
							url: base_url + 'pegawai-utang/ajaxSaveDataBayar',
							data: new FormData(form),
							processData: false,
							contentType: false,
							dataType: 'json',
							success: function (data) {
								
								$spinner.remove();
								$button.prop('disabled', false);
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
										dataTables.draw(false);
									} else {
										dataTables.draw();
									}
									
									$bootbox.modal('hide');
									modal_backdrop_length = $('.modal-backdrop').length;
									if (modal_backdrop_length) {
										if (modal_backdrop_length > 1) {
											$('.modal-backdrop').eq(modal_backdrop_length - 1).show();
										} else {
											$('.modal-backdrop').show();
										}
										$('.bootbox').css('z-index', '');
									}
									
									$('.btn-delete-all-data').prop('disabled', false);
								} else {
									show_alert('Error !!!', data.message, 'error');
								}
								
								if (typeof submitCallback == 'function') {
									submitCallback();
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
	
		var $button = $bootbox.find('button').prop('disabled', true);
		var $button_submit = $bootbox.find('button.submit');
		
		// $bootbox.find('.modal-dialog').addClass('modal-size-medium');
		$bootbox.find('.modal-dialog').css('max-width', '700px');
		$.get(base_url + 'pegawai-utang/ajaxGetFormDataBayar?id=' + id + '&id_pegawai_utang=' + id_pegawai_utang, function(html){
			$button.prop('disabled', false);
			$bootbox.find('.modal-body').empty().append(html);
			$('.flatpickr').flatpickr({
				dateFormat: "d-m-Y"
			});
		});
	};
	
	/* $('body').delegate('.add-row', 'click', function() {
		$this = $(this);
		$tbody = $this.parents('tbody');
		$new_row = $this.parents('tr').eq(0).clone();
		$new_row.find('input, textarea').val('');
		$new_row.find('button').removeClass('btn-outline-success');
		$new_row.find('button').addClass('btn-outline-danger');
		$new_row.find('button').removeClass('add-row');
		$new_row.find('button').addClass('del-row');
		$new_row.find('i').removeClass('fa-plus');
		$new_row.find('i').addClass('fa-times');
		$new_row.find('td').eq(0).text($tbody.find('tr').length + 1);
		$tbody.append($new_row);
	}); */
	
	/* $('body').delegate('.del-row', 'click', function() {
		$this = $(this);

		$this.parents('tr:eq(0)').remove();
		$tr = $('#tabel-list-item-pengeluaran').find('tbody').find('tr');
		$tr.each(function(i, elm) {
			$(elm).find('td').eq(0).text(i + 1);
		})
		calculate_total();
	}) */
	
	/* $('body').delegate('.item-nilai-bayar', 'keyup', function() 
	{
		calculate_total();
	});
	
	function calculate_total() 
	{
		$input_harga = $('.item-nilai-bayar');

		total = 0;
		$input_harga.each(function(i, elm) 
		{
			value = $(elm).val();
			total += setInt( value );
		});
		$('#total-item-nilai-bayar').text(format_ribuan(total));
		// $('#total-pembayaran').trigger('keyup');	
		// $('#total-pembayaran').val(format_ribuan(total));
	} */
	
	$('body').delegate('.number', 'keyup', function() {
		this.value = format_ribuan(this.value);
	})
	
	$('.btn-delete-all-data').click(function() {
		$this = $(this);
		$bootbox =  bootbox.dialog({
			title: 'Hapus Semua Data',
			message: '<div class="px-2">' +
						'<p>Tindakan ini akan menghapus semua data pada database tabel pemasukan_jenis dan pemasukan_jenis_sumber</p>' +
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
							url: base_url + 'pegawai-utang/ajaxDeleteAllData',
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
									$this.prop('disabled', true);
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
	
	$('#btn-excel').click(function() {
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$spinner.prependTo($this);
		
		filename = 'Daftar Utang Pegawai - ' + format_date('dd-mm-yyyy') + '.xlsx';
		export_url = base_url + 'pegawai-utang/ajaxExportExcel';
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
	});
});