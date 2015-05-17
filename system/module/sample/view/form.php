<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title"><?=t('settings');?></h4>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label>Option 1</label>
					<input type="text" name="opt1" class="form-control" value="<?=$opt1?>">
				</div>
				<div class="form-group">
					<label>Option 2</label>
					<textarea name="opt2" class="form-control" rows="5"><?=$opt2?></textarea>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					<label>Option 3</label>
					<?=H::yesNo('opt3', $opt3);?>
				</div>
			</div>
		</div>
	</div>
</div>