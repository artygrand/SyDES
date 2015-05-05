<div class="row">
<div class="col-sm-6">
	<div class="panel panel-new">
		<div class="panel-heading">
			<h4 class="panel-title"><?=t('new_site');?></h4>
		</div>
		<div class="panel-body text-center">
			<br><br><br>
			<a href="?route=config/sites/add"><span class="glyphicon glyphicon-plus"></span> <?=t('create_new_site');?></a>
			<br><br><br><br>
		</div>
	</div>
</div>
<?php foreach ($sites as $key => $site){ ?>
<div class="col-sm-6">
	<div class="panel panel-default">
		<div class="panel-heading">
			<span class="pull-right"><a href="?site=<?=$key;?>" class="btn btn-xs btn-default"><?=t('view');?></a> <a href="?site=<?=$key;?>&route=config/sites/edit" class="btn btn-xs btn-default"><?=t('edit');?></a></span>
			<h4 class="panel-title"><?=$site['config']['name'];?></h4>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-sm-3">
					<?=t('domains');?>:
				</div>
				<div class="col-sm-9">
					<?=implode(', ', $site['domain']);?>
				</div>
				<div class="clearfix"></div>
				<div class="col-sm-3">
					<?=t('locales');?>:
				</div>
				<div class="col-sm-9">
					<?=implode(', ', $site['config']['locales']);?>
				</div>
				<div class="clearfix"></div>
				<div class="col-sm-3">
					<?=t('content');?>:
				</div>
				<div class="col-sm-9">
					<?php $types = array();
					foreach ($site['config']['page_types'] as $key => $type){
						if ($key == 'trash') continue;
						$types[] = $type['title'];
					}
					echo implode(', ', $types);
					?>
				</div>
				<div class="clearfix"></div>
				<div class="col-sm-3">
					<?=t('modules');?>:
				</div>
				<div class="col-sm-9">
					<?php $mods = array();
					foreach ($site['config']['modules'] as $mod => $data){
						$mods[] = $mod;
					}
					echo implode(', ', $mods);
					?>
				</div>
				<div class="clearfix"></div>
				<div class="col-sm-3">
					<?=t('template');?>:
				</div>
				<div class="col-sm-9">
					<?=$site['config']['template'];?>
				</div>
<?php if ($site['config']['maintenance_mode'] == 1){ ?>
				<div class="clearfix"></div>
				<div class="col-sm-3">
					<?=t('pass_users');?>:
				</div>
				<div class="col-sm-9">
					http://<?=$site['domain'][0];?>?let_me_in
				</div>
<?php } ?>
			</div>
		</div>
		<div class="panel-footer">
			<?php if ($site['config']['maintenance_mode'] == 0){ ?>
			<a href="?site=<?=$key;?>&route=config/sites/config&key=maintenance_mode&value=1" class="btn btn-xs btn-success hover-content">
				<span class="hover-info"><?=t('site_works');?></span>
				<span class="hover-link"><?=t('enable_maintenance_mode');?></span>
			</a>
			<?php } else { ?>
			<a href="?site=<?=$key;?>&route=config/sites/config&key=maintenance_mode&value=0" class="btn btn-xs btn-default hover-content">
				<span class="hover-info"><?=t('maintenance_mode');?></span>
				<span class="hover-link"><?=t('disable_maintenance_mode');?></span>
			</a>
			<?php } ?>

			<?php if ($site['config']['need_cache'] == 0){ ?>
			<a href="?site=<?=$key;?>&route=config/sites/config&key=need_cache&value=1" class="btn btn-xs btn-default hover-content pull-right">
				<span class="hover-info"><?=t('caching_disabled');?></span>
				<span class="hover-link"><?=t('enable_caching');?></span>
			</a>
			<?php } else { ?>
			<a href="?site=<?=$key;?>&route=config/sites/config&key=need_cache&value=0" class="btn btn-xs btn-success hover-content pull-right">
				<span class="hover-info"><?=t('caching_enabled');?></span>
				<span class="hover-link"><?=t('disable_caching');?></span>
			</a>
			<?php } ?>
		</div>
	</div>
</div>
<?php } ?>
</div>

