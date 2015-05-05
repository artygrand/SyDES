<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title"><?=t('iblocks');?></h4>
	</div>
	<table class="table table-hover table-condensed va-middle">
		<tbody>
	<?php foreach($iblocks as $iblock){ ?>
			<tr>
				<td>{iblock:<?=$iblock;?>}</td>
				<td>
					<div class="btn-group pull-right btn-group-sm">
						<a class="btn btn-default" href="?route=iblocks/edit&iblock=<?=$iblock;?>"><?=t('edit');?></a>
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
						<ul class="dropdown-menu dropdown-menu-right">
							<li><a data-toggle="modal" data-target="#modal" data-size="sm" href="?route=iblocks/cloneit&iblock=<?=$iblock;?>"><?=t('clone');?></a></li>
							<li class="divider"></li>
							<li><a class="danger" href="?route=iblocks/delete&iblock=<?=$iblock;?>"><?=t('delete');?></a></li>
						</ul>
					</div>
				</td>
			</tr>
	<?php } ?>
		</tbody>
	</table>
	<div class="panel-footer">
		<div class="input-group">
			<input type="text" name="iblock" class="form-control" data-toggle="tooltip" title="<?=t('iblock_name')?>" placeholder="my_block">
			<span class="input-group-btn"><a class="btn btn-default goto" data-url="?route=iblocks/save&iblock="><?=t('add');?></a></span>
		</div>
	</div>
</div>

<script>
$(document).ready(function(){
	$('.goto').click(function(){
		var item = $(this).parent().prev().val().match(/[a-z\.-]+/)
		if (item){
			location.href = $(this).data('url') + item
		}
	})
})
</script>