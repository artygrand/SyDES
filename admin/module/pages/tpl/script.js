$(document).ready(function(){
	$('.btn-group .btn-group').slice(-2).addClass('dropup')
	$('.status').change(function(){$.ajax({type:'GET',url:'?mod=pages&act=setstatus&id='+$(this).data('id')+'&val='+$(this).val()+'&ajax='+token})})
	$(document).on('click', '.hidechilds', function(){
		var parent = $(this).parents('tr').data('path')
		$('tr[data-path^="'+parent+'/"]').hide()
		$(this).toggleClass('hidechilds showchilds glyphicon-minus glyphicon-plus')
	})
	$(document).on('click', '.showchilds', function(){
		var parent = $(this).parents('tr').data('path')
		$('tr[data-path^="'+parent+'/"]').each(function(){
			if($(this).find('span').hasClass('showchilds')){
				$(this).find('.showchilds').toggleClass('hidechilds showchilds glyphicon-minus glyphicon-plus')
			}
			$(this).show()
		})
		$(this).toggleClass('hidechilds showchilds glyphicon-minus glyphicon-plus')
	})

})