<div class="row">
<?php foreach ($columns as $column){ ?>
	<div class="col-sm-<?=$col_sm;?>">
	<?php if($column){ ?>
		<div class="widgets-wrapper">
		<?php foreach ($column as $widget){ 
			echo $widget;
		} ?>
		</div>
	<?php } else { ?>
		<div class="widgets-wrapper empty"></div>
	<?php } ?>
	</div>
<?php } ?>
</div>

<script>
	$(function(){
		$('.widgets-wrapper').sortable({
			connectWith: '.widgets-wrapper',
			items: '.widget',
			handle: '.panel-heading',
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