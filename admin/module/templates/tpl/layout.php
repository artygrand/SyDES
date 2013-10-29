<div class="row">
	<div class="col-sm-6 col-lg-4">
		<div class="form-group">
			<label class="control-label"><?php echo lang('name');?></label>
			<input type="text" class="form-control" name="name" value="<?php echo $name;?>">
		</div>
	</div>
	<div class="col-sm-0 col-lg-4">
	</div>
	<div class="col-sm-6 col-lg-4">
		<div class="form-group">
			<label class="control-label"><?php echo lang('file');?></label>
			<?php echo $files;?>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-6 col-md-4">
		<div class="form-group"><textarea name="left" class="form-control" rows="22" placeholder="<?php echo lang('left_column');?>"><?php echo $left;?></textarea></div>
	</div>
	<div class="col-sm-6 col-md-4 col-md-push-4">
		<div class="form-group"><textarea name="right" class="form-control" rows="22" placeholder="<?php echo lang('right_column');?>"><?php echo $right;?></textarea></div>
	</div>
	<div class="col-md-4 col-md-pull-4">
		<div class="form-group"><textarea name="top" class="form-control" rows="9" placeholder="<?php echo lang('before_content');?>"><?php echo $top;?></textarea></div>
		<div class="form-group"><pre class="text-center"><?php echo lang('content');?></pre></div>
		<div class="form-group"><textarea name="bottom" class="form-control" rows="9" placeholder="<?php echo lang('after_content');?>"><?php echo $bottom;?></textarea></div>
	</div>
</div>