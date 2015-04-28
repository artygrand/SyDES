<div class="row">
<? foreach($columns as $column){ ?>
	<div class="col-sm-<?=$col_sm;?>">
	<? if($column){ ?>
		<div class="widgets-wrapper">
		<? foreach($column as $widget){ 
			echo $widget;
		} ?>
		</div>
	<? } else { ?>
		<div class="widgets-wrapper empty"></div>
	<? } ?>
	</div>
<? } ?>
</div>

<script>
	$(function(){
		$('.widgets-wrapper').sortable({
			connectWith: '.widgets-wrapper',
			items: '.widget',
			handle: '.panel-title',
			cancel: '.widget-remove',
			placeholder: 'ui-state-highlight',
			forcePlaceholderSize: true,
			stop: function(e, ui){
				var column = []
				$('.widgets-wrapper').each(function(){
					var widgets = $(this).find('.widget'), index = $(this).parent().index()
					column[index] = []
					if (widgets.length){
						$(this).removeClass('empty')
						widgets.each(function(){
							column[index].push($(this).data('widget'))
						})
					} else {
						$(this).addClass('empty')
					}
				})
				$.ajax({url:'?route=dashboard/sort',
					data:{sort:column}
				})
			}
		}).disableSelection()
	});
</script>