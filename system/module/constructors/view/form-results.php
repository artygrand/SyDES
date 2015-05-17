<ul class="nav nav-tabs">
	<li><a href="?route=constructors/form/edit&id=<?=$results[0]['form_id'];?>#tab-fields"><?=t('fields');?></a></li>
	<li><a href="?route=constructors/form/edit&id=<?=$results[0]['form_id'];?>#tab-settings"><?=t('settings');?></a></li>
	<li><a href="?route=constructors/form/edit&id=<?=$results[0]['form_id'];?>#tab-notice"><?=t('notices');?></a></li>
	<li class="active"><a href="#"><?=t('results');?></a></li>
</ul>
<br>
<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title"><?=t('results');?></h4>
	</div>
	<table class="table table-hover table-condensed va-middle">
		<thead>
			<tr>
				<th>ID</th>
				<th> </th>
				<th><?=t('submitted');?></th>
				<th>IP</th>
				<th><?=t('status');?></th>
				<th style="width:150px;"><?=t('actions');?></th>
			</tr>
		</thead>
		<tbody>
	<?php foreach ($results as $result){
			$first_field = current($result['content']);
	?>
			<tr class="viewed-<?=$result['viewed'];?>">
				<td><?=$result['id'];?></td>
				<td><?=$first_field;?></td>
				<td><?=tDate($locale, 'D, d M, Y - H:i', $result['date']);?></td>
				<td><?=$result['ip'];?></td>
				<td><a class="label label-<?=$result['status'] ? 'success' : 'danger';?>" href="?route=constructors/form/toggleresult&id=<?=$result['id'];?>"><?=$result['status'];?></a></td>
				<td>
					<div class="btn-group pull-right btn-group-sm">
						<a class="btn btn-default" data-toggle="modal" data-target="#modal" href="?route=constructors/form/result&id=<?=$result['id'];?>"><?=t('view');?></a>
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
						<ul class="dropdown-menu dropdown-menu-right">
							<li><a class="danger" href="?route=constructors/form/delresult&id=<?=$result['id'];?>"><?=t('delete');?></a></li>
						</ul>
					</div>
				</td>
			</tr>
	<?php } ?>
		</tbody>
	</table>
</div>

<style>
	.viewed-0 td{background-color:#eee;}
</style>