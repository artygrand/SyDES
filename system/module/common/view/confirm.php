<div class="row">
	<div class="col-sm-4 col-sm-offset-4">
		<div class="well">
			<?=$message;?>
			<form action="" method="post" class="text-center form-confirm">
				<input type="hidden" name="confirm" value="1">
				<input type="submit" class="btn btn-danger btn-sm" value="<?=t('yes');?>"> &nbsp; &nbsp; <a href="<?=$return_url;?>" class="btn btn-default btn-sm"><?=t('no');?></a>
			</form>
		</div>
	</div>
</div>