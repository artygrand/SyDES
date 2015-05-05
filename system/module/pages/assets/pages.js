$(document).ready(function(){
	$('.type').each(function(){if($(this).parents('.pages-row').next('ul').length){$(this).addClass('open')}})
	if (localStorage['closed'] == undefined){localStorage['closed'] = '[]'}
	var closed = JSON.parse(localStorage['closed'])
	for(ul in closed){
		$('#'+closed[ul]+'>ul').hide()
		$('#'+closed[ul]+' .open').toggleClass('closed open')
	}
	$('.btn-group').slice(10).slice(-2).addClass('dropup')
	$('#page-1 .dropdown-toggle').remove();
	$('#page-1 .col-xs-9').toggleClass('col-xs-9 btn-block');
	$('select[name="status"]').change(function(){
		$.ajax({
			url:'?route=pages/setstatus&value='+$(this).val()+'&reload='+$(this).data('reload'),
			data:{id:$(this).data('id')}
		})
	})
	$('#batch').hide().change(function(){
		var ids=[];
		$.each($('input:checked.ids'),function(){ids.push($(this).val())});
		$.ajax({
			url:'?route=pages/'+$(this).val(),
			data:{id:ids.join()}
		})
	})
	$(document).on('click', 'span.open', function(){
		$(this).toggleClass('closed open').parents('.pages-row').next().hide()
		var closed = JSON.parse(localStorage['closed'])
		closed.push($(this).parents('li').attr('id'))
		localStorage['closed'] = JSON.stringify(closed)
	})
	$(document).on('click', 'span.closed', function(){
		$(this).toggleClass('closed open').parents('.pages-row').next().show()
		var closed = JSON.parse(localStorage['closed'])
		closed.splice(closed.indexOf($(this).parents('li').attr('id')), 1)
		localStorage['closed'] = JSON.stringify(closed)
	})
	$('#filters select').each(function(){
		$(this).prepend('<option value=""> </option>');
		if (!$(this).children('option[selected]').size()){
			$(this).children('option').first().attr('selected','selected');
		}
	})
	$('#filter').submit(function(){
		var action = $(this).attr('action'),
			data = decodeURIComponent($("#filters :input").serialize()),
			filter = data.replace(/[^&]+=\.?(?:&|$)/g, '').replace(/&$/, '');
		location.href = action + '&' + filter;
		return false;
	})

	$('.selectable').selectable({
		filter:'.selectitem',
		cancel:'.type,a,input,textarea,button,select,option',
		stop:function(){
			$('.ids').prop('checked', '');
			$('.ui-selected', this).each(function(){
				$(this).find('.ids').prop('checked', 'checked');
			});
			if($('input:checked.ids').size()){
				$('#batch').show()
				$('#sort-start').hide()
			} else {
				$('#batch').hide()
				$('#sort-start').show()
			}
		}
	})

	$('#sort-stop').hide()
	$('#sort-start').click(function(){
		$('#sort-stop').show();
		$(this).hide();
		$('.dropdown-menu').remove();
		$('#page-1').addClass('mjs-nestedSortable-no-nesting');
		$('#pages-tree').removeClass('idle').selectable('destroy').nestedSortable({
			handle: 'div',
			items: 'li:not(#page-1)',
			toleranceElement: '> div',
			listType: 'ul',
			rootID: root,
			placeholder: 'ui-state-highlight',
			forcePlaceholderSize: true,
		});
	})
	$('#sort-stop').click(function(){
		$(this).hide()
		$.ajax({url:'?route=pages/reorder',
			data:$('#pages-tree').nestedSortable('serialize'),
			complete:function(){
				localStorage['closed'] = '[]';
			}
		})
	})
	
	$('select[name="parent_id"]').change(function(){
		if ($('.btn-save.disabled').length){
			return false
		}
		$.ajax({
			url:'?route=pages/setparent&type='+$(this).data('type'),
			data:{
				id:$('#id').val(),
				parent_id:$(this).val()
			}
		})
	})
})