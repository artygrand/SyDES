<div>
	<ul class="nav nav-tabs">
		<li class="active"><a href="#tab-fields" data-toggle="tab"><?=t('fields');?></a></li>
		<li class=""><a href="#tab-settings" data-toggle="tab"><?=t('settings');?></a></li>
		<li class=""><a href="#tab-notice" data-toggle="tab"><?=t('notices');?></a></li>
		<li class=""><a href="?route=constructors/form/results&id=<?=$form['id'];?>"><?=t('results');?></a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="tab-fields">
			<div class="row">
				<div class="col-sm-8">
					<div class="panel-group ready" id="form-holder">
						<div class="onempty"><?=t('tip_fields');?></div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="list-group" id="form-fields">
						<a href="#" class="list-group-item" data-type="string"><?=t('input_text');?></a>
						<a href="#" class="list-group-item" data-type="textarea"><?=t('input_textarea');?></a>
						<a href="#" class="list-group-item" data-type="email"><?=t('input_email');?></a>
						<a href="#" class="list-group-item" data-type="tel"><?=t('input_phone');?></a>
						<a href="#" class="list-group-item" data-type="file"><?=t('input_file');?></a>
						<a href="#" class="list-group-item" data-type="number"><?=t('input_number');?></a>
						<a href="#" class="list-group-item" data-type="listing"><?=t('input_listing');?></a>
						<a href="#" class="list-group-item" data-type="date"><?=t('input_date');?></a>
						<a href="#" class="list-group-item" data-type="hidden"><?=t('input_hidden');?></a>
					</div>
				</div>
			</div>
		</div>


		<div class="tab-pane form-horizontal" id="tab-settings">
			<input type="hidden" name="settings[id]" value="<?=$form['id'];?>">
			<div class="form-group">
				<label class="col-sm-2 control-label"><?=t('form_name');?></label>
				<div class="col-sm-10">
					<input type="text" name="settings[name]" class="form-control" value="<?=$form['name'];?>" required>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"><?=t('description');?></label>
				<div class="col-sm-10">
					<textarea name="settings[description]" class="form-control" rows="3"><?=$form['description'];?></textarea>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"><?=t('success_text');?></label>
				<div class="col-sm-10">
					<textarea name="settings[success_text]" class="form-control" rows="3"><?=$form['success_text'];?></textarea>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"><?=t('submit_button_text');?></label>
				<div class="col-sm-10">
					<input type="text" name="settings[submit_button]" class="form-control" value="<?=$form['submit_button'];?>">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"><?=t('template');?></label>
				<div class="col-sm-10">
					<?=H::radio('settings[template]', $form['template'], $templates, array('inline' => true));?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"><?=t('form_attributes');?></label>
				<div class="col-sm-10">
					<input type="text" name="settings[form_attr]" class="form-control" value="<?=$form['form_attr'];?>">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"><?=t('work');?></label>
				<div class="col-sm-10">
					<?=H::yesNo('settings[status]', $form['status']);?>
				</div>
			</div>
		</div>


		<div class="tab-pane form-horizontal" id="tab-notice">

<div class="panel-group" id="notices">
<?php foreach ($notices as $notice){
	$panel_heading = $notice['id'] > 0 ? $notice['to'] . ' :: ' . $notice['subject'] : '<span class="glyphicon glyphicon-plus-sign"></span> ' . t('add');
	?>

	<div class="widget panel panel-default">
		<?php if ($notice['id'] > 0){ ?><div class="widget-tools">
			<span class="glyphicon glyphicon-trash" data-id="<?=$notice['id'];?>" data-dismiss="widget" data-toggle="tooltip" title="<?=t('remove');?>"></span>
		</div><?php } ?>
		<div class="panel-heading" data-toggle="collapse" data-parent="#notices" href="#notice<?=$notice['id'];?>"><?=$panel_heading;?></div>
		<div id="notice<?=$notice['id'];?>" class="panel-collapse collapse <?=$notice['id'] > 0 ? '' : 'in';?>">
			<div class="panel-body">

				<div class="form-group">
					<label class="col-sm-2 control-label"><?=t('to');?></label>
					<div class="col-sm-10">
						<div class="input-group">
							<input type="text" name="notice[<?=$notice['id'];?>][to]" class="form-control" value="<?=$notice['to'];?>">
							<div class="input-group-btn">
								<button type="button" class="btn btn-default dropdown-toggle find-emails" data-toggle="dropdown" data-source="<?=$mails;?>"><?=t('set');?> <span class="caret"></span></button>
								<ul class="dropdown-menu dropdown-menu-right">
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label"><?=t('from');?></label>
					<div class="col-sm-10">
						<div class="input-group">
							<input type="text" name="notice[<?=$notice['id'];?>][from]" class="form-control" value="<?=$notice['from'];?>">
							<div class="input-group-btn">
								<button type="button" class="btn btn-default dropdown-toggle find-emails" data-toggle="dropdown" data-source="<?=$mails;?>"><?=t('set');?> <span class="caret"></span></button>
								<ul class="dropdown-menu dropdown-menu-right">
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label"><?=t('subject');?></label>
					<div class="col-sm-10">
						<div class="input-group">
							<input type="text" name="notice[<?=$notice['id'];?>][subject]" class="form-control" value="<?=$notice['subject'];?>">
							<div class="input-group-btn">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?=t('set');?> <span class="caret"></span></button>
								<ul class="dropdown-menu dropdown-menu-right">
									<li><a href="#" class="insert-name"><?=t('from_form_name');?></a></li>
								</ul>
							</div>
						</div>
					</div>
					
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label"><?=t('message');?></label>
					<div class="col-sm-10">
						<textarea name="notice[<?=$notice['id'];?>][body]" class="form-control" rows="15"><?=$notice['body'];?></textarea>
						<button type="button" class="btn btn-default btn-xs generate-message"><?=t('generate');?></button>
					</div>
				</div>

			</div>
		</div>
	</div>

<?php } ?>
</div>

		</div>
	</div>
</div>

<script>
var fields = <?=empty($form['fields']) ? '{}' : $form['fields'];?>;
</script>

<style>
.tab-pane{padding-top:15px;}
.onempty{color:#666;text-align:center;padding-top:150px;}
.onempty:not(:only-of-type){display:none;}
#form-holder{min-height:375px;padding-bottom:50px;border:3px dashed transparent;}
#form-holder.ready{border-color:#ddd;}
#notices .panel-heading{cursor:pointer;}
</style>


<div id="field-base" class="hidden">
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label><?=t('label');?></label>
				<div class="input-group">
					<input type="text" class="form-control input-label" name="fields[][label]">
					<span class="input-group-addon">
						<input type="checkbox" name="fields[][hide_label]" value="1" data-toggle="tooltip" data-placement="left" title="<?=t('hide_label');?>">
					</span>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label><?=t('key');?></label>
				<input type="text" class="form-control input-key" name="fields[][key]">
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label><?=t('description');?></label>
				<input type="text" class="form-control" name="fields[][description]">
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label>&nbsp;</label>
				<div class="checkbox"><label class="checkbox-inline"><input type="checkbox" name="fields[][required]" value="1"> <?=t('required');?></label></div>
			</div>
		</div>
	</div>
</div>

<div id="field-attr" class="hidden">
	<div class="form-group">
		<label><?=t('attributes');?></label>
		<input type="text" class="form-control" name="fields[][attr]">
		<p class="help-block"><?=t('attributes_help');?></p>
	</div>
</div>

<div id="field-text" class="hidden">
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label><?=t('placeholder');?></label>
				<input type="text" class="form-control" name="fields[][placeholder]">
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label><?=t('defaults');?></label>
				<input type="text" class="form-control" name="fields[][defaults]">
			</div>
		</div>
	</div>
</div>

<div id="field-number" class="hidden">
	<div class="form-group">
		<label><?=t('values');?></label>
		<div class="row">
			<div class="col-sm-4">
				<input type="text" class="form-control" name="fields[][min]" placeholder="<?=t('min');?>">
			</div>
			<div class="col-sm-4">
				<input type="text" class="form-control" name="fields[][step]" placeholder="<?=t('step');?>">
			</div>
			<div class="col-sm-4">
				<input type="text" class="form-control" name="fields[][max]" placeholder="<?=t('max');?>">
			</div>
		</div>
	</div>
</div>

<div id="field-text-rows" class="hidden">
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label><?=t('rows_count');?></label>
				<input type="text" class="form-control" name="fields[][rows]">
			</div>
		</div>
	</div>
</div>

<div id="field-listing" class="hidden">
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label><?=t('source');?></label>
				<textarea class="form-control" name="fields[][source]" rows="5"></textarea>
				<p class="help-block"><?=t('source_help');?></p>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label><?=t('defaults');?></label>
				<input type="text" class="form-control" name="fields[][defaults]">
			</div>
			<div class="form-group">
				<div class="radio"><label><input type="radio" name="fields[][list_type]" value="select"> <?=t('list_select');?></label></div>
				<div class="radio"><label><input type="radio" name="fields[][list_type]" value="checkbox"> <?=t('list_checkbox');?></label></div>
				<div class="radio"><label><input type="radio" name="fields[][list_type]" value="radio"> <?=t('list_radio');?></label></div>
			</div>
		</div>
	</div>
</div>

<div id="field-allowed-files" class="hidden">
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label><?=t('file_extensions');?></label>
				<input type="text" class="form-control" name="fields[][allowed_files]">
				<p class="help-block"><?=t('file_extensions_help');?></p>
			</div>
		</div>
	</div>
</div>

<div id="field-hidden" class="hidden">
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label><?=t('key');?></label>
				<input type="text" class="form-control input-key" name="fields[][key]">
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label><?=t('defaults');?></label>
				<input type="text" class="form-control" name="fields[][defaults]">
			</div>
		</div>
	</div>
</div>