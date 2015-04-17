<div class="row">
<div class="col-sm-6">
	<div class="panel panel-new">
		<div class="panel-heading">
			<h4 class="panel-title"><?=t('new_type');?></h4>
		</div>
		<div class="panel-body text-center">
			<br><br>
			<a href="?route=config/pagetypes/edit"><span class="glyphicon glyphicon-plus"></span> <?=t('create_new_type');?></a>
			<br><br><br>
		</div>
	</div>
</div>
<? foreach($types as $key => $type){
if (isset($type['hidden'])) continue; ?>
<div class="col-sm-6">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title"><?=$type['title'];?></h4>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-sm-3">
					<?=t('layout');?>:
				</div>
				<div class="col-sm-9">
					<?=$layouts[$type['layout']]['name'];?>
				</div>
				<div class="clearfix"></div>
				<div class="col-sm-3">
					<?=t('structure');?>:
				</div>
				<div class="col-sm-9">
					<?=t($type['structure']);?>
				</div>
				<div class="clearfix"></div>
				<div class="col-sm-3">
					<?=t('key');?>:
				</div>
				<div class="col-sm-9">
					<?=$key;?>
				</div>
			</div>
		</div>
		<div class="panel-footer">
			<? if ($key != 'page'){ ?>
			<a href="?route=config/pagetypes/delete&type=<?=$key;?>" class="btn btn-xs btn-danger pull-right"><?=t('delete');?></a>
			<? } ?>
			<a href="?route=config/pagetypes/edit&type=<?=$key;?>" class="btn btn-xs btn-default"><?=t('edit');?></a>
		</div>
	</div>
</div>
<? } ?>
</div>