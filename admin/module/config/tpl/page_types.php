<div class="panel-group" id="accordion">
<?php 
$i = 0;
foreach($types as $type => $data){
	$data['meta'] = implode(' ', $data['meta']);?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#type-<?php echo $type;?>"><?php echo $data['title'], ' :: ', $type, ' :: ', $data['structure'];?></a>
			</h4>
		</div>
		<div id="type-<?php echo $type;?>" class="panel-collapse collapse <?php if (++$i == 1){echo 'in';}?>">
			<div class="panel-body">
<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			<label><?php echo lang('title_in_menu');?></label>
			<input type="text" name="types[<?php echo $type;?>][title]" class="form-control" placeholder="<?php echo lang('news');?>" value="<?php if ($type != 'new'){echo $data['title'];}?>">
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label><?php echo lang('default_layout');?></label>
<?php echo getSelect($layouts, $data['layout'], $props = 'name="types[' . $type . '][layout]" class="form-control"');?>
		</div>
	</div>
<?php if ($type == 'new'){?>
	<div class="col-md-6">
		<div class="form-group">
			<label><?php echo lang('key_in_db');?></label>
			<input type="text" name="types[<?php echo $type;?>][type]" class="form-control" placeholder="news" value="">
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label><?php echo lang('structure');?></label>
<?php echo getSelect(array('tree' => lang('tree'), 'list' => lang('list')), $data['structure'], $props = 'name="types[' . $type . '][structure]" class="form-control"');?>
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label><?php echo lang('root');?></label>
<?php echo getSelect($roots, $data['root'], $props = 'name="types[' . $type . '][root]" class="form-control"');?>
		</div>
	</div>
<?php }?>
	<div class="col-md-12">
		<div class="form-group">
			<label><?php echo lang('metadata_in_view');?></label>
			<input type="text" name="types[<?php echo $type;?>][meta]" class="form-control" placeholder="date price" value="<?php echo $data['meta'];?>" data-toggle="tooltip" title="<?php echo lang('tip_space_separated');?>">
		</div>
	</div>
</div>
			</div>
		</div>
	</div>
<?php }?>
</div>