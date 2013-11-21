<script>
		$(document).on('change','#key',function(){doMagic($(this).val())})		
		function doMagic(tag){var tags=%arr%,tagclass='';for(t in tags){if(tags[t].indexOf(tag) >= 0){tagclass=t;break}}$('.date').datepicker('destroy');$('#value').removeClass().addClass('form-control '+tagclass);if(tagclass == 'date'){$('#value').datepicker({dateFormat:'dd.mm.yy'})}}
</script>