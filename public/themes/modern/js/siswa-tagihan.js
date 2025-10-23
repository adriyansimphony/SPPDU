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
	
	$('.btn-add-tagihan').click(function() {
		
		$bootbox =  bootbox.dialog({
			title: 'Edit Data',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				success: {
					label: 'Submit',
					className: 'btn-success submit-data submit',
					callback: function() 
					{
						if(!$('#nilai-tagihan').val()) {
							$bootbox.hide();
							bootbox.alert('<div class="alert alert-danger">Error: Nilai Tagihan harus diisi</div>', function() {
								$bootbox.show();
							});
							return false;
						}
						
						if ($('#tabel-siswa').find('tbody').find('.form-check-input:checkbox:checked').length == 0) {
							$bootbox.hide();
							bootbox.alert('<div class="alert alert-danger">Error: Siswa belum dipilih</div>', function() {
								$bootbox.show();
							});
							return false;
						}
						
						$button.prop('disabled', true);
						data = $('.form-siswa').serialize();
						$spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
						$spinner.prependTo($button_submit);
						$.ajax({
							type: 'POST',
							url: base_url + 'siswa-tagihan/ajaxSaveDataAdd',
							data: data,
							dataType: 'json',
							success: function (data) {
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
										html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil disimpan</div>'
									})
									$('#option-kelas-asal').trigger('change');
									$('.btn-delete-all-data').prop('disabled', false);
								} else {
									show_alert('Error !!!', data.message, 'error');
								}
								
								dataTables.draw();
							},
							error: function (xhr) {
								$button.prop('disabled', false);
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
	
		var $button = $bootbox.find('button').prop('disabled', true);
		var $button_submit = $bootbox.find('button.submit');
		$bootbox.find('.modal-dialog').css('max-width', '700px');
		$.get(base_url + 'siswa-tagihan/ajaxGetFormAdd', function(html){
			$button.prop('disabled', false);
			$bootbox.find('.modal-body').empty().append(html);
			$('#option-kelas').trigger('change');
		});
	});
	
	$('#table-result').delegate('.btn-edit', 'click', function() {
		
		$bootbox =  bootbox.dialog({
			title: 'Edit Data',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				success: {
					label: 'Submit',
					className: 'btn-success submit-data submit',
					callback: function() 
					{
						if(!$('#nilai-tagihan').val()) {
							$bootbox.hide();
							bootbox.alert('<div class="alert alert-danger">Error: Nilai Tagihan harus diisi</div>', function() {
								$bootbox.show();
							});
							return false;
						}
						
						$button.prop('disabled', true);
						data = $('.form-siswa').serialize();
						$spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
						$spinner.prependTo($button_submit);
						$.ajax({
							type: 'POST',
							url: base_url + 'siswa-tagihan/ajaxSaveDataEdit',
							data: data,
							dataType: 'json',
							success: function (data) {
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
										html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil disimpan</div>'
									})
								} else {
									show_alert('Error !!!', data.message, 'error');
								}
								
								dataTables.draw();
							},
							error: function (xhr) {
								$button.prop('disabled', false);
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
	
		var $button = $bootbox.find('button').prop('disabled', true);
		var $button_submit = $bootbox.find('button.submit');
		id = $(this).attr('data-id');
		bulan = $(this).attr('data-bulan');
		tahun = $(this).attr('data-tahun');
		$.get(base_url + 'siswa-tagihan/ajaxGetFormEdit?id=' + id, function(html){
			$button.prop('disabled', false);
			$bootbox.find('.modal-body').empty().append(html);
			$('#option-kelas').trigger('change');
		});
	});
	
	$('body').delegate('#option-kelas', 'change', function() {
		
		$('#check-all').prop('checked', false);
		$this = $(this);
		/* $spinner = $('<div class="spinner-container" style="position: absolute;right: -25px;top: 5px;">' + 
								'<div class="spinner-border spinner-border-sm"></div>' +
							'</div>').appendTo($this.parent());
							return; */
		if (dataTablesSiswa) {
			dataTablesSiswa.destroy();
		}
		
		$tbody = $('#tabel-siswa').find('tbody');
		len = $('#tabel-siswa').find('th').length;
		html = '<tr><td colspan="' + len + '" class="text-center align-middle" style="height:50vh"><span class="spinner-border spinner-border-sm me-2"></span>Loading data...</td></tr>';
		$tbody.html(html);
	
		$.get(base_url + 'siswa-tagihan/ajaxGetSiswaByGroupKelas?id=' + $(this).val(), function(data) {
			// $spinner.remove();
			if (data) {
				data = JSON.parse(data);
				// console.log(data);
				if (data.length == 0) {
					$('#check-all').prop('disabled', true);
				} else {
					$('#check-all').prop('disabled', false);
				}
				html = '';
				data.map( (item, index) => {
					html += '<tr>' +
								'<td>' + (index + 1) + '</td>' +
								'<td>' + item.nama + '</td>' +
								'<td class="text-end">' + item.nis + '</td>' +
								'<td class="text-end">' + item.nama_kelas + '</td>' +
								'<td><div class="form-check text-start fw-bold">' +
										  '<input name="id_siswa[]" class="form-check-input" type="checkbox" value="'+ item.id_siswa +'" id="siswa-' + item.id_siswa + '">' +
										  '<label class="form-check-label" for="siswa-' + item.id_siswa + '">Pilih</label>' +
									'</div></td>' +
							'</tr>';
				})
				
				$tbody.html(html);
				initDataTablesSiswa();
				$('#jml-siswa-dipilih').text(0);
			}
		});
	})
	
	$('#option-kelas-result').change(function() {
		new_url = base_url + 'siswa-tagihan/getDataDT?kelas=' + $(this).val();
		dataTables.ajax.url( new_url ).load();
	});
	
	$('body').delegate('.number', 'keyup', function() {
		this.value = format_ribuan(this.value);
	})
	
	dataTablesSiswa = '';
	function initDataTablesSiswa() {
		let settings = {
				order:[1,"asc"]
				, columnDefs:[{targets:[0,4], orderable:false}]
				, paging: false
				, lengthChange: false
				, scrollY: '50vh'
				, bInfo : false
			};
		
		dataTablesSiswa = $('#tabel-siswa').DataTable(settings);
		
		// No urut
		dataTablesSiswa.on( 'order.dt search.dt', function () {
			dataTablesSiswa.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
				cell.innerHTML = i+1;
			} );
		} ).draw();
		$('#tabel-siswa_wrapper').addClass('mt-3');
		$('#tabel-siswa_wrapper').children('.row').children().eq(0).remove();
	}
	
	$('body').delegate('input[type="search"]', 'keyup', function() {
		jml_checkbox = $('#tabel-siswa').find('tbody').find('.form-check-input').length;
		jml_checked = $('#tabel-siswa').find('tbody').find('.form-check-input:checkbox:checked').length;
		$('#jml-siswa-dipilih').text(jml_checked);
		if (jml_checkbox == jml_checked) {
			$('#check-all').prop('checked', true);
		} else {
			$('#check-all').prop('checked', false);
		}	
	});
	
	$('body').delegate('#check-all', 'click', function() {
		checked_status = $(this).is(':checked') ? true : false;
		$('#tabel-siswa').find('.form-check-input').prop('checked', checked_status);
		
		if (checked_status) {
			jml_checked = $('#tabel-siswa').find('tbody').find('.form-check-input').length;
			$('#jml-siswa-dipilih').text(80);
		} else {
			$('#jml-siswa-dipilih').text(0);
		}
	})
	
	// $('.select2').select2({theme: 'bootstrap-5'});
	
	$('body').delegate('.form-check-input', 'click', function() {
		id = $(this).attr('id');
		if (id == 'check-all') {
			return;
		}
		jml_awal = parseInt($('#jml-siswa-dipilih').text());
		if ($(this).is(':checked')) {
			jml_checkbox = $('#tabel-siswa').find('tbody').find('.form-check-input').length;
			jml_checked = $('#tabel-siswa').find('tbody').find('.form-check-input:checkbox:checked').length;
			if (jml_checkbox == jml_checked) {
				$('#check-all').prop('checked', true);
			}
			$('#jml-siswa-dipilih').text(jml_awal + 1);
		} else {
			$('#check-all').prop('checked', false);
			$('#jml-siswa-dipilih').text(jml_awal - 1);
		}
	});
	
	// Periode Bulan
	function change_periode(perubahan = 'awal') 
	{
		bulan_awal = parseInt($('#bulan-awal').val());
		bulan_akhir = parseInt($('#bulan-akhir').val());
		tahun_awal = parseInt($('#tahun-awal').val());
		tahun_akhir = parseInt($('#tahun-akhir').val());
		
		if (perubahan == 'awal') {
			if (bulan_akhir < bulan_awal && tahun_awal == tahun_akhir) {
				$('#bulan-akhir').val(bulan_awal);
			}
			
			if (tahun_akhir <= tahun_awal) {
				$('#tahun-akhir').val(tahun_awal);
				if (bulan_akhir < bulan_awal) {
					$('#bulan-akhir').val(bulan_awal);
				}	
			}
		} else {
			if (bulan_awal > bulan_akhir && tahun_awal == tahun_akhir) {
				$('#bulan-awal').val(bulan_akhir);
			}
			
			if (tahun_awal >= tahun_akhir) {
				$('#tahun-awal').val(tahun_akhir);
				if (bulan_akhir < bulan_awal) {
					$('#bulan-awal').val(bulan_akhir);
				}	
			}			
		}
	}
	
	$('body').delegate('#bulan-awal, #tahun-awal', 'change', function() {
		change_periode();
	});
	
	$('body').delegate('#bulan-akhir, #tahun-akhir', 'change', function() {
		change_periode('akhir');
	});
	//-- Periode Bulan
	
	$('body').delegate('#id-pendapatan-jenis', 'change', function() {
		jenis = JSON.parse($('#pendapatan-jenis').text());
		$('.row-periode-bulan, .row-periode-tahun, .row-periode-tahun-ajaran').hide();
		if (jenis[this.value].using_periode == 'Y') {
			if (jenis[this.value].jenis_periode == 'bulan') {
				$('.row-periode-bulan').show();
			} else if (jenis[this.value].jenis_periode == 'tahun') {
				$('.row-periode-tahun').show();
			} else if (jenis[this.value].jenis_periode == 'tahun_ajaran') {
				$('.row-periode-tahun-ajaran').show();
			}
		}
	});
	
	$('body').delegate('.btn-delete', 'click', function(e) {
		e.preventDefault();
		id = $(this).attr('data-id');
		bulan = $(this).attr('data-bulan');
		tahun = $(this).attr('data-tahun');
		
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
						url: base_url + 'siswa-tagihan/ajaxDeleteData',
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
	
	$('.btn-delete-all-data').click(function() {
		
		$bootbox =  bootbox.dialog({
			title: 'Hapus Semua Data',
			message: '<div class="px-2">' +
						'<p>Tindakan ini akan menghapus semua data pada database tabel siswa_tagihan</p>' +
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
							url: base_url + 'siswa-tagihan/ajaxDeleteAllData',
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
									$('.btn-delete-all-data').prop('disabled', true);
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
		
		filename = 'Daftar Siswa - ' + format_date('dd-mm-yyyy') + '.xlsx';
		export_url = base_url + 'siswa-tagihan/ajaxExportExcel';
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
});