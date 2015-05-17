<input type="hidden" name="type" value="<?=$type;?>">
<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title"><?php if ($type != 'new'){echo t('editing'), ' ', $title; } else { echo t('adding'); } ?></h4>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="form-group col-sm-6">
				<label><?=t('title');?></label>
				<input type="text" name="title" class="form-control" value="<?=$title;?>" placeholder="<?=t('news');?>" required>
			</div>
			<div class="form-group col-sm-6">
				<label><?=t('default_layout');?></label>
				<?=$layout;?>
			</div>
<?php if ($type == 'new'){ ?>
			<div class="form-group col-sm-6">
				<label><?=t('key');?></label>
				<input type="text" name="key" class="form-control" value="" placeholder="news" required>
			</div>
			<div class="form-group col-sm-6">
				<label><?=t('root');?></label>
				<?=$root;?>
			</div>
			<div class="form-group col-sm-6 col-sm-offset-6">
				<label><?=t('structure');?></label>
				<?=$structure;?>
			</div>
<?php } ?>
		</div>
	</div>
</div>