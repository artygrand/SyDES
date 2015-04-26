var count = 1,
	field = function(type){
		var heading = '<span class="heading-title"></span> #' + type,
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

		var html =
			'<div class="widget-tools"><span class="glyphicon glyphicon-trash field-remove"></span></div>' +
			'<div class="panel-heading" data-toggle="collapse" data-parent="#form-holder" href="#collapse' + count + '">' + heading + '</div>' +
			'<div id="collapse' + count + '" class="panel-collapse collapse">' +
				'<div class="panel-body">' + content + '</div>' +
			'</div>';
		count++;
		return html;
	};


$(document).ready(function(){
	$('#form-holder').sortable({
		placeholder: 'panel ui-state-highlight',
		forcePlaceholderSize: true,
	}).disableSelection();

	var last_placeholder_index = 0;
	$('.insert-field').sortable({
		connectWith: '#form-holder',
		beforeStop: function(event, ui){
			last_placeholder_index = $('#form-holder .ui-sortable-placeholder').index()
		},
		remove: function(event, ui){
			var el = $('<div>').addClass('panel panel-default').html(field(ui.item.data('type')));
			$('#form-holder > div').eq(last_placeholder_index - 2).after(el);
			return false;
		}
	}).disableSelection();

	$('.insert-field a').click(function(){
		var el = $('<div>').addClass('panel panel-default').html(field($(this).data('type')));
		$('#form-holder').append(el);
	})
	
	$(document).on('click', '.field-remove', function(){
		$(this).parents('.panel').remove()
	})

	$('#form-holder').on('keyup', 'input[name="fields[label][]"]', function(){
		$(this).parents('.panel').find('.heading-title').text($(this).val())
	})

})

function getFields(fields){
	var html = '';
	for (var i in fields){
		html += $('#field-' + fields[i]).html();
	}
	return html;
}


/*
https://github.com/twbs/bootstrap/issues/12354

https://www.google.ru/webhp?sourceid=chrome-instant&ion=1&espv=2&ie=UTF-8#q=bootstrap+modal+callback+on+apply&newwindow=1&safe=off&start=10

http://habibillah.kalicode.com/posts/2013/12/21/creating-simple-jquery-modal-container-plugins-based-on-bootrap-and-bootstrap-modal/
http://jsfiddle.net/yFLK6/13/
*/

