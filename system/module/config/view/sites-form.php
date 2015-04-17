<input type="hidden" name="site" value="<?=$site;?>">
<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title"><?=$title;?></h4>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label><?=t('site_name');?></label>
					<input type="text" name="name" class="form-control" placeholder="<?=t('my_super_site');?>" value="<?=$name;?>" required>
				</div>
				<div class="form-group">
					<label><?=t('template');?></label>
					<?=$template;?>
				</div>
				<div class="form-group">
					<label><?=t('locales');?></label>
					<input type="text" name="locales" class="form-control" placeholder="en ru de it" data-toggle="tooltip" title="<?=t('tip_locales');?>" value="<?=$locales;?>" required>
				</div>
				<div class="form-group">
					<label><?=t('connected_domains');?></label>
					<textarea name="domains" class="form-control" rows="5" data-toggle="tooltip" title="<?=t('tip_one_per_line');?>" placeholder="site.com" required><?=$domains;?></textarea>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					<label><?=t('maintenance_mode');?></label>
					<?=H::yesNo('maintenance_mode', $maintenance_mode);?>
				</div>
				<div class="form-group">
					<label><?=t('need_cache');?></label>
					<?=H::yesNo('need_cache', $need_cache);?>
				</div>
				<div class="form-group">
					<label><?=t('use_alias_as_path');?></label>
					<?=H::yesNo('use_alias_as_path', $use_alias_as_path);?>
				</div>
			</div>
		</div>
	</div>
</div>
<? if (!$sites){ ?>
<script>
$('a').attr('href', '#')
</script>
<? } ?>