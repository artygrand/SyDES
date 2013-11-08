<div class="row">
	<div class="col-sm-2"></div>
	<form class="col-sm-3 importform" action="?mod=import&act=get&table=" method="post">
		<h4><?php echo lang('import');?></h4>
		<div class="form-group">
			<?php echo $select;?>
		</div>
		<div class="form-group">
			<label class="radio-inline"><input type="radio" name="encoding" value="utf8" class="input-sm" checked> utf-8</label>
			<label class="radio-inline"><input type="radio" name="encoding" value="cp1251" class="input-sm"> cp1251</label>
		</div>
		<div class="form-group">
			<button class="btn btn-primary"><?php echo lang('download_csv');?></button>
		</div>
	</form>
	<div class="col-sm-2"></div>
	<form class="col-sm-3 importform" action="?mod=import&act=put&table=" enctype="multipart/form-data" method="post">
		<h4><?php echo lang('export');?></h4>
		<div class="form-group">
			<?php echo $select;?>
		</div>
		<div class="form-group">
			<input type="file" class="form-control" name="file">
		</div>
		<div class="form-group">
			<button class="btn btn-primary"><?php echo lang('upload_csv');?></button>
		</div>
	</form>
	<div class="col-sm-2"></div>
</div>