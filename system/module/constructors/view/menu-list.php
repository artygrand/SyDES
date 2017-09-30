<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title"><?=t('module_menu');?></h4>
	</div>
	<table class="table table-hover table-condensed va-middle">
		<thead>
			<tr>
				<th><?=t('menu_name');?></th>
				<th><?=t('token');?></th>
				<th style="width:150px;"><?=t('actions');?></th>
			</tr>
		</thead>
		<tbody>
	<?php
	$used = array();
	foreach ($menus as $id => $menu){
		$parts = explode(':', $id);
		$id = $parts[0];
		if (isset($used[$id])) {
			continue;
		}
		if ($parts[1] != $locale) {
			foreach($menus as $i => $t) {
				if ($i == $id.':'.$locale) {
					$menu['title'] = $t['title'];
					break;
				}
			}
		}
		$used[$id] = 1;
	?>
			<tr>
				<td><?=$menu['title'];?></td>
				<td>{iblock:menu?show=<?=$id;?>}</td>
				<td>
					<div class="btn-group pull-right btn-group-sm">
						<a class="btn btn-default" href="?route=constructors/menu/edit&id=<?=$id;?>"><?=t('edit');?></a>
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
						<ul class="dropdown-menu dropdown-menu-right">
							<li><a href="?route=constructors/menu/cloneit&id=<?=$id;?>"><?=t('clone');?></a></li>
							<li class="divider"></li>
							<li><a class="danger" href="?route=constructors/menu/delete&id=<?=$id;?>"><?=t('delete');?></a></li>
						</ul>
					</div>
				</td>
			</tr>
	<?php } ?>
		</tbody>
	</table>
	<div class="panel-footer">
	<form action="?route=constructors/menu/add" method="post">
		<div class="input-group">
			<input type="text" name="title" class="form-control" data-toggle="tooltip" title="<?=t('menu_name')?>" placeholder="<?=t('menu_name_example')?>" required>
			<span class="input-group-btn"><button type="submit" class="btn btn-default"><?=t('add');?></button></span>
		</div>
	</form>
	</div>
</div>