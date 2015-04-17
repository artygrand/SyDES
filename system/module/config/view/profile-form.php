<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title"><?=t('module_profile');?></h4>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label><?=t('new_username');?></label>
					<input type="text" name="newusername" class="form-control">
				</div>
				<div class="form-group">
					<label><?=t('enable_autologin');?></label>
					<?=H::yesNo('autologin', $autologin);?>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label><?=t('new_password');?></label>
					<input type="text" name="newpassword" class="form-control">
				</div>
				<div class="form-group">
					<label><?=t('new_mastercode');?></label>
					<input type="text" name="newmastercode" class="form-control">
				</div>
			</div>
		</div>
	</div>
</div>