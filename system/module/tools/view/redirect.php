<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title"><?=t('redirect_list');?></h4>
	</div>
	<table class="table table-condensed va-middle dtable">
	<thead>
		<tr>
			<th><?=t('from');?></th>
			<th><?=t('to');?></th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($result as $i => $item){ ?>
		<tr>
			<td><input type="text" class="form-control input-sm" name="redir[<?=$i;?>][from]" value="<?=$item['from'];?>"></td>
			<td><input type="text" class="form-control input-sm" name="redir[<?=$i;?>][to]" value="<?=$item['to'];?>"></td>
		</tr>
<?php } ?>
		<tr>
			<td><input type="text" class="form-control input-sm" name="redir[new_key][from][]"></td>
			<td><input type="text" class="form-control input-sm" name="redir[new_key][to][]"></td>
		</tr>
	</tbody>
	</table>
</div>
<p class="help-block"><?=t('help_text');?></p>
<script>
$(document).ready(function(){
	$('.dtable').dtable({'append':'<?=t('tip_add_more');?>', 'remove': '<?=t('delete');?>'})
})
</script>