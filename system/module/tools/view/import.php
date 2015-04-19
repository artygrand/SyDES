<div class="row">

	<form class="col-sm-4 importform" data-action="?route=tools/import/get&table=" method="post">
		<h4><?=t('import');?></h4>
		<div class="form-group">
			<?=$select;?>
		</div>
		<div class="form-group">
			<div class="radio">
				<label class="radio-inline"><input type="radio" name="encoding" value="utf8" checked> utf-8</label>
				<label class="radio-inline"><input type="radio" name="encoding" value="cp1251"> cp1251</label>
			</div>			
		</div>
		<div class="form-group">
			<button class="btn btn-primary"><?=t('download_csv');?></button>
		</div>
	</form>

	<div class="col-sm-4"></div>

	<form class="col-sm-4 importform" data-action="?route=tools/import/put&table=" enctype="multipart/form-data" method="post">
		<h4><?=t('export');?></h4>
		<div class="form-group">
			<?=$select;?>
		</div>
		<div class="form-group">
			<input type="file" class="form-control" name="file" required>
		</div>
		<div class="form-group">
			<button class="btn btn-primary"><?=t('upload_csv');?></button>
		</div>
	</form>

</div>

<script>
$(document).on('submit', '.importform', function(){
	$(this).prop('action', $(this).data('action') + $(this).find('select').val())
})
</script>