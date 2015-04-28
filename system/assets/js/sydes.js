var syd = syd || {'settings':{}, 'translations':{}};

(function ($){
/**
 * Shows notice on top right corner of window
 * @param message
 *   Message, only text
 * @param status
 *   Status, may be one of 'success', 'info', 'warning', 'danger'
 * @param delay
 *   Delay in milliseconds before hiding
 */
 
syd.notify = function(message, status, delay){
	status = status || 'info';
	delay = delay || 4000;
	if (!$('#notify').length){
		$('body').append($('<ul id="notify"></ul>'));
	};
	if (message != null){
		$('#notify').append($('<li class="'+status+'">'+message+'</li>').delay(delay).slideUp());
	};
};

/**
 * Shows dismissible alert box
 * @param message
 *   Message, text or html
 * @param status
 *   Status, may be one of 'success', 'info', 'warning', 'danger'
 */
syd.alert = function(message, status){
	var duplicate = false;
	$('.alert').each(function(){
		if ($(this).text() == '×'+message){
			duplicate = true;
		}
	})
	status = status || 'info';
	if (message != null && !duplicate){
		$('#alerts').append($('<div class="alert alert-'+status+' alert-dismissible"><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>'+message+'</div>'));
	};
};

/**
 * Translate strings to the page language
 * @param str
 *   A string containing the string to translate.
 */
syd.t = function(str){
	if (syd.translations && syd.translations[str]){
		return syd.translations[str];
	} else {
		return str;
	};
};


})(jQuery);