$(document).ready(function(){
	if(typeof Shadowbox !== "undefined") Shadowbox.init()
	if (typeof jQuery.fn.nivoSlider !== "undefined"){
		$('#slider').nivoSlider({
			effect: 'fold',
			pauseTime:5000,
			controlNav:false,
			beforeChange: function(){},
			afterChange: function(){}
		})
	}

	// fix menu when scrolling
	$(window).scroll(function(){
		var top = $(document).scrollTop()
		if (top > 150) $('#menu-num').addClass('menu-fixed')
		else $('#menu-num').removeClass('menu-fixed')
		//#menu-num replace with actual id of menu
	})
	
	// all form anti-bot script
	$('input').click(function(){
		var form = $(this).parents('form')
		if(form.find('input[name="hummel"]').length == 0){
			form.append('<input type="hidden" name="hummel" value="yes">');
		} 
	})
	
	// just spoiler .spoiler+div
	$('.spoiler').next().hide()
	$('.spoiler').click(function(){
		$(this).next().slideToggle()
	})
	
	// fix redirect to home when # clicked
	var pathname = window.location.href.split('#')[0];
	$('a[href^="#"]').each(function(){
		var $this = $(this),
		link = $this.attr('href');
		$this.attr('href', pathname + link);
	})
})