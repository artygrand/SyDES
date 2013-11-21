$(document).ready(function(){
	if (localStorage['closed'] == undefined){
		localStorage['closed'] = '[]'
	}
	var closed = JSON.parse(localStorage['closed'])
	for(ul in closed) {
		$('#'+closed[ul]+'>ul').hide()
		$('#'+closed[ul]+' .catopen').toggleClass('catclosed catopen')
	}
	$('.btn-group .btn-group').slice(10).slice(-2).addClass('dropup')
	$('.status').change(function(){if($(this).data('id') > 0)$.ajax({type:'GET',url:'?mod=pages&act=setstatus&id='+$(this).data('id')+'&val='+$(this).val()+'&rel='+$(this).data('rel')+'&ajax='+token})})
	$('#actions').change(function(){$.ajax({type:'GET',url:'?mod=pages&act='+$(this).val()+'&id='+getChecked()+'&ajax='+token})})
	$(document).on('click', '.catopen', function(){
		$(this).toggleClass('catclosed catopen').parents('.pagerow').next().hide()
		var closed = JSON.parse(localStorage['closed'])
		closed.push($(this).parents('li').attr('id'))
		localStorage['closed'] = JSON.stringify(closed)
	})
	$(document).on('click', '.catclosed', function(){
		$(this).toggleClass('catclosed catopen').parents('.pagerow').next().show()
		var closed = JSON.parse(localStorage['closed'])
		closed.splice(closed.indexOf($(this).parents('li').attr('id')), 1)
		localStorage['closed'] = JSON.stringify(closed)
	})
	
	$('.sortable').sortable({
		distance: 20,
		items: '>li:not(#0)',
		cursor: 'n-resize',
		start: function(event, ui){
			ui.item.startPos = ui.item.index();
		},
		update: function(event, ui){
			var delta = ui.item.index() - ui.item.startPos, min = Math.min(ui.item.index(),ui.item.startPos), max = Math.max(ui.item.index(),ui.item.startPos)
			if (delta > 0){
				delta = ui.item.parent().find('>li').slice(min,max).find('.pagerow').length
			} else {
				delta = 0 - ui.item.parent().find('>li').slice(min+1,max+1).find('.pagerow').length
			}
			$.ajax({type:'GET',url:'?mod=pages&act=setposition&id='+ui.item.attr('id')+'&qty='+ui.item.find('.pagerow').length+'&delta='+delta+'&ajax='+token})
		}
	})
	
	$('.sortable').disableSelection()
	setPathWidth()
	$('input[name="alias"]').change(function(){
		if ($('#id').val() < '1' || $('.btn.disabled, .btn:disabled').length){return false}
		$.ajax({type:'POST',url:'?mod=pages&act=setalias&ajax='+token,data:{id:$('#id').val(),alias:$(this).val()}})
	})
	$('select[name="parent_id"]').change(function(){
		if ($('#id').val() < '1' || $('.btn.disabled, .btn:disabled').length){return false}
		$.ajax({type:'POST',url:'?mod=pages&act=setnewparent&ajax='+token+'&id='+$('#id').val()+'&rel='+$(this).data('rel'),
		data:{parent_id:$(this).val()},complete:function(){
			$('#path').text(window.respond.parent_path+'/');$('select[name="parent_id"]').blur();
			setTimeout(refreshPathWidth, 200);setTimeout(setPathWidth, 500);
		}})
	})
})
function refreshPathWidth(){$('#path').width('auto')}
function setPathWidth(){$('#path').width($('#path').width())}
function getChecked(){var ids=[];$.each($('input:checked.ids'),function(){ids.push($(this).val())});return ids.join()}