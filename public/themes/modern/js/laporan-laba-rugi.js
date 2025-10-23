/**
* Written by: Agus Prawoto Hadi
* Year		: 2023-2023
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	$('.select2').select2({'theme' : 'bootstrap-5'});
	$('.flatpickr').flatpickr({
		enableTime: true,
		dateFormat: "d-m-Y"
	});
	
	$('.btn-print').click(function(e) {
		e.preventDefault();
		const url = $(this).attr('data-url');
		window_width = $(window).width();
		window_height = $(window).height();
		width = Math.round(window_width - (window_width * 20/100));
		left = Math.round((window_width - width)/2);
		windowFeatures = 'left=' + left + ',top=100,width=' + width + ',height=' + window_height;
		// window.open(url, top = 700, left = 1000, width = 1200, height = 800, menubar = 'no', status = 'no', titlebar = 'no'); 
		window.open(url, 'chrome', windowFeatures); 
		return false;
	})
	
	$('.btn-excel').click(function() {
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$spinner.prependTo($this);
		query_string = $('#query-string').text() ? $('#query-string').text() : 'daterange=' + $('#daterange').val() + '&tampilkan_pengeluaran=' + $('.tampilkan-pengeluaran').val();
		params = new URLSearchParams(query_string);
		
		filename = 'Laporan Laba Rugi - ' + params.get('daterange') + '.xlsx';
		url = base_url + 'laporan-laba-rugi/ajaxExportExcel?' + query_string;
		fetch(url)
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
	
	$('.btn-pdf').click(function() {
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$spinner.prependTo($this);
		query_string = $('#query-string').text() ? $('#query-string').text() : 'daterange=' + $('#daterange').val() + '&tampilkan_pengeluaran=' + $('.tampilkan-pengeluaran').val();
		params = new URLSearchParams(query_string);
		
		filename = 'Laporan Laba Rugi - ' + params.get('daterange') + '.pdf';
		url = base_url + 'laporan-laba-rugi/ajaxExportPdf?' + query_string;
		fetch(url)
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
	
	$('#btn-send-email').click(function() {
		$bootbox =  bootbox.dialog({
			title: 'Kirim Email',
			message: '<form method="post" class="px-2">' +
				'<div class="row mb-3">' +
					'<label class="col-sm-3 col-form-label">Email</label>' +
					'<div class="col-sm-8">' +
						'<input class="form-control" name="email" id="email-address" value="laporanpenjualan@yopmail.com"/>' +
					'</div>' +
				'</div>'+
				'<div class="row mb-3">' +
					'<label class="col-sm-3 col-form-label">Format File</label>' +
					'<div class="col-sm-8">' +
						'<select class="form-select" name="fromat_file" id="format-file"><option value="excel">Excel</option><option value="pdf">PDF</option></select>' +
					'</div>' +
				'</div>'+
			'</form>',
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				success: {
					label: 'Submit',
					className: 'btn-success submit',
					callback: function() 
					{
						var $button = $bootbox.find('button').prop('disabled', true);
						var $button_submit = $bootbox.find('button.submit');
						
						$bootbox.find('.alert').remove();
						$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
						$button_submit.prepend($spinner);
						$button.prop('disabled', true);
						
						start_date = $('#start-date').val();
						end_date = $('#end-date').val();
						$.ajax({
							type: 'GET',
							url: current_url + '/ajaxSendEmail?start_date=' + start_date + '&end_date=' + end_date + '&email=' + $('#email-address').val() + '&ajax=true&file=true&file_format=' + $('#format-file').val(),
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
										html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Email berhasil dikirim</div>'
									})
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
	},	function(start, end, label) 
	{
		
	})
});