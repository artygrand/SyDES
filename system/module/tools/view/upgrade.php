<div class="row versions">
	<div class="col-xs-6">
	<?=t('your_version');?>
	<div class="ver"><?=VERSION;?></div>
	</div>
	<div class="col-xs-6">
	<?=t('latest_version');?>
	<div class="ver"><?=$latest;?></div>
	</div>
</div>

<?php if (version_compare(VERSION, $latest) < 0){ ?>
<div class="row">
	<div class="col-xs-4 col-xs-offset-4">
		<a href="?route=tools/upgrade/run" class="btn btn-lg btn-success btn-block"><?=t('upgrade');?></a>
	</div>
</div>
<?php } ?>

<style>
.versions{text-align:center;font-size:24px;}
.ver{font-size:36px;}
</style>
