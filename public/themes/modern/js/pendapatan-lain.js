/**
* Written by: Agus Prawoto Hadi
* Year		: 2023-2023
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {

	if ($('#table-result-pendapatan-lain').length > 0) {
		
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
		
		dataTables =  $('#table-result-pendapatan-lain').DataTable( settings );
	}
	
	// Pembayar
	$('body').delegate('.btn-add-pembayar', 'click', function (e) {
		e.preventDefault();
		$('.modal-backdrop').hide();
		$bootbox.css('z-index', '10');
					  
		$('.modal-backdrop').hide();
		$bootbox.css('z-index', '10');
		$bootbox_form =  bootbox.dialog({
			title: 'Tambah Pembayar',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			onEscape: function() {
				$('.modal-backdrop').show();
				$bootbox.css('z-index', '');
			},
			buttons: {
				cancel: {
					label: 'Cancel',
					callback: function() {
						$('.modal-backdrop').show();
						$bootbox.css('z-index', '');
					}
				},
				success: {
					label: 'Submit',
					className: 'btn-success submit',
					callback: function() 
					{
											
						$bootbox_form.find('.alert').remove();
						$spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
						$button_form_submit.prepend($spinner);
						$button_form.prop('disabled', true);
						
						form = $bootbox_form.find('form')[0];
						$.ajax({
							type: 'POST',
							url: base_url + 'pembayar/ajaxSaveData',
							data: new FormData(form),
							processData: false,
							contentType: false,
							dataType: 'json',
							success: function (data) {
								
								$button_form.prop('disabled', false);
								$spinner.remove();
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
									
									$('#id-pembayar').val(data.id_pembayar);
									$('#nama-pembayar').val($bootbox_form.find('[name="nama_pembayar"]').val());
									
									$bootbox_form.modal('hide');
									$('.modal-backdrop').show();
									$bootbox.css('z-index', '');
								} else {
									show_alert('Error !!!', data.message, 'error');
								}
							},
							error: function (xhr) {
								$button_form.prop('disabled', false);
								$spinner.remove();
								show_alert('Error !!!', xhr.responseText, 'error');
								console.log(xhr.responseText);
							}
						})
						return false;
					}
				}
			}
		});
		
		$bootbox_form.find('.modal-dialog').css('max-width', '550px');
		var $button_form = $bootbox_form.find('button').prop('disabled', true);
		var $button_form_submit = $bootbox_form.find('button.submit');
		
		$.get(base_url + 'pembayar/ajaxGetFormData', function(html){
			$button_form.prop('disabled', false);
			$bootbox_form.find('.modal-body').empty().append(html);
		});
	});
	
	$('body').delegate('.cari-pembayar', 'click', function(e) {
		
		e.preventDefault();
		$('.modal-backdrop').hide();
		$bootbox.css('z-index', '10');
		
		$bootbox_custom = bootbox.dialog({
			title: 'Pilih Pembayar',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			onEscape: function() {
				$('.modal-backdrop').show();
				$bootbox.css('z-index', '');
			},
			backdrop: true,
			buttons: false
		});
		
		$bootbox_custom.find('.modal-dialog').css('max-width', '650px');
		$bootbox_custom.find('.modal-body').addClass('p-4');
		$.get(base_url + '/pendapatan-lain/getListPembayar', function(html){
			$bootbox_custom.find('.modal-body').empty().append(html);
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
		
		$(document)
		.undelegate('.btn-pilih-pembayar', 'click')
		.delegate('.btn-pilih-pembayar', 'click', function() {
			$('.modal-backdrop').show();
			$bootbox.css('z-index', '');
			
			// Pembayar popup
			$this = $(this);
			$this.attr('disabled', 'disabled');
			pembayar = JSON.parse($(this).next().text())
			$('#id-pembayar').val(pembayar.id_pembayar);
			$('#nama-pembayar').val(pembayar.nama_pembayar);
			
			$bootbox_custom.modal('hide');
		});
	});
	
	$('body').delegate('#using-invoice', 'change', function () {
		if (this.value == 'Y') {
			$(this).parents('form').find('.row-invoice').show();
		} else {
			$(this).parents('form').find('.row-invoice').hide();
		}
	});
	
	$('body').delegate('.btn-edit-pendapatan-lain', 'click', function(e) {
		e.preventDefault();
		showFormPendapatanLain('edit', $(this).attr('data-id'))
	})
	
	$('body').delegate('.btn-add-pendapatan-lain', 'click', function(e) {
		e.preventDefault();
		showFormPendapatanLain();
	})
	
	function showFormPendapatanLain(type='add', id = '') {
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
						error_message = '';							
												
						$form = $bootbox.find('form');
						if (!error_message && $form.find('#id-pembayar').val() == '') {
							error_message = 'Nama belum dipilih';
						}
						
						if (!error_message && $form.find('.tanggal-invoice').val() == '') {
							error_message = 'Tanggal invoice harus diisi';
						}
						
						$form = $bootbox.find('form');
						if (!error_message && $form.find('.tanggal-bayar').val() == '') {
							error_message = 'Tanggal bayar harus diisi';
						}
						
						if (!error_message && $('#tabel-list-item-bayar').is(':hidden')) {
							error_message = 'Pembayaran belum dipilih';
						}
						
						if (!error_message) {
							$form.find('.item-nilai-bayar').each (function(i, elm) {
								if ($(elm).val() == '' || parseInt($(elm).val()) == 0) {
									error_message = 'Nilai bayar harus diisi';
								}
							});
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
						
						$spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
						$button_submit.prepend($spinner);
						$button.prop('disabled', true);
						
						$.ajax({
							type: 'POST',
							url: base_url + 'pendapatan-lain/ajaxSaveData',
							data: new FormData($form[0]),
							processData: false,
							contentType: false,
							dataType: 'json',
							success: function (data) {
								
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
										html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil disimpan</div>'
									})
									if (type == 'edit') {
										dataTables.draw(false);
									} else {
										dataTables.draw();
									}
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
		$bootbox.find('.modal-dialog').css('max-width', '600px');
		var $button = $bootbox.find('button').prop('disabled', true);
		var $button_submit = $bootbox.find('button.submit');
		
		$.get(base_url + 'pendapatan-lain/ajaxGetFormData?id=' + id, function(html){
			$button.prop('disabled', false);
			$bootbox.find('.modal-body').empty().append(html);
			$('.flatpickr').flatpickr({
				dateFormat: "d-m-Y",
			});
		});
	};
	
	$('body').delegate('.item-nilai-bayar', 'keyup', function() 
	{
		$row = $(this).parents('tr').eq(0);
		$td = $row.find('td');
		tagihan = $td.eq(2).text().replace(/\D/g,'');
		bayar = this.value.replace(/\D/g,'');
		kurang = parseInt(tagihan) - parseInt(bayar);
		kurang = kurang < 0 ? 0 : kurang;
		console.log(tagihan + 'RR');
		console.log(bayar + 'FF');
		console.log(kurang);
		$td.eq(4).text(format_ribuan(kurang));
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
		$('#total-pembayaran').trigger('keyup');	
		// $('#total-pembayaran').val(format_ribuan(total));
	}
	
	$('body').delegate('#total-pembayaran', 'keyup', function() {
		$total_item = $('#total-item-nilai-bayar');
		total_item = parseInt($total_item.text().replaceAll('.',''));
		total_pembayaran = parseInt(this.value.replaceAll('.',''));
		console.log(total_item);
		console.log(total_pembayaran);
		if (total_pembayaran > total_item ) {
			kembali = total_pembayaran - total_item;
		} else {
			kembali = 0;
		}
		
		$('#kembali').text(format_ribuan(kembali));
	});
	
	$('body').delegate('.add-item-pembayaran', 'click', function(e) 
	{
		e.preventDefault();
		id_pembayar = $('#id-pembayar').val();
		if (!id_pembayar) {
			$('.modal-backdrop').hide();
			$bootbox.css('z-index', '10');
			bootbox.alert('Nama pembayar belum dipilih', function() {
				$bootbox.css('z-index', '');
				$('.modal-backdrop').show();
			})
			return false;
		}
		$table = $('#tabel-list-item-pendapatan');
		table_visible = $table.is(':visible');
		
		$('.modal-backdrop').hide();
		$bootbox.css('z-index', '10');
		
		$bootbox_custom = bootbox.dialog({
			title: 'Pilih Jenis Pendapatan',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			onEscape: function() {
				$('.modal-backdrop').show();
				$bootbox.css('z-index', '');
			},
			backdrop: true,
			buttons: false
		});
		
		$bootbox_custom.find('.modal-dialog').css('max-width', '600px');
		
		
		$.get(base_url + 'pendapatan-lain/getListJenisPembayaran', function(html){
			$bootbox_custom.find('.modal-body').addClass('p-0');
			$bootbox_custom.find('.modal-body').empty().append(html);
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
		
		
		
		
		
		/* $bootbox.hide();
		$('.modal-backdrop').hide();
		$this = $(this); */
		

		/* var $modal = jwdmodal({
			title: 'Pilih Jenis Pembayaran',
			url: base_url + '/pendapatan-lain/getListJenisPembayaran',
			width: '750px',
			onClose: function() {
				$bootbox.show();
				$('.modal-backdrop').show();
			},
			action :function () 
			{
				var list_barang = '<span class="belum-ada mb-2">Pembayaran belum dipilih</span>';
				if (table_visible) {
					$trs = $table.find('tbody').eq(0).find('tr');
					var list_barang = '';
					$trs.each (function (i, elm) {
						$td = $(elm).find('td');
						list_barang += '<small  class="px-3 py-2 me-2 mb-2 text-success bg-success bg-opacity-10 border border-success rounded-2">' + $td.eq(1).html() + '</small>';
					});
				}
				$('.jwd-modal-header-panel').prepend('<div class="list-pembayaran-terpilih p-3 border d-flex flex-wrap">' + list_barang + '</div>');
			}
		}); */
		
		$(document)
		.undelegate('.btn-pilih-pembayaran', 'click')
		.delegate('.btn-pilih-pembayaran', 'click', function() {
			// $bootbox.show();
			// $('.modal-backdrop').show();
			// Pembayar popup
			$this = $(this);
			$this.attr('disabled', 'disabled');
			/* pembayar = JSON.parse($(this).next().text())
			$('#id-pembayar').val(pembayar.id_pembayar);
			$('#nama-pembayar').val(pembayar.nama); */
			// $modal.remove();
			$tr_popup = $this.parents('tr').eq(0);
			jenis_pendapatan = $tr_popup.find('td').eq(1).text();
			tagihan = $tr_popup.find('td').eq(2).text();
			kurang = $tr_popup.find('td').eq(4).text();
			$detail_jenis_pembayaran = $this.parent().children('textarea');
			$tbody_target = $('#tabel-list-item-pendapatan').find('tbody');
			$target_row = $tbody_target.find('tr');
			$new_tr = $tbody_target.find('tr:last').clone();
			$new_tr.find('input').val('');
			$new_tr.find('td').eq(1).html(jenis_pendapatan);
			$new_tr.find('td').eq(2).val('');
			$new_tr.find('td').eq(2).find('textarea').remove();
			$new_tr.find('td').eq(2).append($detail_jenis_pembayaran);
			if ($('#table-show').val() == 0) {
				$new_tr.find('td').eq(0).text($target_row.length);
				$target_row.replaceWith($new_tr);
			} else {
				$new_tr.find('td').eq(0).text($target_row.length + 1);
				$tbody_target.append($new_tr);
			}
			$table.show();
			$('#table-show').val(1);
			$('.list-pembayaran-terpilih').find('.belum-ada').remove();
			$('.list-pembayaran-terpilih').append('<small  class="px-3 py-1 me-2 mb-2 text-success bg-success bg-opacity-10 border border-success rounded-2">' + jenis_pendapatan + '</small>');
		});
	
		/* alert();
		$tabel_list_item = $('#tabel-list-item-bayar');
		if ($tabel_list_item.is(':hidden')) {
			$tabel_list_item.show();
		} else {
			$tbody = $tabel_list_item.find('tbody');
			$tr = $tbody.children('.row-item-bayar:last');
			$new_tr = $tr.clone();
			$new_tr.appendTo($tbody);
			$new_tr.find('td:first').html( $tbody.find('.row-item-bayar').length);
			$new_tr.find('input').val('');
			$new_tr.find('.jenis-item-pembayaran').val(1).trigger('change');
		} */
	})
	
	$('body').delegate('.jenis-item-pembayaran', 'change', function() {
		$this = $(this);
		$select = $this.parents('tr:eq(0)').find('.spp-bulan').find('select');
		if ($(this).val() == 1) {
			// $select.prop('disabled', false);
			$select.show();
		} else {
			// $select.prop('disabled', true);
			$select.hide();
		}
	})
	
	$('body').delegate('.del-row', 'click', function() {
		$this = $(this);
		
		$tabel_list_item = $('#tabel-list-item-pendapatan');
		$tr = $tabel_list_item.find('tbody').find('tr');
		if ($tr.length == 1) {
			$tr.find('input').val('');
			$('#total-pembayaran').val('');
			$('#total-pembayaran').trigger('keyup');
			$tabel_list_item.hide();
			$('#table-show').val(0);
		} else {
			$this.parents('tr:eq(0)').remove();
			$tr = $('#tabel-list-item-pendapatan').find('tbody').find('tr');
			$tr.each(function(i, elm) {
				$(elm).find('td').eq(0).text(i + 1);
			})
		}
		calculate_total();
	})
		
	$('.btn-delete-all-data').click(function() {
		$this = $(this);
		$bootbox =  bootbox.dialog({
			title: 'Hapus Semua Data',
			message: '<div class="px-2">' +
						'<p>Tindakan ini akan menghapus semua data pada database tabel</p><ul class="list-circle"><li>pendapatan dengan id_pembayar not null</li><li>pendapatan_detail yang sesuai</li></ul>' +
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
							url: base_url + 'pendapatan-lain/ajaxDeleteAllData',
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
	
	$('body').delegate('.number', 'keyup', function() {
		this.value = format_ribuan(this.value);
	})
		
	$('body').delegate('.btn-delete-pendapatan-lain', 'click', function(e) {
		e.preventDefault();
		id = $(this).attr('data-id');
		
		$bootbox = bootbox.confirm({
			message: $(this).attr('data-delete-title'),
			buttons: {
				confirm: {
					label: 'Delete',
					className: 'btn-danger'
				},
				cancel: {
					label: 'Cancel',
					className: 'btn-secondary'
				}
			},
			callback: function(confirmed) {
				if (confirmed) {
					$button = $bootbox.find('button');
					$button.attr('disabled', 'disabled');
					$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
					$spinner.prependTo($bootbox.find('.bootbox-accept'));
					$.ajax({
						type: 'POST',
						url: base_url + 'pendapatan-lain/ajaxDeleteData',
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
	
	
	$('#btn-excel').click(function() {
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$spinner.prependTo($this);
		
		filename = 'Daftar Pembayar - ' + format_date('dd-mm-yyyy') + '.xlsx';
		export_url = base_url + 'riwayat-status-pembayar/ajaxExportExcel';
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
	
	// Invoice - PDF
	$('body').delegate('.save-pdf', 'click', function(e){
		e.preventDefault();
		$this = $(this);
		url = $this.attr('href');
		filename = $this.attr('data-filename').replace('/','_').replace('\\', '_')
		
		$swal =  Swal.fire({
			title: 'Memproses Invoice',
			text: 'Mohon sabar menunggu...',
			showConfirmButton: false,
			allowOutsideClick: false,
			didOpen: function () {
			  	Swal.showLoading();
			},
			didClose () {
				Swal.hideLoading()
			},
		});
		
		fetch(url)
		  .then(resp => resp.blob())
		  .then(blob => {
				saveAs(blob, filename + '.pdf');
				$swal.close();
		  })
		.catch(() => alert('Ajax Error'));

	})
	
	$('table').delegate('.btn-print-invoice', 'click', function(e) {
		e.preventDefault();
		const url = $(this).attr('data-url');
		window.open(url, top = 500, left = 500, width = 600, height = 600, menubar = 'no', status = 'no', titlebar = 'no'); 
		return false;
	});
});