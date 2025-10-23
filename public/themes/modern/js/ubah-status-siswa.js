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
	
		const $setting = $('#dataTables-setting');
		let settings = {};
		if ($setting.length > 0) {
			settings = $.parseJSON($('#dataTables-setting').html());
		}
		
		dataTables =  $('#table-result').DataTable( settings );
		
		// No urut
		table.on( 'order.dt search.dt', function () {
			table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
				cell.innerHTML = i+1;
			} );
		} ).draw();
	}
		
	$('.submit-data').click(function(e) {
		e.preventDefault();
		if(!$('#option-kelas-tujuan').is(':hidden') && $('#option-kelas-asal').val() == $('#option-kelas-tujuan').val()) 
		{
			bootbox.alert('<div class="alert alert-danger">Error: Kelas asal dan kelas tujuan harus berbeda</div>');
			return;
		}
		
		if ($('#tabel-siswa').find('tbody').find('.form-check-input:checkbox:checked').length == 0) {
			bootbox.alert('<div class="alert alert-danger">Error: Siswa belum dipilih</div>');
			return;
		}
		
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
			url: base_url + 'ubah-status-siswa/ajaxSaveData',
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
					$('#option-kelas-asal').trigger('change');
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
	
	$('#check-all').click(function() {
		checked_status = $(this).is(':checked') ? true : false;
		$('#tabel-siswa').find('.form-check-input').prop('checked', checked_status);
		
		if (checked_status) {
			jml_checked = $('#tabel-siswa').find('tbody').find('.form-check-input').length;
			$('#jml-siswa-dipilih').text(jml_checked);
		} else {
			$('#jml-siswa-dipilih').text(0);
		}
	})
	
	$('.select2').select2({theme: 'bootstrap-5'});
	
	$('#tabel-siswa').delegate('.form-check-input', 'click', function() {
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
	
	$('.flatpickr').flatpickr({
		dateFormat: "d-m-Y"
	});
	
	$('#id-status-siswa').change(function() {
		value = parseInt($(this).val());
		if (value <= 4 ) {
			$('#row-pindah-ke').show();
		} else {
			$('#row-pindah-ke').hide();
		}
	})
	dataTablesSiswa = '';
	$('#option-kelas-asal').change(function() {
		
		$('#check-all').prop('checked', false);
		$this = $(this);
		$spinner = $('<div class="spinner-container" style="position: absolute;right: -25px;top: 5px;">' + 
								'<div class="spinner-border spinner-border-sm"></div>' +
							'</div>').appendTo($this.parent());
							// return;
		if (dataTablesSiswa) {
			dataTablesSiswa.destroy();
		}
		
		$tbody = $this.parents('.card').eq(0).find('tbody');
		len = $this.parents('.card').eq(0).find('th').length;
		html = '<tr><td colspan="' + len + '" class="text-center align-middle" style="height:50vh"><span class="spinner-border spinner-border-sm me-2"></span>Loading data...</td></tr>';
		$tbody.html(html);
	
		$.get(base_url + 'ubah-status-siswa/ajaxGetSiswaByIdKelas?id=' + $(this).val(), function(data) {
			$spinner.remove();
			if (data) {
				data = JSON.parse(data);
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

	$('#option-kelas-asal').trigger('change');
	
	$('.form-siswa').delegate('input[type="search"]', 'keyup', function() {
		jml_checkbox = $('#tabel-siswa').find('tbody').find('.form-check-input').length;
		jml_checked = $('#tabel-siswa').find('tbody').find('.form-check-input:checkbox:checked').length;
		$('#jml-siswa-dipilih').text(jml_checked);
		console.log(jml_checkbox);
		console.log(jml_checked);
		if (jml_checkbox == jml_checked) {
			$('#check-all').prop('checked', true);
		} else {
			$('#check-all').prop('checked', false);
		}	
	});
});