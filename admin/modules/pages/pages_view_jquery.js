$('#checkall').click( function() {
				if($(this).attr('checked')){
					$('.ids').attr('checked', true)
				} else {
					$('.ids').attr('checked', false)
				}
			})
			$('#fullpath').click( function(){
				$('.table').toggleClass('hideLinks')
			})
			$('#full-tree').click(function(){
				if ($(this).attr('checked')){
					setCookie('fullTree', 1, 7)
				} else {
					setCookie('fullTree', null, -1)
				}
				window.location.reload()
			})