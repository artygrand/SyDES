<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			<label for="username"><?php echo lang('new_username');?></label>
			<input type="text" name="username" class="form-control" id="username" placeholder="<?php echo lang('new_username');?>">
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label for="password"><?php echo lang('new_password');?></label>
			<input type="password" name="password" class="form-control" id="password" placeholder="<?php echo lang('new_password');?>">
		</div>
	</div>
	<div class="col-md-6">
		<div class="checkbox">
			<label>
				<input name="autologin" type="checkbox"<?php echo $autologin_check;?>> <?php echo lang('enable_autologin');?>
			</label>
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label for="mastercode"><?php echo lang('new_mastercode');?></label>
			<input type="text" name="mastercode" class="form-control" id="newmastercode" placeholder="<?php echo lang('new_mastercode');?>">
		</div>
	</div>
	<div class="col-md-12">
		<div class="form-group">
			<label for="ip"><?php echo lang('admin_ip');?></label>
			<input type="text" name="admin_ip" class="form-control" id="ip" value="<?php echo $admin_ip_list;?>" placeholder="127.0.0.1 192.168.32.1" data-toggle="tooltip" title="<?php echo lang('tip_space_separated');?>">
			<span class="help-block"><?php echo lang('your_ip'), getip();?></span>
		</div>
	</div>
</div>
