var $ = jQuery.noConflict();
$(document).ready(function() {
		/* for top navigation */
		$(" .navigation ul ").css({display: "none"}); // Opera Fix
		$(" .navigation li").hover(function(){
		$(this).find('ul:first').css({visibility: "visible",display: "none"}).slideDown(400);
		},function(){
		$(this).find('ul:first').css({visibility: "hidden"});
		});
		
});		 
	
