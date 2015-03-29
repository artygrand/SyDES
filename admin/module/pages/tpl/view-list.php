<form method="get" action="?mod=pages&type=<?=$type;?>" id="pagesfilter">
<table class="table table-hover table-condensed va-middle">
	<thead>
		<tr>
			<th style="width:14px;"><input type="checkbox" id="checkall" title="<?=lang('check_all');?>"></th>
			<th style="width:40px;">ID</th>
			<th><?=lang('name');?></th>
			<th><?=lang('category');?></th>	
<?php if($show_meta){?>
	<?php foreach($show_meta as $sm){?>
			<th><?=$sm;?></th>
	<?php }?>
<?php }?>
			<th style="width:120px;padding-left:15px"><?=lang('status');?></th>
			<th style="width:150px;"><?=lang('actions');?></th>
		</tr>
	</thead>
	<tbody>
	<tr id="filters">
		<td colspan="2"><a href="?mod=pages&type=<?=$type;?>&filter=clear" class="btn btn-sm btn-default"><?=lang('clear');?></a></td>
		<td><input type="text" name="filter[title]" class="form-control input-sm" value="<?php if(isset($filter['title'])){echo $filter['title'];}?>"></td>
		<td><select name="filter[parent_id]" class="form-control input-sm"><?=$parents;?></select></td>
<?php if($show_meta){?>
	<?php foreach($show_meta as $sm){?>
			<td><input type="text" name="filter[meta][<?=$sm;?>]" class="form-control input-sm" value="<?php if(isset($filter['meta'][$sm])){echo $filter['meta'][$sm];}?>"></td>
	<?php }?>
<?php }?>
		<td>
		<?=getSelect($statuses, 6, 'name="filter[status]" class="form-control input-sm"');?>
		</td>
		<td><input type="submit" value="<?=lang('filter');?>" class="btn btn-sm btn-default"></td>
	</tr>
<?php foreach($pages as $page => $data){?>
		<tr class="status-<?=$data['status'];?>">
			<td><input type="checkbox" class="ids" name="id[]" value="<?=$data['id'];?>"></td>
			<td>#<?=$data['id'];?></td>
			<td><a href="..<?=$data['fullpath'];?>"><?=empty($data['title']) ? lang('no_translation') : $data['title'];?></a></td>
			<td><?=$data['parent_title'];?></td>
<?php if($show_meta){?>
	<?php foreach($show_meta as $sm){?>
			<td><?php if(isset($data['meta_' . $sm])){echo $data['meta_' . $sm];}?></td>
	<?php }?>
<?php }?>
			<td><div class="status-wrap"><?=getSelect($statuses, $data['status'], 'data-id="' . $data['id'] . '" class="form-control status input-sm"');?></div></td>
			<td>
				<div class="btn-group btn-block with-dropdown">
					<a class="btn btn-default btn-sm btn-block" href="?mod=pages&type=<?=$type;?>&act=edit&id=<?=$data['id'];?>"><?=lang('edit');?></a>
					<div class="btn-group">
						<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
						<ul class="dropdown-menu pull-right">
							<li><a href="?mod=pages&type=<?=$type;?>&act=edit&source=<?=$data['id'];?>"><?=lang('clone');?></a></li>
							<li class="divider"></li>
							<li><a class="danger" href="?mod=pages&type=<?=$type;?>&act=delete&id=<?=$data['id'];?>"><?=lang('delete');?></a></li>
						</ul>
					</div>
				</div>
			</td>
		</tr>
<?php }?>
	</tbody>
</table>
</form>