$(document).ready(function(){
	// spoiler
	$('.spoiler').attr('title', syd.t('click_to_open'))
	$('.spoiler-content').hide()
	$('.spoiler-title').click(function(){
		$(this).next().slideToggle()
		$(this).find('.spoiler-toggle').toggleClass('show-icon hide-icon')
	})
	
	// fix redirect to home when # clicked
	var pathname = window.location.href.split('#')[0];
	$('a[href^="#"]').each(function(){
		var $this = $(this),
		link = $this.attr('href');
		$this.attr('href', pathname + link);
	})
	
	$('.modal').on('show.bs.modal', function(e){
		var size = $(e.relatedTarget).data('size'), dialog = $(this).find('.modal-dialog')
		dialog.removeClass('modal-sm modal-lg')
		if (size){
			dialog.addClass('modal-' + size)
		}
		setTimeout(modalPosition, 10)
	})
	$('.modal').on('loaded.bs.modal', function(e){modalPosition()})
	$('.modal').on('hidden.bs.modal', function (e){$(this).removeData('bs.modal')})
})

// bootstrap modals to center
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

		$(this).find('.modal-dialog').addClass('modal-dialog-center').css({
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