<div class="row">
	<div class="col-md-6 col-md-offset-6">
		<div class="form-group">
			<label><?php echo lang('site_template');?></label>
			<?php echo $templates;?>
		</div>
	</div>
	<div class="col-md-6">
		<div class="checkbox">
			<label>
				<input name="maintenance_mode" type="checkbox" value="1"<?php echo $maintenance_check;?>>
				<?php echo lang('maintenance_mode');?>
			</label>
		</div>
	</div>
	<div class="col-md-6">
		<div class="checkbox">
			<label>
				<input name="need_cache" type="checkbox" value="1"<?php echo $cache_check;?>>
				<?php echo lang('use_caching');?>
				<span class="help-block"><?php echo lang('tip_caching');?></span>
			</label>
		</div>
	</div>
	<div class="col-md-12">
		<div class="form-group">
			<label><?php echo lang('maintenance_text');?></label>
			<textarea class="form-control" rows="17" name="say"><?php echo $say;?></textarea>
		</div>
	</div>
</div>