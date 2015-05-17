$(document).ready(function(){
	$('#title').autocomplete({
		source: '?route=pages/find',
		minLength: 2,
		select: function(event, ui){
			$('#url').val(ui.item.url);
		}
	})

	$('.item-add').click(function(){
		if ($('#title').val() == ''){
			return
		}
		var el = $('#item-source').clone().removeAttr('id'), id = Math.floor(Math.random() * 9001) + 1000;
		el.find('.ins-url').val($('#url').val());
		el.find('.ins-attr-title').val($('#attr_title').val());
		el.find('.ins-title').val($('#title').val());
		el.find('.item-title').text($('#title').val()).attr('href', '#mi-'+id);
		el.find('.collapse').attr('id', 'mi-'+id);
		$('#menu-holder').append($('<li>').attr('id', 'item-'+id).append(el));
		$('.new input').val('');
	})

	$('#menu-holder').nestedSortable({
		handle: '.panel-heading',
		items: 'li',
		toleranceElement: '> div',
		listType: 'ul',
		rootID: 0,
		placeholder: 'panel ui-state-highlight',
		forcePlaceholderSize: true,
	});
})

$(document).on('click', '.remove-item', function(){
	$(this).parents('li').eq(0).remove()
})

$(document).on('submit', 'form', function(event){
	if ($('.menu-item').length < 3){
		syd.alert(syd.t('add_one_item'), 'warning');
		return false;
	}
	if (!$(this).find('[name="item[level][]"]').length){
		$('.menu-item').not('#item-source, .new').each(function(){
			$('<input>').attr('type','hidden').attr('name','item[level][]').val($(this).parents('li').length).appendTo(this);
		})
	}
});