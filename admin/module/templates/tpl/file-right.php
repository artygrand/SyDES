<div class="form-group">
	<div class="btn-group btn-block">
		<a href="#" class="btn btn-primary submit" id="btn-save" data-act="apply"><?php echo lang('save');?></a>
		<div class="btn-group">
			<button type="button" class="btn btn-primary dropdown-toggle " data-toggle="dropdown">
			<span class="caret"></span>
			</button>
			<ul class="dropdown-menu pull-right">
				<li><a href="#" class="submit" data-act="save"><?php echo lang('save_and_back');?></a></li>
				<li><a href="#" class="ajaxmodal" data-url="?mod=templates&act=modal_saveas&file=<?php echo $thisFile;?>"><?php echo lang('save_as');?></a></li>
			</ul>
		</div>
	</div>
</div>
<div class="form-group">
	<label class="control-label"><?php echo lang('mastercode');?></label>
	<input type="text" class="form-control" name="mastercode">
</div>
<div class="form-group">
	<label class="control-label"><?php echo lang('other_files');?></label>
	<?php echo $otherFiles;?>
</div>

