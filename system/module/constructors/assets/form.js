var count = 1,
	field = function(type, values){
		var heading = '<span class="heading-title"></span> <span class="label label-default">' + type + '</span>',
			content;

		switch (type){
			case 'textarea':
				content = getFields(['base', 'text', 'text-rows', 'attr']);
				break
			case 'file':
				content = getFields(['base', 'allowed-files', 'attr']);
				break
			case 'number':
				content = getFields(['base', 'text', 'number', 'attr']);
				break
			case 'listing':
				content = getFields(['base', 'listing', 'attr']);
				break
			case 'hidden':
				content = getFields(['hidden', 'attr']);
				break
			default: // string based
				content = getFields(['base', 'text', 'attr']);
		}

		if (values != undefined){
			for(var i in values){
				switch (i){
					case 'required':
					case 'list_type':
						content = content.replace(i + ']" value="' + values[i] + '"', i + ']" value="' + values[i] + '" checked');
						break
					case 'source':
						content = content.replace('name="fields[][source]" rows="5">', 'name="fields[][source]" rows="5">' + values[i]);
						break
					default:
						content = content.replace('name="fields[][' + i + ']"', 'name="fields[][' + i + ']" value="' + values[i] + '"');
				}
			}
			heading = '<span class="heading-title">' + (values.label || '') + '</span> <span class="label label-default">' + type + '</span>';
		}

		var html =
			'<div class="widget-tools"><span class="glyphicon glyphicon-trash" data-dismiss="widget" data-toggle="tooltip" title="' + syd.t('remove') + '"></span></div>' +
			'<div class="panel-heading" data-toggle="collapse" data-parent="#form-holder" href="#collapse' + count + '">' + heading + '</div>' +
			'<div id="collapse' + count + '" class="panel-collapse collapse">' +
				'<div class="panel-body"><input type="hidden" name="fields[][type]" value="' + type + '">' + content + '</div>' +
			'</div>';
		count++;
		return html.replace(/\[\]/g, '[' + count + ']');
	};


$(document).ready(function(){
	$('#form-holder').sortable({
		placeholder: 'panel ui-state-highlight',
		forcePlaceholderSize: true,
		cancel: '.onempty',
		handle: '.panel-heading'
	}).disableSelection();

	var last_placeholder_index = 0;
	$('#form-fields').sortable({
		connectWith: '#form-holder',
		activate: function(event, ui){
			$('#form-holder').addClass('ready')
		},
		beforeStop: function(event, ui){
			last_placeholder_index = $('#form-holder .ui-sortable-placeholder').index()
		},
		remove: function(event, ui){
			var type = ui.item.data('type'), el = $('<div>').addClass('widget panel panel-default fields-' + type).html(field(type));
			$('#form-holder > div').eq(last_placeholder_index - 2).after(el);
			$('#form-holder').removeClass('ready');
			$('[data-toggle="tooltip"]').tooltip();
			return false;
		}
	}).disableSelection();

	$('#form-fields a').click(function(){
		var type = $(this).data('type'), el = $('<div>').addClass('widget panel panel-default fields-' + type).html(field(type));
		$('#form-holder').append(el).removeClass('ready');
		$('[data-toggle="tooltip"]').tooltip();
	})

	$('#form-holder').on('keyup', '.input-label', function(){
		$(this).parents('.panel').find('.heading-title').text($(this).val())
	})

	$('.insert-name').click(function(){
		$(this).parents('.input-group-btn').prev().val(syd.t('form_sended') + ' "' + $('[name="settings[name]"]').val() + '"');
	})
	$('.find-emails').click(function(){
		var mails = {}, source = $(this).data('source').split(',');
		if (source != ''){
			for (var i in source){
				mails[source[i]] = source[i];
			}
		}
		$('.fields-email').each(function(){
			mails['#' + $(this).find('.input-key').val() + '#'] = syd.t('from_field') + ' ' + $(this).find('.input-label').val();
		})
		var dropdown = $(this).next();
		dropdown.html('');
		if (Object.keys(mails).length){
			for (i in mails){
				dropdown.append('<li><a href="#" class="insert-email" data-mail="' + i + '">' + mails[i] + '</a></li>');
			}
		} else {
			dropdown.append('<li><a href="#">none</a></li>');
		}
	})
	$(document).on('click', '.insert-email', function(){
		$(this).parents('.input-group-btn').prev().val($(this).data('mail'));
	})
	
	$('.generate-message').click(function(){
		var message = '';
		$('#form-holder .panel').each(function(){
			var label = $(this).find('.input-label').val(), key = $(this).find('.input-key').val();
			message += '<b>' + label + ':</b> #' + key + "#\n";
		})
		$(this).prev().val(message);
	})
	$('[name="main-form"]').submit(function(){
		$('.hidden').remove();
	})
	
	for (var i in fields){
		var type = fields[i]['type'], el = $('<div>').addClass('widget panel panel-default fields-' + type).html(field(type, fields[i]));
		$('#form-holder').append(el).removeClass('ready');
		$('[data-toggle="tooltip"]').tooltip();
	}
	
	$('[name="settings[template]"][value="custom"]').parent().attr('title', syd.t('tip_custom_template')).tooltip();
})

function getFields(fields){
	var html = '';
	for (var i in fields){
		html += $('#field-' + fields[i]).html();
	}
	return html;
}