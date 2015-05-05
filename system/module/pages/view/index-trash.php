<table class="table table-hover table-condensed va-middle selectable idle">
	<thead>
		<tr>
			<th><?=t('title');?></th>
			<th class="actions"><?=t('actions');?></th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($pages as $page => $data){ ?>
		<tr class="selectitem s-<?=$data['status'];?>">
			<td><span class="type"></span><a href="<?=$base;?><?=$data['fullpath'];?>" target="_blank"><?=$data['title'];?></a></td>
			<td>
				<input type="checkbox" class="ids" name="id[]" value="<?=$data['id'];?>">
				<div class="btn-group btn-block btn-group-sm">
					<a class="col-xs-9 btn btn-default" href="?route=pages/edit&type=trash&id=<?=$data['id'];?>"><?=t('edit');?></a>
					<button type="button" class="col-xs-3 btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
					<ol class="dropdown-menu dropdown-menu-right">
						<li><a class="danger" href="?route=pages/delete&type=trash&id=<?=$data['id'];?>"><?=t('delete');?></a></li>
					</ol>
				</div>
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>