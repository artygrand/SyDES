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
			<td><input type="text" class="form-control input-sm" name="metatype[new_type][key][]"></td>
			<td><input type="text" class="form-control input-sm" name="metatype[new_type][title][]"></td>
			<td><?=H::select("metatype[new_type][type][]", 'string', $types, 'class="form-control input-sm meta-type"')?></td>
			<td>
				<a href="#" class="btn btn-default btn-sm btn-block meta-setup" title="<?=t('set_up');?>"><span class="glyphicon glyphicon-cog"></span></a>
				<input type="hidden" name="metatype[new_type][config][]">
			</td>
		</tr>
		</tbody>
	</table>
</div>

<div id="settings-listing" class="hidden">
	<div class="form-group">
		<label><?=t('display_as');?></label>
		<?=H::radio('display', '', array('select' => t('list_select'), 'checkbox' => t('list_checkbox'), 'radio' => t('list_radio')), array('inline' => true));?>
	</div>
	<div class="form-group">
		<label><?=t('items_list');?></label>
		<textarea name="source" class="form-control" rows="10"></textarea>
		<p class="help-block"><?=t('meta_listing_help');?></p>
	</div>
</div>



<div class="modal fade" id="settings-modal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"><?=t('meta_field_settings');?></h4>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer">
				<input type="button" value="<?=t('apply');?>" class="btn btn-primary modal-apply" data-dismiss="modal">
			</div>
		</div>
	</div>
</div>