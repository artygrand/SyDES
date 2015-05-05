<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title"><?=t('settings');?></h4>
	</div>
	<table class="table table-condensed va-middle dtable">
	<thead>
		<tr>
			<th><?=t('key');?></th>
			<th><?=t('value');?></th>
		</tr>
	</thead>
	<tbody>
<?php foreach($data as $key => $value){ ?>
		<tr>
			<td>{config:<?=$key;?>}</td>
			<td><input type="text" class="form-control input-sm" name="config[<?=$key;?>]" value="<?=$value;?>"></td>
		</tr>
<?php } ?>
		<tr>
			<td><input type="text" class="form-control input-sm" name="config[new_key][key][]"></td>
			<td><input type="text" class="form-control input-sm" name="config[new_key][value][]"></td>
		</tr>
	</tbody>
	</table>
</div>
<script>
$(document).ready(function(){
	$('.dtable').dtable({'append':'<?=t('tip_add_more');?>', 'remove': '<?=t('delete');?>'})
})
</script>