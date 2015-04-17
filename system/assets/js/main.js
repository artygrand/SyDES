$(document).ready(function(){
	if ($('.menu-top').length){
		header_height = $('#menu').height();
		$('#menu').css('height', '50px');
		if (getCookie('menu') != 'click'){
			$('#menu').hoverIntent(makeTall, makeShort)
		}
		$('#menu').click(function(){
			makeTall();$('#menuclick').show()
		})
		$('#menuclick').click(function(){
			makeShort();$('#menuclick').hide()
		})
	}
	
	if ($('.tab-container .col-xs-2').width() < 183){$('.tab-container .col-xs-2').toggleClass('col-xs-2 col-xs-3');$('.tab-container .col-xs-10').toggleClass('col-xs-10 col-xs-9')}

	$('[data-toggle="tooltip"]').tooltip();
	$("[data-toggle=popover]").popover({html: true});

	syd.notify(getCookie('notify.message'), getCookie('notify.status'));

	$('#checkall').click(function(){$('.ids').prop('checked', $(this).prop('checked'))})
	$('select.goto').change(function(){location.href = $(this).data('url') + $(this).val()})
	$('.submit').click(function(){ajaxFormApply()})

	$('#modal').on('show.bs.modal', function(e){
		var size = $(e.relatedTarget).data('size'), dialog = $(this).find('.modal-dialog')
		dialog.removeClass('modal-sm modal-lg')
		if (size){
			dialog.addClass('modal-' + size)
		}
		modalPosition()
	})
	$('#modal').on('loaded.bs.modal', function(e){modalPosition()})
	$('#modal').on('hidden.bs.modal', function (e){$(this).removeData('bs.modal')})
})
var syd = [];
syd.notify = function(m, s){
	if (m != null){
		$('#notify').append($('<li class="'+s+'">'+m+'</li>').delay(4000).slideUp())
	}
}
syd.alert = function(m, s){
	if (m != null){
		$('#alerts').append($('<div class="alert alert-'+s+' alert-dismissible"><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>'+m+'</div>'))
	}
}

var editorBuffer = '';
$(document).on('click', '.lazy.ckeditor', function(){
	var editor = CKEDITOR.replace(this, {
		toolbar: [
			[ 'Source', 'Save' ],
			[ 'Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', '-', 'Blockquote' ],
			[ 'Maximize', 'ShowBlocks', 'Image' ]
		],
		height: 200,
		allowedContent: true,
	});
	
	editor.on('blur', function(){
		if (editorBuffer != editor.getData()){
			for (var i in CKEDITOR.instances)
				CKEDITOR.instances[i].updateElement();
			$('.lazy.ckeditor').change()
		}
		editorBuffer = editor.getData();
	});
	editorBuffer = editor.getData();
})

$(document).on('click', '.apply-modal', function(){
	ajaxModalFormApply()
})

$(document).on('click', '.skin-selector a', function(){
	var skin = $(this).attr('title')
	$('#skin').attr('href', 'assets/css/skin.' + skin + '.css')
	setCookie('skin', skin, 7)
	return false
})

$.ajaxSetup({
	type: 'POST',
	data:{ajax: 1}
});
$(document).ajaxSend(function(e, xhr, settings){
	$('html').css('cursor', 'wait');
	settings.data += '&token='+token
}).ajaxSuccess(function(e, xhr, settings){
	if (getCookie('debug') == '1'){
		console.log(xhr.responseText)
	}
	if (xhr.getResponseHeader('Content-Type') == 'application/json'){
		window.respond = $.parseJSON(xhr.responseText)
		if ('notify' in window.respond){
			syd.notify(window.respond.notify.message, window.respond.notify.status)
		}
		if ('reload' in window.respond){
			window.location.reload()
		}
		if ('redirect' in window.respond){
			location.href = window.respond.redirect
		}
	}
}).ajaxError(function(){
	$('html').css('cursor', 'auto')
	syd.notify('AJAX 404 (Not Found)', 'danger')
}).ajaxComplete(function(){
	$('html').css('cursor', 'auto')
})

$(document).on('submit', 'form', function( event ){
	if (!$(this).find('input[name="token"]').length){
		event.preventDefault();
		$(this).append('<input type="hidden" name="token" value="'+token+'">').submit();
	}
});

var ua = navigator.userAgent.toLowerCase(),
	isIE = (ua.indexOf("msie") != -1 && ua.indexOf("opera") == -1),
	isSafari = ua.indexOf("safari") != -1,
	isGecko = (ua.indexOf("gecko") != -1 && !isSafari);
if (isIE || isSafari){
	addHandler(document, "keydown", hotSave)
} else {
	addHandler(document, "keypress", hotSave)
}

function makeTall(){$('#menu').animate({"height": header_height}, 150)}
function makeShort(){$('#menu').animate({"height": 50}, 150)}

function setCookie(n, v, x){
	var d = new Date();
	d.setDate(d.getDate() + x);
	var e = escape(v) + ((x == null) ? "" : "; expires=" + d.toUTCString());
	document.cookie = n + "=" + e;
}

function getCookie(n){
	var i, x, y, arr = document.cookie.split(';');
	for (i = 0; i < arr.length; i++){
		x = arr[i].substr(0, arr[i].indexOf('='));
		y = arr[i].substr(arr[i].indexOf('=') + 1);
		x = x.replace(/^\s+|\s+$/g, '');
		if (x == n){
			return decodeURI(y.replace(/\+/g, ' '))
		}
	}
}

function ajaxFormApply(){
	if (window.codemirror) window.codemirror.save();
	if (typeof CKEDITOR != 'undefined'){
		for (var instance in CKEDITOR.instances) {
			CKEDITOR.instances[instance].updateElement();
		}
	}
	var form = $('form[name="main-form"]');
	if (form.length){
		$.ajax({
			url: form.prop('action'),
			data: form.serialize()+'&act=apply'
		})
	}
}

function ajaxModalFormApply(){
	var form = $('form[name="modal-form"]');
	if (form.length){
		$.ajax({
			url: form.prop('action'),
			data: form.serialize(),
			complete: function(){
				location.reload()
			}
		})
	}
}

function addHandler(object, event, handler, useCapture){
	if (object.addEventListener) object.addEventListener(event, handler, useCapture);
	else if (object.attachEvent) object.attachEvent('on' + event, handler);
	else object['on' + event] = handler;
}

function hotSave(evt){
	evt = evt || window.event;
	var key = evt.keyCode || evt.which;
	key = !isGecko ? (key == 83 ? 1 : 0) : (key == 115 ? 1 : 0);
	if (evt.ctrlKey && key){
		if (evt.preventDefault) evt.preventDefault();
		evt.returnValue = false;
		ajaxFormApply();
		window.focus();
		return false;
	}
}

function toBuffer(fileUrl, data, allFiles){
	var body = '';
	for (var key in allFiles){
		if (allFiles.hasOwnProperty(key)){
			body += '<pre>' + allFiles[key]['url'] + '<br>&lt;img src="' + allFiles[key]['url'] + '"></pre>';
		}
	}
	$('#modal .modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Ctrl + C</h4></div>'+
		'<div class="modal-body">' + body + '</div>');
	$('#modal').modal('show');
}

function modalPosition(){
	$('.modal').each(function(){
		if ($(this).hasClass('in') == false){
			$(this).show();
		};
		var content = $(window).height() - 60;
		var header = $(this).find('.modal-header').outerHeight() || 2;
		var footer = $(this).find('.modal-footer').outerHeight() || 2;

		$(this).find('.modal-content').css({
			'max-height': function(){
				return content;
			}
		});

		$(this).find('.modal-body').css({
			'max-height': function(){
				return (content - (header + footer));
			}
		});

		$(this).find('.modal-dialog').css({
			'margin-top': function(){
				return -($(this).outerHeight() / 2);
			},
			'margin-left': function(){
				return -($(this).outerWidth() / 2);
			}
		});
		if ($(this).hasClass('in') == false){
			$(this).hide();
		};
	});
};

$(document).on('mousedown', '.field-date', function(){
	if (!$(this).hasClass('hasDatepicker')){
		$(this).datepicker({
			dateFormat: 'dd.mm.yy'
		})
	}
})

$(document).on('click', '.field-image', function(){BrowseServer('Images:/', $(this))})
$(document).on('click', '.field-file', function(){BrowseServer('Files:/', $(this))})
$(document).on('click', '.field-pdf', function(){BrowseServer('Files:/pdf/', $(this))})
$(document).on('click', '.field-flash', function(){BrowseServer('Flash:/', $(this))})
$(document).on('click', '.field-folder', function(){var e = $(this),folder = e.val().replace('/upload/images/', '');BrowseServer('Images:/' + folder + '/', e, 'crop')})

function BrowseServer(path, e, w){
	var finder = new CKFinder();
	finder.basePath = '../vendor/ckfinder/';
	finder.startupPath = path;
	if (w == 'crop'){
		finder.selectActionFunction = SetInputCropped
	} else {
		finder.selectActionFunction = SetInput
	}
	finder.selectActionData = e;
	finder.popup();
}

function SetInput(fileUrl, data, allFiles){
	var files = []
	for (var file in allFiles){
		files.push(allFiles[file]['url'])
	}
	data['selectActionData'].val(files.join()).change();
}

function SetInputCropped(fileUrl, data){
	data['selectActionData'].val(fileUrl.split('/').splice(3, fileUrl.split('/').length - 4).join('/')).change()
}