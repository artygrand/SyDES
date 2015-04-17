<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title"><?=t('settings');?></h4>
	</div>
	<table class="table table-condensed va-middle meta-table dtable">
		<thead>
		<tr>
			<th><?=t('key');?></th>
			<th><?=t('title');?></th>
			<th><?=t('field_type');?></th>
			<th style="width:50px;"> </th>
		</tr>
		</thead>
		<tbody>
<? foreach($meta as $key => $data){ ?>
		<tr>
			<td><?=$key;?></td>
			<td><input type="text" class="form-control input-sm" name="metatype[<?=$key;?>][title]" value="<?=$data['title'];?>"></td>
			<td><?=H::select("metatype[{$key}][type]", $data['type'], $types, 'class="form-control input-sm meta-type"')?></td>
			<td>
				<a href="#" class="btn btn-default btn-sm btn-block meta-setup" data-key="<?=$key;?>" title="<?=t('set_up');?>"><span class="glyphicon glyphicon-cog"></span></a>
				<input type="hidden" name="metatype[<?=$key;?>][config]" value="<?=$data['config'];?>">
			</td>
		</tr>
<? } ?>
		<tr>
			<td><input type="text" class="form-control input-sm" name="metatype[new_type][key][]" value=""></td>
			<td><input type="text" class="form-control input-sm" name="metatype[new_type][title][]" value=""></td>
			<td><?=H::select("metatype[new_type][type][]", 'string', $types, 'class="form-control input-sm meta-type"')?></td>
			<td>
				<a href="#" class="btn btn-default btn-sm btn-block meta-setup" title="<?=t('set_up');?>"><span class="glyphicon glyphicon-cog"></span></a>
				<input type="hidden" name="metatype[new_type][config][]" value="">
			</td>
		</tr>
		</tbody>
	</table>
</div>
<script>
$(document).ready(function(){
	$('.dtable').dtable({'append':'<?=t('tip_add_more');?>', 'remove': '<?=t('delete');?>'})
	$('.meta-type').eq(0).change();
})
$('.meta-table').on('change', '.meta-type', function(){
	$(this).parents('tr').find('input[type="hidden"]').val('')
	$('.meta-setup').hide()
	$('.meta-table tr').each(function(){
		var type = $(this).find('select').val(),
			has_config = ['select', 'checkbox', 'radio'];
		if (has_config.indexOf(type) > -1){
			$(this).find('.meta-setup').data('type', type).show()
		}
	})
}).on('click', '.meta-setup', function(){
	window.modalCallback = function(){}
	var type = $(this).data('type'),
		input = $(this).next(),
		value = input.val(),
		body = '';

	if (['select', 'checkbox', 'radio'].indexOf(type) > -1){
		body = '<div class="form-group"><label><?=t('items_list');?></label><textarea id="source" class="form-control" rows="10">'+value+'</textarea><p class="help-block"><?=t('meta_listing_help');?></p></div>';
		window.modalCallback = function(){
			input.val($('#source').val());
		}
	}

	$('#modal .modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><?=t('meta_field_settings');?></h4></div>'+
		'<div class="modal-body">' + body + '</div><div class="modal-footer"><input type="button" value="<?=t('apply');?>" class="btn btn-primary modal-return" data-dismiss="modal"></div>');
	$('#modal').modal('show');
}).on('dt.append.row', function(){
	$('.meta-type').eq(0).change();
})

$(document).on('click', '.modal-return', function(){
	syd.alert('<?=t('temporarily_stored');?>', 'warning');
	modalCallback();
	$('#modal .modal-content').html('');
})
</script>