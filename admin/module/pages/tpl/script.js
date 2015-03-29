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

	$('.sortable>li:not(#0)').each(function(index){
		if($(this).find('.sortable').length == 0){
			$(this).append('<ul class="sortable ready-to-connect"></ul>')
		}
	})
	$('.ready-to-connect').css({visibility:'hidden', padding:'0'})

	$('.sortable').sortable({
		distance: 20,
		items: '>li:not(#0)',
		cursor: 'n-resize',
		connectWith: '.sortable',
		start: function(event, ui){
			ui.item.startPos = ui.item.index()
			ui.item.startParent = ui.item.parent().parent().attr('id')
			window.doubl = 0
		},
		update: function(event, ui){
			if (window.doubl == 0){
				window.doubl = 1
				var newlevel = ui.item.parent().prev().data('level') + 1
				ui.item.find('>div .title').removeClass().addClass('cell title l-' + newlevel)
				ui.item.parent().removeClass('ready-to-connect').prev().find('.page').removeClass('page').addClass('catopen')
				ui.item.stopParent = ui.item.parent().parent().attr('id')
				if (ui.item.startParent == ui.item.stopParent){
					$.ajax({type:'POST',url:'?mod=pages&act=moveto&ajax='+token,
						data:{
							id:ui.item.attr('id'),
							stoppos:ui.item.index()
						}
					})
				}
			}
		},
		receive: function(event, ui){
			if (ui.item.stopParent === undefined){
				ui.item.stopParent = 0
			}
			$.ajax({type:'POST',url:'?mod=pages&act=moveto&ajax='+token,
				data:{
					id:ui.item.attr('id'),
					parent:ui.item.stopParent,
					stoppos:ui.item.index()
				}
			})
		},
		stop: function(event, ui){
			$('.ready-to-connect').css({visibility:'hidden', padding:'0'})
		}
	})
	$('.content').on('sort', function(event, ui){showUlNear(event.pageY - $(this).offset().top)})
	$('.sortable').disableSelection()
	setPathWidth()
	$('input[name="alias"]').change(function(){
		if ($('#id').val() < '1' || $('.btn.disabled, .btn:disabled').length){return false}
		$.ajax({type:'POST',url:'?mod=pages&act=setalias&ajax='+token,data:{id:$('#id').val(),alias:$(this).val()}})
	})
	$('select[name="parent_id"]').change(function(){
		if ($('#id').val() < '1' || $('.btn.disabled, .btn:disabled').length){return false}
		$.ajax({type:'POST',url:'?mod=pages&act=moveto&ajax='+token,
			data:{
				id:$('#id').val(),
				parent:$(this).val(),
				norefresh:1
			},
			complete:function(){
				$('#path').text(window.respond.parent_path+'/');$('select[name="parent_id"]').blur();
				setTimeout(refreshPathWidth, 200);setTimeout(setPathWidth, 500);
			}
		})
	})
	$("#filters select").prepend("<option value='' selected='selected'></option>");
	$('#pagesfilter').submit(function(){
		var action=$(this).attr('action'), data = decodeURIComponent($("#pagesfilter :input").serialize()), filter = data.replace(/[^&]+=\.?(?:&|$)/g, '').replace(/&$/, '')
		location.href = action+'&'+filter
		return false;
	})
})

function showUlNear(coord){
	$('.ready-to-connect').css({visibility:'hidden', padding:'0'})
	$('.ready-to-connect').each(function(index){
		var pos = $(this).position().top
		if (pos > (coord-40) && pos < (coord+20)){
			$(this).css({visibility:'visible', padding:'10px 0'})
			return false
		}
	})
}
function refreshPathWidth(){$('#path').width('auto')}
function setPathWidth(){$('#path').width($('#path').width())}
function getChecked(){var ids=[];$.each($('input:checked.ids'),function(){ids.push($(this).val())});return ids.join()}