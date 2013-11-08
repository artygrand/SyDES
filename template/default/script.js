$(document).ready(function(){
	Shadowbox.init()
	if (typeof jQuery.fn.nivoSlider !== "undefined"){
		$('#slider').nivoSlider({pauseTime: 5000, effect: 'sliceUpDownLeft'})
	}
	if (document.getElementById("ymap")){
		ymaps.ready(initMap);
		$('.map-link').click(function(){
			var cord = $(this).attr('data-coords').split(',')
			cord[0] = 1*cord[0]
			cord[1] = 1*cord[1]
			myMap.panTo(cord, 
				{delay: 0, duration: 1000, flying:true,
				callback:function(){
					myMap.setZoom(16, {duration: 1000})
				}}
			)
			return false
		})

	}
	$(window).scroll(function(){ 
		var top = $(document).scrollTop();
		if (top > 120) $('#submenu ul ul').addClass('fixed'); 
		else $('#submenu ul ul').removeClass('fixed');
	});
})
var myMap;
function initMap(){
	myMap = new ymaps.Map("ymap", {
		center: [82.938234, 55.047317],
		zoom: 12
	}),
	offices = new ymaps.GeoObjectCollection({}, {
		iconImageHref: '/templates/deco/img/icon-map-flag_32x38.png',
		iconImageSize: [32, 38],
		iconImageOffset: [-14, -30],
		visible:true
	})
	offices.add(new ymaps.Placemark([82.938234, 55.047317],{hintContent: 'ул. Достоевского, д. 58'}))
	offices.add(new ymaps.Placemark([82.932503, 55.081498],{hintContent: 'ул. Советская, д. 8'}))
	offices.add(new ymaps.Placemark([82.915615, 55.026422],{hintContent: 'ул. Светлановская, д. 50'}))
	offices.add(new ymaps.Placemark([92.913657, 56.042054],{hintContent: 'ул. 78 Добровольческой бригады, д. 12'}))

	myMap.geoObjects.add(offices)
}