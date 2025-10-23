$(document).ready(function() {
	
	function export_excel(obj, url, filename) {
		$this = $(obj);
		$this.prop('disabled', true);
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$spinner.prependTo($this);

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
		
	}
	
	// Siswa
	let dataTablesSiswa = '';
	let column = $.parseJSON($('#dataTablesSiswa-column').html());
	let url = $('#dataTablesSiswa-url').text();
	
	const settings = {
		"processing": true,
		"serverSide": true,
		"scrollX": false,
		pageLength : 5,
		lengthChange: false,
		"ajax": {
			"url": url,
			"type": "POST"
		},
		"columns": column
	}
	
	let $add_setting = $('#dataTablesSiswa-setting');
	if ($add_setting.length > 0) {
		add_setting = $.parseJSON($('#dataTablesSiswa-setting').html());
		for (k in add_setting) {
			settings[k] = add_setting[k];
		}
	}
	
	dataTablesSiswa =  $('#tabel-siswa').DataTable( settings );
	
	$('#option-kelas').change(function() {
		new_url = base_url + 'dashboard-siswa/getDataDTSiswa?id_kelas=' + $(this).val();
		dataTablesSiswa.ajax.url( new_url ).load();
	});
	
	$('#btn-excel-siswa').click(function() {
		export_excel(this, base_url + 'siswa/ajaxExportExcel', 'Daftar Siswa - ' + format_date('dd-mm-yyyy') + '.xlsx');
	})
	
	// Chart Siswa Baru Gender
	backgroundColorItem = [];
	backgroundColorItem.push('rgba(90, 205, 89, 0.8)')
	backgroundColorItem.push('rgba(133,113,189, 0.8)')

	var configChartSiswaGender = {
		type: 'doughnut',
		data: {
			datasets: [{
				data: siswa_baru_gender,
				backgroundColor: backgroundColorItem,
			}],
			labels: ['Perempuan', 'Laki - Laki']
		},
		options: {
			responsive: false,
			// maintainAspectRatio: false,
			title: {
				display: true,
				text: '',
				fontSize: 14,
				lineHeight:3
			},
			plugins: {
			  legend: {
				display: true,
				position: 'bottom',
				fullWidth: false,
				labels: {
					padding: 10,
					boxWidth: 30
				}
			  },
			  title: {
				display: false,
				text: 'Gender Siswa'
			  }
			}
		}
	};
	
	/* Chart Siswa */
	var ctx = document.getElementById('chart-gender-siswa').getContext('2d');
	chartSiswaGender = new Chart(ctx, configChartSiswaGender);
	
	$('#option-tahun-ajaran-siswa-baru').change(function() {
		
		$this = $(this);
		$spinner = $('<div class="spinner-container me-2" style="margin:auto">' + 
								'<div class="spinner-border spinner-border-sm"></div>' +
							'</div>').prependTo($this.parent());

		$.get(base_url + 'dashboard-siswa/ajaxGetSiswaBaru?tahun_ajaran=' + $(this).val(), function(data) {
			$spinner.remove();
			if (data) {
				
				data = JSON.parse(data);
			
				data_gender = [];
				data_gender.push(data.total_gender.jml_perempuan);
				data_gender.push(data.total_gender.jml_laki);
				
				configChartSiswaGender.data.datasets[0].data = data_gender
				
				/* randomBackground = [];
				randomBackground.push('rgba(90, 205, 89, 0.8)')
				randomBackground.push('rgba(133,113,189, 0.8)')
				
				configChartSiswaGender.data.datasets= [{
					data: data_gender,
					backgroundColor: randomBackground
				}] */
				
				chartSiswaGender.update();
				$detail = $('.chart-gender-siswa-detail');
				$detail.find('.jml-laki').html(data.total_gender.jml_laki);
				$detail.find('.jml-perempuan').html(data.total_gender.jml_perempuan);
				$detail.find('.jml-total').html(data.total);
			}
		});
	});
	/*-- Siswa Baru Gender */
	
	/* Pegawai */
	// Pegawai
	let dataTablesPegawai = '';
	let column_pegawai = $.parseJSON($('#dataTablesPegawai-column').html());
	let url_pegawai = $('#dataTablesPegawai-url').text();
	
	const setting_pegawai = {
		"processing": true,
		"serverSide": true,
		"scrollX": false,
		pageLength : 5,
		lengthChange: false,
		"ajax": {
			"url": url_pegawai,
			"type": "POST"
		},
		"columns": column_pegawai
	}
	
	let $add_setting_pegawai = $('#dataTablesPegawai-setting');
	if ($add_setting_pegawai.length > 0) {
		add_setting_pegawai = $.parseJSON($('#dataTablesPegawai-setting').html());
		for (k in add_setting_pegawai) {
			setting_pegawai[k] = add_setting_pegawai[k];
		}
	}
	
	dataTablesPegawai =  $('#tabel-pegawai').DataTable( setting_pegawai );
	btn_excel = '<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-excel-pegawai"><i class="fas fa-file-excel me-2"></i>XLSX</button>';
	$('#tabel-pegawai_wrapper').children().eq(0).children().eq(0).html(btn_excel);
	
	$('body').delegate('#btn-excel-pegawai', 'click', function() {
		export_excel(this, base_url + 'builtin/pegawai/ajaxExportExcel', 'Daftar Pegawai - ' + format_date('dd-mm-yyyy') + '.xlsx');
	})
	
	// Chart Pegawai Gender
	backgroundColorItem = [];
	backgroundColorItem.push('rgba(166,178,166,0.8)')
	backgroundColorItem.push('rgba(96,158,196,0.8)')
	
	var configChartPegawaiGender = {
		type: 'doughnut',
		data: {
			datasets: [{
				data: pegawai_gender,
				backgroundColor: backgroundColorItem,
			}],
			labels: ['Perempuan', 'Laki - Laki']
		},
		options: {
			responsive: false,
			// maintainAspectRatio: false,
			title: {
				display: true,
				text: '',
				fontSize: 14,
				lineHeight:3
			},
			plugins: {
			  legend: {
				display: true,
				position: 'bottom',
				fullWidth: false,
				labels: {
					padding: 10,
					boxWidth: 30
				}
			  },
			  title: {
				display: false,
				text: 'Gender Pegawai'
			  }
			}
		}
	};
	
	/* Chart Pegawai */
	var ctx = document.getElementById('chart-gender-pegawai').getContext('2d');
	chartPegawaiGender = new Chart(ctx, configChartPegawaiGender);
	
	function deleteData(obj, url, dataTables) 
	{
		$this = $(obj);
		id = $this.attr('data-id');
		$bootbox = bootbox.confirm({
			message: $this.attr('data-delete-title'),
			callback: function(confirmed) {
				if (confirmed) {
					$button = $bootbox.find('button');
					$button.attr('disabled', 'disabled');
					$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
					$spinner.prependTo($bootbox.find('.bootbox-accept'));
					$.ajax({
						type: 'POST',
						url: url,
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
	}
	
	$('body').delegate('.btn-delete-pegawai', 'click', function(e) {
		e.preventDefault();
		deleteData(this, base_url + 'builtin/pegawai/ajaxDeleteData', dataTablesPegawai);
	})
	
	$('body').delegate('.btn-delete-siswa', 'click', function(e) {
		e.preventDefault();
		deleteData(this, base_url + 'siswa/ajaxDeleteData', dataTablesSiswa);
	})
});