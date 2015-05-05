<form method="get" action="?route=pages&type=<?=$type;?>" id="filter">
<table class="table table-hover table-condensed va-middle selectable idle">
	<thead>
		<tr>
			<th><a class="<?=$ordered['title']['current'];?>" href="?route=pages&type=<?=$type;?>&orderby=title&order=<?=$ordered['title']['new'];?>"><?=t('title');?></a></th>
<?php if ($show_category){ ?>
			<th><a class="<?=$ordered['parent_id']['current'];?>" href="?route=pages&type=<?=$type;?>&orderby=parent_id&order=<?=$ordered['parent_id']['new'];?>"><?=t('category');?></a></th>
<?php } ?>			
<?php foreach($show_meta as $sm){ ?>
			<th><a class="<?=$ordered[$sm]['current'];?>" href="?route=pages&type=<?=$type;?>&orderby=<?=$sm;?>&order=<?=$ordered[$sm]['new'];?>"><?=$sm;?></a></th>
<?php } ?>
			<th class="status"><a class="<?=$ordered['status']['current'];?>" href="?route=pages&type=<?=$type;?>&orderby=status&order=<?=$ordered['status']['new'];?>"><?=t('status');?></a></th>
			<th class="actions"><?=t('actions');?></th>
		</tr>
	</thead>
	<tbody>
	<tr id="filters">
		<td><input type="text" name="filter[title]" class="form-control input-sm" value="<?=$filter['title'];?>"></td>
<?php if ($show_category){ ?>
		<td><select name="filter[parent_id]" class="form-control input-sm"><?=$parents;?></select></td>
<?php } ?>
<?php foreach($show_meta as $sm){?>
			<td><input type="text" name="filter[meta][<?=$sm;?>]" class="form-control input-sm" value="<?php if(isset($filter['meta'][$sm])){echo $filter['meta'][$sm];}?>"></td>
<?php } ?>
		<td><?=H::select('filter[status]', $filter['status'], $statuses, 'class="form-control input-sm"');?></td>
		<td><input type="submit" value="<?=t('filter');?>" class="btn btn-sm btn-default"> <a href="?route=pages&type=<?=$type;?>&filter=clear" class="btn btn-sm btn-default"><?=t('clear');?></a></td>
	</tr>

<?php foreach($pages as $page => $data){ ?>
		<tr class="selectitem s-<?=$data['status'];?>">
			<td>
			<span class="type"></span><a href="?route=pages/edit&type=<?=$type;?>&id=<?=$data['id'];?>"><?=$data['title'];?></a>
			<a href="<?=$base;?><?=$data['fullpath'];?>" target="_blank" class="view-page"><span class="glyphicon glyphicon-new-window"></span></a>
			</td>
<?php if ($show_category){ ?>
			<td>
				<a href="?route=pages/edit&type=<?=$type;?>&id=<?=$data['parent_id'];?>"><?=$data['parent_title'];?></a>
				<a href="<?=$base;?><?=$data['parent_path'];?>" target="_blank" class="view-page"><span class="glyphicon glyphicon-new-window"></span></a>
			</td>
<?php } ?>
<?php foreach($show_meta as $sm){ ?>
			<td><?php if(isset($data[$sm])){echo $data[$sm];}?></td>
<?php } ?>
			<td><div class="status-wrap"><?=$data['status_select']?></div></td>
			<td>
				<input type="checkbox" class="ids" name="id[]" value="<?=$data['id'];?>">
				<div class="btn-group btn-block btn-group-sm">
					<a class="col-xs-9 btn btn-default" href="?route=pages/edit&type=<?=$type;?>&id=<?=$data['id'];?>"><?=t('edit');?></a>
					<button type="button" class="col-xs-3 btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
					<ol class="dropdown-menu dropdown-menu-right">
						<li><a href="?route=pages/edit&type=<?=$type;?>&source=<?=$data['id'];?>"><?=t('clone');?></a></li>
						<li class="divider"></li>
						<li><a class="danger" href="?route=pages/move&to=trash&type=<?=$type;?>&id=<?=$data['id'];?>"><?=t('delete');?></a></li>
					</ol>
				</div>
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
</form>