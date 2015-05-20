$(document).ready(function(){
	if (typeof jQuery.fn.datepicker !== "undefined"){
	$('.datepicker').datepicker({
		format: syd.settings.datepicker_format,
		language: syd.settings.locale,
		autoclose: true,
		todayHighlight: true,
	});
	}
});

$(document).on('submit', '.dform', function(){
	var valid = true;
	$('[type="file"]', this).each(function(){
		if (this.files[0] && this.files[0].size > 1048576 * 2){
			alert(syd.t('file_too_heavy'));
			return valid = false;
		}
	});
	if (!valid){
		return false;
	}

	$('[type="submit"]', this).text(syd.t('please_wait'));
	var form = this;
	$.ajax({
		type: 'POST',
		url: $(form).prop('action'),
		data: new FormData(form),
		processData: false,
		contentType: false,
		success: function(response){
			if ('message' in response){
				$(form).html(response.message);
			} else {
				$(form).html(syd.t('submit_error'));
			}
		},
		error: function(){
			$(form).html(syd.t('submit_error'));
		}
	});

	return false;
})