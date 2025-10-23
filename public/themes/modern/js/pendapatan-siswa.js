/**
* Written by: Agus Prawoto Hadi
* Year		: 2023-2023
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	if ($('#table-result-pendapatan-siswa').length > 0) {
		
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
		
		dataTables =  $('#table-result-pendapatan-siswa').DataTable( settings );
		$('#option-kelas').change(function() {
			new_url = base_url + 'pendapatan-siswa/getDataDT?kelas=' + $(this).val();
			dataTables.ajax.url( new_url ).load();
		})
	}
	
	// Siswa
	$('body').delegate('.cari-siswa', 'click', function() {
		$bootbox.hide();
		$('.modal-backdrop').hide();
		$this = $(this);
		var $modal = jwdmodal({
			title: 'Pilih Siswa',
			url: base_url + '/pendapatan-siswa/getListSiswa',
			width: '650px',
			action :function () 
			{
				
			}
		});
		
		$(document)
		.undelegate('.btn-pilih-siswa', 'click')
		.delegate('.btn-pilih-siswa', 'click', function() {
			$bootbox.show();
			$('.modal-backdrop').show();
			// Siswa popup
			$this = $(this);
			$this.attr('disabled', 'disabled');
			siswa = JSON.parse($(this).next().text())
			$('#id-siswa').val(siswa.id_siswa);
			$('#nama-siswa').val(siswa.nama);
			$modal.remove();
		});
	});
	
	$('body').delegate('.btn-edit-pendapatan-siswa', 'click', function(e) {
		e.preventDefault();
		showFormPendapatanSiswa('edit', $(this).attr('data-id'))
	})
	
	$('body').delegate('.btn-add-pendapatan-siswa', 'click', function(e) {
		e.preventDefault();
		showFormPendapatanSiswa();
	})
	
	function showFormPendapatanSiswa(type='add', id = '') {
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
						if (!error_message && $form.find('#id-siswa').val() == '') {
							error_message = 'Siswa belum dipilih';
						}
						
						if (!error_message && $form.find('.tanggal-invoice').val() == '') {
							error_message = 'Tanggal invoice harus diisi';
						}
						
						$form = $bootbox.find('form');
						if (!error_message && $form.find('.tanggal-bayar').val() == '') {
							error_message = 'Tanggal bayar harus diisi';
						}
						
						if (!error_message && $('#tabel-list-item-pendapatan').is(':hidden')) {
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
							url: base_url + 'pendapatan-siswa/ajaxSaveData',
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
		$bootbox.find('.modal-dialog').css('max-width', '700px');
		var $button = $bootbox.find('button').prop('disabled', true);
		var $button_submit = $bootbox.find('button.submit');
		
		$.get(base_url + 'pendapatan-siswa/ajaxGetFormDataPendapatanSiswa?id=' + id, function(html){
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
	
	$('body').delegate('.add-item-pembayaran-siswa', 'click', function() {
		
		id_siswa = $('#id-siswa').val();
		if (!id_siswa) {
			$('.modal-backdrop').hide();
			$bootbox.css('z-index', '10');
			bootbox.alert('Siswa belum dipilih', function() {
				$bootbox.css('z-index', '');
				$('.modal-backdrop').show();
			})
			return false;
		}
		$table = $('#tabel-list-item-pendapatan');
		table_visible = $table.is(':visible');
		$bootbox.hide();
		$('.modal-backdrop').hide();
		$this = $(this);
		var $modal = jwdmodal({
			title: 'Pilih Jenis Pembayaran',
			url: base_url + '/pendapatan-siswa/getListJenisPembayaran?id_siswa=' + id_siswa,
			width: '750px',
			onClose: function() {
				$bootbox.show();
				$('.modal-backdrop').show();
			},
			action :function () 
			{
				var list_bayar = '<span class="belum-ada mb-2">Pembayaran belum dipilih</span>';
				if (table_visible) {
					$trs = $table.find('tbody').eq(0).find('tr');
					var list_bayar = '';
					$trs.each (function (i, elm) {
						$td = $(elm).find('td');
						list_bayar += '<small  class="px-3 py-2 me-2 mb-2 text-success bg-success bg-opacity-10 border border-success rounded-2">' + $td.eq(1).html() + '</small>';
					});
				}
				$('.jwd-modal-header-panel').prepend('<div class="list-pembayaran-terpilih p-3 border d-flex flex-wrap">' + list_bayar + '</div>');
			},
			DTDrawCallback: function() {
				list_id_tagihan = []
				$data = $table.find('textarea');
				console.log($data.length);
				$data.each(function(i, elm) {
					data = JSON.parse($(elm).val());
					list_id_tagihan.push(data.id_siswa_tagihan);
				});
				console.log(list_id_tagihan);
				$btn = $('.jwd-modal').find('.btn-pilih-pembayaran');
				$btn.each (function(i, elm) {
					data = JSON.parse($(elm).next().val());
					if($.inArray(data.id_siswa_tagihan,  list_id_tagihan) !== -1) {
						console.log(data.id_siswa_tagihan);
						$(elm).prop('disabled', true);
					}
				});
			}
		});
		
		$(document)
		.undelegate('.btn-pilih-pembayaran', 'click')
		.delegate('.btn-pilih-pembayaran', 'click', function() {

			$this = $(this);
			$this.attr('disabled', 'disabled');

			$tr_popup = $this.parents('tr').eq(0);
			jenis_bayar = $tr_popup.find('td').eq(1).text();
			tagihan = $tr_popup.find('td').eq(2).text();
			kurang = $tr_popup.find('td').eq(4).text();
			$detail_jenis_pembayaran = $this.parent().children('textarea');
			$tbody_target = $('#tabel-list-item-pendapatan').find('tbody');
			$target_row = $tbody_target.find('tr');
			$new_tr = $tbody_target.find('tr:last').clone();
			$new_tr.find('input').val('');
			$new_tr.find('td').eq(1).html(jenis_bayar);
			$new_tr.find('td').eq(2).text(tagihan);
			$new_tr.find('td').eq(4).text(kurang);
			$new_tr.find('td').eq(3).val('');
			$new_tr.find('td').eq(3).find('textarea').remove();
			$new_tr.find('td').eq(3).append($detail_jenis_pembayaran);
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
			$('.list-pembayaran-terpilih').append('<small  class="px-3 py-2 me-2 mb-2 text-success bg-success bg-opacity-10 border border-success rounded-2">' + jenis_bayar + '</small>');
		});
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
						'<p>Tindakan ini akan menghapus semua data pada database tabel</p><ul class="list-circle"><li>siswa_bayar</li><li>siswa_bayar_detail</li></ul>' +
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
							url: base_url + 'pendapatan-siswa/ajaxDeleteAllData',
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
		
	$('body').delegate('.btn-delete-pendapatan-siswa', 'click', function(e) {
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
						url: base_url + 'pendapatan-siswa/ajaxDeleteData',
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
	
	// Invoice - PDF
	$('body').delegate('.save-pdf-siswa', 'click', function(e){
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
	
	$('table').delegate('.btn-print-invoice-siswa', 'click', function(e) {
		e.preventDefault();
		const url = $(this).attr('data-url');
		window.open(url, 'Window', "top = 200, left = 200, width = 1000, height = 700, menubar = 'no', status = 'no', titlebar = 'no'"); 
		return false;
	});
});