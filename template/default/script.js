$(document).ready(function(){
	// fancybox initialization
	if (typeof jQuery.fn.fancybox !== "undefined"){
		$(".fancybox").fancybox()
	};

	// back to top
	$(window).scroll(function(){
		if ($(this).scrollTop() > 200){
			$('.scrollup').fadeIn()
		} else {
			$('.scrollup').fadeOut()
		}
	});
	$('.scrollup').click(function(){
		$('html, body').animate({
			scrollTop: 0
		}, 600);
		return false;
	});
 
	// mobile menu
	$('.main-menu').parent().prepend('<a class="mobile-menu">' + syd.t('select_page') + ' <span class="glyphicon glyphicon-menu-hamburger pull-right"></span></a>');
	$('.mobile-menu').on('click', function(){
		$(this).parent().find('.main-menu').slideToggle()
	});

	// your scripts here
	
})