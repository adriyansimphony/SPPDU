/**
* Written by: Agus Prawoto Hadi
* Year		: 2023-2023
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	dataTables = '';
	if ($('#table-result').length > 0) {
		
		const column = $.parseJSON($('#dataTables-column').html());
		url = $('#dataTables-url').text();
		
		current_url = document.location.href;
		new_url = new URL(current_url);
		query_string = new_url.searchParams.toString();
		if (query_string) {
			url =url + '?' + query_string;
		}
		
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
							url: base_url + 'pendapatan/ajaxDeleteAllData',
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
	
	$('#btn-excel-pendapatan').click(function() {
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$spinner.prependTo($this);
		
		url = base_url + 'pendapatan/ajaxExportExcel';
		current_url = document.location.href;
		new_url = new URL(current_url);
		query_string = new_url.searchParams.toString();
		if (query_string) {
			url = url + '?' + query_string;
		}
		
		if (query_string) {
			daterange = new_url.searchParams.get('daterange');
			if (!daterange) {
				daterange = $('#daterange').val();
			}
		} else {
			daterange = $('#daterange').val();
		}
		
		filename = 'Rincian Pendapatan - '  + daterange + '.xlsx';
		export_url = url;
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
	
	$('#tampilkan-pendapatan').change(function() {
		current_url = document.location.href;
		new_url = new URL(current_url);
		new_url.searchParams.set('tampilkan_pendapatan', this.value);
		history.pushState({}, null, new_url);
		
		new_dt_url = base_url + 'pendapatan/getDataDT?' + new_url.searchParams.toString();
		dataTables.ajax.url( new_dt_url ).load();
	})
		
	$('body').delegate('.btn-delete', 'click', function(e) {
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
						url: base_url + 'pendapatan/ajaxDeleteData',
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
								$('#tampilkan-pendapatan').trigger('change');
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
	
	$('#daterange').daterangepicker({
		opens: 'right',
		ranges: {
             'Hari ini': [moment(), moment()],
			 'Bulan ini': [moment().startOf('month'), moment()],
             'Tahun ini': [moment().startOf('year'), moment()],
             '7 Hari Terakhir': [moment().subtract('days', 6), moment()],
             '30 Hari Terakhir': [moment().subtract('days', 29), moment()],
             
          },
		showDropdowns: true,
		   "linkedCalendars": false,
		locale: {
			customRangeLabel: 'Pilih Tanggal',
            format: 'DD-MM-YYYY',
			applyLabel: 'Pilih',
			separator: " s.d. ",
				 "monthNames": [
				"Januari",
				"Februari",
				"Maret",
				"April",
				"Mei",
				"Juni",
				"Juli",
				"Agustus",
				"September",
				"Oktober",
				"November",
				"Desember"
			],
        }
	},	function(startDate, endDate, label) 
	{
		start = startDate.format('DD-MM-YYYY');
		end = endDate.format('DD-MM-YYYY');
	
		current_url = document.location.href;
		new_url = new URL(current_url);
		new_url.searchParams.set('daterange', start + ' s.d. ' + end);
		history.pushState({}, null, new_url);

		new_dt_url = base_url + 'pendapatan/getDataDT?' + new_url.searchParams.toString();
		dataTables.ajax.url( new_dt_url ).load();		
	})
});