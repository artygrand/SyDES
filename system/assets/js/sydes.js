var syd = syd || {'settings':{}, 'l10n':{}};

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
	if (syd.l10n && syd.l10n[str]){
		return syd.l10n[str];
	} else {
		return str;
	};
};

/**
 * Sets and gets cookies
 * @param key
 * @param value
 *   If passed, then assign a value to the key
 * @param days
 *   If passed value, then specify the period
 */
syd.cookie = function(key, value, days){
	if (arguments.length > 1){
		var d = new Date();
		d.setDate(d.getDate() + days);
		var e = escape(value) + ((days == null) ? '' : '; expires=' + d.toUTCString());
		document.cookie = key + '=' + e;
	} else {
		var i, x, y, arr = document.cookie.split(';');
		for (i = 0; i < arr.length; i++){
			x = arr[i].substr(0, arr[i].indexOf('='));
			y = arr[i].substr(arr[i].indexOf('=') + 1);
			x = x.replace(/^\s+|\s+$/g, '');
			if (x == key){
				return decodeURI(y.replace(/\+/g, ' '))
			}
		}
	};
};

/**
 * Creates a random string of a certain length
 * @param length
 */
syd.token = function(length){
	var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz',
		string = '';
	for (var i=0; i<length; i++){
		string += chars.charAt(Math.floor(Math.random() * chars.length));
	}
	return string;
};

})(jQuery);