jQuery(document).ready(function () {
	$('#gunakan-footer').change(function() {
		// alert();
		if (this.value == 'Y') {
			$('.row-footer').show();
		} else {
			$('.row-footer').hide();
		}
	})
});
