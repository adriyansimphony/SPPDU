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
	
	// Pendapatan
	dataTablesPendapatan = '';
	column = $.parseJSON($('#dataTablesPendapatan-column').html());
	url = $('#dataTablesPendapatan-url').text();
	
	settings = {
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
	
	$add_setting = $('#dataTablesPendapatan-setting');
	if ($add_setting.length > 0) {
		add_setting = $.parseJSON($('#dataTablesPendapatan-setting').html());
		for (k in add_setting) {
			settings[k] = add_setting[k];
		}
	}
	
	dataTablesPendapatan =  $('#tabel-pendapatan').DataTable( settings );
	
	$('#btn-excel-siswa').click(function() {
		export_excel(this, base_url + 'siswa/ajaxExportExcel', 'Daftar Siswa - ' + format_date('dd-mm-yyyy') + '.xlsx');
	})
	
	// Chart Pendapatan
	backgroundColorItem = [];
	nama_pendapatan_kategori.map(function() {
		backgroundColorItem.push(dynamicColors());
	});

	var configChartPendapatanKategori = {
		type: 'doughnut',
		data: {
			datasets: [{
				data: total_pendapatan_kategori,
				backgroundColor: backgroundColorItem,
			}],
			labels: nama_pendapatan_kategori
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
				text: 'Pendapatan'
			  }
			}
		}
	};
	
	var ctx = document.getElementById('chart-pendapatan').getContext('2d');
	chartPendapatanKategori = new Chart(ctx, configChartPendapatanKategori);
	
	// Pengeluaran
	dataTablesPengeluaran = '';
	column = $.parseJSON($('#dataTablesPengeluaran-column').html());
	url = $('#dataTablesPengeluaran-url').text();
	
	settings = {
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
	
	$add_setting = $('#dataTablesPengeluaran-setting');
	if ($add_setting.length > 0) {
		add_setting = $.parseJSON($('#dataTablesPengeluaran-setting').html());
		for (k in add_setting) {
			settings[k] = add_setting[k];
		}
	}
	
	dataTablesPengeluaran =  $('#tabel-pengeluaran').DataTable( settings );

	// Chart Pengeluaran
	backgroundColorItem = [];
	nama_pengeluaran_kategori.map(function() {
		backgroundColorItem.push(dynamicColors());
	});

	var configChartPengeluaranKategori = {
		type: 'doughnut',
		data: {
			datasets: [{
				data: total_pengeluaran_kategori,
				backgroundColor: backgroundColorItem,
			}],
			labels: nama_pengeluaran_kategori
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
				text: 'Pengeluaran'
			  }
			}
		}
	};
	
	var ctx = document.getElementById('chart-pengeluaran').getContext('2d');
	chartPengeluaranKategori = new Chart(ctx, configChartPengeluaranKategori);

	$('#option-tahun-ajaran-siswa-baru').change(function() {
		
		$this = $(this);
		$spinner = $('<div class="spinner-container me-2" style="margin:auto">' + 
								'<div class="spinner-border spinner-border-sm"></div>' +
							'</div>').prependTo($this.parent());

		$.get(base_url + 'dashboard/ajaxGetSiswaBaru?tahun_ajaran=' + $(this).val(), function(data) {
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
	
	$('body').delegate('#btn-excel-pegawai', 'click', function() {
		export_excel(this, base_url + 'builtin/pegawai/ajaxExportExcel', 'Daftar Pegawai - ' + format_date('dd-mm-yyyy') + '.xlsx');
	})
	
	
	
	$('body').delegate('.btn-delete-pegawai', 'click', function(e) {
		e.preventDefault();
		deleteData(this, base_url + 'builtin/pegawai/ajaxDeleteData', dataTablesPegawai);
	})
	
	$('body').delegate('.btn-delete-siswa', 'click', function(e) {
		e.preventDefault();
		deleteData(this, base_url + 'siswa/ajaxDeleteData', dataTablesSiswa);
	})
});